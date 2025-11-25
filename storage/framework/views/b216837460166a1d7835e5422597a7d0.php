<?php
    $fav = $composer->favicon;
?>

<?php if($fav->fileUrl('favicon-96x96.png')): ?>
    <link rel="icon" sizes="96x96" href="<?php echo e($fav->fileUrl('favicon-96x96.png')); ?>" />
<?php endif; ?>

<?php if($fav->fileUrl('apple-touch-icon.png')): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e($fav->fileUrl('apple-touch-icon.png')); ?>" />
<?php endif; ?>

<link rel="manifest" href="<?php echo e($fav->staticUrl('site.webmanifest')); ?>" />

<?php if($fav->fileUrl('favicon.ico')): ?>
    <link rel="shortcut icon" href="<?php echo e($fav->fileUrl('favicon.ico')); ?>" />
<?php endif; ?>

<?php if($fav->fileUrl('open_graph_image')): ?>
    <meta property="og:image" content="<?php echo e($fav->fileUrl('open_graph_image')); ?>" />
<?php endif; ?>

<meta name="mobile-wep-app-capable" content="yes">
<meta name="apple-mobile-wep-app-capable" content="yes">
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/components/favicon.blade.php ENDPATH**/ ?>