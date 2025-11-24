@if (is_dev())
    <script type=module crossorigin src=http://localhost:85/main.js></script>
@else
    @include('blue.partials.head.blue-assets')
@endif
