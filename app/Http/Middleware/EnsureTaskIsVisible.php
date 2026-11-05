<?php

namespace App\Http\Middleware;

use App\Models\Task;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTaskIsVisible
{
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');
        $user = $request->user();

        if ($task instanceof Task && $user instanceof User && $task->isHiddenFrom($user)) {
            abort(404);
        }

        return $next($request);
    }
}
