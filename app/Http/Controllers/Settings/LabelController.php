<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Label\UpdateLabelRequest;
use App\Http\Resources\Label\LabelResource;
use App\Models\Label;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LabelController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Label::class, 'label');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Labels/Index', [
            'items' => LabelResource::collection(
                Label::searchByQueryString()
                    ->sortByQueryString()
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->paginate(12)
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Settings/Labels/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLabelRequest $request)
    {
        Label::create($request->validated());

        return redirect()->route('settings.labels.index')->success('Label created', 'A new label was successfully created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Label $label)
    {
        return Inertia::render('Settings/Labels/Edit', ['item' => new LabelResource($label)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Label $label, UpdateLabelRequest $request)
    {
        $label->update($request->validated());

        return redirect()->route('settings.labels.index')->success('Label updated', 'The label was successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Label $label)
    {
        $label->archive();

        return redirect()->back()->success('Label archived', 'The label was successfully archived.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $labelId)
    {
        $label = Label::withArchived()->findOrFail($labelId);

        $this->authorize('restore', $label);

        $label->unArchive();

        return redirect()->back()->success('Label restored', 'The restoring of the label was completed successfully.');
    }
}
