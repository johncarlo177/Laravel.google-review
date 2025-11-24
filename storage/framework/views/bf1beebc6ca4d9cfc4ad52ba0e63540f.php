<?php if(is_dev()): ?>
    <script type=module crossorigin src=http://localhost:85/main.js></script>
<?php else: ?>
    <?php echo $__env->make('blue.partials.head.blue-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/partials/head/blue-assets-loader.blade.php ENDPATH**/ ?>