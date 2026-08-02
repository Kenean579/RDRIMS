<!DOCTYPE html>
<html>
<head>
    <title>Projects Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 30px; }
        h1 { color: #1a5276; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a5276; color: white; }
    </style>
</head>
<body>
    <h1>Projects Report</h1>
    <p>Generated on: {{ now()->format('F j, Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>PI</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Budget</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->title }}</td>
                <td>{{ $project->status->name ?? 'N/A' }}</td>
                <td>{{ $project->pi->name ?? 'N/A' }}</td>
                <td>{{ $project->start_date?->format('Y-m-d') ?? 'N/A' }}</td>
                <td>{{ $project->end_date?->format('Y-m-d') ?? 'N/A' }}</td>
                <td>{{ number_format($project->total_budget ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
