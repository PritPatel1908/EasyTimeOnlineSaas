<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $reportName }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { font-size: 16px; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #777; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>{{ $reportName }}</h1>
    <table>
        <thead>
            <tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr>
        </thead>
        <tbody></tbody>
    </table>
</body>
</html>
