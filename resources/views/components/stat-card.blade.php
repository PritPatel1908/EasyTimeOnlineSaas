@props(['icon', 'title', 'period', 'value', 'trend', 'trendClass' => 'text-success'])
<div class="col-12 col-sm-6 col-xl-3 mt-3">
    <div class="card">
        <div class="card-body">
            <img src="{{ asset('admin-dist/images/'.$icon) }}" alt="" class="float-right">
            <h6 class="card-title font-weight-bold">{{ $title }}</h6>
            <h6 class="card-subtitle mb-2 text-muted">{{ $period }}</h6>
            <h2>{{ $value }}</h2>
            <span class="{{ $trendClass }}"><i class="ion ion-android-arrow-dropup"></i> {{ $trend }}</span> than last period
        </div>
    </div>
</div>
