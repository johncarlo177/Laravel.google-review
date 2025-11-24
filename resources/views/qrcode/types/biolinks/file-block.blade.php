<div class="block file-block" id="{{ $model->getId() }}">
    <a href="{{ $model->fileUrl('file') }}" target="_blank">
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>
