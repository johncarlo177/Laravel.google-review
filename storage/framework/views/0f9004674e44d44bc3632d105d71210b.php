<?php echo ContentManager::customCode('Footer: Above Footer'); ?>


<div class="layout-gap"></div>

<?php if(!has_custom_frontend()): ?>

    <footer class="website-footer">
        <div class="layout-box">
            <div class="footer-wrapper">
                <div class="footer-logo-block">
                    <?php echo $__env->make('blue.components.logo', ['inverse' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo ContentManager::contentBlocks('Footer: below logo.'); ?>

                    <qrcg-language-picker></qrcg-language-picker>
                </div>
                <?php $__currentLoopData = $composer->menu(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gi => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list">
                        <h3><?php echo e($composer->groupName($group, $gi)); ?></h3>
                        <ul>
                            <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e($composer->itemLink($item, $i, $gi)); ?>" <?php if(!empty($item['target'])): ?> target="<?php echo e($item['target']); ?>" <?php endif; ?>>
                                        <?php echo e($composer->label($item, $i, $gi)); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="footer-copy">
                <?php echo ContentManager::contentBlocks('Footer Copyrights'); ?>

            </div>
        </div>
    </footer>
<?php endif; ?>

<?php echo ContentManager::customCode('Footer: Below Footer'); ?>

<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/partials/footer.blade.php ENDPATH**/ ?>