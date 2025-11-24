@php
    $message = $composer->config('submission_blocked_message', t('This form has been already submitted.'));
@endphp

@if (!$composer->isMultipleSubmissionAllowed())
    <div class="single-submission-overlay">
        <div class="single-submission-text">
            {{ $message }}
        </div>
    </div>
@endif
