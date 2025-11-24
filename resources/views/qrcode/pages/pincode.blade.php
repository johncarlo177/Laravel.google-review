@php
    $length = config('qrcode.pincode_length') ?? 5;
    $numbers_only = config('qrcode.pincode_type') != 'any';
@endphp

@extends('qrcode.pages.layout')

@section('page')
    <div class="layout-generated-webpage pincode-layout">
        <div class="main-details">
            <h1>{{ t('PIN Code Protection') }}</h1>

            <p class="default-message">
                {{ t('This QR code is protected, enter pin code to see the QR code content.') }}
            </p>

            {!! ContentManager::customCode('PIN Code Screen Message') !!}

            @if (session()->has('error'))
                <div class="error-message">{{ session()->get('error') }}</div>
            @endif

            <form method="post" action="" class="pincode-form">
                @csrf
                @if ($numbers_only)
                    <input type="number" pattern="[0-9]*" name="pincode" class="hidden-input" autofocus />
                @else
                    <input name="pincode" class="hidden-input" autofocus />
                @endif
            </form>

            <div class='pincode-input-container'>
                <div class="pincode-input">
                    @for($i = 0; $i < $length; $i++)
                        <div class="fake-input">
                            <div class="content">0</div>
                        </div>
                    @endfor
                </div>
            </div>
            

            <div class="loader">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
