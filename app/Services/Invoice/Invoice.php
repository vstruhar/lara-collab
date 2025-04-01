<?php

namespace App\Services\Invoice;

use App\Enums\Invoice as InvoiceEnum;
use LaravelDaily\Invoices\Invoice as InvoiceBase;

class Invoice extends InvoiceBase
{
    protected string $type;

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isFixedAmount(): bool
    {
        return $this->type === InvoiceEnum::TYPE_FIXED_AMOUNT->value;
    }
}
