<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Manually test the email logic
use App\Notify\Email;
use App\Models\GeneralSetting;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "--- Debugging Email ---\n";

try {
    $general = GeneralSetting::first();
    echo "General Settings Found: " . ($general ? 'Yes' : 'No') . "\n";
    
    if (!$general) {
        die("No General Settings found in DB.\n");
    }

    $mailConfig = $general->mail_config;
    echo "Mail Config Raw: " . print_r($mailConfig, true) . "\n";

    if (!isset($mailConfig->name)) {
        echo "No mail method name set.\n";
    } else {
        echo "Mail Method: " . $mailConfig->name . "\n";
    }

    // Attempt to mimic the NotificationController logic for email testing
    // Ref: public function emailTest(Request $request)
    
    // We will try to send using PHPMailer directly first to see if connection works, using the config from DB
    
    if (isset($mailConfig->name) && $mailConfig->name == 'smtp') {
        echo "\nAttempting SMTP Connection...\n";
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 3; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = $mailConfig->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig->username;
        $mail->Password   = $mailConfig->password;
        if ($mailConfig->enc == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port       = $mailConfig->port;
        
        echo "Host: " . $mail->Host . "\n";
        echo "Port: " . $mail->Port . "\n";
        echo "Username: " . $mail->Username . "\n";
        echo "Encryption: " . $mailConfig->enc . "\n";

        // Disable SSL verification for debug if needed (matching app code)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        try {
            $mail->setFrom($general->email_from, $general->site_name);
            $mail->addAddress('test@example.com', 'Test User'); // Just a test address
            $mail->Subject = 'Debug Email Test';
            $mail->Body    = 'This is a debug email.';
            $mail->send();
            echo "\nMessage has been sent successfully via PHPMailer.\n";
        } catch (Exception $e) {
            echo "\nMessage could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        }
    } else {
        echo "Mail method is not SMTP or not configured.\n";
    }

} catch (\Exception $e) {
    echo "Global Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n--- End Debug ---\n";
