@extends('blue.layouts.page')

@section('page-content')
    <script>
        (function() {

            document.addEventListener('DOMContentLoaded', function() {
                refreshLocalUser()

                setTimeout(() => {
                    refreshLocalUser()
                }, 1000);

                setTimeout(() => {
                    refreshLocalUser()
                }, 3000);
            })

            function refreshLocalUser() {
                window.dispatchEvent(new CustomEvent('auth:request-validate'))
            }
        })()
    </script>
    <section class="payment-result">
        <div class="layout-box">
            <div class="text">
                @if ($composer->thankYouViewPath())
                    @include($composer->thankYouViewPath())
                @else
                    <h1>{{ t('Thank you') }}</h1>

                    <p>{{ t('We have received your payment successfully.') }}</p>

                    <a href="{{ url('/account/login') }}" class="button primary">{{ t('Login') }}</a>
                @endif
            </div>
        </div>
    </section>
@endsection
