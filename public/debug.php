<?php
// Retrieve the root path. Assuming this file is in /public/
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\GeneralSetting;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "--- Web Debug Email ---\n";

try {
    $general = GeneralSetting::first();
    echo "GS Found: " . ($general ? 'Yes' : 'No') . "<br>";
    
    if (!$general) die('No GS');

    $config = $general->mail_config;
    echo "Method: " . $config->name . "<br>";
    echo "Host: " . $config->host . "<br>";
    
    // Check gs('en')
    echo "Email Notification (en): " . (gs('en') ? 'Enabled' : 'Disabled') . "<br>";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config->host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $config->username;
    $mail->Password   = $config->password;
    
    // Port 465 requires SSL (SMTPS), Port 587 requires TLS (STARTTLS)
    if ($config->port == 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($config->port == 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($config->enc == 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port       = $config->port;
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom($general->email_from, $general->site_name);
    $mail->addAddress('test_web@example.com', 'Test Web User');
    $mail->Subject = 'Web Debug Email';
    $mail->Body    = 'Web debug body';
    
    $mail->send();
    echo "SENT SUCCESS";

} catch (Exception $e) {
    echo "ERROR: " . $mail->ErrorInfo;
} catch (\Exception $e) {
    echo "GENERIC ERROR: " . $e->getMessage();
}
