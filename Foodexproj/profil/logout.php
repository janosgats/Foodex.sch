<?php
session_start();

set_include_path(getcwd());
include_once '../Eszkozok/Eszk.php';


unset($_SESSION['profilint_id']);

Eszkozok\Eszk::RedirectUnderRoot('');
