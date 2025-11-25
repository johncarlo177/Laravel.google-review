<!DOCTYPE html>
<html lang="<?php echo e($composer->locale()); ?>" dir="<?php echo e($composer->dir()); ?>">

<head>
    <?php echo $__env->make('blue.partials.head.entrypoint', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('qrcode.components.language-collector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="<?php echo e($composer->bodyClasses()); ?>">
    <?php echo ContentManager::customCode('Dynamic QR Code: Body Tag - after open.'); ?>


    <?php $__env->startSection('body'); ?>
    <?php echo $__env->yieldSection(); ?>

    <?php echo ContentManager::customCode('Dynamic QR Code: Body Tag - before close.'); ?>

</body>

</html>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/types/skeleton.blade.php ENDPATH**/ ?>