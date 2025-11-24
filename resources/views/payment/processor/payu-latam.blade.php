@extends('blue.layouts.page')

@section('head')
@parent

<style>
    form {
        display: none;
    }

    .main-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    h1 {
        font-weight: normal;
        margin-top: 10rem;
        margin-bottom: 3rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('form').submit()
    });
</script>

@endsection

@section('page-content')

<div class="main-container">

    <h1>{{ t('Redirecting you to PayU ...')}}</h1>

    <div class="loader">
        <div class="lds-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <form action="{{ $processor->api()->getCheckoutUrl() }}" method="post">
        @foreach ($processor->getDataArray($subscription) as $key => $value)
        <input name="{{ $key }}" value="{!! $value !!}" />
        @endforeach
    </form>
</div>

@endsection