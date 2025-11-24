<?php

namespace App\Models;

use App\Support\Invoicing\TaxManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property string description
 * @property int invoice_id
 * @property float unit_price
 * @property int quantity
 * @property float total
 * @property string related_model
 * @property int related_model_id
 * @property double subtotal
 * @property double tax_rate
 * @property double tax_amount
 * @property Invoice invoice
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function (InvoiceItem $item) {
            $item->calculateTotal();
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    protected function calculateTotal()
    {
        $this->total  = bcmul($this->quantity, $this->unit_price);

        $this->tax_rate = $this->tax()->getRate();

        $this->tax_amount = $this->tax()->calculateTax($this->total);

        $this->subtotal = $this->tax()->calculateNetAmount($this->total);
    }

    protected function tax()
    {
        return new TaxManager;
    }
}
