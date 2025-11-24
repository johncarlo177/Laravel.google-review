@section('head')

    <title>
        @section('title')
            {{ PageTitle::makeTitle() }}
        @show
    </title>

@section('meta-description')
@show

<meta charset="UTF-8" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover" />

<base href="{{ request()->getSchemeAndHttpHost() }}/" />

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('blue.partials.head.favicon')

@include('blue.partials.head.configs')

@section('blue-assets')
    @include('blue.partials.head.blue-assets-loader')
@show

{{-- Dashboard assets section start --}}
@section('dashboard-assets')
    @include('blue.partials.head.dashboard-bundle-loader')
@show
{{-- Dashboard assets section end --}}

{!! ContentManager::renderConfigThemeStyles() !!}

{!! ContentManager::customCode('Head Tag: before close.') !!}

@include('blue.partials.head.dashboard-custom-code')

{{-- end of @section('head') --}}
@show
