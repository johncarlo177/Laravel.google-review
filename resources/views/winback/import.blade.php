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
        max-width: 900px;
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
    .import-section {
        margin-bottom: 32px;
        padding: 24px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .import-section h3 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #1a1d29;
        font-size: 20px;
        font-weight: 600;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #374151;
        font-weight: 500;
        font-size: 14px;
    }
    .form-group input[type="file"],
    .form-group textarea,
    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #4285f4;
        box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
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
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        margin-left: 12px;
    }
    .info-box {
        background: #e3f2fd;
        padding: 16px;
        border-radius: 8px;
        border-left: 4px solid #2196f3;
        margin-bottom: 24px;
        font-size: 14px;
        color: #374151;
        line-height: 1.6;
    }
    .info-box strong {
        display: block;
        margin-bottom: 8px;
        color: #1976d2;
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
        .import-section {
            padding: 16px;
        }
        .import-section h3 {
            font-size: 18px;
        }
        .form-group textarea {
            min-height: 120px;
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
        .import-section {
            padding: 12px;
        }
        .info-box {
            padding: 12px;
            font-size: 13px;
        }
    }
</style>

<div class="winback-wrapper">
    <div class="container">
        <h1>Import Customers</h1>

        <div class="info-box">
            <strong>📋 Import Options:</strong>
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                <li><strong>CSV:</strong> Upload a CSV file with columns: name, email, phone, last_visit_date, total_spend, visit_count, lifetime_value</li>
                <li><strong>Text List:</strong> Paste a list (one per line) in format: "Name, email@example.com" or "Name, +1234567890"</li>
                <li><strong>Email List:</strong> Enter email addresses (one per line or comma-separated)</li>
            </ul>
        </div>

        <!-- CSV Import -->
        <div class="import-section">
            <h3>📄 Import from CSV</h3>
            <form method="POST" action="{{ route('winback.import.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="import_type" value="csv">
                <div class="form-group">
                    <label for="csv_file">CSV File</label>
                    <input type="file" id="csv_file" name="file" accept=".csv,.txt" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload & Import</button>
            </form>
        </div>

        <!-- Text Import -->
        <div class="import-section">
            <h3>📝 Import from Text List</h3>
            <form method="POST" action="{{ route('winback.import.store') }}">
                @csrf
                <input type="hidden" name="import_type" value="text">
                <div class="form-group">
                    <label for="text_list">Customer List (one per line)</label>
                    <textarea id="text_list" name="text" placeholder='John Doe, john@example.com&#10;Jane Smith, +1234567890&#10;Bob Johnson, bob@example.com' required></textarea>
                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Format: "Name, email@example.com" or "Name, +1234567890"</small>
                </div>
                <button type="submit" class="btn btn-primary">Import from Text</button>
            </form>
        </div>

        <!-- Email Import -->
        <div class="import-section">
            <h3>📧 Import from Email List</h3>
            <form method="POST" action="{{ route('winback.import.store') }}">
                @csrf
                <input type="hidden" name="import_type" value="email">
                <div class="form-group">
                    <label for="email_list">Email Addresses (one per line or comma-separated)</label>
                    <textarea id="email_list" name="emails" placeholder="john@example.com&#10;jane@example.com&#10;bob@example.com" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Import Emails</button>
            </form>
        </div>

        <div style="margin-top: 32px;">
            <a href="{{ route('winback.index') }}" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection

