<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $listings = DB::table('trade_listings')->whereNotNull('image_path')->get();
        foreach ($listings as $row) {
            DB::table('trade_listing_images')->insert([
                'trade_listing_id' => $row->id,
                'image_path' => $row->image_path,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Optional: could delete rows that match old image_path
    }
};
