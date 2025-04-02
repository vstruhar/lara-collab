<?php

namespace App\Services\Invoice;

use App\Enums\PricingType;
use LaravelDaily\Invoices\Classes\InvoiceItem as InvoiceItemBase;

class InvoiceItem extends InvoiceItemBase
{
    protected string $invoiceType;

    protected string $pricingType;

    public function __construct()
    {
        parent::__construct();
    }

    public static function make($title)
    {
        return (new self)->title($title);
    }

    public function pricingType(string $pricingType)
    {
        $this->pricingType = $pricingType;

        return $this;
    }

    public function invoiceType(string $invoiceType)
    {
        $this->invoiceType = $invoiceType;

        return $this;
    }

    public function isFixedPrice(): bool
    {
        return $this->pricingType === PricingType::FIXED->value;
    }

    public function isHourly(): bool
    {
        return $this->pricingType === PricingType::HOURLY->value;
    }
}
