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
    .filters {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .filters select {
        padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: #ffffff;
        color: #1a1d29;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    td {
        color: #1a1d29;
        font-size: 14px;
    }
    tr:hover {
        background: #f9fafb;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-lost { background: #fee2e2; color: #991b1b; }
    .badge-dormant { background: #fef3c7; color: #92400e; }
    .badge-vip { background: #e9d5ff; color: #6b21a8; }
    .badge-one-time { background: #dbeafe; color: #1e40af; }
    .badge-failed-lead { background: #f3f4f6; color: #374151; }
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
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
    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -16px;
        padding: 0 16px;
    }
    .customer-card {
        display: none;
    }
    .customer-card-mobile {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    .customer-card-mobile .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    .customer-card-mobile .card-name {
        font-weight: 600;
        font-size: 16px;
        color: #1a1d29;
        margin-bottom: 4px;
    }
    .customer-card-mobile .card-segment {
        margin-top: 4px;
    }
    .customer-card-mobile .card-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .customer-card-mobile .card-row:last-child {
        border-bottom: none;
    }
    .customer-card-mobile .card-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    .customer-card-mobile .card-value {
        font-size: 14px;
        color: #1a1d29;
        text-align: right;
        font-weight: 500;
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
            margin-bottom: 20px;
        }
        .filters {
            flex-direction: column;
            gap: 8px;
        }
        .filters select,
        .filters button {
            width: 100%;
            margin-top: 0
        }
        .table-wrapper {
            display: none;
        }
        .mobile-cards-wrapper {
            display: block !important;
        }
        .btn {
            width: 100%;
            margin-top: 16px;
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
        .customer-card-mobile {
            padding: 12px;
        }
        .customer-card-mobile .card-name {
            font-size: 15px;
        }
    }
</style>

<div class="winback-wrapper">
    <div class="container">
        <h1>Customers</h1>

        <div class="filters">
            <form method="GET" style="display: flex; gap: 12px; align-items: center;">
                <select name="segment">
                    <option value="all" {{ $currentSegment === 'all' ? 'selected' : '' }}>All Segments</option>
                    <option value="lost" {{ $currentSegment === 'lost' ? 'selected' : '' }}>Lost (60+ days)</option>
                    <option value="dormant" {{ $currentSegment === 'dormant' ? 'selected' : '' }}>Dormant (30-59 days)</option>
                    <option value="vip" {{ $currentSegment === 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="one-time" {{ $currentSegment === 'one-time' ? 'selected' : '' }}>One-Time</option>
                    <option value="failed-lead" {{ $currentSegment === 'failed-lead' ? 'selected' : '' }}>Failed Leads</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>

        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Last Visit</th>
                    <th>Days Since</th>
                    <th>Visits</th>
                    <th>Lifetime Value</th>
                    <th>Segment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name ?? 'N/A' }}</td>
                        <td>
                            @if($customer->email)
                                <div>{{ $customer->email }}</div>
                            @endif
                            @if($customer->phone)
                                <div style="font-size: 12px; color: #6b7280;">{{ $customer->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $customer->last_visit_date ? $customer->last_visit_date->format('M d, Y') : 'Never' }}</td>
                        <td>{{ $customer->days_since_last_visit ?? 'N/A' }}</td>
                        <td>{{ $customer->visit_count }}</td>
                        <td>${{ number_format($customer->lifetime_value, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $customer->segment }}">{{ ucfirst(str_replace('-', ' ', $customer->segment ?? 'N/A')) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            No customers found. <a href="{{ route('winback.import') }}" style="color: #4285f4;">Import customers</a> to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-cards-wrapper" style="display: none;">
            @forelse($customers as $customer)
                <div class="customer-card-mobile">
                    <div class="card-header">
                        <div>
                            <div class="card-name">{{ $customer->name ?? 'N/A' }}</div>
                            <div class="card-segment">
                                <span class="badge badge-{{ $customer->segment }}">{{ ucfirst(str_replace('-', ' ', $customer->segment ?? 'N/A')) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Contact</span>
                        <span class="card-value">
                            @if($customer->email)
                                {{ $customer->email }}
                            @elseif($customer->phone)
                                {{ $customer->phone }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Last Visit</span>
                        <span class="card-value">{{ $customer->last_visit_date ? $customer->last_visit_date->format('M d, Y') : 'Never' }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Days Since</span>
                        <span class="card-value">{{ $customer->days_since_last_visit ?? 'N/A' }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Visits</span>
                        <span class="card-value">{{ $customer->visit_count }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Lifetime Value</span>
                        <span class="card-value">${{ number_format($customer->lifetime_value, 2) }}</span>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    No customers found. <a href="{{ route('winback.import') }}" style="color: #4285f4;">Import customers</a> to get started.
                </div>
            @endforelse
        </div>

        <div style="margin-top: 24px;">
            {{ $customers->links() }}
        </div>

        <div style="margin-top: 32px;">
            <a href="{{ route('winback.index') }}" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection

