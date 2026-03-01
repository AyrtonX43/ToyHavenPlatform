<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModeratorAction extends Model
{
    protected $fillable = [
        'moderator_id',
        'action_type',
        'actionable_type',
        'actionable_id',
        'description',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(int $moderatorId, string $actionType, Model $actionable, ?string $description = null, ?array $metadata = null): self
    {
        return self::create([
            'moderator_id' => $moderatorId,
            'action_type' => $actionType,
            'actionable_type' => get_class($actionable),
            'actionable_id' => $actionable->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
