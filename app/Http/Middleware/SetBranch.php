<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('branch')) {
            if (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Bordador') || auth()->user()->hasRole('Estampador')) {
                session()->put('branch', $request->get('branch'));
            } else {
                session()->put('branch', auth()->user()->branch_id);
            }
        }
        return $next($request);
    }
}
