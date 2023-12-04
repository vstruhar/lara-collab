<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\ClientCompany;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(): Response
    {
        return Inertia::render('Invoices/Index', [
            'items' => InvoiceResource::collection(
                Invoice::with(['clientCompany', 'createdByUser'])
                    ->paginate(12)
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoices/Create', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_company_id']),
            'clientCompanies' => ClientCompany::has('projects')
                ->with(['currency'])
                ->orderBy('name')
                ->get(['id', 'name', 'rate', 'currency_id']),
            'dropdowns' => [
                'clientCompanies' => ClientCompany::dropdownValues(['hasProjects']),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        // (new CreateInvoice)->create($request->validated());

        return redirect()->route('invoices.index')->success('Invoice created', 'A new company was successfully created.');
    }

    public function edit(Invoice $invoice)
    {
        return Inertia::render('Invoice/Edit', [
            'item' => new InvoiceResource($invoice),
            'dropdowns' => [],
        ]);
    }

    public function update(Invoice $invoice, UpdateInvoiceRequest $request)
    {
        // (new UpdateInvoice)->update($invoice, $request->validated());

        return redirect()->route('invoices.index')->success('Invoice updated', 'The invoice was successfully updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->archive();

        return redirect()->back()->success('Invoice archived', 'The invoice was successfully archived.');
    }

    public function restore(int $invoiceId)
    {
        $invoice = Invoice::withArchived()->findOrFail($invoiceId);

        $this->authorize('restore', $invoice);

        $invoice->unArchive();

        return redirect()->back()->success('Invoice restored', 'The restoring of the invoice was completed successfully.');
    }
}
