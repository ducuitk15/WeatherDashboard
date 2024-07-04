<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $verificationCode = $_POST['verification_code'];

    // Connect to SQLite database
    $conn = new SQLite3('database.db');

    // Retrieve verification code from the database
    $stmt = $conn->prepare('SELECT code FROM email_verification WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row && $verificationCode == $row['code']) {
        $response['status'] = 'success';
        $response['message'] = 'Email verified successfully.';

        // Delete the verification code after successful verification
        $stmt = $conn->prepare('DELETE FROM email_verification WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();

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
            $mail->Password   = 'REPLACE_WITH_YOUR_APP_PASSWORD';
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
            $response['message'] .= ' Search history report has been sent to your email.';
        } catch (Exception $e) {
            $response['message'] .= " Search history report could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid verification code.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
