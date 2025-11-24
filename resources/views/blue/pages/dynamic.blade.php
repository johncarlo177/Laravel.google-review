@extends('blue.layouts.page')

@section('head')
    @parent

    <meta name="description" content="{{ $page->meta_description }}" />

    {!! $page->head_tag_code ?? '' !!}
@endsection

@section('title')
    {{ PageTitle::makeTitle($page->title) }}
@endsection

@section('page-content')
    <section class="inner-page-title">
        <div class="layout-box">
            <h1>{{ $page->title }}</h1>
        </div>
    </section>


    {!! $composer->renderHtmlContent() !!}
@endsection
