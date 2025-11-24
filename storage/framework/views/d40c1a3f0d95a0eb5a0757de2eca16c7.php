<section class="website-banner">
    <?php if(config('website-banner.background-custom-code')): ?>
        <?php echo config('website-banner.background-custom-code'); ?>

    <?php else: ?>
        <?php if($src = ContentManager::websiteBannerSrc()): ?>
            <div class="background user-background" style="background-image: url(<?php echo e($src); ?>);"></div>
        <?php else: ?>
            <div class="background">
                <blue-website-banner-background></blue-website-banner-background>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="layout-box small">
        <h1 class="section-title">
            <?php echo ContentManager::contentBlocks('Front Page: main title'); ?>

        </h1>

        <?php echo ContentManager::contentBlocks('Front Page: below main title', noParagraph: false); ?>


    </div>

    <div class="layout-box">
        <div class="control">
            <qrcg-website-banner></qrcg-website-banner>
        </div>
    </div>
</section>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/website-banner.blade.php ENDPATH**/ ?>