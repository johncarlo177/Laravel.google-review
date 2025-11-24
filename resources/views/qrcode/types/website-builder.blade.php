@extends('qrcode.types.layout')

@section('loader')
@endsection

@section('blue-assets')
@endsection

@section('share-overlay')
@endsection

@section('qrcode-layout-head')
    <script src="{{ url('/assets/lib/tailwindcss@3.4.16.js') }}"></script>

    <style>
        {!! $composer->css() !!}
    </style>

    <style>
        .powered-by {
            padding: 0.5rem;
            font-family: sans-serif;
            text-align: center
        }

        .powered-by a {
            font-weight: bold;
            text-decoration: none;
        }
    </style>
@endsection


@section('page')
    {!! $composer->html() !!}
@endsection
