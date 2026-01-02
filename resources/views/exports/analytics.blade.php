<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Health Report</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; }
        h2 { margin-bottom: 5px; }
        p { margin: 4px 0; }
        table {
            width:100%;
            border-collapse: collapse;
            margin-top:20px;
        }
        th, td {
            border:1px solid #ddd;
            padding:8px;
            text-align:left;
        }
        th {
            background:#f3f3f3;
        }
    </style>
</head>
<body>

<h2>Health Report</h2>
<p><strong>User:</strong> {{ $user->name }}</p>
<p><strong>Period:</strong> {{ $from }} - {{ $to }}</p>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Name</th>
            <th>Calories In</th>
            <th>Calories Out</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>

        {{-- Meals (Calories In) --}}
        @foreach($data['meals'] as $m)
        <tr>
            <td>{{ $m->meal_date }}</td>
            <td>Meal</td>
            <td>{{ $m->name }}</td>
            <td>{{ $m->calories }}</td>
            <td></td>
            <td></td>
        </tr>
        @endforeach

        {{-- Workouts (Calories Out) --}}
        @foreach($data['workouts'] as $w)
        <tr>
            <td>{{ $w->created_at->toDateString() }}</td>
            <td>Workout</td>
            <td>{{ $w->name }}</td>
            <td></td>
            <td>{{ $w->calories }}</td>
            <td></td>
        </tr>
        @endforeach

        {{-- Steps (Value only – no calories) --}}
        @foreach($data['steps'] as $s)
        <tr>
            <td>{{ $s->created_at->toDateString() }}</td>
            <td>Steps</td>
            <td>Steps</td>
            <td></td>
            <td></td>
            <td>{{ $s->steps }}</td>
        </tr>
        @endforeach

        {{-- Water (Value only – ml) --}}
        @foreach($data['water'] as $w)
        <tr>
            <td>{{ $w->created_at->toDateString() }}</td>
            <td>Water</td>
            <td>Water</td>
            <td></td>
            <td></td>
            <td>{{ $w->amount }}</td>
        </tr>
        @endforeach

        {{-- BMI (Latest or Not updated) --}}
        <tr>
            <td>{{ $data['bmi']?->created_at?->toDateString() ?? $to }}</td>
            <td>BMI</td>
            <td>BMI</td>
            <td></td>
            <td></td>
            <td>{{ $data['bmi']?->bmi ?? 'Not updated' }}</td>
        </tr>

    </tbody>
</table>

</body>
</html>
