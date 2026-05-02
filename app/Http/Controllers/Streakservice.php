<?php

namespace App\Services;

use App\Models\Streak;
use App\Models\Task;
use App\Models\AppNotification;
use App\Models\SecretLetter;
use Carbon\Carbon;

class StreakService
{
    public function updateStreak($user): void
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $completedToday = Task::where('user_id', $user->id)
            ->where('status', 'done')
            ->whereDate('completed_at', $today)
            ->exists();

        if (!$completedToday) return;

        if ($streak->last_completed_date && $streak->last_completed_date->equalTo($today)) {
            return; // Already updated today
        }

        if ($streak->last_completed_date && $streak->last_completed_date->equalTo($yesterday)) {
            $streak->current_streak++;
        } else {
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max($streak->current_streak, $streak->longest_streak);
        $streak->last_completed_date = $today;
        $streak->save();

        // Check streak milestones
        $milestones = [3, 7, 14, 30, 100];
        if (in_array($streak->current_streak, $milestones)) {
            AppNotification::create([
                'user_id' => $user->id,
                'title' => '🔥 Streak Milestone!',
                'message' => "You've maintained a {$streak->current_streak}-day streak! {$streak->badge} 🎉",
                'type' => 'success',
                'icon' => '🔥',
            ]);

            if ($user->partner_id) {
                AppNotification::create([
                    'user_id' => $user->partner_id,
                    'title' => '🔥 Partner Streak Milestone!',
                    'message' => "{$user->name} hit a {$streak->current_streak}-day streak! Give them some love 💪",
                    'type' => 'info',
                    'icon' => '🔥',
                ]);
            }
        }

        // Check streak-based letters
        $letters = SecretLetter::where('receiver_id', $user->id)
            ->where('unlock_condition', 'streak')
            ->where('unlock_streak_count', '<=', $streak->current_streak)
            ->where('is_unlocked', false)
            ->get();

        foreach ($letters as $letter) {
            $letter->update(['is_unlocked' => true, 'unlocked_at' => now()]);
            AppNotification::create([
                'user_id' => $user->id,
                'title' => '💌 Secret Letter Unlocked!',
                'message' => "Your streak unlocked a secret letter from {$letter->sender->name}! 🔓",
                'type' => 'love',
                'icon' => '💌',
            ]);
        }
    }

    public function checkDateLetters($user): void
    {
        $letters = SecretLetter::where('receiver_id', $user->id)
            ->where('unlock_condition', 'date')
            ->whereDate('unlock_date', today())
            ->where('is_unlocked', false)
            ->get();

        foreach ($letters as $letter) {
            $letter->update(['is_unlocked' => true, 'unlocked_at' => now()]);
            AppNotification::create([
                'user_id' => $user->id,
                'title' => '💌 Secret Letter Unlocked!',
                'message' => "Today is the special day! A secret letter from {$letter->sender->name} is now open 💝",
                'type' => 'love',
                'icon' => '💌',
            ]);
        }
    }
}