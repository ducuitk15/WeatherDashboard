<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$response = [];

try {
    // Connect to SQLite database
    $conn = new SQLite3('database.db');

    // Fetch the subscribed email
    $emailResult = $conn->query('SELECT email FROM subscribed_users ORDER BY created_at DESC LIMIT 1');
    $emailRow = $emailResult->fetchArray(SQLITE3_ASSOC);
    if (!$emailRow) {
        throw new Exception("No subscribed email found.");
    }
    $email = $emailRow['email'];

    // Fetch search history from the database
    $historyStmt = $conn->prepare('SELECT * FROM search_history WHERE email = :email');
    $historyStmt->bindValue(':email', $email, SQLITE3_TEXT);
    $historyResult = $historyStmt->execute();

    $history = [];
    while ($historyRow = $historyResult->fetchArray(SQLITE3_ASSOC)) {
        $history[] = $historyRow;
    }

    // Create the history report
    $historyReport = "Search History:\n\n";
    foreach ($history as $item) {
        $historyReport .= "City: " . $item['city'] . "\n";
        $historyReport .= "Date: " . $item['date'] . "\n";
        $historyReport .= "Temperature: " . $item['temperature'] . "°C\n";
        $historyReport .= "Wind: " . $item['wind'] . " KPH\n";
        $historyReport .= "Humidity: " . $item['humidity'] . "%\n";
        $historyReport .= "-------------------------\n";
    }

    // Send the search history report email
    $mail = new PHPMailer(true);

    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ducyt0607@gmail.com';
        $mail->Password   = 'znrr eckj nzpk dmuj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient settings
        $mail->setFrom('ducyt0607@gmail.com', 'Weather Service');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(false);
        $mail->Subject = 'Your Search History';
        $mail->Body    = $historyReport;

        $mail->send();
        $response['status'] = 'success';
        $response['message'] = 'Search history report has been sent to your email.';
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = "Search history report could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
