@php
    $bannerType = $composer->designValue('banner_type');

    $allowedTypes = ['video', 'image'];

    $isTypeAllowed = collect($allowedTypes)->filter(fn($t) => $t === $bannerType)->isNotEmpty();

    $videoUrl = @file_url($composer->designValue('backgroundVideo'));
@endphp

@if ($isTypeAllowed)
    @if ($videoUrl)
        <video src="{{ $videoUrl }}" autoplay muted class="bg-video" loop playsinline>
        </video>
        <video src="{{ $videoUrl }}" autoplay muted class="bg-video-placeholder" loop playsinline>
        </video>
    @else
        <img src="{{ $composer->bg() }}" class="bg-image" />
        <img src="{{ $composer->bg() }}" class="bg-image-placeholder" />
    @endif
@endif
