<?php

namespace App\Http\Controllers\Trading;

use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Events\MessageUnsent;
use App\Events\UserPresenceUpdated;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationReport;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Trade;
use App\Models\TradeListing;
use App\Models\TradeOffer;
use App\Models\TradeProof;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TradeMeetupService;
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
            ->has('messages')
            ->with(['user1', 'user2', 'trade', 'tradeListing.images'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_id', '!=', Auth::id())->whereNull('seen_at');
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('trading.conversations.index', compact('conversations'));
    }

    /**
     * Return unread message count for the current user (for nav badge and notifications).
     */
    public function unreadCount(Request $request)
    {
        $count = Message::whereHas('conversation', function ($q) {
            $q->where('user1_id', Auth::id())->orWhere('user2_id', Auth::id());
        })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('seen_at')
            ->count();

        $conversationsWithUnread = Conversation::query()
            ->where(function ($q) {
                $q->where('user1_id', Auth::id())->orWhere('user2_id', Auth::id());
            })
            ->withCount(['messages as unread' => function ($q) {
                $q->where('sender_id', '!=', Auth::id())->whereNull('seen_at');
            }])
            ->having('unread', '>', 0)
            ->with(['user1', 'user2'])
            ->get()
            ->map(function ($c) {
                $other = $c->getOtherUser(Auth::id());
                return [
                    'id' => $c->id,
                    'other_name' => $other ? $other->name : 'User',
                    'unread' => $c->unread,
                ];
            });

        return response()->json([
            'count' => $count,
            'conversations' => $conversationsWithUnread,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['user1', 'user2', 'trade.proofs', 'trade.tradeOffer.offeredTradeListing.images', 'trade.items', 'tradeListing.user', 'tradeListing.images', 'messages' => fn ($q) => $q->with(['sender', 'attachments', 'tradeListing.images'])->orderByDesc('id')->limit(100)]);
        $messages = $conversation->messages->sortBy('id')->values();

        // When offerer (participant) first opens conversation: add system welcome message
        $trade = $conversation->trade;
        if ($trade && !$conversation->welcome_message_sent && $trade->participant_id === Auth::id()) {
            DB::transaction(function () use ($conversation, $trade) {
                $conversation->messages()->create([
                    'sender_id' => null,
                    'is_system' => true,
                    'system_type' => 'welcome',
                    'message' => 'You can now start the conversation. Use the buttons below to mark listing received, cancel, or report.',
                ]);
                $conversation->update(['welcome_message_sent' => true, 'last_message_at' => now()]);
            });
            $conversation->refresh();
            $messages = $conversation->messages()->with(['sender', 'attachments', 'tradeListing.images'])->orderBy('id')->get();
        }

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

        // For product offering: load current user's listings (and other's) so they can offer products to each other
        $myListings = $other
            ? TradeListing::active()->where('user_id', Auth::id())->with('images')->orderByDesc('updated_at')->limit(20)->get()
            : collect();
        $otherListings = $other
            ? TradeListing::active()->where('user_id', $other->id)->with('images')->orderByDesc('updated_at')->limit(20)->get()
            : collect();

        $participantListing = $trade?->tradeOffer?->offeredTradeListing;
        $participantItem = $trade?->participantItems?->first();
        return view('trading.conversations.show', compact('conversation', 'messages', 'other', 'myListings', 'otherListings', 'participantListing', 'participantItem'));
    }

    public function storeFromListing(Request $request, $id)
    {
        try {
            $listing = TradeListing::findOrFail($id);
            if ($listing->user_id === Auth::id()) {
                return redirect()->route('trading.listings.show', $id)->with('error', 'You cannot message yourself.');
            }
            $conversation = Conversation::firstOrCreateForListing($listing->id, Auth::id(), $listing->user_id);
            return redirect()->route('trading.conversations.show', $conversation)
                ->with('success', 'Conversation started.');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Make an offer failed (DB): ' . $e->getMessage(), ['listing_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('trading.listings.show', $id)->with('error', 'Unable to start conversation. Please try again or contact support.');
        } catch (\Throwable $e) {
            Log::error('Make an offer failed: ' . $e->getMessage(), ['listing_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('trading.listings.show', $id)->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $query = $conversation->messages()->with(['sender', 'attachments', 'tradeListing.images'])->orderBy('created_at');
        $beforeId = $request->integer('before_id');
        $afterId = $request->integer('after_id');
        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }
        if ($afterId) {
            $query->where('id', '>', $afterId);
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
            'offered_listing_id' => ['nullable', 'integer', 'exists:trade_listings,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,webm', 'max:25600'], // 25MB
        ]);

        if (empty(trim($validated['message'] ?? '')) && empty($validated['attachments'] ?? []) && empty($validated['offered_listing_id'] ?? null)) {
            return response()->json(['error' => 'Message, attachment, or product offer required.'], 422);
        }

        $offeredListingId = $validated['offered_listing_id'] ?? null;
        if ($offeredListingId) {
            $listing = TradeListing::find($offeredListingId);
            if (!$listing || $listing->user_id !== Auth::id()) {
                return response()->json(['error' => 'You can only offer your own listings.'], 422);
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'trade_listing_id' => $offeredListingId,
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
        $message->load(['sender', 'attachments', 'tradeListing.images']);
        
        $this->safeBroadcast(new MessageSent($message));

        $messageData = [
            'id' => $message->id,
            'message' => $message->message,
            'created_at' => $message->created_at->timezone(config('app.timezone', 'Asia/Manila'))->toIso8601String(),
            'formatted_created_at' => $message->formatted_created_at,
            'attachments' => $message->attachments->map(fn ($a) => [
                'id' => $a->id,
                'url' => $a->url,
                'file_name' => $a->file_name,
                'is_image' => $a->isImage(),
                'is_video' => $a->isVideo(),
            ])->toArray(),
            'offered_listing' => null,
        ];
        if ($message->tradeListing) {
            $listing = $message->tradeListing;
            $firstImg = $listing->images->first();
            $imgPath = $listing->image_path ?? ($firstImg?->image_path ?? null);
            $messageData['offered_listing'] = [
                'id' => $listing->id,
                'title' => $listing->title,
                'condition' => $listing->condition,
                'image_url' => $imgPath ? asset('storage/' . $imgPath) : null,
                'url' => route('trading.listings.show', $listing->id),
            ];
        }

        return response()->json(['message' => $messageData]);
    }

    public function unsendMessage(Conversation $conversation, Message $message)
    {
        $this->authorize('view', $conversation);

        if ($message->conversation_id !== $conversation->id || $message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'You can only unsend your own messages.'], 403);
        }

        if ($message->isUnsent()) {
            return response()->json(['error' => 'Message already unsent.'], 422);
        }

        $message->update(['unsent_at' => now()]);

        $this->safeBroadcast(new MessageUnsent($conversation->id, $message->id));

        return response()->json(['ok' => true]);
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
        $key = 'chat_typing:'.$conversation->id.':'.Auth::id();
        if ($typing) {
            Cache::put($key, ['name' => Auth::user()->name], now()->addSeconds(4));
        } else {
            Cache::forget($key);
        }
        $this->safeBroadcast(new UserTyping($conversation->id, Auth::id(), Auth::user()->name, $typing));

        return response()->json(['ok' => true]);
    }

    /**
     * Get whether the other user is currently typing (for polling when WebSockets unavailable).
     */
    public function typingStatus(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $other = $conversation->getOtherUser(Auth::id());
        if (! $other) {
            return response()->json(['typing' => false, 'user_name' => null]);
        }
        $key = 'chat_typing:'.$conversation->id.':'.$other->id;
        $data = Cache::get($key);

        return response()->json([
            'typing' => (bool) $data,
            'user_name' => $data['name'] ?? $other->name,
        ]);
    }

    /**
     * Get delivered/seen status of my messages in this conversation (for polling when WebSockets unavailable).
     */
    public function messageStatuses(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $statuses = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', Auth::id())
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'status' => $m->seen_at ? 'seen' : ($m->delivered_at ? 'delivered' : 'sent'),
            ]);

        return response()->json(['statuses' => $statuses]);
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

        $other = $conversation->getOtherUser(Auth::id());
        ConversationReport::create([
            'conversation_id' => $conversation->id,
            'reporter_id' => Auth::id(),
            'reported_user_id' => $other?->id,
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
     * Lock deal: User 1 proposes (creates Trade with only their lock). User 2 must click to agree (sets their lock).
     * Deal is only locked when BOTH users have agreed.
     */
    public function lockDeal(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        if ($conversation->is_locked) {
            return back()->with('error', 'This conversation is locked.');
        }
        $listing = $conversation->tradeListing;
        if (!$listing || !in_array($listing->status, ['active', 'pending_deal'])) {
            return back()->with('error', 'The listing is no longer available for a deal.');
        }

        $other = $conversation->getOtherUser(Auth::id());
        if (!$other) {
            return back()->with('error', 'Invalid conversation.');
        }

        $initiatorId = $listing->user_id;
        $participantId = ($listing->user_id === Auth::id()) ? $other->id : Auth::id();
        if ($initiatorId === $participantId) {
            return back()->with('error', 'Cannot lock deal with yourself.');
        }

        $userId = Auth::id();
        $isInitiator = ($userId === $initiatorId);
        $isParticipant = ($userId === $participantId);

        DB::beginTransaction();
        try {
            if (!$conversation->trade_id) {
                // First user: create Trade (cash-only from chat) and set their agree-to-meet
                $trade = Trade::create([
                    'trade_listing_id' => $listing->id,
                    'trade_offer_id' => null,
                    'initiator_id' => $initiatorId,
                    'initiator_seller_id' => $listing->seller_id,
                    'participant_id' => $participantId,
                    'participant_seller_id' => null,
                    'cash_amount' => $listing->cash_amount,
                    'status' => 'pending_meetup',
                    'initiator_locked_at' => $isInitiator ? now() : null,
                    'participant_locked_at' => $isParticipant ? now() : null,
                ]);
                $conversation->update(['trade_id' => $trade->id]);
                $listing->update(['status' => 'pending_deal']);
                DB::commit();
                return back()->with('success', 'You proposed the deal. Waiting for the other party to agree.');
            }

            $trade = $conversation->trade;
            if (!$trade || !in_array($trade->status, ['pending_meetup', 'meetup_scheduled'])) {
                DB::rollBack();
                return back()->with('error', 'No pending deal to agree to.');
            }

            // Second user (or first user clicking again): set their locked_at if not already set
            if ($isInitiator && !$trade->initiator_locked_at) {
                $trade->update(['initiator_locked_at' => now()]);
            } elseif ($isParticipant && !$trade->participant_locked_at) {
                $trade->update(['participant_locked_at' => now()]);
            } else {
                DB::rollBack();
                return back()->with('info', 'You have already agreed to this deal.');
            }

            $trade->refresh();
            if ($trade->bothLocked()) {
                $trade->update(['status' => 'meetup_scheduled']);
            }

            DB::commit();
            return back()->with('success', $trade->bothLocked()
                ? 'Both parties agreed to meet. Schedule your meetup and confirm when done.'
                : 'You agreed to the deal. Waiting for the other party.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Lock deal failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to lock deal. Please try again.');
        }
    }

    /**
     * Confirm received (payment/product) for the locked deal. Optional proof image.
     */
    public function confirmReceived(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $trade = $conversation->trade;
        if (!$trade || !in_array($trade->status, ['meetup_scheduled', 'meetup_completed']) || !$trade->bothLocked()) {
            return back()->with('error', 'The meetup must be agreed by both parties before confirming.');
        }
        if (!$trade->bothSubmittedProof()) {
            return back()->with('error', 'Both parties must submit their trade photo proof before the trade can be completed.');
        }

        $userId = Auth::id();
        $isInitiator = $trade->initiator_id === $userId;
        $isParticipant = $trade->participant_id === $userId;
        if (!$isInitiator && !$isParticipant) {
            return back()->with('error', 'You are not part of this trade.');
        }

        DB::beginTransaction();
        try {
            if ($isInitiator) {
                $trade->update(['initiator_confirmed_meetup_at' => now()]);
            } else {
                $trade->update(['participant_confirmed_meetup_at' => now()]);
            }

            if ($trade->fresh()->bothConfirmedMeetup()) {
                $trade->update(['status' => 'completed', 'completed_at' => now(), 'meetup_completed_at' => now()]);
                $listing = $trade->tradeListing;
                $listing->update(['status' => 'completed']);

                if ($listing->user_product_id) {
                    UserProduct::where('id', $listing->user_product_id)->update(['status' => 'traded']);
                }
                if ($listing->product_id) {
                    \App\Models\Product::where('id', $listing->product_id)->update(['trade_status' => 'traded']);
                }
                if ($trade->trade_offer_id) {
                    $offer = TradeOffer::find($trade->trade_offer_id);
                    if ($offer && $offer->offered_user_product_id) {
                        UserProduct::where('id', $offer->offered_user_product_id)->update(['status' => 'traded']);
                    }
                    if ($offer && $offer->offered_product_id) {
                        \App\Models\Product::where('id', $offer->offered_product_id)->update(['trade_status' => 'traded']);
                    }
                }

                $conversation->update(['is_locked' => true]);
            } else {
                $trade->update(['status' => 'meetup_completed']);
            }
            DB::commit();
            return back()->with('success', $trade->fresh()->bothConfirmedMeetup()
                ? 'Trade completed. This chat is now locked.'
                : 'You confirmed the meetup. Waiting for the other party.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Confirm received failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to confirm. Please try again.');
        }
    }

    /**
     * Submit trade photo proof (required from both parties). When both have submitted, trade is completed.
     */
    public function submitTradeProof(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $trade = $conversation->trade;
        if (!$trade || in_array($trade->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This trade is no longer active.');
        }

        $userId = Auth::id();
        if ($trade->initiator_id !== $userId && $trade->participant_id !== $userId) {
            return back()->with('error', 'You are not part of this trade.');
        }

        $existing = $trade->proofs()->where('user_id', $userId)->first();
        if ($existing) {
            return back()->with('info', 'You have already submitted your trade proof.');
        }

        $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'],
        ]);

        DB::beginTransaction();
        try {
            $path = $request->file('proof_image')->store('trade_proofs/' . $trade->id, 'public');
            TradeProof::create([
                'trade_id' => $trade->id,
                'user_id' => $userId,
                'proof_image_path' => $path,
                'submitted_at' => now(),
            ]);

            $trade->refresh();
            if ($trade->bothSubmittedProof()) {
                app(TradeMeetupService::class)->complete($trade);
                $conversation->update(['is_locked' => true]);
            }
            DB::commit();
            return back()->with('success', $trade->bothSubmittedProof()
                ? 'Both parties have submitted proof. Trade completed and chat locked.'
                : 'Your trade proof has been submitted. Waiting for the other party.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Submit trade proof failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit proof. Please try again.');
        }
    }

    /**
     * Cancel trade from chat. Posts system message, locks conversation, saves to Trade History as rejected.
     */
    public function cancelTrade(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $trade = $conversation->trade;
        if (!$trade || in_array($trade->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This trade is no longer active.');
        }

        $userId = Auth::id();
        if ($trade->initiator_id !== $userId && $trade->participant_id !== $userId) {
            return back()->with('error', 'You are not part of this trade.');
        }

        $userName = Auth::user()->name;

        DB::beginTransaction();
        try {
            $conversation->messages()->create([
                'sender_id' => null,
                'is_system' => true,
                'system_type' => 'cancelled',
                'message' => $userName . ' cancelled the trade transaction.',
            ]);
            $conversation->update(['last_message_at' => now(), 'is_locked' => true]);

            $trade->update([
                'status' => 'cancelled',
                'cancelled_by_user_id' => $userId,
            ]);

            app(TradeMeetupService::class)->cancel($trade);
            DB::commit();
            return back()->with('success', 'Trade cancelled. You can report this conversation with feedback for admin review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Cancel trade failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel. Please try again.');
        }
    }

    /**
     * Mark listing as received. When both have marked, complete trade and lock chat.
     */
    public function markListingReceived(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $trade = $conversation->trade;
        if (!$trade || in_array($trade->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This trade is no longer active.');
        }

        $userId = Auth::id();
        if ($trade->initiator_id !== $userId && $trade->participant_id !== $userId) {
            return back()->with('error', 'You are not part of this trade.');
        }

        DB::beginTransaction();
        try {
            if ($trade->isInitiator($userId)) {
                $trade->update(['initiator_received_at' => now()]);
            } else {
                $trade->update(['participant_received_at' => now()]);
            }

            if ($trade->fresh()->bothReceived()) {
                app(TradeMeetupService::class)->complete($trade);
                $conversation->messages()->create([
                    'sender_id' => null,
                    'is_system' => true,
                    'system_type' => 'completed',
                    'message' => 'Both parties marked the listing as received. Trade completed.',
                ]);
                $conversation->update(['is_locked' => true, 'last_message_at' => now()]);
            }
            DB::commit();
            return back()->with('success', $trade->fresh()->bothReceived()
                ? 'Trade completed. Both parties received their items. Chat locked.'
                : 'You marked the listing as received. Waiting for the other party.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Mark received failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to mark received. Please try again.');
        }
    }

    /**
     * Broadcast an event without failing the request when Reverb/Pusher is unavailable.
     */
    private function safeBroadcast(object $event): void
    {
        try {
            broadcast($event)->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed (is Reverb/Pusher running?): ' . $e->getMessage());
        }
    }
}
