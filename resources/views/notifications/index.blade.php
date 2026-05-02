@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="notif-page">

    {{-- Header --}}
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">🔔</div>
            <div>
                <h1 class="page-title">Notifications</h1>
                <p class="page-subtitle">All your updates and activity in one place</p>
            </div>
        </div>
        @if($notifications->total() > 0)
           <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn-markall">✓ Mark all read</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- Notification List --}}
    @if($notifications->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">🔕</div>
            <h3>You're all caught up!</h3>
            <p>No notifications yet. We'll ping you when something important happens.</p>
        </div>
    @else
        <div class="notif-list">
            @foreach($notifications as $notif)
                @php
                    $typeColor = match($notif->type ?? 'info') {
                        'success' => 'green',
                        'warning' => 'amber',
                        'error'   => 'red',
                        default   => 'purple',
                    };
                @endphp
                <div class="notif-item {{ $notif->read_status ? 'read' : 'unread' }} {{ $typeColor }}">
                    <div class="notif-icon-wrap {{ $typeColor }}">
                        {{ $notif->icon ?? '🔔' }}
                    </div>

                    <div class="notif-body">
                        <div class="notif-title">{{ $notif->title }}</div>
                        @if($notif->message)
                            <div class="notif-msg">{{ $notif->message }}</div>
                        @endif
                        <div class="notif-meta">
                            <span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                            {{-- Action button ikut ke bawah title di mobile --}}
                            @if($notif->action_url)
                                <a href="{{ $notif->action_url }}" class="notif-action-btn">View →</a>
                            @endif
                        </div>
                    </div>

                    <div class="notif-right">
                        @if(!$notif->read_status)
                            <span class="unread-dot"></span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="pagination-wrap">
                {{ $notifications->links() }}
            </div>
        @endif
    @endif

</div>

<style>
/* =====================
   BASE
   ===================== */
.notif-page {
    padding: 2rem;
    max-width: 780px;
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
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 16px;
    width: 64px; height: 64px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(245,158,11,0.18);
    flex-shrink: 0;
}

.page-title { font-size: 1.8rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { color: #888; margin: 0; font-size: 0.9rem; }

.btn-markall {
    padding: 0.55rem 1.1rem;
    background: #f5f3ff;
    color: #7c3aed;
    border: 1.5px solid #ddd6fe;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-markall:hover {
    background: #7c3aed;
    color: white;
    border-color: #7c3aed;
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

.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    color: #9ca3af;
}
.empty-icon { font-size: 4rem; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.25rem; font-weight: 600; color: #374151; margin: 0 0 0.5rem; }
.empty-state p { font-size: 0.95rem; margin: 0; }

/* Notification List */
.notif-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: white;
    border-radius: 16px;
    padding: 1.1rem 1.25rem;
    border: 1.5px solid #f3f4f6;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    transition: all 0.2s;
    position: relative;
}
.notif-item:hover {
    transform: translateX(3px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.notif-item.unread {
    background: #fafbff;
    border-color: #ede9fe;
}
.notif-item.unread.green  { border-left: 3px solid #22c55e; }
.notif-item.unread.amber  { border-left: 3px solid #f59e0b; }
.notif-item.unread.red    { border-left: 3px solid #ef4444; }
.notif-item.unread.purple { border-left: 3px solid #7c3aed; }

/* Icon */
.notif-icon-wrap {
    width: 42px; height: 42px;
    border-radius: 12px;
    font-size: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.notif-icon-wrap.purple { background: #f5f3ff; }
.notif-icon-wrap.green  { background: #ecfdf5; }
.notif-icon-wrap.amber  { background: #fffbeb; }
.notif-icon-wrap.red    { background: #fef2f2; }

/* Body */
.notif-body { flex: 1; min-width: 0; }

.notif-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.2rem;
    line-height: 1.3;
}
.notif-item.read .notif-title {
    font-weight: 500;
    color: #6b7280;
}

.notif-msg {
    font-size: 0.83rem;
    color: #6b7280;
    line-height: 1.45;
    margin-bottom: 0.35rem;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.25rem;
}

.notif-time {
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 500;
}

/* Right — hanya dot, action btn sudah pindah ke meta */
.notif-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
}

.unread-dot {
    width: 9px; height: 9px;
    background: #7c3aed;
    border-radius: 50%;
    display: block;
    margin-top: 4px;
}

.notif-action-btn {
    font-size: 0.78rem;
    font-weight: 600;
    color: #7c3aed;
    text-decoration: none;
    padding: 0.2rem 0.6rem;
    background: #f5f3ff;
    border-radius: 8px;
    transition: all 0.2s;
    white-space: nowrap;
}
.notif-action-btn:hover {
    background: #7c3aed;
    color: white;
}

/* Pagination */
.pagination-wrap {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}
.pagination-wrap nav { display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap; justify-content: center; }
.pagination-wrap .page-link,
.pagination-wrap span[aria-current] span {
    padding: 0.45rem 0.85rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.2s;
}
.pagination-wrap .page-link:hover {
    background: #f5f3ff;
    border-color: #c4b5fd;
    color: #7c3aed;
}
.pagination-wrap span[aria-current] span {
    background: #7c3aed;
    border-color: #7c3aed;
    color: white;
}

/* =====================
   TABLET (max 768px)
   ===================== */
@media (max-width: 768px) {
    .notif-page {
        padding: 1rem;
        max-width: 100%;
    }

    .page-header {
        margin-bottom: 1.25rem;
    }

    .page-icon {
        width: 52px; height: 52px;
        font-size: 2rem;
        border-radius: 14px;
    }

    .page-title { font-size: 1.4rem; }
    .page-subtitle { font-size: 0.82rem; }

    .btn-markall {
        font-size: 0.8rem;
        padding: 0.45rem 0.9rem;
    }

    .notif-item {
        padding: 0.9rem 1rem;
        border-radius: 14px;
        gap: 0.75rem;
    }

    .notif-icon-wrap {
        width: 36px; height: 36px;
        font-size: 1rem;
        border-radius: 10px;
    }

    .notif-title { font-size: 0.88rem; }
    .notif-msg   { font-size: 0.8rem; }
}

/* =====================
   MOBILE (max 480px)
   ===================== */
@media (max-width: 480px) {
    .notif-page { padding: 0.75rem; }

    /* Header stack vertikal */
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    /* Mark all read full width */
    .page-header form { width: 100%; }
    .btn-markall { width: 100%; justify-content: center; display: flex; }

    .page-icon {
        width: 44px; height: 44px;
        font-size: 1.6rem;
        border-radius: 12px;
    }

    .page-title { font-size: 1.2rem; }

    .notif-item {
        padding: 0.75rem 0.875rem;
        gap: 0.6rem;
        border-radius: 12px;
    }

    /* Sembunyikan icon di mobile sangat kecil kalau perlu ruang */
    .notif-icon-wrap {
        width: 32px; height: 32px;
        font-size: 0.9rem;
        border-radius: 8px;
    }

    .notif-title { font-size: 0.85rem; }
    .notif-msg   { font-size: 0.78rem; }
    .notif-time  { font-size: 0.7rem; }

    .unread-dot { width: 8px; height: 8px; }

    /* Pagination lebih compact */
    .pagination-wrap .page-link,
    .pagination-wrap span[aria-current] span {
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
    }

    .empty-state { padding: 3rem 1rem; }
    .empty-icon  { font-size: 3rem; }
}
</style>
@endsection