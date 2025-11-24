@extends('qrcode.types.layout')

@section('qrcode-layout-head')
@endsection

@section('page')
    <div class="layout-generated-webpage">
        <img src="{{ $composer->bg() }}" class="bg-image" />
        <img src="{{ $composer->bg() }}" class="bg-image-placeholder" />

        <div class="details-container">
            <div class="main-details">
                <div class="vertical-list">
                    <h1 class="business-name">
                        {{ $composer->qrcodeData('appName') }}
                    </h1>
                    <p class="business-description">
                        {!! $composer->qrcodeData('appDescription') !!}
                    </p>

                </div>
            </div>

            <div class="sep"></div>


            <div class="download-list">
                @if ($composer->qrcodeData('apple_store_url'))
                    <a href="{{ $composer->qrcodeData('apple_store_url') }}" class="app-store">
                        <img src="/assets/images/app-download/appstore-badge.svg" />
                    </a>
                @endif
                @if ($composer->qrcodeData('google_play_url'))
                    <a href="{{ $composer->qrcodeData('google_play_url') }}" class="google-play">
                        <img src="/assets/images/app-download/google-play-badge.png" alt="" />
                    </a>
                @endif
            </div>

            <div class="sep"></div>

            <div class="social-icons">
                @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
            </div>


            @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
                {!! $composer->designValue('custom_code') !!}
            @endif

        </div>
    </div>
@endsection
