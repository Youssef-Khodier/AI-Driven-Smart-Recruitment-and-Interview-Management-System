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


// ---------------------------------------------------------
// Feature 008: Screening & Shortlisting Seed Data
// ---------------------------------------------------------
$hrUser = \App\Core\Database::fetch('SELECT user_id FROM users WHERE role = ? LIMIT 1', ['HR_ADMIN']);
$job = \App\Core\Database::fetch('SELECT job_id FROM job_requisitions LIMIT 1');

if ($hrUser && $job) {
    $configRepo = new \App\Repositories\ScreeningConfigRepository();
    $skills = [
        ['skill_name' => 'PHP', 'weight' => 40, 'evidence_field' => 'skill_keywords'],
        ['skill_name' => 'MySQL', 'weight' => 30, 'evidence_field' => 'skill_keywords'],
        ['skill_name' => 'JavaScript', 'weight' => 20, 'evidence_field' => 'skill_keywords'],
        ['skill_name' => 'Software Architecture', 'weight' => 10, 'evidence_field' => 'skill_keywords']
    ];
    $thresholds = [
        ['min_score' => 0, 'max_score' => 39, 'target_status' => 'REJECTED'],
        ['min_score' => 40, 'max_score' => 59, 'target_status' => 'SCREENING'],
        ['min_score' => 60, 'max_score' => 79, 'target_status' => 'ASSESSMENT'],
        ['min_score' => 80, 'max_score' => 100, 'target_status' => 'INTERVIEW']
    ];
    $configRepo->saveConfig($job['job_id'], $hrUser['user_id'], $skills, $thresholds);
    echo 'Screening and shortlisting seed data created successfully.\n';
}

