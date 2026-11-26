@extends('layout.mainlayout')
@section('content')

<style>
    .dashboard-structure-scroll {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding: 20px 0;
        position: relative;
        z-index: 1;
        overflow-x: clip;
        overflow-y: clip;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        perspective: 2400px;
        perspective-origin: center center;
    }

    .widget-info-left {
        min-width: 0;
        position: relative;
        z-index: 1;
    }

    .widget-info-left > .dashboard-structure-panel {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .dashboard-structure-panel {
        position: relative;
    }

    .dashboard-widget-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        margin-bottom: 10px;
    }

    .dashboard-widget-drag-handle {
        cursor: grab;
    }

    .dashboard-widget-drag-handle:active {
        cursor: grabbing;
    }

    .dashboard-widget-locked .dashboard-widget-drag-handle {
        cursor: not-allowed;
        opacity: .55;
    }

    .dashboard-widget-sort-ghost {
        opacity: .45;
    }

    #dashboard-widget-grid,
    #dashboard-widget-grid-secondary {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        align-items: start;
        gap: 18px;
        margin: 0;
    }

    #dashboard-widget-grid > *,
    #dashboard-widget-grid-secondary > * {
        width: auto;
        max-width: 100%;
        min-width: 0;
        padding-right: 0;
        padding-left: 0;
    }

    .dashboard-widget-width-select-container {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 82px;
        min-width: 82px;
        flex: 0 0 82px;
    }

    .dashboard-widget-width-select-container .ts-wrapper {
        width: 100%;
        min-width: 0;
        flex: 1 1 auto;
    }

    .dashboard-widget-width-select-container.is-custom-width {
        display: grid;
        grid-template-columns: minmax(0, 96px) minmax(0, 80px);
        gap: 8px;
        width: 184px;
        min-width: 184px;
        flex-basis: 184px;
    }

    .dashboard-widget-width-select-container.is-custom-width .ts-wrapper {
        width: 96px !important;
        min-width: 0 !important;
        max-width: 96px;
    }

    .dashboard-widget-custom-width-input {
        display: none;
        box-sizing: border-box;
        height: 38px;
        min-height: 38px;
        width: 80px;
        min-width: 0;
        max-width: 80px;
        padding: 4px 8px;
        font-size: 12px;
        line-height: 1.5;
        text-align: center;
    }

    .dashboard-widget-width-select-container.is-custom-width .dashboard-widget-custom-width-input {
        display: block;
    }

    .dashboard-widget-width-select-container .ts-wrapper,
    .dashboard-widget-width-select-container .ts-control,
    .dashboard-widget-width-select-container .dashboard-widget-custom-width-input {
        box-sizing: border-box;
        height: 38px !important;
        min-height: 38px !important;
        align-self: stretch;
    }

    .dashboard-widget-width-select-container .ts-control {
        box-sizing: border-box;
        height: 38px;
        min-height: 38px;
        display: flex;
        align-items: center;
        padding-top: 4px;
        padding-bottom: 4px;
        font-size: 12px;
    }

    .employee-status-period-select-container {
        width: 125px;
        min-width: 125px;
        flex: 0 0 125px;
    }

    .employee-status-period-select-container .ts-wrapper,
    .employee-status-period-select-container .ts-control {
        width: 125px !important;
        min-width: 125px;
        height: 38px !important;
        min-height: 38px !important;
        box-sizing: border-box;
    }

    .employee-status-period-select-container .ts-control {
        display: flex;
        align-items: center;
        padding: 6px 10px;
        font-size: 13px;
    }

    .dashboard-structure-panel > .card-body {
        min-width: 0;
    }

    .dashboard-structure-overview {
        min-width: 0;
    }

    .dashboard-structure-panel .dashboard-structure-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr));
        gap: 16px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-left: 0;
        margin-right: 0;
    }

    .dashboard-structure-panel .dashboard-structure-layout > * {
        width: auto;
        max-width: 100%;
        padding-right: 0;
        padding-left: 0;
    }

    .dashboard-structure-column {
        grid-column: 1 / -1;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-top: 16px;
        overflow: clip;
    }

    .widget-info-right {
        position: relative;
        z-index: 2;
        min-width: 0;
        padding-right: 0;
    }

    .widget-info-right > .card {
        position: relative;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        border: 1px solid #e7eaf1;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        isolation: isolate;
    }

    .dashboard-widget-positioned-header {
        position: relative;
        padding-right: 0;
        justify-content: flex-start !important;
    }

    .dashboard-widget-positioned-header > h5 {
        flex: 0 0 100%;
        max-width: calc(100% - 78px);
    }

    .dashboard-widget-position-controls {
        position: absolute;
        top: 8px;
        right: 12px;
        z-index: 3;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dashboard-widget-position-controls > .btn {
        box-sizing: border-box;
        width: 34px;
        min-width: 34px;
        height: 34px;
        min-height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-widget-positioned-header .dashboard-widget-toolbar {
        justify-content: flex-start;
    }

    .dashboard-widget-positioned-header .dashboard-widget-header-controls {
        flex: 0 1 auto;
        justify-content: flex-start;
        margin-bottom: 0 !important;
    }

    .dashboard-widget-header-controls {
        display: flex;
        flex: 1 1 300px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        min-width: 0;
        max-width: 100%;
    }

    .dashboard-widget-header-controls > * {
        min-width: 0;
        max-width: 100%;
    }

    .dashboard-widget-title-group {
        display: flex;
        flex: 1 1 420px;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 0;
        max-width: 100%;
    }

    .widget-info-right .dashboard-widget-header-controls {
        flex: 0 1 auto;
    }

    .widget-info-right .dashboard-widget-positioned-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .widget-info-right .dashboard-widget-header-controls {
        width: auto;
        flex: 0 1 auto;
    }

    .widget-info-right .dashboard-widget-title-group {
        width: auto;
        flex: 0 1 auto;
    }

    .widget-info-right .dashboard-widget-title-group,
    .widget-info-right .dashboard-widget-filter-selects,
    .widget-info-right .dashboard-widget-header-controls {
        display: contents;
    }

    .widget-info-right .dashboard-widget-title-group h5 {
        flex: 0 0 100%;
        max-width: calc(100% - 78px);
    }

    .widget-info-right .dashboard-widget-header-controls > .dashboard-widget-width-select-container {
        width: 82px;
        min-width: 82px;
        flex: 0 0 82px;
    }

    .dashboard-widget-title-group h5 {
        margin-bottom: 0 !important;
    }

    .dashboard-widget-filter-selects {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 0;
        max-width: 100%;
    }

    .dashboard-widget-filter-select {
        min-width: 0;
    }

    .dashboard-widget-filter-select-grouping {
        width: 190px;
        flex: 0 1 190px;
    }

    .dashboard-widget-filter-select-metric {
        width: 145px;
        flex: 0 1 145px;
    }

    .dashboard-widget-filter-select .ts-wrapper {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100%;
    }

    .dashboard-widget-filter-select .ts-control {
        box-sizing: border-box;
        height: 38px;
        min-height: 38px;
        display: flex;
        align-items: center;
        font-size: 12px;
    }

    .dashboard-structure-scroll.is-animated {
        overflow-x: clip;
        overflow-y: clip;
    }

    .dashboard-structure-track {
        display: flex;
        width: max-content;
        transform-style: preserve-3d;
    }

    .dashboard-structure-track.is-moving {
        will-change: transform;
    }

    .dashboard-structure-group {
        display: flex;
        align-items: stretch;
        flex: 0 0 auto;
        gap: 22px;
        padding-right: 22px;
        transform-style: preserve-3d;
    }

    .dashboard-structure-section-label {
        display: flex;
        align-items: center;
        flex: 0 0 150px;
        font-weight: 600;
    }

    .dashboard-structure-card {
        flex: 0 0 170px;
        width: 170px;
        position: relative;
        cursor: pointer;
        transform: translateY(var(--curve-y, 0px));
        transform-style: preserve-3d;
    }

    .dashboard-structure-card > .card {
        transform: rotateY(var(--curve-rotation, 0deg));
        transform-origin: center center;
        transition: none;
        backface-visibility: hidden;
    }

    .dashboard-structure-card:hover .link-default,
    .dashboard-structure-card:focus-within .link-default {
        color: #F26522;
    }

    .employee-chart-scroll {
        height: 350px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
    }

    .employee-chart-row,
    .employee-chart-axis {
        display: grid;
        grid-template-columns: minmax(0, 40%) minmax(0, 60%);
    }

    .employee-grouping-chart {
        position: relative;
    }

    .employee-chart-row {
        align-items: center;
        height: 35px;
    }

    .employee-chart-label {
        overflow: hidden;
        padding-right: 10px;
        color: #111827;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-chart-track {
        display: flex;
        height: 100%;
        align-items: center;
        border-left: 1px solid #e5e7eb;
    }

    .employee-chart-bar-stack {
        display: flex;
        overflow: hidden;
        border-radius: 6px;
        transition: width 500ms ease-out;
    }

    .employee-chart-bar {
        height: 100%;
        transition: width 500ms ease-out, height 300ms ease;
    }

    .employee-chart-bar-present {
        background-color: #16a34a;
    }

    .employee-chart-bar-count {
        background-color: #ff8146;
    }

    .employee-chart-tooltip {
        position: absolute;
        z-index: 10;
        width: max-content;
        min-width: 130px;
        max-width: 220px;
        box-sizing: border-box;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 120ms ease;
    }

    .employee-chart-tooltip.is-visible {
        opacity: 1;
        visibility: visible;
    }

    .employee-chart-tooltip-title {
        overflow-wrap: anywhere;
        padding: 8px 10px;
        border-radius: 4px 4px 0 0;
        background: #f3f4f6;
        color: #64748b;
        font-size: 12px;
        white-space: normal;
    }

    .employee-chart-tooltip-value {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 10px;
        color: #64748b;
        font-size: 12px;
        white-space: nowrap;
    }

    .employee-chart-tooltip-dot {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-radius: 50%;
        background: #ff8146;
    }

    .employee-chart-tooltip-dot-present,
    .employee-chart-summary-dot-present {
        background: #16a34a;
    }

    .employee-chart-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 16px;
    }

    .employee-chart-summary-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .employee-chart-summary-dot-count {
        background: #ff8146;
    }

    .employee-chart-content {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 180ms ease, transform 180ms ease;
    }

    .employee-chart-content.is-changing {
        opacity: 0;
        transform: translateY(4px);
    }

    @media (max-width: 767.98px) {
        #dashboard-widget-grid > *,
        #dashboard-widget-grid-secondary > * {
            grid-column: 1 / -1 !important;
        }

        .dashboard-widget-header-controls {
            flex-basis: 100%;
            justify-content: flex-start;
        }

        .dashboard-widget-toolbar {
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .employee-chart-bar-stack,
        .employee-chart-bar,
        .employee-chart-tooltip,
        .employee-chart-content {
            transition: none;
        }
    }

    .employee-chart-ticks {
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #e5e7eb;
        padding-top: 4px;
        color: #111827;
        font-size: 12px;
    }

</style>

<!-- ========================
        Start Page Content
    ========================= -->

<div class="page-wrapper">

    <!-- Start Content -->
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Admin Dashboard</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            Dashboard
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="me-2 mb-2">
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                            data-bs-toggle="dropdown">
                            <i class="ti ti-file-export me-1"></i>Export
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                        class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                        class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-icon w-100 position-relative">
                        <span class="input-icon-addon">
                            <i class="ti ti-calendar text-gray-9"></i>
                        </span>
                        <input type="text" class="form-control yearpicker" value="2025">
                    </div>
                </div>
                <div class="ms-2 mb-2 head-icons">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <!-- Welcome Wrap -->
        <div class="card border-0">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap pb-1">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar avatar-xl avatar-rounded flex-shrink-0{{ $currentUserProfilePic ? '' : ' bg-primary' }}">
                        @if ($currentUserProfilePic)
                            <img src="{{ \Illuminate\Support\Str::startsWith($currentUserProfilePic, ['http://', 'https://']) ? $currentUserProfilePic : tenant_asset($currentUserProfilePic) }}"
                                class="rounded-circle" alt="{{ $currentUserName }}">
                        @else
                            {{ strtoupper(substr($currentUserName, 0, 1)) }}
                        @endif
                    </span>
                    <div class="ms-3">
                        <h3 class="mb-2">Welcome Back, {{ $currentUserName }}</h3>
                        @if ($canApprovePendingRequests)
                            <p>You have <a href="{{ url('requests/pending') }}"
                                    class="text-primary text-decoration-underline">{{ $pendingApprovalCount }}</a>
                                Pending Approval Requests</p>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap mb-1">
                    <a href="#" class="btn btn-white me-2 mb-2" data-bs-toggle="modal" data-bs-target="#add_project"><i
                            class="ti ti-calendar-cog me-1"></i>Add Schedule</a>
                    @if (count($applicationModules) > 0)
                            <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                data-bs-target="#add_request_modal">
                                <i class="ti ti-square-rounded-plus me-1"></i>Add Requests
                            </button>
                    @endif
                </div>
            </div>
        </div>
        <!-- /Welcome Wrap -->

        @if (count($applicationModules) > 0)
            <div class="modal fade" id="add_request_modal" tabindex="-1" aria-labelledby="addRequestModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRequestModalLabel">Select an Application</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    @foreach ($applicationModules as $module)
                                        <div class="col-md-6 col-lg-4">
                                            <a href="{{ url($module['path']) }}"
                                                class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-between text-start">
                                                <span>{{ $module['label'] }}</span>
                                                <i class="ti ti-arrow-right ms-2"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" id="dashboard-widget-grid" data-dashboard-layout-user="{{ $currentUserId }}">

            <!-- Widget Info -->
            <div class="d-flex widget-info-left"
                data-dashboard-widget="overview"
                data-dashboard-widget-label="Overview"
                data-dashboard-widget-locked="false"
                data-dashboard-widget-width="6">
                <div class="card flex-fill dashboard-structure-panel">
                    <div class="card-body dashboard-widget-positioned-header">
                        <div class="dashboard-widget-position-controls">
                            <button type="button" class="btn btn-white border btn-sm dashboard-widget-drag-handle"
                                data-dashboard-drag-handle aria-label="Move Overview section" title="Drag to move">
                                <i class="ti ti-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-white border btn-sm"
                                data-dashboard-widget-lock aria-label="Lock Overview position"
                                aria-pressed="false" title="Lock position">
                                <i class="ti ti-lock-open"></i>
                            </button>
                        </div>
                        <div class="dashboard-widget-toolbar">
                            <div class="dashboard-widget-width-select-container">
                                <select class="form-control select2 dashboard-widget-width-select"
                                    data-dashboard-width-select aria-label="Overview card width">
                                    <option value="12">1/1</option>
                                    <option value="6" selected>1/2</option>
                                    <option value="4">1/3</option>
                                    <option value="auto">Auto</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="number" class="form-control dashboard-widget-custom-width-input"
                                    data-dashboard-custom-width-input min="1" max="12" step="1" value="6" required hidden
                                    aria-label="Custom card width in columns" title="Enter 1 to 12 columns">
                            </div>
                        </div>
                        <div class="row dashboard-structure-layout">
                            <div class="col-md-4 col-sm-6 d-flex">
                                <div class="card flex-fill dashboard-structure-overview">
                                    <div class="card-body">
                                        <span class="avatar rounded-circle bg-primary mb-2">
                                            <i class="ti ti-calendar-share fs-16"></i>
                                        </span>
                                        <h6 class="fs-13 fw-medium text-default mb-1">Attendance Overview</h6>
                                        <h3 class="mb-3">{{ $attendanceCount }}/{{ $userCount }}</h3>
                                        <a href="{{ url('time-management/time-card') }}" class="link-default">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 d-flex">
                                <div class="card flex-fill dashboard-structure-overview">
                                    <div class="card-body">
                                        <span class="avatar rounded-circle bg-primary mb-2">
                                            <i class="ti ti-calendar-event fs-16"></i>
                                        </span>
                                        <h6 class="fs-13 fw-medium text-default mb-2">My Dates</h6>
                                        <p class="fs-13 text-muted mb-1">Birthdate</p>
                                        <h6 class="mb-2">{{ $currentUserDob?->format('d M Y') ?? 'Not provided' }}</h6>
                                        <p class="fs-13 text-muted mb-1">Joining Date</p>
                                        <h6 class="mb-0">{{ $currentUserJoinDate?->format('d M Y') ?? 'Not provided' }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 d-flex">
                                <div class="card flex-fill dashboard-structure-overview">
                                    <div class="card-body">
                                        <span class="avatar rounded-circle bg-primary mb-2">
                                            <i class="ti ti-cake fs-16"></i>
                                        </span>
                                        <h6 class="fs-13 fw-medium text-default mb-2">Birthdays</h6>
                                        <p class="fs-13 text-muted mb-1">Today</p>
                                        @forelse ($birthdaysToday as $birthday)
                                            <p class="fs-13 fw-medium mb-1 text-truncate" title="{{ $birthday['name'] }}">{{ $birthday['name'] }}</p>
                                        @empty
                                            <p class="fs-13 text-muted mb-2">No birthdays today</p>
                                        @endforelse
                                        <p class="fs-13 text-muted mb-1 mt-2">Tomorrow</p>
                                        @forelse ($birthdaysTomorrow as $birthday)
                                            <p class="fs-13 fw-medium mb-1 text-truncate" title="{{ $birthday['name'] }}">{{ $birthday['name'] }}</p>
                                        @empty
                                            <p class="fs-13 text-muted mb-0">No birthdays tomorrow</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @if (count($structureSections) > 0)
                                <div class="col-12 dashboard-structure-column">
                                    <div class="dashboard-structure-scroll" data-dashboard-marquee tabindex="0"
                                        role="region" aria-label="Company and employee structure counts">
                                        <div class="dashboard-structure-track" data-dashboard-marquee-track>
                                            <div class="dashboard-structure-group">
                                                @foreach ($structureSections as $section)
                                                    @foreach ($section['cards'] as $card)
                                                        <div class="dashboard-structure-card d-flex"
                                                            data-href="{{ url($card['path']) }}">
                                                            <div class="card flex-fill">
                                                                <div class="card-body">
                                                                    <span class="avatar rounded-circle bg-primary mb-2">
                                                                        <i class="ti {{ $card['icon'] }} fs-16"></i>
                                                                    </span>
                                                                    <h6 class="fs-13 fw-medium text-default mb-1">{{ $card['label'] }}</h6>
                                                                    <h3 class="mb-3">{{ $card['count'] }}</h3>
                                                                    <a href="{{ url($card['path']) }}" class="link-default">View All</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Widget Info -->

            <!-- Employees By Department -->
            <div class="d-flex widget-info-right"
                data-dashboard-widget="employee-grouping"
                data-dashboard-widget-label="Employees By master"
                data-dashboard-widget-locked="false"
                data-dashboard-widget-width="6">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap dashboard-widget-positioned-header">
                        <div class="dashboard-widget-position-controls">
                            <button type="button" class="btn btn-white border btn-sm dashboard-widget-drag-handle"
                                data-dashboard-drag-handle aria-label="Move Employees By master section"
                                title="Drag to move">
                                <i class="ti ti-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-white border btn-sm"
                                data-dashboard-widget-lock aria-label="Lock Employees By master position"
                                aria-pressed="false" title="Lock position">
                                <i class="ti ti-lock-open"></i>
                            </button>
                        </div>
                        <div class="dashboard-widget-title-group">
                            <h5 id="employee-grouping-title">Employees By Department</h5>
                            <div class="dashboard-widget-filter-selects">
                                <div class="dashboard-widget-filter-select dashboard-widget-filter-select-grouping">
                                    <select class="form-control select2" data-employee-grouping-select
                                        aria-label="Employee grouping">
                                        @foreach ($employeeGroupings as $key => $grouping)
                                            <option value="{{ $key }}" @selected($key === 'department')>
                                                By {{ $grouping['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="dashboard-widget-filter-select dashboard-widget-filter-select-metric">
                                    <select class="form-control select2" data-employee-metric-select
                                        aria-label="Employee count metric">
                                        <option value="count" selected>Count Based</option>
                                        <option value="present">Present Based</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2 dashboard-widget-header-controls">
                            <div class="dashboard-widget-width-select-container">
                                <select class="form-control select2 dashboard-widget-width-select"
                                    data-dashboard-width-select aria-label="Employees By master card width">
                                    <option value="12">1/1</option>
                                    <option value="6" selected>1/2</option>
                                    <option value="4">1/3</option>
                                    <option value="auto">Auto</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="number" class="form-control dashboard-widget-custom-width-input"
                                    data-dashboard-custom-width-input min="1" max="12" step="1" value="6" required hidden
                                    aria-label="Custom card width in columns" title="Enter 1 to 12 columns">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <script type="application/json" id="employee-grouping-data">@json($employeeGroupings)</script>
                        <div id="employee-chart-content" class="employee-chart-content">
                            <div id="emp-department" class="employee-grouping-chart">
                                <div id="employee-chart-scroll" class="employee-chart-scroll" tabindex="0"
                                    role="region" aria-label="Scrollable employee master counts">
                                    <div id="employee-chart-rows" role="list"></div>
                                </div>
                                <div class="employee-chart-axis">
                                    <div></div>
                                    <div id="employee-chart-ticks" class="employee-chart-ticks"></div>
                                </div>
                                <div id="employee-chart-tooltip" class="employee-chart-tooltip" role="tooltip"
                                    aria-hidden="true">
                                    <div id="employee-chart-tooltip-title" class="employee-chart-tooltip-title"></div>
                                    <div class="employee-chart-tooltip-value">
                                        <span id="employee-chart-tooltip-dot" class="employee-chart-tooltip-dot"></span>
                                        <span><span id="employee-chart-tooltip-label">Employee Count</span>:
                                            <strong id="employee-chart-tooltip-count"></strong></span>
                                    </div>
                                </div>
                            </div>
                            <p class="employee-chart-summary fs-13 mb-0 mt-2">
                                <i id="employee-chart-summary-dot" class="employee-chart-summary-dot employee-chart-summary-dot-count"></i>
                                <span id="employee-grouping-summary-text">Summary: {{ $employeeGroupings['department']['summaryCount'] }} employees linked to
                                    {{ count($employeeGroupings['department']['categories']) }} visible masters.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Employees By Department -->

        </div>

        <div class="row" id="dashboard-widget-grid-secondary"
            data-dashboard-layout-user="{{ $currentUserId }}">

            <!-- Total Employee -->
            <div class="d-flex"
                data-dashboard-widget="employee-status"
                data-dashboard-widget-label="Employee Status"
                data-dashboard-widget-locked="false"
                data-dashboard-widget-width="6">
                <div class="card flex-fill" data-employee-status-card aria-busy="false">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap dashboard-widget-positioned-header">
                        <div class="dashboard-widget-position-controls">
                            <button type="button" class="btn btn-white border btn-sm dashboard-widget-drag-handle"
                                data-dashboard-drag-handle aria-label="Move Employee Status section"
                                title="Drag to move">
                                <i class="ti ti-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-white border btn-sm"
                                data-dashboard-widget-lock aria-label="Lock Employee Status position"
                                aria-pressed="false" title="Lock position">
                                <i class="ti ti-lock-open"></i>
                            </button>
                        </div>
                        <h5 class="mb-2">Employee Status</h5>
                        <div class="d-flex align-items-center gap-2 mb-2 dashboard-widget-header-controls">
                            <div class="dashboard-widget-width-select-container">
                                <select class="form-control select2 dashboard-widget-width-select"
                                    data-dashboard-width-select aria-label="Employee Status card width">
                                    <option value="12">1/1</option>
                                    <option value="6" selected>1/2</option>
                                    <option value="4">1/3</option>
                                    <option value="auto">Auto</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="number" class="form-control dashboard-widget-custom-width-input"
                                    data-dashboard-custom-width-input min="1" max="12" step="1" value="6" required hidden
                                    aria-label="Custom card width in columns" title="Enter 1 to 12 columns">
                            </div>
                            <div class="employee-status-period-select-container">
                                <select class="form-control select2 employee-status-period-select"
                                    name="employee_status_period" aria-label="Employee Status period"
                                    data-employee-status-period
                                    data-employee-status-url="{{ url('dashboard/employee-status') }}"
                                    data-csrf-token="{{ csrf_token() }}">
                                    <option value="today" @selected($employeeStatusPeriod === 'today')>Today</option>
                                    <option value="yesterday" @selected($employeeStatusPeriod === 'yesterday')>Yesterday</option>
                                    <option value="this_week" @selected($employeeStatusPeriod === 'this_week')>This Week</option>
                                    <option value="last_week" @selected($employeeStatusPeriod === 'last_week')>Last Week</option>
                                    <option value="this_month" @selected($employeeStatusPeriod === 'this_month')>This Month</option>
                                    <option value="last_month" @selected($employeeStatusPeriod === 'last_month')>Last Month</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="small text-danger d-none mb-2" data-employee-status-feedback role="status"
                            aria-live="polite"></div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <p class="fs-13 mb-3">Total Employee</p>
                            <h3 class="mb-3" data-employee-status-total>{{ number_format($totalEmployees) }}</h3>
                        </div>
                        <div class="progress-stacked emp-stack mb-3">
                            <div class="progress" role="progressbar" aria-label="Present"
                                aria-valuenow="{{ (int) round($employeeStatusPercentages['present']) }}"
                                aria-valuemin="0" aria-valuemax="100"
                                data-employee-status-progress="present"
                                style="width: {{ number_format($employeeStatusPercentages['present'], 2, '.', '') }}%">
                                <div class="progress-bar bg-success"></div>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Partial Absent"
                                aria-valuenow="{{ (int) round($employeeStatusPercentages['partial_absent']) }}"
                                aria-valuemin="0" aria-valuemax="100"
                                data-employee-status-progress="partial_absent"
                                style="width: {{ number_format($employeeStatusPercentages['partial_absent'], 2, '.', '') }}%">
                                <div class="progress-bar bg-warning"></div>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Complete Absent"
                                aria-valuenow="{{ (int) round($employeeStatusPercentages['complete_absent']) }}"
                                aria-valuemin="0" aria-valuemax="100"
                                data-employee-status-progress="complete_absent"
                                style="width: {{ number_format($employeeStatusPercentages['complete_absent'], 2, '.', '') }}%">
                                <div class="progress-bar bg-danger"></div>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Leave"
                                aria-valuenow="{{ (int) round($employeeStatusPercentages['leave']) }}"
                                aria-valuemin="0" aria-valuemax="100"
                                data-employee-status-progress="leave"
                                style="width: {{ number_format($employeeStatusPercentages['leave'], 2, '.', '') }}%">
                                <div class="progress-bar bg-info"></div>
                            </div>
                        </div>
                        <div class="border mb-3">
                            <div class="row gx-0">
                                <div class="col-6">
                                    <div class="p-2 flex-fill border-end border-bottom">
                                        <p class="fs-13 mb-2"><i class="ti ti-square-rounded-filled text-success fs-12 me-2"></i>Present
                                            <span class="text-gray-9" data-employee-status-percent="present">({{ (int) round($employeeStatusPercentages['present']) }}%)</span>
                                        </p>
                                        <h2 class="fs-40 fw-bold" data-employee-status-count="present">{{ number_format($employeeStatusCounts['present']) }}</h2>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 flex-fill border-bottom text-end">
                                        <p class="fs-13 mb-2"><i class="ti ti-square-rounded-filled me-2 text-warning fs-12"></i>Partial Absent
                                            <span class="text-gray-9" data-employee-status-percent="partial_absent">({{ (int) round($employeeStatusPercentages['partial_absent']) }}%)</span>
                                        </p>
                                        <h2 class="fs-40 fw-bold" data-employee-status-count="partial_absent">{{ number_format($employeeStatusCounts['partial_absent']) }}</h2>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 flex-fill border-end">
                                        <p class="fs-13 mb-2"><i class="ti ti-square-rounded-filled me-2 text-danger fs-12"></i>Complete Absent
                                            <span class="text-gray-9" data-employee-status-percent="complete_absent">({{ (int) round($employeeStatusPercentages['complete_absent']) }}%)</span>
                                        </p>
                                        <h2 class="fs-40 fw-bold" data-employee-status-count="complete_absent">{{ number_format($employeeStatusCounts['complete_absent']) }}</h2>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 flex-fill text-end">
                                        <p class="fs-13 mb-2"><i class="ti ti-square-rounded-filled text-info me-2 fs-12"></i>Leave
                                            <span class="text-gray-9" data-employee-status-percent="leave">({{ (int) round($employeeStatusPercentages['leave']) }}%)</span>
                                        </p>
                                        <h2 class="fs-40 fw-bold" data-employee-status-count="leave">{{ number_format($employeeStatusCounts['leave']) }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0">Top Performer</h6>
                            <div class="d-flex gap-1 @if ($topPerformers->count() <= 1) d-none @endif"
                                data-employee-status-carousel-controls>
                                <button type="button" class="btn btn-white border btn-sm"
                                    data-bs-target="#employee-status-performers" data-bs-slide="prev"
                                    aria-label="Previous top performer">
                                    <i class="ti ti-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-white border btn-sm"
                                    data-bs-target="#employee-status-performers" data-bs-slide="next"
                                    aria-label="Next top performer">
                                    <i class="ti ti-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div id="employee-status-performers" class="carousel slide mb-3"
                            data-bs-interval="false">
                            <div class="carousel-inner" data-employee-status-performer-list>
                                @foreach ($topPerformers as $performer)
                                    <div class="carousel-item @if ($loop->first) active @endif">
                                        <div class="p-2 d-flex align-items-center justify-content-between border border-primary bg-primary-100 br-5 perfomer-card">
                                            <div class="d-flex align-items-center overflow-hidden">
                                                <span class="me-2 flex-shrink-0">
                                                    <i class="ti ti-award-filled text-primary fs-24"></i>
                                                </span>
                                                <span class="avatar avatar-md me-2 flex-shrink-0">
                                                    @if ($performer['profile_pic_url'])
                                                        <img src="{{ $performer['profile_pic_url'] }}"
                                                            class="rounded-circle border border-white" alt="">
                                                    @else
                                                        <span class="avatar-title rounded-circle bg-light text-dark">
                                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($performer['name'], 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </span>
                                                <div class="overflow-hidden">
                                                    <h6 class="text-truncate mb-1 fs-14 fw-medium">{{ $performer['name'] }}</h6>
                                                    <p class="fs-13 mb-0 text-truncate">{{ $performer['designation'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-end ms-2 flex-shrink-0">
                                                <p class="fs-13 mb-1">Performance</p>
                                                <h5 class="text-primary mb-0">{{ $performer['performance'] }}%</h5>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-muted mb-3 @if ($topPerformers->isNotEmpty()) d-none @endif"
                            data-employee-status-empty>No attendance data for this period.</p>
                        <a href="{{ url('time-management/time-card') }}" class="btn btn-light btn-md w-100">View All Employees</a>
                    </div>
                </div>
            </div>
            <!-- /Total Employee -->

            <!-- Attendance Overview -->
            <div class="d-flex" data-dashboard-static-widget="attendance-overview"
                data-dashboard-widget-label="Attendance Overview"
                data-dashboard-widget-width="6">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap dashboard-widget-positioned-header">
                        <div class="dashboard-widget-position-controls">
                            <button type="button" class="btn btn-white border btn-sm dashboard-widget-drag-handle"
                                data-dashboard-drag-handle aria-label="Move Attendance Overview section"
                                title="Drag to move">
                                <i class="ti ti-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-white border btn-sm"
                                data-dashboard-widget-lock aria-label="Lock Attendance Overview position"
                                aria-pressed="false" title="Lock position">
                                <i class="ti ti-lock-open"></i>
                            </button>
                        </div>
                        <h5 class="mb-2">Attendance Overview</h5>
                        <div class="d-flex align-items-center gap-2 mb-2 dashboard-widget-header-controls">
                            <div class="dashboard-widget-width-select-container">
                                <select class="form-control select2 dashboard-widget-width-select"
                                    data-dashboard-width-select aria-label="Attendance Overview card width">
                                    <option value="12">1/1</option>
                                    <option value="6" selected>1/2</option>
                                    <option value="4">1/3</option>
                                    <option value="auto">Auto</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="number" class="form-control dashboard-widget-custom-width-input"
                                    data-dashboard-custom-width-input min="1" max="12" step="1" value="6" required hidden
                                    aria-label="Custom card width in columns" title="Enter 1 to 12 columns">
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>Today
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chartjs-wrapper-demo position-relative mb-4">
                            <canvas id="attendance" height="200"></canvas>
                            <div class="position-absolute text-center attendance-canvas">
                                <p class="fs-13 mb-1">Total Attendance</p>
                                <h3>120</h3>
                            </div>
                        </div>
                        <h6 class="mb-3">Status</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-success me-1"></i>Present
                            </p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">59%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-secondary me-1"></i>Late</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">21%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-warning me-1"></i>Permission
                            </p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">2%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-danger me-1"></i>Absent</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">15%</p>
                        </div>
                        <div
                            class="bg-light br-5 box-shadow-xs p-2 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center">
                                <p class="mb-2 me-2">Total Absenties</p>
                                <div class="avatar-list-stacked avatar-group-sm mb-2">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/profiles/avatar-30.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10"
                                        href="javascript:void(0);">
                                        +1
                                    </a>
                                </div>
                            </div>
                            <a href="{{url('leaves')}}" class="fs-13 link-primary text-decoration-underline mb-2">View
                                Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Attendance Overview -->

            <!-- Clock-In/Out -->
            <div class="d-flex" data-dashboard-static-widget="clock-in-out"
                data-dashboard-widget-label="Clock-In/Out"
                data-dashboard-widget-width="6">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2 dashboard-widget-positioned-header">
                        <div class="dashboard-widget-position-controls">
                            <button type="button" class="btn btn-white border btn-sm dashboard-widget-drag-handle"
                                data-dashboard-drag-handle aria-label="Move Clock-In/Out section"
                                title="Drag to move">
                                <i class="ti ti-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-white border btn-sm"
                                data-dashboard-widget-lock aria-label="Lock Clock-In/Out position"
                                aria-pressed="false" title="Lock position">
                                <i class="ti ti-lock-open"></i>
                            </button>
                        </div>
                        <h5 class="mb-0">Clock-In/Out</h5>
                        <div class="d-flex align-items-center gap-2 dashboard-widget-header-controls">
                            <div class="dashboard-widget-width-select-container">
                                <select class="form-control select2 dashboard-widget-width-select"
                                    data-dashboard-width-select aria-label="Clock-In/Out card width">
                                    <option value="12">1/1</option>
                                    <option value="6" selected>1/2</option>
                                    <option value="4">1/3</option>
                                    <option value="auto">Auto</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="number" class="form-control dashboard-widget-custom-width-input"
                                    data-dashboard-custom-width-input min="1" max="12" step="1" value="6" required hidden
                                    aria-label="Custom card width in columns" title="Enter 1 to 12 columns">
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center border-0 fs-13 me-2"
                                    data-bs-toggle="dropdown">
                                    All Departments
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Finance</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Development</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Marketing</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>Today
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
                                            class="rounded-circle border border-2" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate">Daniel Esbella</h6>
                                        <p class="fs-13">UI/UX Designer</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="link-default me-2"><i
                                            class="ti ti-clock-share"></i></a>
                                    <span
                                        class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success"><i
                                            class="ti ti-circle-filled fs-5 me-1"></i>09:15</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3 p-2 border br-5">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
                                            class="rounded-circle border border-2" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium">Doglas Martini</h6>
                                        <p class="fs-13">Project Manager</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="link-default me-2"><i
                                            class="ti ti-clock-share"></i></a>
                                    <span
                                        class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success"><i
                                            class="ti ti-circle-filled fs-5 me-1"></i>09:36</span>
                                </div>
                            </div>
                            <div class="mb-3 p-2 border br-5">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                            <img src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}"
                                                class="rounded-circle border border-2" alt="img">
                                        </a>
                                        <div class="ms-2">
                                            <h6 class="fs-14 fw-medium text-truncate">Brian Villalobos</h6>
                                            <p class="fs-13">PHP Developer</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="link-default me-2"><i
                                                class="ti ti-clock-share"></i></a>
                                        <span
                                            class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success"><i
                                                class="ti ti-circle-filled fs-5 me-1"></i>09:15</span>
                                    </div>
                                </div>
                                <div
                                    class="d-flex align-items-center justify-content-between flex-wrap mt-2 border br-5 p-2 pb-0">
                                    <div>
                                        <p class="mb-1 d-inline-flex align-items-center"><i
                                                class="ti ti-circle-filled text-success fs-5 me-1"></i>Clock In
                                        </p>
                                        <h6 class="fs-13 fw-normal mb-2">10:30 AM</h6>
                                    </div>
                                    <div>
                                        <p class="mb-1 d-inline-flex align-items-center"><i
                                                class="ti ti-circle-filled text-danger fs-5 me-1"></i>Clock Out
                                        </p>
                                        <h6 class="fs-13 fw-normal mb-2">09:45 AM</h6>
                                    </div>
                                    <div>
                                        <p class="mb-1 d-inline-flex align-items-center"><i
                                                class="ti ti-circle-filled text-warning fs-5 me-1"></i>Production
                                        </p>
                                        <h6 class="fs-13 fw-normal mb-2">09:21 Hrs</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h6 class="mb-2">Late</h6>
                        <div
                            class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                            <div class="d-flex align-items-center">
                                <span class="avatar flex-shrink-0">
                                    <img src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
                                        class="rounded-circle border border-2" alt="img">
                                </span>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate">Anthony Lewis <span
                                            class="fs-10 fw-medium d-inline-flex align-items-center badge badge-danger"><i
                                                class="ti ti-clock-hour-11 me-1"></i>30 Min</span></h6>
                                    <p class="fs-13">Marketing Head</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="link-default me-2"><i
                                        class="ti ti-clock-share"></i></a>
                                <span class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success"><i
                                        class="ti ti-circle-filled fs-5 me-1"></i>08:35</span>
                            </div>
                        </div>
                        <a href="{{url('attendance-report')}}" class="btn btn-light btn-md w-100">View All
                            Attendance</a>
                    </div>
                </div>
            </div>
            <!-- /Clock-In/Out -->

        </div>

        <div class="row">

            <!-- Jobs Applicants -->
            <div class="col-xxl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Jobs Applicants</h5>
                        <a href="{{url('job-list')}}" class="btn btn-light btn-md mb-2">View All</a>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs tab-style-1 nav-justified d-sm-flex d-block p-0 mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" data-bs-target="#openings"
                                    aria-current="page" href="#openings" aria-selected="true" role="tab">Openings</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-medium active" data-bs-toggle="tab" data-bs-target="#applicants"
                                    href="#applicants" aria-selected="false" tabindex="-1" role="tab">Applicants</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade" id="openings">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0 bg-gray-100">
                                            <img src="{{URL::asset('build/img/icons/apple.svg')}}"
                                                class="img-fluid rounded-circle w-auto h-auto" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a
                                                    href="javascript:void(0);">Senior IOS Developer</a></p>
                                            <span class="fs-12">No of Openings : 25 </span>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);"
                                        class="btn btn-light btn-sm p-0 btn-icon d-flex align-items-center justify-content-center"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0 bg-gray-100">
                                            <img src="{{URL::asset('build/img/icons/php.svg')}}"
                                                class="img-fluid w-auto h-auto" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a
                                                    href="javascript:void(0);">Junior PHP Developer</a></p>
                                            <span class="fs-12">No of Openings : 20 </span>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);"
                                        class="btn btn-light btn-sm p-0 btn-icon d-flex align-items-center justify-content-center"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0 bg-gray-100">
                                            <img src="{{URL::asset('build/img/icons/react.svg')}}"
                                                class="img-fluid w-auto h-auto" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a
                                                    href="javascript:void(0);">Junior React Developer </a></p>
                                            <span class="fs-12">No of Openings : 30 </span>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);"
                                        class="btn btn-light btn-sm p-0 btn-icon d-flex align-items-center justify-content-center"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0 bg-gray-100">
                                            <img src="{{URL::asset('build/img/icons/laravel-icon.svg')}}"
                                                class="img-fluid w-auto h-auto" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a
                                                    href="javascript:void(0);">Senior Laravel Developer</a></p>
                                            <span class="fs-12">No of Openings : 40 </span>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);"
                                        class="btn btn-light btn-sm p-0 btn-icon d-flex align-items-center justify-content-center"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                            </div>
                            <div class="tab-pane fade show active" id="applicants">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0">
                                            <img src="{{URL::asset('build/img/users/user-09.jpg')}}"
                                                class="img-fluid rounded-circle" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a href="#">Brian
                                                    Villalobos</a></p>
                                            <span class="fs-13 d-inline-flex align-items-center">Exp : 5+
                                                Years<i
                                                    class="ti ti-circle-filled fs-4 mx-2 text-primary"></i>USA</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-secondary badge-xs">UI/UX Designer</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0">
                                            <img src="{{URL::asset('build/img/users/user-32.jpg')}}"
                                                class="img-fluid rounded-circle" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a href="#">Anthony
                                                    Lewis</a></p>
                                            <span class="fs-13 d-inline-flex align-items-center">Exp : 4+
                                                Years<i
                                                    class="ti ti-circle-filled fs-4 mx-2 text-primary"></i>USA</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-info badge-xs">Python Developer</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar overflow-hidden flex-shrink-0">
                                            <img src="{{URL::asset('build/img/users/user-32.jpg')}}"
                                                class="img-fluid rounded-circle" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a href="#">Stephan
                                                    Peralt</a></p>
                                            <span class="fs-13 d-inline-flex align-items-center">Exp : 6+
                                                Years<i
                                                    class="ti ti-circle-filled fs-4 mx-2 text-primary"></i>USA</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-pink badge-xs">Android Developer</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar overflow-hidden flex-shrink-0">
                                            <img src="{{URL::asset('build/img/users/user-34.jpg')}}"
                                                class="img-fluid rounded-circle" alt="img">
                                        </a>
                                        <div class="ms-2 overflow-hidden">
                                            <p class="text-dark fw-medium text-truncate mb-0"><a
                                                    href="javascript:void(0);">Doglas Martini</a></p>
                                            <span class="fs-13 d-inline-flex align-items-center">Exp : 2+
                                                Years<i
                                                    class="ti ti-circle-filled fs-4 mx-2 text-primary"></i>USA</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-purple badge-xs">React Developer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Jobs Applicants -->

            <!-- Employees -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Employees</h5>
                        <a href="{{url('employees')}}" class="btn btn-light btn-md mb-2">View All</a>
                    </div>
                    <div class="card-body p-0 employee-table">
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-32.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="javascript:void(0);">Anthony
                                                            Lewis</a></h6>
                                                    <span class="fs-12">Finance</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-secondary-transparent badge-xs">
                                                Finance
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-09.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="#">Brian Villalobos</a></h6>
                                                    <span class="fs-12">PHP Developer</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-danger-transparent badge-xs">Development</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-01.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="#">Stephan Peralt</a></h6>
                                                    <span class="fs-12">Executive</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-info-transparent badge-xs">Marketing</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-34.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="javascript:void(0);">Doglas
                                                            Martini</a></h6>
                                                    <span class="fs-12">Project Manager</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-purple-transparent badge-xs">Manager</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0">
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-37.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="javascript:void(0);">Anthony
                                                            Lewis</a></h6>
                                                    <span class="fs-12">UI/UX Designer</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border-0 text-end">
                                            <span class="badge badge-pink-transparent badge-xs">UI/UX
                                                Design</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Employees -->

            <!-- Todo -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Todo</h5>
                        <div class="d-flex align-items-center">
                            <div class="dropdown mb-2 me-2">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>Today
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                            <a href="#"
                                class="btn btn-primary btn-icon btn-xs rounded-circle d-flex align-items-center justify-content-center p-0 mb-2"
                                data-bs-toggle="modal" data-bs-target="#add_todo"><i class="ti ti-plus fs-16"></i></a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center todo-item border bg-white p-2 br-5">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo1">
                                <label class="form-check-label fw-medium" for="todo1">Add Holidays</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center todo-item border p-2 br-5 bg-primary-transparent">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo2">
                                <label class="form-check-label fw-medium" for="todo2">Add Meeting to
                                    Client</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center todo-item border p-2 br-5 bg-danger-transparent">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo3">
                                <label class="form-check-label fw-medium" for="todo3">Chat with Adrian</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center todo-item border p-2 br-5 bg-purple-transparent">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo4">
                                <label class="form-check-label fw-medium" for="todo4">Management Call</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center todo-item border p-2 br-5 bg-info-transparent">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo5">
                                <label class="form-check-label fw-medium" for="todo5">Add Payroll</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center todo-item border p-2 br-5 bg-warning-transparent">
                            <i class="ti ti-grid-dots me-2"></i>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo6">
                                <label class="form-check-label fw-medium" for="todo6">Add Policy for Increment
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Todo -->

        </div>

        <div class="row">

            <!-- Sales Overview -->
            <div class="col-xl-7 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Sales Overview</h5>
                        <div class="d-flex align-items-center">
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle dropdown-sm btn btn-white border-0 btn-sm d-inline-flex align-items-center fs-13 me-2"
                                    data-bs-toggle="dropdown">
                                    All Departments
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">UI/UX
                                            Designer</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">HR
                                            Manager</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Junior
                                            Tester</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>Today
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center mb-1">
                                <p class="fs-13 text-gray-9 me-3 mb-0"><i
                                        class="ti ti-square-rounded-filled me-2 text-primary"></i>Income</p>
                                <p class="fs-13 text-gray-9 mb-0"><i
                                        class="ti ti-square-rounded-filled me-2 text-gray-2"></i>Expenses</p>
                            </div>
                            <p class="fs-13 mb-1">Last Updated at 11:30PM</p>
                        </div>
                        <div id="sales-income"></div>
                    </div>
                </div>
            </div>
            <!-- /Sales Overview -->

            <!-- Invoices -->
            <div class="col-xl-5 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Invoices</h5>
                        <div class="d-flex align-items-center">
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle dropdown-sm btn btn-white btn-sm d-inline-flex align-items-center fs-13 me-2 border-0"
                                    data-bs-toggle="dropdown">
                                    Invoices
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Invoices</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>This Week
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive pt-1">
                            <table class="table table-nowrap table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('invoice-details')}}" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-39.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="{{url('invoice-details')}}">Redesign
                                                            Website</a>
                                                    </h6>
                                                    <span class="fs-13 d-inline-flex align-items-center">#INVOO2<i
                                                            class="ti ti-circle-filled fs-4 mx-1 text-primary"></i>Logistics</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fs-13 mb-1">Payment</p>
                                            <h6 class="fw-medium">$3560</h6>
                                        </td>
                                        <td class="px-0 text-end">
                                            <span
                                                class="badge badge-danger-transparent badge-xs d-inline-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-5 me-1"></i>Unpaid</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('invoice-details')}}" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-40.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="{{url('invoice-details')}}">Module
                                                            Completion</a></h6>
                                                    <span class="fs-13 d-inline-flex align-items-center">#INVOO5<i
                                                            class="ti ti-circle-filled fs-4 mx-1 text-primary"></i>Yip
                                                        Corp</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fs-13 mb-1">Payment</p>
                                            <h6 class="fw-medium">$4175</h6>
                                        </td>
                                        <td class="px-0 text-end">
                                            <span
                                                class="badge badge-danger-transparent badge-xs d-inline-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-5 me-1"></i>Unpaid</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('invoice-details')}}" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-55.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="{{url('invoice-details')}}">Change
                                                            on Emp Module</a></h6>
                                                    <span class="fs-13 d-inline-flex align-items-center">#INVOO3<i
                                                            class="ti ti-circle-filled fs-4 mx-1 text-primary"></i>Ignis
                                                        LLP</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fs-13 mb-1">Payment</p>
                                            <h6 class="fw-medium">$6985</h6>
                                        </td>
                                        <td class="px-0 text-end">
                                            <span
                                                class="badge badge-danger-transparent badge-xs d-inline-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-5 me-1"></i>Unpaid</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('invoice-details')}}" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-42.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="{{url('invoice-details')}}">Changes
                                                            on the Board</a></h6>
                                                    <span class="fs-13 d-inline-flex align-items-center">#INVOO2<i
                                                            class="ti ti-circle-filled fs-4 mx-1 text-primary"></i>Ignis
                                                        LLP</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fs-13 mb-1">Payment</p>
                                            <h6 class="fw-medium">$1457</h6>
                                        </td>
                                        <td class="px-0 text-end">
                                            <span
                                                class="badge badge-danger-transparent badge-xs d-inline-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-5 me-1"></i>Unpaid</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('invoice-details')}}" class="avatar">
                                                    <img src="{{URL::asset('build/img/users/user-44.jpg')}}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="{{url('invoice-details')}}">Hospital
                                                            Management</a>
                                                    </h6>
                                                    <span class="fs-13 d-inline-flex align-items-center">#INVOO6<i
                                                            class="ti ti-circle-filled fs-4 mx-1 text-primary"></i>HCL
                                                        Corp</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fs-13 mb-1">Payment</p>
                                            <h6 class="fw-medium">$6458</h6>
                                        </td>
                                        <td class="px-0 text-end">
                                            <span
                                                class="badge badge-success-transparent badge-xs d-inline-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-5 me-1"></i>Paid</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="{{url('invoice')}}" class="btn btn-light btn-md w-100 mt-2">View All</a>
                    </div>
                </div>
            </div>
            <!-- /Invoices -->

        </div>

        <div class="row">

            <!-- Projects -->
            <div class="col-xxl-8 col-xl-7 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Projects</h5>
                        <div class="d-flex align-items-center">
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>September
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Team</th>
                                        <th>Hours</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-001</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Office
                                                    Management App</a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
                                                        alt="img">
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">15/255 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 40%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>High
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-002</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Clinic
                                                    Management </a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                                    href="javascript:void(0);">
                                                    +1
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">15/255 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 40%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Low
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-003</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Educational
                                                    Platform</a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
                                                        alt="img">
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">40/255 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 50%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Medium
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-004</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Chat & Call
                                                    Mobile App</a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
                                                        alt="img">
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">35/155 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 50%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>High
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-005</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Travel Planning
                                                    Website</a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
                                                        alt="img">
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">50/235 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 50%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Medium
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{url('project-details')}}" class="link-default">PRO-006</a></td>
                                        <td>
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Service Booking
                                                    Software</a></h6>
                                        </td>
                                        <td>
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
                                                        alt="img">
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-1">40/255 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 50%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Low
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0"><a href="{{url('project-details')}}"
                                                class="link-default">PRO-008</a></td>
                                        <td class="border-0">
                                            <h6 class="fw-medium"><a href="{{url('project-details')}}">Travel Planning
                                                    Website</a></h6>
                                        </td>
                                        <td class="border-0">
                                            <div class="avatar-list-stacked avatar-group-sm">
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img class="border border-white"
                                                        src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}"
                                                        alt="img">
                                                </span>
                                                <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                                    href="javascript:void(0);">
                                                    +2
                                                </a>
                                            </div>
                                        </td>
                                        <td class="border-0">
                                            <p class="mb-1">15/255 Hrs</p>
                                            <div class="progress progress-xs w-100" role="progressbar"
                                                aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: 45%"></div>
                                            </div>
                                        </td>
                                        <td class="border-0">
                                            <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Medium
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Projects -->

            <!-- Tasks Statistics -->
            <div class="col-xxl-4 col-xl-5 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Tasks Statistics</h5>
                        <div class="d-flex align-items-center">
                            <div class="dropdown mb-2">
                                <a href="javascript:void(0);"
                                    class="border btn btn-white btn-md d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>This Week
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Month</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chartjs-wrapper-demo position-relative mb-4">
                            <canvas id="mySemiDonutChart" height="190"></canvas>
                            <div class="position-absolute text-center attendance-canvas">
                                <p class="fs-13 mb-1">Total Tasks</p>
                                <h3>124/165</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="border-end text-center me-2 pe-2 mb-3">
                                <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                        class="ti ti-circle-filled fs-10 me-1 text-warning"></i>Ongoing</p>
                                <h5>24%</h5>
                            </div>
                            <div class="border-end text-center me-2 pe-2 mb-3">
                                <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                        class="ti ti-circle-filled fs-10 me-1 text-info"></i>On Hold </p>
                                <h5>10%</h5>
                            </div>
                            <div class="border-end text-center me-2 pe-2 mb-3">
                                <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                        class="ti ti-circle-filled fs-10 me-1 text-danger"></i>Overdue</p>
                                <h5>16%</h5>
                            </div>
                            <div class="text-center me-2 pe-2 mb-3">
                                <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                        class="ti ti-circle-filled fs-10 me-1 text-success"></i>Ongoing</p>
                                <h5>40%</h5>
                            </div>
                        </div>
                        <div class="bg-dark br-5 p-3 pb-0 d-flex align-items-center justify-content-between hrs-card">
                            <div class="mb-2">
                                <h4 class="text-success">389/689 hrs</h4>
                                <p class="fs-13 mb-0">Spent on Overall Tasks This Week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Tasks Statistics -->

        </div>

        <div class="row">

            <!-- Schedules -->
            <div class="col-xxl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Schedules</h5>
                        <a href="{{url('candidates')}}" class="btn btn-light btn-md mb-2">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-3 br-5 mb-4">
                            <span class="badge badge-secondary badge-xs mb-1">UI/ UX Designer</span>
                            <h6 class="mb-2 text-truncate">Interview Candidates - UI/UX Designer</h6>
                            <div class="d-flex align-items-center flex-wrap">
                                <p class="fs-13 mb-1 me-2"><i class="ti ti-calendar-event me-2"></i>Thu, 15 Feb
                                    2025</p>
                                <p class="fs-13 mb-1"><i class="ti ti-clock-hour-11 me-2"></i>01:00 PM - 02:20
                                    PM</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between border-top mt-2 pt-3">
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-49.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-58.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                        href="javascript:void(0);">
                                        +3
                                    </a>
                                </div>
                                <a href="#" class="btn btn-white">Join Meeting</a>
                            </div>
                        </div>
                        <div class="bg-light p-3 br-5 mb-0">
                            <span class="badge badge-dark badge-xs mb-1">IOS Developer</span>
                            <h6 class="mb-2 text-truncate">Interview Candidates - IOS Developer</h6>
                            <div class="d-flex align-items-center flex-wrap">
                                <p class="fs-13 mb-1 me-2"><i class="ti ti-calendar-event me-2"></i>Thu, 15 Feb
                                    2025</p>
                                <p class="fs-13 mb-1"><i class="ti ti-clock-hour-11 me-2"></i>02:00 PM - 04:20
                                    PM</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between border-top mt-2 pt-3">
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-49.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white"
                                            src="{{URL::asset('build/img/users/user-58.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                        href="javascript:void(0);">
                                        +3
                                    </a>
                                </div>
                                <a href="#" class="btn btn-white">Join Meeting</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Schedules -->

            <!-- Recent Activities -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Recent Activities</h5>
                        <a href="{{url('activity')}}" class="btn btn-light btn-md mb-2">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">Matt
                                                    Morgan</a></h6>
                                            <p class="fs-13">05:30 PM</p>
                                        </div>
                                        <p class="fs-13">Added New Project <span class="text-primary">HRMS
                                                Dashboard</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">Jay
                                                    Ze</a></h6>
                                            <p class="fs-13">05:00 PM</p>
                                        </div>
                                        <p class="fs-13">Commented on Uploaded Document</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">Mary
                                                    Donald</a></h6>
                                            <p class="fs-13">05:30 PM</p>
                                        </div>
                                        <p class="fs-13">Approved Task Projects</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">George
                                                    David</a></h6>
                                            <p class="fs-13">06:00 PM</p>
                                        </div>
                                        <p class="fs-13">Requesting Access to Module Tickets</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">Aaron
                                                    Zeen</a></h6>
                                            <p class="fs-13">06:30 PM</p>
                                        </div>
                                        <p class="fs-13">Downloaded App Reportss</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="recent-item">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center w-100">
                                    <a href="javscript:void(0);" class="avatar  flex-shrink-0">
                                        <img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="rounded-circle"
                                            alt="img">
                                    </a>
                                    <div class="ms-2 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fs-medium text-truncate"><a href="javscript:void(0);">Hendry
                                                    Daniel</a></h6>
                                            <p class="fs-13">05:30 PM</p>
                                        </div>
                                        <p class="fs-13">Completed New Project <span>HMS</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Recent Activities -->

            <!-- Birthdays -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Birthdays</h5>
                        <a href="javascript:void(0);" class="btn btn-light btn-md mb-2">View All</a>
                    </div>
                    <div class="card-body pb-1">
                        <h6 class="mb-2">Today</h6>
                        @forelse ($birthdaysToday as $birthday)
                            <div class="p-2 border border-dashed rounded mb-2 birthday-card active">
                                <span class="avatar avatar-xs me-2"><i class="ti ti-cake"></i></span>
                                <span>{{ $birthday['name'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-3">No birthdays today</p>
                        @endforelse
                        <h6 class="mb-2">Tomorrow</h6>
                        @forelse ($birthdaysTomorrow as $birthday)
                            <div class="p-2 border border-dashed rounded mb-2 birthday-card">
                                <span class="avatar avatar-xs me-2"><i class="ti ti-cake"></i></span>
                                <span>{{ $birthday['name'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-3">No birthdays tomorrow</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- /Birthdays -->

        </div>

    </div>
    <!-- End Content -->

    @include('partials.footer')

</div>

<!-- ========================
        End Page Content
    ========================= -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const periodSelect = document.querySelector('[data-employee-status-period]');
                const statusCard = periodSelect && periodSelect.closest('[data-employee-status-card]');
                if (!periodSelect || !statusCard) {
                    return;
                }

                const feedback = statusCard.querySelector('[data-employee-status-feedback]');
                const performerList = statusCard.querySelector('[data-employee-status-performer-list]');
                const emptyPerformers = statusCard.querySelector('[data-employee-status-empty]');
                const carouselControls = statusCard.querySelector('[data-employee-status-carousel-controls]');
                const statusKeys = ['present', 'partial_absent', 'complete_absent', 'leave'];
                const numberFormatter = new Intl.NumberFormat();
                let confirmedPeriod = periodSelect.value;

                function renderPerformers(performers) {
                    performerList.replaceChildren();

                    performers.forEach(function (performer, index) {
                        const item = document.createElement('div');
                        item.className = 'carousel-item' + (index === 0 ? ' active' : '');

                        const card = document.createElement('div');
                        card.className = 'p-2 d-flex align-items-center justify-content-between border border-primary bg-primary-100 br-5 perfomer-card';

                        const identity = document.createElement('div');
                        identity.className = 'd-flex align-items-center overflow-hidden';
                        const award = document.createElement('span');
                        award.className = 'me-2 flex-shrink-0';
                        const awardIcon = document.createElement('i');
                        awardIcon.className = 'ti ti-award-filled text-primary fs-24';
                        award.appendChild(awardIcon);

                        const avatar = document.createElement('span');
                        avatar.className = 'avatar avatar-md me-2 flex-shrink-0';
                        if (performer.profile_pic_url) {
                            const image = document.createElement('img');
                            image.src = performer.profile_pic_url;
                            image.className = 'rounded-circle border border-white';
                            image.alt = '';
                            avatar.appendChild(image);
                        } else {
                            const initials = document.createElement('span');
                            initials.className = 'avatar-title rounded-circle bg-light text-dark';
                            initials.textContent = performer.name.slice(0, 1).toUpperCase();
                            avatar.appendChild(initials);
                        }

                        const details = document.createElement('div');
                        details.className = 'overflow-hidden';
                        const name = document.createElement('h6');
                        name.className = 'text-truncate mb-1 fs-14 fw-medium';
                        name.textContent = performer.name;
                        const designation = document.createElement('p');
                        designation.className = 'fs-13 mb-0 text-truncate';
                        designation.textContent = performer.designation;
                        details.append(name, designation);
                        identity.append(award, avatar, details);

                        const performance = document.createElement('div');
                        performance.className = 'text-end ms-2 flex-shrink-0';
                        const performanceLabel = document.createElement('p');
                        performanceLabel.className = 'fs-13 mb-1';
                        performanceLabel.textContent = 'Performance';
                        const performanceValue = document.createElement('h5');
                        performanceValue.className = 'text-primary mb-0';
                        performanceValue.textContent = performer.performance + '%';
                        performance.append(performanceLabel, performanceValue);

                        card.append(identity, performance);
                        item.appendChild(card);
                        performerList.appendChild(item);
                    });

                    emptyPerformers.classList.toggle('d-none', performers.length > 0);
                    carouselControls.classList.toggle('d-none', performers.length <= 1);
                }

                periodSelect.addEventListener('change', function () {
                    const selectedPeriod = periodSelect.value;
                    statusCard.setAttribute('aria-busy', 'true');
                    periodSelect.disabled = true;
                    feedback.classList.remove('d-none', 'text-danger');
                    feedback.classList.add('text-muted');
                    feedback.textContent = 'Updating Employee Status...';

                    fetch(periodSelect.dataset.employeeStatusUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': periodSelect.dataset.csrfToken,
                        },
                        body: JSON.stringify({ employee_status_period: selectedPeriod }),
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Employee Status request failed.');
                            }

                            return response.json();
                        })
                        .then(function (data) {
                            if (
                                !data
                                || !Number.isFinite(data.totalEmployees)
                                || data.employeeStatusPeriod !== selectedPeriod
                                || !data.employeeStatusCounts
                                || !data.employeeStatusPercentages
                                || !Array.isArray(data.topPerformers)
                                || statusKeys.some(function (key) {
                                    return !Number.isFinite(data.employeeStatusCounts[key])
                                        || !Number.isFinite(data.employeeStatusPercentages[key])
                                        || data.employeeStatusPercentages[key] < 0
                                        || data.employeeStatusPercentages[key] > 100;
                                })
                                || data.topPerformers.some(function (performer) {
                                    return !performer
                                        || typeof performer.name !== 'string'
                                        || typeof performer.designation !== 'string'
                                        || !Number.isFinite(performer.performance)
                                        || (
                                            performer.profile_pic_url !== null
                                            && typeof performer.profile_pic_url !== 'string'
                                        );
                                })
                            ) {
                                throw new Error('Employee Status response is invalid.');
                            }

                            statusCard.querySelector('[data-employee-status-total]').textContent =
                                numberFormatter.format(data.totalEmployees);

                            statusKeys.forEach(function (key) {
                                const count = data.employeeStatusCounts[key];
                                const percentage = data.employeeStatusPercentages[key];
                                statusCard.querySelector('[data-employee-status-count="' + key + '"]').textContent =
                                    numberFormatter.format(count);
                                statusCard.querySelector('[data-employee-status-percent="' + key + '"]').textContent =
                                    '(' + Math.round(percentage) + '%)';

                                const progress = statusCard.querySelector('[data-employee-status-progress="' + key + '"]');
                                progress.style.width = percentage.toFixed(2) + '%';
                                progress.setAttribute('aria-valuenow', String(Math.round(percentage)));
                            });

                            renderPerformers(data.topPerformers);
                            confirmedPeriod = data.employeeStatusPeriod;
                            feedback.classList.add('d-none');
                            feedback.textContent = '';
                        })
                        .catch(function () {
                            if (periodSelect.tomselect) {
                                periodSelect.tomselect.setValue(confirmedPeriod, true);
                            } else {
                                periodSelect.value = confirmedPeriod;
                            }

                            feedback.classList.remove('d-none', 'text-muted');
                            feedback.classList.add('text-danger');
                            feedback.textContent = 'Unable to update Employee Status. Please try again.';
                        })
                        .finally(function () {
                            periodSelect.disabled = false;
                            statusCard.setAttribute('aria-busy', 'false');
                        });
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const viewport = document.querySelector('[data-dashboard-marquee]');
                if (!viewport) {
                    return;
                }

                const track = viewport.querySelector('[data-dashboard-marquee-track]');
                if (!track) {
                    return;
                }

                const group = track.firstElementChild;
                if (!group) {
                    return;
                }

                const duplicate = group.cloneNode(true);
                duplicate.setAttribute('aria-hidden', 'true');
                duplicate.removeAttribute('inert');
                duplicate.querySelectorAll('[inert]').forEach(function (element) {
                    element.removeAttribute('inert');
                });
                duplicate.querySelectorAll('a').forEach(function (link) {
                    link.setAttribute('tabindex', '-1');
                });
                track.appendChild(duplicate);
                track.classList.add('is-moving');
                viewport.classList.add('is-animated');

                let position = 0;
                let previousTime = null;
                let paused = false;
                const speed = 35;
                const resetAt = () => group.getBoundingClientRect().width;
                const cards = Array.from(track.querySelectorAll('.dashboard-structure-card'));
                let cardCenters = [];

                track.addEventListener('click', function (event) {
                    const card = event.target.closest('.dashboard-structure-card[data-href]');
                    if (!card || !track.contains(card) || event.target.closest('a')) {
                        return;
                    }

                    window.location.assign(card.dataset.href);
                });

                function measureCardCenters() {
                    const viewportLeft = viewport.getBoundingClientRect().left;
                    cardCenters = cards.map(function (card) {
                        const curveY = card.style.getPropertyValue('--curve-y');
                        card.style.removeProperty('--curve-y');
                        const center = card.getBoundingClientRect().left + (card.offsetWidth / 2) - viewportLeft - position;
                        if (curveY) card.style.setProperty('--curve-y', curveY);
                        return center;
                    });
                }

                measureCardCenters();
                window.addEventListener('resize', measureCardCenters);

                viewport.addEventListener('mouseenter', function () {
                    paused = true;
                });
                viewport.addEventListener('mouseleave', function () {
                    paused = false;
                    previousTime = null;
                });
                viewport.addEventListener('focusin', function () {
                    paused = true;
                });
                viewport.addEventListener('focusout', function () {
                    paused = false;
                    previousTime = null;
                });

                function move(time) {
                    if (previousTime === null) {
                        previousTime = time;
                    }

                    if (!paused) {
                        position -= ((time - previousTime) / 1000) * speed;
                        const resetPosition = resetAt();
                        if (resetPosition > 0 && position <= -resetPosition) {
                            position += resetPosition;
                        }
                        track.style.transform = `translateX(${position}px)`;
                        updateCurve();
                    }

                    previousTime = time;
                    window.requestAnimationFrame(move);
                }

                function updateCurve() {
                    const viewportCenter = viewport.clientWidth / 2;

                    cards.forEach(function (card, index) {
                        const cardCenter = cardCenters[index] + position;
                        const distance = (cardCenter - viewportCenter) / Math.max(viewportCenter + (card.offsetWidth / 2), 1);
                        const edgeDistance = Math.min(Math.abs(distance), 1);
                        const rotation = Math.max(-16, Math.min(16, distance * -24));
                        const curveY = -Math.pow(edgeDistance, 1.8) * 34;
                        const face = card.querySelector('.card');
                        card.style.setProperty('--curve-y', `${curveY.toFixed(2)}px`);
                        if (face) {
                            face.style.setProperty('--curve-rotation', `${rotation.toFixed(2)}deg`);
                        }
                    });
                }

                window.requestAnimationFrame(move);
            });
        </script>
    @endpush

@endsection