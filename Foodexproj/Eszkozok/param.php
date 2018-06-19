<?php

function GetParam($parameterneve)
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

function IsParamSet($parameterneve)
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