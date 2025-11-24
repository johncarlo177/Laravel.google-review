<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print Sheet</title>
    <style>

        @page {
            size: {{ $composer->getPageWidth() }}cm {{ $composer->getPageHeight() }}cm; /* or size: 21cm 29.7cm; */
            margin: 0.5cm;
        }

        @media print {
            .instructions {
                display: none;
            }

            .qrcodes:not(:last-child) {
                page-break-after: always;   /* old */
                break-after: page;     
            }

            .qrcodes:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .qrcodes {
            width: {{ $composer->getPageWidth() }}cm;
            display: flex;
            flex-wrap: wrap;
            gap: {{ $composer->getGridGap() }}cm;
        }

        .qrcode {
            overflow: hidden;
            padding: {{ $composer->getCutlineMargin() }}mm;
            background-color: {{ $composer->getQRCodeBackgroundColor() }};
            width: {{ $composer->getQRCodeWidth() }}cm;
            height: {{ $composer->getQRCodeHeight() }}cm;
        }

        svg {
            max-width: 100%;
            display: block;
            height: auto;
            max-height: 100%;
            border: 0.125pt solid #FF00FF;
            border-radius: 1mm;
        }

        .instructions {
            padding: 1rem;
            text-align: center;
            background-color: #ffeaea;
            border: 2px solid red;
            border-radius: 5px;
            margin: 2rem;
            font-family: sans-serif;
            line-height: 1.6;
        }

        .instructions button {
            background-color: #3f51b5;
            color: white;
            outline: 0;
            border: 2px solid transparent;
            font-size: 1rem;
            padding: 0.5rem 1.3rem;
            border-radius: 0.3rem;
            cursor: pointer;
        }

        .instructions button:hover {
            background-color:#434d83;
            border: 2px solid black;
        }
        
        .sheet-signature {
            margin-inline-start: 2cm;
            margin-bottom: .5cm;
            align-self: flex-end;
            display: flex;
            gap: 1rem;
            align-items: center
        }

        .sheet-signature img {
            width: 1cm;
            height: 1cm;
            object-fit: contain;
        }

        
    </style>
</head>
<body>
    <div class=instructions>
        <p>Click on the print button and save the file as PDF.</p>
        <p>After that you will have to go back to the bulk operations screen and click on 3 dots ... and then <strong>Inject CutContour</strong> button </p>
        <button onclick="window.print()">Print</button>
    </div>

    @foreach ($composer->getPageRange() as $page)
        @if ($composer->getPageQRCodes($page)->isNotEmpty())
            <div class=qrcodes>
                @foreach ($composer->getPageQRCodes($page) as $qrcode)
                    <div class="qrcode">
                        {!! $composer->createSvgScope($qrcode) !!}
                    </div>
                @endforeach

                <div class="sheet-signature">
                    <div class=text>
                        {{ $composer->getSignatureText() }} {{ $page + 1}}
                    </div>

                    @if ($composer->getSignatureImage())
                        <img src="{{ $composer->getSignatureImage() }}" />
                    @endif
                </div>
            </div>
        @endif
    @endforeach
</body>
</html>