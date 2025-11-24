@extends('payment.processor.layout')

@section('body')
    <style>
        .loading-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 10vh;
        }

        form {
            display: none;
        }
    </style>

    <script>
        (function() {

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('form').submit()
            })
        })()
    </script>

    <div class="loading-container">
        <qrcg-loader></qrcg-loader>
    </div>

    <form method="POST" action="https://checkout.flutterwave.com/v3/hosted/pay">
        <input type="hidden" name="public_key" value="{{ $processor->publicKey() }}" />
        <input type="hidden" name="customer[email]" value="{{ $subscription->user->email }}" />
        <input type="hidden" name="customer[name]" value="{{ $subscription->user->name }}" />
        <input type="hidden" name="tx_ref" value="{{ $processor->transactionRef($subscription) }}" />
        <input type="hidden" name="amount" value="{{ $subscription->subscription_plan->price }}" />
        <input type="hidden" name="currency" value="{{ $processor->currencyCode() }}" />
        <input type=hidden name=redirect_url value="{{ $processor->successUrl() }}" />
        <input type="hidden" name="meta[source]" value="{{ url('/') }}" />
    </form>
@endsection
