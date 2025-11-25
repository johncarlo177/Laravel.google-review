<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-new { background: #e3f2fd; color: #1976d2; }
        .badge-resolved { background: #e8f5e9; color: #388e3c; }
        .badge-escalated { background: #ffebee; color: #d32f2f; }
        .badge-high { background: #ffebee; color: #d32f2f; }
        .badge-medium { background: #fff3e0; color: #f57c00; }
        .badge-low { background: #e8f5e9; color: #388e3c; }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-item .value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .stars {
            color: #ffd700;
            font-size: 24px;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            min-height: 120px;
            resize: vertical;
        }
        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .actions-list {
            list-style: none;
            padding: 0;
        }
        .actions-list li {
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 4px;
            border-left: 3px solid #4285f4;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-primary { background: #4285f4; color: white; }
        .btn-success { background: #4caf50; color: white; }
        .btn-secondary { background: #f5f5f5; color: #333; }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Feedback #<?php echo e($feedback->id); ?></h1>
            <div>
                <span class="badge badge-<?php echo e($feedback->status); ?>"><?php echo e(ucfirst($feedback->status)); ?></span>
                <span class="badge badge-<?php echo e($feedback->urgency ?? 'low'); ?>"><?php echo e(ucfirst($feedback->urgency ?? 'N/A')); ?></span>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <!-- Customer Feedback -->
        <div class="section">
            <h2>Customer Feedback</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Rating</label>
                    <div class="value">
                        <span class="stars"><?php echo e(str_repeat('★', $feedback->rating)); ?><?php echo e(str_repeat('☆', 5 - $feedback->rating)); ?></span>
                        (<?php echo e($feedback->rating); ?>/5)
                    </div>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <div class="value"><?php echo e(ucfirst($feedback->category ?? 'N/A')); ?></div>
                </div>
                <div class="info-item">
                    <label>Sentiment</label>
                    <div class="value"><?php echo e(ucfirst($feedback->sentiment ?? 'N/A')); ?></div>
                </div>
                <div class="info-item">
                    <label>Contact</label>
                    <div class="value"><?php echo e($feedback->contact ?? 'Not provided'); ?></div>
                </div>
            </div>
            <?php if($feedback->comment): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Comment</label>
                    <div class="value" style="white-space: pre-wrap;"><?php echo e($feedback->comment); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- AI Generated Reply -->
        <?php if($feedback->gpt_reply): ?>
            <div class="section">
                <h2>AI Suggested Reply</h2>
                <form method="POST" action="<?php echo e(route('staff.feedback.approve-reply', $feedback->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <textarea name="reply" required><?php echo e($feedback->gpt_reply); ?></textarea>
                    <div style="margin-top: 10px; font-size: 12px; color: #666;">
                        <strong>Suggested Next Step:</strong> <?php echo e($feedback->gpt_next_step); ?>

                    </div>
                    <div class="actions" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-success">Approve & Send Reply</button>
                        <a href="<?php echo e(route('staff.feedback.index')); ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Recovery Plan -->
        <?php if($feedback->gpt_actions && count($feedback->gpt_actions) > 0): ?>
            <div class="section">
                <h2>Recovery Plan</h2>
                <ul class="actions-list">
                    <?php $__currentLoopData = $feedback->gpt_actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <strong><?php echo e(ucfirst($action['priority'] ?? 'medium')); ?> Priority:</strong>
                            <?php echo e($action['action'] ?? 'N/A'); ?><br>
                            <small>Assigned to: <?php echo e($action['assigned_to'] ?? 'N/A'); ?> | ETA: <?php echo e($action['eta'] ?? 'N/A'); ?></small>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="section">
            <h2>Actions</h2>
            <div class="actions">
                <?php if($feedback->status !== 'resolved'): ?>
                    <form method="POST" action="<?php echo e(route('staff.feedback.resolve', $feedback->id)); ?>" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-success">Mark as Resolved</button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('staff.feedback.index')); ?>" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        <!-- Audit Log -->
        <?php if($feedback->auditLogs && $feedback->auditLogs->count() > 0): ?>
            <div class="section">
                <h2>Audit Log</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 8px; text-align: left;">Action</th>
                            <th style="padding: 8px; text-align: left;">Date</th>
                            <th style="padding: 8px; text-align: left;">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $feedback->auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="padding: 8px;"><?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?></td>
                                <td style="padding: 8px;"><?php echo e($log->created_at->format('M d, Y H:i')); ?></td>
                                <td style="padding: 8px;"><?php echo e($log->user->name ?? 'System'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/staff/feedback/show.blade.php ENDPATH**/ ?>