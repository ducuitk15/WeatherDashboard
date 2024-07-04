<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Nhận email từ form
$email = $_POST['email'];

// Tạo mã xác nhận ngẫu nhiên
$verificationCode = rand(100000, 999999);

// Kết nối đến cơ sở dữ liệu SQLite
$conn = new SQLite3('database.db');

// Lưu email và mã xác nhận vào cơ sở dữ liệu
$stmt = $conn->prepare("INSERT INTO email_verification (email, code) VALUES (:email, :code)");
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':code', $verificationCode, SQLITE3_INTEGER);
$stmt->execute();

// Cấu hình PHPMailer
$mail = new PHPMailer(true);

try {
    // Cài đặt máy chủ
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->Username   = 'ducyt0607@gmail.com'; 
    $mail->Password   = 'REPLACE_WITH_YOUR_APP_PASSWORD'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Người gửi và người nhận
    $mail->setFrom('ducyt0607@gmail.com', 'Mailer');
    $mail->addAddress($email);

    // Nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code';
    $mail->Body    = "Your verification code is: <b>$verificationCode</b>";
    $mail->AltBody = "Your verification code is: $verificationCode";

    $mail->send();
    $message = 'Verification code has been sent to your email.';
    $messageType = 'success';
} catch (Exception $e) {
    $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        .alert {
            padding: 20px;
            margin-bottom: 15px;
        }
        .success {
            background-color: #4CAF50; /* Green */
            color: white;
        }
        .error {
            background-color: #f44336; /* Red */
            color: white;
        }
    </style>
</head>
<body>
    <?php if (isset($message)) : ?>
        <div class="alert <?= $messageType; ?>">
            <?= $message; ?>
        </div>
    <?php endif; ?>
</body>
</html>
