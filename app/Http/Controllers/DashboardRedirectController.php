<?php

namespace App\Http\Controllers;

use App\Support\RoleDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route(RoleDashboard::routeNameFor($request->user()->refresh()));
    }
}
