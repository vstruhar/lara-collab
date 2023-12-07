<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InvoiceTasksController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'projectTasks' => Project::whereIn('id', $request->get('projectIds', []))
                ->with(['tasks' => function ($query) use ($request) {
                    $query->whereNotNull('completed_at')
                        ->where('billable', true)
                        ->where(function ($query) use ($request) {
                            $query->whereNull('invoice_id')
                                ->when($request->invoiceId, fn ($query) => $query->orWhere('invoice_id', $request->invoiceId));
                        })
                        ->with(['labels:id,name,color'])
                        ->withSum('timeLogs AS total_minutes', 'minutes');
                }])
                ->get(),
        ]);
    }
}
