<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        {{ t('Dynamic Domain Name') }}
    </title>
</head>

<body>
    {!! ContentManager::customCode('Custom Domain Page: Before Content') !!}

    <div class="custom-domain-message">
        {{ t('This domain name is used to serve dynamic QR codes.') }}
    </div>
</body>

</html>
