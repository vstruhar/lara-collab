<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\CreateInvoice;
use App\Actions\Invoice\UpdateInvoice;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\ClientCompany;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Invoices/Index', [
            'items' => InvoiceResource::collection(
                Invoice::searchByQueryString()
                    ->sortByQueryString()
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->with(['clientCompany.currency', 'createdByUser'])
                    ->paginate(12)
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoices/Create', [
            'nextNumber' => Invoice::getNextNumber(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_company_id', 'rate']),
            'clientCompanies' => ClientCompany::has('projects')
                ->with(['currency'])
                ->orderBy('name')
                ->get(['id', 'name', 'currency_id']),
            'dropdowns' => [
                'clientCompanies' => ClientCompany::dropdownValues(['hasProjects']),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = (new CreateInvoice)->create($request->validated());

        InvoiceService::generate($invoice);

        return redirect()->route('invoices.index')->success('Invoice created', 'A new invoice was successfully created.');
    }

    public function edit(Invoice $invoice)
    {
        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice->load('tasks:id,invoice_id'),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_company_id', 'rate']),
            'selectedProjects' => DB::table('tasks')
                ->where('invoice_id', $invoice->id)
                ->groupBy('project_id')
                ->get(['project_id'])
                ->pluck('project_id'),
            'clientCompanies' => ClientCompany::has('projects')
                ->with(['currency'])
                ->orderBy('name')
                ->get(['id', 'name', 'currency_id']),
            'dropdowns' => [
                'clientCompanies' => ClientCompany::dropdownValues(['hasProjects']),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    public function update(Invoice $invoice, UpdateInvoiceRequest $request)
    {
        $invoice = (new UpdateInvoice)->update($invoice, $request->validated());

        InvoiceService::generate($invoice);

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

    public function setStatus(Request $request, Invoice $invoice)
    {
        $this->authorize('changeStatus', $invoice);

        $request->validate(['status' => ['required', 'in:new,sent,paid']]);

        $invoice->update(['status' => $request->status]);

        return redirect()->back();
    }

    public function download(Invoice $invoice): StreamedResponse
    {
        $this->authorize('download', $invoice);

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/pdf',
        ];

        return Storage::drive('invoices')->download($invoice->filename, null, $headers);
    }

    public function pdf(Invoice $invoice): BinaryFileResponse
    {
        $this->authorize('print', $invoice);

        $filepath = Storage::drive('invoices')->path($invoice->filename);

        if (! File::exists($filepath)) {
            abort(404);
        }

        return response()->file($filepath, ['Cache-Control' => 'no-cache, must-revalidate']);
    }
}
