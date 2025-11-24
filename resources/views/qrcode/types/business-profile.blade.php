@extends('qrcode.types.layout')

@section('qrcode-layout-head')
@endsection

@section('page')

    @include('qrcode.components.lead-form.lead-form', ['id' => $composer->designValue('lead_form_id'), 'qrcodeComposer' => $composer])

    <div class="layout-generated-webpage">
        @include('qrcode.components.banner-background')

        <div class="details-container">
            <div class="main-details">
                <div class="business-logo-container">
                    <div class="logo-circle" style="
                        background-image: url({{ $composer->logo() }});
                    "></div>

                    <img src="{{ $composer->logo() }}" alt="" class="preload-image" />
                </div>
                <div class="vertical-list">
                    <h1 class="business-name">
                        {{ $composer->qrcodeData('businessName', 'Emily Bakery') }}
                    </h1>
                    <p class="business-description">
                        {{ $composer->qrcodeData('businessDescription', 'We provide high quality bakery products, get your first order now!') }}
                    </p>
                    @if ($url = $composer->getWebsiteUrl())
                        <a href="{{ $url }}" class="button white">{{ t('Know more') }}</a>
                    @endif
                </div>
            </div>

            <div class="sep"></div>

            <div class="social-icons">
                @if (!empty($composer->qrcodeData('phone')))
                    <a href="tel:{{ $composer->qrcodeData('phone') }}">
                        @include('blue.components.icons.phone')
                    </a>
                @endif

                @if (!empty($composer->qrcodeData('email')))
                    <a href="mailto:{{ $composer->qrcodeData('email', '#') }}">
                        @include('blue.components.icons.email')
                    </a>
                @endif

                @if (!empty($composer->qrcodeData('maps_url')))
                    <a href="{{ $composer->qrcodeData('maps_url', '#') }}" target="_blank">
                        @include('blue.components.icons.map-marker')
                    </a>
                @endif
            </div>

            @include('qrcode.components.review-sites')

            {!! $composer->renderUpiBlock() !!}

            @if ($composer->qrcodeData('openingHoursEnabled') != 'disabled')
                <div class="white-card">
                    <header>
                        @include('blue.components.icons.timer')
                        {{ t('Opening Hours') }}
                    </header>

                    <div class="body">
                        @include('qrcode.components.opening-hours', ['hours' => $composer->openingHours()])
                    </div>
                </div>
            @endif

            @if (!empty($composer->qrcodeData('address')))
                <div class="white-card">
                    <header>
                        @include('blue.components.icons.map-marker')
                        {{ t('Address') }}
                    </header>

                    <div class="body">
                        <p>
                            {!! $composer->qrcodeData('address') !!}
                        </p>
                    </div>
                </div>
            @endif

            @include('qrcode.components.lead-form.trigger', ['id' => $composer->designValue('lead_form_id')])

            <div class="social-icons">
                @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
            </div>

            @if ($composer->shouldRenderPortfolio())
                <div class="portfolio">
                    @if ($title = $composer->designValue('portfolio_section_title'))
                        <h2 class="portfolio-title">
                            {{ $title }}
                        </h2>
                    @endif

                    @foreach ($composer->portfolio() as $image)
                        <div class="portfolio-image">
                            <img src="{{ $composer->portfolioItemImage($image) }}" />

                            @if (!empty($image['caption']))
                                <div class="caption">
                                    {{ $image['caption'] }}
                                </div>
                            @endif

                            @if (!empty($image['description']))
                                <div class="description">
                                    {!! $image['description'] !!}
                                </div>
                            @endif


                            @if (@$image['url'])
                                <a href="{{ $image['url'] }}" target="_blank"></a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
                {!! $composer->designValue('custom_code') !!}
            @endif

            @include('qrcode.components.additional-information-popup')

        </div>
    </div>

@endsection
