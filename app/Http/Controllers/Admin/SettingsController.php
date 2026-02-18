<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        // Get all settings grouped by category
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        // Default settings if none exist
        $defaultSettings = [
            'general' => [
                'site_name' => 'ToyHaven Platform',
                'site_logo' => '',
                'site_favicon' => '',
                'contact_email' => '',
                'contact_phone' => '',
                'contact_address' => '',
                'facebook_url' => '',
                'twitter_url' => '',
                'instagram_url' => '',
                'terms_of_service' => '',
                'privacy_policy' => '',
            ],
            'business' => [
                'commission_rate' => '5',
                'tax_rate' => '12',
                'transaction_fee' => '0',
                'currency' => 'PHP',
                'currency_symbol' => '₱',
            ],
            'email' => [
                'smtp_host' => '',
                'smtp_port' => '587',
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'from_email' => '',
                'from_name' => 'ToyHaven',
            ],
            'system' => [
                'maintenance_mode' => '0',
                'maintenance_message' => 'We are currently performing maintenance. Please check back soon.',
            ],
        ];
        
        return view('admin.settings.index', compact('settings', 'defaultSettings'));
    }

    public function update(Request $request)
    {
        // Validate email settings if provided
        if ($request->has('email')) {
            $emailSettings = $request->input('email');
            
            // Only validate if user is trying to configure SMTP (not just clearing settings)
            if (!empty($emailSettings['smtp_host']) || !empty($emailSettings['smtp_username'])) {
                $validation = MailConfigService::validateSmtpSettings($emailSettings);
                
                if (!$validation['valid']) {
                    return back()
                        ->withInput()
                        ->withErrors(['email' => $validation['errors']])
                        ->with('error', 'Email settings validation failed. Please check the errors below.');
                }
            }
        }

        $groups = ['general', 'business', 'email', 'system'];
        
        foreach ($groups as $group) {
            if ($request->has($group)) {
                foreach ($request->input($group) as $key => $value) {
                    // Don't overwrite SMTP password with empty (admin leaves blank to keep current)
                    if ($group === 'email' && $key === 'smtp_password' && (string) $value === '') {
                        continue;
                    }
                    SystemSetting::set(
                        $key,
                        $value,
                        $this->getSettingType($key),
                        $group
                    );
                }
            }
        }

        // Reload mail configuration after saving
        try {
            MailConfigService::loadConfiguration();
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::warning('Failed to reload mail configuration after settings update', [
                'error' => $e->getMessage(),
            ]);
        }
        
        return back()->with('success', 'Settings updated successfully!');
    }
    
    private function getSettingType($key)
    {
        $booleanKeys = ['maintenance_mode'];
        $integerKeys = ['commission_rate', 'tax_rate', 'transaction_fee', 'smtp_port'];
        
        if (in_array($key, $booleanKeys)) {
            return 'boolean';
        }
        if (in_array($key, $integerKeys)) {
            return 'integer';
        }
        
        return 'string';
    }
}
