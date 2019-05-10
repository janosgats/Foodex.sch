<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../profil/Profil.php';
require_once __DIR__ . '/../Eszkozok/ProfilInfo.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getAdminJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');


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
    <title>Fx - Globális Beállítások</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>



    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js'></script>
    <script
        src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js'></script>
    <script

    <script src='../3rdparty/jquery.bootstrap-touchspin.js'></script>

</head>

<body style="background-color: #de520d">


<script>
    function ShowMentve()
    {
        $("#mentvediv").fadeIn(700);
    }
    function HideMentve()
    {
        $("#mentvediv").fadeOut(1000);
    }
    function ShowHiba()
    {
        $("#hibadiv").fadeIn(700);
    }
    function HideHiba()
    {
        $("#hibadiv").fadeOut(1000);
    }


    function StartShowMentve()
    {
        ShowMentve();
        setTimeout(HideMentve, 3000);
    }
    function StartShowHiba()
    {
        ShowHiba();
        setTimeout(HideHiba, 6000);
    }

</script>


<div id="mentvediv" style="position: fixed; z-index: 10; width: 100%; text-align: center; background-color: white; opacity: 0.9;" onclick="HideMentve();" hidden>
    <h1 style="color: greenyellow;">Mentve <i class="fa fa-check"></i></h1>
</div>

<div id="hibadiv" style="position: fixed; z-index: 10; width: 100%; text-align: center; background-color: white; opacity: 0.9;" onclick="HideHiba();" hidden>
    <h1 style="color: red;">Hiba <i class="fa fa-times"></i></h1>
</div>


<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'settings');
    ?>


    <div class="container">
        <div class="row">

            <div class="col-md-3">
                <div class="list-group" id="sidebar">
                    <a href="#pontozasiidoszak" class="list-group-item">Pontozási időszak</a>
                    <a href="#timezone" class="list-group-item">Time zone defaults</a>
                    <a href="#jeldelay" class="list-group-item">Jelentkezés delay</a>
                    <a href="#adatbazis" class="list-group-item">SAndor gyermeke</a>
                    <a href="#devlogin" class="list-group-item">Fejlesztői bejelentkezés</a>
                </div>
            </div>

            <div class="col-md-9">

                <div id="pontozasiidoszak">


                    <?php

                    \Eszkozok\Eszk::GetGlobalSettings(["pontozasi_idoszak_kezdete", "pontozasi_idoszak_vege"]);

                    ?>

                    <h2>Pontozási Időszak</h2>

                    <p>A pontok, műszakok és kompenzációk kezelése erre az időszakra vonatkozóan történik.</p>


                    <div class="row">
                        <div class="form-group col-md-6 col-sm-12">
                            <label for="pontidokezd">Kezdet</label>

                            <div class="input-group date">
                                <input type="text" class="form-control" id="pontidokezd" name="pontidokezd"
                                       placeholder="YYYY/MM/DD HH:mm" value="<?php echo $GLOBALS["pontozasi_idoszak_kezdete"]; ?>"/>
                        <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-12">
                            <label for="pontidoveg">Vég</label>

                            <div class="input-group date">
                                <input class="form-control" id="pontidoveg" name="pontidoveg" placeholder="YYYY/MM/DD HH:mm" value="<?php echo $GLOBALS["pontozasi_idoszak_vege"]; ?>"
                                       type="text"/>

                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>


                    <button class="btn custbtn" contenteditable="false" onclick="submitPontIdoszak('pontidoszak')">Időszak Módosítása</button>


                    <hr class="col-md-12">
                </div>

                <div id="timezone">
                    <h2>Time Zone Defaults</h2>

                    <p>All of the settings for time zone and daylight savings.
                    </p>
                    <hr class="col-md-12">
                </div>
                <div id="jeldelay">
                    <h2>Jelentkezés delay</h2>

                    <p>Állítsd be, hogy milyen pontszám felett mennyi idővel a műszak kiírása után jelentkezhet rá egy tag!</p>

                    <table class="table" style="background-color: white">
                        <thead>
                        <tr >
                            <th scope="col">Minimum pont</th>
                            <th scope="col">Kivárás (perc)</th>
                            <th scope="col">Törlés</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr >
                            <td>1</td>
                            <td>Fsdf</td>
                            <td>Otto</td>
                        </tr>
                        <tr >
                            <td>
                                <input id="pont" type="text" value="25" name="pont">
                                <script>
                                    $("input[name='pont']").TouchSpin({
                                        min: 0,
                                        max: 999,
                                        step: 0.5,
                                        decimals: 1,
                                        boostat: 5,
                                        maxboostedstep: 10
                                    });
                                    $("input[name='pont']").on.
                                </script>
                            </td>
                            <td >
                                <input id="perc" type="text" value="25" name="perc">
                                <script>
                                    $("input[name='perc']").TouchSpin({
                                        min: 0,
                                        max: 999,
                                        step: 0.5,
                                        decimals: 1,
                                        boostat: 5,
                                        maxboostedstep: 10
                                    });
                                </script>
                            </td>
                            <td><i class="fas fa-trash-alt fa-2x szabalytorles"></i></td>
                        </tr>
                        <tr class="ujszabalyrow">
                            <td><p style="font-size: large; padding: 0;margin: 0">Új szabály hozzáadása</p></td>
                            <td> <i class="fa fa-plus fa-2x"></i></td>
                            <td> <i class="fa fa-plus fa-2x"></i></td>
                        </tr>
                        </tbody>
                    </table>

                    <hr class="col-md-12">
                </div>
                <div id="adatbazis">
                    <h2>Adatbázis</h2>

                    <p>Alapvető adatbázis kezelés.</p>

                    <a href="<?php echo \Eszkozok\Eszk::GetRootURL() . 'adminer' ?>" target="_blank" style="color: inherit;text-decoration: none;">
                        <button class="btn custbtn" contenteditable="false">Adminer Megnyitása</button>
                    </a>
                    <br>
                    <a href="<?php echo \Eszkozok\Eszk::GetRootURL() . 'Eszkozok/export_db.php' ?>" target="_blank" style="color: inherit;text-decoration: none;">
                        <button class="btn custbtn" contenteditable="false">Teljes Adatbázis Exportálása</button>
                    </a>

                    <hr class="col-md-12">
                </div>

                <div id="devlogin">
                    <h2>Fejlesztői Bejelentkezés</h2>

                    <p>Bejelentkezés AuthSCH nélkül, választott accounttal.</p>
                    <a href="<?php echo \Eszkozok\Eszk::GetRootURL() . 'devlogin' ?>" target="_blank" style="color: inherit;text-decoration: none;">
                        <button class="btn custbtn" contenteditable="false">Devlogin Megnyitása</button>
                    </a>
                </div>

            </div>
            <div class="span9"></div>
        </div>
    </div>


    <script>
        function escapeHtml(unsafe)
        {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function HandlePHPPageData(ret)
        {
            if (ret == "siker4567")
            {
                StartShowMentve();
            }
            else
            {
                alert(escapeHtml(ret));
                StartShowHiba();
            }
        }

        function callPHPPage(postdata)
        {
            $.post('SetAJAX.php', postdata, HandlePHPPageData).fail(
                function ()
                {
                    alert("Error at AJAX call!");
                });
        }

        function submitPontIdoszak(beallID)
        {
            callPHPPage({
                beallID: beallID,
                pontidokezd: document.getElementById("pontidokezd").value,
                pontidoveg: document.getElementById("pontidoveg").value
            });
        }

        $(document).ready(function ()
        {
            var bindDatetimePicker = function (id)
            {
                var date_input = $('input[name=' + id + ']'); //our date input has the name "pontidokezd"
                var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";

                date_input.datetimepicker({
                    format: 'YYYY-MM-DD HH:mm',
                    container: container,
                    todayHighlight: true,
                    autoclose: true,
                    sideBySide: true,
                    showTodayButton: true,
                    locale: 'hu'
                });
            };

            bindDatetimePicker("pontidokezd");
            bindDatetimePicker("pontidoveg");
        });
    </script>



<!--    <script>-->
<!---->
<!--        var PageIsDirty = false;-->
<!---->
<!--        window.addEventListener("beforeunload", function (e) {-->
<!--            if(PageIsDirty)-->
<!--            {-->
<!--                var confirmationMessage = 'Nem mentett változtatások vannak az oldalon.'-->
<!--                    + 'Biztosan kilép?';-->
<!---->
<!--                (e || window.event).returnValue = confirmationMessage; //Gecko + IE-->
<!--                return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.-->
<!--            }-->
<!--            else-->
<!--            {-->
<!--                return;-->
<!--            }-->
<!--        });-->
<!--    </script>-->

</body>
</html>