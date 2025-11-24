<div class="question-page-footer">
    <div class="navigation">
        <button class="button primary down">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z" />
            </svg>
        </button>
        <button class="button primary up">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z" />
            </svg>
        </button>
    </div>

    @if ($qrcodeComposer->shouldShowPoweredBy())
        <div class="powered-by">
            {{ t('Powered by') }}
            <a href="{{ config('app.url') }}" class="powered-by">{{ $qrcodeComposer->poweredByName() }}</a>
        </div>
    @endif
</div>
