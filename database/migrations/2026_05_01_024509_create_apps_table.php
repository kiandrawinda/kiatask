<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Moods
        Schema::create('moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('mood_level'); // 1-5
            $table->string('mood_emoji')->nullable();
            $table->text('note')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });

        // Streaks
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_completed_date')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        // Focus Sessions
        Schema::create('focus_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(25);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Secret Letters
        Schema::create('secret_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('message');
            $table->enum('unlock_condition', ['task_complete', 'goal_reached', 'streak', 'date'])->default('task_complete');
            $table->unsignedBigInteger('unlock_ref_id')->nullable();
            $table->integer('unlock_streak_count')->nullable();
            $table->date('unlock_date')->nullable();
            $table->boolean('is_unlocked')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, love
            $table->string('icon')->nullable();
            $table->boolean('read_status')->default(false);
            $table->string('action_url')->nullable();
            $table->timestamps();
        });

        // Special Dates
        Schema::create('special_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->boolean('is_recurring')->default(true);
            $table->string('emoji')->default('💑');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_dates');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('secret_letters');
        Schema::dropIfExists('focus_sessions');
        Schema::dropIfExists('streaks');
        Schema::dropIfExists('moods');
    }
};