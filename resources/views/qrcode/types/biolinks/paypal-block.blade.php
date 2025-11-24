<div class="block paypal-block" id="{{ $model->getId() }}">
    <a href="{{ $block->getUrl() }}" target="_blank">
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>
