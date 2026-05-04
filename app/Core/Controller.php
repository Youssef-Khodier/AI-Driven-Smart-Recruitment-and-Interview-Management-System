<?php

namespace App\Core;

use App\Enums\AccountStatus;

abstract class Controller
{
    protected function view(string $view, array $data = []): Response
    {
        return Response::view($view, $data);
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect($path);
    }

    protected function back(): Response
    {
        return Response::redirect(Request::current()?->referer() ?: url('dashboard'));
    }

    protected function requireAuth(): array
    {
        $user = Auth::user();
        if (! $user) {
            throw new RedirectException(url('login'));
        }

        if ($user['status'] !== AccountStatus::ACTIVE->value) {
            Auth::logout();
            throw new HttpException(403, 'Inactive accounts cannot access SRIM.');
        }

        return $user;
    }

    protected function requireRole(string ...$roles): array
    {
        $user = $this->requireAuth();
        if (! in_array($user['role'], $roles, true)) {
            throw new HttpException(403, 'You are not authorized to access this page.');
        }

        return $user;
    }

    protected function validate(array $data, array $rules): array
    {
        return Validator::validate($data, $rules);
    }
}
