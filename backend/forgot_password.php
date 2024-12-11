<?php
session_start();
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];

    if (empty($email)) {
        header('Location: ../frontend/html/forgot_password.html?error=1');
        exit;
    }

    if ($action === 'get_otp' || $action === 'resend_otp') {
        try {
            // Fetch user by email
            $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                header('Location: ../frontend/html/forgot_password.html?error=email_not_found');
                exit;
            }

            // Generate OTP and expiry
            $otp = rand(100000, 999999);
            $expiry = time() + 600;  // 10 minutes expiry

            // Update the OTP and expiry in Users table
            $stmt = $pdo->prepare("UPDATE Users SET otp = ?, otp_expire = ? WHERE id = ?");
            $stmt->execute([$otp, $expiry, $user['id']]);

            // Send OTP via email using PHPMailer
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth = true;
                $mail->Username = 'b93029b66b14f6';
                $mail->Password = '5cb7df49c91ebc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('bd67df7a37-8a9f88@inbox.mailtrap.io', 'MystryMenu');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "Your OTP for resetting your password is: <strong>$otp</strong><br>This OTP is valid for 10 minutes.";

                $mail->send();
            } catch (Exception $e) {
                header('Location: ../frontend/html/forgot_password.html?error=email_failed');
                exit;
            }

            // Redirect with email to maintain form state
            header("Location: ../frontend/html/forgot_password.html?email=" . urlencode($email) . "&otp_" . ($action === 'resend_otp' ? 'resend' : 'sent') . "=1");
            exit;

        } catch (PDOException $e) {
            header('Location: ../frontend/html/forgot_password.html?error=1');
            exit;
        }
    } elseif ($action === 'verify_otp') {
        $otp = $_POST['otp'];

        if (empty($otp)) {
            header("Location: ../frontend/html/forgot_password.html?email=" . urlencode($email) . "&error=1");
            exit;
        }

        // delete expired otp
        try {
            $stmt = $pdo->prepare("UPDATE Users SET otp = NULL, otp_expire = NULL WHERE otp_expire < ?");
            $stmt->execute([time()]);
        } catch (PDOException $e) {
            header("Location: ../frontend/html/forgot_password.html?email=" . urlencode($email) . "&error=1");
            exit;
        }
        // Validate OTP
        try {
            $stmt = $pdo->prepare("SELECT id, otp, otp_expire FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['otp'] !== $otp || $user['otp_expire'] < time()) {
                header("Location: ../frontend/html/forgot_password.html?email=" . urlencode($email) . "&error=invalid_otp");
                exit;
            }

            // OTP is valid, proceed to reset password
            $_SESSION['user_id'] = $user['id'];
            header('Location: ../frontend/html/set_password.html');
            exit;

        } catch (PDOException $e) {
            header("Location: ../frontend/html/forgot_password.html?email=" . urlencode($email) . "&error=1");
            exit;
        }
    }
}
?>
