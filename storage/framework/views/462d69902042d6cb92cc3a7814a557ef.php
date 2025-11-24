<?php
    $bodyAttributes = 'class="dashboard-page"';
?>



<?php $__env->startSection('body'); ?>
    <qrcg-account-router></qrcg-account-router>
    <qrcg-qrcode-router></qrcg-qrcode-router>
    <qrcg-user-router></qrcg-user-router>
    <qrcg-subscription-plan-router></qrcg-subscription-plan-router>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('blue.layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/pages/dashboard.blade.php ENDPATH**/ ?>