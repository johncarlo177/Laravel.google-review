@extends('qrcode.pages.layout')

@section('page')
    <div class="layout-generated-webpage banner-layout">
        <script>
            window.BANNER_TIMEOUT = {{ $ads->timeout() }}
        </script>


        <div class="navbar">
            {{ t('Redirecting you in') }} <span class="number"></span> {{ t(' seconds.') }}
        </div>

        {!! $ads->code() !!}


    </div>
@endsection
