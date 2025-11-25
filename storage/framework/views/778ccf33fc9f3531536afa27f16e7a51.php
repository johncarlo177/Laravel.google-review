<?php
    /**
     * @see App\Support\ViewComposers\HeadConfigsComposer
     *
     **/

    $enabledWorkflows = App\Support\Auth\AuthManager::instance()->getEnabledNames();

    $aiIsEnabled = !empty(App\Models\Config::get('quickqr_art.api_key'));

?>


<script>
    history.scrollRestoration = 'manual'

    window.CONFIG = <?php echo $composer->configs(); ?>


    window.QRCG_TRANSLATION = <?php echo $composer->translationFile(); ?>


    window.QRCG_CURRENT_LOCALE = '<?php echo e($composer->locale()); ?>'

    window.QRCG_DIRECTION = '<?php echo e($composer->direction()); ?>'

    window.QRCG_BUNDLE_TYPE = 'build';

    window.QRCG_ENABLED_WORKFLOWS = <?php echo json_encode($enabledWorkflows); ?>;

    window.QRCG_AI_IS_ENABLED = <?php echo e($aiIsEnabled ? 'true' : 'false'); ?>;

    window.QRCG_VERSION = "<?php echo e(System::version()); ?>";

    window.QRCG_ENVIRONEMNT = '<?php echo e(app()->environment()); ?>';
</script>

<?php echo PageManager::renderQrTypeConfigsJsVariable(); ?>


<?php echo App\Support\QRCodeTypes\QRCodeTypeManager::renderQrTypesSortOrder(); ?>

<?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/blue/partials/head/configs.blade.php ENDPATH**/ ?>