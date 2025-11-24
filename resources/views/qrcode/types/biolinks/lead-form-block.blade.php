<div class="block lead-form-block link-block" id="{{ $model->getId() }}">
    <a data-target=".lead-form-{{ $model->field('lead_form_id') }}" class="lead-form-trigger">
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>

@include('qrcode.components.lead-form.lead-form', ['id' => $model->field('lead_form_id'), 'qrcodeComposer' => $composer, 'ignoreDisabledConfig' => true])
