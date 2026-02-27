<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class MailDiagnostic extends Command
{
    protected $signature = 'mail:diagnostic {--send-test : Send a test email to the from address}';
    protected $description = 'Diagnose mail configuration and test SMTP connectivity';

    public function handle(): int
    {
        $this->info('=== Mail Diagnostic ===');
        $this->newLine();

        // 1. Show .env values
        $this->info('--- .env values ---');
        $this->table(['Key', 'Value'], [
            ['MAIL_MAILER', env('MAIL_MAILER', '(not set)')],
            ['MAIL_HOST', env('MAIL_HOST', '(not set)')],
            ['MAIL_PORT', env('MAIL_PORT', '(not set)')],
            ['MAIL_USERNAME', env('MAIL_USERNAME') ? substr(env('MAIL_USERNAME'), 0, 10) . '...' : '(not set)'],
            ['MAIL_PASSWORD', env('MAIL_PASSWORD') ? '****' . substr(env('MAIL_PASSWORD'), -4) : '(not set)'],
            ['MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', '(not set)')],
            ['MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', '(not set)')],
            ['MAIL_FROM_NAME', env('MAIL_FROM_NAME', '(not set)')],
        ]);

        // 2. Show database overrides
        $this->newLine();
        $this->info('--- Database overrides (system_settings) ---');
        try {
            $dbHost = SystemSetting::get('smtp_host', '');
            $dbPort = SystemSetting::get('smtp_port', '');
            $dbUser = SystemSetting::get('smtp_username', '');
            $dbPass = SystemSetting::get('smtp_password', '');
            $dbEnc  = SystemSetting::get('smtp_encryption', '');
            $dbFrom = SystemSetting::get('from_email', '');
            $dbName = SystemSetting::get('from_name', '');

            $hasDbConfig = trim((string)$dbHost) !== '' && trim((string)$dbUser) !== '' && (string)$dbPass !== '';

            $this->table(['Key', 'Value'], [
                ['smtp_host', $dbHost ?: '(empty)'],
                ['smtp_port', $dbPort ?: '(empty)'],
                ['smtp_username', $dbUser ? substr((string)$dbUser, 0, 10) . '...' : '(empty)'],
                ['smtp_password', $dbPass ? '****' : '(empty)'],
                ['smtp_encryption', $dbEnc ?: '(empty)'],
                ['from_email', $dbFrom ?: '(empty)'],
                ['from_name', $dbName ?: '(empty)'],
            ]);

            if ($hasDbConfig) {
                $this->warn('>> Database SMTP is configured — it OVERRIDES .env values.');
            } else {
                $this->info('>> Database SMTP is NOT configured — .env values are used.');
            }
        } catch (\Exception $e) {
            $this->error('Could not read system_settings: ' . $e->getMessage());
        }

        // 3. Show effective (runtime) config
        $this->newLine();
        $this->info('--- Effective runtime config (after MailConfigService) ---');
        $this->table(['Key', 'Value'], [
            ['mail.default', config('mail.default')],
            ['mail.mailers.smtp.host', config('mail.mailers.smtp.host')],
            ['mail.mailers.smtp.port', config('mail.mailers.smtp.port')],
            ['mail.mailers.smtp.username', config('mail.mailers.smtp.username') ? substr(config('mail.mailers.smtp.username'), 0, 10) . '...' : '(not set)'],
            ['mail.mailers.smtp.password', config('mail.mailers.smtp.password') ? '****' : '(not set)'],
            ['mail.mailers.smtp.encryption', config('mail.mailers.smtp.encryption') ?? '(null)'],
            ['mail.from.address', config('mail.from.address')],
            ['mail.from.name', config('mail.from.name')],
        ]);

        // 4. Identify problems
        $this->newLine();
        $this->info('--- Checks ---');

        $mailer = config('mail.default');
        if ($mailer === 'log') {
            $this->error('PROBLEM: mail.default is "log" — emails are written to log, not sent!');
            $this->warn('Fix: Set MAIL_MAILER=smtp in .env, or configure SMTP in Admin → Settings → Email.');
        } elseif ($mailer === 'array') {
            $this->error('PROBLEM: mail.default is "array" — emails are discarded!');
        } else {
            $this->info("Mailer: {$mailer} (OK)");
        }

        $host = config('mail.mailers.smtp.host');
        if (empty($host) || $host === '127.0.0.1') {
            $this->error("PROBLEM: SMTP host is '{$host}' — no real SMTP server configured.");
        } else {
            $this->info("SMTP host: {$host} (OK)");
        }

        $fromAddr = config('mail.from.address');
        if (empty($fromAddr) || $fromAddr === 'hello@example.com') {
            $this->error("PROBLEM: From address is '{$fromAddr}' — not set properly.");
        } else {
            $this->info("From address: {$fromAddr} (OK)");
        }

        // 5. Optional: send test email
        if ($this->option('send-test')) {
            $this->newLine();
            $this->info('--- Sending test email ---');
            $to = $fromAddr;
            $this->line("Sending to: {$to}");

            try {
                Mail::raw('This is a mail diagnostic test from ToyHaven.', function ($msg) use ($to) {
                    $msg->to($to)->subject('ToyHaven Mail Diagnostic Test');
                });
                $this->info('Test email sent successfully!');
            } catch (\Throwable $e) {
                $this->error('FAILED: ' . $e->getMessage());
                $this->newLine();
                $this->warn('Common fixes:');
                $this->line('  - Verify SMTP credentials are correct in Brevo dashboard');
                $this->line('  - Ensure from_email is verified as a sender in Brevo');
                $this->line('  - Check if port 587 is open on this server (not blocked by firewall)');
                $this->line('  - Try MAIL_ENCRYPTION=tls with port 587, or ssl with port 465');
            }
        } else {
            $this->newLine();
            $this->comment('Tip: Run with --send-test to actually send a test email:');
            $this->comment('  php artisan mail:diagnostic --send-test');
        }

        return 0;
    }
}
