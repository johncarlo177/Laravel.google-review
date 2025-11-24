<header class="lead-form-header">
    @if ($composer->logoUrl())
        <img class="lead-form-header-logo" src="{{ $composer->logoUrl() }}" alt="logo" />
    @endif

    @if ($composer->config('header_text'))
        <div class="lead-form-header-text">{{ $composer->config('header_text') }}</div>
    @endif
</header>
