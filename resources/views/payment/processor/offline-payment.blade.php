@extends('payment.processor.layout', compact('subscription'))

@section('head')
@parent
<style>
    qrcg-client-offline-payment {
        margin: 1rem 0;
    }
</style>
@endsection

@section('payform')
<qrcg-client-offline-payment plan-id="{{ $subscription->subscription_plan_id }}">
</qrcg-client-offline-payment>
@endsection