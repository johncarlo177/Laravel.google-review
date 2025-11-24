@if ($composer->designValue('meta_description'))
    <meta name="description" value="{{ $composer->designValue('meta_description') }}" />
@endif

@if ($composer->designValue('meta_keywords'))
    <meta name="keywords" value="{{ $composer->designValue('meta_keywords') }}" />
@endif
