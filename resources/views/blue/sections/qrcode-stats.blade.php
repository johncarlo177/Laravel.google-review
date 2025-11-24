@php
    $background = config_file_url('appearance.stats_image');
@endphp

@if (ContentManager::hasAnyBlocks('QR code stats: title'))
    <section class="qrcode-stats">
        <div class="layout-box">
            <div class="text-wrapper">
                <p class="tag-line">
                    {!! ContentManager::contentBlocks('QR code stats: tag line') !!}

                </p>
                <h2 class="section-title">
                    {!! ContentManager::contentBlocks('QR code stats: title') !!}
                </h2>
                <p class="explainer">
                    {!! ContentManager::contentBlocks('QR code stats: text') !!}
                </p>
            </div>

            <img class="main-image" src="{{ $background ?? override_asset('/assets/images/qrcode-stats.jpg') }}" alt="{{ t('Statistics') }}" loading="lazy" />

        </div>
    </section>
@endif
