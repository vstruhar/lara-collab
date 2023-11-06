<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DropdownValuesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $dropdowns = collect();

        $dropdowns->when($request->has('users'), function (Collection $collection) {
            return $collection->put('users', User::userDropdownValues());
        });

        $dropdowns->when($request->has('clients'), function (Collection $collection) {
            return $collection->put('clients', User::clientDropdownValues());
        });

        return response()->json($dropdowns);
    }
}
