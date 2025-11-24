<style>
    .review-site {
        font-size: 0;
        display: block;
        margin: auto;
        margin-bottom: 1rem;
        border-radius: 1rem;
        padding: 0.5rem;
        position: relative;
    }

    .review-site img {
        display: block;
        width: 100%;
    }
</style>

@foreach ($composer->getReviewSites() as $site)
    @php
        $url = file_url($site['image']);

        $width = @$site['width'] ?? 50;

        $width = filter_var($width, FILTER_SANITIZE_NUMBER_INT);

        $width = max(20, min(100, $width));

        $background_color = @$site['background_color'] ?? 'transparent';
    @endphp

    <a class="review-site" href="{{ $site['url'] }}" target="_blank" title="{{ @$site['name'] }}" style="background-color: {{ $background_color }}; width: {{ $width }}%;">

        @if (!empty($url))
            <img src="{{ $url }}" alt="{{ @$site['name'] }}" />
        @endif
    </a>
@endforeach
