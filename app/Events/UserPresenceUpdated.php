<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public int $conversationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserPresenceUpdated';
    }

    public function broadcastWith(): array
    {
        $lastSeen = $this->user->last_seen_at?->timezone(config('app.timezone'));
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'last_seen_at' => $this->user->last_seen_at?->toIso8601String(),
            'last_seen_relative' => $lastSeen ? $lastSeen->diffForHumans() : null,
            'is_online' => $this->user->isOnline(),
        ];
    }
}
