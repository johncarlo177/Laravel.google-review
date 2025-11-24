@php
    if ($currency == '$') {
        $currency = '\\$';
    }
@endphp
<section class="pricing account-credit-pricing">
    <div class="text-wrapper">
        <h2 class="section-title">
            {{ t('Simple one time pricing') }}
        </h2>
        <p class="explainer">
            {{ t('Pay for what you need only') }}
        </p>
    </div>

    <div class="layout-box">
        <div class="blocks">
            <div class="block">
                <div class="block-box static">
                    <div class="qrcode-type">{{ t('Static QR Codes') }}</div>
                    <div class="price">
                        <div class="amount">
                            @if ($composer->currencyBefore())
                                {{ $currency }}{{ $composer->staticQRCodePrice() }}
                            @else
                                {{ $composer->staticQRCodePrice() }}{{ $currency }}
                            @endif
                        </div>
                        <div class="description">
                            <div>{{ t('Per QR Code') }}</div>
                            <div>{{ t('One Time') }}</div>
                        </div>
                    </div>
                    <p>
                        {{ t('Design beautiful static QR codes and download them, 50+ outlined shapes available and 10+ types.') }}
                    </p>
                    <a href="/checkout-account-credit" class="button accent">
                        {{ t('Buy Account Credit') }}
                    </a>
                </div>
                <ul class="features with-margin">
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('1400+ Google fonts in stickers.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('8 stickers available.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('Advaned gradients.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('Add your logo to QR code.') }}
                    </li>
                    <li class="no">
                        @include('blue.components.icons.close-thick')
                        {{ t('No stats') }}
                    </li>
                </ul>
                <p class="features-title">{{ t('QR Code Types') }}</p>
                <ul class="features">
                    @foreach ($composer->getStaticTypes() as $type)
                        <li class="yes">
                            @include('blue.components.icons.check-bold')
                            {{ $type::name() }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="block">
                <div class="block-box dynamic">
                    <div class="qrcode-type">{{ t('Dynamic QR Codes') }}</div>
                    <div class="price">
                        <div class="amount">
                            @if ($composer->currencyBefore())
                                {{ $currency }}{{ $composer->dynamicQRCodePrice() }}
                            @else
                                {{ $composer->dynamicQRCodePrice() }} {{ $currency }}
                            @endif
                        </div>
                        <div class="description">
                            <div>{{ t('Per QR Code') }}</div>
                            <div>{{ t('One Time') }}</div>
                        </div>
                    </div>
                    <p>
                        {{ t('Design beautiful static QR codes and download them, 50+ outlined shapes available and 10+ types.') }}
                    </p>
                    <a href="/checkout-account-credit" class="button accent">
                        {{ t('Buy Account Credit') }}
                    </a>
                </div>
                <ul class="features with-margin">
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('1400+ Google fonts in stickers.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('8 stickers available.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('Advaned gradients.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('Add your logo to QR code.') }}
                    </li>
                    <li class="yes">
                        @include('blue.components.icons.check-bold')
                        {{ t('Stats available') }}
                    </li>
                </ul>
                <p class="features-title">{{ t('QR Code Types') }}</p>
                <ul class="features">
                    @foreach ($composer->getDynamicTypes() as $type)
                        <li class="yes">
                            @include('blue.components.icons.check-bold')
                            {{ $type::name() }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
