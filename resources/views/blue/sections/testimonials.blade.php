@if (ContentManager::hasAnyBlocks('Testimonials: title'))
<section class="testimonials" id="testimonials">
    <div class="main-testimonial">
        <div class="layout-box">
            <h2 class="heading">
                {!! ContentManager::contentBlocks('Testimonials: title') !!}
            </h2>
            <p class="text">
                {!! ContentManager::contentBlocks('Testimonials - Main testimonial: text') !!}
            </p>
            <div class="details">
                <label>
                    {!! ContentManager::contentBlocks('Testimonials - Main testimonial: customer name') !!}
                </label>
                {!! ContentManager::contentBlocks('Testimonials - Main testimonial: date') !!}
            </div>
        </div>

        <img src="{{ override_asset('/assets/images/quote.svg') }}" class="quote-image" alt="{{ t('Quote image') }}" />
    </div>

    @php

    $blocks = ContentManager::contentBlocks(
    'Testimonials: list',
    join: false,
    noParagraph: false
    );

    @endphp

    @if (!empty($blocks))
    <div class="testimonials-track">
        @foreach ($blocks as $block)
        <div class="testimonial-item">
            {!! $block !!}
        </div>
        @endforeach
    </div>
    @endif
</section>
@endif