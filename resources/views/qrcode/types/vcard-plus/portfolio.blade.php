<div class="portfolio">
    @if ($title = $composer->designValue('portfolio_section_title'))
        <h2 class="portfolio-title">
            {{ $title }}
        </h2>
    @endif

    @foreach ($composer->portfolio() as $image)
        <div class="portfolio-image">
            <img src="{{ $image['src'] }}" />

            @if (!empty($image['title']))
                <div class="caption">
                    {{ $image['title'] }}
                </div>
            @endif

            @if (!empty($image['description']))
                <div class="description">
                    {!! $image['description'] !!}
                </div>
            @endif

            @if ($image['link'])
                <a href="{{ $image['link'] }}" target="_blank"></a>
            @endif
        </div>
    @endforeach
</div>
