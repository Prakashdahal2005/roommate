@extends('admin.layouts.app')

@section('title', 'Cluster Evaluation Dashboard')

@push('styles')
<style>
.dash-container {
    max-width: 1000px;
    margin: 40px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Arial, sans-serif;
}
.dash-title {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 25px;
    color: #4f46e5;
}
.metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}
.metric-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 6px solid #4f46e5;
    transition: transform 0.2s ease;
}
.metric-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.metric-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 8px;
    color: #222;
}
.status-message {
    background: #e0f7fa;
    color: #00695c;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-size: 16px;
    border-left: 6px solid #00695c;
    animation: fadeOut 5s forwards;
}
@keyframes fadeOut {
    0% { opacity: 1; }
    80% { opacity: 1; }
    100% { opacity: 0; display: none; }
}
.btn-run {
    display: inline-block;
    padding: 12px 22px;
    background: #4f46e5;
    color: white;
    border-radius: 10px;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    margin-top: 20px;
    transition: background 0.2s ease;
}
.btn-run:hover {
    background: #4338ca;
}
pre {
    background: #eef2ff;
    padding: 15px;
    border-radius: 10px;
    overflow-x: auto;
    font-size: 14px;
}
</style>
@endpush

@section('content')
<div class="dash-container">

    <div class="dash-title">Cluster Evaluation Dashboard</div>

    {{-- Flash message --}}
    @if(session('status'))
        <div class="status-message" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="metric-grid">
        <div class="metric-box">
            <div class="metric-title">Optimal K</div>
            <div style="font-size:20px;">{{ $results['optimal_k'] ?? 'N/A' }}</div>
        </div>

        <div class="metric-box">
            <div class="metric-title">Precision Gain (%)</div>
            <div style="font-size:18px;">{{ $results['precision_gain'] ?? 'N/A' }} %</div>
        </div>

        <div class="metric-box">
            <div class="metric-title">Performance Gain (%)</div>
            <div style="font-size:18px;">{{ $results['performance_gain'] ?? 'N/A' }} %</div>
        </div>

        <div class="metric-box">
            <div class="metric-title">Silhouette Scores</div>
            <pre>{{ print_r($results['silhouette_scores'] ?? [], true) }}</pre>
        </div>
    </div>

    {{-- Warning --}}
    @if(!empty($results['warning']))
        <div class="metric-box" style="border-left-color:#ffca2c; background:#fff7e6; color:#856404;">
            ⚠️ {{ $results['warning'] }}
        </div>
    @endif

    @if($showRecalc)
    <a href="{{ route('runkmean',$results['optimal_k'] ?? 4) }}" class="btn-run">
        Run KMeans++ Recalculation
    </a>
@endif


</div>
@endsection
