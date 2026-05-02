@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="analytics-page">

    {{-- Header --}}
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">📊</div>
            <div>
                <h1 class="page-title">Analytics</h1>
                <p class="page-subtitle">Track your progress and couple stats over time</p>
            </div>
        </div>
        <div class="header-badge">
            <span class="badge-dot"></span>
            {{ now()->format('F Y') }}
        </div>
    </div>

    {{-- Couple Overview --}}
    <div class="couple-row">
        <div class="couple-card you">
            <div class="couple-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="couple-info">
                <div class="couple-name">{{ $user->name }}</div>
                <div class="couple-role">You</div>
            </div>
            @php
                $myRate = $weeklyCompletion[3]['rate'] ?? 0;
            @endphp
            <div class="couple-stat">
                <div class="stat-num">{{ $myRate }}%</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>

        <div class="versus">💑</div>

        @if($partner)
        <div class="couple-card partner">
            <div class="couple-avatar partner-av">{{ strtoupper(substr($partner->name, 0, 1)) }}</div>
            <div class="couple-info">
                <div class="couple-name">{{ $partner->name }}</div>
                <div class="couple-role">Partner</div>
            </div>
            <div class="couple-stat">
                <div class="stat-num" style="color:#ec4899">—</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>
        @else
        <div class="couple-card partner empty-partner">
            <div class="couple-avatar" style="background:#e5e7eb; color:#9ca3af">?</div>
            <div class="couple-info">
                <div class="couple-name" style="color:#9ca3af">No Partner Yet</div>
                <div class="couple-role">Partner</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Weekly Completion KPIs --}}
    <div class="kpi-row">
        @foreach($weeklyCompletion as $week)
        <div class="kpi-card">
            <div class="kpi-label">{{ $week['week'] }}</div>
            <div class="kpi-ring-wrap">
                <svg viewBox="0 0 52 52" class="kpi-ring">
                    <circle cx="26" cy="26" r="22" fill="none" stroke="#f3f4f6" stroke-width="5"/>
                    <circle cx="26" cy="26" r="22" fill="none"
                        stroke="{{ $week['rate'] >= 80 ? '#22c55e' : ($week['rate'] >= 50 ? '#f59e0b' : '#f87171') }}"
                        stroke-width="5"
                        stroke-dasharray="{{ round($week['rate'] * 1.382) }} 138.2"
                        stroke-linecap="round"
                        transform="rotate(-90 26 26)"/>
                </svg>
                <div class="kpi-num">{{ $week['rate'] }}%</div>
            </div>
            <div class="kpi-sub">{{ $week['done'] }}/{{ $week['total'] }} tasks</div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row 1: Monthly Tasks --}}
    <div class="charts-row">
        <div class="chart-card wide">
            <div class="chart-header">
                <div class="chart-title">📋 Personal Tasks — Last 6 Months</div>
                <div class="chart-legend">
                    <span class="legend-dot" style="background:#7c3aed"></span> Created
                    <span class="legend-dot" style="background:#22c55e; margin-left:1rem"></span> Completed
                </div>
            </div>
            <canvas id="chartPersonal" height="90"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">🤝 Priority Distribution</div>
            </div>
            <canvas id="chartPriority" height="180"></canvas>
        </div>
    </div>

    {{-- Charts Row 2: Shared + Mood --}}
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">🔗 Shared Tasks — Last 6 Months</div>
            </div>
            <canvas id="chartShared" height="160"></canvas>
        </div>

        <div class="chart-card wide">
            <div class="chart-header">
                <div class="chart-title">🌡️ Mood Trend — Last 14 Days</div>
                <div class="chart-legend">
                    <span class="legend-dot" style="background:#7c3aed"></span> You
                    @if($partner)
                    <span class="legend-dot" style="background:#ec4899; margin-left:1rem"></span> {{ $partner->name }}
                    @endif
                </div>
            </div>
            <canvas id="chartMood" height="90"></canvas>
        </div>
    </div>

</div>

<style>
.analytics-page {
    padding: 2rem;
    max-width: 1200px;
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
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    border-radius: 16px;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(124,58,237,0.15);
    flex-shrink: 0;
}

.page-title { font-size: 1.8rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { color: #888; margin: 0; font-size: 0.9rem; }

.header-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    padding: 0.45rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
}

.badge-dot {
    width: 8px; height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* Couple Row */
.couple-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.couple-card {
    flex: 1;
    background: white;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1.5px solid #f3f4f6;
    position: relative;
    overflow: hidden;
}

.couple-card.you::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #7c3aed, #a78bfa);
}

.couple-card.partner::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #ec4899, #f9a8d4);
}

.couple-avatar {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    font-weight: 800;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.partner-av {
    background: linear-gradient(135deg, #ec4899, #db2777);
}

.couple-info { flex: 1; }
.couple-name { font-weight: 700; font-size: 1rem; color: #1f2937; }
.couple-role { font-size: 0.78rem; color: #9ca3af; }

.couple-stat { text-align: right; }
.stat-num { font-size: 1.5rem; font-weight: 800; color: #7c3aed; }
.stat-label { font-size: 0.72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }

.versus {
    font-size: 2rem;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

/* KPI Row */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1.5px solid #f3f4f6;
    transition: all 0.2s;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.09);
}

.kpi-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.kpi-ring-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    margin: 0 auto 0.5rem;
}

.kpi-ring { width: 64px; height: 64px; }

.kpi-num {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 800;
    color: #1f2937;
}

.kpi-sub {
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 500;
}

/* Chart Cards */
.charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.chart-card {
    background: white;
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1.5px solid #f3f4f6;
}

.chart-card.wide {
    grid-column: span 1;
}

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.chart-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #374151;
}

.chart-legend {
    display: flex;
    align-items: center;
    font-size: 0.78rem;
    color: #6b7280;
    font-weight: 500;
}

.legend-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-right: 0.35rem;
}

@media (max-width: 900px) {
    .charts-row { grid-template-columns: 1fr; }
    .kpi-row { grid-template-columns: repeat(2, 1fr); }
    .couple-row { flex-direction: column; }
    .versus { transform: rotate(90deg); }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const purple = '#7c3aed';
const purpleLight = 'rgba(124,58,237,0.12)';
const green = '#22c55e';
const greenLight = 'rgba(34,197,94,0.12)';
const pink = '#ec4899';
const pinkLight = 'rgba(236,72,153,0.12)';
const amber = '#f59e0b';

Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.color = '#9ca3af';
Chart.defaults.plugins.legend.display = false;

const monthlyPersonal = @json($monthlyPersonal);
const monthlyShared   = @json($monthlyShared);
const moodTrend       = @json($moodTrend);
const weeklyCompletion = @json($weeklyCompletion);
const priorityDist    = @json($priorityDist);

// --- Personal Tasks Chart ---
new Chart(document.getElementById('chartPersonal'), {
    type: 'bar',
    data: {
        labels: monthlyPersonal.map(d => d.month),
        datasets: [
            {
                label: 'Created',
                data: monthlyPersonal.map(d => d.created),
                backgroundColor: purpleLight,
                borderColor: purple,
                borderWidth: 2,
                borderRadius: 6,
            },
            {
                label: 'Completed',
                data: monthlyPersonal.map(d => d.completed),
                backgroundColor: greenLight,
                borderColor: green,
                borderWidth: 2,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: { grid: { color: '#f3f4f6' }, border: { display: false }, ticks: { precision: 0 } }
        }
    }
});

// --- Shared Tasks Chart ---
new Chart(document.getElementById('chartShared'), {
    type: 'line',
    data: {
        labels: monthlyShared.map(d => d.month),
        datasets: [
            {
                label: 'Created',
                data: monthlyShared.map(d => d.created),
                borderColor: purple,
                backgroundColor: purpleLight,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: purple,
                pointRadius: 4,
            },
            {
                label: 'Completed',
                data: monthlyShared.map(d => d.completed),
                borderColor: green,
                backgroundColor: greenLight,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: green,
                pointRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: { grid: { color: '#f3f4f6' }, border: { display: false }, ticks: { precision: 0 } }
        }
    }
});

// --- Mood Trend Chart ---
new Chart(document.getElementById('chartMood'), {
    type: 'line',
    data: {
        labels: moodTrend.map(d => d.date),
        datasets: [
            {
                label: 'You',
                data: moodTrend.map(d => d.user_mood),
                borderColor: purple,
                backgroundColor: purpleLight,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: purple,
                pointRadius: 3,
            },
            {
                label: 'Partner',
                data: moodTrend.map(d => d.partner_mood),
                borderColor: pink,
                backgroundColor: pinkLight,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: pink,
                pointRadius: 3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { maxRotation: 0 } },
            y: {
                grid: { color: '#f3f4f6' },
                border: { display: false },
                min: 0, max: 10,
                ticks: { stepSize: 2 }
            }
        }
    }
});

// --- Priority Distribution Doughnut ---
const priorityLabels = priorityDist.map(d => d.priority ?? 'none');
const priorityColors = priorityLabels.map(p => {
    if (p === 'high') return '#ef4444';
    if (p === 'medium') return amber;
    if (p === 'low') return green;
    return '#e5e7eb';
});

new Chart(document.getElementById('chartPriority'), {
    type: 'doughnut',
    data: {
        labels: priorityLabels.map(p => p.charAt(0).toUpperCase() + p.slice(1)),
        datasets: [{
            data: priorityDist.map(d => d.count),
            backgroundColor: priorityColors,
            borderWidth: 2,
            borderColor: 'white',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { padding: 16, font: { size: 12 }, usePointStyle: true, pointStyleWidth: 10 }
            }
        }
    }
});
</script>
@endsection