<?php switch($exception->getStatusCode()):
    case (404): ?>
        <?php echo $__env->make('errors.404', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php break; ?>

    <?php case (500): ?>
        <?php echo $__env->make('errors.500', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php break; ?>

    <?php default: ?>
        <?php echo $__env->make('errors.fallback', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endswitch; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/errors/default.blade.php ENDPATH**/ ?>