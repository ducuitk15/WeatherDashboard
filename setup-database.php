<?php
// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to the SQLite database
$conn = new SQLite3('database.db');
$conn->exec("DROP TABLE IF EXISTS email_verification;");
$conn->exec("DROP TABLE IF EXISTS subscribed_users;");

// Create email_verification table
$createEmailVerificationTableQuery = "
CREATE TABLE IF NOT EXISTS email_verification (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    code INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$conn->exec($createEmailVerificationTableQuery);

// Create subscribed_users table
$createSubscribedUsersTableQuery = "
CREATE TABLE IF NOT EXISTS subscribed_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$conn->exec($createSubscribedUsersTableQuery);

echo "Tables created successfully.";
?>

