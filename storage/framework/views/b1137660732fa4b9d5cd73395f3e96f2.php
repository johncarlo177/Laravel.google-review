<?php $__env->startSection('page-content'); ?>
<style>
    .staff-feedback-wrapper {
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
        align-items: center;
    }
    .filters select, .filters input {
        padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: #ffffff;
        color: #1a1d29;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
    }
    .filters select:focus, .filters input:focus {
        outline: none;
        border-color: #4285f4;
        box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    }
    .analytics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: #f9fafb;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .stat-card h3 {
        margin: 0 0 8px 0;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .stat-card .value {
        font-size: 28px;
        font-weight: 700;
        color: #1a1d29;
        letter-spacing: -0.02em;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
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
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .badge-new { background: #e3f2fd; color: #1976d2; }
    .badge-resolved { background: #e8f5e9; color: #2e7d32; }
    .badge-escalated { background: #ffebee; color: #c62828; }
    .badge-high { background: #ffebee; color: #c62828; }
    .badge-medium { background: #fff3e0; color: #ef6c00; }
    .badge-low { background: #e8f5e9; color: #2e7d32; }
    .btn {
        padding: 10px 20px;
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
    .btn-secondary { 
        background: #f3f4f6; 
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }
    .stars {
        color: #fbbf24;
        font-size: 18px;
        letter-spacing: 2px;
    }
    
    /* Remove layout-gap spacing */
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }
    
    @media (max-width: 1024px) {
        .container {
            padding: 24px;
        }
        table {
            font-size: 13px;
        }
        th, td {
            padding: 12px;
        }
    }
    @media (max-width: 768px) {
        .staff-feedback-wrapper {
            padding: 40px 20px 20px 20px;
        }
        .container {
            padding: 20px;
            border-radius: 12px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 24px;
        }
        .analytics {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .stat-card {
            padding: 16px;
        }
        .stat-card .value {
            font-size: 24px;
        }
        .filters {
            flex-direction: column;
            align-items: stretch;
        }
        .filters select, .filters input, .filters button, .filters a {
            width: 100%;
        }
        table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        th, td {
            padding: 10px 8px;
            font-size: 12px;
        }
    }
    @media (max-width: 480px) {
        .analytics {
            grid-template-columns: 1fr;
        }
        .stat-card h3 {
            font-size: 12px;
        }
        .stat-card .value {
            font-size: 22px;
        }
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