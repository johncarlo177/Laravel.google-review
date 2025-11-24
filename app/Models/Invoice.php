<?php

namespace App\Models;

use App\Support\Invoicing\TaxManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property int id
 * @property string uuid
 * @property string status
 * @property int user_id
 * @property double total
 * @property double subtotal
 * @property double tax_rate
 * @property double tax_amount
 * @property int billing_details_response_id
 * @property Collection<InvoiceItem> items
 * @property CustomFormResponse billing_details
 * @property User user
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Invoice extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    protected static function booted()
    {
        static::creating(function (Invoice $invoice) {
            $invoice->uuid = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addItem(InvoiceItem $item)
    {
        if (!$this->id) {
            $this->save();
        }

        $item->invoice_id = $this->id;

        $item->save();

        $this->calculateTotal();

        return $this;
    }

    public function removeItem(InvoiceItem $item)
    {
        $item->delete();

        $this->calculateTotal();

        return $this;
    }

    protected function sum($field)
    {
        return $this->items->reduce(
            function ($result, InvoiceItem $item) use ($field) {
                return bcadd($item->{$field}, $result);
            },
            0
        );
    }

    public function calculateTotal()
    {
        $this->load('items');

        $this->tax_rate = $this->tax()->getRate();

        $this->tax_amount = $this->sum('tax_amount');

        $this->subtotal = $this->sum('subtotal');

        $this->total = $this->sum('total');

        $this->save();

        return $this;
    }

    protected function tax()
    {
        return new TaxManager;
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function delete()
    {
        $this->load('items');

        $this->items->each(function (InvoiceItem $item) {
            $item->delete();
        });

        parent::delete();
    }

    public function billing_details()
    {
        return $this->belongsTo(CustomFormResponse::class, 'billing_details_response_id');
    }
}
