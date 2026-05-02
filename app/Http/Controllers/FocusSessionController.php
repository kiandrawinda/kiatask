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
 