<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Participation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; margin: 50px; }
        .certificate { border: 5px solid #1a5276; padding: 50px; }
        h1 { color: #1a5276; font-size: 36px; margin-bottom: 20px; }
        .name { font-size: 28px; margin: 30px 0; color: #2980b9; }
        .event { font-size: 20px; margin: 20px 0; }
        .date { margin-top: 40px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Participation</h1>
        <p>This is to certify that</p>
        <div class="name">{{ $user_name }}</div>
        <p>has participated in</p>
        <div class="event"><strong>{{ $event_title }}</strong></div>
        <p>held on {{ $event_date }} at {{ $venue }}.</p>
        <div class="date">Issued on: {{ now()->format('F j, Y') }}</div>
    </div>
</body>
</html>
