@extends('blue.layouts.main')

@section('head')
    @parent
    <style>
        .row {
            display: flex;
            flex-direction: column;
            position: relative;
            justify-content: space-between;
            z-index: 1;
            margin-top: 2rem;
            padding: 1rem;
        }

        @media (min-width: 920px) {
            .row {
                flex-direction: row;
                padding: 0;
            }
        }

        .col {}

        .container {
            max-width: 920px;
            margin: auto;
        }

        @media (min-width: 920px) {
            .container::before {
                -webkit-animation-fill-mode: both;
                animation-fill-mode: both;
                background: #ffffff;
                content: " ";
                height: 100%;
                position: fixed;
                right: 0;
                top: 0;
                -webkit-transform-origin: right;
                -ms-transform-origin: right;
                transform-origin: right;
                width: 50%;

            }
        }


        @media (min-width: 920px) {
            .container::before {
                box-shadow: 15px 0 30px 0 rgb(0 0 0 / 18%);
            }
        }

        .subscribe-to {
            color: var(--gray-2);
            margin-bottom: 2rem;
        }

        .price {
            font-weight: bold;
            margin: 1rem 0;
            font-size: 2rem;
            margin-right: 1rem;
            margin-bottom: 2rem;
        }

        .frequency {
            text-transform: capitalize;
            color: var(--gray-2);
        }

        .price-row {
            display: flex;
            align-items: center;
        }

        qrcg-subscription-plan-details {
            min-width: 15rem;
        }

        qrcg-app-logo {
            margin-bottom: 2rem;
        }

        @media (min-width: 920px) {
            .col.right {
                max-width: 43%;
            }
        }
    </style>
@endsection

@section('body')
    <div class="container">
        <div class="row">
            <div class="col">
                <qrcg-app-logo variation="inverse"></qrcg-app-logo>

                <div class="subscribe-to">
                    {{ t('Subscribe to') }}
                    <span class="plan-name">{{ $subscription->subscription_plan->name }}</span>
                </div>
                <div class="price-row">
                    <div class="price">
                        @if ($currencySymbolPosition === 'after')
                            {{ $subscription->subscription_plan->price }}{{ $currency }}
                        @else
                            {{ $currency }}{{ $subscription->subscription_plan->price }}
                        @endif
                    </div>
                    @if (!$subscription->subscription_plan->isLifetime())
                        <div class="frequency">
                            {{ t('Per') }} {{ $subscription->subscription_plan->isMonthly() ? t('Month') : t('Year') }}
                        </div>
                    @endif
                </div>

                <qrcg-subscription-plan-details plan-id="{{ $subscription->subscription_plan_id }}">
                </qrcg-subscription-plan-details>
            @section('after-plan-details')
            @show
        </div>
        @section('payform-col')
            <div class="col right">
            @section('payform')
            @show
        </div>
    @show
</div>
</div>
@endsection
