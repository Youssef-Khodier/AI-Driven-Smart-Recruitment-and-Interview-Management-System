<?php

require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Core\Config;
use App\Core\Database;

Config::load(dirname(__DIR__));

$command = $argv[1] ?? 'help';

if ($command === 'schema') {
    $config = Config::database();
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $config['host'], $config['port']);
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec((string) file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
    print "Database schema loaded.\n";
    exit(0);
}

if ($command === 'seed') {
    require __DIR__ . '/seed.php';
    exit(0);
}

if ($command === 'create-hr-admin') {
    Database::configure(Config::database());
    $options = getopt('', ['name::', 'email:', 'password:']);
    $email = $options['email'] ?? null;
    $password = $options['password'] ?? null;
    $name = $options['name'] ?? Config::get('FIRST_HR_ADMIN_NAME');

    if (! $email || ! $password) {
        print "Usage: php scripts/db.php create-hr-admin --email=admin@example.com --password=secret [--name=Name]\n";
        exit(1);
    }

    if (Database::fetch('SELECT user_id FROM users WHERE email = ?', [$email])) {
        print "User already exists: {$email}\n";
        exit(0);
    }

    $department = Database::fetch('SELECT department_id FROM departments WHERE name = ?', ['Human Resources']);
    $now = date('Y-m-d H:i:s');
    Database::insert('users', [
        'department_id' => $department['department_id'] ?? null,
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'HR_ADMIN',
        'status' => 'ACTIVE',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    print "Created HR admin: {$email}\n";
    exit(0);
}

print "SRIM database utility\n";
print "Usage:\n";
print "  php scripts/db.php schema\n";
print "  php scripts/db.php seed\n";
print "  php scripts/db.php create-hr-admin --email=admin@example.com --password=secret [--name=Name]\n";
