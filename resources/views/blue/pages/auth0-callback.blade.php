@extends('blue.layouts.page')

@section('page-content')

<section class="inner-page-title">
    <div class="layout-box">
        <h1 style="text-align: center">
            @if (!empty($error))
            <span style="color: var(--blue-danger-0)">
                {{ $error }}
            </span>
            @else
            {{ t('Redirecting you to the dashboard')}}
            @endif
        </h1>
    </div>
</section>


@if (empty($error))
<qrcg-auth0-callback-handler user='{{ json_encode($user) }}' token="{{ $token }}">
</qrcg-auth0-callback-handler>
@endif

@endsection