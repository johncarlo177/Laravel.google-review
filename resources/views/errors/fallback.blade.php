<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $exception->getStatusCode() }} Error</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 16px;
            user-select: none;
            -webkit-user-select: none;
            pointer-events: none;
        }

        body {
            background-color: whitesmoke;
            padding: 0;
            margin: 0;

            overflow: hidden;
        }

        * {
            box-sizing: border-box;
        }

        h1 {
            margin: 0;
            margin-bottom: 1rem;
        }

        .container {
            height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-message {
            font-size: 2rem;
            background-color: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.1);
            color: #898989;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-message">
            @if ($exception->getStatusCode() == 404)
                {{ t('The requested page could not be found.') }}
            @else
                {{ $exception->getMessage() }}
            @endif
        </div>
    </div>
</body>

</html>
