<?php if($composer->isAccountCreditBilling()): ?>
    <?php echo $__env->make('blue.sections.pricing.account-credit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <?php echo $__env->make('blue.sections.pricing.plans', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('blue.sections.free-trial-notice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/sections/pricing.blade.php ENDPATH**/ ?>