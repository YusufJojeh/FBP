<?php
require_once 'includes/db.php';

$newHash = password_hash('password', PASSWORD_BCRYPT);

$emails = [
    'admin@example.com',
    'alice@vendor.test',
    'bob@client.test'
];

foreach ($emails as $email) {
    $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")
        ->execute([$newHash, $email]);
}

echo "Passwords updated for all users!";

