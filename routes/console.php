<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Schedule::command('auction:end-expired')->everyMinute();
Schedule::command('auction:check-payment-deadlines')->everyFiveMinutes();
Schedule::command('auction:release-escrow')->hourly();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email : The email address to send the test to}', function (string $email): int {
    $this->info('Sending test email to ' . $email . '...');
    try {
        Mail::raw('This is a test email from ToyHaven. If you received this, mail is working.', function ($m) use ($email) {
            $m->to($email)->subject('ToyHaven mail test');
        });
        $this->info('Test email sent successfully.');
        return 0;
    } catch (\Throwable $e) {
        $this->error('Failed to send: ' . $e->getMessage());
        $this->line('');
        $this->line('Full error:');
        $this->line($e->getTraceAsString());
        return 1;
    }
})->purpose('Send a test email to verify Brevo/SMTP configuration');
