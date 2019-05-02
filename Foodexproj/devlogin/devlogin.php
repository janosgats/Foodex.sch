<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/ini.php';
require_once __DIR__ . '/../foodexpws.php';
require_once __DIR__ . '/../Eszkozok/MonologHelper.php';

//die(sha1(GetURLParam('password')));

if (\Eszkozok\GlobalServerInitParams::$DevloginEnabled)
{

    if (IsURLParamSet('int_id') && IsURLParamSet('password'))
    {
        $int_id = GetURLParam('int_id');
            $password = GetURLParam('password');
        if (sha1($password) == \Eszkozok\FoodexPWs::$DevloginPasswordHashed)
        {
            $_SESSION['profilint_id'] = $int_id;

            $session_token = base64_encode(openssl_random_pseudo_bytes(64));

            $conn = \Eszkozok\Eszk::initMySqliObject();
            $stmt = $conn->prepare("UPDATE `fxaccok` SET `session_token` = ? WHERE `fxaccok`.`internal_id` = ?");
            $stmt->bind_param('ss', $session_token, $int_id);

            if ($stmt->execute())
            {

            }

            $_SESSION['session_token'] = $session_token;

            \Eszkozok\Eszk::RedirectUnderRoot('profil');
        }
        else
        {

            $logger = new \MonologHelper('devlogin/devlogin.php');
            $logger->warning('Sikertelen fejlesztői bejelentkezés kísérlet!', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $int_id, $password]);
        }
    }
}