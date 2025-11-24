@php
    $fav = $composer->favicon;
@endphp

@if ($fav->fileUrl('favicon-96x96.png'))
    <link rel="icon" sizes="96x96" href="{{ $fav->fileUrl('favicon-96x96.png') }}" />
@endif

@if ($fav->fileUrl('apple-touch-icon.png'))
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $fav->fileUrl('apple-touch-icon.png') }}" />
@endif

<link rel="manifest" href="{{ $fav->staticUrl('site.webmanifest') }}" />

@if ($fav->fileUrl('favicon.ico'))
    <link rel="shortcut icon" href="{{ $fav->fileUrl('favicon.ico') }}" />
@endif

@if ($fav->fileUrl('open_graph_image'))
    <meta property="og:image" content="{{ $fav->fileUrl('open_graph_image') }}" />
@endif

<meta name="mobile-wep-app-capable" content="yes">
<meta name="apple-mobile-wep-app-capable" content="yes">
