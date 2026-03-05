<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeProof extends Model
{
    protected $fillable = ['trade_id', 'user_id', 'proof_image_path', 'proof_images', 'submitted_at'];

    protected $casts = [
        'submitted_at' => 'datetime',
        'proof_images' => 'array',
    ];

    /** @return array<string> */
    public function getImagePaths(): array
    {
        if (!empty($this->proof_images) && is_array($this->proof_images)) {
            return $this->proof_images;
        }
        return $this->proof_image_path ? [$this->proof_image_path] : [];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
