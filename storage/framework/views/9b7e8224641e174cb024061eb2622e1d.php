<?php
    $description = $composer->designValue('meta_description');
?>

<meta property="og:title" content="<?php echo e($composer->getQRCode()->name); ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo e(request()->fullUrl()); ?>" />

<?php if($description): ?>
    <meta property="og:description" content="<?php echo e($description); ?>" />
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/components/opengraph.blade.php ENDPATH**/ ?>