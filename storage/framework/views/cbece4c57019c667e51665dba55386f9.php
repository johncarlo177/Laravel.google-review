<?php $__env->startSection('favicon'); ?>

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(FaviconManager::url('apple-touch-icon.png')); ?>" />

<link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(FaviconManager::url('favicon-32x32.png')); ?>" />

<link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(FaviconManager::url('favicon-16x16.png')); ?>" />

<link rel="manifest" href="<?php echo e(FaviconManager::url('site.webmanifest')); ?>" />

<link rel="mask-icon" href="<?php echo e(FaviconManager::url('safari-pinned-tab.svg')); ?>" color=<?php echo config('theme.primary_0') ?? '"#eee"'; ?> />

<link rel="shortcut icon" href="<?php echo e(FaviconManager::url('favicon.ico')); ?>" />

<meta name="msapplication-TileColor" content=<?php echo config('frontend.browserconfig.tile_color') ?? '"#fff"'; ?> />

<meta name="msapplication-config" content="<?php echo e(FaviconManager::url('browserconfig.xml')); ?>" />

<meta name="theme-color" content="#ffffff" />

<?php echo $__env->yieldSection(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/blue/partials/head/favicon.blade.php ENDPATH**/ ?>