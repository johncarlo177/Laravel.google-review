<div class="block youtube-block" id="{{ $model->getId() }}">
    @if (!$block->isYoutubeIframe())
        <div class="error-message">
            {{ t('Not a YouTube iframe.')}}
        </div>
    @else
        <div class="iframe-container">
            {!! $model->field('youtube_iframe') !!}
        </div>
    @endif
</div>