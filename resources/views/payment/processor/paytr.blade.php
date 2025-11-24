@extends('payment.processor.layout', compact('subscription'))

@section('head')
@parent

<style>
    #paytriframe {
        min-width: 445px;
    }
</style>
@endsection

@section('payform')

<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>

<iframe src="{{ $processor->generateIframeLink($subscription) }}" id="paytriframe" frameborder="0" scrolling="no"
    style="width: 100%;"></iframe>
<script>
    iFrameResize({},'#paytriframe');
</script>

@endsection