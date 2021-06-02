<?php

namespace Core\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mail {
    private static $mailer;

    public static function send($to, $subject, $message)
    {
        try {
            $config = Config::load('smtp_mail');
            if (self::$mailer == null) {
                self::$mailer = new PHPMailer(true);
                self::$mailer->isSMTP();
                self::$mailer->CharSet = "UTF-8";
                self::$mailer->SMTPSecure = 'ssl';
                //Server settings
                self::$mailer->SMTPDebug = SMTP::DEBUG_OFF;
                self::$mailer->isSMTP();
                self::$mailer->Host = $config['host'];
                self::$mailer->SMTPAuth = true;
                self::$mailer->Username = $config['username'];
                self::$mailer->Password = $config['password'];
                self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                self::$mailer->Port = 465;
            }

            //Recipients
            self::$mailer->setFrom($config['support_mail'], $config['support_name']);
            self::$mailer->addAddress($to);
            self::$mailer->addReplyTo($config['support_mail'], $config['support_name']);

            // Content
            self::$mailer->isHTML(true);
            self::$mailer->Subject = $subject;
            self::$mailer->Body = $message;

            self::$mailer->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}