@php
    $enabled = $composer->designField('share-overlay-enabled');

    $enabled = $enabled != 'disabled';

    $appleWalletEnabled = App\Support\Apple\ApplePassGenerator::empty()->isEnabled();
@endphp

@if ($enabled)
    <div class="share-overlay-trigger">
        @include('blue.components.icons.share')
    </div>


    <div class="share-overlay">
        <div class="share-overlay-content">
            <header>
                <div class=close>
                    @include('blue.components.icons.close-thick')
                </div>
            </header>


            <div class="share-overlay-body">

                <p class=scan-text>
                    {{ t('Scan the QR Code') }}
                </p>

                <img src="{{ $composer->getQRCode()->getDirectSvgUrl() }}" alt="QR Code" class="qrcode-image">

                <p>
                    {{ t('Or Share On') }}
                </p>

                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="share-button facebook">
                        @include('blue.components.icons.social.facebook')
                    </a>

                    <a href="https://api.whatsapp.com/send?text={{ urlencode(request()->fullUrl()) }}" target="_blank" class="share-button whatsapp">
                        @include('blue.components.icons.social.whatsapp')
                    </a>

                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}" target="_blank" class="share-button twitter">
                        @include('blue.components.icons.social.twitter')
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}" target="_blank" class="share-button linkedin">
                        @include('blue.components.icons.social.linkedin')
                    </a>
                </div>

                <p>
                    {{ t('Or') }}
                </p>

                <button class="button native-share-button black">
                    @include('blue.components.icons.share-all')

                    <span class="button-text">
                        {{ t('Share another way') }}
                    </span>
                </button>

                @if ($appleWalletEnabled)
                    <a href="/add-to-apple-wallet/{{ $composer->getQRCode()->id }}" class="apple-wallet">
                        <img src="{{ url('/assets/images/add-to-apple-wallet.svg') }}" />
                    </a>
                @endif
            </div>

        </div>
    </div>
@endif
