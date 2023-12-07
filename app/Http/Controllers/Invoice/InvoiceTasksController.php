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
                ->with(['tasks' => function ($query) {
                    $query->whereNotNull('completed_at')
                        ->whereNull('invoice_id')
                        ->where('billable', true)
                        ->with(['labels:id,name,color'])
                        ->withSum('timeLogs AS total_minutes', 'minutes');
                }])
                ->get(),
        ]);
    }
}
