<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('hr.dashboard', [
            'title' => 'HR Dashboard',
            'userCount' => User::count(),
        ]);
    }
}
