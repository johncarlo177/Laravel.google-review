@extends('blue.layouts.page')

@section('page-content')
<style>
    .winback-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 40px 20px 10px 20px;
        background: #f5f7fa;
        min-height: calc(100vh - 200px);
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: #ffffff;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #e8ecf0;
    }
    h1 {
        margin-top: 0;
        margin-bottom: 32px;
        color: #1a1d29;
        font-size: 32px;
        font-weight: 600;
        letter-spacing: -0.02em;
    }
    .period-selector {
        margin-bottom: 32px;
    }
    .period-selector a {
        display: inline-block;
        padding: 8px 16px;
        margin-right: 8px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .period-selector a.active {
        background: #4285f4;
        color: #ffffff;
    }
    .period-selector a:not(.active) {
        background: #f3f4f6;
        color: #374151;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: #f9fafb;
        padding: 24px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }
    .stat-card h3 {
        margin: 0 0 12px 0;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
    }
    .stat-card .value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1d29;
    }
    .campaign-item {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
    }
    .campaign-item h4 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        color: #1a1d29;
    }
    .campaign-meta {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .campaign-stats {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }
    .campaign-stat {
        font-size: 14px;
    }
    .campaign-stat strong {
        color: #1a1d29;
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
    }
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }
    @media (max-width: 768px) {
        .winback-wrapper {
            padding: 20px 12px 20px 12px;
        }
        .container {
            padding: 20px 16px;
            border-radius: 8px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 24px;
        }
        .period-selector {
            margin-bottom: 24px;
        }
        .period-selector a {
            display: block;
            margin-bottom: 8px;
            text-align: center;
        }
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .stat-card {
            padding: 16px;
        }
        .stat-card .value {
            font-size: 24px;
        }
        .campaign-item {
            padding: 16px;
        }
        .campaign-item h4 {
            font-size: 16px;
        }
        .campaign-stats {
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            width: 100%;
        }
    }
    @media (max-width: 480px) {
        .winback-wrapper {
            padding: 16px 8px;
        }
        .container {
            padding: 16px 12px;
        }
        h1 {
            font-size: 20px;
        }
        .stat-card .value {
            font-size: 20px;
        }
        .campaign-item {
            padding: 12px;
        }
    }
</style>

<div class="winback-wrapper">
    <div class="container">
        <h1>Revenue Report</h1>

        <div class="period-selector">
            <a href="{{ route('winback.report', ['period' => 'week']) }}" class="{{ $period === 'week' ? 'active' : '' }}">This Week</a>
            <a href="{{ route('winback.report', ['period' => 'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">This Month</a>
            <a href="{{ route('winback.report', ['period' => 'year']) }}" class="{{ $period === 'year' ? 'active' : '' }}">This Year</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Revenue Recovered</h3>
                <div class="value">${{ number_format($report['total_revenue'], 2) }}</div>
            </div>
            <div class="stat-card">
                <h3>Customers Returned</h3>
                <div class="value">{{ number_format($report['total_customers_returned']) }}</div>
            </div>
            <div class="stat-card">
                <h3>Messages Sent</h3>
                <div class="value">{{ number_format($report['total_messages_sent']) }}</div>
            </div>
            <div class="stat-card">
                <h3>Response Rate</h3>
                <div class="value">
                    @if($report['total_messages_sent'] > 0)
                        {{ number_format(($report['total_responses'] / $report['total_messages_sent']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>
        </div>

        <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 20px;">Campaigns</h2>
        
        @forelse($report['campaigns'] as $campaign)
            <div class="campaign-item">
                <h4>{{ $campaign->name }}</h4>
                <div class="campaign-meta">
                    {{ $campaign->segment_name ? ucfirst(str_replace('-', ' ', $campaign->segment_name)) : 'All Segments' }} • 
                    Completed: {{ $campaign->completed_at ? $campaign->completed_at->format('M d, Y') : 'N/A' }}
                </div>
                <div class="campaign-stats">
                    <div class="campaign-stat">
                        <strong>{{ $campaign->messages_sent }}</strong> messages sent
                    </div>
                    <div class="campaign-stat">
                        <strong>{{ $campaign->responses_received }}</strong> responses
                    </div>
                    <div class="campaign-stat">
                        <strong>{{ $campaign->customers_returned }}</strong> customers returned
                    </div>
                    <div class="campaign-stat">
                        <strong>${{ number_format($campaign->revenue_recovered, 2) }}</strong> revenue recovered
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                No completed campaigns in this period.
            </div>
        @endforelse

        <div style="margin-top: 32px;">
            <a href="{{ route('winback.index') }}" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection

