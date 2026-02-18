<?php

namespace App\Http\Controllers\Trading;

use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Events\UserPresenceUpdated;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationReport;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\TradeListing;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::query()
            ->where(function ($q) {
                $q->where('user1_id', Auth::id())
                    ->orWhere('user2_id', Auth::id());
            })
            ->with(['user1', 'user2', 'trade', 'tradeListing.images'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_id', '!=', Auth::id())->whereNull('seen_at');
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('trading.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['user1', 'user2', 'trade', 'tradeListing.images', 'messages' => fn ($q) => $q->with(['sender', 'attachments'])->orderByDesc('id')->limit(100)]);
        $messages = $conversation->messages->sortBy('id')->values();

        // Mark others' messages as delivered (if not already) and seen when opening the chat
        $otherMessageIds = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->pluck('id');
        if ($otherMessageIds->isNotEmpty()) {
            Message::whereIn('id', $otherMessageIds)->update([
                'is_read' => true,
                'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
                'seen_at' => now(),
            ]);
            foreach ($otherMessageIds as $messageId) {
                $this->safeBroadcast(new MessageStatusUpdated($conversation->id, $messageId, 'seen'));
            }
        }

        // Update presence
        Auth::user()->update(['last_seen_at' => now()]);
        $this->safeBroadcast(new UserPresenceUpdated(Auth::user(), $conversation->id));

        $other = $conversation->getOtherUser(Auth::id());

        return view('trading.conversations.show', compact('conversation', 'messages', 'other'));
    }

    public function storeFromListing(Request $request, $id)
    {
        $listing = TradeListing::findOrFail($id);
        if ($listing->user_id === Auth::id()) {
            return redirect()->route('trading.listings.show', $id)->with('error', 'You cannot message yourself.');
        }
        $conversation = Conversation::firstOrCreateForListing($listing->id, Auth::id(), $listing->user_id);
        return redirect()->route('trading.conversations.show', $conversation)
            ->with('success', 'Conversation started.');
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $query = $conversation->messages()->with(['sender', 'attachments'])->orderBy('created_at');
        $beforeId = $request->integer('before_id');
        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }
        $messages = $query->limit(50)->get();

        $otherIds = $messages->where('sender_id', '!=', Auth::id())->pluck('id');
        if ($otherIds->isNotEmpty()) {
            Message::whereIn('id', $otherIds)->update([
                'is_read' => true,
                'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
                'seen_at' => now(),
            ]);
        }

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,webm', 'max:25600'], // 25MB
        ]);

        if (empty(trim($validated['message'] ?? '')) && empty($validated['attachments'] ?? [])) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => trim($validated['message'] ?? '') ?: '',
            'delivered_at' => now(), // Auto-mark as delivered to sender immediately
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('chat-attachments/' . $conversation->id . '/' . $message->id, 'public');
                $message->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        $conversation->update(['last_message_at' => $message->created_at]);

        // Reload message with relationships before broadcasting
        $message->load(['sender', 'attachments']);
        
        $this->safeBroadcast(new MessageSent($message));

        return response()->json(['message' => $message]);
    }

    public function markDelivered(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $ids = $request->validate(['message_ids' => 'required|array', 'message_ids.*' => 'integer'])['message_ids'];
        // Only update and broadcast for messages that are not yet delivered
        $idsToUpdate = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereIn('id', $ids)
            ->whereNull('delivered_at')
            ->pluck('id');
        $updated = Message::whereIn('id', $idsToUpdate)->update(['delivered_at' => now()]);

        foreach ($idsToUpdate as $id) {
            $this->safeBroadcast(new MessageStatusUpdated($conversation->id, $id, 'delivered'));
        }

        return response()->json(['updated' => $updated]);
    }

    public function markSeen(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        // Only update messages that are not yet seen; set delivered_at if not set (seen implies delivered)
        $idsToUpdate = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('seen_at')
            ->pluck('id');
        if ($idsToUpdate->isNotEmpty()) {
            Message::whereIn('id', $idsToUpdate)->update([
                'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
                'seen_at' => now(),
                'is_read' => true,
            ]);
            foreach ($idsToUpdate as $id) {
                $this->safeBroadcast(new MessageStatusUpdated($conversation->id, $id, 'seen'));
            }
        }

        return response()->json(['ok' => true]);
    }

    public function typing(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $typing = (bool) $request->input('typing', true);
        $this->safeBroadcast(new UserTyping($conversation->id, Auth::id(), Auth::user()->name, $typing));

        return response()->json(['ok' => true]);
    }

    public function presence(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        Auth::user()->update(['last_seen_at' => now()]);
        $this->safeBroadcast(new UserPresenceUpdated(Auth::user(), $conversation->id));

        return response()->json([
            'last_seen_at' => Auth::user()->last_seen_at?->toIso8601String(),
            'is_online' => Auth::user()->isOnline(),
        ]);
    }

    /**
     * Get the other participant's online status (for polling when WebSockets unavailable).
     */
    public function otherStatus(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $other = $conversation->getOtherUser(Auth::id());
        if (! $other) {
            return response()->json(['is_online' => false, 'last_seen_at' => null]);
        }
        $lastSeen = $other->last_seen_at?->timezone(config('app.timezone'));
        return response()->json([
            'is_online' => $other->isOnline(),
            'last_seen_at' => $other->last_seen_at?->toIso8601String(),
            'last_seen_relative' => $lastSeen ? $lastSeen->diffForHumans() : null,
        ]);
    }

    public function report(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at')
            ->get();

        $snapshot = $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'message' => $m->message,
                'created_at' => $m->created_at->toIso8601String(),
                'attachments' => $m->attachments->map(fn ($a) => [
                    'file_path' => $a->file_path,
                    'url' => Storage::url($a->file_path),
                    'file_type' => $a->file_type,
                ])->toArray(),
            ];
        })->toArray();

        ConversationReport::create([
            'conversation_id' => $conversation->id,
            'reporter_id' => Auth::id(),
            'reason' => $validated['reason'],
            'snapshot' => $snapshot,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Conversation reported. Admin will review the snapshot.');
    }

    public function reportForm(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        return view('trading.conversations.report', compact('conversation'));
    }

    /**
     * Broadcast an event without failing the request when Reverb/Pusher is unavailable.
     */
    private function safeBroadcast(object $event): void
    {
        try {
            broadcast($event)->toOthers();
        } catch (BroadcastException $e) {
            Log::warning('Broadcast failed (is Reverb/Pusher running?): ' . $e->getMessage());
        }
    }
}
