@php
    $locale = $composer->locale();
@endphp

@extends('qrcode.types.skeleton')

@section('favicon')
    @include('qrcode.components.meta')

    @include('qrcode.components.favicon')

    @include('qrcode.components.opengraph')
@endsection

@section('head')
    @parent

    {!! $composer->styles() !!}

    {{-- Google Tag Manager --}}
    @if(!empty(config('services.google.tag_manager_id')))
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ config('services.google.tag_manager_id') }}');
    </script>
    @endif
    {{-- End Google Tag Manager --}}

    {{-- Google Ads Conversion Tracking --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17686052305"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17686052305');
    </script>
    {{-- End Google Ads Conversion Tracking --}}

@section('qrcode-layout-head')
@show
@endsection

@section('title')
{{ $composer->getQRCode()->name }}
@endsection



@section('body')

{{-- Google Tag Manager (noscript) --}}
@if(!empty(config('services.google.tag_manager_id')))
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google.tag_manager_id') }}" 
            height="0" width="0" style="display:none;visibility:hidden">
    </iframe>
</noscript>
@endif
{{-- End Google Tag Manager (noscript) --}}

@section('loader')
    <div class="loader">
        <div class="lds-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
@show

@section('page')
@show

@include('qrcode.components.desktop-background')

@section('powered-by')
    @if ($composer->shouldShowPoweredBy())
        <div class="powered-by">
            {{ t('Powered by') }}
            <a href="{{ config('app.url') }}" class="powered-by">{{ $composer->poweredByName() }}</a>
        </div>
    @endif
@show

@section('share-overlay')
    @include('qrcode.components.share-overlay')
@show

@endsection
