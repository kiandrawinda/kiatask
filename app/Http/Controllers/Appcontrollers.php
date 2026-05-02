<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Mood;
use App\Models\AppNotification;
use App\Models\FocusSession;
use App\Models\SecretLetter;
use App\Models\SpecialDate;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// ==================== GOAL CONTROLLER ====================
class GoalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $partner = $user->partner;
        $partnerIds = array_filter([$user->id, $partner?->id]);

        $goals = Goal::whereIn('owner_id', $partnerIds)
            ->with(['owner', 'partner'])
            ->latest()->get();

        return view('goals.index', compact('goals', 'user', 'partner'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'target_value' => 'required|numeric|min:1',
            'unit' => 'nullable|string',
            'deadline' => 'nullable|date',
        ]);

        $goal = Goal::create([
            'owner_id' => $user->id,
            'partner_id' => $user->partner_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category ?? 'general',
            'target_value' => $request->target_value,
            'current_value' => 0,
            'unit' => $request->unit ?? '%',
            'deadline' => $request->deadline,
        ]);

        if ($user->partner) {
            AppNotification::create([
                'user_id' => $user->partner_id,
                'title' => '🎯 New Couple Goal!',
                'message' => "{$user->name} created a new goal: \"{$goal->title}\"",
                'type' => 'info',
                'icon' => '🎯',
            ]);
        }

        return redirect()->route('goals.index')->with('success', 'Goal created! Let\'s crush it! 💪');
    }

    public function update(Request $request, Goal $goal)
    {
        $user = Auth::user();
        $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $newValue = min($request->current_value, $goal->target_value);
        $wasCompleted = $goal->is_completed;
        $isNowCompleted = $newValue >= $goal->target_value;

        $goal->update([
            'current_value' => $newValue,
            'is_completed' => $isNowCompleted,
            'completed_at' => (!$wasCompleted && $isNowCompleted) ? now() : $goal->completed_at,
        ]);

        if (!$wasCompleted && $isNowCompleted) {
            if ($user->partner) {
                AppNotification::create([
                    'user_id' => $user->partner_id,
                    'title' => '🏆 Goal Achieved!',
                    'message' => "You both achieved the goal: \"{$goal->title}\" 🎉",
                    'type' => 'success',
                    'icon' => '🏆',
                ]);
            }

            // Unlock letters
            $letters = SecretLetter::where('receiver_id', $user->id)
                ->where('unlock_condition', 'goal_reached')
                ->where('unlock_ref_id', $goal->id)
                ->where('is_unlocked', false)
                ->get();

            foreach ($letters as $letter) {
                $letter->update(['is_unlocked' => true, 'unlocked_at' => now()]);
                AppNotification::create([
                    'user_id' => $user->id,
                    'title' => '💌 Secret Letter Unlocked!',
                    'message' => "You unlocked a secret letter from {$letter->sender->name}!",
                    'type' => 'love',
                    'icon' => '💌',
                ]);
            }
        }

        return back()->with('success', 'Progress updated! 🚀');
    }

    public function destroy(Goal $goal)
    {
        $goal->delete();
        return back()->with('success', 'Goal removed.');
    }
}

// ==================== MOOD CONTROLLER ====================
class MoodController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'mood_level' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:500',
        ]);

        $mood = Mood::updateOrCreate(
            ['user_id' => $user->id, 'date' => today()],
            [
                'mood_level' => $request->mood_level,
                'mood_emoji' => Mood::getMoodEmoji($request->mood_level),
                'note' => $request->note,
            ]
        );

        // Notify partner if mood is low
        if ($request->mood_level <= 2 && $user->partner) {
            AppNotification::create([
                'user_id' => $user->partner_id,
                'title' => '💌 Your partner might need you today',
                'message' => "{$user->name} is feeling {$mood->mood_label} today. Maybe send some love? 🤗",
                'type' => 'love',
                'icon' => '💌',
            ]);
        }

        return back()->with('success', 'Mood logged! ' . Mood::getMoodEmoji($request->mood_level));
    }

    public function history()
    {
        $user = Auth::user();
        $moods = Mood::where('user_id', $user->id)->orderBy('date', 'desc')->paginate(30);
        return view('moods.history', compact('moods', 'user'));
    }
}

// ==================== PARTNER CONTROLLER ====================
class PartnerController extends Controller
{
    public function connect(Request $request)
    {
        $user = Auth::user();
        $request->validate(['partner_code' => 'required|string|size:8']);

        $partner = User::where('partner_code', strtoupper($request->partner_code))->first();

        if (!$partner) {
            return back()->withErrors(['partner_code' => 'Invalid partner code. Double-check and try again! 💔']);
        }

        if ($partner->id === $user->id) {
            return back()->withErrors(['partner_code' => 'You cannot connect with yourself! 😄']);
        }

        if ($partner->partner_id && $partner->partner_id !== $user->id) {
            return back()->withErrors(['partner_code' => 'This user is already connected with someone else.']);
        }

        // Mutual connection
        $user->update(['partner_id' => $partner->id]);
        $partner->update(['partner_id' => $user->id]);

        AppNotification::create([
            'user_id' => $partner->id,
            'title' => '💑 Partner Connected!',
            'message' => "{$user->name} connected with you! Time to be productive together 🚀",
            'type' => 'love',
            'icon' => '💑',
        ]);

        return redirect()->route('dashboard')->with('success', "Connected with {$partner->name}! Welcome to Power Couple mode! 💑");
    }

    public function disconnect()
    {
        $user = Auth::user();
        $partner = $user->partner;

        if ($partner) {
            $partner->update(['partner_id' => null]);
            AppNotification::create([
                'user_id' => $partner->id,
                'title' => '💔 Partner Disconnected',
                'message' => "{$user->name} disconnected. Your data is still safe.",
                'type' => 'warning',
                'icon' => '💔',
            ]);
        }

        $user->update(['partner_id' => null]);
        return redirect()->route('dashboard')->with('success', 'Partner disconnected.');
    }
}

// ==================== FOCUS SESSION CONTROLLER ====================
class FocusSessionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeSession = $user->getActiveFocusSession();
        $partnerSession = $user->partner ? $user->partner->getActiveFocusSession() : null;
        $recentSessions = FocusSession::where('user_id', $user->id)->latest()->take(10)->get();

        return view('focus.index', compact('user', 'activeSession', 'partnerSession', 'recentSessions'));
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        $request->validate(['duration_minutes' => 'nullable|integer|min:1|max:120']);

        // Cancel existing active session
        FocusSession::where('user_id', $user->id)->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $session = FocusSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'duration_minutes' => $request->duration_minutes ?? 25,
            'status' => 'active',
            'note' => $request->note,
        ]);

        if ($user->partner) {
            AppNotification::create([
                'user_id' => $user->partner_id,
                'title' => '🍅 Focus Session Started!',
                'message' => "{$user->name} started a {$session->duration_minutes}-min focus session. Join them! 💪",
                'type' => 'info',
                'icon' => '🍅',
                'action_url' => route('focus.index'),
            ]);
        }

        return response()->json(['success' => true, 'session' => $session]);
    }

    public function stop()
    {
        $user = Auth::user();
        $session = $user->getActiveFocusSession();

        if ($session) {
            $session->update(['status' => 'completed', 'ended_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function status()
    {
        $user = Auth::user();
        $session = $user->getActiveFocusSession();
        $partnerSession = $user->partner ? $user->partner->getActiveFocusSession() : null;

        return response()->json([
            'my_session' => $session ? [
                'id' => $session->id,
                'remaining_seconds' => $session->remaining_seconds,
                'duration_minutes' => $session->duration_minutes,
                'is_expired' => $session->isExpired(),
            ] : null,
            'partner_session' => $partnerSession ? [
                'id' => $partnerSession->id,
                'partner_name' => $user->partner->name,
                'remaining_seconds' => $partnerSession->remaining_seconds,
                'is_expired' => $partnerSession->isExpired(),
            ] : null,
        ]);
    }
}

// ==================== SECRET LETTER CONTROLLER ====================
class SecretLetterController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sent = SecretLetter::where('sender_id', $user->id)->with('receiver')->latest()->get();
        $received = SecretLetter::where('receiver_id', $user->id)->with('sender')->latest()->get();

        return view('letters.index', compact('user', 'sent', 'received'));
    }

    public function create()
    {
        $user = Auth::user();
        $goals = Goal::whereIn('owner_id', [$user->id, $user->partner_id ?? 0])->get();
        $tasks = Task::where('type', 'shared')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('user_id', $user->partner_id);
            })->get();

        return view('letters.create', compact('user', 'goals', 'tasks'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'unlock_condition' => 'required|in:task_complete,goal_reached,streak,date',
            'unlock_ref_id' => 'nullable|integer',
            'unlock_streak_count' => 'nullable|integer|min:1',
            'unlock_date' => 'nullable|date',
        ]);

        SecretLetter::create([
            'sender_id' => $user->id,
            'receiver_id' => $user->partner_id,
            'title' => $request->title,
            'message' => $request->message,
            'unlock_condition' => $request->unlock_condition,
            'unlock_ref_id' => $request->unlock_ref_id,
            'unlock_streak_count' => $request->unlock_streak_count,
            'unlock_date' => $request->unlock_date,
            'is_unlocked' => false,
        ]);

        return redirect()->route('letters.index')->with('success', 'Secret letter sent! 💌 It\'ll unlock when your partner completes their mission.');
    }

    public function show(SecretLetter $letter)
    {
        $user = Auth::user();
        if ($letter->receiver_id !== $user->id && $letter->sender_id !== $user->id) {
            abort(403);
        }

        if ($letter->receiver_id === $user->id && !$letter->is_unlocked) {
            abort(403, 'This letter is still locked! Complete the mission first 🔒');
        }

        return view('letters.show', compact('letter', 'user'));
    }

    public function destroy(SecretLetter $letter)
    {
        if ($letter->sender_id !== Auth::id()) abort(403);
        $letter->delete();
        return redirect()->route('letters.index')->with('success', 'Letter deleted.');
    }
}

// ==================== NOTIFICATION CONTROLLER ====================
class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = AppNotification::where('user_id', $user->id)
            ->latest()->paginate(20);

        AppNotification::where('user_id', $user->id)
            ->where('read_status', false)
            ->update(['read_status' => true]);

        return view('notifications.index', compact('notifications', 'user'));
    }

    public function markRead(AppNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['read_status' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        AppNotification::where('user_id', Auth::id())
            ->where('read_status', false)
            ->update(['read_status' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function getUnread()
    {
        $user = Auth::user();
        $notifications = AppNotification::where('user_id', $user->id)
            ->where('read_status', false)
            ->latest()->take(10)->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }
}

// ==================== SPECIAL DATE CONTROLLER ====================
class SpecialDateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $dates = SpecialDate::where('user_id', $user->id)->get()->sortBy('days_until');
        return view('dates.index', compact('dates', 'user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'emoji' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
        ]);

        SpecialDate::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'emoji' => $request->emoji ?? '💑',
            'is_recurring' => $request->boolean('is_recurring', true),
        ]);

        return back()->with('success', 'Special date added! 📅');
    }

    public function destroy(SpecialDate $specialDate)
    {
        if ($specialDate->user_id !== Auth::id()) abort(403);
        $specialDate->delete();
        return back()->with('success', 'Date removed.');
    }
}

// ==================== ANALYTICS CONTROLLER ====================
class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $partner = $user->partner;

        // Monthly stats
        $monthlyPersonal = $this->getMonthlyStats($user->id, 'personal');
        $monthlyShared = $this->getMonthlyStats($user->id, 'shared');

        // Mood trend (last 14 days)
        $moodTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $mood = Mood::where('user_id', $user->id)->whereDate('date', $date)->first();
            $partnerMood = $partner ? Mood::where('user_id', $partner->id)->whereDate('date', $date)->first() : null;
            $moodTrend[] = [
                'date' => $date->format('M d'),
                'user_mood' => $mood?->mood_level ?? 0,
                'partner_mood' => $partnerMood?->mood_level ?? 0,
            ];
        }

        // Priority distribution
        $priorityDist = Task::where('user_id', $user->id)
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')->get();

        // Completion rate by week
        $weeklyCompletion = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->startOfWeek()->subWeeks($i);
            $weekEnd = Carbon::now()->endOfWeek()->subWeeks($i);
            $total = Task::where('user_id', $user->id)->whereBetween('created_at', [$weekStart, $weekEnd])->count();
            $done = Task::where('user_id', $user->id)->where('status', 'done')->whereBetween('completed_at', [$weekStart, $weekEnd])->count();
            $weeklyCompletion[] = [
                'week' => 'Week ' . ($i === 0 ? 'Now' : "-{$i}"),
                'total' => $total,
                'done' => $done,
                'rate' => $total > 0 ? round(($done / $total) * 100) : 0,
            ];
        }

        return view('analytics.index', compact(
            'user', 'partner', 'monthlyPersonal', 'monthlyShared',
            'moodTrend', 'priorityDist', 'weeklyCompletion'
        ));
    }

    private function getMonthlyStats($userId, $type): array
    {
        $stats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $stats[] = [
                'month' => $month->format('M'),
                'created' => Task::where('user_id', $userId)->where('type', $type)
                    ->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
                'completed' => Task::where('user_id', $userId)->where('type', $type)->where('status', 'done')
                    ->whereMonth('completed_at', $month->month)->whereYear('completed_at', $month->year)->count(),
            ];
        }
        return $stats;
    }
}