<div class="block dynamic-block dynamic-block-{{ $block::id() }}" id="{{ $model->getId() }}">
    @foreach ($block->fields() as $field)
        @if (@$field['name'])
            {!! ContentManager::customCode(sprintf('%s %s: %s', $block::name(), $field['name'], 'before')) !!}
        @endif

        @if ($block->shouldRenderField($field, $composer))
            @include('qrcode.types.biolinks.dynamic-block.' . $block->type($field))
        @endif

        @if (@$field['name'])
            {!! ContentManager::customCode(sprintf('%s %s: %s', $block::name(), $field['name'], 'after')) !!}
        @endif
    @endforeach
</div>

{!! $block->customCode() !!}
