<div
    class="inner-block inner-block-boxed dynamic-block-{{ $block->type($field) }} {{ $block->icon($field) ? '' : 'no-icon' }}">
    @if ($block->icon($field))
    <div class="icon">
        <img src="{{ $block->icon($field) }}" />
    </div>
    @endif

    <div class="details">
        <h3 class="field-name">
            {{ @$field['name'] }}
        </h3>
        <div class="field-value">
            {{ $block->value($field) }}
        </div>
    </div>
</div>