<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$email_username = getenv('MY_EMAIL_USERNAME');
$email_password = getenv('MY_EMAIL_PASSWORD');



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// reCAPTCHA configuration
$recaptcha_secret =getenv('RECAPTCHA_SECRET');
$recaptcha_threshold = 0.5;

// Fetch POST data
$token = $_POST['recaptcha_token'];
$hidden_field = $_POST['hidden_field'];
$name = htmlspecialchars($_POST['name']);
$company = htmlspecialchars($_POST['company']);
$email = htmlspecialchars($_POST['email']);
$phone = htmlspecialchars($_POST['phone']);
$message = htmlspecialchars($_POST['message']);

// Honeypot check
if (!empty($hidden_field)) {
  die('Submission blocked due to unusual activity. Please try again or contact support if this continues.');

}

$restricted_companies = ['google']; // Add 'google' to block only the simplest form

if (in_array(strtolower($company), $restricted_companies)) {
    die('Your submission was blocked due to restrictions on company information.');
}

$blocked_domains = ['rudiplomust.com', 'rambler.ru', 'dehumanmail.com', 'mail.ru', 'stt45.mailprocessor.pics'];

$email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
if (in_array($email_domain, $blocked_domains)) {
   die('Your email domain is restricted. Please ensure you are using a valid email address.');
}

// Verify reCAPTCHA token with Google
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$token");
$responseData = json_decode($response);

if (!$responseData->success || $responseData->score < $recaptcha_threshold) {
   die('Failed verification. Please try again or refresh the page. If this persists, contact support.');
}


// Send email using PHPMailer
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $email_username; // Your email
    $mail->Password = $email_password; // Your email password or app-specific password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom($email, $name);
    $mail->addAddress('amaiyo.praises@gmail.com'); // Test email

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body = "
        <h2>Contact Form Submission</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Company:</strong> $company</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Message:</strong> $message</p>
    ";

        // Send email
        if ($mail->send()) {
            // After sending the email, redirect to the thank-you page
            header("Location: thank-you.php"); // Redirect to the thank-you page
            exit(); // Make sure the script ends after redirection
        } else {
            echo 'An error occurred while processing your request. Please try again later or contact support';
        }

} catch (Exception $e) {
    echo "An error occurred while processing your request. Please try again later or contact support";
}
?>
