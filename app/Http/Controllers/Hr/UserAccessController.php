<?php

namespace App\Http\Controllers\Hr;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\UpdateUserAccessRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Support\AccountAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function edit(User $user): View
    {
        return view('hr.users.access', [
            'title' => 'Edit User Access',
            'target' => $user,
            'roles' => UserRole::cases(),
            'statuses' => AccountStatus::cases(),
        ]);
    }

    public function update(UpdateUserAccessRequest $request, User $user, AccountAuditLogger $auditLogger, UserPolicy $policy): RedirectResponse
    {
        $newRole = UserRole::from($request->string('role')->toString());
        $newStatus = AccountStatus::from($request->string('status')->toString());

        if ($policy->wouldRemoveLastActiveHrAdmin($user, $newRole->value, $newStatus->value)) {
            throw ValidationException::withMessages([
                'role' => 'The last active HR admin cannot be downgraded or deactivated.',
            ]);
        }

        DB::transaction(function () use ($request, $user, $newRole, $newStatus, $auditLogger): void {
            $oldRole = $user->role->value;
            $oldStatus = $user->status->value;

            $user->forceFill([
                'role' => $newRole,
                'status' => $newStatus,
            ])->save();

            if ($oldRole !== $newRole->value) {
                $auditLogger->roleChanged($request->user(), $user, $oldRole, $newRole->value);
            }

            if ($oldStatus !== $newStatus->value) {
                $auditLogger->statusChanged($request->user(), $user, $oldStatus, $newStatus->value);
            }
        });

        return redirect()->route('hr.users.index')->with('status', "Updated access for {$user->name}.");
    }
}
