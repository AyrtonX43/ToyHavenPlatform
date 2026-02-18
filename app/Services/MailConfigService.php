<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailConfigService
{
    /**
     * Load mail configuration from database and apply to config.
     * Falls back to environment variables if database settings are not available.
     *
     * @return void
     */
    public static function loadConfiguration(): void
    {
        try {
            // Get SMTP settings from database (Admin → Settings → Email)
            $smtpHost = trim((string) SystemSetting::get('smtp_host', ''));
            $smtpPort = SystemSetting::get('smtp_port', 587);
            $smtpUsername = trim((string) SystemSetting::get('smtp_username', ''));
            $smtpPassword = (string) SystemSetting::get('smtp_password', '');
            $smtpEncryption = SystemSetting::get('smtp_encryption', 'tls');
            $fromEmail = trim((string) SystemSetting::get('from_email', ''));
            $fromName = trim((string) SystemSetting::get('from_name', 'ToyHaven'));

            // When database has SMTP configured, always use it (overrides .env / Mailtrap)
            if ($smtpHost !== '' && $smtpUsername !== '' && $smtpPassword !== '') {
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.host', $smtpHost);
                Config::set('mail.mailers.smtp.port', (int) $smtpPort);
                Config::set('mail.mailers.smtp.encryption', $smtpEncryption ?: null);
                Config::set('mail.mailers.smtp.username', $smtpUsername);
                Config::set('mail.mailers.smtp.password', $smtpPassword);
                Config::set('mail.mailers.smtp.timeout', 50);

                if ($fromEmail !== '') {
                    Config::set('mail.from.address', $fromEmail);
                }
                if ($fromName !== '') {
                    Config::set('mail.from.name', $fromName);
                }

                Log::debug('Mail configuration loaded from database (SMTP)', [
                    'host' => $smtpHost,
                    'port' => $smtpPort,
                    'encryption' => $smtpEncryption,
                    'from_email' => $fromEmail ?: '(not set)',
                ]);
            } else {
                // No database SMTP: use .env. Prefer smtp (or brevo), not mailtrap, if not set.
                $defaultMailer = env('MAIL_MAILER', 'smtp');
                if (!in_array($defaultMailer, ['smtp', 'brevo', 'sendmail', 'log', 'array'], true)) {
                    $defaultMailer = 'smtp';
                }
                Config::set('mail.default', $defaultMailer);
                Log::debug('Mail configuration using .env', ['mailer' => config('mail.default')]);
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            // Fall back to environment variables
            Log::warning('Failed to load mail configuration from database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate SMTP settings.
     *
     * @param array $settings
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateSmtpSettings(array $settings): array
    {
        $errors = [];

        if (empty($settings['smtp_host'])) {
            $errors[] = 'SMTP host is required.';
        }

        if (empty($settings['smtp_port']) || !is_numeric($settings['smtp_port'])) {
            $errors[] = 'SMTP port must be a valid number.';
        } elseif ((int)$settings['smtp_port'] < 1 || (int)$settings['smtp_port'] > 65535) {
            $errors[] = 'SMTP port must be between 1 and 65535.';
        }

        if (empty($settings['smtp_username'])) {
            $errors[] = 'SMTP username is required.';
        }

        if (empty($settings['smtp_password'])) {
            $errors[] = 'SMTP password is required.';
        }

        if (!empty($settings['smtp_encryption']) && !in_array($settings['smtp_encryption'], ['tls', 'ssl', null])) {
            $errors[] = 'SMTP encryption must be either TLS or SSL.';
        }

        if (!empty($settings['from_email']) && !filter_var($settings['from_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'From email must be a valid email address.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Test SMTP connection with given settings.
     *
     * @param array $settings
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testConnection(array $settings): array
    {
        $validation = self::validateSmtpSettings($settings);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid settings: ' . implode(' ', $validation['errors']),
            ];
        }

        try {
            // Temporarily set config
            $originalConfig = [
                'default' => config('mail.default'),
                'smtp' => config('mail.mailers.smtp'),
            ];

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
            Config::set('mail.mailers.smtp.port', $settings['smtp_port']);
            Config::set('mail.mailers.smtp.encryption', $settings['smtp_encryption'] ?? 'tls');
            Config::set('mail.mailers.smtp.username', $settings['smtp_username']);
            Config::set('mail.mailers.smtp.password', $settings['smtp_password']);

            // Try to send a test email
            $fromEmail = $settings['from_email'] ?? config('mail.from.address');
            $fromName = $settings['from_name'] ?? config('mail.from.name', 'ToyHaven');

            \Illuminate\Support\Facades\Mail::raw('This is a test email from ToyHaven Platform.', function ($message) use ($fromEmail, $fromName) {
                $message->to($fromEmail)
                        ->subject('Test Email - ToyHaven Platform');
                if ($fromName) {
                    $message->from($fromEmail, $fromName);
                }
            });

            // Restore original config
            Config::set('mail.default', $originalConfig['default']);
            Config::set('mail.mailers.smtp', $originalConfig['smtp']);

            return [
                'success' => true,
                'message' => 'Test email sent successfully!',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ];
        }
    }
}
