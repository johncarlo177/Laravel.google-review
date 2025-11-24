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

    <script src="https://cdn.paddle.com/paddle/paddle.js"></script>

    <script type="text/javascript">
        @if ($processor->getMode() === 'sandbox')
            Paddle.Environment.set('sandbox');
        @endif

        Paddle.Setup({
            vendor: {{ $processor->getVendorId() or 0 }},
            eventCallback: function(data) {
                console.log(data)

                if (data.event === 'Checkout.Complete') {
                    const checkoutId = data.eventData.checkout.id

                    window.location = '{{ $processor->successUrl() }}&checkout_id=' + checkoutId;
                }
            }
        });
    </script>
@endsection

@section('payform')
    <div class="checkout-container"></div>
    <script>
        Paddle.Checkout.open({
            email: '{{ $subscription->user->email }}',
            method: 'inline',
            product: '{{ $processor->getPaddleId($subscription->subscription_plan) }}', // Replace with your Product or Plan ID
            allowQuantity: false,
            disableLogout: true,
            frameTarget: 'checkout-container', // The className of your checkout <div>
            frameInitialHeight: 700,
            customData: {
                subscription_id: {{ $subscription->id }}
            },
            passthrough: {
                subscription_id: {{ $subscription->id }}
            },
            frameStyle: 'height: 740px; width:100%; min-width:312px; background-color: transparent; border: none;',
            // Please ensure the minimum width is kept at or above 286px with checkout padding disabled, or 312px with checkout padding enabled. See "General" section under "Branded Inline Checkout" below for more information on checkout padding.

        });
    </script>
@endsection
