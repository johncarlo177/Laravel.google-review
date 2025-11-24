@if (config('cookie_consent_enabled') != 'disabled' && config('app.installed'))
    <!--HTML Cookie Consent Banner-->
    <div id="cookie-consent" class="hidden-div">
        <div class="main-text">
            <span id="cookie-consent-msg">
                {!! t('This site uses cookies. Visit our cookies policy page or click the link in any footer for more information and to change your preferences.') !!}
            </span>
            <span>
                <a id="cookie-consent-more-info-1" target="_blank" href="/privacy-policy">
                    {{ t('Privacy Policy') }}
                </a>
            </span>

        </div>

        <div class="controls">
            <a id="button-accept-all" class="button primary" href="#">
                {{ t('Accept All Cookies') }}
            </a>
            <a id="accept-necessary" class="button accent" href="#">
                {{ t('Accept Only Essential Cookies') }}
            </a>
            <a id="customize-link" class="button outline full-width" href="#">
                {{ t('Customize') }}
            </a>
        </div>

    </div>

    <!--HTML Popup Cookie Consent Banner-->
    <div id="popup" class="popup-overlay">
        <div class="popup-content">
            <h3>
                {{ t('Cookie Settings') }}
            </h3>
            <hr>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="necessary" checked disabled>
                <span>
                    {{ t('Necessary') }}
                </span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="preferences"><span>{{ t('Preferences') }}</span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="statistics"><span>{{ t('Statistics') }}</span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="marketing"><span>{{ t('Marketing') }}</span>
            </label>
            <label class="checkbox-label">
                <input class="my-input-class" type="checkbox" name="others"><span>{{ t('Others') }}</span>
            </label>
            <a id="cookie-consent-more-info-2" style="color: black; font-size: small;" target="_blank" href="/privacy-policy">{{ t('Privacy Policy') }}</a>
            <hr>
            <div class="controls">
                <button id="popup-ok" class="button primary">{{ t('Apply') }}</button>
                <button id="popup-cancel" class="button danger">{{ t('Cancel') }}</button>
            </div>
        </div>
    </div>
@endif
