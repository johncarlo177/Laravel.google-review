<?php $__env->startSection('page-content'); ?>
<style>
    .staff-feedback-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
        h1 {
            margin-top: 0;
        }
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filters select, .filters input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .analytics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #666;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
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
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #4285f4; color: white; }
        .btn-secondary { background: #f5f5f5; color: #333; }
    .stars {
        color: #ffd700;
        font-size: 18px;
    }
</style>

<div class="staff-feedback-wrapper">
    <div class="container">
        <h1>Customer Feedback Management</h1>

        <!-- Analytics -->
        <div class="analytics">
            <div class="stat-card">
                <h3>Total Feedback</h3>
                <div class="value"><?php echo e($analytics['total']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Resolved</h3>
                <div class="value"><?php echo e($analytics['resolved']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Escalated</h3>
                <div class="value"><?php echo e($analytics['escalated']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Avg Rating</h3>
                <div class="value"><?php echo e($analytics['avg_rating']); ?>/5</div>
            </div>
            <div class="stat-card">
                <h3>Recovery Rate (7 days)</h3>
                <div class="value"><?php echo e($analytics['recovery_rate']); ?>%</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters">
            <select name="status">
                <option value="all" <?php echo e(($filters['status'] ?? 'all') === 'all' ? 'selected' : ''); ?>>All Status</option>
                <option value="new" <?php echo e(($filters['status'] ?? '') === 'new' ? 'selected' : ''); ?>>New</option>
                <option value="resolved" <?php echo e(($filters['status'] ?? '') === 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                <option value="escalated" <?php echo e(($filters['status'] ?? '') === 'escalated' ? 'selected' : ''); ?>>Escalated</option>
            </select>
            <select name="urgency">
                <option value="all" <?php echo e(($filters['urgency'] ?? 'all') === 'all' ? 'selected' : ''); ?>>All Urgency</option>
                <option value="high" <?php echo e(($filters['urgency'] ?? '') === 'high' ? 'selected' : ''); ?>>High</option>
                <option value="medium" <?php echo e(($filters['urgency'] ?? '') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                <option value="low" <?php echo e(($filters['urgency'] ?? '') === 'low' ? 'selected' : ''); ?>>Low</option>
            </select>
            <select name="rating">
                <option value="all" <?php echo e(($filters['rating'] ?? 'all') === 'all' ? 'selected' : ''); ?>>All Ratings</option>
                <option value="1" <?php echo e(($filters['rating'] ?? '') === '1' ? 'selected' : ''); ?>>1 Star</option>
                <option value="2" <?php echo e(($filters['rating'] ?? '') === '2' ? 'selected' : ''); ?>>2 Stars</option>
                <option value="3" <?php echo e(($filters['rating'] ?? '') === '3' ? 'selected' : ''); ?>>3 Stars</option>
                <option value="4" <?php echo e(($filters['rating'] ?? '') === '4' ? 'selected' : ''); ?>>4 Stars</option>
                <option value="5" <?php echo e(($filters['rating'] ?? '') === '5' ? 'selected' : ''); ?>>5 Stars</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?php echo e(route('staff.feedback.export')); ?>" class="btn btn-secondary">Export CSV</a>
        </form>

        <!-- Feedback Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Category</th>
                    <th>Urgency</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($feedback->id); ?></td>
                        <td>
                            <span class="stars"><?php echo e(str_repeat('★', $feedback->rating)); ?><?php echo e(str_repeat('☆', 5 - $feedback->rating)); ?></span>
                        </td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($feedback->comment ?? 'No comment', 50)); ?></td>
                        <td><?php echo e(ucfirst($feedback->category ?? 'N/A')); ?></td>
                        <td>
                            <span class="badge badge-<?php echo e($feedback->urgency ?? 'low'); ?>">
                                <?php echo e(ucfirst($feedback->urgency ?? 'N/A')); ?>

                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo e($feedback->status); ?>">
                                <?php echo e(ucfirst($feedback->status)); ?>

                            </span>
                        </td>
                        <td><?php echo e($feedback->created_at->format('M d, Y H:i')); ?></td>
                        <td>
                            <a href="<?php echo e(route('staff.feedback.show', $feedback->id)); ?>" class="btn btn-primary">View</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            No feedback found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php echo e($feedbacks->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('blue.layouts.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/staff/feedback/index.blade.php ENDPATH**/ ?>