@props(['headers', 'rows'])
<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table mb-0 text-nowrap']) }}>
        <thead><tr>@foreach ($headers as $header)<th class="border-top-0" scope="col">{{ $header }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</div>
