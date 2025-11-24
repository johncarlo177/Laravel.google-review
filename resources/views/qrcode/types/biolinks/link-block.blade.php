<div class="block link-block" id="{{ $model->getId() }}">
    <a href="{{ $block->url() }}" {!! $block->linkTarget() !!}>
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>
