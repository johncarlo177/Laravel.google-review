@php
    $classes = 'error-page not-found page';

    $bodyAttributes = "class='$classes'";
@endphp

@extends('blue.layouts.page')


@section('title')
    {{ PageTitle::makeTitle('Page Not Found') }}
@endsection

@section('page-content')

    {!! ContentManager::customCode('404 Error: Before Content') !!}

    <section class="page-content">
        <div class="layout-box">
            <div class="inner-page-content">
                <div class=icon>
                    404
                </div>

                <h1>
                    {{ t('Page Not Found') }}
                </h1>

                <div class=content>
                    {{ t('The requested page could not be found on this server.') }}
                </div>
            </div>
        </div>
    </section>
@endsection
