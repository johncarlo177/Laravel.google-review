<div class="block logo-block" id="{{ $model->getId() }}">
    <img src="{{ $model->fileUrl('logo') }}" />

    @if ($model->notEmpty('text'))
    <div class="handle">{{ $model->field('text') }}</div>
    @endif
</div>