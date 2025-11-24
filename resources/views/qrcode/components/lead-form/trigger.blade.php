@if ($composer->shouldRender())

    @if ($composer->hasQuestions())
        <button class="lead-form-trigger button" data-target=".lead-form-{{ $id }}">
            {{ $composer->config('button_text', t('Contact Me')) }}
        </button>
    @elseif (request()->boolean('preview'))
        <button class="button">
            {{ t('Add some questions to make me functional') }}
        </button>
    @endif

@endif
