@extends('blue.layouts.page')

@section('page-content')
<section class="payment-result failed">
    <div class="layout-box">
        <div class="text">
            <h1>{{ t('Payment canceled') }}</h1>
            <p>{{ t('You have not been charged.') }}</p>

            <a href="{{ url('/') }}" class="button primary">{{ t('Go Home') }}</a>
        </div>
    </div>
</section>
@endsection