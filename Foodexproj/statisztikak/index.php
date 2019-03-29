<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../profil/Profil.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');



////TODO: megcsinálni a két SQL lekérdezést és belőlük még PHP-ban elkészíteni a data-t a graph számára.
/////======A charton megjelenítendő adat lekérdezése===========================
//SELECT jelentkezo, muszid,  TIMEDIFF(MinJelIdo, `datetime`) as JelIdotartam, TIMESTAMPDIFF(second, `datetime`, MinJelIdo) as JelIdotartamSec FROM
//(
//  select jelentkezo, muszid, min(jelido) as MinJelIdo from fxjelentk
//  group by muszid, jelentkezo
//  ) as MinJel
//JOIN logs
//ON logs.context = CONCAT('[',  MinJel.muszid, ']' )
//ORDER BY muszid ASC;
/////============================================================
//
/////=====A logolt időtartamokkal jelentkező internal_id-k lekérdezése=================
//SELECT jelentkezo FROM fxjelentk
//JOIN logs
//ON logs.context = CONCAT('[',  fxjelentk.muszid, ']' )
//group by fxjelentk.jelentkezo
/////============================================================



?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">



    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>

</head>

<body style="background-color: #de520d">

<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'statisztikak');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Jelentkezési idők</h4>
        </div>
        <div class="panel-body">

        </div>
    </div>
</div>
</body>
</html>