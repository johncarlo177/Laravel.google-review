@extends('qrcode.types.layout')

@section('qrcode-layout-head')
    {!! ContentManager::customCode('Restaurant Menu: Head Tag Before Close') !!}
@endsection

@section('loader')
@show

@section('page')
    <div class="layout-generated-webpage {{ $composer->getLayoutClasses() }}">
        {!! ContentManager::customCode('Restaurant Menu: Before Content') !!}

        {!! $composer->renderCategories() !!}

        {!! $composer->buy()->renderConfigs() !!}

        <div class="main-page">
            {!! $composer->renderImageCarousel() !!}
            
            @if ($composer->shouldShowLogo())
                <img src="{{ $composer->logo() }}" class="logo" />
            @endif

            <h1 class="main-line">
                {{ $composer->menuName() }}
            </h1>

            <div class="restaurant-details">
                <div class="restaurant-name">
                    {{ $composer->qrcodeData('restaurant_name') }}
                </div>

                @if ($composer->qrcodeData('address'))
                    <div class="restaurant-address">
                        {{ $composer->qrcodeData('address') }}
                    </div>
                @endif

                @if ($composer->qrcodeData('phone'))
                    <div class="restaurant-phone">
                        {{ $composer->qrcodeData('phone') }}
                    </div>
                @endif

                <div class="opening-hours">
                    @include('qrcode.components.opening-hours', ['hours' => $composer->openingHours()])
                </div>

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

                    @if (!empty($composer->qrcodeData('review_url')))
                        <a href="{{ $composer->qrcodeData('review_url') }}" target="_blank">
                            @include('blue.components.icons.star')
                        </a>
                    @endif

                    @if (!empty($composer->qrcodeData('website')))
                        <a href="{{ $composer->qrcodeData('website') }}" target="_blank">
                            @include('blue.components.icons.web')
                        </a>
                    @endif

                    @if (!empty($composer->qrcodeData('maps_url')))
                        <a href="{{ $composer->qrcodeData('maps_url', '#') }}" target="_blank">
                            @include('blue.components.icons.map-marker')
                        </a>
                    @endif

                    @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
                </div>

                @include('qrcode.components.review-sites')

                @include('qrcode.components.additional-information-popup')
            </div>

            <div class="categories-container">
                <div class="categories">
                    @foreach ($composer->topLevelCategories() as $category)
                        <div class="menu-category" slug="{{ $category['id'] }}">
                            {{ $category['name'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
        {!! $composer->designValue('custom_code') !!}
    @endif
@endsection
