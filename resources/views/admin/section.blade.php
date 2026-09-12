@extends('layouts.admin')

@section('title', $title.' | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 align-self-center">
            <div class="sub-header mt-3 py-3 px-3 align-self-center d-sm-flex w-100 rounded">
                <div class="w-sm-100 mr-auto"><h4 class="mb-0">{{ $title }}</h4><b>{{ $description }}</b></div>
                <ol class="breadcrumb bg-transparent align-self-center m-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">{{ $title }}</li></ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"><h4 class="card-title">{{ $title }}</h4><a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">Back to dashboard</a></div>
                <div class="card-body">
                    @if ($headers)
                        <div class="table-responsive">
                            <table class="table mb-0 text-nowrap">
                                <thead><tr>@foreach ($headers as $header)<th class="border-top-0">{{ $header }}</th>@endforeach</tr></thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                                    @empty
                                        <tr><td colspan="{{ count($headers) }}" class="text-center text-muted py-4">No {{ strtolower($title) }} found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5"><i class="icon-settings display-3 color-primary"></i><h4 class="mt-3">{{ $title }} module</h4><p class="text-muted mb-0">{{ $description }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

