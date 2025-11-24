<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Razorpay
{
    private $keyId, $keySecret, $webhookSecret;

    public function __construct($keyId, $keySecret, $webhookSecret)
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->webhookSecret = $webhookSecret;
    }

    /*
{
    "id": "inv_Krl0S82ZgTbn6C",
    "entity": "invoice",
    "receipt": null,
    "invoice_number": null,
    "customer_id": "cust_Krl25WegYKNBZT",
    "customer_details": {
        "id": "cust_Krl25WegYKNBZT",
        "name": null,
        "email": "walid.a.alhomsi@gmail.com",
        "contact": "+919101697880",
        "gstin": null,
        "billing_address": null,
        "shipping_address": null,
        "customer_name": null,
        "customer_email": "walid.a.alhomsi@gmail.com",
        "customer_contact": "+919101697880"
    },
    "order_id": "order_Krl0S9TkMowJnK",
    "subscription_id": "sub_Krl0RiUPPlCAlR",
    "line_items": [
        {
            "id": "li_Krl0S8UHE5X0d3",
            "item_id": null,
            "ref_id": null,
            "ref_type": null,
            "name": "PRO",
            "description": "PRO Plan, 17 Dynamic QR Codes., 17000 Scans, Single user",
            "amount": 4800,
            "unit_amount": 4800,
            "gross_amount": 4800,
            "tax_amount": 0,
            "taxable_amount": 4800,
            "net_amount": 4800,
            "currency": "INR",
            "type": "plan",
            "tax_inclusive": false,
            "hsn_code": null,
            "sac_code": null,
            "tax_rate": null,
            "unit": null,
            "quantity": 1,
            "taxes": []
        }
    ],
    "payment_id": "pay_Krl25OtGU1y89t",
    "status": "paid",
    "expire_by": null,
    "issued_at": 1671013698,
    "paid_at": 1671013799,
    "cancelled_at": null,
    "expired_at": null,
    "sms_status": null,
    "email_status": null,
    "date": 1671013698,
    "terms": null,
    "partial_payment": false,
    "gross_amount": 4800,
    "tax_amount": 0,
    "taxable_amount": 4800,
    "amount": 4800,
    "amount_paid": 4800,
    "amount_due": 0,
    "currency": "INR",
    "currency_symbol": "₹",
    "description": null,
    "notes": [],
    "comment": null,
    "short_url": "https://rzp.io/i/cnJXEZmOl",
    "view_less": true,
    "billing_start": 1671013791,
    "billing_end": 1702492200,
    "type": "invoice",
    "group_taxes_discounts": false,
    "created_at": 1671013698,
    "idempotency_key": null
}
    */
    public function getInvoice($invoice_id)
    {
        return $this->request()->get('invoices/' . $invoice_id)->json();
    }

    /*
{
    "id": "sub_Krl0RiUPPlCAlR",
    "entity": "subscription",
    "plan_id": "plan_Krl0BsvZPK8oX4",
    "customer_id": "cust_Krl25WegYKNBZT",
    "status": "completed",
    "current_start": 1671013791,
    "current_end": 1702492200,
    "ended_at": 1671013791,
    "quantity": 1,
    "notes": {
        "subscription_id": "177"
    },
    "charge_at": null,
    "start_at": 1671013791,
    "end_at": 1671013791,
    "auth_attempts": 0,
    "total_count": 1,
    "paid_count": 1,
    "customer_notify": true,
    "created_at": 1671013697,
    "expire_by": null,
    "short_url": "https://rzp.io/i/k1DWPfki65",
    "has_scheduled_changes": false,
    "change_scheduled_at": null,
    "source": "api",
    "payment_method": "card",
    "offer_id": null,
    "remaining_count": 0
}
    */
    public function getSubscription($subscription_id)
    {
        return $this->request()->get('subscriptions/' . $subscription_id)->json();
    }

    /*
{
    "id": "pay_Krl25OtGU1y89t",
    "entity": "payment",
    "amount": 4800,
    "currency": "INR",
    "status": "captured",
    "order_id": "order_Krl0S9TkMowJnK",
    "invoice_id": "inv_Krl0S82ZgTbn6C",
    "international": false,
    "method": "card",
    "amount_refunded": 0,
    "refund_status": null,
    "captured": true,
    "description": null,
    "card_id": "card_Krl25ZiOEaZnP4",
    "card": {
        "id": "card_Krl25ZiOEaZnP4",
        "entity": "card",
        "name": "",
        "last4": "5449",
        "network": "MasterCard",
        "type": "credit",
        "issuer": "UTIB",
        "international": false,
        "emi": false,
        "sub_type": "consumer",
        "token_iin": null
    },
    "bank": null,
    "wallet": null,
    "vpa": null,
    "email": "walid.a.alhomsi@gmail.com",
    "contact": "+919101697880",
    "customer_id": "cust_Krl25WegYKNBZT",
    "token_id": "token_Krl25mvEjQjDLk",
    "notes": [],
    "fee": 140,
    "tax": 0,
    "error_code": null,
    "error_description": null,
    "error_source": null,
    "error_step": null,
    "error_reason": null,
    "acquirer_data": {
        "auth_code": "995283"
    },
    "created_at": 1671013791
}
    */
    public function getPayment($payment_id)
    {
        return $this->request()->get('payments/' . $payment_id)->json();
    }

    /*
{
    "id": "order_Krkwewo5bRhOz5",
    "entity": "order",
    "amount": 4800,
    "amount_paid": 4800,
    "amount_due": 0,
    "currency": "INR",
    "receipt": "subscription_176",
    "offer_id": null,
    "status": "paid",
    "attempts": 1,
    "notes": [],
    "created_at": 1671013482
}
    */
    public function getOrder($order_id)
    {
        return $this->request()->get('orders/' . $order_id)->json();
    }

    /*
{
    "entity": "collection",
    "count": 8,
    "items": [
        {
            "id": "plan_KrGpIaOGhtKtRg",
            "entity": "plan",
            "interval": 1,
            "period": "weekly",
            "item": {
                "id": "item_KrGpIa3aTb2HJS",
                "active": true,
                "name": "Test plan - Weekly",
                "description": "Description for the test plan - Weekly",
                "amount": 69900,
                "unit_amount": 69900,
                "currency": "INR",
                "type": "plan",
                "unit": null,
                "tax_inclusive": false,
                "hsn_code": null,
                "sac_code": null,
                "tax_rate": null,
                "tax_id": null,
                "tax_group_id": null,
                "created_at": 1670907416,
                "updated_at": 1670907416
            },
            "notes": [],
            "created_at": 1670907416
        },
        ... 7 more items
    ]
}
*/
    public function listPlans()
    {
        return $this->request()->get('plans')->json();
    }

    /**
     * Example response
     */
    /*
{
    "id": "plan_KrGpIaOGhtKtRg",
    "entity": "plan",
    "interval": 1,
    "period": "weekly",
    "item": {
        "id": "item_KrGpIa3aTb2HJS",
        "active": true,
        "name": "Test plan - Weekly",
        "description": "Description for the test plan - Weekly",
        "amount": 69900,
        "unit_amount": 69900,
        "currency": "INR",
        "type": "plan",
        "unit": null,
        "tax_inclusive": false,
        "hsn_code": null,
        "sac_code": null,
        "tax_rate": null,
        "tax_id": null,
        "tax_group_id": null,
        "created_at": 1670907416,
        "updated_at": 1670907416
    },
    "notes": [],
    "created_at": 1670907416
}
*/
    public function createPlan($period, $interval, $item_name, $item_amount, $item_currency, $item_description)
    {
        return $this->request()->post('plans', [
            'period' => $period,
            'interval' => $interval,
            'item' => [
                'name' => $item_name,
                'amount' => $item_amount,
                'description' => $item_description,
                'currency' => $item_currency
            ]
        ])->json();
    }

    /*
{
    "id": "sub_KrI0XSp0xJjerG",
    "entity": "subscription",
    "plan_id": "plan_KpMniX56fquHYe",
    "status": "created",
    "current_start": null,
    "current_end": null,
    "ended_at": null,
    "quantity": 1,
    "notes": {
        "subscription_id": "1235"
    },
    "charge_at": 1735689600,
    "start_at": 1735689600,
    "end_at": 1738693800,
    "auth_attempts": 0,
    "total_count": 6,
    "paid_count": 0,
    "customer_notify": true,
    "created_at": 1670911576,
    "expire_by": 1893456000,
    "short_url": "https://rzp.io/i/Qq7aFVnmc",
    "has_scheduled_changes": false,
    "change_scheduled_at": null,
    "source": "api",
    "remaining_count": 6
}
*/
    public function createSubscription($plan_id, $total_count, $notes = [])
    {
        return $this->request()->post('subscriptions', [
            'plan_id' => $plan_id,
            'total_count' => $total_count,
            'notes' => $notes
        ])->json();
    }

    /*
{
    "accept_partial": true,
    "amount": 1000,
    "amount_paid": 0,
    "callback_method": "get",
    "callback_url": "https://example-callback-url.com/",
    "cancelled_at": 0,
    "created_at": 1670914513,
    "currency": "INR",
    "customer": {
        "email": "gaurav.kumar@example.com",
        "name": "Gaurav Kumar"
    },
    "description": "Payment for policy no #23456",
    "expire_by": 1686639313,
    "expired_at": 0,
    "first_min_partial_amount": 100,
    "id": "plink_KrIqFJ1uqlWJ0Z",
    "notes": {
        "policy_name": "Jeevan Bima"
    },
    "notify": {
        "email": true,
        "sms": true,
        "whatsapp": false
    },
    "payments": null,
    "reference_id": "TSsd1989",
    "reminder_enable": true,
    "reminders": [],
    "short_url": "https://rzp.io/i/juAAHGu",
    "status": "created",
    "updated_at": 1670914513,
    "upi_link": false,
    "user_id": ""
}
    */
    public function createPaymentLink(
        $amount,
        $currency,
        $reference_id,
        $description,
        $customer_name,
        $customer_email,
        $callback_url,
        $notes = []
    ) {
        return $this->request()->post('payment_links', [
            'amount' => $amount,
            'currency' => $currency,
            'reference_id' => $reference_id,
            'notes' => $notes,
            'description' => $description,
            'customer' => [
                'name' => $customer_name,
                'email' => $customer_email,
            ],
            'notify' => [
                'sms' => true,
                'email' => true,
            ],
            'callback_url' => $callback_url,
            'options' => [
                'checkout' => [
                    'methods' => [
                        'upi' => true,
                        'card' => true,
                        'netbanking' => true,
                    ]
                ]
            ]
        ])->json();
    }

    private function request()
    {
        return Http::asJson()
            ->withBasicAuth($this->keyId, $this->keySecret)
            ->baseUrl('https://api.razorpay.com/v1/');
    }
}
