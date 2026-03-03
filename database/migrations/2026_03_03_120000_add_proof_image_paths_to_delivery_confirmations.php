<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            $table->json('proof_image_paths')->nullable()->after('proof_image_path');
        });

        // Migrate existing single path to array
        $rows = \DB::table('delivery_confirmations')->whereNotNull('proof_image_path')->get();
        foreach ($rows as $row) {
            \DB::table('delivery_confirmations')->where('id', $row->id)->update([
                'proof_image_paths' => json_encode([$row->proof_image_path]),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            $table->dropColumn('proof_image_paths');
        });
    }
};
