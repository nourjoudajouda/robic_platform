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
        
        @if(!$general)
            <div class="error"><strong>Error:</strong> General Settings not found!</div>
        @else
            <div class="info">
                <h3>📋 Current Configuration:</h3>
                <strong>Site Name:</strong> {{ $general->site_name ?? 'N/A' }}<br>
                <strong>Email From:</strong> {{ $general->email_from ?? 'NOT SET' }}<br>
                <strong>Email From Name:</strong> {{ $general->email_from_name ?? 'NOT SET' }}<br>
                <strong>Email Notification Enabled:</strong> {{ gs('en') ? '✅ Yes' : '❌ No' }}<br>
            </div>
            
            @if($config)
                <div class="info">
                    <h3>⚙️ SMTP Settings:</h3>
                    <strong>Method:</strong> {{ strtoupper($config->name ?? 'N/A') }}<br>
                    <strong>Host:</strong> {{ $config->host ?? 'NOT SET' }}<br>
                    <strong>Port:</strong> {{ $config->port ?? 'NOT SET' }}<br>
                    <strong>Encryption:</strong> {{ strtoupper($config->enc ?? 'NOT SET') }}<br>
                    <strong>Username:</strong> {{ $config->username ?? 'NOT SET' }}<br>
                    <strong>Password:</strong> {{ (isset($config->password) && !empty($config->password)) ? '***' . substr($config->password, -3) : 'NOT SET' }}<br>
                </div>
            @else
                <div class="error"><strong>Error:</strong> Mail configuration not found!</div>
            @endif
            
            @if($testEmail)
                <div class="info">
                    <h3>📧 Testing Email Sending...</h3>
                    <strong>To:</strong> {{ $testEmail }}<br>
                </div>
                
                @if($success)
                    <div class="success">
                        <strong>✅ PHPMailer reported SUCCESS!</strong><br>
                        Email sent successfully to: {{ $testEmail }}<br>
                        <strong>⚠️ Important:</strong> If you don't receive the email, check:<br>
                        <ul>
                            <li>Spam/Junk folder</li>
                            <li>Email server logs</li>
                            <li>SMTP server may have accepted but not delivered</li>
                        </ul>
                        @if($errorDetails)
                            <strong>SMTP Debug Output:</strong><br>
                            <pre style="max-height: 300px; overflow-y: auto;">{{ $errorDetails }}</pre>
                        @endif
                    </div>
                @elseif($error)
                    <div class="error">
                        <strong>❌ FAILED TO SEND EMAIL</strong><br>
                        <strong>Error:</strong> {{ $error }}<br>
                        @if($errorDetails)
                            <strong>SMTP Debug Output:</strong><br>
                            <pre style="max-height: 300px; overflow-y: auto;">{{ $errorDetails }}</pre>
                        @endif
                    </div>
                @endif
            @else
                <div class="info">
                    <h3>🧪 Test Email Sending</h3>
                    <form method="GET">
                        <label>Enter email address to test:</label>
                        <input type="email" name="test_email" placeholder="your-email@example.com" required>
                        <button type="submit">Send Test Email</button>
                    </form>
                </div>
            @endif
        @endif
        
        <div class="warning" style="margin-top: 20px;">
            <strong>⚠️ Note:</strong> This tool is for debugging only. Remove or protect this file in production.
        </div>
    </div>
</body>
</html>

