<?php $__env->startSection('page-content'); ?>
<style>
    .feedback-test-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 900px;
        margin: 40px auto;
        padding: 24px;
        background: #f5f7fa;
        min-height: calc(100vh - 200px);
    }
    .card {
        background: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
        color: #1a1d29;
        border: 1px solid #e8ecf0;
    }
    h1 {
        color: #1a1d29;
        margin-bottom: 24px;
        font-size: 28px;
        font-weight: 600;
        letter-spacing: -0.02em;
    }
    h2 {
        color: #1a1d29;
        font-size: 20px;
        font-weight: 600;
        margin-top: 32px;
        margin-bottom: 16px;
        letter-spacing: -0.01em;
    }
    .info {
        background: #e3f2fd;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        border-left: 4px solid #2196f3;
    }
    .info strong {
        display: block;
        margin-bottom: 8px;
        color: #1976d2;
        font-weight: 600;
        font-size: 14px;
    }
    .info div {
        color: #374151;
        margin-bottom: 6px;
        font-size: 14px;
    }
    .button {
        display: inline-block;
        background: #4285f4;
        color: #ffffff;
        padding: 14px 32px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        margin-top: 12px;
        font-size: 15px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(66, 133, 244, 0.2);
    }
    .button:hover {
        background: #357ae8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
    }
    .url-box {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        word-break: break-all;
        font-family: 'SF Mono', 'Monaco', 'Cascadia Code', 'Roboto Mono', monospace;
        margin: 12px 0;
        border: 1px solid #e5e7eb;
        color: #1a1d29;
        font-size: 13px;
        line-height: 1.6;
    }
    ol {
        color: #374151;
        line-height: 1.8;
        padding-left: 24px;
    }
    ol li {
        margin-bottom: 8px;
    }
    
    /* Remove layout-gap spacing */
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }
    
    @media (max-width: 640px) {
        .feedback-test-wrapper {
            padding: 16px;
            margin: 20px auto;
        }
        .card {
            padding: 28px 24px;
        }
        h1 {
            font-size: 24px;
        }
        h2 {
            font-size: 18px;
        }
        .button {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="feedback-test-wrapper">
    <div class="card">
        <h1>🧪 Feedback Rating Page</h1>
        
        <div class="info">
            <strong>QR Code Found:</strong>
            <div>ID: <?php echo e($qrcode->id); ?></div>
            <div>Name: <?php echo e($qrcode->name); ?></div>
            <div>Business Name: <?php echo e($qrcode->data->businessName ?? 'N/A'); ?></div>
        </div>

        <h2>Feedback URL:</h2>
        <div class="url-box"><?php echo e($feedbackUrl); ?></div>

        <a href="<?php echo e($feedbackUrl); ?>" class="button">
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
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('blue.layouts.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/feedback/test.blade.php ENDPATH**/ ?>