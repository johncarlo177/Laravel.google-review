@if ($composer->designValue('share_button_enabled') === 'enabled')
    <a href="#" class="button share-button">
        {{ $composer->designValue('share_button_text', null) ?? t('Share') }}
    </a>

    <script>
        (function() {

            function onClick(e) {
                e.preventDefault()
                e.stopImmediatePropagation()

                navigator.share({
                    url: window.location.href
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const link = document.querySelector('.share-button');

                link.addEventListener('click', onClick)
            })
        })()
    </script>

    @if ($c = $composer->designValue('share_button_background_color'))
        <style>
            .share-button {
                background-color: {{ $c }};
                border-color: {{ $c }};
            }

            .share-button:hover {
                background-color: {{ $c }};
                border-color: {{ $c }};
            }
        </style>
    @endif

    @if ($c = $composer->designValue('share_button_text_color'))
        <style>
            .share-button {
                color: {{ $c }};
            }

            .share-button:hover {
                color: {{ $c }};
            }
        </style>
    @endif
@endif
