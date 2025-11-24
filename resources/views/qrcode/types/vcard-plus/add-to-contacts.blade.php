@php

$type = $composer->designValue('add_to_contacts_button_style');

$configPosition = $composer->designValue('add_to_contacts_button_position');

$configPosition = empty($configPosition) ? 'top-section' : $configPosition;

$text = $composer->designValue('add_to_contacts_button_text', t('Add to Contacts'));

$shouldRender = $configPosition === 'both';

if (!$shouldRender) $shouldRender = $position === $configPosition;

@endphp

@if ($shouldRender)

@if ($type === 'classic')

<div class="classic-add-to-contacts">
    <button class="button add-contact">
        {{ $text }}
    </button>
</div>

@endif

@endif