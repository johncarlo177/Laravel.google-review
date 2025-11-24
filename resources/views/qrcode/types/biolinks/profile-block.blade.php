<div class="block profile-block" id="{{ $model->getId() }}">

    @if ($model->notEmpty('background_image'))
        <div class="background"></div>
    @endif

    <div class="image"></div>

    @if ($model->notEmpty('text'))
        <div class="handle">{{ $model->field('text') }}</div>
    @endif
</div>
