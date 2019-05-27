<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();


\Eszkozok\Eszk::Export_Database();