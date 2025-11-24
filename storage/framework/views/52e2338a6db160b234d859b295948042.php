<?php if(ContentManager::hasAnyBlocks('Testimonials: title')): ?>
<section class="testimonials" id="testimonials">
    <div class="main-testimonial">
        <div class="layout-box">
            <h2 class="heading">
                <?php echo ContentManager::contentBlocks('Testimonials: title'); ?>

            </h2>
            <p class="text">
                <?php echo ContentManager::contentBlocks('Testimonials - Main testimonial: text'); ?>

            </p>
            <div class="details">
                <label>
                    <?php echo ContentManager::contentBlocks('Testimonials - Main testimonial: customer name'); ?>

                </label>
                <?php echo ContentManager::contentBlocks('Testimonials - Main testimonial: date'); ?>

            </div>
        </div>

        <img src="<?php echo e(override_asset('/assets/images/quote.svg')); ?>" class="quote-image" alt="<?php echo e(t('Quote image')); ?>" />
    </div>

    <?php

    $blocks = ContentManager::contentBlocks(
    'Testimonials: list',
    join: false,
    noParagraph: false
    );

    ?>

    <?php if(!empty($blocks)): ?>
    <div class="testimonials-track">
        <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="testimonial-item">
            <?php echo $block; ?>

        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/testimonials.blade.php ENDPATH**/ ?>