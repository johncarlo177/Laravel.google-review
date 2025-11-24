@extends('blue.layouts.page')

@section('page-content')

@section('before-content')
@show

<div class="layout-box">
    <div class="layout-content-with-sidebar{{ isset($gridGapBorder) && $gridGapBorder ? ' grid-gap-border' : ''}}">

        <div class="main-content">
            @section('main-content')
            @show
        </div>

        <div class="sidebars">
            @section('sidebars')
            @show
        </div>
    </div>
</div>

@endsection