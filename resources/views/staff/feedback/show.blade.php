@extends('blue.layouts.page')

@section('page-content')
<style>
    .staff-feedback-show-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 40px 20px 10px 20px;
        background: #f5f7fa;
        min-height: calc(100vh - 200px);
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #e8ecf0;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
        flex-wrap: wrap;
        gap: 16px;
    }
    .header h1 {
        margin: 0;
        color: #1a1d29;
        font-size: 28px;
        font-weight: 600;
        letter-spacing: -0.02em;
    }
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.01em;
        margin-left: 8px;
    }
    .badge-new { background: #e3f2fd; color: #1976d2; }
    .badge-resolved { background: #e8f5e9; color: #2e7d32; }
    .badge-escalated { background: #ffebee; color: #c62828; }
    .badge-high { background: #ffebee; color: #c62828; }
    .badge-medium { background: #fff3e0; color: #ef6c00; }
    .badge-low { background: #e8f5e9; color: #2e7d32; }
    .section {
        margin-bottom: 32px;
    }
    .section h2 {
        font-size: 20px;
        margin-bottom: 20px;
        color: #1a1d29;
        font-weight: 600;
        letter-spacing: -0.01em;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .info-item {
        padding: 20px;
        background: #f9fafb;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .info-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    .info-item label {
        display: block;
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .info-item .value {
        font-size: 16px;
        font-weight: 600;
        color: #1a1d29;
        line-height: 1.5;
    }
    .stars {
        color: #fbbf24;
        font-size: 24px;
        letter-spacing: 2px;
    }
    textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        min-height: 140px;
        resize: vertical;
        background: #ffffff;
        color: #1a1d29;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    textarea:focus {
        outline: none;
        border-color: #4285f4;
        box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    }
    .actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .actions-list {
        list-style: none;
        padding: 0;
    }
    .actions-list li {
        padding: 16px;
        background: #f9fafb;
        margin-bottom: 12px;
        border-radius: 8px;
        border-left: 4px solid #4285f4;
        border: 1px solid #e5e7eb;
        border-left-width: 4px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .actions-list li:hover {
        background: #f3f4f6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
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
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    table th, table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    table th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    table td {
        color: #1a1d29;
        font-size: 14px;
    }
    table tr:hover {
        background: #f9fafb;
    }
    
    /* Remove layout-gap spacing */
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }
    
    @media (max-width: 768px) {
        .staff-feedback-show-wrapper {
            padding: 40px 20px 20px 20px;
        }
        .container {
            padding: 24px;
        }
        .header {
            flex-direction: column;
            align-items: flex-start;
        }
        .header h1 {
            font-size: 24px;
        }
        .info-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .actions {
            grid-template-columns: 1fr;
        }
        .btn {
            width: 100%;
            text-align: center;
        }
        table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    @media (max-width: 480px) {
        .container {
            padding: 20px;
        }
        .section h2 {
            font-size: 18px;
        }
        .info-item {
            padding: 16px;
        }
    }
</style>

<div class="staff-feedback-show-wrapper">
    <div class="container">
        <div class="header">
            <h1>Feedback #{{ $feedback->id }}</h1>
            <div>
                <span class="badge badge-{{ $feedback->status }}">{{ ucfirst($feedback->status) }}</span>
                <span class="badge badge-{{ $feedback->urgency ?? 'low' }}">{{ ucfirst($feedback->urgency ?? 'N/A') }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Customer Feedback -->
        <div class="section">
            <h2>Customer Feedback</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Rating</label>
                    <div class="value">
                        <span class="stars">{{ str_repeat('★', $feedback->rating) }}{{ str_repeat('☆', 5 - $feedback->rating) }}</span>
                        ({{ $feedback->rating }}/5)
                    </div>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <div class="value">{{ ucfirst($feedback->category ?? 'N/A') }}</div>
                </div>
                <div class="info-item">
                    <label>Sentiment</label>
                    <div class="value">{{ ucfirst($feedback->sentiment ?? 'N/A') }}</div>
                </div>
                <div class="info-item">
                    <label>Contact</label>
                    <div class="value">{{ $feedback->contact ?? 'Not provided' }}</div>
                </div>
            </div>
            @if($feedback->comment)
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Comment</label>
                    <div class="value" style="white-space: pre-wrap;">{{ $feedback->comment }}</div>
                </div>
            @endif
        </div>

        <!-- AI Generated Reply -->
        @if($feedback->gpt_reply)
            <div class="section">
                <h2>AI Suggested Reply</h2>
                <form method="POST" action="{{ route('staff.feedback.approve-reply', $feedback->id) }}">
                    @csrf
                    <textarea name="reply" required>{{ $feedback->gpt_reply }}</textarea>
                    
                    @if($feedback->gpt_suggested_remedy)
                        <div style="margin-top: 16px; padding: 16px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 8px; border: 1px solid #bbdefb;">
                            <strong style="color: #1976d2; display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">💡 Suggested Remedy:</strong>
                            <span style="color: #1a1d29; font-size: 14px; line-height: 1.6;">{{ $feedback->gpt_suggested_remedy }}</span>
                        </div>
                    @endif
                    
                    <div style="margin-top: 16px; padding: 16px; background: #f9fafb; border-radius: 8px; font-size: 13px; color: #6b7280; border: 1px solid #e5e7eb;">
                        <strong style="color: #374151; font-weight: 600;">📋 Suggested Next Step:</strong> <span style="color: #1a1d29;">{{ $feedback->gpt_next_step }}</span>
                    </div>
                    
                    <div class="actions" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-success">Approve & Send Reply</button>
                        <a href="{{ route('staff.feedback.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </form>
            </div>
        @endif

        <!-- Operational Recommendation -->
        @if($feedback->operational_recommendation)
            <div class="section">
                <h2>🤖 AI Operational Recommendation</h2>
                <div style="padding: 16px; background: #fffbeb; border-left: 4px solid #fbbf24; border-radius: 8px; color: #92400e; border: 1px solid #fde68a;">
                    <strong style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">System Suggestion:</strong>
                    <p style="margin: 0; line-height: 1.6; font-size: 14px; color: #78350f;">{{ $feedback->operational_recommendation }}</p>
                </div>
            </div>
        @endif

        <!-- Recovery Plan -->
        @if($feedback->gpt_actions && count($feedback->gpt_actions) > 0)
            <div class="section">
                <h2>Recovery Plan</h2>
                <ul class="actions-list">
                    @foreach($feedback->gpt_actions as $action)
                        <li>
                            <strong>{{ ucfirst($action['priority'] ?? 'medium') }} Priority:</strong>
                            {{ $action['action'] ?? 'N/A' }}<br>
                            <small>Assigned to: {{ $action['assigned_to'] ?? 'N/A' }} | ETA: {{ $action['eta'] ?? 'N/A' }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Follow-up Status -->
        <div class="section">
            <h2>Follow-up Status</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Follow-up Scheduled</label>
                    <div class="value">{{ $feedback->followup_scheduled_at ? $feedback->followup_scheduled_at->format('M d, Y H:i') : 'Not scheduled' }}</div>
                </div>
                <div class="info-item">
                    <label>Follow-up Sent</label>
                    <div class="value">{{ $feedback->followup_sent_at ? $feedback->followup_sent_at->format('M d, Y H:i') : 'Not sent' }}</div>
                </div>
                <div class="info-item">
                    <label>Customer Satisfied</label>
                    <div class="value">
                        @if($feedback->customer_satisfied === true)
                            <span style="color: #10b981; font-weight: 600;">✓ Yes</span>
                        @elseif($feedback->customer_satisfied === false)
                            <span style="color: #ef4444; font-weight: 600;">✗ No</span>
                        @else
                            <span style="color: #6b7280;">Pending</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <label>Google Review Requested</label>
                    <div class="value">
                        @if($feedback->google_review_requested)
                            <span style="color: #10b981; font-weight: 600;">✓ Yes</span>
                        @else
                            <span style="color: #9ca3af;">Not yet</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="section">
            <h2>Actions</h2>
            <div class="actions">
                @if($feedback->status !== 'resolved')
                    <form method="POST" action="{{ route('staff.feedback.resolve', $feedback->id) }}" style="display: inline; width: 100%;">
                        @csrf
                        <button type="submit" class="btn btn-success" style="width: 100%;">Mark as Resolved</button>
                    </form>
                @endif
                <a href="{{ route('staff.feedback.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        <!-- Audit Log -->
        @if($feedback->auditLogs && $feedback->auditLogs->count() > 0)
            <div class="section">
                <h2>Audit Log</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Date</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feedback->auditLogs as $log)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

