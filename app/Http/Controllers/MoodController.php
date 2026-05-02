<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mood;
use App\Models\AppNotification;

class MoodController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'mood_level' => 'required|integer|min:1|max:5',
            'note'       => 'nullable|string|max:500',
        ]);

        $mood = Mood::updateOrCreate(
            ['user_id' => $user->id, 'date' => today()],
            [
                'mood_level'  => $request->mood_level,
                'mood_emoji'  => Mood::getMoodEmoji($request->mood_level),
                'note'        => $request->note,
            ]
        );

        // Notify partner if mood is low
        if ($request->mood_level <= 2 && $user->partner) {
            AppNotification::create([
                'user_id' => $user->partner_id,
                'title'   => '💌 Your partner might need you today',
                'message' => "{$user->name} is feeling {$mood->mood_label} today. Maybe send some love? 🤗",
                'type'    => 'love',
                'icon'    => '💌',
            ]);
        }

        return back()->with('success', 'Mood logged! ' . Mood::getMoodEmoji($request->mood_level));
    }

    public function history()
    {
        $user  = Auth::user();
        $moods = Mood::where('user_id', $user->id)
                     ->orderBy('date', 'desc')
                     ->paginate(30);

        return view('moods.history', compact('moods', 'user'));
    }
}