@php
    $inverse = isset($inverse) ? $inverse : false;

    if ($inverse) {
        /**
         *
         * Dark logo 
         **/
        $logo = config('frontend.header_logo_inverse_url');
    } else {
        /**
         *
         * Light logo
         **/
        $logo = config('frontend.header_logo_url');
    }
@endphp

<a href="/" class="logo" title="{{ t('Logo') }}">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ t('Logo') }}" />
    @else
        <img src="{{ url('/assets/images/logo-white.png') }}" alt="{{ t('Logo') }}" />
    @endif
</a>
