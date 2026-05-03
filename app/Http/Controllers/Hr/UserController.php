<?php

namespace App\Http\Controllers\Hr;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreUserRequest;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\User;
use App\Support\AccountAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('hr.users.index', [
            'title' => 'User Administration',
            'users' => User::with('department')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('hr.users.create', [
            'title' => 'Create User',
            'departments' => Department::orderBy('name')->get(),
            'roles' => UserRole::cases(),
            'statuses' => \App\Enums\AccountStatus::cases(),
        ]);
    }

    public function store(StoreUserRequest $request, AccountAuditLogger $auditLogger): RedirectResponse
    {
        $target = DB::transaction(function () use ($request, $auditLogger): User {
            $target = User::create([
                'department_id' => $request->input('department_id'),
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'password_hash' => Hash::make($request->string('password')),
                'role' => UserRole::from($request->string('role')->toString()),
                'status' => \App\Enums\AccountStatus::from($request->string('status')->toString()),
            ]);

            if ($target->hasRole(UserRole::CANDIDATE)) {
                Candidate::create([
                    'candidate_id' => $target->user_id,
                    'phone' => $request->string('phone'),
                ]);
            }

            $auditLogger->created($request->user(), $target);

            return $target;
        });

        return redirect()->route('hr.users.index')->with('status', "Created {$target->name}.");
    }
}
