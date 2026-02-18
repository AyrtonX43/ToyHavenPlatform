<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or Update Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@toyhaven.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Always update password and role to ensure they're correct
        $admin->update([
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created/updated:');
        $this->command->info('  Email: admin@toyhaven.com');
        $this->command->info('  Password: password');
        $this->command->info('  Role: admin');

        // Create or Update Seller User (Business Admin)
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@toyhaven.com'],
            [
                'name' => 'Business Admin',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'email_verified_at' => now(),
            ]
        );

        // Always update password and role to ensure they're correct
        $sellerUser->update([
            'password' => Hash::make('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        // Create or update Seller record
        $seller = Seller::firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'business_name' => 'Test Toyshop',
                'business_slug' => Str::slug('Test Toyshop') . '-' . $sellerUser->id,
                'description' => 'A test toyshop for testing product uploads and system functionality.',
                'phone' => '+63 912 345 6789',
                'email' => 'seller@toyhaven.com',
                'address' => '123 Test Street',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'postal_code' => '1000',
                'verification_status' => 'approved', // Auto-approved for testing
                'is_active' => true,
                'is_verified_shop' => true,
            ]
        );

        // Update seller to approved status if it exists but is pending
        if ($seller->verification_status !== 'approved') {
            $seller->update([
                'verification_status' => 'approved',
                'is_active' => true,
            ]);
        }

        $this->command->info('');
        $this->command->info('Seller user created/updated:');
        $this->command->info('  Email: seller@toyhaven.com');
        $this->command->info('  Password: password');
        $this->command->info('  Role: seller');
        $this->command->info('  Business Name: ' . $seller->business_name);
        $this->command->info('  Verification Status: ' . $seller->verification_status);
        $this->command->info('');
        $this->command->info('✅ Test users are ready!');
        $this->command->info('You can now login using the credentials above.');
    }
}
