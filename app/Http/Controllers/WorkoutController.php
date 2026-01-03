<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function index()
    {
        try {
            Log::debug('WorkoutController@index called', [
                'auth_id' => Auth::id(),
                'headers' => request()->headers->all(),
                'cookies' => array_keys(request()->cookie()),
                'ip' => request()->ip(),
            ]);

            return Workout::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('WorkoutController@index exception', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to fetch workouts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sets' => 'required|integer|min:1',
            'reps' => 'required|integer|min:1',
            'date' => 'required|date',
            'time' => 'required',
            'calories' => 'required|integer|min:0',
            'completed' => 'boolean',
            'duration' => 'nullable|integer|min:0',
            'muscle_group' => 'nullable|string|max:255'
        ]);

        $validated['user_id'] = Auth::id();

        // Save workout
        $workout = Workout::create($validated);

        // 🔔 Create workout notification
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "🏋️ Time for your workout: {$request->name}",
            'scheduled_at' => $request->date . ' ' . $request->time
        ]);

        return response()->json($workout, 201);
    }

    public function update(Request $request, Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sets' => 'required|integer|min:1',
            'reps' => 'required|integer|min:1',
            'date' => 'required|date',
            'time' => 'required',
            'calories' => 'required|integer|min:0',
            'completed' => 'boolean',
            'duration' => 'nullable|integer|min:0',
            'muscle_group' => 'nullable|string|max:255'
        ]);

        $workout->update($validated);

        return response()->json($workout);
    }

    public function destroy(Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $workout->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function complete(Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $workout->completed = true;
        $workout->save();

        return response()->json($workout);
    }
}
