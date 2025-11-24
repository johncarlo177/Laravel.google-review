@section('favicon')

<link rel="apple-touch-icon" sizes="180x180" href="{{ FaviconManager::url('apple-touch-icon.png') }}" />

<link rel="icon" type="image/png" sizes="32x32" href="{{ FaviconManager::url('favicon-32x32.png') }}" />

<link rel="icon" type="image/png" sizes="16x16" href="{{ FaviconManager::url('favicon-16x16.png') }}" />

<link rel="manifest" href="{{ FaviconManager::url('site.webmanifest') }}" />

<link rel="mask-icon" href="{{
        FaviconManager::url('safari-pinned-tab.svg') }}" color={!! config('theme.primary_0') ?? '"#eee"' !!} />

<link rel="shortcut icon" href="{{ FaviconManager::url('favicon.ico') }}" />

<meta name="msapplication-TileColor" content={!! config('frontend.browserconfig.tile_color') ?? '"#fff"' !!} />

<meta name="msapplication-config" content="{{ FaviconManager::url('browserconfig.xml') }}" />

<meta name="theme-color" content="#ffffff" />

@show