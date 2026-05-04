<?php

require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Core\Config;
use App\Core\Database;

Config::load(dirname(__DIR__));
Database::configure(Config::database());

$now = date('Y-m-d H:i:s');
Database::query("INSERT INTO departments (name, description) VALUES ('Human Resources', 'Recruitment and HR operations'), ('Engineering', 'Technical hiring department') ON DUPLICATE KEY UPDATE description = VALUES(description)");

$email = Config::get('FIRST_HR_ADMIN_EMAIL');
$existing = Database::fetch('SELECT user_id FROM users WHERE email = ?', [$email]);
if (! $existing) {
    $department = Database::fetch('SELECT department_id FROM departments WHERE name = ?', ['Human Resources']);
    Database::insert('users', [
        'department_id' => $department['department_id'] ?? null,
        'name' => Config::get('FIRST_HR_ADMIN_NAME'),
        'email' => $email,
        'password_hash' => password_hash(Config::get('FIRST_HR_ADMIN_PASSWORD'), PASSWORD_DEFAULT),
        'role' => 'HR_ADMIN',
        'status' => 'ACTIVE',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    print "Seeded first HR admin: {$email}\n";
} else {
    print "First HR admin already exists: {$email}\n";
}
