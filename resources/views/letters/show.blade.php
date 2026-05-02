@extends('layouts.app')

@section('title', $letter->title)

@section('content')
<div class="show-page">

    <div class="page-header">
        <a href="{{ route('letters.index') }}" class="btn-back">← Back to Letters</a>
    </div>

    {{-- Letter Display --}}
    <div class="letter-wrapper">
        {{-- Envelope header --}}
        <div class="envelope-header">
            <div class="wax-seal">💌</div>
            <div class="envelope-meta">
                <div class="env-from">
                    <span class="env-label">From</span>
                    <div class="env-avatar">{{ strtoupper(substr($letter->sender->name ?? 'U', 0, 1)) }}</div>
                    <span class="env-name">{{ $letter->sender->name ?? 'Anonymous' }}</span>
                </div>
                <div class="env-arrow">→</div>
                <div class="env-to">
                    <span class="env-label">To</span>
                    <div class="env-avatar to-avatar">{{ strtoupper(substr($letter->receiver->name ?? 'U', 0, 1)) }}</div>
                    <span class="env-name">{{ $letter->receiver->name ?? 'You' }}</span>
                </div>
            </div>
            <div class="env-date">{{ $letter->created_at->format('d M Y') }}</div>
        </div>

        {{-- Letter Paper --}}
        <div class="letter-paper">
            {{-- Decorative lines --}}
            <div class="paper-lines"></div>

            <div class="letter-content">
                <h1 class="letter-title">{{ $letter->title }}</h1>

                <div class="unlock-badge">
                    @switch($letter->unlock_condition)
                        @case('task_complete') ✅ Unlocked after completing a task @break
                        @case('goal_reached') 🎯 Unlocked after reaching a goal @break
                        @case('streak') 🔥 Unlocked after maintaining a streak @break
                        @case('date') 📅 Unlocked on {{ $letter->unlock_date ? \Carbon\Carbon::parse($letter->unlock_date)->format('d M Y') : 'a special date' }} @break
                    @endswitch
                </div>

                <div class="message-body">
                    {!! nl2br(e($letter->message)) !!}
                </div>

                <div class="letter-signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        With love, <strong>{{ $letter->sender->name }}</strong> 💕
                    </div>
                    <div class="signature-date">{{ $letter->created_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="letter-footer">
            <a href="{{ route('letters.index') }}" class="btn-secondary">← All Letters</a>

            @if($letter->sender_id === $user->id)
                <form action="{{ route('letters.destroy', $letter) }}" method="POST" onsubmit="return confirm('Delete this letter permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">🗑️ Delete Letter</button>
                </form>
            @endif
        </div>
    </div>

</div>

<style>
.show-page {
    padding: 2rem;
    max-width: 760px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 1.5rem;
}

.btn-back {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border-radius: 10px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
    display: inline-block;
}

.btn-back:hover {
    background: #ede9fe;
    color: #7c3aed;
}

.letter-wrapper {
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Envelope Header */
.envelope-header {
    background: linear-gradient(135deg, #fff9c4, #fff59d);
    border: 1.5px solid #ffe082;
    border-bottom: none;
    border-radius: 20px 20px 0 0;
    padding: 1.75rem 2rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    position: relative;
}

.envelope-header::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ffe082, #ffd54f, #ffe082);
}

.wax-seal {
    font-size: 2.5rem;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.1));
    flex-shrink: 0;
}

.envelope-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    justify-content: center;
}

.env-from, .env-to {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.env-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #a16207;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.env-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    color: white;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.to-avatar {
    background: linear-gradient(135deg, #ec4899, #f97316);
}

.env-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #78350f;
}

.env-arrow {
    font-size: 1.2rem;
    color: #f59e0b;
}

.env-date {
    font-size: 0.8rem;
    color: #a16207;
    font-style: italic;
    flex-shrink: 0;
}

/* Letter Paper */
.letter-paper {
    background: white;
    border: 1.5px solid #ffe082;
    border-top: none;
    border-bottom: none;
    position: relative;
    padding: 2.5rem 2.5rem 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.paper-lines {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: repeating-linear-gradient(
        transparent,
        transparent 31px,
        #f0f4ff 31px,
        #f0f4ff 32px
    );
    opacity: 0.5;
    pointer-events: none;
}

.letter-content {
    position: relative;
    z-index: 1;
}

.letter-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0 0 1rem;
    line-height: 1.3;
}

.unlock-badge {
    display: inline-block;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    color: #2e7d32;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.35rem 0.9rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    border: 1px solid #a5d6a7;
}

.message-body {
    font-size: 1rem;
    line-height: 1.85;
    color: #374151;
    white-space: pre-wrap;
    min-height: 200px;
    font-family: 'Georgia', serif;
}

.letter-signature {
    margin-top: 2.5rem;
    padding-top: 1.5rem;
}

.signature-line {
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, #ec4899, #f97316);
    margin-bottom: 0.75rem;
    border-radius: 2px;
}

.signature-name {
    font-size: 1rem;
    color: #ec4899;
    font-style: italic;
    font-family: 'Georgia', serif;
    margin-bottom: 0.25rem;
}

.signature-date {
    font-size: 0.78rem;
    color: #9ca3af;
}

/* Footer */
.letter-footer {
    background: linear-gradient(135deg, #fff9c4, #fff59d);
    border: 1.5px solid #ffe082;
    border-top: none;
    border-radius: 0 0 20px 20px;
    padding: 1.25rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.btn-secondary {
    padding: 0.6rem 1.25rem;
    background: white;
    border-radius: 10px;
    text-decoration: none;
    color: #7c3aed;
    font-weight: 600;
    font-size: 0.9rem;
    border: 1.5px solid #ede9fe;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #ede9fe;
}

.btn-danger {
    padding: 0.6rem 1.25rem;
    background: #fef2f2;
    color: #ef4444;
    border: 1.5px solid #fecaca;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}
</style>
@endsection