@if ($composer->designValue('share_on_whatsapp') === 'enabled')
    <div class="share-on-whatsapp-container" data-message="{{ isset($message) ? $message : '' }}">
        <input type="tel" pattern="\\d*" class="share-input" placeholder="{{ t('Share on WhatsApp ...') }}">

        <div class="icon">
            @include('blue.components.icons.social.whatsapp')
        </div>
    </div>
@endif
