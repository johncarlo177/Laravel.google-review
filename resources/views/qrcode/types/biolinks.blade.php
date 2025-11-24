@extends('qrcode.types.layout')

@section('qrcode-layout-head')
    {!! $composer->blockStyleTags() !!}

    <script>
        window.SMOOTH_SCROLLER_SMOOTH_PREVIEW = {{ $composer->designField('animate_preview') === 'enabled' ? 'true' : 'false' }}
        window.SMOOTH_SCROLLER_IS_ONE_CYCLE = {{ $composer->designField('animate_preview_one_cycle') === 'enabled' ? 'true' : 'false' }}
    </script>
@endsection

@section('page')
    @include('qrcode.components.splash-screen')

    <div class="layout-generated-webpage">
        
        {!! ContentManager::customCode('BioLinks: before banner') !!}

        @include('qrcode.components.dynamic-banner')

        <div class="details-container">
            <div class="details-bg">
                @if ($composer->hasVideoBackground())
                    <video class="video-bg" autoplay muted playsinline loop>
                        <source src="{{ $composer->fileUrl('biolinksBackgroundVideo') }}">
                        </source>
                    </video>
                @endif
            </div>


            <div class=main-details>
                @include('qrcode.components.profile-image')

                {!! ContentManager::customCode('BioLinks: start') !!}

                @foreach ($composer->blocks() as $block)
                    {!! ContentManager::customCode(sprintf('BioLinks %s: before', $block::slug())) !!}

                    {!! $block->render($composer) !!}

                    {!! ContentManager::customCode(sprintf('BioLinks %s: after', $block::slug())) !!}
                @endforeach

                {!! ContentManager::customCode('BioLinks: end') !!}
            </div>
        </div>

        @include ('qrcode.components.stack-component', ['stack_data' => $composer->designField('stack_data'), 'enabled' => $composer->designField('stack_enabled')])
    </div>

    @include('qrcode.components.automatic-form-popup')

    @php
        $action = PluginManager::ACTION_BIOLINKS_AFTER_LAYOUT;

        echo PluginManager::doAction($action, $composer->getQRCode());
    @endphp

    @include('qrcode.components.welcome-modal')
@endsection
