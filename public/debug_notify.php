<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\GeneralSetting;

echo "--- Debug Notify Helper ---\n";
echo "<pre>";

$general = GeneralSetting::first();
echo "Email Template (Raw):\n[" . htmlspecialchars($general->email_template) . "]\n\n";

if (empty($general->email_template)) {
    echo "WARNING: Email Template is EMPTY!\n";
} elseif (strpos($general->email_template, '{{message}}') === false) {
    echo "WARNING: Email Template MISSING {{message}} placeholder!\n";
} else {
    echo "Email Template seems OK (contains {{message}}).\n";
}

echo "Attempting notify()...\n";

$user = new stdClass();
$user->email = 'test_notify@example.com';
$user->fullname = 'Notify Tester';
$user->id = 888;
$user->username = 'notify_tester';

// Mimic NotificationController::emailTest
notify($user, null, [
    'subject' => 'Notify Helper Test',
    'message' => 'This is a test from debug_notify.php'
], ['email'], false);

if (session('mail_error')) {
    echo "SESSION MAIL ERROR: " . session('mail_error') . "\n";
} else {
    echo "No session error. Check Mailtrap for email.\n";
}

echo "</pre>";
