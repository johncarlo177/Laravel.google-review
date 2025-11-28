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
        max-width: 1400px;
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
    .header-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        letter-spacing: -0.01em;
    }
    .btn-primary {
        background: #4285f4;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(66, 133, 244, 0.2);
    }
    .btn-primary:hover {
        background: #357ae8;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(66, 133, 244, 0.3);
    }
    .btn-success {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
    }
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: #f9fafb;
        padding: 24px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .stat-card h3 {
        margin: 0 0 12px 0;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .stat-card .value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1d29;
        letter-spacing: -0.02em;
        margin-bottom: 4px;
    }
    .stat-card .sub-value {
        font-size: 14px;
        color: #6b7280;
    }
    .segment-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .segment-card {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .segment-card:hover {
        border-color: #4285f4;
        box-shadow: 0 4px 12px rgba(66, 133, 244, 0.15);
    }
    .segment-card.lost { border-left: 4px solid #ef4444; }
    .segment-card.dormant { border-left: 4px solid #f59e0b; }
    .segment-card.vip { border-left: 4px solid #8b5cf6; }
    .segment-card.one-time { border-left: 4px solid #3b82f6; }
    .segment-card.failed-lead { border-left: 4px solid #6b7280; }
    .segment-card h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1a1d29;
    }
    .segment-card .count {
        font-size: 24px;
        font-weight: 700;
        color: #1a1d29;
        margin-bottom: 4px;
    }
    .segment-card .value {
        font-size: 14px;
        color: #6b7280;
    }
    .campaigns-list {
        margin-top: 32px;
    }
    .campaign-item {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .campaign-item:hover {
        background: #f3f4f6;
    }
    .campaign-info h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1a1d29;
    }
    .campaign-info .meta {
        font-size: 13px;
        color: #6b7280;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-draft { background: #f3f4f6; color: #374151; }
    .badge-scheduled { background: #dbeafe; color: #1e40af; }
    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-completed { background: #e0e7ff; color: #3730a3; }
    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        border: 1px solid;
    }
    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #10b981;
    }
    /* Remove layout-gap spacing */
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
        .stats-grid, .segment-cards {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .stat-card {
            padding: 16px;
        }
        .stat-card .value {
            font-size: 24px;
        }
        .segment-card {
            padding: 16px;
        }
        .segment-card .count {
            font-size: 20px;
        }
        .header-actions {
            flex-direction: column;
            gap: 8px;
        }
        .header-actions form {
            width: 100%;
        }
        .btn {
            width: 100%;
            text-align: center;
            padding: 12px 16px;
            font-size: 14px;
        }
        .campaign-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .campaign-item .campaign-info {
            width: 100%;
        }
        .campaign-item .campaign-info .meta {
            font-size: 12px;
            word-break: break-word;
        }
        /* Campaign creation form */
        form[action="{{ route('winback.campaigns.create') }}"] {
            flex-direction: column;
            gap: 16px;
        }
        form[action="{{ route('winback.campaigns.create') }}"] > div {
            width: 100% !important;
            min-width: 100% !important;
        }
        form[action="{{ route('winback.campaigns.create') }}"] button {
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
        .stat-card h3 {
            font-size: 11px;
        }
        .segment-card h4 {
            font-size: 14px;
        }
    }
</style>

<div class="winback-wrapper">
    <div class="container">
        <h1>AI Win-Back System</h1>

        @if(session('success'))
            <div class="alert alert-success" style="padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert" style="background: #fef2f2; color: #991b1b; border-color: #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <strong>⚠️ Error:</strong> {{ session('error') }}
            </div>
        @endif

        <div class="header-actions">
            <a href="{{ route('winback.import') }}" class="btn btn-primary">📥 Import Customers</a>
            <form method="POST" action="{{ route('winback.segment') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">🔄 Run Segmentation</button>
            </form>
            <a href="{{ route('winback.customers') }}" class="btn btn-secondary">👥 View Customers</a>
            <a href="{{ route('winback.report') }}" class="btn btn-secondary">📊 Revenue Report</a>
        </div>

        <!-- Revenue Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Revenue Recovered</h3>
                <div class="value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="sub-value">From win-back campaigns</div>
            </div>
            <div class="stat-card">
                <h3>Customers Returned</h3>
                <div class="value">{{ number_format($totalReturned) }}</div>
                <div class="sub-value">Reactivated customers</div>
            </div>
        </div>

        <!-- Segment Overview -->
        <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 20px; color: #1a1d29;">Customer Segments</h2>
        <div class="segment-cards">
            <div class="segment-card lost">
                <h4>Lost Customers</h4>
                <div class="count">{{ $segmentStats['lost']['count'] ?? 0 }}</div>
                <div class="value">${{ number_format($segmentStats['lost']['value'] ?? 0, 2) }} value</div>
            </div>
            <div class="segment-card dormant">
                <h4>Dormant Customers</h4>
                <div class="count">{{ $segmentStats['dormant']['count'] ?? 0 }}</div>
                <div class="value">${{ number_format($segmentStats['dormant']['value'] ?? 0, 2) }} value</div>
            </div>
            <div class="segment-card vip">
                <h4>VIP Customers</h4>
                <div class="count">{{ $segmentStats['vip']['count'] ?? 0 }}</div>
                <div class="value">${{ number_format($segmentStats['vip']['value'] ?? 0, 2) }} value</div>
            </div>
            <div class="segment-card one-time">
                <h4>One-Time Visitors</h4>
                <div class="count">{{ $segmentStats['one_time']['count'] ?? 0 }}</div>
                <div class="value">${{ number_format($segmentStats['one_time']['value'] ?? 0, 2) }} value</div>
            </div>
            <div class="segment-card failed-lead">
                <h4>Failed Leads</h4>
                <div class="count">{{ $segmentStats['failed_lead']['count'] ?? 0 }}</div>
                <div class="value">Never visited</div>
            </div>
        </div>

        <!-- Create Campaign -->
        <div style="background: #f9fafb; padding: 24px; border-radius: 8px; margin-bottom: 32px; border: 1px solid #e5e7eb;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #1a1d29;">Create New Campaign</h2>
            <form method="POST" action="{{ route('winback.campaigns.create') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                @csrf
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Campaign Name</label>
                    <input type="text" name="name" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px;" placeholder="e.g., Lost Customers Win-Back">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Segment</label>
                    <select name="segment" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                        <option value="lost">Lost Customers (60+ days)</option>
                        <option value="dormant">Dormant Customers (30-59 days)</option>
                        <option value="vip">VIP Customers</option>
                        <option value="one-time">One-Time Visitors</option>
                        <option value="failed-lead">Failed Leads</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Schedule (Optional)</label>
                    <input type="datetime-local" name="scheduled_at" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                </div>
                <button type="submit" class="btn btn-primary" id="createCampaignBtn" style="min-width: 150px;">Create Campaign</button>
            </form>
        </div>

        <script>
            document.querySelector('form[action="{{ route('winback.campaigns.create') }}"]').addEventListener('submit', function(e) {
                const btn = document.getElementById('createCampaignBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px; vertical-align: middle;"></span>Creating...';
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                }
            });
        </script>
        <style>
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <!-- Recent Campaigns -->
        <div class="campaigns-list">
            <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 20px; color: #1a1d29;">Recent Campaigns</h2>
            
            @forelse($campaigns as $campaign)
                <div class="campaign-item">
                    <div class="campaign-info">
                        <h4>{{ $campaign->name }}</h4>
                        <div class="meta">
                            {{ $campaign->messages_sent }} messages sent • 
                            {{ $campaign->responses_received }} responses • 
                            {{ $campaign->customers_returned }} returned • 
                            ${{ number_format($campaign->revenue_recovered, 2) }} recovered
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span>
                        <a href="{{ route('winback.campaigns.show', $campaign->id) }}" class="btn btn-secondary" style="margin-left: 12px; padding: 8px 16px;">View</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    No campaigns yet. Create your first campaign by importing customers and running segmentation.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

