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
 

