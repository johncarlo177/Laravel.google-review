@extends('blue.layouts.page-with-sidebar', ['gridGapBorder' => true])

@section('title', PageTitle::makeTitle($post->title))

@section('meta-description')
    <meta name="description" content="{{ $post->meta_description }}">
@endsection

@section('sidebars')
    @include('blue.sidebars.subscribe')
@endsection

@section('main-content')

    @include('blue.sections.blog-post')

@endsection
