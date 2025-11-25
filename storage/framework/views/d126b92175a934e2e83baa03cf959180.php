<?php $__env->startSection('body'); ?>

    <?php echo $__env->make('blue.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('page-content'); ?>
<?php echo $__env->yieldSection(); ?>

<?php echo $__env->make('blue.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('blue.layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/blue/layouts/page.blade.php ENDPATH**/ ?>