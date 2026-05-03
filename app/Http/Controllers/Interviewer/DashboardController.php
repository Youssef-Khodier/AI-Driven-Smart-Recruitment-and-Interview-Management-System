<?php

namespace App\Http\Controllers\Interviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('interviewer.dashboard', [
            'title' => 'Interviewer Dashboard',
            'user' => $request->user(),
        ]);
    }
}
