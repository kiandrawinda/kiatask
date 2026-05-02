<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\AppNotification;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;
        $type = $request->get('type', 'personal');

        $query = Task::with(['user', 'assignedTo'])
            ->where('type', $type);

        if ($type === 'personal') {
            $query->where('user_id', $user->id);
        } else {
            $partnerIds = [$user->id];
            if ($partner) $partnerIds[] = $partner->id;
            $query->where(function ($q) use ($partnerIds) {
                $q->whereIn('user_id', $partnerIds)
                  ->orWhereIn('assigned_to', $partnerIds);
            });
        }

        // Filters
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $tasks = $query->orderBy('deadline')->orderByRaw("FIELD(priority, 'high','medium','low')")->paginate(12);

        $stats = [
            'total' => Task::where('user_id', $user->id)->where('type', $type)->count(),
            'done' => Task::where('user_id', $user->id)->where('type', $type)->where('status', 'done')->count(),
            'on_progress' => Task::where('user_id', $user->id)->where('type', $type)->where('status', 'on_progress')->count(),
            'pending' => Task::where('user_id', $user->id)->where('type', $type)->where('status', 'pending')->count(),
        ];

        return view('tasks.index', compact('tasks', 'user', 'partner', 'type', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        return view('tasks.create', compact('user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:personal,shared',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $task = Task::create([
            'user_id' => $user->id,
            'assigned_to' => $request->type === 'shared' ? ($request->assigned_to ?? $user->partner_id) : null,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => 'pending',
            'progress' => 0,
        ]);

        // Notify partner if shared task
        if ($task->type === 'shared' && $user->partner) {
            AppNotification::create([
                'user_id' => $user->partner_id,
                'title' => '📋 New Shared Task!',
                'message' => "{$user->name} created a shared task: \"{$task->title}\"",
                'type' => 'info',
                'icon' => '📋',
                'action_url' => route('tasks.show', $task),
            ]);
        }

        return redirect()->route('tasks.index', ['type' => $task->type])
            ->with('success', 'Task created successfully! 🎯');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $user = Auth::user();
        return view('tasks.edit', compact('task', 'user'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:personal,shared',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,on_progress,done',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $wasNotDone = $task->status !== 'done';
        $isNowDone = $request->status === 'done';

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => $request->status,
            'progress' => $isNowDone ? 100 : $request->progress,
            'completed_at' => ($wasNotDone && $isNowDone) ? now() : $task->completed_at,
        ]);

        // Handle task completion
        if ($wasNotDone && $isNowDone) {
            $this->handleTaskCompletion($task);
        }

        return redirect()->route('tasks.index', ['type' => $task->type])
            ->with('success', 'Task updated! ✨');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $type = $task->type;
        $task->delete();
        return redirect()->route('tasks.index', ['type' => $type])
            ->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate(['status' => 'required|in:pending,on_progress,done']);

        $wasNotDone = $task->status !== 'done';
        $isNowDone = $request->status === 'done';

        $task->update([
            'status' => $request->status,
            'progress' => $isNowDone ? 100 : ($request->status === 'on_progress' ? 50 : $task->progress),
            'completed_at' => ($wasNotDone && $isNowDone) ? now() : $task->completed_at,
        ]);

        if ($wasNotDone && $isNowDone) {
            $this->handleTaskCompletion($task);
        }

        return response()->json(['success' => true, 'task' => $task]);
    }

    private function handleTaskCompletion(Task $task): void
    {
        $user = Auth::user();
        $partner = $user->partner;

        // Update streak
        app(StreakService::class)->updateStreak($user);

        // Notify partner
        if ($task->type === 'shared' && $partner) {
            AppNotification::create([
                'user_id' => $partner->id,
                'title' => '✅ Task Completed!',
                'message' => "{$user->name} just completed \"{$task->title}\" 🎉",
                'type' => 'success',
                'icon' => '✅',
            ]);
        }

        // Check secret letters unlock
        if ($partner) {
            $letters = \App\Models\SecretLetter::where('receiver_id', $user->id)
                ->where('unlock_condition', 'task_complete')
                ->where('unlock_ref_id', $task->id)
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
                    'action_url' => route('letters.show', $letter),
                ]);
            }
        }
    }
}