<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../Eszkozok/entitas/Profil.php';
require_once __DIR__ . '/../Eszkozok/entitas/ProfilInfo.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';
require_once __DIR__ . '/../Eszkozok/GlobalSettings.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();


?>

<!DOCTYPE html>
<html>

<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag()
        {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-137789203-1');
    </script>

    <meta charset="UTF-8">
    <title>Fx - Globális Beállítások</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>


    <!--    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>-->
    <script src='../node_modules/jquery/dist/jquery.js'></script>
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


<div class="container" style="min-width: 420px">

    <?php
    NavBar::echonavbar('settings');
    ?>


    <div class="container">
        <div class="row">

            <div class="col-md-3">
                <div class="list-group" id="sidebar">
                    <a href="#pontozasiidoszak" class="list-group-item">Pontozási időszak</a>
                    <a href="#timezone" class="list-group-item">Time zone defaults</a>
                    <a href="#masodikmuszak" class="list-group-item">Második műszak</a>
                    <a href="#jeldelay" class="list-group-item">Jelentkezés delay</a>
                    <a href="#adatbazis" class="list-group-item">SAndor gyermeke</a>
                    <a href="#autoemailteszt" class="list-group-item">Auto e-mail teszt</a>
                    <a href="#devlogin" class="list-group-item">Fejlesztői bejelentkezés</a>
                </div>
            </div>

            <div class="col-md-9">

                <div id="pontozasiidoszak">

                    <h2>Pontozási Időszak</h2>

                    <p>A pontok, műszakok és kompenzációk kezelése erre az időszakra vonatkozóan történik.</p>


                    <div class="row">
                        <div class="form-group col-md-6 col-sm-12">
                            <label for="pontidokezd">Kezdet</label>

                            <div class="input-group date">
                                <input type="text" class="form-control" id="pontidokezd" name="pontidokezd"
                                       placeholder="YYYY/MM/DD HH:mm" value="<?php echo \Eszkozok\GlobalSettings::GetSetting("pontozasi_idoszak_kezdete"); ?>"/>
                        <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-12">
                            <label for="pontidoveg">Vég</label>

                            <div class="input-group date">
                                <input class="form-control" id="pontidoveg" name="pontidoveg" placeholder="YYYY/MM/DD HH:mm" value="<?php echo \Eszkozok\GlobalSettings::GetSetting("pontozasi_idoszak_vege"); ?>"
                                       type="text"/>

                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>


                    <button class="btn custbtn" contenteditable="false" onclick="submitPontIdoszak()">Időszak Módosítása</button>


                    <hr class="col-md-12">
                </div>

                <div id="timezone">
                    <h2>Time Zone Defaults</h2>

                    <p>All of the settings for time zone and daylight savings.
                    </p>
                    <hr class="col-md-12">
                </div>

                <div id="masodikmuszak">
                    <h2>Második Műszak</h2>

                    <p>Állítsd be, hogy legfeljebb mennyi idővel a műszak kezdete előtt vehet fel új műszakot egy tag, ha aktuálisan már van egy aktív jelentkezése!</p>


                    <div class="row">
                        <div class="form-group col-md-6 col-sm-12">
                            <input id="masmuszjelido" type="text" value="<?php echo(\Eszkozok\GlobalSettings::GetSetting("mas_muszakra_ennyivel_elotte_jelentkezhet") / (60 * 60)); ?>" name="masmuszjelido">
                            <script>
                                $("input[name='masmuszjelido']").TouchSpin({
                                    min: 0,
                                    max: 99999999,
                                    step: 0.1,
                                    decimals: 1,
                                    boostat: 5,
                                    maxboostedstep: 10,
                                    postfix: 'óra'
                                });
                                $("input[name='masmuszjelido']").on('change', function ()
                                {
                                    submitMasMuszJelIdo();
                                });
                            </script>
                        </div>
                    </div>
                    <hr class="col-md-12">
                </div>

                <div id="jeldelay">
                    <h2>Jelentkezés Delay</h2>

                    <p>Állítsd be, hogy milyen pontszámtól leghamarabb mennyi idővel a műszak aktiválása után jelentkezhet a műszakra egy tag!</p>

                    <table class="table" name="jeldelaytable" id="jeldelaytable" style="background-color: transparent; min-width: 360px">
                        <thead>
                        <tr>
                            <th scope="col">Minimum pont</th>
                            <th scope="col">Kivárás (perc)</th>
                            <th scope="col">Törlés</th>
                        </tr>
                        </thead>
                        <!--                        <tbody>-->
                        <!---->
                        <!--                        <tr>-->
                        <!--                            <td>1</td>-->
                        <!--                            <td>Fsdf</td>-->
                        <!--                            <td>Otto</td>-->
                        <!--                        </tr>-->
                        <!--                        <tr>-->
                        <!--                            <td>-->
                        <!--                                <input id="jeldelpont" type="text" value="25" name="jeldelpont">-->
                        <!--                                <script>-->
                        <!--                                    $("input[name='jeldelpont']").TouchSpin({-->
                        <!--                                        min: 0,-->
                        <!--                                        max: 999,-->
                        <!--                                        step: 0.5,-->
                        <!--                                        decimals: 1,-->
                        <!--                                        boostat: 5,-->
                        <!--                                        maxboostedstep: 10-->
                        <!--                                    });-->
                        <!--                                </script>-->
                        <!--                            </td>-->
                        <!--                            <td>-->
                        <!--                                <input id="jeldelperc" type="text" value="25" name="jeldelperc">-->
                        <!--                                <script>-->
                        <!--                                    $("input[name='jeldelperc']").TouchSpin({-->
                        <!--                                        min: 0,-->
                        <!--                                        max: 999,-->
                        <!--                                        step: 0.5,-->
                        <!--                                        decimals: 1,-->
                        <!--                                        boostat: 5,-->
                        <!--                                        maxboostedstep: 10-->
                        <!--                                    });-->
                        <!--                                </script>-->
                        <!--                            </td>-->
                        <!--                            <td><i class="fas fa-trash-alt fa-2x szabalytorles"></i></td>-->
                        <!--                        </tr>-->
                        <!--                        <tr class="ujszabalyrow">-->
                        <!--                            <td><p style="font-size: large; padding: 0;margin: 0">Új szabály hozzáadása</p></td>-->
                        <!--                            <td><i class="fa fa-plus fa-2x"></i></td>-->
                        <!--                            <td><i class="fa fa-plus fa-2x"></i></td>-->
                        <!--                        </tr>-->
                        <!--                        </tbody>-->
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

                <div id="autoemailteszt">
                    <h2>Auto E-mail Küldés</h2>

                    <button class="btn custbtn" contenteditable="false" onclick="doAutoEmailTeszt()">Küldj nekem (<?php echo htmlspecialchars(\Eszkozok\Eszk::GetBejelentkezettProfilAdat()->getEmail()); ?>) egy e-mailt!</button>
                </div>
                <hr class="col-md-12">
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

        var JelDelayQueryInProgress = false;
        var JelDelayRowIDToAnimate = null;
        var ElsoJelDelayLekeres = true;
        function HandlePHPPageDataJelDelay(ret)
        {
            JelDelayQueryInProgress = false;
            if (ret == "")
            {
                alert("#1 Hiba a jelentkezési delayek lekérésekor. Próbáld frissíteni az oldalt!");
                StartShowHiba();
            }
            else
            {
                var delaytimes = JSON.parse(ret);

                if (typeof delaytimes['error'] === 'undefined')
                {
                    if (delaytimes['new_inserted_row_id'] !== 'undefined' && delaytimes['new_inserted_row_id'] != null)
                    {
                        JelDelayRowIDToAnimate = 'jeldelrow' + escapeHtml(delaytimes['new_inserted_row_id'].toString());

                        //////////Ez azért, hogy az Object()-ből Array()-t csináljon és működjön rajta a forEach().
                        delete delaytimes['new_inserted_row_id'];

                        console.log(delaytimes);
                        var arr = [];

                        var i = 0;
                        for (var key in delaytimes)
                        {
                            if (delaytimes.hasOwnProperty(key))
                                arr[i] = delaytimes[key];
                            ++i;
                        }

                        delaytimes = arr;
                        /////////////////////////////////////////////////////////////
                    }
                    console.log(delaytimes);

                    var thead = jQuery.parseHTML('<thead>' +
                        '<tr >' +
                        '<th scope="col" style="text-align: center">Alsó ponthatár</th>' +
                        '<th scope="col" style="text-align: center">Kivárás (perc)</th>' +
                        '<th scope="col" style="text-align: center">Törlés</th>' +
                        '</tr>' +
                        '</thead>')[0];

                    var tbody = jQuery.parseHTML('<tbody></tbody>')[0];

                    delaytimes.forEach(function (delaytimesrow)
                    {

                        var tr = jQuery.parseHTML('<tr></tr>')[0];
                        tr.id = 'jeldelrow' + escapeHtml(delaytimesrow['id'].toString());

                        var td_minpont = jQuery.parseHTML('<td></td>')[0];
                        var td_delay = jQuery.parseHTML('<td></td>')[0];
                        var td_delete = jQuery.parseHTML('<td style="text-align: center"></td>')[0];


                        var num_minpont = jQuery.parseHTML('<input type="text">')[0];
                        num_minpont.id = 'jeldelpont' + escapeHtml(delaytimesrow['id'].toString());
                        num_minpont.name = 'jeldelpont' + escapeHtml(delaytimesrow['id'].toString());
                        num_minpont.value = escapeHtml(delaytimesrow['minpont'].toString());


                        var num_delay = jQuery.parseHTML('<input type="text">')[0];
                        num_delay.id = 'jeldeldelay' + escapeHtml(delaytimesrow['id'].toString());
                        num_delay.name = 'jeldeldelay' + escapeHtml(delaytimesrow['id'].toString());
                        num_delay.value = escapeHtml((delaytimesrow['delay'] / 60).toString());


                        function SubAktModositas()
                        {
//                            if(JelDelayQueryInProgress)
//                                return;
                            JelDelayRowIDToAnimate = tr.id;
                            submitJelDelayModosit(delaytimesrow['id'], num_minpont.value, num_delay.value * 60);
                        }

                        $(num_minpont).on('touchspin.on.stopspin', SubAktModositas);
                        $(num_delay).on('touchspin.on.stopspin', SubAktModositas);
                        num_minpont.addEventListener('blur', SubAktModositas, false);
                        num_delay.addEventListener('blur', SubAktModositas, false);
                        num_minpont.addEventListener("keyup", function (event)
                        {
                            if (event.keyCode === 13)//Enter
                                SubAktModositas();
                        });
                        num_delay.addEventListener("keyup", function (event)
                        {
                            if (event.keyCode === 13)//Enter
                                SubAktModositas();
                        });


                        var i_delete = jQuery.parseHTML('<i class="fas fa-trash-alt fa-2x szabalytorles"></i>')[0];
                        i_delete.onclick = function ()
                        {
//                            if(JelDelayQueryInProgress)
//                                return;
                            tr.style.display = "none";
                            submitJelDelayTorol(delaytimesrow['id']);
                        };


                        td_minpont.appendChild(num_minpont);
                        td_delay.appendChild(num_delay);
                        td_delete.appendChild(i_delete);

                        tr.appendChild(td_minpont);
                        tr.appendChild(td_delay);
                        tr.appendChild(td_delete);

                        tbody.appendChild(tr);

                        $(num_minpont).TouchSpin({
                            min: 0,
                            max: 99999999,
                            step: 0.1,
                            decimals: 1,
                            boostat: 5,
                            maxboostedstep: 10
                        });
                        $(num_delay).TouchSpin({
                            min: 0,
                            max: 99999999,
                            step: 1,
                            decimals: 0,
                            boostat: 5,
                            maxboostedstep: 10
                        });
                    });

                    var taddnewitemrow = jQuery.parseHTML('<tr class="ujszabalyrow">' +
                        '<td style="text-align: center"><p style="font-size: x-large; text-decoration: underline">Új szabály hozzáadása</p></td>' +
                        '<td style="text-align: center"> <i class="fa fa-plus fa-2x"></i></td>' +
                        '<td style="text-align: center"> <i class="fa fa-plus fa-2x"></i></td>' +
                        '</tr>')[0];
                    taddnewitemrow.onclick = submitJelDelayHozzaad;

                    tbody.appendChild(taddnewitemrow);

                    var table = document.getElementById('jeldelaytable');
                    table.innerHTML = '';

                    table.appendChild(thead);
                    table.appendChild(tbody);

                    if (JelDelayRowIDToAnimate != null)
                    {
                        document.getElementById(JelDelayRowIDToAnimate).style.backgroundColor = '#FFFFFF77';
                        document.getElementById(JelDelayRowIDToAnimate).firstChild.focus();
                    }

                    if (!ElsoJelDelayLekeres)
                        StartShowMentve();
                    ElsoJelDelayLekeres = false;
                }
                else
                {
                    alert("#2 Hiba a jelentkezési delayek lekérésekor: " + delaytimes['error'] + " Próbáld frissíteni az oldalt!");
                    StartShowHiba();
                }

            }

        }


        function callPHPPageJelDelay(postdata)
        {
//            if(JelDelayQueryInProgress)
//            return;

            JelDelayQueryInProgress = true;
            $.post('SetandGetJelDelayAJAX.php', postdata, HandlePHPPageDataJelDelay).fail(
                function ()
                {
                    JelDelayQueryInProgress = false;
                    alert("Error at AJAX call! A Jelentkezési delayek hibásan jelenhetnek meg. Próbáld frissíteni az oldalt!");
                    StartShowHiba();
                });
        }

        function submitJelDelayLekeres()
        {
            callPHPPageJelDelay({
                muvelet: 'lekeres',
                ajaxuse: 1
            });
        }
        function submitJelDelayHozzaad()
        {
            callPHPPageJelDelay({
                muvelet: 'hozzaadas',
                ajaxuse: 1
            });
        }
        function submitJelDelayTorol(id)
        {
            callPHPPageJelDelay({
                muvelet: 'torles',
                id: id,
                ajaxuse: 1
            });
        }
        function submitJelDelayModosit(id, minpont, delay)
        {
            callPHPPageJelDelay({
                muvelet: 'modositas',
                id: id,
                minpont: minpont,
                delay: delay,
                ajaxuse: 1
            });
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
                    StartShowHiba();
                });
        }

        function doAutoEmailTeszt()
        {
            $.post('sendtestmail.php', {},
                function (ret)
                {
                    if (ret == "siker4567")
                    {
                        alert('Az e-mail elméletileg sikeresen el lett küldve.\nEllenőrizd a postaládádban!')
                    }
                    else
                    {
                        alert(escapeHtml(ret))
                    }
                }).fail(
                function ()
                {
                    alert("Error at AJAX call!");
                });
        }

        function submitPontIdoszak()
        {
            callPHPPage({
                beallID: 'pontidoszak',
                pontidokezd: document.getElementById("pontidokezd").value,
                pontidoveg: document.getElementById("pontidoveg").value
            });
        }
        function submitMasMuszJelIdo()
        {
            callPHPPage({
                beallID: 'masmuszjelido',
                ido: (document.getElementById("masmuszjelido").value * 60 * 60)
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

    <script>
        $(document).ready(function ()
        {
            submitJelDelayLekeres();
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