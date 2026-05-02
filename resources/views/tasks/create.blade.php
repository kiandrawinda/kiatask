@extends('layouts.app')
@section('title', 'Create Task')

@section('content')
<div style="max-width:640px;margin:0 auto">
    <div class="topbar">
        <div>
            <h1 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:400;margin:0">✨ New Task</h1>
            <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.875rem">What do you want to achieve?</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card">
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px;color:var(--text-primary)">Task Title <span style="color:#dc2626">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    placeholder="e.g. Finish the presentation"
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;outline:none;transition:border-color 0.15s"
                    onfocus="this.style.borderColor='#8b5cf6'" onblur="this.style.borderColor='var(--border)'">
                @error('title')<div style="color:#dc2626;font-size:0.8rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Description</label>
                <textarea name="description" rows="3" placeholder="Any details about this task..."
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;resize:vertical;outline:none"
                    onfocus="this.style.borderColor='#8b5cf6'" onblur="this.style.borderColor='var(--border)'">{{ old('description') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Type</label>
                    <select name="type" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
                        <option value="personal" {{ old('type') !== 'shared' ? 'selected' : '' }}>👤 Personal</option>
                        @if($user->hasPartner())
                        <option value="shared" {{ old('type') === 'shared' ? 'selected' : '' }}>🤝 Shared with {{ $user->partner->name }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Priority</label>
                    <select name="priority" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🔴 High</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Low</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:28px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Deadline</label>
                <input type="date" name="deadline" value="{{ old('deadline') }}"
                    min="{{ today()->format('Y-m-d') }}"
                    style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;font-size:0.9rem;font-family:inherit;background:white">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px">
                🚀 Create Task
            </button>
        </form>
    </div>
</div>
@endsection