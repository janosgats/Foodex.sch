<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Eszkozok/Eszk.php';

require_once __DIR__ . '/../foodexpws.php';

class SMTPSender
{
    public static function SendVarolistaKeretbeKerultTomb($musznev, $emailcimTomb)
    {
        if (count($emailcimTomb) > 0)
        {

            try
            {
                $musznev = htmlspecialchars($musznev, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');


                date_default_timezone_set('Etc/UTC');

                $mail = new \PHPMailer\PHPMailer\PHPMailer();

                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'ssl://smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = \Eszkozok\FoodexPWs::$SMTPSenderUser;                 // SMTP username
                $mail->Password = \Eszkozok\FoodexPWs::$SMTPSenderPassword;                           // SMTP password
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465;                                    // TCP port to connect to                                  // TCP port to connect to


                //$mail->sign(
                //    $_SERVER['DOCUMENT_ROOT'] . '/certificates/comodo_smime_cert.crt', //The location of your certificate file
                //    $_SERVER['DOCUMENT_ROOT'] . '/certificates/comodo_smime_cert.key', //The location of your private key file
                //    'CtrlAltF5', //The password you protected your private key with (not the Import Password! may be empty but parameter must not be omitted!)
                //    $_SERVER['DOCUMENT_ROOT'] . '/certificates/comodo_smime_certchain.pem' //The location of your chain file
                //);

                $mail->setFrom('foodexsmtp@gmail.com', "=?UTF-8?B?" . base64_encode("Foodex") . "?=");

                foreach ($emailcimTomb as $email)
                {
                    if (\Eszkozok\Eszk::isEmailValid($email))
                    {
                        $mail->addAddress($email);     // Add a recipient
                    }
                }
                $mail->addReplyTo('noreplyfoodex@gmail.com', 'Do not answer this letter!');

                $mail->isHTML(true);                                  // Set email format to HTML


                $nevelo = 'a';
                if (\Eszkozok\Eszk::startsWidthMaganhangzo($musznev))
                    $nevelo = 'az';

                $mail->Subject = "=?UTF-8?B?" . base64_encode('Bekerültél ' . $nevelo . ' ' . $musznev . ' keretbe!') . "?=";


                $mail->Body = 'Hi boi!<br><br>Valaki lejelentkezett, így bekerültél ' . $nevelo . ' ' . $musznev . ' Foodexesek közé.<br><br><a href="http://foodex.sch.bme.hu/jelentkezes">Pillants rá!</a>';

                if (!$mail->send())
                {
                    throw new \Exception('Az e-mail elküldése nem sikerült!');
                }

            }
            catch (\Exception $e)
            {
                \Eszkozok\Eszk::dieToErrorPage('4615: ' . $e->getMessage());
            }
        }
    }
}