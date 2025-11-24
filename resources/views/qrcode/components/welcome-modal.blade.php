@php

    $enabled = $composer->designField('welcome_popup_enabled');

@endphp


@if ($enabled === 'enabled')
    <script>
        (function() {

            const config = {
                welcome_popup_enabled: "{{ $composer->designField('welcome_popup_enabled') }}",
                welcome_modal_show_times: {{ $composer->designField('welcome_modal_show_times') ?? 1 }},
                welcome_modal_video_url: {!! json_encode(file_url($composer->designField('welcome_modal_video'))) ?? 'null' !!},
                welcome_modal_image_url: {!! json_encode(file_url($composer->designField('welcome_modal_image'))) ?? 'null' !!},
                welcome_modal_text: `{!! $composer->designField('welcome_modal_text') !!}`
            }

            window.addEventListener(
                'qrcg-body-resolver::dashboard-bundle-ready',
                function() {

                    window.WelcomeModal.configObject = config

                    window.WelcomeModal.openIfNeeded()
                })



        })()
    </script>
@endif
