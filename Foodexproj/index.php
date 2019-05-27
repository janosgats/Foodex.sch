<?php
session_start();

require_once __DIR__ . '/Eszkozok/Eszk.php';
require_once __DIR__ . '/Eszkozok/LoginValidator.php';
require_once __DIR__ . '/Eszkozok/GlobalSettings.php';

if (\Eszkozok\LoginValidator::AccountSignedIn_NOEXIT())
{
    Eszkozok\Eszk::RedirectUnderRoot('profil');
}

//date_default_timezone_set('Europe/Budapest');
//ini_set('date.timezone', 'Europe/Budapest');
//$d = new DateTime();
//echo $d->format('Y-m-d H:i:s');
//require_once 'Eszkozok/MonologHelper.php';
//$l = new MonologHelper('test1');
//$l->debug('test message 1');
//
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-137789203-1');
    </script>

    <meta charset="UTF-8">
    <title>Foodexet a népnek!</title>

    <link rel="stylesheet" href="index.css">
    <link rel="icon" href="res/kepek/favicon1_64p.png">
</head>

<body>

<script>
    function startgp()
    {
        window.open('https://www.pornhub.com/gayporn','_blank')
    }
</script>

<div class="outer">
    <div class="middle">
        <div class="inner">


            <!--<h1 style=";text-align: center;color: #999999; font-size: 50px; padding-bottom: 0; margin-bottom: 0;letter-spacing: -4px;">-->
            <!--    Food<p-->
            <!--        style="display: inline;color: #f4511e;margin-left: -3px">Ex</p>-->
            <!--</h1>-->

            <div style="horiz-align: center; text-align: center;margin-left:20px" onclick="startgp();">

                <img class="fxlogo" src="res/kepek/favicon1.svg">

            </div>
            <div style="horiz-align: center; text-align: center;">


                <a href="https://www.youtube.com/watch?v=FyYF7-W0AyQ" target="_blank"
                   style="text-decoration:none; display: inline-block">
                    <p style="color: #999999;font-size: larger; font-style: italic; padding-top: 0;margin-top: 0">Mi
                        vagyunk a
                        rock,
                        mi vagyunk az
                        <span style="display: inline;color: #f4511e">étel</span>!</p>
                </a>
            </div>


            <form action="login.php" method="post">
                <input type="input" name="muvelet" value="startlogin" hidden>

                <div style="horiz-align: center; text-align: center; padding-top: 5vh">

                    <button class="button" type="submit" style="vertical-align:middle; horiz-align: center">
                        <span>Belépés </span>
                    </button>
<p style="color: #BBBBBB; font-family: 'Arial';">A belépéssel beleegyezel, hogy a GDPR minden egyes rendeletét teljes mértékben telibe szarod (ezen a weblapon).</p>
                </div>

            </form>
        </div>
    </div>
</div>
</body>

</html>