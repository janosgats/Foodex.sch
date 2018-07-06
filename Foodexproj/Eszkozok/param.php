<?php

function GetURLParam($parameterneve)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        return $_POST[$parameterneve];
    }
    else
    {
        return $_GET[$parameterneve];
    }
}

function IsURLParamSet($parameterneve)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        return isset($_POST[$parameterneve]);
    }
    else
    {
        return isset($_GET[$parameterneve]);
    }
}

function SetURLParam($parameterneve, $ertek)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $_POST[$parameterneve] = $ertek;
    }
    else
    {
        $_GET[$parameterneve] = $ertek;
    }
}