@if(ContentManager::hasAnyBlocks('QR Code Types: title'))
<section class="qrcode-types">
    <div class="layout-box">
        <div class="text-wrapper">
            <p class="tag-line">
                {!! ContentManager::contentBlocks('QR Code Types: tag') !!}
            </p>
            <h2 class="section-title">
                {!! ContentManager::contentBlocks('QR Code Types: title') !!}
            </h2>
            <p class="explainer">
                {!! ContentManager::contentBlocks('QR Code Types: text') !!}
            </p>
        </div>
        <div class="boxes">
            @foreach (App\Models\QRCode::getTypes() as $type)

            @php

            $title = ContentManager::contentBlocks("QR Code Types ($type): title");

            $title = trim($title);

            if (empty($title)) continue;

            @endphp

            <div class="single-box">
                <h3 class="heading">
                    {!! $title !!}
                </h3>

                <div class="badge">
                    @php

                    $badge = trim(ContentManager::contentBlocks("QR Code Types ($type): badge"));

                    $badge = empty($badge) ? null : $badge;

                    @endphp

                    {!! $badge ?? t('Static') !!}
                </div>

                {!! ContentManager::contentBlocks("QR Code Types ($type): text", noParagraph: false) !!}
            </div>
            @endforeach
        </div>
        <div class="show-more">
            {{ t('Show more types') }}
        </div>
    </div>
</section>
@endif