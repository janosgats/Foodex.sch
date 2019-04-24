<?php
require_once '3rdparty/securimage/securimage.php';

// Code Validation

$image = new Securimage();
if (isset($_POST['captcha_code']) && $image->check($_POST['captcha_code']) == true) {
    echo "Correct!";
} else {
    echo "Sorry, wrong code.";
}