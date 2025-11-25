<?php
    $enabled = $composer->designField('share-overlay-enabled');

    $enabled = $enabled != 'disabled';

    $appleWalletEnabled = App\Support\Apple\ApplePassGenerator::empty()->isEnabled();
?>

<?php if($enabled): ?>
    <div class="share-overlay-trigger">
        <?php echo $__env->make('blue.components.icons.share', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>


    <div class="share-overlay">
        <div class="share-overlay-content">
            <header>
                <div class=close>
                    <?php echo $__env->make('blue.components.icons.close-thick', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </header>


            <div class="share-overlay-body">

                <p class=scan-text>
                    <?php echo e(t('Scan the QR Code')); ?>

                </p>

                <img src="<?php echo e($composer->getQRCode()->getDirectSvgUrl()); ?>" alt="QR Code" class="qrcode-image">

                <p>
                    <?php echo e(t('Or Share On')); ?>

                </p>

                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(request()->fullUrl())); ?>" target="_blank" class="share-button facebook">
                        <?php echo $__env->make('blue.components.icons.social.facebook', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </a>

                    <a href="https://api.whatsapp.com/send?text=<?php echo e(urlencode(request()->fullUrl())); ?>" target="_blank" class="share-button whatsapp">
                        <?php echo $__env->make('blue.components.icons.social.whatsapp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </a>

                    <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode(request()->fullUrl())); ?>" target="_blank" class="share-button twitter">
                        <?php echo $__env->make('blue.components.icons.social.twitter', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo e(urlencode(request()->fullUrl())); ?>" target="_blank" class="share-button linkedin">
                        <?php echo $__env->make('blue.components.icons.social.linkedin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </a>
                </div>

                <p>
                    <?php echo e(t('Or')); ?>

                </p>

                <button class="button native-share-button black">
                    <?php echo $__env->make('blue.components.icons.share-all', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <span class="button-text">
                        <?php echo e(t('Share another way')); ?>

                    </span>
                </button>

                <?php if($appleWalletEnabled): ?>
                    <a href="/add-to-apple-wallet/<?php echo e($composer->getQRCode()->id); ?>" class="apple-wallet">
                        <img src="<?php echo e(url('/assets/images/add-to-apple-wallet.svg')); ?>" />
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/components/share-overlay.blade.php ENDPATH**/ ?>