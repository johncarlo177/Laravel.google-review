<div class="block social-links-block" id="{{ $model->getId() }}">
    <div class="social-icons">
        @include('blue.components.social-links', ['urls' => $model->field('socialLinks')])
    </div>
</div>