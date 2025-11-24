@switch($exception->getStatusCode())
    @case(404)
        @include('errors.404')
    @break

    @case(500)
        @include('errors.500')
    @break

    @default
        @include('errors.fallback')
@endswitch
