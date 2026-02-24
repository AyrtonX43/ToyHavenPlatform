<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    protected $appends = ['url', 'is_image', 'is_video'];

    protected $fillable = [
        'message_id',
        'file_path',
        'file_type',
        'file_name',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        return $this->file_path ? Storage::url($this->file_path) : '';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->file_type ?? '', 'video/');
    }

    public function getIsImageAttribute(): bool
    {
        return $this->isImage();
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->isVideo();
    }
}
