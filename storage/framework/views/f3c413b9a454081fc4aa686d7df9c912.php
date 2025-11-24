<?php echo $__env->make('blue.partials.head.dashboard-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(is_dev()): ?>
    <script type="module" src="http://localhost:8000/@vite/client"></script>
    <script type="module" src="http://localhost:8000/src/index.js" id="dev"></script>
<?php else: ?>
    <?php echo $__env->make('blue.partials.head.dashboard-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/partials/head/dashboard-bundle-loader.blade.php ENDPATH**/ ?>