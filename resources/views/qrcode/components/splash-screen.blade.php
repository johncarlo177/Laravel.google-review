@php
    $enabled = $composer->designValue('splash_screen_enabled') === 'enabled';

    $url = $composer->fileUrl('splash_screen_logo');

    $timeout = $composer->designValue('splash_screen_timeout') ?? 5;

    if (empty($timeout) || is_nan($timeout)) {
        $timeout = 5;
    }

    $shouldRender = $enabled && !empty($url);
@endphp

@if ($shouldRender)
    <script>
        window.QRCG_SPLASH_SCREEN_TIMEOUT = {{ $timeout }};
    </script>

    <div class="splash-screen">
        <img src="{{ $url }}" alt="{{ t('Logo') }}">
    </div>
@endif
