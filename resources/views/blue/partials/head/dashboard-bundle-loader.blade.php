@include('blue.partials.head.dashboard-styles')

@if (is_dev())
    <script type="module" src="http://localhost:8000/@@vite/client"></script>
    <script type="module" src="http://localhost:8000/src/index.js" id="dev"></script>
@else
    @include('blue.partials.head.dashboard-assets')
@endif