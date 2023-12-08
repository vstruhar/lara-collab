<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\OwnerCompany;
use App\Models\Task;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice as InvoiceGenerator;

class InvoiceService
{
    public static function generate(Invoice $invoice): void
    {
        $ownerCompany = OwnerCompany::first();
        $clientCompany = $invoice->clientCompany;

        $invoice->load([
            'tasks' => fn ($query) => $query->withSum('timeLogs AS total_minutes', 'minutes'),
        ]);

        $owner = new Party([
            'name' => $ownerCompany->name,
            'phone' => $ownerCompany->phone,
            'custom_fields' => [
                'business id' => $ownerCompany->business_id,
            ],
        ]);

        $client = new Party([
            'name' => $clientCompany->name,
            'address' => $clientCompany->address,
            'custom_fields' => [],
        ]);

        $items = $invoice->tasks->map(function (Task $task) use ($invoice) {
            return InvoiceItem::make($task->name)
                ->pricePerUnit($invoice->hourly_rate / 100)
                ->quantity(round($task->total_minutes / 60, 2))
                ->units('hours');
        });

        $filename = today()->year.'/'.$invoice->number.' - '.trim($client->name);

        InvoiceGenerator::make('invoice')
            ->sequence($invoice->number)
            ->serialNumberFormat('{SEQUENCE}')
            ->seller($owner)
            ->buyer($client)
            ->date(now()->subWeeks(2))
            ->dateFormat('F j, Y')
            ->payUntilDays(14)
            ->currencySymbol($clientCompany->currency->symbol)
            ->currencyCode($clientCompany->currency->code)
            ->currencyDecimals($clientCompany->currency->decimals)
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->filename($filename)
            ->addItems($items)
            ->notes($invoice->note ?? '')
            // ->logo(public_path('vendor/invoices/sample-logo.png'))
            ->save('invoices');

        $invoice->update(['filename' => $filename.'.pdf']);
    }
}
