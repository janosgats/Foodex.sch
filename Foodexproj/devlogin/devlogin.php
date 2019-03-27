<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/ini.php';

//die(sha1(GetURLParam('password')));

if (\Eszkozok\GlobalServerInitParams::$DevloginEnabled)
{

    if (IsURLParamSet('int_id') && IsURLParamSet('password'))
    {
        if (sha1(GetURLParam('password')) == \Eszkozok\GlobalServerInitParams::$DevloginPasswordHashed)
        {
            $_SESSION['profilint_id'] = GetURLParam('int_id');
            \Eszkozok\Eszk::RedirectUnderRoot('profil');
        }
    }
}