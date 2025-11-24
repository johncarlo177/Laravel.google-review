@if (ContentManager::hasAnyBlocks('Pricing: title'))
    <section class="pricing" id="pricing">
        <div class="text-wrapper">
            <p class="tag-line">
                {!! ContentManager::contentBlocks('Pricing: tagline') !!}
            </p>
            <h2 class="section-title">
                {!! ContentManager::contentBlocks('Pricing: title') !!}
            </h2>
        </div>

        @php

            $billingCycle = config('pricing.default_billing_cycle');

            $monthlyActive = $billingCycle === 'monthly';

            $yearlyActive = empty($billingCycle) || $billingCycle === 'yearly';

            $liftimeActive = $billingCycle === 'life-time';

        @endphp

        <div class="pricing-switch">
            <div class="pricing-switch-box">
                <div class="switch-item {{ $monthlyActive ? 'active' : '' }}" tab-id="monthly">
                    {{ t('Monthly Billing') }}
                </div>
                <div class="switch-item {{ $yearlyActive ? 'active' : '' }}" tab-id="yearly">
                    {{ t('Yearly Billing') }}
                </div>

                @if ($composer->hasLifetimePlans())
                    <div class="switch-item {{ $liftimeActive ? 'active' : '' }}" tab-id="life-time">
                        {{ t('Life Time') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="layout-box">
            <div class="blocks">
                <div class="items-wrapper" style="background-image: url({{ override_asset('/assets/images/qrcode-showcase-bg-1.png') }})">

                    <div class="tag" tab-id="yearly">
                        {{ t('Paid yearly') }}
                    </div>

                    <div class="tag" tab-id="monthly">
                        {{ t('Paid monthly') }}
                    </div>

                    <div class="tag" tab-id="life-time">
                        {{ t('One Time') }}
                    </div>

                    <div class="items">
                        @foreach ($plans as $plan)
                            <div class="item" tab-id="{{ $plan->frequency }}">
                                <h3 class="heading">
                                    {{ $composer->name($plan) }}
                                </h3>
                                <div class="price">
                                    @if ($composer->planIsFree($plan))
                                        <span class="free">
                                            {{ t('FREE') }}
                                        </span>
                                    @else
                                        @if ($composer->currencyBefore())
                                            <span class="currency">{{ $currency }}</span>
                                            <span class="number">
                                                {{ $composer->price($plan) }}
                                            </span>
                                        @else
                                            <span class="number">
                                                {{ $composer->price($plan) }}
                                            </span>
                                            <span class="currency">{{ $currency }}</span>
                                        @endif
                                        <span class="sep">/</span>

                                        <span class="term">
                                            {{ $composer->getPlanFrequencyText($plan) }}
                                        </span>
                                    @endif
                                </div>
                                <p class="below-price">
                                    @if ($composer->shouldShowNumberOfUsers($plan))
                                        {{ $composer->numberOfUsers($plan) }}
                                    @else
                                        {!! ContentManager::contentBlocks('Pricing: below price') !!}
                                    @endif
                                </p>
                                <ul class="text-list">
                                    {!! $composer->tableMenuItem($composer->formatTotalValue($plan->number_of_scans), t('scans')) !!}

                                    {!! $composer->tableMenuItem($composer->formatTotalValue($plan->number_of_dynamic_qrcodes), t('dynamic QR codes')) !!}

                                    {!! $composer->tableMenuItem($composer->outlinedShapes($plan)->count(), t('shapes')) !!}

                                    {!! $composer->tableMenuItem($composer->stickers($plan)->count(), t('stickers')) !!}

                                    <li>1400+ {{ t('fonts') }}</li>

                                    <li>{{ count($plan->qr_types) }} {{ t('QR code types') }}</li>

                                    @php
                                        $d = $plan->number_of_custom_domains;
                                    @endphp

                                    {!! $composer->tableMenuItem($composer->formatTotalValue($d), t('domains can be connected')) !!}

                                    <li>
                                        @php
                                            $d = $plan->file_size_limit;
                                            $d = $composer->formatTotalValue($d);
                                        @endphp

                                        {{ $d }} {{ t('MB') }} {{ t('max upload file size.') }}
                                    </li>

                                    @if ($composer->aiGenerationEnabled())
                                        @php
                                            $d = $plan->number_of_ai_generations;
                                            $d = $composer->formatTotalValue($d);
                                        @endphp

                                        @if ($d)
                                            <li>
                                                {{ $d }} {{ t('monthly AI generations allowed.') }}
                                            </li>
                                        @endif
                                    @endif

                                    @if ($composer->isLarge())
                                        @php
                                            $d = $plan->number_of_restaurant_menu_items;
                                        @endphp

                                        {!! $composer->tableMenuItem($composer->formatTotalValue($d), t('items can be added in') . ' ' . t('Restaurant Menu')) !!}
                                    @endif

                                    @if ($composer->isLarge())
                                        @php
                                            $d = $plan->number_of_product_catalogue_items;
                                        @endphp

                                        {!! $composer->tableMenuItem($composer->formatTotalValue($d), t('items can be added in') . ' ' . t('Product Catalogue')) !!}
                                    @endif

                                    <li>
                                        {{ $composer->scanRetention($plan) }} {{ t('stats retention') }}.
                                    </li>

                                    @foreach ($plan->getAllTypesWithLimitedAllowance() as $type)
                                        @php
                                            $allowance = $plan->getSpecificTypeLimits($type);
                                        @endphp
                                        <li>
                                            {{ $composer->formatTotalValue($allowance) }} <qrcg-qrcode-type-name type="{{ $type }}"></qrcg-qrcode-type-name>
                                        </li>
                                    @endforeach
                                </ul>

                                <ul class="icons-list">

                                    @foreach ($composer->checkpoints($plan) as $i => $checkpoint)
                                        <li class="{{ $composer->checkpointClass($i) }}">{{ $composer->checkpoint($i) }}</li>
                                    @endforeach

                                </ul>

                                <a href="{{ $composer->checkoutUrl($plan) }}" class="button accent buy-button">
                                    {{ $composer->getButtonText($plan) }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
