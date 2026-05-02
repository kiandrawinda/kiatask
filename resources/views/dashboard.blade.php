@extends('layouts.app')
@section('title', 'Dashboard')

@section('styles')
<style>
.partner-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 24px;
    padding: 28px;
}
.mood-widget {
    background: linear-gradient(135deg, #fdfcff, #f5f0ff);
    border: 1px solid #e8e3f5;
    border-radius: 20px;
    padding: 24px;
}
.streak-badge {
    background: linear-gradient(135deg, #ff6b6b, #feca57);
    color: white;
    border-radius: 14px;
    padding: 12px 20px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.connect-banner {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: 24px;
    padding: 32px;
    color: white;
    text-align: center;
}
.hero-stat {
    text-align: center;
}
.hero-stat-num {
    font-family: 'Fraunces', serif;
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}
</style>
@endsection

@section('content')
<!-- Topbar -->
<div class="topbar">
    <div>
        <h1 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:400;margin:0">
            Good {{ now()->format('G') < 12 ? 'morning' : (now()->format('G') < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }} ✨
        </h1>
        <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.875rem">{{ now()->format('l, F j, Y') }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        @if(!$todayMood)
        <a href="#mood-check" class="btn btn-secondary" style="font-size:0.8rem">
            😊 Log Mood
        </a>
        @endif
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            ＋ New Task
        </a>
    </div>
</div>

@if(!$user->hasPartner())
<!-- ============ SOLO MODE DASHBOARD ============ -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px">
    <div class="card stat-card-violet">
        <div class="hero-stat">
            <div class="hero-stat-num gradient-text">{{ $personalStats['total'] }}</div>
            <div style="font-size:0.875rem;color:var(--text-secondary);margin-top:6px">Total Tasks</div>
        </div>
    </div>
    <div class="card stat-card-emerald">
        <div class="hero-stat">
            <div class="hero-stat-num" style="color:#059669">{{ $personalStats['done'] }}</div>
            <div style="font-size:0.875rem;color:var(--text-secondary);margin-top:6px">Completed</div>
        </div>
    </div>
    <div class="card stat-card-blue">
        <div class="hero-stat">
            <div class="hero-stat-num" style="color:#2563eb">{{ $personalStats['completion_rate'] }}%</div>
            <div style="font-size:0.875rem;color:var(--text-secondary);margin-top:6px">Completion Rate</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- Recent Tasks -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-weight:700;font-size:1rem;margin:0">📋 Recent Tasks</h3>
            <a href="{{ route('tasks.index') }}" style="font-size:0.8rem;color:#8b5cf6">View all →</a>
        </div>
        @forelse($personalTasks->take(5) as $task)
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)">
            <form action="{{ route('tasks.status', $task) }}" method="POST" style="display:inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'pending' : 'done' }}">
                <button type="submit" style="width:20px;height:20px;border-radius:50%;border:2px solid {{ $task->status === 'done' ? '#059669' : '#d1d5db' }};background:{{ $task->status === 'done' ? '#059669' : 'white' }};cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                    @if($task->status === 'done') <span style="color:white;font-size:10px">✓</span> @endif
                </button>
            </form>
            <div style="flex:1;min-width:0">
                <div style="font-size:0.875rem;font-weight:500;{{ $task->status === 'done' ? 'text-decoration:line-through;color:var(--text-muted)' : '' }}">{{ Str::limit($task->title, 32) }}</div>
                @if($task->deadline)
                <div style="font-size:0.75rem;color:{{ $task->isOverdue() ? '#dc2626' : 'var(--text-muted)' }}">
                    📅 {{ $task->deadline->format('M d') }}
                </div>
                @endif
            </div>
            <span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:var(--text-muted)">
            <div style="font-size:2rem;margin-bottom:8px">📝</div>
            <div>No tasks yet. Create your first one!</div>
        </div>
        @endforelse
    </div>

    <!-- Connect Partner + Mood -->
    <div style="display:flex;flex-direction:column;gap:20px">
        <div class="connect-banner fade-in">
            <div style="font-size:2.5rem;margin-bottom:12px">💑</div>
            <h3 style="font-family:'Fraunces',serif;font-size:1.4rem;font-weight:400;margin:0 0 8px">Connect Your Partner</h3>
            <p style="margin:0 0 20px;opacity:0.9;font-size:0.875rem">Share tasks, set couple goals, and grow together. Enter your partner's unique code to link up.</p>
            <form action="{{ route('partner.connect') }}" method="POST" style="display:flex;gap:10px;max-width:320px;margin:0 auto">
                @csrf
                <input type="text" name="partner_code" placeholder="Partner's code" maxlength="8"
                    style="flex:1;padding:12px 16px;border-radius:12px;border:none;font-size:0.875rem;text-transform:uppercase;letter-spacing:2px;font-family:'Plus Jakarta Sans',sans-serif"
                    value="{{ old('partner_code') }}">
                <button type="submit" style="background:rgba(255,255,255,0.2);color:white;border:2px solid rgba(255,255,255,0.4);border-radius:12px;padding:12px 20px;font-weight:700;cursor:pointer;font-size:0.875rem">Connect</button>
            </form>
            @error('partner_code')<div style="margin-top:10px;font-size:0.8rem;background:rgba(0,0,0,0.2);border-radius:8px;padding:8px">{{ $message }}</div>@enderror
            <div style="margin-top:16px;font-size:0.8rem;opacity:0.8">
                Your code: <strong style="letter-spacing:2px;font-size:1rem">{{ $user->partner_code }}</strong>
            </div>
        </div>

        <!-- Mood Check -->
        <div class="mood-widget" id="mood-check">
            <h3 style="font-weight:700;font-size:1rem;margin:0 0 16px">How are you feeling today?</h3>
            @if($todayMood)
            <div style="text-align:center;padding:8px">
                <div style="font-size:2.5rem">{{ $todayMood->mood_emoji }}</div>
                <div style="font-weight:600;margin-top:8px">{{ $todayMood->mood_label }}</div>
                @if($todayMood->note)<div style="color:var(--text-muted);font-size:0.8rem;margin-top:4px">"{{ $todayMood->note }}"</div>@endif
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:8px">Mood logged for today ✓</div>
            </div>
            @else
            <form action="{{ route('mood.store') }}" method="POST" x-data="{ selected: 0, note: '' }">
                @csrf
                <div style="display:flex;gap:10px;justify-content:center;margin-bottom:16px">
                    @foreach([1=>'😔',2=>'😕',3=>'😊',4=>'😄',5=>'🥰'] as $level => $emoji)
                    <button type="button" class="mood-btn" :class="{ selected: selected === {{ $level }} }"
                        @click="selected = {{ $level }}; document.getElementById('mood_level').value = {{ $level }}">{{ $emoji }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="mood_level" id="mood_level" value="3">
                <textarea name="note" placeholder="Any notes? (optional)" rows="2"
                    style="width:100%;border:1px solid var(--border);border-radius:10px;padding:10px;font-size:0.8rem;resize:none;font-family:inherit;margin-bottom:12px"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Log Mood</button>
            </form>
            @endif
        </div>
    </div>
</div>

@else
<!-- ============ POWER COUPLE DASHBOARD ============ -->

<!-- Partner Alert if low mood -->
@if(isset($partnerMood) && $partnerMood && $partnerMood->mood_level <= 2)
<div style="background:linear-gradient(135deg,#fdf2f8,#fce7f3);border:1px solid #fbcfe8;border-radius:16px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px" class="fade-in">
    <div style="font-size:1.75rem">💌</div>
    <div>
        <div style="font-weight:700;color:#be185d">Your partner might need you today</div>
        <div style="color:#9d174d;font-size:0.875rem">{{ $partner->name }} is feeling {{ $partnerMood->mood_label }} today. Maybe reach out? 🤗</div>
    </div>
</div>
@endif

<!-- Active Focus Alert -->
@if(isset($activeFocus) && $activeFocus && $activeFocus->user_id !== $user->id)
<div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:16px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px" class="fade-in">
    <div style="font-size:1.75rem pulse-soft">🍅</div>
    <div style="flex:1">
        <div style="font-weight:700;color:#1e40af">{{ $partner->name }} is focusing now!</div>
        <div style="color:#1d4ed8;font-size:0.875rem">Join their focus session to stay in sync 💪</div>
    </div>
    <a href="{{ route('focus.index') }}" class="btn btn-primary btn-sm">Join →</a>
</div>
@endif

<!-- Stats Row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="card stat-card-violet">
        <div class="hero-stat">
            <div class="hero-stat-num gradient-text">{{ $personalStats['total'] }}</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:4px">My Tasks</div>
        </div>
    </div>
    <div class="card stat-card-emerald">
        <div class="hero-stat">
            <div class="hero-stat-num" style="color:#059669">{{ $personalStats['done'] }}</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:4px">Completed</div>
        </div>
    </div>
    <div class="card stat-card-blue">
        @php $coupleStreak = $coupleStreak ?? 0 @endphp
        <div class="hero-stat">
            <div class="hero-stat-num" style="color:#2563eb">{{ $coupleStreak }}</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:4px">🔥 Couple Streak</div>
        </div>
    </div>
    <div class="card stat-card-rose">
        <div class="hero-stat">
            <div class="hero-stat-num" style="color:#e11d48">{{ isset($goals) ? $goals->count() : 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:4px">Active Goals</div>
        </div>
    </div>
</div>

<!-- Main grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <!-- Shared Tasks -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-weight:700;font-size:1rem;margin:0">🤝 Shared Tasks</h3>
            <a href="{{ route('tasks.index', ['type'=>'shared']) }}" style="font-size:0.8rem;color:#8b5cf6">View all →</a>
        </div>
        @forelse(isset($sharedTasks) ? $sharedTasks : [] as $task)
        <div style="padding:12px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:flex-start;gap:10px">
                <form action="{{ route('tasks.status', $task) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'pending' : 'done' }}">
                    <button type="submit" style="width:20px;height:20px;border-radius:50%;border:2px solid {{ $task->status === 'done' ? '#059669' : '#d1d5db' }};background:{{ $task->status === 'done' ? '#059669' : 'white' }};cursor:pointer;flex-shrink:0;margin-top:2px">
                        @if($task->status === 'done')<span style="color:white;font-size:10px;display:flex;align-items:center;justify-content:center">✓</span>@endif
                    </button>
                </form>
                <div style="flex:1;min-width:0">
                    <div style="font-size:0.875rem;font-weight:500">{{ Str::limit($task->title, 30) }}</div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                        <span class="badge badge-{{ $task->priority }}" style="padding:2px 8px">{{ ucfirst($task->priority) }}</span>
                        <span style="font-size:0.75rem;color:var(--text-muted)">by {{ $task->user->id === $user->id ? 'You' : $task->user->name }}</span>
                    </div>
                    <div class="progress-bar" style="margin-top:6px">
                        <div class="progress-fill" style="width:{{ $task->progress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:24px;color:var(--text-muted)">
            <div style="font-size:2rem;margin-bottom:8px">🤝</div>
            No shared tasks yet
        </div>
        @endforelse
        <a href="{{ route('tasks.create') }}" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:12px;font-size:0.8rem">＋ Add Shared Task</a>
    </div>

    <!-- Couple Goals -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-weight:700;font-size:1rem;margin:0">🎯 Couple Goals</h3>
            <a href="{{ route('goals.index') }}" style="font-size:0.8rem;color:#8b5cf6">View all →</a>
        </div>
        @forelse(isset($goals) ? $goals->take(3) : [] as $goal)
        <div style="margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <span style="font-size:0.875rem;font-weight:600">{{ Str::limit($goal->title, 25) }}</span>
                <span style="font-size:0.8rem;color:var(--text-muted)">{{ $goal->current_value }}/{{ $goal->target_value }} {{ $goal->unit }}</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $goal->progress_percentage }}%;background:{{ $goal->is_completed ? 'linear-gradient(90deg,#059669,#34d399)' : 'linear-gradient(90deg,#8b5cf6,#a78bfa)' }}"></div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px">
                <span style="font-size:0.75rem;color:var(--text-muted)">{{ $goal->progress_percentage }}%</span>
                @if($goal->deadline)
                <span style="font-size:0.75rem;color:{{ $goal->days_until_deadline < 0 ? '#dc2626' : 'var(--text-muted)' }}">
                    {{ $goal->days_until_deadline > 0 ? $goal->days_until_deadline . 'd left' : 'Overdue' }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:24px;color:var(--text-muted)">
            <div style="font-size:2rem;margin-bottom:8px">🎯</div>
            No goals yet. Dream bigger!
        </div>
        @endforelse
        <a href="{{ route('goals.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:4px;font-size:0.8rem">＋ Add Goal</a>
    </div>
</div>

<!-- Second row -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
    <!-- Partner Mood -->
    <div class="card">
        <h3 style="font-weight:700;font-size:1rem;margin:0 0 16px">💝 Mood Today</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div style="text-align:center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:6px">You</div>
                @if($todayMood)
                <div style="font-size:2rem">{{ $todayMood->mood_emoji }}</div>
                <div style="font-size:0.75rem;font-weight:600;margin-top:4px">{{ $todayMood->mood_label }}</div>
                @else
                <div style="font-size:2rem">—</div>
                <a href="#" onclick="document.getElementById('mood-modal').style.display='flex'" style="font-size:0.7rem;color:#8b5cf6">Log mood</a>
                @endif
            </div>
            <div style="text-align:center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:6px">{{ $partner->name }}</div>
                @if(isset($partnerMood) && $partnerMood)
                <div style="font-size:2rem">{{ $partnerMood->mood_emoji }}</div>
                <div style="font-size:0.75rem;font-weight:600;margin-top:4px">{{ $partnerMood->mood_label }}</div>
                @else
                <div style="font-size:2rem">❓</div>
                <div style="font-size:0.7rem;color:var(--text-muted)">Not logged</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Streak -->
    <div class="card" style="text-align:center">
        <h3 style="font-weight:700;font-size:1rem;margin:0 0 16px">🔥 Streak</h3>
        @if(isset($streak) && $streak)
        <div style="font-family:'Fraunces',serif;font-size:3rem;line-height:1;color:#f97316">{{ $streak->current_streak }}</div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 12px">days</div>
        @if($streak->badge)
        <div class="streak-badge" style="font-size:0.75rem;margin:0 auto">{{ $streak->badge }}</div>
        @endif
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:10px">Best: {{ $streak->longest_streak }} days</div>
        @else
        <div style="font-size:3rem">🌱</div>
        <div style="font-size:0.875rem;color:var(--text-muted)">Complete a task today to start your streak!</div>
        @endif
    </div>

    <!-- Next Special Date -->
    <div class="card">
        <h3 style="font-weight:700;font-size:1rem;margin:0 0 16px">📅 Next Special Date</h3>
        @if(isset($nextDate) && $nextDate)
        <div style="text-align:center">
            <div style="font-size:2rem;margin-bottom:8px">{{ $nextDate->emoji }}</div>
            <div style="font-weight:600;margin-bottom:4px">{{ $nextDate->title }}</div>
<div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:16px">
    {{ $nextDate->next_occurrence ? $nextDate->next_occurrence->format('F d, Y') : '-' }}
</div>
            @php $days = $nextDate->days_until @endphp
            @if($days == 0)
            <div style="background:#fdf2f8;color:#be185d;border-radius:12px;padding:10px;font-weight:700">🎉 Today!</div>
            @elseif($days > 0)
            <div style="display:flex;gap:8px;justify-content:center">
                <div class="countdown-unit">
                    <div style="font-family:'Fraunces',serif;font-size:1.5rem;font-weight:700">{{ floor($days / 30) }}</div>
                    <div style="font-size:0.65rem;color:var(--text-muted)">months</div>
                </div>
                <div class="countdown-unit">
                    <div style="font-family:'Fraunces',serif;font-size:1.5rem;font-weight:700">{{ $days % 30 }}</div>
                    <div style="font-size:0.65rem;color:var(--text-muted)">days</div>
                </div>
            </div>
            @endif
        </div>
        @else
        <div style="text-align:center;color:var(--text-muted)">
            <div style="font-size:2rem;margin-bottom:8px">📅</div>
            <a href="{{ route('dates.index') }}" style="color:#8b5cf6;font-size:0.875rem">Add a special date →</a>
        </div>
        @endif
    </div>
</div>

<!-- Productivity Chart -->
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <h3 style="font-weight:700;font-size:1rem;margin:0">📊 Weekly Productivity</h3>
        <a href="{{ route('analytics.index') }}" style="font-size:0.8rem;color:#8b5cf6">Full analytics →</a>
    </div>
    <canvas id="weeklyChart" height="80"></canvas>
</div>

@endif

<!-- Mood Modal -->
@if($user->hasPartner() && !$todayMood)
<div id="mood-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;align-items:center;justify-content:center">
    <div class="card" style="max-width:400px;width:100%;margin:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-weight:700;margin:0">How are you feeling? 💫</h3>
            <button onclick="document.getElementById('mood-modal').style.display='none'" style="background:none;border:none;font-size:1.2rem;cursor:pointer">✕</button>
        </div>
        <form action="{{ route('mood.store') }}" method="POST" x-data="{ selected: 3 }">
            @csrf
            <div style="display:flex;gap:12px;justify-content:center;margin-bottom:16px">
                @foreach([1=>'😔',2=>'😕',3=>'😊',4=>'😄',5=>'🥰'] as $level => $emoji)
                <button type="button" class="mood-btn" :class="{ selected: selected === {{ $level }} }"
                    @click="selected = {{ $level }}; document.getElementById('ml2').value = {{ $level }}">{{ $emoji }}</button>
                @endforeach
            </div>
            <input type="hidden" name="mood_level" id="ml2" value="3">
            <textarea name="note" placeholder="What's on your mind? (optional)" rows="2"
                style="width:100%;border:1px solid var(--border);border-radius:10px;padding:10px;font-size:0.875rem;resize:none;font-family:inherit;margin-bottom:12px"></textarea>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Save Mood</button>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($user->hasPartner() && isset($weeklyData))
<script>
const ctx = document.getElementById('weeklyChart');
if (ctx) {
    const weeklyData = @json($weeklyData);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: weeklyData.map(d => d.label),
            datasets: [
                {
                    label: '{{ explode(" ", $user->name)[0] }}',
                    data: weeklyData.map(d => d.user_tasks),
                    backgroundColor: 'rgba(139, 92, 246, 0.7)',
                    borderRadius: 8,
                },
                {
                    label: '{{ explode(" ", $partner->name)[0] }}',
                    data: weeklyData.map(d => d.partner_tasks),
                    backgroundColor: 'rgba(168, 200, 240, 0.7)',
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Plus Jakarta Sans', size: 12 } } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0ebff' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endif
@endsection