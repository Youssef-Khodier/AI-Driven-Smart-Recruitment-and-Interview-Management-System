<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('candidate');

        return view('candidate.profile', [
            'title' => 'Candidate Profile',
            'user' => $user,
            'candidate' => $user->candidate,
        ]);
    }
}
