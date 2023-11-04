<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


// Initialize PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'jsnode741@gmail.com';
$mail->Password = 'vanc njjm vpmg mnmf';

$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$userEmail = $_POST['userEmail'];

// Your email configuration goes here

try {
    // Set up PHPMailer and send the login alert email
    $mail->isSMTP();
    // Configure your SMTP settings here
    $mail->Subject = 'Login Alert';
    $mail->Body = '
    <html>
    <body>
        <div style="font-family: Arial, sans-serif; font-size: 14px;">
            <p>A successful login has been detected for your account at <strong>UIU Activity Tracker</strong> on ' . date('Y-m-d H:i:s') . '.</p>
            <p style="color: #FF0000;">If this was not you, please contact us immediately.</p>
        </div>
    </body>
    </html>';
    $mail->IsHTML(true);


    
    $mail->addAddress($userEmail);

  
    $mail->send();

    echo 'Login alert email sent successfully.';
} catch (Exception $e) {
    echo 'Failed to send login alert email: ' . $mail->ErrorInfo;
}
