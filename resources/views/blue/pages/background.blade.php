<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    @include('blue.partials.head.entrypoint')

</head>

<body>
    <div class="background" style="height: 800px; position: relateive;">
        <gradient-bubbles-renderer style="width: 100%; height: 80%;"></gradient-bubbles-renderer>

    </div>
    <script src="{{ url('/assets/lib/gl-matrix.min.js') }}"></script>
</body>

</html>
