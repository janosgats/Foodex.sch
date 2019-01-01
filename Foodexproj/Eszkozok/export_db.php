<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');


\Eszkozok\Eszk::Export_Database();