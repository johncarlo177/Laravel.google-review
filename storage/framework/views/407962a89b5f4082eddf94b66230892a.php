<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Feedback Page</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .button:hover {
            background: #5568d3;
        }
        .url-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 Test Feedback Rating Page</h1>
        
        <div class="info">
            <strong>QR Code Found:</strong>
            <div>ID: <?php echo e($qrcode->id); ?></div>
            <div>Name: <?php echo e($qrcode->name); ?></div>
            <div>Business Name: <?php echo e($qrcode->data->businessName ?? 'N/A'); ?></div>
        </div>

        <h2>Feedback URL:</h2>
        <div class="url-box"><?php echo e($feedbackUrl); ?></div>

        <a href="<?php echo e($feedbackUrl); ?>" target="_blank" class="button">
            🚀 Open Feedback Rating Page
        </a>

        <h2 style="margin-top: 30px;">How to Use:</h2>
        <ol>
            <li>Click the button above to open the feedback page</li>
            <li>Test the star rating (click 1-5 stars)</li>
            <li>For 4-5 stars: You'll see Google Review button</li>
            <li>For 1-3 stars: You'll see feedback form</li>
            <li>Submit feedback to test the full flow</li>
        </ol>
    </div>
</body>
</html>

<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/feedback/test.blade.php ENDPATH**/ ?>