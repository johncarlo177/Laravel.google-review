

<?php $__env->startSection('qrcode-layout-head'); ?>
    <script>
        window.__BUSINESS_REVIEW_STARS_BEFORE_REDIRECT__ = <?php echo e($composer->starsBeforeRedirect()); ?>;

        window.__BUSINESS_REVIEW_FINAL_URL__ = "<?php echo e($composer->finalReviewLink()); ?>";
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page'); ?>
    <svg width="0" height="0" style="position: fixed; top: -10000px;">
        <defs>
            <clipPath id="clip-banner" clipPathUnits="objectBoundingBox">
                <path d="M0 0H1V.9C.92.83.73.74.5.9A.6.6 90 00.49.91C.27 1.07.07.98 0 .91V0Z" />
            </clipPath>
        </defs>
    </svg>

    <div class="layout-generated-webpage">

        <div class="banner"></div>

        <img src="<?php echo e($composer->getLogoUrl()); ?>" class="logo" />

        <h1>
            <?php echo e($composer->designField('page_title', t('Review Us Now'))); ?>

        </h1>

        <?php if(request()->boolean('success')): ?>
            <div class="success-message">
                <?php echo e($composer->designField('success_message', t('Thank you for submitting your feedback.'))); ?>

            </div>
        <?php else: ?>
            <div class="success-message hidden" 
                 data-high-rating-text="<?php echo e($composer->designField('high_rating_message', t("We're glad you had a great experience! If you'd like, you can share it on Google."))); ?>"
                 data-low-rating-title="<?php echo e($composer->designField('low_rating_title', t('Thank you for your feedback — we want to make this right.'))); ?>"
                 data-low-rating-message="<?php echo e($composer->designField('low_rating_message', t('Tell us what happened so we can fix it.'))); ?>"
                 data-google-link-text="<?php echo e(t('share your experience on Google')); ?>"
                 data-google-link-url="<?php echo e($composer->finalReviewLink()); ?>">
            </div>

            <div class="stars-container">
                <?php $__currentLoopData = $composer->totalStars(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="star">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <title>star</title>
                            <path d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" />
                        </svg>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <form method="post" class="business-review-form">

                <?php echo csrf_field(); ?>

                <input type="hidden" name="stars" required pattern="[1-9]" />

                <input required type=text name="name" placeholder="<?php echo e(t('Enter your name')); ?>" />

                <input required type=email name="email" placeholder="<?php echo e(t('Enter your email')); ?>" />

                <input required type=tel name="mobile" placeholder="<?php echo e(t('Enter your mobile')); ?>" />

                <textarea required name="feedback" placeholder="<?php echo e($composer->designField('placeholder_text', t('Enter your feedback'))); ?>" rows="8"></textarea>

                <button type="submit" class="button submit-review"
                    style="--submit-button-background-color: <?php echo e($composer->designField('send_button_background_color', '#ff812f')); ?>; --submit-button-text-color: <?php echo e($composer->designField('send_button_text_color', 'white')); ?>;">

                    <span>
                        <?php echo e($composer->designField('send_button_text', t('Send Review'))); ?>

                    </span>

                    <qrcg-loader></qrcg-loader>

                </button>

                <?php if($composer->qrcodeData('show_final_review_link') === 'enabled'): ?>
                    <div style="text-align: center; padding: 1rem;">

                        <a href="<?php echo e($composer->finalReviewLink()); ?>" target=_blank style="color: var(--gray-2)">
                            <?php echo e(t('Post your review directly.')); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>


    </div>

    <?php if($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code'))): ?>
        <?php echo $composer->designValue('custom_code'); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('qrcode.types.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/types/business-review.blade.php ENDPATH**/ ?>