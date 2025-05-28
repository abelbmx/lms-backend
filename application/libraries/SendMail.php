<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require 'PHPMailer/PHPMailerAutoload.php';

class SendMail
{

    public $mail;

    public function __construct()
    {
		$this->mail = new PHPMailer;
		$this->mail->isSMTP();
		 $this->mail->SMTPDebug = 2;
		$this->mail->Debugoutput = 'html';
        $this->mail->isHTML(true);
		$this->mail->Host = 'mail.surlit.cl';
		$this->mail->Port = 587;
		$this->mail->SMTPSecure = 'tls';
		$this->mail->SMTPAuth = true;
		$this->mail->Username = "notificaciones@surlit.cl";
		$this->mail->Password = "notificaciones2021**";
		$this->mail->CharSet = 'UTF-8';
    }

    public function sendTo($toEmail, $recipientName, $subject, $msg)
    {
        $this->mail->setFrom('notificaciones@surlit.cl', 'Surlit Santa Augusta');
        $this->mail->addAddress($toEmail, $recipientName);
        //$this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $msg;
        if (!$this->mail->send()) {
            log_message('error', 'Mailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function sendToContacto($toEmail, $subject, $msg)
    {
        $this->mail->setFrom('notificaciones@surlit.cl', 'Surlit Santa Augusta');
        $this->mail->addAddress('contacto@surlit.cl', 'Surlit Santa Augusta');

        //$this->mail->addCC($toEmail);
        $this->mail->addCC('abelonxo@gmail.com');
        //$this->mail->addBCC('abelonxo@gmail.com');
        //$this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $msg;
        if (!$this->mail->send()) {
            log_message('error', 'Mailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
        $this->mail->ClearAddresses();
        return true;
    }

}
