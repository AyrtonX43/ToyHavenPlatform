@php
    $isMine = $msg->sender_id === Auth::id();
    $attrs = $msg->getAttributes();
    $isUnsent = isset($attrs['unsent_at']) && $attrs['unsent_at'] !== null;
    $status = $msg->seen_at ? 'Seen' : ($msg->delivered_at ? 'Delivered' : 'Sent');
    if (!$isMine) $status = '';
@endphp
<div class="msg-bubble {{ $isMine ? 'mine' : 'theirs' }} {{ $isUnsent ? 'msg-unsent' : '' }}" data-message-id="{{ $msg->id }}" data-unsent="{{ $isUnsent ? '1' : '0' }}">
    @if(!$isMine)
        <div class="msg-sender">{{ $msg->sender?->name }}</div>
    @endif
    @if($isUnsent)
        <div class="msg-unsent-text"><i class="bi bi-x-circle me-1"></i> {{ $isMine ? 'You removed this message' : 'This message was removed' }}</div>
    @else
    @if($msg->tradeListing)
        <a href="{{ route('trading.listings.show', $msg->tradeListing->id) }}" class="msg-offered-product d-block text-decoration-none text-dark rounded p-2 mb-2" style="background: rgba(0,0,0,0.06);">
            <div class="d-flex align-items-center gap-2">
                @php $img = $msg->tradeListing->image_path ?? $msg->tradeListing->images->first()?->image_path; @endphp
                @if($img)
                    <img src="{{ asset('storage/' . $img) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>
                @endif
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold small text-truncate">{{ $msg->tradeListing->title }}</div>
                    @if($msg->tradeListing->condition)
                        <span class="badge bg-secondary" style="font-size:0.65rem;">{{ $msg->tradeListing->condition }}</span>
                    @endif
                    <div class="text-primary small mt-0"><i class="bi bi-box-arrow-up-right me-1"></i>View listing</div>
                </div>
            </div>
        </a>
    @endif
    @if($msg->message)
        <div class="msg-text">{{ $msg->message }}</div>
    @endif
    @if($msg->attachments->isNotEmpty())
        <div class="msg-attachments">
            @foreach($msg->attachments as $att)
                @if($att->isImage())
                    <img src="{{ $att->url }}" alt="">
                @elseif($att->isVideo())
                    <video src="{{ $att->url }}" controls></video>
                @else
                    <a href="{{ $att->url }}" target="_blank">{{ $att->file_name ?: 'File' }}</a>
                @endif
            @endforeach
        </div>
    @endif
    <div class="msg-time d-flex align-items-center gap-1 flex-wrap">
        {{ $msg->formatted_created_at }}
        @if($isMine)
            @if(!$isUnsent)
                <button type="button" class="msg-unsend-btn btn btn-link btn-sm p-0 ms-1" data-message-id="{{ $msg->id }}" title="Remove message" aria-label="Remove message">
                    <i class="bi bi-trash3 text-danger" style="font-size: 0.75rem;"></i>
                </button>
            @endif
            @if(!$isUnsent)
            <span class="msg-status msg-status-{{ strtolower($status) }}" data-status="{{ strtolower($status) }}">
                @if($status === 'Seen')
                    <i class="bi bi-check2-all me-1 seen-check" aria-hidden="true"></i>
                @elseif($status === 'Delivered')
                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>
                @endif
                {{ $status }}
            </span>
            @endif
        @endif
    </div>
    @endif
</div>
