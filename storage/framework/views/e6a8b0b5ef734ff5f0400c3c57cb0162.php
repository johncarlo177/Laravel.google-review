<?php if(config('cookie_consent_enabled') != 'disabled' && config('app.installed')): ?>
    <!--HTML Cookie Consent Banner-->
    <div id="cookie-consent" class="hidden-div">
        <div class="main-text">
            <span id="cookie-consent-msg">
                <?php echo t('This site uses cookies. Visit our cookies policy page or click the link in any footer for more information and to change your preferences.'); ?>

            </span>
            <span>
                <a id="cookie-consent-more-info-1" target="_blank" href="/privacy-policy">
                    <?php echo e(t('Privacy Policy')); ?>

                </a>
            </span>

        </div>

        <div class="controls">
            <a id="button-accept-all" class="button primary" href="#">
                <?php echo e(t('Accept All Cookies')); ?>

            </a>
            <a id="accept-necessary" class="button accent" href="#">
                <?php echo e(t('Accept Only Essential Cookies')); ?>

            </a>
            <a id="customize-link" class="button outline full-width" href="#">
                <?php echo e(t('Customize')); ?>

            </a>
        </div>

    </div>

    <!--HTML Popup Cookie Consent Banner-->
    <div id="popup" class="popup-overlay">
        <div class="popup-content">
            <h3>
                <?php echo e(t('Cookie Settings')); ?>

            </h3>
            <hr>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="necessary" checked disabled>
                <span>
                    <?php echo e(t('Necessary')); ?>

                </span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="preferences"><span><?php echo e(t('Preferences')); ?></span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="statistics"><span><?php echo e(t('Statistics')); ?></span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="marketing"><span><?php echo e(t('Marketing')); ?></span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="others"><span><?php echo e(t('Others')); ?></span>
            </label>
            <a id="cookie-consent-more-info-2" style="color: black; font-size: small;" target="_blank" href="/privacy-policy"><?php echo e(t('Privacy Policy')); ?></a>
            <hr>
            <div class="controls">
                <button id="popup-ok" class="button primary"><?php echo e(t('Apply')); ?></button>
                <button id="popup-cancel" class="button danger"><?php echo e(t('Cancel')); ?></button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/public_html (1)/review/resources/views/blue/components/gdpr.blade.php ENDPATH**/ ?>