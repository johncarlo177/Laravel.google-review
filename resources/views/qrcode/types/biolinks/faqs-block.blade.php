<div class="block faqs-block" id="{{ $model->getId() }}">
    <div class="white-card faqs-card">

        @include('qrcode.components.faqs', [
            'title' => $model->field('main_title'),
            'subtitle' => $model->field('subtitle'),
            'items' => $model->field('faqs'),
            'hasHeaderBorder' => !empty($model->field('title_border_color')),
            'renderEnclosingCardTag' => false,
        ])

        @if ($model->field('link_enabled') === 'enabled')
            <a class="button" href="{{ $model->field('link_url') }}" target="_blank">
                {{ $model->field('link_text', t('Click Me')) }}
            </a>
        @endif

    </div>

</div>
