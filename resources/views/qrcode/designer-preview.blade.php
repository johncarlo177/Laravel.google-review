@php
if (!isset($_GET['src'])) {
abort(422, 'Invalid request');
}
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>
        QR Code Preview
    </title>

    <style>
        :root {
            --primary-0: black;

            --primary-1: #eee;

            --primary-2: #888;

            --toolbar-bg: var(--primary-1);

            --button-color: white;

            --button-outline-focus: #9f9f9f;
        }

        html {
            font-family: sans-serif;
        }

        body {
            margin: 0;

        }

        * {
            box-sizing: border-box;
        }

        .img {
            display: block;
            width: 100%;
            max-width: 45rem;
            margin: auto;
            display: flex;
            position: relative;
            padding: 1rem;
        }

        .img>* {
            flex: 1;
        }

        .img svg {
            width: 100%;
            height: 100%;
        }

        .loader-container {
            display: flex;
            justify-content: center;
            margin-top: 30vh;
        }
    </style>

    <style>
        .lds-roller {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-roller div {
            animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            transform-origin: 40px 40px;
        }

        .lds-roller div:after {
            content: " ";
            display: block;
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: black;
            margin: -4px 0 0 -4px;
        }

        .lds-roller div:nth-child(1) {
            animation-delay: -0.036s;
        }

        .lds-roller div:nth-child(1):after {
            top: 63px;
            left: 63px;
        }

        .lds-roller div:nth-child(2) {
            animation-delay: -0.072s;
        }

        .lds-roller div:nth-child(2):after {
            top: 68px;
            left: 56px;
        }

        .lds-roller div:nth-child(3) {
            animation-delay: -0.108s;
        }

        .lds-roller div:nth-child(3):after {
            top: 71px;
            left: 48px;
        }

        .lds-roller div:nth-child(4) {
            animation-delay: -0.144s;
        }

        .lds-roller div:nth-child(4):after {
            top: 72px;
            left: 40px;
        }

        .lds-roller div:nth-child(5) {
            animation-delay: -0.18s;
        }

        .lds-roller div:nth-child(5):after {
            top: 71px;
            left: 32px;
        }

        .lds-roller div:nth-child(6) {
            animation-delay: -0.216s;
        }

        .lds-roller div:nth-child(6):after {
            top: 68px;
            left: 24px;
        }

        .lds-roller div:nth-child(7) {
            animation-delay: -0.252s;
        }

        .lds-roller div:nth-child(7):after {
            top: 63px;
            left: 17px;
        }

        .lds-roller div:nth-child(8) {
            animation-delay: -0.288s;
        }

        .lds-roller div:nth-child(8):after {
            top: 56px;
            left: 12px;
        }

        @keyframes lds-roller {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            background-color: #eee;
            align-items: center;
        }

        .close {
            padding: 0.8rem 1rem;

            font-weight: bold;
            background-color: var(--primary-0);
            color: var(--button-color);
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-appearance: none;
            border: 0;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.8rem;
            letter-spacing: 1px;
            min-width: 110px;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
            /** prevent zoom on multiple tap */
            touch-action: manipulation;
            border-radius: 0.5rem;
            outline: 0;
            position: relative;

        }

        .close:focus {
            color: var(--button-color);
            outline: 0.1rem solid var(--button-outline-focus);
        }

        .close:hover {
            background-color: var(--primary-2);
        }

        .close:hover:focus {
            outline-color: var(--primary-0);
        }

        .close:active {
            color: var(--button-color);
        }

        h1 {
            color: var(--primary-2);
            margin: 0;
            font-size: 1.2rem;
            user-select: none;
            -webkit-user-select: none;
        }

        .app-name {
            display: none;
        }

        @media (min-width: 900px) {
            .app-name {
                display: inline;
            }
        }
    </style>
    <script>
        (function () {
            function getHeaders() {
                const headers = {}

                if (localStorage['auth:token']) {
                    headers.Authorization = 'Bearer '+ localStorage['auth:token']
                }

                return headers;
            }

            async function loadImage() {
                const img = document.querySelector('.img')

                const response = await fetch(img.getAttribute('src'), {
                    headers: getHeaders(),
                    method: 'GET'
                })

                const data = await response.json()

                const content = data.content

                img.innerHTML = atob(content)
            }

            
            document.addEventListener('DOMContentLoaded', function() {
                loadImage();

                document.querySelector('button.close').addEventListener('click', () => window.close());
            })
        })()
        
    </script>
</head>

<body>
    <div class="toolbar">
        <h1>
            <span class="app-name"> {{ json_decode(config('app.name')) ?: config('app.name') }} - </span>
            {{ t('QR Code Preview') }}
        </h1>
        <button class="close">
            {{ t('Close') }}
        </button>
    </div>
    <div class="img" src="{{ $_GET['src'] }}">
        <div class="loader-container">
            <div class="lds-roller">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
</body>

</html>