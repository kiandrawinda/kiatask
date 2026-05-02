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
