@php
    $columnCount = max(1, count($headers));
    foreach ($rows as $row) {
        $columnCount = max($columnCount, count($row));
    }
@endphp
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
        .spacer-row td { border: 0; height: 12px; padding: 0; }
    </style>
</head>
<body>
    <h1>{{ $reportName }}</h1>
    <table>
        @if($headers !== [])
            <thead>
                <tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr>
            </thead>
        @endif
        <tbody>
            @foreach($rows as $row)
                @if($row === [])
                    <tr class="spacer-row"><td colspan="{{ $columnCount }}">&nbsp;</td></tr>
                @else
                    <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>
