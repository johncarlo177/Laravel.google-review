<?php
    $inverse = isset($inverse) ? $inverse : false;

    if ($inverse) {
        /**
         *
         * Dark logo 
         **/
        $logo = config('frontend.header_logo_inverse_url');
    } else {
        /**
         *
         * Light logo
         **/
        $logo = config('frontend.header_logo_url');
    }
?>

<a href="/" class="logo" title="<?php echo e(t('Logo')); ?>">
    <?php if($logo): ?>
        <img src="<?php echo e($logo); ?>" alt="<?php echo e(t('Logo')); ?>" />
    <?php else: ?>
        <img src="<?php echo e(url('/assets/images/logo-white.png')); ?>" alt="<?php echo e(t('Logo')); ?>" />
    <?php endif; ?>
</a>
<?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/blue/components/logo.blade.php ENDPATH**/ ?>