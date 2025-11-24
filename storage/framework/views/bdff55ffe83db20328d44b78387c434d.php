<?php
    $background = config_file_url('appearance.stats_image');
?>

<?php if(ContentManager::hasAnyBlocks('QR code stats: title')): ?>
    <section class="qrcode-stats">
        <div class="layout-box">
            <div class="text-wrapper">
                <p class="tag-line">
                    <?php echo ContentManager::contentBlocks('QR code stats: tag line'); ?>


                </p>
                <h2 class="section-title">
                    <?php echo ContentManager::contentBlocks('QR code stats: title'); ?>

                </h2>
                <p class="explainer">
                    <?php echo ContentManager::contentBlocks('QR code stats: text'); ?>

                </p>
            </div>

            <img class="main-image" src="<?php echo e($background ?? override_asset('/assets/images/qrcode-stats.jpg')); ?>" alt="<?php echo e(t('Statistics')); ?>" loading="lazy" />

        </div>
    </section>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/qrcode-stats.blade.php ENDPATH**/ ?>