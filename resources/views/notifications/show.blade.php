@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Notification Details</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ data_get($data, 'url', url('dashboard')) }}">Notifications</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                @if (data_get($data, 'download_url'))
                    <a href="{{ data_get($data, 'download_url') }}" class="btn btn-primary"><i class="ti ti-download me-2"></i>Download</a>
                @endif
                <a href="{{ data_get($data, 'url', url('dashboard')) }}" class="btn btn-light"><i class="ti ti-arrow-left me-2"></i>Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">{{ data_get($data, 'title', 'Notification') }}</h5>
                <span class="text-muted fs-13">{{ optional($notification->created_at)->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <p class="mb-4">{{ data_get($data, 'message', '') }}</p>

                @if ($previewError)
                    <div class="alert alert-warning d-flex align-items-center mb-0">
                        <i class="ti ti-alert-circle me-2"></i>{{ $previewError }}
                    </div>
                @elseif ($preview)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Export Preview</h6>
                        <span class="text-muted fs-13">Showing up to 1,000 rows</span>
                    </div>
                    <div class="table-responsive border rounded">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    @foreach ($preview['headers'] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($preview['rows'] as $row)
                                    <tr>
                                        @foreach ($preview['headers'] as $index => $header)
                                            <td>{{ $row[$index] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ max(count($preview['headers']), 1) }}" class="text-center text-muted py-4">The export contains no records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
