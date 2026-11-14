<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    public function index()
    {
        return Workout::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $workout = Workout::create([
            ...$this->validated($request),
            'user_id' => Auth::id(),
        ]);
        $workout->syncReminder();

        return response()->json($workout, 201);
    }

    public function update(Request $request, Workout $workout)
    {
        $this->authorizeWorkout($workout);

        $workout->update($this->validated($request));
        $workout->syncReminder();

        return response()->json($workout);
    }

    public function destroy(Workout $workout)
    {
        $this->authorizeWorkout($workout);

        // Its reminder notification is removed by the foreign key cascade.
        $workout->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function complete(Workout $workout)
    {
        $this->authorizeWorkout($workout);

        $workout->completed = true;
        $workout->save();
        // Cancels the reminder if the workout was finished before it was due.
        $workout->syncReminder();

        return response()->json($workout);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sets' => 'required|integer|min:1|max:1000',
            'reps' => 'required|integer|min:1|max:10000',
            'date' => 'required|date_format:Y-m-d',
            'time' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'calories' => 'required|integer|min:0|max:20000',
            'completed' => 'boolean',
            'duration' => 'nullable|integer|min:0',
            'muscle_group' => 'nullable|string|max:255',
        ], [
            'time.regex' => 'Enter a valid time, like 07:30.',
        ]);
    }

    private function authorizeWorkout(Workout $workout): void
    {
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
