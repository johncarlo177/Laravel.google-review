@php
    $enabled = $composer->designValue('desktop_customizations') == 'enabled';

    $left_url = file_url($composer->notEmptyDesignValue('desktop_left_image', ''));
    $right_url = file_url($composer->notEmptyDesignValue('desktop_right_image'));

    $left_color = $composer->notEmptyDesignValue('desktop_left_color');
    $right_color = $composer->notEmptyDesignValue('desktop_right_color');

    $should_render = $enabled && !empty(array_filter([$left_url, $right_url, $left_color, $right_color]));
@endphp

@if ($should_render)

    @if ($left_url)
        <img src="{{ $left_url }}" class="preload-image" />
    @endif

    @if ($right_url)
        <img src="{{ $right_url }}" class="preload-image" />
    @endif

    <style>
        .desktop-placeholder {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            height: 100%;
            width: 100%;
            pointer-events: none;
            user-select: none;
            display: flex;
            z-index: -1;
        }

        .desktop-placeholder .middle {
            max-width: 500px;
            width: 100%;
        }

        .desktop-placeholder .desktop-image {
            flex: 1;
            background-size: cover;
            background-position: center;
        }

        .desktop-placeholder .desktop-image.left {
            background-color: {{ $left_color }};

            background-image: url({{ $left_url }});
        }

        .desktop-placeholder .desktop-image.right {
            background-color: {{ $right_color }};

            background-image: url({{ $right_url }});
        }

        @media (max-width: 500px) {
            .desktop-placeholder {
                display: none;
            }
        }
    </style>

    <div class="desktop-placeholder">
        <div class="desktop-image left">
        </div>
        <div class="middle">
        </div>
        <div class="desktop-image right">
        </div>
    </div>

@endif
