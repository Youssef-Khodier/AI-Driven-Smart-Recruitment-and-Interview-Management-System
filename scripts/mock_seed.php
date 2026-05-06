<?php

require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Core\Config;
use App\Core\Database;

Config::load(dirname(__DIR__));
Database::configure(Config::database());

$now = date('Y-m-d H:i:s');

print "Starting mock data seeding...\n";

// 1. Departments
print "1. Departments...\n";
$deptIds = [];
$departments = [
    ['name' => 'Human Resources', 'description' => 'Recruitment and HR operations'],
    ['name' => 'Engineering', 'description' => 'Technical hiring department'],
    ['name' => 'Marketing', 'description' => 'Marketing and Growth'],
    ['name' => 'Sales', 'description' => 'Sales and Business Development']
];
foreach ($departments as $dept) {
    try {
        Database::query("INSERT INTO departments (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)", [$dept['name'], $dept['description']]);
    } catch (PDOException $e) {}
    $row = Database::fetch('SELECT department_id FROM departments WHERE name = ?', [$dept['name']]);
    if ($row) $deptIds[] = $row['department_id'];
}

// 2. Users (HR, Interviewers)
print "2. Users...\n";
$password = password_hash('password123', PASSWORD_DEFAULT);
$users = [
    ['name' => 'John HR', 'email' => 'hr@test.com', 'role' => 'HR_ADMIN', 'status' => 'ACTIVE'],
    ['name' => 'Alice Interviewer', 'email' => 'interviewer1@test.com', 'role' => 'INTERVIEWER', 'status' => 'ACTIVE'],
    ['name' => 'Bob Interviewer', 'email' => 'interviewer2@test.com', 'role' => 'INTERVIEWER', 'status' => 'ACTIVE'],
];

$hrIds = [];
$interviewerIds = [];

foreach ($users as $u) {
    $deptId = $deptIds[array_rand($deptIds)];
    try {
        Database::insert('users', [
            'department_id' => $deptId,
            'name' => $u['name'],
            'email' => $u['email'],
            'password_hash' => $password,
            'role' => $u['role'],
            'status' => $u['status'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } catch (PDOException $e) {}
    $row = Database::fetch('SELECT user_id FROM users WHERE email = ?', [$u['email']]);
    if ($row) {
        if ($u['role'] === 'HR_ADMIN') $hrIds[] = $row['user_id'];
        else $interviewerIds[] = $row['user_id'];
    }
}

// 3. Candidates
print "3. Candidates...\n";
$candidateUsers = [];
$cData = [
    ['name' => 'Cathy Candidate', 'email' => 'candidate1@test.com'],
    ['name' => 'Dan Candidate', 'email' => 'candidate2@test.com'],
    ['name' => 'Eve Candidate', 'email' => 'candidate3@test.com'],
    ['name' => 'Frank Candidate', 'email' => 'candidate4@test.com'],
];
foreach ($cData as $c) {
    try {
        Database::insert('users', [
            'name' => $c['name'],
            'email' => $c['email'],
            'password_hash' => $password,
            'role' => 'CANDIDATE',
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } catch (PDOException $e) {}
    $row = Database::fetch('SELECT user_id FROM users WHERE email = ?', [$c['email']]);
    if ($row) {
        $cId = $row['user_id'];
        $candidateUsers[] = $cId;
        try {
            Database::insert('candidates', [
                'user_id' => $cId,
                'resume_path' => 'resumes/mock_resume.pdf',
                'parsed_skills' => json_encode(['PHP', 'MySQL', 'JavaScript']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (PDOException $e) {}
    }
}

// 4. Job Requisitions
print "4. Job Requisitions...\n";
$jobs = [
    ['title' => 'Senior PHP Engineer', 'type' => 'FULL_TIME', 'location' => 'Remote', 'status' => 'PUBLISHED'],
    ['title' => 'Frontend Developer', 'type' => 'FULL_TIME', 'location' => 'New York', 'status' => 'PUBLISHED'],
    ['title' => 'Product Manager', 'type' => 'FULL_TIME', 'location' => 'London', 'status' => 'DRAFT'],
];
$jobIds = [];
$hrId = $hrIds[0] ?? 1;

foreach ($jobs as $j) {
    $deptId = $deptIds[array_rand($deptIds)];
    try {
        $jobId = Database::insert('job_requisitions', [
            'department_id' => $deptId,
            'created_by' => $hrId,
            'title' => $j['title'],
            'description' => 'This is a mock description for ' . $j['title'],
            'requirements' => 'Mock requirements',
            'employment_type' => $j['type'],
            'location' => $j['location'],
            'salary_range' => '100k - 150k',
            'status' => $j['status'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $jobIds[] = $jobId;
    } catch (PDOException $e) {}
}

if (empty($jobIds)) {
    $rows = Database::query('SELECT job_id FROM job_requisitions LIMIT 3');
    foreach ($rows as $r) $jobIds[] = $r['job_id'];
}

// 5. Applications & Interivews
print "5. Applications & Interviews...\n";
$appStatuses = ['NEW', 'SCREENING', 'ASSESSMENT', 'INTERVIEW', 'OFFER', 'REJECTED'];

foreach ($candidateUsers as $index => $cId) {
    if (empty($jobIds)) break;
    $appliedJobs = array_slice($jobIds, 0, 2); // apply to first 2 jobs
    foreach ($appliedJobs as $jobId) {
        $status = $appStatuses[array_rand($appStatuses)];
        try {
            $appId = Database::insert('applications', [
                'candidate_id' => $cId,
                'job_id' => $jobId,
                'status' => $status,
                'score' => rand(50, 100),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (in_array($status, ['INTERVIEW', 'OFFER', 'REJECTED'])) {
                // Add an interview
                $intId = Database::insert('interviews', [
                    'application_id' => $appId,
                    'round_number' => 1,
                    'round_name' => 'Technical Interview',
                    'scheduled_time' => date('Y-m-d H:i:s', strtotime('+'.rand(1,5).' days')),
                    'duration_minutes' => 60,
                    'status' => rand(0,1) ? 'SCHEDULED' : 'COMPLETED',
                    'meeting_link' => 'https://zoom.us/mock',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if (!empty($interviewerIds)) {
                    Database::insert('interviewers_assignment', [
                        'interview_id' => $intId,
                        'user_id' => $interviewerIds[array_rand($interviewerIds)],
                        'is_primary' => 1,
                    ]);
                }
            }

        } catch (PDOException $e) {}
    }
}

// 6. Demo Offers
print "6. Demo Offers...\n";
if (!empty($candidateUsers) && !empty($jobIds) && count($candidateUsers) > 0) {
    try {
        $appRow = Database::fetch('SELECT application_id, candidate_id FROM applications LIMIT 1');
        if ($appRow) {
            Database::insert('offers', [
                'application_id' => $appRow['application_id'],
                'candidate_id' => $appRow['candidate_id'],
                'base_salary' => 120000.00,
                'bonus_percentage' => 10.00,
                'stock_options' => 5000,
                'other_benefits' => '{"health":"Premium"}',
                'total_package_value' => 135000.00,
                'status' => 'PENDING',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    } catch (PDOException $e) {}
}

print "Seeding completed successfully.\n";
print "Use credentials: \n";
print " - HR: hr@test.com / password123\n";
print " - Candidate: candidate1@test.com / password123\n";
print " - Interviewer: interviewer1@test.com / password123\n";
