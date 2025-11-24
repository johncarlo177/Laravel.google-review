@extends('payment.processor.layout', compact('subscription'))

@section('head')
<style>
    .orange-bf-payform {
        min-width: 350px;
    }

    .desc {
        margin-bottom: 2rem;
    }

    [name] {
        margin-bottom: 1rem;
    }
</style>
@parent

@endsection

@section('payform')

<div class="orange-bf-payform">
    <h1 class="page-title">
        {{ t('Secure Payment')}}
    </h1>

    <p class="desc">
        {{ t('Payment service is secured and provided by orange.')}}
    </p>

    <qrcg-client-orange-bf amount="{{ $subscription->subscription_plan->price }}"
        subscription-id="{{ $subscription->id }}"></qrcg-client-orange-bf>

</div>

@endsection