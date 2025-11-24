<?php $__env->startSection('head'); ?>
    <?php echo \Illuminate\View\Factory::parentPlaceholder('head'); ?>

    <meta name="description" content="<?php echo e($page->meta_description); ?>" />

    <?php echo $page->head_tag_code ?? ''; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
    <?php echo e(PageTitle::makeTitle($page->title)); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <section class="inner-page-title">
        <div class="layout-box">
            <h1><?php echo e($page->title); ?></h1>
        </div>
    </section>


    <?php echo $composer->renderHtmlContent(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('blue.layouts.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/pages/dynamic.blade.php ENDPATH**/ ?>