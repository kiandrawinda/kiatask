@extends('layouts.app')

@section('title', 'Special Dates')

@section('content')
<div class="dates-page">

    {{-- Header --}}
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">📅</div>
            <div>
                <h1 class="page-title">Special Dates</h1>
                <p class="page-subtitle">Every milestone, every anniversary — never forget what matters 💑</p>
            </div>
        </div>
        <button class="btn-primary" onclick="toggleModal(true)">
            + Add Date
        </button>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- Upcoming Highlight --}}
    @php
        $next = $dates->first();
    @endphp
    @if($next)
        <div class="next-date-banner">
            <div class="banner-left">
                <div class="banner-emoji">{{ $next->emoji ?? '💑' }}</div>
                <div>
                    <div class="banner-label">Next Special Date</div>
                    <div class="banner-title">{{ $next->title }}</div>
                    <div class="banner-date">{{ \Carbon\Carbon::parse($next->date)->format('d F Y') }}</div>
                </div>
            </div>
            <div class="countdown-badge">
                @if($next->days_until == 0)
                    <span class="days-num">🎉</span>
                    <span class="days-label">Today!</span>
                @elseif($next->days_until == 1)
                    <span class="days-num">1</span>
                    <span class="days-label">day away</span>
                @else
                    <span class="days-num">{{ $next->days_until }}</span>
                    <span class="days-label">days away</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Dates Grid --}}
    @if($dates->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">🗓️</div>
            <h3>No special dates yet</h3>
            <p>Add your anniversaries, birthdays, and meaningful milestones!</p>
            <button class="btn-primary mt-4" onclick="toggleModal(true)">+ Add Your First Date</button>
        </div>
    @else
        <div class="dates-grid">
            @foreach($dates as $date)
                @php
                    $daysUntil = $date->days_until ?? 999;
                    $urgency = $daysUntil <= 7 ? 'urgent' : ($daysUntil <= 30 ? 'soon' : 'normal');
                    $parsedDate = \Carbon\Carbon::parse($date->date);
                @endphp
                <div class="date-card {{ $urgency }}">
                    <div class="card-top">
                        <div class="date-emoji">{{ $date->emoji ?? '💑' }}</div>
                        <div class="date-countdown {{ $urgency }}">
                            @if($daysUntil == 0)
                                🎉 Today!
                            @elseif($daysUntil == 1)
                                Tomorrow!
                            @else
                                {{ $daysUntil }}d
                            @endif
                        </div>
                    </div>

                    <div class="date-info">
                        <h3 class="date-title">{{ $date->title }}</h3>
                        @if($date->description)
                            <p class="date-desc">{{ $date->description }}</p>
                        @endif
                        <div class="date-meta">
                            <span class="date-val">{{ $parsedDate->format('d M Y') }}</span>
                            @if($date->is_recurring)
                                <span class="recurring-badge">🔁 Yearly</span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar until date --}}
                    @if($daysUntil <= 365 && $daysUntil > 0)
                        <div class="progress-wrap">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ max(5, 100 - ($daysUntil / 365 * 100)) }}%"></div>
                            </div>
                            <span class="progress-label">{{ $daysUntil }} days to go</span>
                        </div>
                    @endif

                    <div class="card-footer">
                        <form action="{{ route('dates.destroy', $date) }}" method="POST" onsubmit="return confirm('Remove this date?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete" title="Remove">🗑️ Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Add Date Modal --}}
<div id="modal-overlay" class="modal-overlay hidden" onclick="handleOverlayClick(event)">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <span class="modal-icon">📅</span>
                <h2 class="modal-title">Add Special Date</h2>
            </div>
            <button class="modal-close" onclick="toggleModal(false)">✕</button>
        </div>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('dates.store') }}" method="POST">
            @csrf

            {{-- Emoji + Title --}}
            <div class="form-row">
                <div class="form-group emoji-group">
                    <label class="form-label">Emoji</label>
                    <input type="text" name="emoji" class="form-input emoji-input" value="{{ old('emoji', '💑') }}" maxlength="4">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Our Anniversary" value="{{ old('title') }}" required>
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="2" placeholder="What makes this day special?">{{ old('description') }}</textarea>
            </div>

            {{-- Date --}}
            <div class="form-group">
                <label class="form-label">Date <span class="required">*</span></label>
                <input type="date" name="date" class="form-input" value="{{ old('date') }}" required>
            </div>

            {{-- Recurring --}}
            <div class="form-group">
                <label class="toggle-label">
                    <div class="toggle-wrap">
                        <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring', true) ? 'checked' : '' }} id="is_recurring">
                        <span class="toggle-slider"></span>
                    </div>
                    <span class="toggle-text">
                        <span class="toggle-title">🔁 Recurring yearly</span>
                        <span class="toggle-sub">Celebrate this date every year automatically</span>
                    </span>
                </label>
            </div>

            {{-- Emoji Presets --}}
            <div class="form-group">
                <label class="form-label">Quick Emojis</label>
                <div class="emoji-presets">
                    @foreach(['💑','💍','🎂','🎉','🥂','✈️','🏠','👶','🐾','🎓'] as $e)
                        <button type="button" class="emoji-preset" onclick="setEmoji('{{ $e }}')">{{ $e }}</button>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-submit">📅 Save Date</button>
        </form>
    </div>
</div>

<style>
.dates-page {
    padding: 2rem;
    max-width: 1100px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.75rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    border-radius: 16px;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(14,165,233,0.15);
    flex-shrink: 0;
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
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(124,58,237,0.3);
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(124,58,237,0.4);
}

.mt-4 { margin-top: 1rem; display: inline-block; }

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-weight: 500;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    margin-bottom: 1rem;
}

/* Next Date Banner */
.next-date-banner {
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    border-radius: 18px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    box-shadow: 0 8px 24px rgba(124,58,237,0.3);
    gap: 1rem;
}

.banner-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.banner-emoji {
    font-size: 3rem;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.2));
}

.banner-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.8;
    margin-bottom: 0.25rem;
}

.banner-title {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1.2;
}

.banner-date {
    font-size: 0.9rem;
    opacity: 0.85;
    margin-top: 0.2rem;
}

.countdown-badge {
    background: rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 1rem 1.5rem;
    text-align: center;
    flex-shrink: 0;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.25);
}

.days-num {
    display: block;
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
}

.days-label {
    display: block;
    font-size: 0.75rem;
    opacity: 0.9;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Dates Grid */
.dates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.25rem;
}

.date-card {
    background: white;
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1.5px solid #f3f4f6;
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
}

.date-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #c4b5fd, #a78bfa);
}

.date-card.urgent::before {
    background: linear-gradient(90deg, #fca5a5, #f87171);
}

.date-card.soon::before {
    background: linear-gradient(90deg, #fcd34d, #fbbf24);
}

.date-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.date-emoji {
    font-size: 2.5rem;
    line-height: 1;
}

.date-countdown {
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    background: #f3f4f6;
    color: #6b7280;
}

.date-countdown.urgent {
    background: #fef2f2;
    color: #ef4444;
}

.date-countdown.soon {
    background: #fffbeb;
    color: #d97706;
}

.date-info {
    margin-bottom: 1rem;
}

.date-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.3rem;
    line-height: 1.3;
}

.date-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0 0 0.6rem;
    line-height: 1.4;
}

.date-meta {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.date-val {
    font-size: 0.83rem;
    color: #6b7280;
    font-weight: 500;
}

.recurring-badge {
    font-size: 0.72rem;
    background: #ede9fe;
    color: #7c3aed;
    padding: 0.15rem 0.6rem;
    border-radius: 20px;
    font-weight: 600;
}

/* Progress */
.progress-wrap {
    margin-bottom: 1rem;
}

.progress-bar {
    height: 5px;
    background: #f3f4f6;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.3rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #7c3aed, #ec4899);
    border-radius: 10px;
    transition: width 0.5s ease;
}

.progress-label {
    font-size: 0.72rem;
    color: #9ca3af;
}

.card-footer {
    border-top: 1px solid #f3f4f6;
    padding-top: 0.75rem;
}

.btn-delete {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 0.8rem;
    cursor: pointer;
    padding: 0.3rem 0.6rem;
    border-radius: 8px;
    transition: all 0.2s;
    font-weight: 500;
}

.btn-delete:hover {
    background: #fef2f2;
    color: #ef4444;
}

/* Empty */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-icon { font-size: 4rem; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.25rem; font-weight: 600; color: #374151; margin: 0 0 0.5rem; }
.empty-state p { font-size: 0.95rem; margin: 0; }

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease;
}

.modal-overlay.hidden { display: none; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal {
    background: white;
    border-radius: 22px;
    padding: 2rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: slideUp 0.25s ease;
    max-height: 90vh;
    overflow-y: auto;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.modal-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-icon {
    font-size: 1.75rem;
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    border-radius: 12px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #fef2f2;
    color: #ef4444;
}

/* Form Styles */
.form-row {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}

.emoji-group { flex-shrink: 0; width: 80px; }

.form-group { margin-bottom: 1.25rem; }

.form-label {
    display: block;
    font-weight: 600;
    font-size: 0.88rem;
    color: #374151;
    margin-bottom: 0.4rem;
}

.required { color: #ef4444; }

.form-input, .form-textarea {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #374151;
    background: #fafafa;
    transition: all 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}

.emoji-input {
    text-align: center;
    font-size: 1.3rem;
    padding: 0.55rem 0.25rem;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #7c3aed;
    background: white;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.08);
}

.form-textarea {
    resize: vertical;
    min-height: 70px;
}

/* Toggle */
.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    cursor: pointer;
}

.toggle-wrap {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.toggle-wrap input { display: none; }

.toggle-slider {
    position: absolute;
    inset: 0;
    background: #e5e7eb;
    border-radius: 24px;
    transition: 0.3s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: 0.3s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.toggle-wrap input:checked + .toggle-slider {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.toggle-wrap input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.toggle-text { flex: 1; }

.toggle-title {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
}

.toggle-sub {
    display: block;
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 0.1rem;
}

/* Emoji Presets */
.emoji-presets {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.emoji-preset {
    width: 38px;
    height: 38px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #fafafa;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.emoji-preset:hover {
    border-color: #7c3aed;
    background: #f5f3ff;
    transform: scale(1.1);
}

.btn-submit {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(124,58,237,0.3);
    transition: all 0.2s;
    margin-top: 0.5rem;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(124,58,237,0.4);
}
</style>

<script>
function toggleModal(show) {
    const overlay = document.getElementById('modal-overlay');
    overlay.classList.toggle('hidden', !show);
    if (show) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
}

function handleOverlayClick(e) {
    if (e.target === document.getElementById('modal-overlay')) toggleModal(false);
}

function setEmoji(emoji) {
    document.querySelector('input[name="emoji"]').value = emoji;
}

// Auto-open modal if there are validation errors
@if($errors->any())
    document.addEventListener('DOMContentLoaded', () => toggleModal(true));
@endif
</script>
@endsection