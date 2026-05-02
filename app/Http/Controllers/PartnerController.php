<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AppNotification;

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
