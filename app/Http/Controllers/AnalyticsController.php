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
