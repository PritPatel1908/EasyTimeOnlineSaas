@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Notifications</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Notifications</h5>
            </div>
            <div class="card-body">
                @forelse ($notifications as $notification)
                    @php($data = $notification->data)
                    <div class="d-flex align-items-start justify-content-between border-bottom py-3">
                        <div class="me-3">
                            <a href="{{ url('/notifications/' . $notification->id) }}" class="text-dark fw-semibold">
                                {{ data_get($data, 'title', 'Notification') }}
                            </a>
                            <p class="text-muted mb-1">{{ data_get($data, 'message', '') }}</p>
                            <small class="text-muted">{{ optional($notification->created_at)->format('d M Y, h:i A') }}</small>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <a href="{{ url('/notifications/' . $notification->id) }}" class="btn btn-light btn-sm">View</a>
                            @if (data_get($data, 'download_url'))
                                <a href="{{ data_get($data, 'download_url') }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-download me-1"></i>Download
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">No notifications found.</div>
                @endforelse

                @if ($notifications->hasPages())
                    <div class="mt-3">{{ $notifications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
