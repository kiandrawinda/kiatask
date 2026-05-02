@extends('layouts.app')
@section('title', 'Couple Goals')

@section('content')
<div class="topbar">
    <div>
        <h1 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:400;margin:0">🎯 Couple Goals</h1>
        <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.875rem">Dream it. Set it. Crush it together 💪</p>
    </div>
    <button onclick="document.getElementById('add-goal-modal').style.display='flex'" class="btn btn-primary">＋ New Goal</button>
</div>

<!-- Goals Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px">
    @forelse($goals as $goal)
    <div class="card fade-in">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-size:0.75rem;background:var(--lavender-soft);color:#5b42a0;padding:3px 8px;border-radius:999px;font-weight:600">{{ ucfirst($goal->category) }}</span>
                    @if($goal->is_completed)<span class="badge badge-done">✅ Achieved!</span>@endif
                </div>
                <h3 style="font-weight:700;font-size:1rem;margin:0">{{ $goal->title }}</h3>
                @if($goal->description)<p style="color:var(--text-muted);font-size:0.8rem;margin:4px 0 0">{{ Str::limit($goal->description, 80) }}</p>@endif
            </div>
            <div style="display:flex;gap:6px">
                @if(!$goal->is_completed)
                <button onclick="openUpdateGoal({{ $goal->id }}, {{ $goal->current_value }}, {{ $goal->target_value }}, '{{ addslashes($goal->title) }}')"
                    style="width:30px;height:30px;border-radius:8px;background:var(--lavender-soft);border:none;cursor:pointer;font-size:0.75rem;color:#5b42a0">📈</button>
                @endif
                <form action="{{ route('goals.destroy', $goal) }}" method="POST" onsubmit="return confirm('Delete this goal?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:30px;height:30px;border-radius:8px;background:#fef2f2;border:none;cursor:pointer;font-size:0.75rem">🗑️</button>
                </form>
            </div>
        </div>

        <!-- Progress -->
        <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span style="font-size:0.875rem;font-weight:600;color:{{ $goal->is_completed ? '#059669' : '#5b42a0' }}">
                    {{ $goal->progress_percentage }}%
                </span>
                <span style="font-size:0.8rem;color:var(--text-muted)">{{ $goal->current_value }} / {{ $goal->target_value }} {{ $goal->unit }}</span>
            </div>
            <div class="progress-bar" style="height:10px">
                <div class="progress-fill" style="width:{{ $goal->progress_percentage }}%;background:{{ $goal->is_completed ? 'linear-gradient(90deg,#059669,#34d399)' : 'linear-gradient(90deg,#8b5cf6,#db2777)' }}"></div>
            </div>
        </div>

        <!-- Info -->
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.8rem;color:var(--text-muted)">
            <div>by {{ $goal->owner->id === $user->id ? 'You' : $goal->owner->name }}</div>
            @if($goal->deadline)
            <div style="color:{{ $goal->days_until_deadline < 0 ? '#dc2626' : ($goal->days_until_deadline <= 7 ? '#d97706' : 'var(--text-muted)') }};font-weight:{{ $goal->days_until_deadline <= 7 ? '600' : '400' }}">
                📅 {{ $goal->deadline->format('M d, Y') }}
                @if($goal->days_until_deadline >= 0)
                · {{ $goal->days_until_deadline }}d left
                @else
                · Overdue
                @endif
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1;text-align:center;padding:60px">
        <div style="font-size:3rem;margin-bottom:12px">🎯</div>
        <h3 style="font-family:'Fraunces',serif;font-weight:400;margin:0 0 8px;font-size:1.5rem">Set your first couple goal</h3>
        <p style="color:var(--text-muted);margin:0 0 20px">Define what you want to achieve together and track progress side by side.</p>
        <button onclick="document.getElementById('add-goal-modal').style.display='flex'" class="btn btn-primary">＋ Create Goal</button>
    </div>
    @endforelse
</div>

<!-- Add Goal Modal -->
<div id="add-goal-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;align-items:center;justify-content:center">
    <div class="card" style="max-width:480px;width:100%;margin:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-weight:700;margin:0;font-family:'Fraunces',serif">🎯 New Couple Goal</h3>
            <button onclick="document.getElementById('add-goal-modal').style.display='none'" style="background:none;border:none;font-size:1.2rem;cursor:pointer">✕</button>
        </div>
        <form action="{{ route('goals.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Goal Title *</label>
                <input type="text" name="title" required placeholder="e.g. Save for vacation"
                    style="width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;font-size:0.875rem;font-family:inherit">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Description</label>
                <textarea name="description" rows="2" placeholder="Describe your goal..."
                    style="width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;font-size:0.875rem;font-family:inherit;resize:none"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;font-weight:600;font-size:0.8rem;margin-bottom:6px">Target Value</label>
                    <input type="number" name="target_value" required min="1" value="100"
                        style="width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;font-size:0.875rem;font-family:inherit">
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:0.8rem;margin-bottom:6px">Unit</label>
                    <input type="text" name="unit" value="%" placeholder="%, km, $"
                        style="width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;font-size:0.875rem;font-family:inherit">
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:0.8rem;margin-bottom:6px">Category</label>
                    <select name="category" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;font-size:0.875rem;font-family:inherit;background:white">
                        <option value="general">General</option>
                        <option value="fitness">Fitness</option>
                        <option value="finance">Finance</option>
                        <option value="travel">Travel</option>
                        <option value="learning">Learning</option>
                        <option value="relationship">Relationship</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">Target Date</label>
                <input type="date" name="deadline" min="{{ today()->format('Y-m-d') }}"
                    style="width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;font-size:0.875rem;font-family:inherit;background:white">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">🚀 Create Goal</button>
        </form>
    </div>
</div>

<!-- Update Goal Modal -->
<div id="update-goal-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;align-items:center;justify-content:center">
    <div class="card" style="max-width:380px;width:100%;margin:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-weight:700;margin:0" id="update-goal-title">Update Progress</h3>
            <button onclick="document.getElementById('update-goal-modal').style.display='none'" style="background:none;border:none;font-size:1.2rem;cursor:pointer">✕</button>
        </div>
        <form id="update-goal-form" method="POST">
            @csrf @method('PATCH')
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:0.875rem;margin-bottom:6px">
                    Current Value (max: <span id="update-goal-max">100</span>)
                </label>
                <input type="number" name="current_value" id="update-goal-value" min="0" required
                    style="width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;font-size:1rem;font-family:inherit">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Update Progress 📈</button>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.getElementById('add-goal-modal').style.display='flex'</script>
@endif
@endsection

@section('scripts')
<script>
function openUpdateGoal(id, current, target, title) {
    document.getElementById('update-goal-title').textContent = title;
    document.getElementById('update-goal-max').textContent = target;
    document.getElementById('update-goal-value').value = current;
    document.getElementById('update-goal-value').max = target;
    document.getElementById('update-goal-form').action = '/goals/' + id;
    document.getElementById('update-goal-modal').style.display = 'flex';
}
</script>
@endsection