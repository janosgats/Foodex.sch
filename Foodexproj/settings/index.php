<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../profil/Profil.php';
require_once __DIR__ . '/../Eszkozok/ProfilInfo.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body style="background-color: #de520d">

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'settings')
    ?>



    <div class="container">
        <div class="row">

            <div class="col-md-3">
                <div class="list-group" id="sidebar">
                    <a href="#pontozasiidoszak" class="list-group-item">Pontozási Időszak</a>
                    <a href="#" class="list-group-item">Time Zone Defaults</a>
                    <a href="#adatbazis" class="list-group-item">SAndor Gyermeke</a>
                    <a href="#devlogin" class="list-group-item">Fejlesztői Bejelentkezés</a>
                </div>
            </div>

            <div class="col-md-9">

                <div id="pontozasiidoszak">
                    <h2>Pontozási Időszak</h2>

                    <p>All of the information for organizing data into projects, and groups.</p>
                    <hr class="col-md-12">
                </div>

                <div>
                    <h2>Time Zone Defaults</h2>

                    <p>All of the settings for time zone and daylight savings.
                    </p>
                    <hr c
                        lass="col-md-12">
                </div>
                <div id="adatbazis">
                    <h2>Adatbázis</h2>

                    <p>Alapvető adatbázis kezelés.</p>

                    <a href = "<?php echo \Eszkozok\Eszk::GetRootURL() . '/adminer'?>" target= "_blank" style="color: inherit;text-decoration: none;"><button class="btn custbtn" contenteditable="false">Adminer Megnyitása</button></a>
                    <br>
                    <a href = "<?php echo \Eszkozok\Eszk::GetRootURL() . '/Eszkozok/export_db.php'?>" target= "_blank" style="color: inherit;text-decoration: none;"><button class="btn custbtn" contenteditable="false">Teljes Adatbázis Exportálása</button></a>

                    <hr class="col-md-12">
                </div>

                <div id="devlogin">
                    <h2>Fejlesztői Bejelentkezés</h2>

                    <p>Bejelentkezés AuthSCH nélkül, választott accounttal.</p>
                    <a href = "<?php echo \Eszkozok\Eszk::GetRootURL() . '/devlogin'?>" target= "_blank" style="color: inherit;text-decoration: none;"><button class="btn custbtn" contenteditable="false">Devlogin Megnyitása</button></a>
                </div>

            </div>
            <div class="span9"></div>
        </div>
    </div>

</body>
</html>