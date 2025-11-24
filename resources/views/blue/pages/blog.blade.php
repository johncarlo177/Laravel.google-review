@extends('blue.layouts.page-with-sidebar')

@php
    $title = PageTitle::makeTitle(ContentManager::contentBlocks('Blog: page title'));
@endphp

@section('title', $title)

@section('meta-description')
    <meta name="description" content="{{ ContentManager::contentBlocks('Blog: meta description') }}" />
@endsection

@section('before-content')
    <section class="inner-page-title">
        <div class="layout-box">
            <h1>
                {!! ContentManager::contentBlocks('Blog: page title') !!}
            </h1>

            {!! ContentManager::contentBlocks('Blog: below title', noParagraph: false) !!}
        </div>
    </section>
@endsection


@section('sidebars')
    @include('blue.sidebars.subscribe')
@endsection

@section('main-content')

    @include('blue.sections.blog-list')

@endsection
