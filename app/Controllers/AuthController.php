<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\AccountStatus;
use App\Enums\UserRole;

final class AuthController extends Controller
{
    public function login(Request $request): Response
    {
        return $this->view('auth/login', ['title' => 'Login']);
    }

    public function authenticate(Request $request): Response
    {
        $data = $this->validate($request->body(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($data['email'], $data['password'])) {
            throw new ValidationException(['email' => ['The provided credentials do not match our records.']]);
        }

        return $this->redirect($this->dashboardPath(Auth::user()));
    }

    public function register(Request $request): Response
    {
        return $this->view('auth/register', ['title' => 'Candidate Registration']);
    }

    public function storeRegistration(Request $request): Response
    {
        $data = $this->validate($request->body(), [
            'name' => ['required', ['max', 160]],
            'email' => ['required', 'email', ['max', 180]],
            'password' => ['required', ['min', 8]],
            'phone' => ['required', ['max', 40]],
            'current_title' => [['max', 160]],
            'years_experience' => ['numeric'],
            'location' => [['max', 160]],
            'skill_keywords' => [['max', 2000]],
        ]);

        if (Database::fetch('SELECT user_id FROM users WHERE email = ?', [$data['email']])) {
            throw new ValidationException(['email' => ['That email address is already registered.']]);
        }

        $candidateId = Database::transaction(function () use ($data): int {
            $now = date('Y-m-d H:i:s');
            $id = Database::insert('users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => UserRole::CANDIDATE->value,
                'status' => AccountStatus::ACTIVE->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Database::insert('candidates', [
                'candidate_id' => $id,
                'phone' => $data['phone'],
                'current_title' => $data['current_title'] ?? null,
                'years_experience' => (int) ($data['years_experience'] ?? 0),
                'location' => $data['location'] ?? null,
                'skill_keywords' => $data['skill_keywords'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        });

        Session::regenerate();
        Session::put('user_id', $candidateId);
        Session::flash('status', 'Registration complete.');

        return $this->redirect(url('candidate.dashboard'));
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        Session::flash('status', 'You have been signed out.');

        return $this->redirect(url('login'));
    }

    private function dashboardPath(?array $user): string
    {
        return match ($user['role'] ?? null) {
            UserRole::HR_ADMIN->value => url('hr.dashboard'),
            UserRole::INTERVIEWER->value => url('interviewer.dashboard'),
            default => url('candidate.dashboard'),
        };
    }
}
