@php

    $enabled = isset($popup) ? 'enabled' : $composer->designValue('information_popup_enabled');

    $text = isset($popup) ? $popup['text'] : $composer->designValue('information_popup_link_text');
    //

    $text = is_string($text) ? $text : '';
@endphp


@if ($enabled === 'enabled')
    @php

        $title = isset($popup) ? $popup['title'] : $composer->designValue('information_popup_title') ?? '';

        $content = isset($popup) ? $popup['content'] : $composer->designValue('information_popup_content') ?? '';

        $title = is_string($title) ? $title : '';

        $content = is_string($content) ? $content : '';

    @endphp

    <div class="link information-pupup-link" data-title="{{ base64_encode($title) }}" data-content="{{ base64_encode($content) }}">
        {{ $text }}
    </div>



    <style>
        .information-pupup-link {
            text-align: center;
            padding: 1rem;
            line-height: 1.7;
            cursor: pointer;
        }
    </style>
@endif
