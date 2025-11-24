<?php if(ContentManager::hasAnyBlocks('QR Code Types: title')): ?>
<section class="qrcode-types">
    <div class="layout-box">
        <div class="text-wrapper">
            <p class="tag-line">
                <?php echo ContentManager::contentBlocks('QR Code Types: tag'); ?>

            </p>
            <h2 class="section-title">
                <?php echo ContentManager::contentBlocks('QR Code Types: title'); ?>

            </h2>
            <p class="explainer">
                <?php echo ContentManager::contentBlocks('QR Code Types: text'); ?>

            </p>
        </div>
        <div class="boxes">
            <?php $__currentLoopData = App\Models\QRCode::getTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php

            $title = ContentManager::contentBlocks("QR Code Types ($type): title");

            $title = trim($title);

            if (empty($title)) continue;

            ?>

            <div class="single-box">
                <h3 class="heading">
                    <?php echo $title; ?>

                </h3>

                <div class="badge">
                    <?php

                    $badge = trim(ContentManager::contentBlocks("QR Code Types ($type): badge"));

                    $badge = empty($badge) ? null : $badge;

                    ?>

                    <?php echo $badge ?? t('Static'); ?>

                </div>

                <?php echo ContentManager::contentBlocks("QR Code Types ($type): text", noParagraph: false); ?>

            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="show-more">
            <?php echo e(t('Show more types')); ?>

        </div>
    </div>
</section>
<?php endif; ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/qrcode-types.blade.php ENDPATH**/ ?>