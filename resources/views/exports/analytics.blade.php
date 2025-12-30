<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h2>Health Analytics Report</h2>
<p>User: {{ $user->name }}</p>

<table>
<tr>
    <th>Date</th>
    <th>Type</th>
    <th>Name</th>
    <th>Calories / Value</th>
</tr>

@foreach($data['meals'] as $m)
<tr><td>{{ $m->meal_date }}</td><td>Meal</td><td>{{ $m->name }}</td><td>{{ $m->calories }}</td></tr>
@endforeach

@foreach($data['steps'] as $s)
<tr><td>{{ $s->date }}</td><td>Steps</td><td>Calories Burned</td><td>{{ $s->calories_burned }}</td></tr>
@endforeach

@foreach($data['bmi'] as $b)
<tr><td>{{ $b->date }}</td><td>BMI</td><td>BMI</td><td>{{ $b->bmi }}</td></tr>
@endforeach

@foreach($data['water'] as $w)
<tr><td>{{ $w->date }}</td><td>Water</td><td>Water Intake</td><td>{{ $w->amount }}</td></tr>
@endforeach

</table>

</body>
</html>
