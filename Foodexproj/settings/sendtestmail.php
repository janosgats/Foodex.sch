<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/entitas/Profil.php';
require_once __DIR__ . '/../Eszkozok/SMTPSender.php';
require_once __DIR__ . '/../Eszkozok/AJAXhost.php';

try{
    \Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

    $LoggedInProfil = \Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if(empty($LoggedInProfil) || empty($LoggedInProfil->getEmail()))
        throw new Exception('Invalid profile!');
    echo $LoggedInProfil->getEmail();

    SMTPSender::sendTestMailTo($LoggedInProfil->getEmail());

    QuitHost('siker4567');
}
catch (\Exception $e)
{
    ob_clean();
    echo 'Hiba: ' . $e->getMessage();
}
