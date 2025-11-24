@extends('blue.layouts.page')

@section('page-content')
    <section class="inner-page-title">
        <div class="layout-box">
            <h1 style="text-align: center">
                {{ t('Redirecting you to the dashboard') }}
            </h1>
        </div>
    </section>

    @if (empty($error))
        <qrcg-auth-callback user="{{ base64_encode(json_encode($user)) }}" token="{{ base64_encode(json_encode($token)) }}">
        </qrcg-auth-callback>
    @endif
@endsection
