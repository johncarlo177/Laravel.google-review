@extends('blue.layouts.page')

@section('page-content')
<section class="payment-result">
    <div class="layout-box">
        <div class="text">
            @if ($result === 'success')

            <h1>
                {{ t('Thank you') }}

            </h1>

            <p>
                {{ t('Plan changed successfully ... redirecting you to your account in a few seconds ...') }}
            </p>

            <script>
                setTimeout(() => {
                    window.location = "{{ url('/dashboard/qrcodes') }}"
                }, 5000)
            </script>

            @else

            <h1>
                {{ t('Change Plan Failed!') }}
            </h1>

            <p>
                {{ t('You have not been charged.') }}
            </p>

            @endif
        </div>
    </div>
</section>
@endsection