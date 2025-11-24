@extends('qrcode.types.layout')

@section('qrcode-layout-head')
    {!! ContentManager::customCode('Lead Form: Head Tag Before Close') !!}
@endsection

@section('page')
    <div class="layout-generated-webpage">
        @if ($composer->leadFormId())
            @include('qrcode.components.lead-form.lead-form', ['id' => $composer->leadFormId(), 'mode' => 'full-page', 'qrcodeComposer' => $composer])
        @else
            {{ t('Loading ...') }}
        @endif
    </div>

    @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
        {!! $composer->designValue('custom_code') !!}
    @endif
@endsection

@section('powered-by')
@endsection
