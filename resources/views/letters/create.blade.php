@extends('layouts.app')

@section('title', 'Write Secret Letter')

@section('content')
<div class="create-page">

    {{-- Header --}}
    <div class="page-header">
        <a href="{{ route('letters.index') }}" class="btn-back">← Back</a>
        <div class="header-center">
            <div class="page-icon">✉️</div>
            <div>
                <h1 class="page-title">Write a Secret Letter</h1>
                <p class="page-subtitle">It'll unlock when your partner completes their mission 🗝️</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-layout">
        {{-- Letter Form --}}
        <div class="form-card">
            <form action="{{ route('letters.store') }}" method="POST">
                @csrf

                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label">Letter Title <span class="required">*</span></label>
                    <input
                        type="text"
                        name="title"
                        class="form-input"
                        placeholder="e.g. A letter for when you finish the marathon 🏅"
                        value="{{ old('title') }}"
                        required
                    >
                </div>

                {{-- Message --}}
                <div class="form-group">
                    <label class="form-label">Your Message <span class="required">*</span></label>
                    <textarea
                        name="message"
                        class="form-textarea"
                        rows="7"
                        placeholder="Pour your heart out... This message is locked until they complete their mission 💝"
                        required
                    >{{ old('message') }}</textarea>
                    <div class="char-hint">Be as sweet or silly as you like — they'll only read it after they earn it!</div>
                </div>

                {{-- Unlock Condition --}}
                <div class="form-group">
                    <label class="form-label">Unlock Condition <span class="required">*</span></label>
                    <div class="condition-grid">
                        <label class="condition-option {{ old('unlock_condition') == 'task_complete' ? 'selected' : '' }}">
                            <input type="radio" name="unlock_condition" value="task_complete" {{ old('unlock_condition') == 'task_complete' ? 'checked' : '' }} onchange="showConditionFields(this.value)">
                            <span class="condition-icon">✅</span>
                            <span class="condition-label">Complete a Task</span>
                        </label>
                        <label class="condition-option {{ old('unlock_condition') == 'goal_reached' ? 'selected' : '' }}">
                            <input type="radio" name="unlock_condition" value="goal_reached" {{ old('unlock_condition') == 'goal_reached' ? 'checked' : '' }} onchange="showConditionFields(this.value)">
                            <span class="condition-icon">🎯</span>
                            <span class="condition-label">Reach a Goal</span>
                        </label>
                        <label class="condition-option {{ old('unlock_condition') == 'streak' ? 'selected' : '' }}">
                            <input type="radio" name="unlock_condition" value="streak" {{ old('unlock_condition') == 'streak' ? 'checked' : '' }} onchange="showConditionFields(this.value)">
                            <span class="condition-icon">🔥</span>
                            <span class="condition-label">Maintain Streak</span>
                        </label>
                        <label class="condition-option {{ old('unlock_condition') == 'date' ? 'selected' : '' }}">
                            <input type="radio" name="unlock_condition" value="date" {{ old('unlock_condition') == 'date' ? 'checked' : '' }} onchange="showConditionFields(this.value)">
                            <span class="condition-icon">📅</span>
                            <span class="condition-label">On a Date</span>
                        </label>
                    </div>
                </div>

                {{-- Task Select --}}
                <div id="field-task" class="form-group sub-field hidden">
                    <label class="form-label">Select Task</label>
                    <select name="unlock_ref_id" class="form-select">
                        <option value="">— Pick a task —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}" {{ old('unlock_ref_id') == $task->id ? 'selected' : '' }}>
                                {{ $task->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Goal Select --}}
                <div id="field-goal" class="form-group sub-field hidden">
                    <label class="form-label">Select Goal</label>
                    <select name="unlock_ref_id" class="form-select">
                        <option value="">— Pick a goal —</option>
                        @foreach($goals as $goal)
                            <option value="{{ $goal->id }}" {{ old('unlock_ref_id') == $goal->id ? 'selected' : '' }}>
                                {{ $goal->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Streak Count --}}
                <div id="field-streak" class="form-group sub-field hidden">
                    <label class="form-label">Streak Count (days)</label>
                    <input type="number" name="unlock_streak_count" class="form-input" min="1" placeholder="e.g. 7" value="{{ old('unlock_streak_count') }}">
                </div>

                {{-- Unlock Date --}}
                <div id="field-date" class="form-group sub-field hidden">
                    <label class="form-label">Unlock Date</label>
                    <input type="date" name="unlock_date" class="form-input" value="{{ old('unlock_date') }}">
                </div>

                <button type="submit" class="btn-submit">
                    🚀 Send Secret Letter
                </button>
            </form>
        </div>

        {{-- Preview Card --}}
        <div class="preview-card">
            <div class="preview-label">Preview</div>
            <div class="envelope-wrap">
                <div class="envelope">
                    <div class="envelope-flap"></div>
                    <div class="envelope-body">
                        <div class="letter-preview-inner">
                            <div class="preview-heart">💌</div>
                            <p class="preview-to">To: <strong>{{ $user->partner->name ?? 'Your Partner' }}</strong></p>
                            <p class="preview-from">From: <strong>{{ $user->name }}</strong></p>
                            <div class="preview-lock">🔒 Locked until mission complete</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="preview-tips">
                <div class="tip-title">💡 Tips</div>
                <ul class="tip-list">
                    <li>Write from the heart — they'll read it at a special moment</li>
                    <li>Choose a condition that's meaningful to both of you</li>
                    <li>You can always write multiple letters for different milestones</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<style>
.create-page {
    padding: 2rem;
    max-width: 1100px;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.header-center {
    display: flex;
    align-items: center;
    gap: 1rem;
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
    white-space: nowrap;
}

.btn-back:hover {
    background: #ede9fe;
    color: #7c3aed;
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

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #ef4444;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.form-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .form-layout { grid-template-columns: 1fr; }
}

.form-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    border: 1.5px solid #f3f4f6;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required { color: #ef4444; }

.form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #374151;
    background: #fafafa;
    transition: all 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
    outline: none;
    border-color: #7c3aed;
    background: white;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.08);
}

.form-textarea {
    resize: vertical;
    min-height: 160px;
    line-height: 1.6;
}

.char-hint {
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 0.4rem;
    font-style: italic;
}

.condition-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.condition-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 1rem 0.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    background: #fafafa;
}

.condition-option:hover {
    border-color: #c4b5fd;
    background: #f5f3ff;
}

.condition-option input[type="radio"] { display: none; }

.condition-option.selected,
.condition-option:has(input:checked) {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
}

.condition-icon { font-size: 1.5rem; }

.condition-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
}

.sub-field {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1rem;
    border: 1.5px dashed #e5e7eb;
    margin-top: -0.5rem;
}

.sub-field.hidden { display: none; }

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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(124,58,237,0.4);
}

/* Preview */
.preview-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    border: 1.5px solid #f3f4f6;
    position: sticky;
    top: 2rem;
}

.preview-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1.25rem;
}

.envelope-wrap {
    margin-bottom: 1.5rem;
}

.envelope {
    background: linear-gradient(135deg, #fff9c4, #fff59d);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(255,193,7,0.25);
    border: 1.5px solid #ffe082;
}

.envelope-flap {
    height: 60px;
    background: linear-gradient(135deg, #ffe082, #ffd54f);
    clip-path: polygon(0 0, 100% 0, 50% 100%);
    margin-bottom: -1px;
}

.envelope-body {
    padding: 1.25rem;
}

.letter-preview-inner {
    text-align: center;
}

.preview-heart {
    font-size: 2rem;
    margin-bottom: 0.75rem;
}

.preview-to, .preview-from {
    font-size: 0.85rem;
    color: #78350f;
    margin: 0.2rem 0;
}

.preview-lock {
    margin-top: 1rem;
    font-size: 0.78rem;
    color: #9ca3af;
    background: #f3f4f6;
    border-radius: 20px;
    padding: 0.3rem 0.75rem;
    display: inline-block;
}

.preview-tips {
    background: #f5f3ff;
    border-radius: 12px;
    padding: 1rem;
}

.tip-title {
    font-weight: 700;
    font-size: 0.85rem;
    color: #7c3aed;
    margin-bottom: 0.6rem;
}

.tip-list {
    padding-left: 1.1rem;
    margin: 0;
}

.tip-list li {
    font-size: 0.8rem;
    color: #6b7280;
    margin-bottom: 0.4rem;
    line-height: 1.4;
}
</style>

<script>
function showConditionFields(value) {
    // Hide all
    document.querySelectorAll('.sub-field').forEach(el => el.classList.add('hidden'));
    // Update selected style
    document.querySelectorAll('.condition-option').forEach(el => el.classList.remove('selected'));

    // Show relevant field
    const map = {
        'task_complete': 'field-task',
        'goal_reached': 'field-goal',
        'streak': 'field-streak',
        'date': 'field-date',
    };

    if (map[value]) {
        document.getElementById(map[value]).classList.remove('hidden');
    }

    // Mark selected
    const checked = document.querySelector(`input[name="unlock_condition"]:checked`);
    if (checked) checked.closest('.condition-option').classList.add('selected');
}

// On load, restore state
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="unlock_condition"]:checked');
    if (checked) showConditionFields(checked.value);

    // Listen for changes on radio
    document.querySelectorAll('input[name="unlock_condition"]').forEach(radio => {
        radio.addEventListener('change', () => showConditionFields(radio.value));
    });
});
</script>
@endsection