<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        $this->message->load(['sender', 'attachments', 'tradeListing.images']);
        $tz = config('app.timezone', 'Asia/Manila');
        $createdAt = $this->message->created_at->timezone($tz);
        $payload = [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'created_at' => $createdAt->toIso8601String(),
            'created_at_formatted' => $createdAt->format('M j, g:i A'),
            'formatted_created_at' => $this->message->formatted_created_at,
            'sender_name' => $this->message->sender?->name,
            'attachments' => $this->message->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_path' => $a->file_path,
                'url' => $a->url,
                'file_type' => $a->file_type,
                'file_name' => $a->file_name,
                'is_image' => $a->isImage(),
                'is_video' => $a->isVideo(),
            ])->toArray(),
        ];
        if ($this->message->tradeListing) {
            $listing = $this->message->tradeListing;
            $firstImg = $listing->images->first();
            $imgPath = $listing->image_path ?? ($firstImg?->image_path ?? null);
            $payload['offered_listing'] = [
                'id' => $listing->id,
                'title' => $listing->title,
                'condition' => $listing->condition,
                'image_url' => $imgPath ? url('storage/' . $imgPath) : null,
                'url' => route('trading.listings.show', $listing->id),
            ];
        } else {
            $payload['offered_listing'] = null;
        }
        return $payload;
    }
}
