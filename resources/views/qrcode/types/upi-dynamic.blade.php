@php

$buttonStyle = sprintf(
    '--submit-button-background-color: %s; --submit-button-text-color: %s;',
    $composer->designField('pay_button_background_color', '#ff812f'),
    $composer->designField('pay_button_text_color', 'white')
);

@endphp

@extends('qrcode.types.layout')

@section('qrcode-layout-head')
@endsection



@section('page')
    <svg width="0" height="0" style="position: fixed; top: -10000px;">
        <defs>
            <clipPath id="clip-banner" clipPathUnits="objectBoundingBox">
                <path d="M0 0H1V.9C.92.83.73.74.5.9A.6.6 90 00.49.91C.27 1.07.07.98 0 .91V0Z" />
            </clipPath>
        </defs>
    </svg>

    <div class="layout-generated-webpage">

        <div class="banner"></div>

        <img src="{{ $composer->getLogoUrl() }}" class="logo" />

        <h1>
            {{ $composer->designField('page_title', t('UPI Payment')) }}
        </h1>

        <div class="text">
            {!! $composer->text() !!}
        </div>

        <div class=payment-container>    
            <div class="upi-qrcode">
                <img src="data:image/svg+xml;base64,{{ base64_encode($composer->renderUpiQRCode())}}" />
            </div>

            <div class="amount-container">
                {{ $composer->getAmount() }} ₹
            </div>

            @foreach ($composer->providers() as $provider)
            <div class=button-container>
                
                <a href="{{ $composer->paymentUrl($provider['scheme']) }}" 
                    class="button"
                    style="{{ $buttonStyle }}"
                    >
                    
                    <img src="{{ @$provider['image'] }}" />
                    
                    
                    <span>
                        {{ $provider['name'] }}
                    </span>
                </a>

                
            </div>
            @endforeach

            <a href="#" class="button download-upi-qrcode" amount="{{ $composer->getAmount() }}">
                {{ __('Download QR Code')}}
            </a>
        </div>
    </div>

    @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
        {!! $composer->designValue('custom_code') !!}
    @endif

@endsection
