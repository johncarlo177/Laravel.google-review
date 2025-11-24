<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    @include('blue.partials.head.entrypoint')
</head>

<body class=invoice>


    <div class=container>
        <div class=header>
            @include('blue.components.logo', ['inverse' => true])

            <div class=business-details>
                @foreach ($business_information as $line)
                    @if (!empty(trim($line)))
                        <div class=line>
                            {!! nl2br($line) !!}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class=second-row>
            <div class=user-details>
                <div class="line">{{ $user->name }}</div>
                <div class="line">{{ $user->email }}</div>
            </div>

            <div class=billing-details>
                @foreach ($billing_details as $line)
                    @if (!empty(trim($line)))
                        <div class=line>
                            {{ nl2br($line) }}
                        </div>
                    @endif
                @endforeach


            </div>
        </div>

        <div class=invoice-number>
            {{ t('Invoice No.') }}

            #{{ $invoice->id }}
        </div>

        <div class=invoice-date>
            {{ t('Invoice Date') }}
            <strong>
                {{ $invoice->created_at->format('Y-m-d') }}
            </strong>
        </div>

        <table class="items-table">
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>
                            <div class=item-name>
                                {{ $item->name }}
                            </div>

                            <div class=item-description>
                                {{ $item->description }}
                            </div>
                        </td>

                        <td>
                            {{ $currency }}
                        </td>

                        <td>
                            {{ $item->subtotal }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td>
                        {{ t('Subtotal') }}
                    </td>

                    <td>
                        {{ $currency }}
                    </td>

                    <td>
                        {{ $invoice->subtotal }}
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ t('Tax') }} ({{ $invoice->tax_rate ?? '0' }}%)
                    </td>

                    <td>
                        {{ $currency }}
                    </td>

                    <td>
                        {{ $invoice->tax_amount }}
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ t('Total') }}
                    </td>

                    <td>
                        {{ $currency }}
                    </td>

                    <td>
                        {{ $invoice->total }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class=invoice-message>
            {!! nl2br($invoice_message) !!}
        </div>

    </div>

</body>

</html>
