<div class="block qrcode-details-block" id="{{ $model->getId() }}">
    <div class=slug>
        {{ $block->getQRCodeSlug()}}
    </div>

    <div class=number-of-scans>
        @if (!$block->getScansCount())
            <div class=no-scans-message>
                {{ t('No scans yet.')}}
            </div>
        @else
        
            @if ($block->hasScanLimit())
                {{ $block->getScansCount() }} / {{ $block->getScanLimit() }}  {{ $block->getScansText() }}
            @else 
                {{ $block->getScansCount() }} {{ $block->getScansText() }}
            @endif

        @endif


    </div>
</div>

<script>
    window.QRCODE_DETAILS_SCANS_COUNT = '{{ $block->getScansCount() }}';
    window.QRCODE_DETAILS_QRCODE_SLUG = '{{ $block->getQRCodeSlug()}}';
    window.QRCODE_DETAILS_SCAN_LIMIT = '{{ $block->getScanLimit() }}';
    window.QRCODE_DETAILS_DID_REACH_LIMITS = {{ $block->didReachLimit() ? "true" : "false" }};
</script>