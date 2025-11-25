<?php
    $locale = $composer->locale();
?>



<?php $__env->startSection('favicon'); ?>
    <?php echo $__env->make('qrcode.components.meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('qrcode.components.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('qrcode.components.opengraph', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('head'); ?>
    <?php echo \Illuminate\View\Factory::parentPlaceholder('head'); ?>

    <?php echo $composer->styles(); ?>


    
    <?php if(!empty(config('services.google.tag_manager_id'))): ?>
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo e(config('services.google.tag_manager_id')); ?>');
    </script>
    <?php endif; ?>
    

    
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17686052305"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17686052305');
    </script>
    

<?php $__env->startSection('qrcode-layout-head'); ?>
<?php echo $__env->yieldSection(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<?php echo e($composer->getQRCode()->name); ?>

<?php $__env->stopSection(); ?>



<?php $__env->startSection('body'); ?>


<?php if(!empty(config('services.google.tag_manager_id'))): ?>
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo e(config('services.google.tag_manager_id')); ?>" 
            height="0" width="0" style="display:none;visibility:hidden">
    </iframe>
</noscript>
<?php endif; ?>


<?php $__env->startSection('loader'); ?>
    <div class="loader">
        <div class="lds-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
<?php echo $__env->yieldSection(); ?>

<?php $__env->startSection('page'); ?>
<?php echo $__env->yieldSection(); ?>

<?php echo $__env->make('qrcode.components.desktop-background', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('powered-by'); ?>
    <?php if($composer->shouldShowPoweredBy()): ?>
        <div class="powered-by">
            <?php echo e(t('Powered by')); ?>

            <a href="<?php echo e(config('app.url')); ?>" class="powered-by"><?php echo e($composer->poweredByName()); ?></a>
        </div>
    <?php endif; ?>
<?php echo $__env->yieldSection(); ?>

<?php $__env->startSection('share-overlay'); ?>
    <?php echo $__env->make('qrcode.components.share-overlay', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('qrcode.types.skeleton', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/types/layout.blade.php ENDPATH**/ ?>