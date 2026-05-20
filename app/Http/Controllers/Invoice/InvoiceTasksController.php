<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceTasksController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectIds = $request->get('projectIds', []);

        $projects = Project::whereIn('id', $projectIds)->get();

        foreach ($projects as $project) {
            $this->authorize('view', $project);
        }

        return response()->json([
            'projectTasks' => $projects->load(['tasks' => function ($query) use ($request) {
                $query->whereNotNull('completed_at')
                    ->where('billable', true)
                    ->where(function ($query) use ($request) {
                        $query->whereNull('invoice_id')
                            ->when($request->invoiceId, fn ($query) => $query->orWhere('invoice_id', $request->invoiceId));
                    })
                    ->with(['labels:id,name,color'])
                    ->withSum('timeLogs AS total_minutes', 'minutes');
            }]),
        ]);
    }
}
