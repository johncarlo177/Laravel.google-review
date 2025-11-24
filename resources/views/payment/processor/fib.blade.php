@extends('payment.processor.layout')

@php
    $response = $processor->createPayment($subscription);
@endphp

@section('payform')
    <div class="fib-container">
        <h2>
            {{ t('First Iraqi Bank') }}
        </h2>

        <div class="qrcode">
            <img src="{{ @$response['qrCode'] }}" />
        </div>

        <div class="qrcode-content">
            {{ @$response['readableCode'] }}
        </div>

        <a class="button primary" href="{{ @$response['personalAppLink'] }}">
            {{ t('Personal App') }}
        </a>

        <a class="button primary" href="{{ @$response['businessAppLink'] }}">
            {{ t('Business App') }}
        </a>

        <a class="button primary" href="{{ @$response['corporateAppLink'] }}">
            {{ t('Corporate App') }}
        </a>

        <qrcg-fib-form-updater></qrcg-fib-form-updater>
    </div>

    <style>
        .fib-container .qrcode-content {
            font-weight: bold;
            text-align: center;
            margin: 1rem 0;
        }

        .fib-container .button {
            margin-bottom: 1rem;
        }
    </style>
@endsection
