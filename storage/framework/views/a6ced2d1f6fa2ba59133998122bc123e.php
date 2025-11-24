<?php
    $layoutBodyAttributes = ContentManager::bodyClass();

    if (isset($bodyAttributes)) {
        $layoutBodyAttributes = $bodyAttributes;
    }
?>

<!DOCTYPE html>
<html lang="<?php echo e($composer->locale()); ?>" dir=<?php echo e($composer->direction()); ?>>



<head>
    <?php echo $__env->make('blue.partials.head.entrypoint', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $composer->frontendHeadCustomCode(); ?>

</head>

<body <?php echo $layoutBodyAttributes; ?>>

    <?php echo ContentManager::customCode('Body Tag: after open.'); ?>


    <?php $__env->startSection('body'); ?>
    <?php echo $__env->yieldSection(); ?>

    <?php echo ContentManager::customCode('Body Tag: before close.'); ?>


    <?php echo PluginManager::doAction(PluginManager::ACTION_BODY_BEFORE_CLOSE); ?>


    <?php echo $__env->make('blue.components.gdpr', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>

</html>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/blue/layouts/main.blade.php ENDPATH**/ ?>