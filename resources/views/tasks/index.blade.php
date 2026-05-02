@extends('layouts.app')
@section('title', ucfirst($type) . ' Tasks')

@section('content')
<div class="topbar">
    <div>
        <h1 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:400;margin:0">
            {{ $type === 'personal' ? '📋 My Tasks' : '🤝 Shared Tasks' }}
        </h1>
        <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.875rem">
            {{ $stats['total'] }} total · {{ $stats['done'] }} done · {{ $stats['on_progress'] }} in progress
        </p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('tasks.index', ['type' => 'personal']) }}" class="btn {{ $type === 'personal' ? 'btn-primary' : 'btn-secondary' }}">Personal</a>
        @if($user->hasPartner())
        <a href="{{ route('tasks.index', ['type' => 'shared']) }}" class="btn {{ $type === 'shared' ? 'btn-primary' : 'btn-secondary' }}">Shared</a>
        @endif
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">＋ New Task</a>
    </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    @foreach(['pending'=>['🕐','Pending','#7c3aed','#f3f0ff'],'on_progress'=>['⚡','In Progress','#2563eb','#eff6ff'],'done'=>['✅','Done','#059669','#f0fdf4'],'total'=>['📊','Total','#374151','#f9fafb']] as $key=>$meta)
    <div class="card" style="background:{{ $meta[3] }};text-align:center;border:none">
        <div style="font-size:1.5rem;margin-bottom:4px">{{ $meta[0] }}</div>
        <div style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:700;color:{{ $meta[2] }}">{{ $stats[$key] }}</div>
        <div style="font-size:0.8rem;color:{{ $meta[2] }};opacity:0.8">{{ $meta[1] }}</div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <form method="GET" action="{{ route('tasks.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search tasks..."
            style="flex:1;min-width:200px;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.875rem">
        <select name="status" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.875rem;background:white">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="on_progress" {{ request('status') === 'on_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
        </select>
        <select name="priority" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.875rem;background:white">
            <option value="">All Priority</option>
            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>🔴 High</option>
            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>🟢 Low</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->hasAny(['search','status','priority']))
        <a href="{{ route('tasks.index', ['type'=>$type]) }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

<!-- Task Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
    @forelse($tasks as $task)
    <div class="card fade-in" style="{{ $task->status === 'done' ? 'opacity:0.75' : '' }}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                    @if($task->type === 'shared')
                    <span class="badge" style="background:#fdf4ff;color:#7e22ce">🤝 Shared</span>
                    @endif
                </div>
                <h4 style="font-weight:600;font-size:0.95rem;margin:0;{{ $task->status === 'done' ? 'text-decoration:line-through;color:var(--text-muted)' : '' }}">
                    {{ $task->title }}
                </h4>
                @if($task->description)
                <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 0;line-height:1.4">{{ Str::limit($task->description, 80) }}</p>
                @endif
            </div>
            <div style="display:flex;gap:6px;margin-left:8px">
                <a href="{{ route('tasks.edit', $task) }}" style="width:30px;height:30px;border-radius:8px;background:var(--lavender-soft);display:flex;align-items:center;justify-content:center;font-size:0.75rem;color:#5b42a0;text-decoration:none">✏️</a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:30px;height:30px;border-radius:8px;background:#fef2f2;border:none;cursor:pointer;font-size:0.75rem;color:#dc2626">🗑️</button>
                </form>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="progress-bar" style="margin-bottom:10px">
            <div class="progress-fill" style="width:{{ $task->progress }}%;background:{{ $task->status === 'done' ? 'linear-gradient(90deg,#059669,#34d399)' : 'linear-gradient(90deg,#8b5cf6,#a78bfa)' }}"></div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <!-- Status selector -->
                <form action="{{ route('tasks.status', $task) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                        style="padding:5px 10px;border:1px solid var(--border);border-radius:8px;font-size:0.75rem;font-family:inherit;background:white;cursor:pointer">
                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>🕐 Pending</option>
                        <option value="on_progress" {{ $task->status === 'on_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                        <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>✅ Done</option>
                    </select>
                </form>
                @if($task->deadline)
                <span style="font-size:0.75rem;color:{{ $task->isOverdue() ? '#dc2626' : ($task->isDueSoon() ? '#d97706' : 'var(--text-muted)') }};font-weight:{{ $task->isOverdue() ? '600' : '400' }}">
                    📅 {{ $task->deadline->format('M d') }}
                    @if($task->isOverdue()) <span style="color:#dc2626">Overdue!</span>
                    @elseif($task->isDueSoon()) <span style="color:#d97706">Soon!</span>
                    @endif
                </span>
                @endif
            </div>
            @if($task->type === 'shared')
            <div style="font-size:0.75rem;color:var(--text-muted)">
                by {{ $task->user->id === $user->id ? '👤 You' : '👤 ' . $task->user->name }}
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1;text-align:center;padding:48px">
        <div style="font-size:3rem;margin-bottom:12px">📝</div>
        <h3 style="font-weight:600;margin:0 0 8px;font-family:'Fraunces',serif">No tasks found</h3>
        <p style="color:var(--text-muted);margin:0 0 20px;font-size:0.875rem">
            {{ request()->hasAny(['search','status','priority']) ? 'No tasks match your filters.' : 'Create your first task to get started!' }}
        </p>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">＋ Create Task</a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div style="margin-top:24px">
    {{ $tasks->appends(request()->query())->links() }}
</div>
@endsection