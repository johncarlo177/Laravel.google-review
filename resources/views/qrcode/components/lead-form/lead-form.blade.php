@php

    $layout = isset($layout) ? $layout : null;

@endphp

@if ($composer->shouldRender())

    {!! $composer->styles() !!}

    {!! PluginManager::doAction(PluginManager::ACTION_LEAD_FORM_BEFORE_RENDER) !!}

    @php
        $mode = isset($mode) ? $mode : 'inline';
    @endphp

    <script>
        window.__LEAD_FORM_SUCCESS_MESSAGE__ = "{{ t('Thank you for submitting the form') }}";
    </script>

    <div class="lead-form lead-form-{{ $composer->id() }} {{ $mode }}" data-id="{{ $composer->id() }}" layout="{{ $layout }}" @if (!$composer->isMultipleSubmissionAllowed()) single-submission @endif
        data-after-submit-url="{{ $composer->afterSubmitUrl() }}">
        @include('qrcode.components.lead-form.close-button')
        @include('qrcode.components.lead-form.header')
        @include('qrcode.components.lead-form.single-submission-overlay')

        <div class="questions">

            @foreach ($composer->questions() as $question)
                @include ('qrcode.components.lead-form.question', compact('question'))
            @endforeach

        </div>

        @include('qrcode.components.lead-form.footer')

        @include('qrcode.components.lead-form.success-page')

    </div>

@endif
