@php
    /**
     * @see App\Support\ViewComposers\HeadConfigsComposer
     *
     **/

    $enabledWorkflows = App\Support\Auth\AuthManager::instance()->getEnabledNames();

    $aiIsEnabled = !empty(App\Models\Config::get('quickqr_art.api_key'));

@endphp


<script>
    history.scrollRestoration = 'manual'

    window.CONFIG = {!! $composer->configs() !!}

    window.QRCG_TRANSLATION = {!! $composer->translationFile() !!}

    window.QRCG_CURRENT_LOCALE = '{{ $composer->locale() }}'

    window.QRCG_DIRECTION = '{{ $composer->direction() }}'

    window.QRCG_BUNDLE_TYPE = 'build';

    window.QRCG_ENABLED_WORKFLOWS = {!! json_encode($enabledWorkflows) !!};

    window.QRCG_AI_IS_ENABLED = {{ $aiIsEnabled ? 'true' : 'false' }};

    window.QRCG_VERSION = "{{ System::version() }}";

    window.QRCG_ENVIRONEMNT = '{{ app()->environment() }}';
</script>

{!! PageManager::renderQrTypeConfigsJsVariable() !!}

{!! App\Support\QRCodeTypes\QRCodeTypeManager::renderQrTypesSortOrder() !!}
