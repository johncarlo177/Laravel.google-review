@php

    $isEnabled = $composer->designValue('profile_image_enabled') === 'enabled';

    $url = $composer->fileUrl('profile_image');

@endphp

@if ($isEnabled && !empty($url))
    <div class="qrcode-container profile-image">
        <img src="{{ $url }}" />
    </div>
@endif
