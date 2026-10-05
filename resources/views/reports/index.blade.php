@extends('layouts.app')

@section('title', 'Rapor')

@section('content')
    <h1 class="h3 mb-3">Rapor</h1>
    <div class="app-panel report-chart mb-3">
        <canvas id="report-chart"></canvas>
    </div>
    <script type="application/json" id="report-chart-data">@json($chart)</script>
    <div class="row g-3">
        @foreach ($reports as $report)
            <div class="col-6 col-lg-4">
                <article class="app-panel h-100">
                    <p class="text-secondary mb-1">{{ $report['title'] }}</p>
                    <p class="h4 mb-1">{{ $report['value'] }}</p>
                    <p class="small mb-0">{{ $report['hint'] }}</p>
                </article>
            </div>
        @endforeach
    </div>
@endsection
