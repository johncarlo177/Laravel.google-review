<?php
    $registrationEnabled = config('app.new_user_registration');

    if (!$registrationEnabled) {
        $registrationEnabled = 'enabled';
    }
?>

<?php echo ContentManager::customCode('Website Header: above header'); ?>


<?php if(!has_custom_frontend()): ?>

    <header class="website-header">
        <div class="close-btn">
            <svg viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
            </svg>
        </div>
        <div class="open-btn">
            <svg viewBox="0 0 24 24">
                <path fill="currentColor" d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z" />
            </svg>
        </div>
        <div class="layout-box">
            <div class="header-content">
                <?php echo $__env->make('blue.components.logo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <ul class="menu">
                    <?php $__currentLoopData = $composer->menu(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($composer->itemLink($item, $i)); ?>" <?php if(!empty($item['target'])): ?> target="<?php echo e($item['target']); ?>" <?php endif; ?>>
                                <?php echo e($composer->label($item, $i)); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <div class="push"></div>

                <qrcg-language-picker></qrcg-language-picker>

                <ul class="call-to-actions" state="guest">
                    <li>
                        <a href="<?php echo e($composer->loginUrl()); ?>" class="button login">
                            <?php echo e(t('Login')); ?>

                        </a>
                    </li>
                    <?php if($registrationEnabled === 'enabled'): ?>
                        <li>
                            <a href="<?php echo e($composer->registerUrl()); ?>" class="button accent">
                                <?php echo e(t('Register')); ?>

                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="call-to-actions hidden" state="logged-in">
                    <li>
                        <a href="<?php echo e($composer->loginUrl()); ?>" class="button login">
                            <?php echo e(t('Dashboard')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="#" class="button accent logout-button">
                            <?php echo e(t('Logout')); ?>

                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
<?php endif; ?>

<?php echo ContentManager::customCode('Website Header: below header'); ?>

<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/partials/header.blade.php ENDPATH**/ ?>