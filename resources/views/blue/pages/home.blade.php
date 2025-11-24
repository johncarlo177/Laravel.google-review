@extends('blue.layouts.page')

@php

    $description = config('homepage.meta_description');
    $keywords = config('homepage.meta_keywrods');

@endphp

@section('meta-description')
    <meta name="description" content="{{ $description }}" />
    <meta name="keywords" content="{{ $keywords }}">
@endsection

@section('page-content')
    @include('blue.sections.website-banner')
    {!! ContentManager::customCode('Home Page: after banner') !!}
    @include('blue.sections.features-in-numbers')
    {!! ContentManager::customCode('Home Page: after features in numbers') !!}
    @include('blue.sections.show-case-gradient')
    {!! ContentManager::customCode('Home Page: after gradient section') !!}
    @include('blue.sections.show-case-qrcode-shapes')
    {!! ContentManager::customCode('Home Page: after outlined shapes section') !!}
    @include('blue.sections.qrcode-types')
    {!! ContentManager::customCode('Home Page: after QR code types section') !!}
    @include('blue.sections.qrcode-stats')
    {!! ContentManager::customCode('Home Page: after stats section') !!}
    @include('blue.sections.testimonials')
    {!! ContentManager::customCode('Home Page: after testimonials section') !!}
    @include('blue.sections.pricing')
    {!! ContentManager::customCode('Home Page: after pricing section') !!}
    @include('blue.sections.blog-short-list')
    {!! ContentManager::customCode('Home Page: after blog section') !!}

    {!! ContentManager::renderAfterBlogSectionAction() !!}

    @include('blue.components.go-to-top')
@endsection
