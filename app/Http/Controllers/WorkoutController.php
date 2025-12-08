<?php
namespace App\Http\Controllers;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    
    public function index()
    {
        
        return Workout::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sets' => 'required|integer|min:1',
            'reps' => 'required|integer|min:1',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $validated['user_id'] = Auth::id();

        $workout = Workout::create($validated);

        return response()->json($workout, 201);
    }

    
    public function update(Request $request, Workout $workout)
    {
        
        if ($workout->user_id !== Auth::id()) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sets' => 'required|integer|min:1',
            'reps' => 'required|integer|min:1',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $workout->update($validated);

        return response()->json($workout);
    }

    
    public function destroy(Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $workout->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
