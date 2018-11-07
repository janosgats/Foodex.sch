<?php

function QuitHost($message)
{
    if (ob_get_length())
        ob_clean();
    die($message);
}