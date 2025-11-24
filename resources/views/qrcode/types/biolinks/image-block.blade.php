<div class="block image-block {{ $block->classString() }}" id="{{ $model->getId() }}">
    <img src="{{ $model->fileUrl('image') }}" />

    @if ($model->notEmpty('text'))
        <div class="caption">{{ $model->field('text') }}</div>
    @endif

    @if ($model->notEmpty('description'))
        <div class="description">{!! $model->field('description') !!}</div>
    @endif

    @if ($block->url())
        <a href="{{ $block->url() }}"></a>
    @endif
</div>
