<?php

namespace App\Actions\Invoice;

use App\Enums\Invoice as InvoiceEnum;
use App\Enums\PricingType;
use App\Models\Invoice;
use App\Models\OwnerCompany;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Support\Facades\DB;

class UpdateInvoice
{
    public function update(Invoice $invoice, array $data): Invoice
    {
        $ownerCompany = OwnerCompany::first();

        $amount = ($data['type'] === InvoiceEnum::TYPE_FIXED_AMOUNT->value)
            ? $data['fixed_amount']
            : $this->getAmount($data['hourly_rate'], $data['tasks']);

        return DB::transaction(function () use ($invoice, $data, $amount, $ownerCompany) {
            $invoice->update([
                'number' => $data['number'],
                'type' => $data['type'],
                'amount' => $amount,
                'amount_with_tax' => $amount * (1 + ($ownerCompany->tax / 10000)),
                'billing_date' => today()->addWeeks(2),
                'note' => $data['note'],
                'hourly_rate' => $data['hourly_rate'],
            ]);

            Task::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
            Task::whereIn('id', $data['tasks'])->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    protected function getAmount(int $rate, array $tasks): int
    {
        // hourly
        $minutesSum = (int) TimeLog::whereIn('task_id', $tasks)
            ->whereHas('task', function ($query) {
                $query->where('pricing_type', PricingType::HOURLY->value);
            })
            ->sum('minutes');

        $hoursSum = round($minutesSum / 60, 2);

        // fixed
        $fixedTasksSum = Task::whereIn('id', $tasks)
            ->where('pricing_type', PricingType::FIXED->value)
            ->sum('fixed_price');

        return ($hoursSum * ($rate / 100)) * 100 + $fixedTasksSum;
    }
}
