<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCandidateRequest;
use App\Models\Candidate;
use App\Models\User;
use App\Support\RoleDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CandidateRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register', ['title' => 'Candidate Registration']);
    }

    public function store(RegisterCandidateRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'password_hash' => Hash::make($request->string('password')),
                'role' => UserRole::CANDIDATE,
                'status' => AccountStatus::ACTIVE,
            ]);

            Candidate::create([
                'candidate_id' => $user->user_id,
                'phone' => $request->string('phone'),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route(RoleDashboard::routeNameFor($user))->with('status', 'Candidate registration complete.');
    }
}
