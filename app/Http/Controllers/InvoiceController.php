<?php

namespace App\Http\Controllers;

use App\Http\Responses\InvoiceResponse;
use App\Models\Invoice;
use App\Repositories\CurrencyManager;
use App\Support\System\Traits\WriteLogs;

class InvoiceController extends Controller
{
    use WriteLogs;

    public function index()
    {
        $invoices = Invoice::where('user_id', $this->optionalUser()->id)->get();

        return InvoiceResponse::list($invoices);
    }

    public function viewInvoice($uuid)
    {
        /**
         * @var Invoice
         */
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        return view('blue.pages.invoice', [
            'invoice' => $invoice,
            'business_information' => [
                config('invoice.business_name'),
                config('invoice.business_email'),
                config('invoice.business_phone'),
                config('invoice.tax_id'),
                config('invoice.business_address'),
            ],
            'invoice_message' => config('invoice.invoice_message'),
            'billing_details' => collect(
                $invoice->billing_details?->fields
            )->map(function ($field) {
                return $field['value'];
            }),
            'user' => $invoice->user,
            'currency' => (new CurrencyManager)->enabledCurrency()->currency_code
        ]);
    }
}
