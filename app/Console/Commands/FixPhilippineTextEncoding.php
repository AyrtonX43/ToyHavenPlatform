<?php

namespace App\Console\Commands;

use App\Models\Seller;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Console\Command;

class FixPhilippineTextEncoding extends Command
{
    protected $signature = 'fix:philippine-text-encoding';

    protected $description = 'Fix Philippine text encoding issues (Ã± to n) in sellers, users, and addresses tables';

    public function handle()
    {
        $this->info('Starting Philippine text encoding fix...');
        $this->newLine();

        $totalFixed = 0;

        // Fix Sellers table
        $this->info('Fixing Sellers table...');
        $sellers = Seller::all();
        $sellerCount = 0;
        
        foreach ($sellers as $seller) {
            $updated = false;
            $fields = ['region', 'province', 'city', 'barangay', 'address', 'business_name', 'description'];
            
            foreach ($fields as $field) {
                if (!empty($seller->$field)) {
                    $normalized = normalizePhilippineText($seller->$field);
                    if ($normalized !== $seller->$field) {
                        $seller->$field = $normalized;
                        $updated = true;
                    }
                }
            }
            
            if ($updated) {
                $seller->save();
                $sellerCount++;
            }
        }
        
        $this->info("Fixed {$sellerCount} seller records.");
        $totalFixed += $sellerCount;

        // Fix Users table
        $this->info('Fixing Users table...');
        $users = User::all();
        $userCount = 0;
        
        foreach ($users as $user) {
            $updated = false;
            $fields = ['region', 'province', 'city', 'barangay', 'address', 'name'];
            
            foreach ($fields as $field) {
                if (!empty($user->$field)) {
                    $normalized = normalizePhilippineText($user->$field);
                    if ($normalized !== $user->$field) {
                        $user->$field = $normalized;
                        $updated = true;
                    }
                }
            }
            
            if ($updated) {
                $user->save();
                $userCount++;
            }
        }
        
        $this->info("Fixed {$userCount} user records.");
        $totalFixed += $userCount;

        // Fix Addresses table
        $this->info('Fixing Addresses table...');
        $addresses = Address::all();
        $addressCount = 0;
        
        foreach ($addresses as $address) {
            $updated = false;
            $fields = ['region', 'province', 'city', 'barangay', 'address', 'label'];
            
            foreach ($fields as $field) {
                if (!empty($address->$field)) {
                    $normalized = normalizePhilippineText($address->$field);
                    if ($normalized !== $address->$field) {
                        $address->$field = $normalized;
                        $updated = true;
                    }
                }
            }
            
            if ($updated) {
                $address->save();
                $addressCount++;
            }
        }
        
        $this->info("Fixed {$addressCount} address records.");
        $totalFixed += $addressCount;

        // Fix Orders table
        $this->info('Fixing Orders table...');
        $orders = Order::all();
        $orderCount = 0;
        
        foreach ($orders as $order) {
            $updated = false;
            $fields = ['shipping_address', 'shipping_city', 'shipping_province', 'shipping_notes'];
            
            foreach ($fields as $field) {
                if (!empty($order->$field)) {
                    $normalized = normalizePhilippineText($order->$field);
                    if ($normalized !== $order->$field) {
                        $order->$field = $normalized;
                        $updated = true;
                    }
                }
            }
            
            if ($updated) {
                $order->save();
                $orderCount++;
            }
        }
        
        $this->info("Fixed {$orderCount} order records.");
        $totalFixed += $orderCount;

        $this->newLine();
        $this->info("✓ Complete! Fixed {$totalFixed} total records.");
        $this->info('All Philippine text encoding issues have been resolved.');

        return Command::SUCCESS;
    }
}
