@php
    $title = isset($title) ? $title : t('FAQs');

    $subtitle = isset($subtitle) ? $subtitle : '';

    $items = isset($items) ? $items : [];

    $hasHeaderBorder = isset($hasHeaderBorder) ? $hasHeaderBorder : true;

    $renderEnclosingCardTag = isset($renderEnclosingCardTag) ? $renderEnclosingCardTag : true;
    //
@endphp

@if ($renderEnclosingCardTag)
    <div class="white-card faqs-card">
@endif

<header class="{{ $hasHeaderBorder ? 'has-border' : '' }}">
    <div class="faq-main-title">
        {{ $title }}
    </div>

    @if (!empty($subtitle))
        <div class=faq-main-subtitle>
            {{ $subtitle }}
        </div>
    @endif
</header>

<div class="faq-items">

    @foreach ($items as $item)
        <div class="faq-item">
            <h3 class="faq-title">
                <span>
                    {!! $item['title'] !!}
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <title>chevron-right</title>
                    <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                </svg>
            </h3>
            <div class="faq-description">
                {!! @$item['description'] !!}
            </div>
        </div>
    @endforeach

</div>

@if ($renderEnclosingCardTag)
    </div>
@endif
