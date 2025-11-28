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
        margin-bottom: 8px;
        color: #1a1d29;
        font-size: 28px;
        font-weight: 600;
    }
    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
        flex-wrap: wrap;
        gap: 16px;
    }
    .campaign-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-box {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    .stat-box .label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .stat-box .value {
        font-size: 24px;
        font-weight: 700;
        color: #1a1d29;
    }
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-draft { background: #f3f4f6; color: #374151; }
    .badge-scheduled { background: #dbeafe; color: #1e40af; }
    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-completed { background: #e0e7ff; color: #3730a3; }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
    }
    .btn-primary {
        background: #4285f4;
        color: #ffffff;
    }
    .btn-success {
        background: #10b981;
        color: #ffffff;
    }
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 24px;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    th {
        background: #f9fafb;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
    }
    .message-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-sent { background: #dbeafe; color: #1e40af; }
    .status-delivered { background: #d1fae5; color: #065f46; }
    .status-failed { background: #fee2e2; color: #991b1b; }
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }
    @media (max-width: 768px) {
        .winback-wrapper {
            padding: 40px 20px 20px 20px;
        }
        .container {
            padding: 24px;
        }
        .campaign-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="winback-wrapper">
    <div class="container">
        <div class="campaign-header">
            <div>
                <h1>{{ $campaign->name }}</h1>
                <div style="color: #6b7280; font-size: 14px; margin-top: 4px;">
                    Segment: {{ ucfirst(str_replace('-', ' ', $campaign->segment_name)) }} • 
                    Created: {{ $campaign->created_at->format('M d, Y') }}
                </div>
            </div>
            <div>
                <span class="badge badge-{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span>
                @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                    <form method="POST" action="{{ route('winback.campaigns.send', $campaign->id) }}" style="display: inline; margin-left: 12px;" id="sendCampaignForm">
                        @csrf
                        <button type="submit" class="btn btn-success" id="sendCampaignBtn">Send Campaign</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="campaign-stats">
            <div class="stat-box">
                <div class="label">Total Customers</div>
                <div class="value">{{ $campaign->total_customers }}</div>
            </div>
            <div class="stat-box">
                <div class="label">Messages Sent</div>
                <div class="value">{{ $campaign->messages_sent }}</div>
            </div>
            <div class="stat-box">
                <div class="label">Responses</div>
                <div class="value">{{ $campaign->responses_received }}</div>
            </div>
            <div class="stat-box">
                <div class="label">Returned</div>
                <div class="value">{{ $campaign->customers_returned }}</div>
            </div>
            <div class="stat-box">
                <div class="label">Revenue Recovered</div>
                <div class="value">${{ number_format($campaign->revenue_recovered, 2) }}</div>
            </div>
        </div>

        <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Channel</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Response</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>
                            <div>{{ $message->customer->name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #6b7280;">
                                {{ $message->customer->email ?? $message->customer->phone ?? '' }}
                            </div>
                        </td>
                        <td>{{ strtoupper($message->channel) }}</td>
                        <td>
                            <span class="message-status status-{{ $message->status }}">
                                {{ ucfirst($message->status) }}
                            </span>
                        </td>
                        <td>{{ $message->sent_at ? $message->sent_at->format('M d, Y H:i') : 'Pending' }}</td>
                        <td>
                            @if($message->response)
                                <span style="color: #10b981;">✓ {{ ucfirst($message->response->type) }}</span>
                            @else
                                <span style="color: #6b7280;">No response</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #6b7280;">
                            No messages yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 24px;">
            {{ $messages->links() }}
        </div>

        <div style="margin-top: 32px;">
            <a href="{{ route('winback.index') }}" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
    const sendCampaignForm = document.getElementById('sendCampaignForm');
    if (sendCampaignForm) {
        sendCampaignForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('sendCampaignBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px; vertical-align: middle;"></span>Sending...';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
            }
        });
    }
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

