<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Meal;
use App\Models\Workout;
use App\Models\StepHistory;
use App\Models\BmiHistory;
use App\Models\WaterHistory;
use Barryvdh\DomPDF\Facade\Pdf;   // ✅ Added for PDF download

class AnalyticsExportController extends Controller
{
    private function getData(Request $request)
    {
        $user = Auth::user();
        $from = $request->from . ' 00:00:00';
        $to   = $request->to . ' 23:59:59';

        return [
            'meals' => Meal::where('user_id', $user->id)
                ->whereBetween('meal_date', [$request->from, $request->to])
                ->get(),

            'workouts' => Workout::where('user_id', $user->id)
                ->whereBetween('created_at', [$from, $to])
                ->get(),

            'steps' => StepHistory::where('user_id', $user->id)
                ->whereBetween('created_at', [$from, $to])
                ->get(),

            'water' => WaterHistory::where('user_id', $user->id)
                ->whereBetween('created_at', [$from, $to])
                ->get(),

            'bmi' => BmiHistory::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->first()
        ];
    }

    public function csv(Request $request)
    {
        $data = $this->getData($request);

        $csv = "Date,Type,Name,Calories In,Calories Out,Value\n";

        // Meals (Calories In)
        foreach ($data['meals'] as $m) {
            $csv .= "{$m->meal_date},Meal,{$m->name},{$m->calories},,\n";
        }

        // Workouts (Calories Out)
        foreach ($data['workouts'] as $w) {
            $csv .= "{$w->created_at->toDateString()},Workout,{$w->name},,{$w->calories},\n";
        }

        // Steps (no calories)
        foreach ($data['steps'] as $s) {
            $csv .= "{$s->created_at->toDateString()},Steps,Steps,,," . $s->steps . "\n";
        }

        // Water
        foreach ($data['water'] as $w) {
            $csv .= "{$w->created_at->toDateString()},Water,Water,,," . $w->amount . "\n";
        }

        // BMI
        if ($data['bmi']) {
            $csv .= "{$data['bmi']->created_at->toDateString()},BMI,BMI,,," . $data['bmi']->bmi . "\n";
        } else {
            $csv .= "{$request->to},BMI,BMI,,,Not updated\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=health_data.csv');
    }

    public function pdf(Request $request)
    {
        $data = $this->getData($request);

        $pdf = Pdf::loadView('exports.analytics', [
            'data' => $data,
            'user' => Auth::user(),
            'from' => $request->from,
            'to'   => $request->to
        ]);

        return $pdf->download('health_report_' . $request->from . '_to_' . $request->to . '.pdf');
    }
}
