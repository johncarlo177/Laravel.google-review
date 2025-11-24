<div class="block opening-hours-block" id="{{ $model->getId() }}">
    <div class="white-card">
        <header>
            @include('blue.components.icons.timer')
            {{ $model->field('title_text', t('Opening Hours')) }}
        </header>

        <div class="body">
            @include('qrcode.components.opening-hours', ['hours' => $block->openingHours(), 'composer' => $block])
        </div>
    </div>
</div>
