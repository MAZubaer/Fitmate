<?php

namespace App\Http\Controllers;

use App\Models\BmiHistory;
use App\Models\Meal;
use App\Models\StepHistory;
use App\Models\WaterHistory;
use App\Models\Workout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsExportController extends Controller
{
    public function csv(Request $request)
    {
        [$from, $to] = $this->range($request);
        $data = $this->getData($from, $to);

        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Date', 'Type', 'Name', 'Calories In', 'Calories Out', 'Value']);

        foreach ($data['meals'] as $m) {
            fputcsv($out, [$this->day($m->meal_date), 'Meal', $this->cell($m->name), $m->calories, '', '']);
        }
        foreach ($data['workouts'] as $w) {
            fputcsv($out, [$this->day($w->date), 'Workout', $this->cell($w->name), '', $w->calories, '']);
        }
        foreach ($data['steps'] as $s) {
            fputcsv($out, [$this->day($s->date), 'Steps', 'Steps', '', '', $s->steps]);
        }
        foreach ($data['water'] as $w) {
            fputcsv($out, [$this->day($w->date), 'Water', 'Water', '', '', $w->amount]);
        }
        fputcsv($out, $data['bmi']
            ? [$this->day($data['bmi']->date), 'BMI', 'BMI', '', '', $data['bmi']->bmi]
            : [$to, 'BMI', 'BMI', '', '', 'Not updated']);

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=health_data_{$from}_to_{$to}.csv");
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);

        $pdf = Pdf::loadView('exports.analytics', [
            'data' => $this->getData($from, $to),
            'user' => Auth::user(),
            'from' => $from,
            'to' => $to,
        ]);

        return $pdf->download("health_report_{$from}_to_{$to}.pdf");
    }

    /** @return array{0: string, 1: string} */
    private function range(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        return [$validated['from'], $validated['to']];
    }

    /** Everything is filtered by the day it happened, not when the row was created. */
    private function getData(string $from, string $to): array
    {
        $userId = Auth::id();

        return [
            'meals' => Meal::where('user_id', $userId)
                ->whereDate('meal_date', '>=', $from)->whereDate('meal_date', '<=', $to)
                ->orderBy('meal_date')->orderBy('meal_time')->get(),

            'workouts' => Workout::where('user_id', $userId)
                ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
                ->orderBy('date')->orderBy('time')->get(),

            'steps' => StepHistory::where('user_id', $userId)
                ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
                ->orderBy('date')->get(),

            'water' => WaterHistory::where('user_id', $userId)
                ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
                ->orderBy('date')->get(),

            // The most recent BMI on or before the end of the range.
            'bmi' => BmiHistory::where('user_id', $userId)
                ->whereDate('date', '<=', $to)
                ->orderByDesc('date')->orderByDesc('id')
                ->first(),
        ];
    }

    private function day(mixed $date): string
    {
        return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : substr((string) $date, 0, 10);
    }

    /**
     * Stop spreadsheet apps from running user text as a formula
     * (a meal named "=HYPERLINK(...)" would otherwise execute).
     */
    private function cell(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
