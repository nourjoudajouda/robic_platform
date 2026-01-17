<?php

namespace App\Notify;
use App\Notify\NotifyProcess;
use App\Notify\Notifiable;
use Mailjet\Client;
use Mailjet\Resources;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use SendGrid;
use SendGrid\Mail\Mail;

class Email extends NotifyProcess implements Notifiable{

    /**
    * Email of receiver
    *
    * @var string
    */
	public $email;

    /**
    * Assign value to properties
    *
    * @return void
    */
	public function __construct(){
		$this->statusField = 'email_status';
		$this->body = 'email_body';
		$this->globalTemplate = 'email_template';
		$this->notifyConfig = 'mail_config';
	}

    /**
    * Send notification
    *
    * @return void|bool
    */
	public function send(){

		if (!gs('en')) {
			return false;
		}
		//get message from parent
		$message = $this->getMessage();
		if (!$message || empty(trim($message))) {
			$errorMsg = 'Email message is empty. Please check your email template configuration.';
			$this->createErrorLog($errorMsg);
			session()->flash('mail_error', $errorMsg);
			return false;
		}
		
		// Ensure subject is set
		if (empty($this->subject)) {
			$this->subject = $this->shortCodes['subject'] ?? 'Notification from ' . gs('site_name');
		}
		
		//Send mail
		$methodName = gs('mail_config')->name;
		$method = $this->mailMethods($methodName);
		try{
			$this->$method();
			$this->createLog('email');
		}catch(\Exception $e){
			$errorMsg = $e->getMessage();
			$this->createErrorLog($errorMsg);
			session()->flash('mail_error', $errorMsg);
		}

	}

    /**
    * Get the method name
    *
    * @return string
    */
	protected function mailMethods($name){
		$methods = [
			'php'=>'sendPhpMail',
			'smtp'=>'sendSmtpMail',
			'sendgrid'=>'sendSendGridMail',
			'mailjet'=>'sendMailjetMail',
		];
		return $methods[$name];
	}

	protected function sendPhpMail(){
        $sentFromName = $this->getEmailFrom()['name'];
        $sentFromEmail = $this->getEmailFrom()['email'];
		$headers = "From: $sentFromName <$sentFromEmail> \r\n";
	    $headers .= "Reply-To: $sentFromName <$sentFromEmail> \r\n";
	    $headers .= "MIME-Version: 1.0\r\n";
	    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
	    @mail($this->email, $this->subject, $this->finalMessage, $headers);
	}

	protected function sendSmtpMail(){
		$mail = new PHPMailer(true);
		$config = gs('mail_config');
		
		// Validate SMTP configuration
		if (empty($config->host)) {
			throw new Exception('SMTP Host is not configured');
		}
		if (empty($config->username)) {
			throw new Exception('SMTP Username is not configured');
		}
		if (empty($config->password)) {
			throw new Exception('SMTP Password is not configured');
		}
		if (empty($config->port)) {
			throw new Exception('SMTP Port is not configured');
		}
		
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $config->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $config->username;
        $mail->Password   = $config->password;
        
        // Port 465 requires SSL (SMTPS), Port 587 requires TLS (STARTTLS)
        // Auto-detect encryption based on port if not explicitly set
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
        
        // Disable SSL verification for local dev (fixes Mailtrap/Laragon issues)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        //Recipients
        $fromEmail = $this->getEmailFrom()['email'];
        $fromName = $this->getEmailFrom()['name'];
        
        if (empty($fromEmail)) {
			throw new Exception('Email From address is not configured. Please set it in Global Email Template settings.');
		}
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($this->email, $this->receiverName);
        $mail->addReplyTo($fromEmail, $fromName);
        // Content
        $mail->isHTML(true);
        $mail->Subject = $this->subject;
        $mail->Body    = $this->finalMessage;
        
        // PHPMailer with exception mode (true) will throw Exception if send() fails
        // But we also check explicitly to ensure we catch any issues
        try {
            $result = $mail->send();
            
            // Even if send() returns true, check ErrorInfo for warnings
            if (!$result) {
                throw new Exception('PHPMailer send() returned false. Error: ' . $mail->ErrorInfo);
            }
            
            // Check for any errors or warnings even after successful send
            if (!empty($mail->ErrorInfo) && strpos($mail->ErrorInfo, 'SMTP') !== false) {
                // Log warning but don't fail - sometimes SMTP servers return warnings but still send
                \Log::warning('SMTP Warning after send: ' . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            // Re-throw with more context
            throw new Exception('SMTP Error: ' . $e->getMessage() . ' | PHPMailer ErrorInfo: ' . $mail->ErrorInfo);
        }
	}

	protected function sendSendGridMail(){
		$sendgridMail = new Mail();
	    $sendgridMail->setFrom($this->getEmailFrom()['email'], $this->getEmailFrom()['name']);
	    $sendgridMail->setSubject($this->subject);
	    $sendgridMail->addTo($this->email, $this->receiverName);
	    $sendgridMail->addContent("text/html", $this->finalMessage);
	    $sendgrid = new SendGrid(gs('mail_config')->appkey);
	    $response = $sendgrid->send($sendgridMail);
	    if($response->statusCode() != 202){
	    	throw new Exception(json_decode($response->body())->errors[0]->message);

	    }
	}

	protected function sendMailjetMail()
	{
	    $mj = new Client(gs('mail_config')->public_key, gs('mail_config')->secret_key, true, ['version' => 'v3.1']);
	    $body = [
	        'Messages' => [
	            [
	                'From' => [
	                    'Email' => $this->getEmailFrom()['email'],
	                    'Name' => $this->getEmailFrom()['name'],
	                ],
	                'To' => [
	                    [
	                        'Email' => $this->email,
	                        'Name' => $this->receiverName,
	                    ]
	                ],
	                'Subject' => $this->subject,
	                'TextPart' => "",
	                'HTMLPart' => $this->finalMessage,
	            ]
	        ]
	    ];
	    $response = $mj->post(Resources::$Email, ['body' => $body]);
	}

    /**
    * Configure some properties
    *
    * @return void
    */
	public function prevConfiguration(){
		if ($this->user) {
			$this->email = $this->user->email;
			$this->receiverName = $this->user->fullname;
		}
		$this->toAddress = $this->email;
	}

    private function getEmailFrom(){
        $this->sentFrom = ($this->template && isset($this->template->email_sent_from_address)) 
            ? $this->template->email_sent_from_address 
            : gs('email_from');
        
        $fromName = ($this->template && isset($this->template->email_sent_from_name)) 
            ? $this->template->email_sent_from_name 
            : gs('site_name');
        
        return [
            'email'=>$this->sentFrom,
            'name'=>$this->replaceTemplateShortCode($fromName),
        ];
    }
}
