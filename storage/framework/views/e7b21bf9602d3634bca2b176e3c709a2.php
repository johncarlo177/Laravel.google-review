<?php
    $classes = 'error-page not-found page';

    $bodyAttributes = "class='$classes'";
?>




<?php $__env->startSection('title'); ?>
    <?php echo e(PageTitle::makeTitle('Page Not Found')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    <?php echo ContentManager::customCode('404 Error: Before Content'); ?>


    <section class="page-content">
        <div class="layout-box">
            <div class="inner-page-content">
                <div class=icon>
                    404
                </div>

                <h1>
                    <?php echo e(t('Page Not Found')); ?>

                </h1>

                <div class=content>
                    <?php echo e(t('The requested page could not be found on this server.')); ?>

                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('blue.layouts.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/errors/404.blade.php ENDPATH**/ ?>