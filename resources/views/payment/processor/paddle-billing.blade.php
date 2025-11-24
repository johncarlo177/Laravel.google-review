@extends('payment.processor.layout', compact('subscription'))

@section('head')
    @parent

    <style>
        @media (min-width: 920px) {
            .col.right {
                width: 43%;
            }
        }
    </style>

    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>

    <script type="text/javascript">
        @if ($processor->getMode() === 'sandbox')
            Paddle.Environment.set('sandbox');
        @endif

        Paddle.Setup({
            token: "{{ $processor->getClientSideToken() }}",

            eventCallback: function(event) {
                // console.log(event)

                if (event.name === 'checkout.completed') {
                    window.location = '{{ $processor->successUrl() }}';
                }
            }
        });
    </script>
@endsection

@section('payform')
    <div class="checkout-container"></div>
    <script>
        (function() {
            var itemsList = [{
                priceId: '{{ $processor->getPriceId($subscription->subscription_plan) }}',
                quantity: 1
            }];

            Paddle.Checkout.open({
                items: itemsList,

                settings: {
                    displayMode: "inline",
                    theme: "light",
                    frameTarget: 'checkout-container', // The className of your checkout <div>
                    frameInitialHeight: 700,
                    frameStyle: 'height: 800px; width:100%; min-width:312px; background-color: transparent; border: none;',
                },

                customer: {
                    email: "{{ $subscription->user->email }}"
                },

                customData: {
                    subscription_id: {{ $subscription->id }}
                },

            });
        }())
    </script>
@endsection
