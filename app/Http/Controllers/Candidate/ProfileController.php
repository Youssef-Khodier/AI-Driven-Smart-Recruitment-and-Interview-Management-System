<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Requests\Candidate\UpdateProfileRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        return $this->edit($request);
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('candidate');

        return view('candidate.profile', [
            'title' => 'Candidate Profile',
            'user' => $user,
            'candidate' => $user->candidate,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->candidate()->updateOrCreate(
            ['candidate_id' => $request->user()->user_id],
            $request->validated()
        );

        return redirect()->route('candidate.profile')->with('status', 'Candidate profile updated.');
    }
}
