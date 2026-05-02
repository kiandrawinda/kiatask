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
