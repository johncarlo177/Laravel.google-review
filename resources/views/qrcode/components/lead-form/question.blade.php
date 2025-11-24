<div class="question-page {{ $composer->isQuestionRequired($question) ? 'required' : '' }}">
    <div class="question-container">
        <div class="question">
            <div class="question-number">
                {{ $composer->questionNumber($question) }}-
            </div>

            <div class="question-text">
                {{ $composer->questionText($question) }}
            </div>

            @if ($composer->shouldRenderQuestionDescription($question))
                <div class="question-description">
                    {{ $composer->questionDescription($question) }}
                </div>
            @endif
        </div>

        @include ('qrcode.components.lead-form.answers.' . $question['type'], compact('question', 'id'))

    </div>

    <div class="ok-button {{ $composer->isLastQuestion($question) ? 'submit' : '' }}">

        <button class="button primary">

            {{ $composer->isLastQuestion($question) ? $composer->submitButtonText() : $composer->okButtonText() }}

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" />
            </svg>
        </button>

    </div>

</div>
