<?php
$shouldRender = array_reduce(range(1, 4), function($result, $i) {
return $result && ContentManager::hasAnyBlocks("Feature $i: number");
}, true)
?>

<?php if($shouldRender): ?>
<section class="features-in-numbers">
    <div class="layout-box">
        <div class="wrapper">
            <?php $__currentLoopData = range(1, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="number-box">
                <span class="number">
                    <?php echo ContentManager::contentBlocks("Feature $i: number"); ?>

                </span>
                <label>
                    <?php echo ContentManager::contentBlocks("Feature $i: label"); ?>

                </label>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>
</section>
<?php endif; ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/features-in-numbers.blade.php ENDPATH**/ ?>