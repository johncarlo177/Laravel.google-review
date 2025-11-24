<?php $__env->startSection('head'); ?>

    <title>
        <?php $__env->startSection('title'); ?>
            <?php echo e(PageTitle::makeTitle()); ?>

        <?php echo $__env->yieldSection(); ?>
    </title>

<?php $__env->startSection('meta-description'); ?>
<?php echo $__env->yieldSection(); ?>

<meta charset="UTF-8" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover" />

<base href="<?php echo e(request()->getSchemeAndHttpHost()); ?>/" />

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<?php echo $__env->make('blue.partials.head.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('blue.partials.head.configs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('blue-assets'); ?>
    <?php echo $__env->make('blue.partials.head.blue-assets-loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldSection(); ?>


<?php $__env->startSection('dashboard-assets'); ?>
    <?php echo $__env->make('blue.partials.head.dashboard-bundle-loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldSection(); ?>


<?php echo ContentManager::renderConfigThemeStyles(); ?>


<?php echo ContentManager::customCode('Head Tag: before close.'); ?>


<?php echo $__env->make('blue.partials.head.dashboard-custom-code', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php echo $__env->yieldSection(); ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/blue/partials/head/entrypoint.blade.php ENDPATH**/ ?>