<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Goal;
use App\Models\Mood;
use App\Models\Streak;
use App\Models\FocusSession;
use App\Models\SpecialDate;
use App\Models\AppNotification;
use App\Models\SecretLetter;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $partner = $user->partner;

        // Today's mood
        $todayMood = $user->todayMood();

        // Streak
        $streak = $user->streak;

        // Personal tasks summary
        $personalTasks = Task::where('user_id', $user->id)
            ->where('type', 'personal')
            ->get();

        $personalStats = [
            'total' => $personalTasks->count(),
            'done' => $personalTasks->where('status', 'done')->count(),
            'pending' => $personalTasks->where('status', 'pending')->count(),
            'on_progress' => $personalTasks->where('status', 'on_progress')->count(),
        ];
        $personalStats['completion_rate'] = $personalStats['total'] > 0
            ? round(($personalStats['done'] / $personalStats['total']) * 100) : 0;

        // Notifications (unread)
        $notifications = AppNotification::where('user_id', $user->id)
            ->where('read_status', false)
            ->latest()
            ->take(5)
            ->get();

        $data = compact('user', 'partner', 'todayMood', 'streak', 'personalTasks', 'personalStats', 'notifications');

        if ($user->hasPartner() && $partner) {
            // Shared tasks
            $sharedTasks = Task::where('type', 'shared')
                ->where(function ($q) use ($user, $partner) {
                    $q->where('user_id', $user->id)
                      ->orWhere('user_id', $partner->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhere('assigned_to', $partner->id);
                })
                ->with(['user', 'assignedTo'])
                ->latest()
                ->take(5)
                ->get();

            // Goals
            $goals = Goal::where(function ($q) use ($user, $partner) {
                $q->where('owner_id', $user->id)->orWhere('owner_id', $partner->id);
            })->latest()->take(4)->get();

            // Partner mood
            $partnerMood = $partner->todayMood();

            // Partner streak
            $partnerStreak = $partner->streak;

            // Active focus session (user or partner)
            $activeFocus = FocusSession::whereIn('user_id', [$user->id, $partner->id])
                ->where('status', 'active')
                ->latest()
                ->first();

            // Special dates
            $specialDates = SpecialDate::where('user_id', $user->id)
                ->orWhere('user_id', $partner->id)
                ->get()
                ->sortBy('days_until')
                ->take(3);

            // Next upcoming date
            $nextDate = $specialDates->first();

            // Weekly productivity data
            $weeklyData = $this->getWeeklyProductivityData($user, $partner);

            // Couple streak
            $coupleStreak = $this->getCoupleStreak($user, $partner);

            // Secret letters (unlocked & unread)
            $unlockedLetters = SecretLetter::where('receiver_id', $user->id)
                ->where('is_unlocked', true)
                ->latest()
                ->take(3)
                ->get();

            $data = array_merge($data, compact(
                'sharedTasks', 'goals', 'partnerMood', 'partnerStreak',
                'activeFocus', 'specialDates', 'nextDate', 'weeklyData',
                'coupleStreak', 'unlockedLetters'
            ));
        }

        return view('dashboard.index', $data);
    }

    private function getWeeklyProductivityData($user, $partner): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = [
                'label' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'user_tasks' => Task::where('user_id', $user->id)
                    ->where('status', 'done')
                    ->whereDate('completed_at', $date)
                    ->count(),
                'partner_tasks' => Task::where('user_id', $partner->id)
                    ->where('status', 'done')
                    ->whereDate('completed_at', $date)
                    ->count(),
            ];
        }
        return $days;
    }

    private function getCoupleStreak($user, $partner): int
    {
        $streak = 0;
        $date = Carbon::today();

        for ($i = 0; $i < 365; $i++) {
            $checkDate = $date->copy()->subDays($i);
            $userDone = Task::where('user_id', $user->id)
                ->where('status', 'done')
                ->whereDate('completed_at', $checkDate)
                ->exists();
            $partnerDone = Task::where('user_id', $partner->id)
                ->where('status', 'done')
                ->whereDate('completed_at', $checkDate)
                ->exists();

            if ($userDone && $partnerDone) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}