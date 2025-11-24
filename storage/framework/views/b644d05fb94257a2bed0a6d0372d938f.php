<?php if($composer->shouldRender()): ?>
<div class="layout-gap"></div>
<section class="blog-short-list">
    <div class="layout-box">
        <div class="text-wrapper">
            <h2 class="section-title">
                <?php echo ContentManager::contentBlocks('Blog: page title'); ?>

            </h2>
            <p class="explainer">
                <?php echo ContentManager::contentBlocks('Blog: below title'); ?>

            </p>
        </div>

        <div class="posts">

            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="post" href="<?php echo e($post->url); ?>">
                <?php if(!empty($post->featured_image_src)): ?>
                <img src="<?php echo e($post->featured_image_src); ?>" alt="Post image" />
                <?php endif; ?>

                <h2 class="post-title"><?php echo e($post->title); ?></h2>
                <div class="post-excerpt">
                    <?php echo e($post->excerpt); ?>

                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

        <div class="explore-more-container">
            <a href="<?php echo e(url('/blog')); ?>" class="button primary"><?php echo e(t('Explore More')); ?></a>
        </div>
    </div>
</section>
<?php endif; ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/blog-short-list.blade.php ENDPATH**/ ?>