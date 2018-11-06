<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/ini.php';

if (\Eszkozok\GlobalServerInitParams::$DevloginEnabled)
{

    if (IsURLParamSet('int_id') && IsURLParamSet('password'))
    {
        if (GetURLParam('password') == \Eszkozok\GlobalServerInitParams::$DevloginPassword)
        {
            $_SESSION['profilint_id'] = GetURLParam('int_id');
            \Eszkozok\Eszk::RedirectUnderRoot('profil');
        }
    }
}