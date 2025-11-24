<?php

    $description = config('homepage.meta_description');
    $keywords = config('homepage.meta_keywrods');

?>

<?php $__env->startSection('meta-description'); ?>
    <meta name="description" content="<?php echo e($description); ?>" />
    <meta name="keywords" content="<?php echo e($keywords); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <?php echo $__env->make('blue.sections.website-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after banner'); ?>

    <?php echo $__env->make('blue.sections.features-in-numbers', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after features in numbers'); ?>

    <?php echo $__env->make('blue.sections.show-case-gradient', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after gradient section'); ?>

    <?php echo $__env->make('blue.sections.show-case-qrcode-shapes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after outlined shapes section'); ?>

    <?php echo $__env->make('blue.sections.qrcode-types', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after QR code types section'); ?>

    <?php echo $__env->make('blue.sections.qrcode-stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after stats section'); ?>

    <?php echo $__env->make('blue.sections.testimonials', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after testimonials section'); ?>

    <?php echo $__env->make('blue.sections.pricing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after pricing section'); ?>

    <?php echo $__env->make('blue.sections.blog-short-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo ContentManager::customCode('Home Page: after blog section'); ?>


    <?php echo ContentManager::renderAfterBlogSectionAction(); ?>


    <?php echo $__env->make('blue.components.go-to-top', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('blue.layouts.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/pages/home.blade.php ENDPATH**/ ?>