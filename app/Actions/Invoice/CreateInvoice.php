<?php

namespace App\Actions\Invoice;

use App\Enums\Invoice as InvoiceEnum;
use App\Models\ClientCompany;
use App\Models\Invoice;
use App\Models\OwnerCompany;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Support\Facades\DB;

class CreateInvoice
{
    public function create(array $data): Invoice
    {
        $ownerCompany = OwnerCompany::first();
        $clientCompany = ClientCompany::find($data['client_company_id']);

        $amount = ($data['type'] === InvoiceEnum::TYPE_FIXED_AMOUNT->value)
            ? $data['fixed_amount']
            : $this->getAmount($data['hourly_rate'], $data['tasks']);

        return DB::transaction(function () use ($clientCompany, $data, $amount, $ownerCompany) {
            $invoice = $clientCompany
                ->invoices()
                ->create([
                    'created_by_user_id' => auth()->id(),
                    'status' => InvoiceEnum::STATUS_NEW->value,
                    'number' => $data['number'],
                    'type' => $data['type'],
                    'amount' => $amount,
                    'amount_with_tax' => $amount * (1 + ($ownerCompany->tax / 10000)),
                    'due_date' => today()->addWeeks(2),
                    'note' => $data['note'],
                    'hourly_rate' => $data['hourly_rate'],
                ]);

            Task::whereIn('id', $data['tasks'])
                ->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    protected function getAmount(int $rate, array $tasks): int
    {
        $minutesSum = (int) TimeLog::whereIn('task_id', $tasks)->sum('minutes');
        $hoursSum = round($minutesSum / 60, 2);

        return ($hoursSum * ($rate / 100)) * 100;
    }
}
