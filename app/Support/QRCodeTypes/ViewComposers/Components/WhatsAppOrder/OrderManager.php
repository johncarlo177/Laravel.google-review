<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\WhatsAppOrder;

use App\Models\QRCode;
use App\Models\QRCodeWebPageDesign;
use App\Support\System\MemoryCache;
use App\Support\System\Traits\WriteLogs;

class OrderManager
{
    use WriteLogs;

    protected $slug = null;

    protected $destination = null;

    public static function withSlug($slug)
    {
        $instance = new static;

        $instance->slug = $slug;

        return $instance;
    }

    public function withDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return QRCode
     */
    protected function getQRCode()
    {
        return MemoryCache::remember(__METHOD__, function () {
            return QRCode::bySlug($this->slug);
        });
    }

    protected function getDesign(): ?QRCodeWebPageDesign
    {
        return MemoryCache::remember(__METHOD__, function () {
            return $this->getQRCode()?->getWebPageDesign();
        });
    }

    protected function getCurrentOrderNumber()
    {
        $value = @$this->getDesign()?->design['whatsapp_order_number'];

        $value = intval($value);

        return max($value, 1);
    }

    protected function setCurrentOrderNumber($value)
    {
        $design = $this->getDesign();

        $this->logDebug('QRCode id (%s)', $this->getQRCode()?->id);
        $this->logDebug(
            'Design id (%s)',
            $this->getQRCode()?->getWebPageDesign()?->id
        );

        $this->logDebug('Slug (%s)', $this->slug);
        $this->logDebug('Setting new order number value (%s)', $value);

        $design->design = array_merge(
            $design->design,
            [
                'whatsapp_order_number' => $value
            ]
        );

        $this->logDebug('New design data %s', $design->design);

        $design->save();
    }

    protected function increaseOrderNumber()
    {
        $value = $this->getCurrentOrderNumber();

        $this->setCurrentOrderNumber($value + 1);
    }

    public function place()
    {
        $this->increaseOrderNumber();

        return redirect()->to($this->destination);
    }
}
