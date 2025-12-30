<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Meal;
use App\Models\StepHistory;
use App\Models\BmiHistory;
use App\Models\WaterHistory;

class AnalyticsExportController extends Controller
{
    private function getData(Request $request)
    {
        $user = Auth::user();

        return [
            'meals' => Meal::where('user_id', $user->id)
                ->whereBetween('meal_date', [$request->from, $request->to])
                ->get(),

            'steps' => StepHistory::where('user_id', $user->id)
                ->whereBetween('date', [$request->from, $request->to])
                ->get(),

            'bmi' => BmiHistory::where('user_id', $user->id)
                ->whereBetween('date', [$request->from, $request->to])
                ->get(),

            'water' => WaterHistory::where('user_id', $user->id)
                ->whereBetween('date', [$request->from, $request->to])
                ->get(),
        ];
    }

    public function csv(Request $request)
    {
        $data = $this->getData($request);

        $csv = "Date,Type,Name,Calories\n";

        foreach ($data['meals'] as $m) {
            $csv .= "{$m->meal_date},Meal,{$m->name},{$m->calories}\n";
        }

        foreach ($data['steps'] as $s) {
            $csv .= "{$s->date},Steps,Steps Burned,{$s->calories_burned}\n";
        }

        foreach ($data['bmi'] as $b) {
            $csv .= "{$b->date},BMI,BMI Value,{$b->bmi}\n";
        }

        foreach ($data['water'] as $w) {
            $csv .= "{$w->date},Water,Water Intake,{$w->amount}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=health_data.csv');
    }

    public function pdf(Request $request)
    {
        $data = $this->getData($request);

        return view('exports.analytics', [
            'data' => $data,
            'user' => Auth::user()
        ]);
    }
}
