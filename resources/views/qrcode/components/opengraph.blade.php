@php
    $description = $composer->designValue('meta_description');
@endphp

<meta property="og:title" content="{{ $composer->getQRCode()->name }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ request()->fullUrl() }}" />

@if ($description)
    <meta property="og:description" content="{{ $description }}" />
@endif
