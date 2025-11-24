@if (ContentManager::hasAnyBlocks('Showcase Gradient: text'))
    <section class="qrcode-showcase" id="gradients">
        <div class="modal-content">
            <div class="layout-box">
                <div class="close-btn">
                    <svg viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                    </svg>
                </div>
                <h2 class="heading">
                    {!! ContentManager::contentBlocks('Showcase Gradient Modal: heading') !!}
                </h2>
                <p class="sub-heading">
                    {!! ContentManager::contentBlocks('Showcase Gradient Modal: text') !!}
                </p>
                <div class="image-gallery">
                    @php
                        $images = array_map(fn($f) => "/assets/images/gradient/qrcode-gradient-showcase-$f.png", range(1, 20));
                    @endphp

                    @foreach ($images as $image)
                        <div class="image animate-cascade">
                            <img src="{{ override_asset($image) }}" alt="QR Code with gradient" loading='lazy' />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="layout-box">
            <div class="wrapper" style="background-image: url({{ override_asset('/assets/images/qrcode-showcase-bg-1.png') }})">
                <div class="overlay">
                    <div class="overlay-wrapper">
                        <div class="text">
                            {!! ContentManager::contentBlocks('Showcase Gradient: overlay') !!}
                        </div>
                        <div class="arrow">➞</div>
                    </div>
                </div>
                <p class="tag">
                    {!! ContentManager::contentBlocks('Showcase Gradient: tag') !!}
                </p>
                <p class="explainer">
                    {!! ContentManager::contentBlocks('Showcase Gradient: text') !!}
                </p>
                <div class="images">
                    <div class="image">
                        <img src="{{ override_asset('/assets/images/gradient/qrcode-gradient-showcase-1.png') }}" alt="QR Code with gradient" loading='lazy' />
                    </div>

                    <div class="image">
                        <img src="{{ override_asset('/assets/images/gradient/qrcode-gradient-showcase-5.png') }}" alt="QR Code with gradient" loading='lazy' />
                    </div>

                    <div class="image">
                        <img src="{{ override_asset('/assets/images/gradient/qrcode-gradient-showcase-15.png') }}" alt="QR Code with gradient" loading='lazy' />
                    </div>

                    <div class="image">
                        <img src="{{ override_asset('/assets/images/gradient/qrcode-gradient-showcase-20.png') }}" alt="QR Code with gradient" loading='lazy' />
                    </div>

                    <div class="image">
                        <img src="{{ override_asset('/assets/images/gradient/qrcode-gradient-showcase-17.png') }}" alt="QR Code with gradient" loading='lazy' />
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
