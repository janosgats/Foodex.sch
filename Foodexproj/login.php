<?php
set_include_path(getcwd());
include_once 'Eszkozok/Eszk.php';
session_start();

//\Eszkozok\Eszk::dieToErrorPage('dieToErrorPage() teszt');
\Eszkozok\Eszk::doAuthSchLogin();