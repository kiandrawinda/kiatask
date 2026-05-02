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
