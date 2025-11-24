<?php if(ContentManager::hasAnyBlocks('Pricing: title')): ?>
    <section class="pricing" id="pricing">
        <div class="text-wrapper">
            <p class="tag-line">
                <?php echo ContentManager::contentBlocks('Pricing: tagline'); ?>

            </p>
            <h2 class="section-title">
                <?php echo ContentManager::contentBlocks('Pricing: title'); ?>

            </h2>
        </div>

        <?php

            $billingCycle = config('pricing.default_billing_cycle');

            $monthlyActive = $billingCycle === 'monthly';

            $yearlyActive = empty($billingCycle) || $billingCycle === 'yearly';

            $liftimeActive = $billingCycle === 'life-time';

        ?>

        <div class="pricing-switch">
            <div class="pricing-switch-box">
                <div class="switch-item <?php echo e($monthlyActive ? 'active' : ''); ?>" tab-id="monthly">
                    <?php echo e(t('Monthly Billing')); ?>

                </div>
                <div class="switch-item <?php echo e($yearlyActive ? 'active' : ''); ?>" tab-id="yearly">
                    <?php echo e(t('Yearly Billing')); ?>

                </div>

                <?php if($composer->hasLifetimePlans()): ?>
                    <div class="switch-item <?php echo e($liftimeActive ? 'active' : ''); ?>" tab-id="life-time">
                        <?php echo e(t('Life Time')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="layout-box">
            <div class="blocks">
                <div class="items-wrapper" style="background-image: url(<?php echo e(override_asset('/assets/images/qrcode-showcase-bg-1.png')); ?>)">

                    <div class="tag" tab-id="yearly">
                        <?php echo e(t('Paid yearly')); ?>

                    </div>

                    <div class="tag" tab-id="monthly">
                        <?php echo e(t('Paid monthly')); ?>

                    </div>

                    <div class="tag" tab-id="life-time">
                        <?php echo e(t('One Time')); ?>

                    </div>

                    <div class="items">
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="item" tab-id="<?php echo e($plan->frequency); ?>">
                                <h3 class="heading">
                                    <?php echo e($composer->name($plan)); ?>

                                </h3>
                                <div class="price">
                                    <?php if($composer->planIsFree($plan)): ?>
                                        <span class="free">
                                            <?php echo e(t('FREE')); ?>

                                        </span>
                                    <?php else: ?>
                                        <?php if($composer->currencyBefore()): ?>
                                            <span class="currency"><?php echo e($currency); ?></span>
                                            <span class="number">
                                                <?php echo e($composer->price($plan)); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="number">
                                                <?php echo e($composer->price($plan)); ?>

                                            </span>
                                            <span class="currency"><?php echo e($currency); ?></span>
                                        <?php endif; ?>
                                        <span class="sep">/</span>

                                        <span class="term">
                                            <?php echo e($composer->getPlanFrequencyText($plan)); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="below-price">
                                    <?php if($composer->shouldShowNumberOfUsers($plan)): ?>
                                        <?php echo e($composer->numberOfUsers($plan)); ?>

                                    <?php else: ?>
                                        <?php echo ContentManager::contentBlocks('Pricing: below price'); ?>

                                    <?php endif; ?>
                                </p>
                                <ul class="text-list">
                                    <?php echo $composer->tableMenuItem($composer->formatTotalValue($plan->number_of_scans), t('scans')); ?>


                                    <?php echo $composer->tableMenuItem($composer->formatTotalValue($plan->number_of_dynamic_qrcodes), t('dynamic QR codes')); ?>


                                    <?php echo $composer->tableMenuItem($composer->outlinedShapes($plan)->count(), t('shapes')); ?>


                                    <?php echo $composer->tableMenuItem($composer->stickers($plan)->count(), t('stickers')); ?>


                                    <li>1400+ <?php echo e(t('fonts')); ?></li>

                                    <li><?php echo e(count($plan->qr_types)); ?> <?php echo e(t('QR code types')); ?></li>

                                    <?php
                                        $d = $plan->number_of_custom_domains;
                                    ?>

                                    <?php echo $composer->tableMenuItem($composer->formatTotalValue($d), t('domains can be connected')); ?>


                                    <li>
                                        <?php
                                            $d = $plan->file_size_limit;
                                            $d = $composer->formatTotalValue($d);
                                        ?>

                                        <?php echo e($d); ?> <?php echo e(t('MB')); ?> <?php echo e(t('max upload file size.')); ?>

                                    </li>

                                    <?php if($composer->aiGenerationEnabled()): ?>
                                        <?php
                                            $d = $plan->number_of_ai_generations;
                                            $d = $composer->formatTotalValue($d);
                                        ?>

                                        <?php if($d): ?>
                                            <li>
                                                <?php echo e($d); ?> <?php echo e(t('monthly AI generations allowed.')); ?>

                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if($composer->isLarge()): ?>
                                        <?php
                                            $d = $plan->number_of_restaurant_menu_items;
                                        ?>

                                        <?php echo $composer->tableMenuItem($composer->formatTotalValue($d), t('items can be added in') . ' ' . t('Restaurant Menu')); ?>

                                    <?php endif; ?>

                                    <?php if($composer->isLarge()): ?>
                                        <?php
                                            $d = $plan->number_of_product_catalogue_items;
                                        ?>

                                        <?php echo $composer->tableMenuItem($composer->formatTotalValue($d), t('items can be added in') . ' ' . t('Product Catalogue')); ?>

                                    <?php endif; ?>

                                    <li>
                                        <?php echo e($composer->scanRetention($plan)); ?> <?php echo e(t('stats retention')); ?>.
                                    </li>

                                    <?php $__currentLoopData = $plan->getAllTypesWithLimitedAllowance(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $allowance = $plan->getSpecificTypeLimits($type);
                                        ?>
                                        <li>
                                            <?php echo e($composer->formatTotalValue($allowance)); ?> <qrcg-qrcode-type-name type="<?php echo e($type); ?>"></qrcg-qrcode-type-name>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>

                                <ul class="icons-list">

                                    <?php $__currentLoopData = $composer->checkpoints($plan); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $checkpoint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="<?php echo e($composer->checkpointClass($i)); ?>"><?php echo e($composer->checkpoint($i)); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </ul>

                                <a href="<?php echo e($composer->checkoutUrl($plan)); ?>" class="button accent buy-button">
                                    <?php echo e($composer->getButtonText($plan)); ?>

                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/pricing/plans.blade.php ENDPATH**/ ?>