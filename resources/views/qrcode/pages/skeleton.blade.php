@php
    $locale = isset($locale) ? $locale : 'en';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    @include('blue.partials.head.entrypoint')
</head>

<body>
    @section('body')
    @show
</body>

</html>
