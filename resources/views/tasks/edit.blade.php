@extends('layouts.app')
@section('title', 'Edit Task')

@section('content')
<div style="max-width:640px;margin:0 auto">
    <div class="topbar">
        <div>
            <h1 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:400;margin:0">✏️ Edit Task</h1>
        </div>
        <a href="{{ route('tasks.index', ['type' => $task->type]) }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf @method('PUT')

            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Task Title *</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit">
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Description</label>
                <textarea name="description" rows="3"
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;resize:vertical">{{ old('description', $task->description) }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Type</label>
                    <select name="type" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
                        <option value="personal" {{ old('type', $task->type) === 'personal' ? 'selected' : '' }}>👤 Personal</option>
                        @if($user->hasPartner())
                        <option value="shared" {{ old('type', $task->type) === 'shared' ? 'selected' : '' }}>🤝 Shared</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Priority</label>
                    <select name="priority" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>🔴 High</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>🟢 Low</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Status</label>
                    <select name="status" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
                        <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>🕐 Pending</option>
                        <option value="on_progress" {{ old('status', $task->status) === 'on_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>✅ Done</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Progress: <span id="prog-val">{{ old('progress', $task->progress) }}%</span></label>
                    <input type="range" name="progress" id="progress-range" min="0" max="100" value="{{ old('progress', $task->progress) }}"
                        style="width:100%;accent-color:#8b5cf6" oninput="document.getElementById('prog-val').textContent=this.value+'%'">
                </div>
            </div>

            <div style="margin-bottom:28px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Deadline</label>
                <input type="date" name="deadline" value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}"
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Save Changes</button>
                <a href="{{ route('tasks.index', ['type' => $task->type]) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection