@php
    $lastDate = null;
@endphp

@foreach ($messages as $message)
    @php
        $messageDate = \Carbon\Carbon::parse($message->sent_at)->timezone('Asia/Singapore')->startOfDay();
    @endphp

    @if ($lastDate === null || $lastDate->notEqualTo($messageDate))
        <div class="text-center my-3">
            <span class="badge bg-secondary">
                {{ $messageDate->isToday() ? 'Today' : ($messageDate->isYesterday() ? 'Yesterday' : $messageDate->format('F j, Y')) }}
            </span>
        </div>
        @php
            $lastDate = $messageDate;
        @endphp
    @endif

    <div class="message mb-2 p-2" id="message-{{ $loop->last ? 'last' : $loop->index }}">
        <div class="d-flex align-items-center @if($message->user_id === auth()->id()) justify-content-end @else justify-content-start @endif">
            @if($message->user_id !== auth()->id())
                <div class="chat-icon">{{ strtoupper(substr($message->user->name, 0, 1)) }}</div>
            @endif

            @if($message->user_id === auth()->id())
                <div class="chat-icon">{{ strtoupper(substr($message->user->name, 0, 1)) }}</div>
            @endif
            <div>
                <strong>{{ $message->user->name }}:</strong> {{ $message->message }}
                <div class="text-muted">
                    @php
                        // Convert to Singapore time
                        $sent_at = \Carbon\Carbon::parse($message->sent_at)->timezone('Asia/Singapore');
                    @endphp
                    {{ $sent_at->format('H:i') }} {{-- Display Singapore time --}}
                </div>
            </div>
        </div>
    </div>
@endforeach
