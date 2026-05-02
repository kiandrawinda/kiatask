@extends('layouts.app')

@section('title', 'Secret Letters')

@section('content')
<div class="letters-page">

    {{-- Header --}}
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">💌</div>
            <div>
                <h1 class="page-title">Secret Letters</h1>
                <p class="page-subtitle">Love notes that unlock when missions are complete</p>
            </div>
        </div>
        <a href="{{ route('letters.create') }}" class="btn-primary">
            ✉️ Write a Letter
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="tab-group">
        <button class="tab-btn active" onclick="switchTab('received', this)">
            📬 Received <span class="tab-badge">{{ $received->count() }}</span>
        </button>
        <button class="tab-btn" onclick="switchTab('sent', this)">
            📤 Sent <span class="tab-badge">{{ $sent->count() }}</span>
        </button>
    </div>

    {{-- Received Letters --}}
    <div id="tab-received" class="tab-content">
        @if($received->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No letters yet</h3>
                <p>Your partner hasn't sent you any secret letters yet. Maybe hint them? 😉</p>
            </div>
        @else
            <div class="letters-grid">
                @foreach($received as $letter)
                    <div class="letter-card {{ $letter->is_unlocked ? 'unlocked' : 'locked' }}">
                        <div class="letter-ribbon">
                            @if($letter->is_unlocked)
                                <span class="ribbon-open">💌 Opened</span>
                            @else
                                <span class="ribbon-locked">🔒 Locked</span>
                            @endif
                        </div>

                        <div class="letter-header">
                            <div class="sender-info">
                                <div class="avatar">{{ strtoupper(substr($letter->sender->name ?? 'U', 0, 1)) }}</div>
                                <div>
                                    <div class="sender-name">From {{ $letter->sender->name ?? 'Someone' }}</div>
                                    <div class="letter-date">{{ $letter->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="letter-body">
                            <h3 class="letter-title">
                                @if($letter->is_unlocked)
                                    {{ $letter->title }}
                                @else
                                    ??? Secret Letter ???
                                @endif
                            </h3>
                            @if($letter->is_unlocked)
                                <p class="letter-preview">{{ Str::limit($letter->message, 80) }}</p>
                            @else
                                <p class="letter-preview locked-hint">Complete your mission to unlock this letter 🗝️</p>
                            @endif
                        </div>

                        <div class="letter-condition">
                            <span class="condition-chip">
                                @switch($letter->unlock_condition)
                                    @case('task_complete') ✅ Complete a task @break
                                    @case('goal_reached') 🎯 Reach a goal @break
                                    @case('streak') 🔥 Maintain streak @break
                                    @case('date') 📅 Unlock on date @break
                                @endswitch
                            </span>
                        </div>

                        <div class="letter-actions">
                            @if($letter->is_unlocked)
                                <a href="{{ route('letters.show', $letter) }}" class="btn-read">Read Letter 💌</a>
                            @else
                                <button class="btn-locked" disabled>🔒 Still Locked</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Sent Letters --}}
    <div id="tab-sent" class="tab-content hidden">
        @if($sent->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No letters sent</h3>
                <p>Write your first secret letter to your partner!</p>
                <a href="{{ route('letters.create') }}" class="btn-primary mt-4">✉️ Write Now</a>
            </div>
        @else
            <div class="letters-grid">
                @foreach($sent as $letter)
                    <div class="letter-card sent-card {{ $letter->is_unlocked ? 'unlocked' : '' }}">
                        <div class="letter-ribbon">
                            @if($letter->is_unlocked)
                                <span class="ribbon-open">💌 Opened by them</span>
                            @else
                                <span class="ribbon-pending">⏳ Waiting</span>
                            @endif
                        </div>

                        <div class="letter-header">
                            <div class="sender-info">
                                <div class="avatar receiver-avatar">{{ strtoupper(substr($letter->receiver->name ?? 'P', 0, 1)) }}</div>
                                <div>
                                    <div class="sender-name">To {{ $letter->receiver->name ?? 'Partner' }}</div>
                                    <div class="letter-date">{{ $letter->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="letter-body">
                            <h3 class="letter-title">{{ $letter->title }}</h3>
                            <p class="letter-preview">{{ Str::limit($letter->message, 80) }}</p>
                        </div>

                        <div class="letter-condition">
                            <span class="condition-chip">
                                @switch($letter->unlock_condition)
                                    @case('task_complete') ✅ Complete a task @break
                                    @case('goal_reached') 🎯 Reach a goal @break
                                    @case('streak') 🔥 Maintain streak @break
                                    @case('date') 📅 Unlock on date @break
                                @endswitch
                            </span>
                        </div>

                        <div class="letter-actions">
                            <a href="{{ route('letters.show', $letter) }}" class="btn-view">View 👁️</a>
                            <form action="{{ route('letters.destroy', $letter) }}" method="POST" onsubmit="return confirm('Delete this letter?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<style>
.letters-page {
    padding: 2rem;
    max-width: 1100px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #fce4ec, #f8bbd0);
    border-radius: 16px;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(233,30,99,0.15);
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.page-subtitle {
    color: #888;
    margin: 0;
    font-size: 0.9rem;
}

.btn-primary {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    padding: 0.65rem 1.4rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(124,58,237,0.3);
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(124,58,237,0.4);
}

.mt-4 { margin-top: 1rem; }

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-weight: 500;
    border-left: 4px solid #28a745;
}

.tab-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    background: #f5f3ff;
    padding: 0.4rem;
    border-radius: 14px;
    width: fit-content;
}

.tab-btn {
    padding: 0.55rem 1.2rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    background: transparent;
    color: #6b7280;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab-btn.active {
    background: white;
    color: #7c3aed;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.tab-badge {
    background: #ede9fe;
    color: #7c3aed;
    border-radius: 20px;
    padding: 0.1rem 0.5rem;
    font-size: 0.75rem;
}

.tab-btn.active .tab-badge {
    background: #7c3aed;
    color: white;
}

.tab-content { display: block; }
.tab-content.hidden { display: none; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem;
}

.empty-state p {
    font-size: 0.95rem;
    margin: 0;
}

.letters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

.letter-card {
    background: white;
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1.5px solid #f3f4f6;
    position: relative;
    transition: all 0.25s;
    overflow: hidden;
}

.letter-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #fce4ec, #f48fb1);
}

.letter-card.unlocked::before {
    background: linear-gradient(90deg, #d4edda, #81c784);
}

.letter-card.sent-card::before {
    background: linear-gradient(90deg, #e8f4fd, #90caf9);
}

.letter-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.letter-card.locked {
    background: #fafafa;
}

.letter-ribbon {
    margin-bottom: 1rem;
}

.ribbon-locked, .ribbon-open, .ribbon-pending {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.7rem;
    border-radius: 20px;
}

.ribbon-locked {
    background: #fff3e0;
    color: #e65100;
}

.ribbon-open {
    background: #e8f5e9;
    color: #2e7d32;
}

.ribbon-pending {
    background: #e8f4fd;
    color: #1565c0;
}

.letter-header {
    margin-bottom: 1rem;
}

.sender-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    color: white;
    font-weight: 700;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.receiver-avatar {
    background: linear-gradient(135deg, #ec4899, #f97316);
}

.sender-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
}

.letter-date {
    font-size: 0.78rem;
    color: #9ca3af;
}

.letter-body {
    margin-bottom: 1rem;
}

.letter-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.4rem;
}

.letter-card.locked .letter-title {
    color: #9ca3af;
    font-style: italic;
}

.letter-preview {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.locked-hint {
    color: #d1d5db;
    font-style: italic;
}

.letter-condition {
    margin-bottom: 1rem;
}

.condition-chip {
    display: inline-block;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.78rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 500;
}

.letter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-read, .btn-view {
    flex: 1;
    text-align: center;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-read {
    background: linear-gradient(135deg, #fce4ec, #f48fb1);
    color: #c2185b;
}

.btn-read:hover {
    background: linear-gradient(135deg, #f48fb1, #e91e63);
    color: white;
}

.btn-view {
    background: #ede9fe;
    color: #7c3aed;
}

.btn-view:hover {
    background: #7c3aed;
    color: white;
}

.btn-locked {
    flex: 1;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    border: none;
    background: #f3f4f6;
    color: #d1d5db;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: not-allowed;
}

.btn-delete {
    padding: 0.55rem 0.75rem;
    border-radius: 10px;
    border: none;
    background: #fef2f2;
    color: #ef4444;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.btn-delete:hover {
    background: #ef4444;
    color: white;
}
</style>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    btn.classList.add('active');
}
</script>
@endsection