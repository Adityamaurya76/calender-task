<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Task;
use Validator;
use Carbon\Carbon;

class TaskController extends Controller
{

    public function index() {
        $categories = ['Work','Personal','Shopping','Health','Education'];

        return view('calender', compact('categories'));
    }

   public function fetchTasks(Request $request) {
        $date = $request->query('date');
        $start = $request->query('start');
        $end = $request->query('end');
        $priority = $request->query('priority');
        $category = $request->query('category');
        $status = $request->query('status');
        $search = $request->query('search');
        $dateRange = $request->query('date_range');
        $customStart = $request->query('custom_start');
        $customEnd = $request->query('custom_end');

        if ($date) {
            $query = Task::whereDate('due_date', $date);
            
            if ($priority) {
                $query->where('priority', $priority);
            }
            if ($category) {
                $query->where('category', $category);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $tasks = $query->orderBy('due_date')->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")->orderBy('created_at')->get();

            return response()->json(['status' => 'ok', 'tasks' => $tasks]);
        }

        
        if ($start && $end) {
            $query = Task::whereBetween('due_date', [$start, $end]);
        } else {
            $query = Task::query();
        }

        if ($dateRange) {
            $today = Carbon::today();
            switch ($dateRange) {
                case 'today': $query->whereDate('due_date', $today);
                break;
                case 'week': $query->whereBetween('due_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
                break;
                case 'month': $query->whereBetween('due_date', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
                break;
                case 'custom': if ($customStart && $customEnd) {
                        $query->whereBetween('due_date', [$customStart, $customEnd]);
                    }
                break;
            }
        }
 
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $tasks = $query->get();

        $events = $tasks->map(function($t) {

            return ['id' => $t->id, 'title' => $t->title, 'start' => $t->due_date->toDateString(), 'allDay' => true, 'extendedProps' => ['priority' => $t->priority, 'status' => $t->status, 'category' => $t->category,],];
        });

        return response()->json($events);
    }
    
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,completed',
        ]);

        if ($validate->fails()) {

            return response()->json(['status'=>'error','errors'=>$validate->errors()], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'category' => $request->category,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json(['status'=>'ok','task'=>$task]);
    }

    public function update(Request $request, Task $task)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:pending,completed',
        ]);

        if ($validate->fails()) {

           return response()->json(['status'=>'error','errors'=>$validate->errors()], 422);
        }

        $task->update($request->only(['title','description','due_date','priority','category','status']));

        return response()->json(['status'=>'ok','task'=>$task]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['status'=>'ok']);
    }

    public function toggleStatus(Task $task)
    {
        $task->status = $task->status === 'pending' ? 'completed' : 'pending';
        $task->save();
        return response()->json(['status'=>'ok', 'task' => $task]);
    }
}
