<?php
// Email Debug Tool - Test SMTP Configuration
// Access: https://turbocard.net/debug_email.php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\GeneralSetting;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; color: #2e7d32; }
        .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; color: #c62828; }
        .warning { background: #fff9c4; padding: 15px; border-radius: 5px; margin: 10px 0; color: #f57f17; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input[type="email"] { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Email Debug Tool</h1>
        
        <?php
        try {
            $general = GeneralSetting::first();
            
            if (!$general) {
                echo '<div class="error"><strong>Error:</strong> General Settings not found!</div>';
                exit;
            }
            
            echo '<div class="info">';
            echo '<h3>📋 Current Configuration:</h3>';
            echo '<strong>Site Name:</strong> ' . htmlspecialchars($general->site_name ?? 'N/A') . '<br>';
            echo '<strong>Email From:</strong> ' . htmlspecialchars($general->email_from ?? 'NOT SET') . '<br>';
            echo '<strong>Email From Name:</strong> ' . htmlspecialchars($general->email_from_name ?? 'NOT SET') . '<br>';
            echo '<strong>Email Notification Enabled:</strong> ' . (gs('en') ? '✅ Yes' : '❌ No') . '<br>';
            echo '</div>';
            
            $config = $general->mail_config;
            
            if (!$config || !isset($config->name)) {
                echo '<div class="error"><strong>Error:</strong> Mail configuration not found!</div>';
                exit;
            }
            
            echo '<div class="info">';
            echo '<h3>⚙️ SMTP Settings:</h3>';
            echo '<strong>Method:</strong> ' . strtoupper($config->name ?? 'N/A') . '<br>';
            echo '<strong>Host:</strong> ' . htmlspecialchars($config->host ?? 'NOT SET') . '<br>';
            echo '<strong>Port:</strong> ' . htmlspecialchars($config->port ?? 'NOT SET') . '<br>';
            echo '<strong>Encryption:</strong> ' . strtoupper($config->enc ?? 'NOT SET') . '<br>';
            echo '<strong>Username:</strong> ' . htmlspecialchars($config->username ?? 'NOT SET') . '<br>';
            echo '<strong>Password:</strong> ' . (isset($config->password) && !empty($config->password) ? '***' . substr($config->password, -3) : 'NOT SET') . '<br>';
            echo '</div>';
            
            // Check if test email is requested
            $testEmail = $_GET['test_email'] ?? null;
            
            if ($testEmail && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="info">';
                echo '<h3>📧 Testing Email Sending...</h3>';
                echo '<strong>To:</strong> ' . htmlspecialchars($testEmail) . '<br>';
                echo '</div>';
                
                // Validate required settings
                $errors = [];
                if (empty($config->host)) $errors[] = 'SMTP Host is not set';
                if (empty($config->username)) $errors[] = 'SMTP Username is not set';
                if (empty($config->password)) $errors[] = 'SMTP Password is not set';
                if (empty($config->port)) $errors[] = 'SMTP Port is not set';
                if (empty($general->email_from)) $errors[] = 'Email From address is not set in Global Template';
                
                if (!empty($errors)) {
                    echo '<div class="error">';
                    echo '<strong>❌ Configuration Errors:</strong><ul>';
                    foreach ($errors as $error) {
                        echo '<li>' . htmlspecialchars($error) . '</li>';
                    }
                    echo '</ul></div>';
                } else {
                    // Try to send email
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
                    $mail->CharSet = 'UTF-8';
                    
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    
                    $fromEmail = $general->email_from;
                    $fromName = $general->email_from_name ?? $general->site_name;
                    
                    $mail->setFrom($fromEmail, $fromName);
                    $mail->addAddress($testEmail, 'Test User');
                    $mail->addReplyTo($fromEmail, $fromName);
                    $mail->isHTML(true);
                    $mail->Subject = 'Test Email from ' . $general->site_name;
                    $mail->Body    = '<h2>Test Email</h2><p>This is a test email from ' . htmlspecialchars($general->site_name) . '</p><p>If you received this email, your SMTP configuration is working correctly!</p>';
                    
                    try {
                        $result = $mail->send();
                        if ($result) {
                            echo '<div class="success">';
                            echo '<strong>✅ SUCCESS!</strong><br>';
                            echo 'Email sent successfully to: ' . htmlspecialchars($testEmail) . '<br>';
                            echo 'Please check your inbox (and spam folder).';
                            echo '</div>';
                        } else {
                            throw new Exception('PHPMailer send() returned false');
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">';
                        echo '<strong>❌ FAILED TO SEND EMAIL</strong><br>';
                        echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br>';
                        if (isset($mail->ErrorInfo)) {
                            echo '<strong>PHPMailer Error Info:</strong><br>';
                            echo '<pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
                        }
                        echo '</div>';
                    }
                }
            } else {
                // Show test form
                echo '<div class="info">';
                echo '<h3>🧪 Test Email Sending</h3>';
                echo '<form method="GET">';
                echo '<label>Enter email address to test:</label>';
                echo '<input type="email" name="test_email" placeholder="your-email@example.com" required>';
                echo '<button type="submit">Send Test Email</button>';
                echo '</form>';
                echo '</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <div class="warning" style="margin-top: 20px;">
            <strong>⚠️ Note:</strong> This tool is for debugging only. Remove or protect this file in production.
        </div>
    </div>
</body>
</html>

