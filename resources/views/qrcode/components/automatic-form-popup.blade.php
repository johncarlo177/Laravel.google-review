@php

    $enabled = $composer->designValue('automatic-form-popup-enabled') === 'enabled';

    $id = $composer->designValue('automatic_form_popup', null);

    $enabled = $enabled && !empty($id);

    $delay = $composer->getAutomaticFormPopupDelay();

    $affirmativeText = $composer->getAutomaticFormPopupButtonText();

@endphp


@if ($enabled)
    <script>
        (function() {

            function localStorageKey() {
                return 'did_show_automatic_form_popup_for_url' + window.location.href;
            }

            if (localStorage[localStorageKey()] === 'true') {
                return;
            }

            window.addEventListener(
                'qrcg-body-resolver::dashboard-bundle-ready',
                function() {

                    setTimeout(async () => {

                        await window.AutomaticFormModal.open({
                            formId: {{ $id }},
                            affirmativeText: {!! json_encode($affirmativeText) !!},
                            target: document.querySelector('.layout-generated-webpage'),
                            header_image: "{{ $composer->getAutomaticFormHeaderImage() }}",
                        })

                        localStorage[localStorageKey()] = "true"

                        document.dispatchEvent(new CustomEvent('vcard-file-generator::request-download'))

                    }, {{ $delay }});
                })
        })()
    </script>
@endif
