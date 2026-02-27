<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->orderByDesc('created_at');
    }

    public function credit(float $amount, string $type, string $description, ?Model $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference) {
            $this->lockForUpdate();
            $this->increment('balance', $amount);
            $this->refresh();

            return WalletTransaction::create([
                'wallet_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $this->balance,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }

    public function debit(float $amount, string $type, string $description, ?Model $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference) {
            $this->lockForUpdate();

            if ($this->balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            $this->decrement('balance', $amount);
            $this->refresh();

            return WalletTransaction::create([
                'wallet_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $this->balance,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }
}
