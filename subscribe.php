<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Connect to the SQLite database
    $conn = new SQLite3('database.db');

    // Insert the email into the subscribed_users table
    $stmt = $conn->prepare("INSERT INTO subscribed_users (email) VALUES (:email)");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->execute();

    $response['status'] = 'success';
    $response['message'] = 'You have been subscribed successfully.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
