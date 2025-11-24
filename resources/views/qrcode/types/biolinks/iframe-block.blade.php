<div class="block iframe-block link-block" id="{{ $model->getId() }}">
    <a destination="{{ $block->url() }}">
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>
