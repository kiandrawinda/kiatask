@extends('layouts.app')

@section('title', 'Focus Session')

@section('content')
<div class="focus-page">

    {{-- Header --}}
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">🍅</div>
            <div>
                <h1 class="page-title">Focus Session</h1>
                <p class="page-subtitle">Stay in the zone together — deep work, side by side 💪</p>
            </div>
        </div>
        @if($activeSession)
            <div class="status-live">
                <span class="live-dot"></span> Session Active
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="focus-layout">

        {{-- Timer Card --}}
        <div class="timer-card">
            {{-- Duration Selector --}}
            <div id="setup-panel" class="{{ $activeSession ? 'hidden' : '' }}">
                <div class="setup-title">Choose Focus Duration</div>
                <div class="preset-row">
                    @foreach([15, 25, 45, 60] as $min)
                        <button class="preset-btn {{ $min == 25 ? 'active' : '' }}" onclick="setDuration({{ $min }}, this)">
                            {{ $min }}<span class="preset-unit">m</span>
                        </button>
                    @endforeach
                </div>
                <div class="custom-row">
                    <input type="number" id="customDuration" class="custom-input" min="1" max="120" placeholder="Custom (1–120 min)">
                </div>
                <div class="note-group">
                    <label class="note-label">Focus note (optional)</label>
                    <input type="text" id="focusNote" class="note-input" placeholder="What are you working on?">
                </div>
                <button class="btn-start" onclick="startSession()">
                    🍅 Start Focus Session
                </button>
            </div>

            {{-- Active Timer --}}
            <div id="timer-panel" class="{{ $activeSession ? '' : 'hidden' }}">
                <div class="timer-ring-wrap">
                    <svg class="timer-ring" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="88" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                        <circle id="ring-progress" cx="100" cy="100" r="88" fill="none"
                            stroke="url(#timerGrad)" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="553"
                            stroke-dashoffset="0"
                            transform="rotate(-90 100 100)"/>
                        <defs>
                            <linearGradient id="timerGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#7c3aed"/>
                                <stop offset="100%" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="timer-center">
                        <div id="timer-display" class="timer-display">25:00</div>
                        <div id="timer-status" class="timer-status">Focusing...</div>
                    </div>
                </div>

                @if($activeSession)
                    <div class="session-meta">
                        <span>🍅 {{ $activeSession->duration_minutes }} min session</span>
                        <span>Started {{ \Carbon\Carbon::parse($activeSession->started_at)->format('H:i') }}</span>
                        @if($activeSession->note)
                            <span>📝 {{ $activeSession->note }}</span>
                        @endif
                    </div>
                @endif

                <button class="btn-stop" onclick="stopSession()">
                    ⏹ End Session
                </button>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="right-panel">

            {{-- Partner Status --}}
            <div class="partner-card">
                <div class="partner-header">
                    <div class="partner-title">👫 Partner Status</div>
                </div>
                @if($user->partner)
                    <div class="partner-body" id="partner-status">
                        <div class="partner-avatar">{{ strtoupper(substr($user->partner->name, 0, 1)) }}</div>
                        <div class="partner-info">
                            <div class="partner-name">{{ $user->partner->name }}</div>
                            @if($partnerSession)
                                <div class="partner-active">
                                    <span class="live-dot small"></span>
                                    Focusing — {{ $partnerSession->duration_minutes }}min session
                                </div>
                                <div id="partner-timer" class="partner-timer">—</div>
                            @else
                                <div class="partner-idle">Not in a session right now</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="no-partner">
                        <div class="no-partner-icon">💔</div>
                        <p>No partner connected yet</p>
                    </div>
                @endif
            </div>

            {{-- Recent Sessions --}}
            <div class="history-card">
                <div class="history-header">🕘 Recent Sessions</div>
                @if($recentSessions->isEmpty())
                    <div class="no-history">No sessions yet — start your first one! 🍅</div>
                @else
                    <div class="history-list">
                        @foreach($recentSessions as $session)
                            @php
                                $statusColor = match($session->status) {
                                    'completed' => 'green',
                                    'active'    => 'purple',
                                    default     => 'gray',
                                };
                                $duration = $session->ended_at
                                    ? \Carbon\Carbon::parse($session->started_at)->diffInMinutes($session->ended_at)
                                    : $session->duration_minutes;
                            @endphp
                            <div class="history-item">
                                <div class="history-icon {{ $statusColor }}">🍅</div>
                                <div class="history-info">
                                    <div class="history-title">{{ $session->note ?: 'Focus Session' }}</div>
                                    <div class="history-meta">
                                        {{ \Carbon\Carbon::parse($session->started_at)->format('d M, H:i') }}
                                        · {{ $duration }}min
                                    </div>
                                </div>
                                <div class="history-badge {{ $statusColor }}">{{ ucfirst($session->status) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<style>
/* =====================
   BASE
   ===================== */
.focus-page {
    padding: 2rem;
    max-width: 1000px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.75rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.header-left { display: flex; align-items: center; gap: 1rem; }

.page-icon {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #fce4ec, #f8bbd0);
    border-radius: 16px;
    width: 64px; height: 64px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(233,30,99,0.15);
    flex-shrink: 0;
}

.page-title { font-size: 1.8rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { color: #888; margin: 0; font-size: 0.9rem; }

.status-live {
    display: flex; align-items: center; gap: 0.5rem;
    background: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    border-radius: 20px;
    padding: 0.45rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #065f46;
    white-space: nowrap;
}

.live-dot {
    width: 9px; height: 9px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse-dot 1.5s infinite;
    flex-shrink: 0;
}
.live-dot.small { width: 7px; height: 7px; }

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-weight: 500;
    border-left: 4px solid #28a745;
}

/* Layout */
.focus-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
}

/* Timer Card */
.timer-card {
    background: white;
    border-radius: 24px;
    padding: 2.5rem 2rem;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border: 1.5px solid #f3f4f6;
    text-align: center;
}

.setup-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1.25rem;
}

.preset-row {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.preset-btn {
    width: 70px; height: 70px;
    border-radius: 18px;
    border: 2px solid #e5e7eb;
    background: #fafafa;
    font-size: 1.3rem;
    font-weight: 800;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.preset-unit { font-size: 0.7rem; font-weight: 500; margin-top: 2px; }
.preset-btn:hover, .preset-btn.active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
}

.custom-row { margin-bottom: 1.25rem; }

.custom-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #374151;
    background: #fafafa;
    box-sizing: border-box;
    text-align: center;
    transition: all 0.2s;
    font-family: inherit;
}
.custom-input:focus {
    outline: none;
    border-color: #7c3aed;
    background: white;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.08);
}

.note-group { margin-bottom: 1.5rem; text-align: left; }
.note-label { display: block; font-size: 0.82rem; font-weight: 600; color: #6b7280; margin-bottom: 0.4rem; }

.note-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #374151;
    background: #fafafa;
    box-sizing: border-box;
    transition: all 0.2s;
    font-family: inherit;
}
.note-input:focus {
    outline: none;
    border-color: #7c3aed;
    background: white;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.08);
}

.btn-start {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(124,58,237,0.35);
    transition: all 0.2s;
}
.btn-start:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(124,58,237,0.45); }
.btn-start:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

/* Timer Ring */
.timer-ring-wrap {
    position: relative;
    width: 200px; height: 200px;
    margin: 0 auto 1.5rem;
}
.timer-ring { width: 200px; height: 200px; }
.timer-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.timer-display {
    font-size: 2.8rem;
    font-weight: 900;
    color: #1f2937;
    font-variant-numeric: tabular-nums;
    letter-spacing: -1px;
}
.timer-status { font-size: 0.82rem; color: #9ca3af; font-weight: 600; margin-top: 0.25rem; }

.session-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    margin-bottom: 1.5rem;
}
.session-meta span {
    font-size: 0.78rem;
    background: #f5f3ff;
    color: #7c3aed;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 500;
}

.btn-stop {
    width: 100%;
    padding: 0.85rem;
    background: #fef2f2;
    color: #ef4444;
    border: 2px solid #fecaca;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-stop:hover { background: #ef4444; color: white; border-color: #ef4444; }

/* Right Panel */
.right-panel { display: flex; flex-direction: column; gap: 1.25rem; }

.partner-card, .history-card {
    background: white;
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1.5px solid #f3f4f6;
}

.partner-header { margin-bottom: 1.25rem; }
.partner-title, .history-header {
    font-size: 0.9rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 0;
}
.history-header { margin-bottom: 1rem; }

.partner-body { display: flex; align-items: center; gap: 1rem; }

.partner-avatar {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: white;
    font-weight: 800;
    font-size: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.partner-info { flex: 1; }
.partner-name { font-weight: 700; font-size: 0.95rem; color: #1f2937; margin-bottom: 0.2rem; }
.partner-active {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.8rem; color: #059669; font-weight: 600; margin-bottom: 0.2rem;
}
.partner-timer { font-size: 1.1rem; font-weight: 800; color: #ec4899; font-variant-numeric: tabular-nums; }
.partner-idle { font-size: 0.82rem; color: #9ca3af; font-style: italic; }

.no-partner { text-align: center; padding: 1rem; color: #9ca3af; }
.no-partner-icon { font-size: 2rem; margin-bottom: 0.5rem; }
.no-partner p { font-size: 0.85rem; margin: 0; }

.no-history { font-size: 0.85rem; color: #9ca3af; text-align: center; padding: 1rem; font-style: italic; }
.history-list { display: flex; flex-direction: column; gap: 0.6rem; }

.history-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f9fafb;
}
.history-item:last-child { border-bottom: none; }

.history-icon {
    font-size: 1.1rem;
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.history-icon.green { background: #ecfdf5; }
.history-icon.purple { background: #f5f3ff; }
.history-icon.gray { background: #f9fafb; }

.history-info { flex: 1; min-width: 0; }
.history-title { font-size: 0.85rem; font-weight: 600; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.history-meta { font-size: 0.75rem; color: #9ca3af; margin-top: 0.1rem; }

.history-badge { font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 20px; flex-shrink: 0; }
.history-badge.green { background: #ecfdf5; color: #059669; }
.history-badge.purple { background: #f5f3ff; color: #7c3aed; }
.history-badge.gray { background: #f9fafb; color: #6b7280; }

.hidden { display: none !important; }

/* =====================
   TABLET (max 860px)
   — right panel turun ke bawah
   ===================== */
@media (max-width: 860px) {
    .focus-layout {
        grid-template-columns: 1fr;
    }

    /* Di tablet, right panel tampil horizontal 2 kolom */
    .right-panel {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
}

/* =====================
   MOBILE (max 600px)
   ===================== */
@media (max-width: 600px) {
    .focus-page { padding: 1rem; }

    .page-header { margin-bottom: 1.25rem; }

    .page-icon { width: 52px; height: 52px; font-size: 2rem; border-radius: 14px; }
    .page-title { font-size: 1.4rem; }
    .page-subtitle { font-size: 0.82rem; }

    .timer-card { padding: 1.5rem 1rem; border-radius: 18px; }

    /* Preset buttons lebih kecil */
    .preset-btn { width: 60px; height: 60px; font-size: 1.1rem; border-radius: 14px; }

    /* Timer ring sedikit lebih kecil */
    .timer-ring-wrap { width: 170px; height: 170px; }
    .timer-ring      { width: 170px; height: 170px; }
    .timer-display   { font-size: 2.2rem; }

    /* Right panel: 1 kolom di mobile */
    .right-panel { grid-template-columns: 1fr; }

    .partner-card, .history-card { padding: 1.1rem; border-radius: 14px; }
}

/* =====================
   SMALL MOBILE (max 400px)
   ===================== */
@media (max-width: 400px) {
    .focus-page { padding: 0.75rem; }

    .page-title { font-size: 1.2rem; }

    .preset-btn { width: 54px; height: 54px; font-size: 1rem; }

    .timer-ring-wrap { width: 150px; height: 150px; }
    .timer-ring      { width: 150px; height: 150px; }
    .timer-display   { font-size: 1.9rem; }

    .btn-start { font-size: 0.95rem; padding: 0.85rem; }
}
</style>

<script>
let timerInterval = null;
let selectedDuration = 25;
let totalSeconds = 0;
let remainingSeconds = 0;
let isActive = {{ $activeSession ? 'true' : 'false' }};

@if($activeSession)
    totalSeconds     = {{ $activeSession->duration_minutes }} * 60;
    remainingSeconds = {{ $activeSession->remaining_seconds ?? ($activeSession->duration_minutes * 60) }};
    initTimer(remainingSeconds, totalSeconds);
@endif

function setDuration(min, btn) {
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedDuration = min;
    document.getElementById('customDuration').value = '';
}

async function startSession() {
    const customVal = document.getElementById('customDuration').value;
    const duration  = customVal ? parseInt(customVal) : selectedDuration;
    const note      = document.getElementById('focusNote').value;

    if (!duration || duration < 1 || duration > 120) {
        alert('Duration must be between 1 and 120 minutes.');
        return;
    }

    const btn = document.querySelector('.btn-start');
    btn.textContent = '⏳ Starting...';
    btn.disabled = true;

    try {
        const res = await fetch('{{ route("focus.start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',   // ← TAMBAH INI
            },
            body: JSON.stringify({ duration_minutes: duration, note }),
        });

        // ← TAMBAH BLOK INI — baca error asli dari server
        if (!res.ok) {
            let errMsg = `Server error ${res.status}`;
            try {
                const errData = await res.json();
                errMsg = errData.error || errData.message || errMsg;
            } catch (_) {}
            throw new Error(errMsg);
        }

        const data = await res.json();

        if (data.success) {
            totalSeconds     = duration * 60;
            remainingSeconds = duration * 60;
            document.getElementById('setup-panel').classList.add('hidden');
            document.getElementById('timer-panel').classList.remove('hidden');
            isActive = true;
            initTimer(remainingSeconds, totalSeconds);
        } else {
            throw new Error(data.message || data.error || 'Failed to start session.');
        }

    } catch (e) {
        console.error('Focus start error:', e);       // ← lihat di DevTools Console
        alert('Error: ' + e.message);                 // ← sekarang pesan aslinya muncul
        btn.textContent = '🍅 Start Focus Session';
        btn.disabled = false;
    }
}

async function stopSession(auto = false) {

    if (!auto) {
        if (!confirm('End this focus session?')) return;
    }

    clearInterval(timerInterval);

    try {
        await fetch('{{ route("focus.stop") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
    } catch (e) {}

    isActive = false;

    if (!auto) {
        location.reload();
    }
}

function initTimer(remaining, total) {

    remainingSeconds = remaining;
    totalSeconds     = total;

    updateTimerDisplay();

    clearInterval(timerInterval);

    timerInterval = setInterval(async () => {

        if (remainingSeconds <= 0) {

            clearInterval(timerInterval);

            document.getElementById('timer-display').textContent = '00:00';
            document.getElementById('timer-status').textContent  = '✅ Complete!';

            setRingProgress(0);

            await stopSession(true); // auto stop backend
            return;
        }

        remainingSeconds--;
        updateTimerDisplay();

    }, 1000);
}

function updateTimerDisplay() {

    const minutes = Math.floor(remainingSeconds / 60)
        .toString()
        .padStart(2, '0');

    const seconds = (remainingSeconds % 60)
        .toString()
        .padStart(2, '0');

    document.getElementById('timer-display').textContent = `${minutes}:${seconds}`;
    document.getElementById('timer-status').textContent  = 'Focusing... 🍅';

    setRingProgress(remainingSeconds);
}

function setRingProgress(remaining) {

    const radius = 88;
    const circumference = 2 * Math.PI * radius;

    const offset = circumference - (remaining / totalSeconds) * circumference;

    document
        .getElementById('ring-progress')
        .style.strokeDasharray  = circumference;

    document
        .getElementById('ring-progress')
        .style.strokeDashoffset = offset;
}

@if($user->partner)
setInterval(async () => {
    try {
        const res  = await fetch('{{ route("focus.status") }}');
        const data = await res.json();

        if (data.partner_session) {
            const sec = data.partner_session.remaining_seconds;
            const m   = Math.floor(sec / 60).toString().padStart(2, '0');
            const s   = (sec % 60).toString().padStart(2, '0');

            const el = document.getElementById('partner-timer');
            if (el) el.textContent = `${m}:${s} left`;
        }
    } catch (e) {}
}, 15000);
@endif
</script>
@endsection