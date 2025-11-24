<!DOCTYPE html>
<html lang="{{ $composer->locale() }}" dir="{{ $composer->dir() }}">

<head>
    @include('blue.partials.head.entrypoint')
    @include('qrcode.components.language-collector')
</head>

<body class="{{ $composer->bodyClasses() }}">
    {!! ContentManager::customCode('Dynamic QR Code: Body Tag - after open.') !!}

    @section('body')
    @show

    {!! ContentManager::customCode('Dynamic QR Code: Body Tag - before close.') !!}
</body>

</html>
