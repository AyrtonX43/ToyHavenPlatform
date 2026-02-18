@php
    $isMine = $msg->sender_id === Auth::id();
    $status = $msg->seen_at ? 'Seen' : ($msg->delivered_at ? 'Delivered' : 'Sent');
    if (!$isMine) $status = '';
@endphp
<div class="msg-bubble {{ $isMine ? 'mine' : 'theirs' }}" data-message-id="{{ $msg->id }}">
    @if(!$isMine)
        <div class="msg-sender">{{ $msg->sender?->name }}</div>
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
    <div class="msg-time">
        {{ $msg->formatted_created_at }}
        @if($isMine)
            <span class="msg-status msg-status-{{ strtolower($status) }}" data-status="{{ strtolower($status) }}">
                @if($status === 'Seen')
                    <i class="bi bi-check2-all me-1 seen-check" aria-hidden="true"></i>
                @elseif($status === 'Delivered')
                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>
                @endif
                {{ $status }}
            </span>
        @endif
    </div>
</div>
