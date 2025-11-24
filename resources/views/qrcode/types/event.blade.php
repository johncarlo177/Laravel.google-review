@extends('qrcode.types.layout')

@section('qrcode-layout-head')
@endsection

@section('page')

    @include('qrcode.components.lead-form.lead-form', ['id' => $composer->designValue('lead_form_id'), 'qrcodeComposer' => $composer])

    <div class="layout-generated-webpage">
        @include('qrcode.components.banner-background')

        <div class="details-container">
            {!! ContentManager::customCode('Event: Before Content') !!}
            <div class="gradient-bg"></div>
            <div class="main-details">

                @if ($composer->shouldRenderLogo())
                    <div class="qrcode-container">
                        <img src="{{ $composer->fileUrl('logo') }}" />
                    </div>
                @endif

                <div class="vertical-list">
                    <h1 class="name">
                        {{ $composer->qrcodeData('event_name', 'Special Event') }}
                    </h1>

                    <p class="designation">
                        @if (empty(($intro = $composer->designValue('organized_by_text'))))
                            {{ t('Organized By') }}
                        @else
                            {{ $composer->designValue('introduction') }}
                        @endif
                    </p>

                    <p class="company">
                        {{ $composer->qrcodeData('organizer_name') }}
                    </p>

                    <div class="short-bio">
                        {!! $composer->qrcodeData('description') !!}
                    </div>
                </div>
            </div>


            <div class="classic-add-to-contacts">
                @if (!empty(($u = $composer->qrcodeData('registration_url'))))
                    <a class="button primary add-contact" href="{{ $u }}" target="_blank">
                        @if (empty(($t = $composer->designValue('registration_button_text'))))
                            {{ t('Register Now') }}
                        @else
                            {{ $t }}
                        @endif
                    </a>
                @endif

                @if ($composer->designValue('addToCalendarButtonEnabled') != 'disabled')
                    <add-to-calendar-button name="{{ $composer->qrcodeData('event_name') }}" timeZone="{{ $composer->qrcodeData('timezone') }}" location="{{ $composer->qrcodeData('location') }}"
                        description="{{ $composer->qrcodeData('description') }}" options="'Apple','Google','iCal','Outlook.com','Microsoft 365','Microsoft Teams','Yahoo'" lightMode="bodyScheme"
                        listStyle="modal" dates='{!! $composer->dates() !!}' label="{{ is_string($t = $composer->designValue('addToCalendarButtonText')) ? $t : t('Add to Calendar') }}"
                        customLabels='{!! $composer->customAddToCalendarButtonLabels() !!}'>

                    </add-to-calendar-button>
                @endif
            </div>


            <div class="white-card date-card">
                <header>
                    @include(' blue.components.icons.calendar') {{ t('Date & Time') }} </header>

                <div class="body">
                    @foreach ($composer->daysBreakdown() as $day)
                        <div class="vertical-item">

                            <div class="key">
                                {{ $day['day_name'] }}
                            </div>

                            <div class="value">
                                {{ $composer->dayBreakdownDate($day) }}

                                @if (@$day['time_from'])
                                    <span class="time">
                                        @include('blue.components.icons.clock')
                                        {{ $day['time_from'] }}

                                        @if (@$day['time_to'])
                                            {{ t('to') }} {{ $day['time_to'] }}
                                        @endif
                                    </span>
                                @endif
                            </div>

                            @if (!empty(@$day['description']))
                                <p class="description">
                                    {{ $day['description'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if (!empty($composer->qrcodeData('location')))
                <div class="white-card location-card">
                    <header>
                        @include('blue.components.icons.map-marker')
                        {{ t('Location') }}
                    </header>

                    <div class="body">
                        <p>
                            {{ $composer->qrcodeData('location') }}
                        </p>

                        @if (!empty($composer->qrcodeData('location_url')))
                            <a class="get-directions" href="{{ $composer->qrcodeData('location_url') }}" target="_blank">
                                {{ t('Get Directions') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @include('qrcode.components.lead-form.trigger', ['id' => $composer->designValue('lead_form_id')])

            @if ($composer->shouldRenderContacts())
                <div class="white-card contacts-card">
                    <header>
                        @include('blue.components.icons.phone')
                        {{ t('Contacts') }}
                    </header>

                    <div class="body">

                        @foreach ($composer->contacts() as $item)
                            @if (!empty($item['value']))
                                <div class="vertical-item">
                                    <div class="key">
                                        {{ $item['name'] }}
                                    </div>
                                    <div class="value">
                                        @if (empty($item['link']))
                                            {{ $item['value'] }}
                                        @else
                                            <a href="{{ $item['link'] }}">
                                                {{ $item['value'] }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="social-icons">
                @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
            </div>

            @if (!empty($composer->faqs()))
                <div class="white-card faqs-card">
                    <header>
                        <div class="faq-main-title">
                            {{ $composer->faqsMainTitle() }}
                        </div>
                    </header>
                    <div class="faq-items">

                        @foreach ($composer->faqs() as $item)
                            <div class="faq-item">
                                <h3 class="faq-title">
                                    <span>
                                        {{ $item['title'] }}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <title>expand</title>
                                        <path d="M5.59,7.41L7,6L13,12L7,18L5.59,16.59L10.17,12L5.59,7.41M11.59,7.41L13,6L19,12L13,18L11.59,16.59L16.17,12L11.59,7.41Z" />
                                    </svg>
                                </h3>
                                <div class="faq-description">
                                    {{ @$item['description'] }}
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endif


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

        </div>
    </div>

@endsection
