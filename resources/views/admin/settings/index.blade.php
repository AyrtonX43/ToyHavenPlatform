@extends('layouts.admin')

@section('title', 'System Settings - ToyHaven')
@section('page-title', 'System Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    
    <!-- General Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">General Settings</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="general[site_name]" class="form-control" value="{{ \App\Models\SystemSetting::get('site_name', $defaultSettings['general']['site_name']) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="general[contact_email]" class="form-control" value="{{ \App\Models\SystemSetting::get('contact_email', $defaultSettings['general']['contact_email']) }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="general[contact_phone]" class="form-control" value="{{ \App\Models\SystemSetting::get('contact_phone', $defaultSettings['general']['contact_phone']) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Address</label>
                    <input type="text" name="general[contact_address]" class="form-control" value="{{ \App\Models\SystemSetting::get('contact_address', $defaultSettings['general']['contact_address']) }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="general[facebook_url]" class="form-control" value="{{ \App\Models\SystemSetting::get('facebook_url', $defaultSettings['general']['facebook_url']) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Twitter URL</label>
                    <input type="url" name="general[twitter_url]" class="form-control" value="{{ \App\Models\SystemSetting::get('twitter_url', $defaultSettings['general']['twitter_url']) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Instagram URL</label>
                    <input type="url" name="general[instagram_url]" class="form-control" value="{{ \App\Models\SystemSetting::get('instagram_url', $defaultSettings['general']['instagram_url']) }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Terms of Service</label>
                <textarea name="general[terms_of_service]" class="form-control" rows="5">{{ \App\Models\SystemSetting::get('terms_of_service', $defaultSettings['general']['terms_of_service']) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Privacy Policy</label>
                <textarea name="general[privacy_policy]" class="form-control" rows="5">{{ \App\Models\SystemSetting::get('privacy_policy', $defaultSettings['general']['privacy_policy']) }}</textarea>
            </div>
        </div>
    </div>

    <!-- Business Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Business Settings</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Commission Rate (%)</label>
                    <input type="number" name="business[commission_rate]" class="form-control" step="0.01" min="0" max="100" value="{{ \App\Models\SystemSetting::get('commission_rate', $defaultSettings['business']['commission_rate']) }}">
                    <small class="text-muted">Percentage of base price</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax Rate (%)</label>
                    <input type="number" name="business[tax_rate]" class="form-control" step="0.01" min="0" max="100" value="{{ \App\Models\SystemSetting::get('tax_rate', $defaultSettings['business']['tax_rate']) }}">
                    <small class="text-muted">VAT rate (default 12%)</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transaction Fee (₱)</label>
                    <input type="number" name="business[transaction_fee]" class="form-control" step="0.01" min="0" value="{{ \App\Models\SystemSetting::get('transaction_fee', $defaultSettings['business']['transaction_fee']) }}">
                    <small class="text-muted">Fixed fee per transaction</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <input type="text" name="business[currency]" class="form-control" value="{{ \App\Models\SystemSetting::get('currency', $defaultSettings['business']['currency']) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Email Settings (SMTP)</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="brevoPresetBtn" title="Fill Brevo SMTP defaults">
                <i class="bi bi-envelope me-1"></i> Use Brevo
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info small mb-3">
                <strong>Brevo (recommended):</strong> Host = <code>smtp-relay.brevo.com</code>, Port = <code>587</code>, Encryption = TLS.
                Use your Brevo login email as <strong>SMTP Username</strong> and an <strong>SMTP key</strong> (not your login password) as password.
                <strong>From Email</strong> must be a verified sender in your Brevo account. Click "Use Brevo" to fill host/port/encryption.
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="email[smtp_host]" id="email_smtp_host" class="form-control" value="{{ \App\Models\SystemSetting::get('smtp_host', $defaultSettings['email']['smtp_host']) }}" placeholder="e.g. smtp-relay.brevo.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="email[smtp_port]" id="email_smtp_port" class="form-control" value="{{ \App\Models\SystemSetting::get('smtp_port', $defaultSettings['email']['smtp_port']) }}" placeholder="587">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" name="email[smtp_username]" id="email_smtp_username" class="form-control" value="{{ \App\Models\SystemSetting::get('smtp_username', $defaultSettings['email']['smtp_username']) }}" placeholder="Brevo login email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" name="email[smtp_password]" id="email_smtp_password" class="form-control" value="" placeholder="Brevo SMTP key (leave blank to keep current)">
                    <small class="text-muted">Leave blank to keep current password. Use Brevo SMTP key, not your login password.</small>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">SMTP Encryption</label>
                    <select name="email[smtp_encryption]" id="email_smtp_encryption" class="form-select">
                        <option value="tls" {{ \App\Models\SystemSetting::get('smtp_encryption', 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ \App\Models\SystemSetting::get('smtp_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Email</label>
                    <input type="email" name="email[from_email]" id="email_from_email" class="form-control" value="{{ \App\Models\SystemSetting::get('from_email', $defaultSettings['email']['from_email']) }}" placeholder="Verified sender in Brevo">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">From Name</label>
                <input type="text" name="email[from_name]" id="email_from_name" class="form-control" value="{{ \App\Models\SystemSetting::get('from_name', $defaultSettings['email']['from_name']) }}">
            </div>
        </div>
    </div>

    <script>
        document.getElementById('brevoPresetBtn').addEventListener('click', function() {
            document.getElementById('email_smtp_host').value = 'smtp-relay.brevo.com';
            document.getElementById('email_smtp_port').value = '587';
            document.getElementById('email_smtp_encryption').value = 'tls';
        });
    </script>

    <!-- System Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">System Settings</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="system[maintenance_mode]" value="1" id="maintenanceMode" {{ \App\Models\SystemSetting::get('maintenance_mode', false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="maintenanceMode">
                        <strong>Maintenance Mode</strong>
                    </label>
                </div>
                <small class="text-muted">When enabled, the site will be unavailable to non-admin users.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Maintenance Message</label>
                <textarea name="system[maintenance_message]" class="form-control" rows="3">{{ \App\Models\SystemSetting::get('maintenance_message', $defaultSettings['system']['maintenance_message']) }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Save Settings
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
