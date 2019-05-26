<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getAdminJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');

$Korok = array();

{
    $conn = Eszkozok\Eszk::initMySqliObject();

    if (!$conn)
        \Eszkozok\Eszk::dieToErrorPage('34254: $conn is false!');

    $stmt = $conn->prepare("SELECT * FROM korok ORDER BY nev ASC;");
    if ($stmt->execute())
    {
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc())
            $Korok[] = $row;
    }
    else
        \Eszkozok\Eszk::dieToErrorPage('34254: $stmt->execute() is false!');
}

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
    <title>Fx - Új műszak kiírása</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js'></script>
    <script
        src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js'></script>

</head>

<body style="background-color: #de520d">
<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'ujmuszak');
    ?>

    <?php
    $MuszakMasolas = false;
    $MasoltMuszak = new Eszkozok\Muszak();
    try
    {
        if (IsURLParamSet('muszmasol') && GetURLParam('muszmasol') == 1 && IsURLParamSet('muszid') && is_numeric(GetURLParam('muszid')))
        {
            $MuszakMasolas = true;
            $MasoltMuszak = Eszkozok\Eszk::GetTaroltMuszakAdat(GetURLParam('muszid'), true);
        }
    }
    catch (\Exception $e)
    {
    }
    ?>

    <div class="jumbotron">
        <form method="get">
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="musznev">Név</label>
                    <input id="musznev" name="musznev" type="text" placeholder="pl. Pizzásch 1"
                           value="<?php if ($MuszakMasolas) echo $MasoltMuszak->musznev; ?>" class="form-control">
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="letszam">Létszám</label>
                    <input id="letszam" name="letszam" type="text" placeholder="pl. 2" value="<?php if ($MuszakMasolas) echo $MasoltMuszak->letszam; ?>" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="idokezd">Kezdet</label>

                    <div class="input-group date">
                        <input type="text" class="form-control" id="idokezd" name="idokezd"
                               placeholder="YYYY/MM/DD HH:mm" value="<?php if ($MuszakMasolas) echo $MasoltMuszak->idokezd; ?>"/>
                        <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    </div>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="idoveg">Vég</label>

                    <div class="input-group date">
                        <input class="form-control" id="idoveg" name="idoveg" placeholder="YYYY/MM/DD HH:mm" value="<?php if ($MuszakMasolas) echo $MasoltMuszak->idoveg; ?>"
                               type="text"/>

                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="pont">Pont</label>
                    <select id="pont" name="pont" class="form-control">
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->pont == 1) echo ' selected="selected" '; ?> >1</option>
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->pont == 2) echo ' selected="selected" '; ?>>2</option>
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->pont == 3) echo ' selected="selected" '; ?>>3</option>
                    </select>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="mospont">Mosogatás pont</label>
                    <select id="mospont" name="pont" class="form-control">
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->mospont == 0) echo ' selected="selected" '; ?>>0</option>
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->mospont == 0.5) echo ' selected="selected" '; ?>>0.5</option>
                        <option <?php if ($MuszakMasolas && $MasoltMuszak->mospont == 1) echo ' selected="selected" '; ?>>1</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="korid">Értékelő kör</label>
                    <select id="korid" name="korid" class="form-control">
                        <option <?php if (!$MuszakMasolas ||  $MasoltMuszak->korID == null) echo ' selected="selected" '; ?> value="NINCS">Nincs</option>


                        <?php
                        foreach($Korok as $kor)
                        {
                            ?>

                            <option <?php if ($MuszakMasolas && $MasoltMuszak->korID == $kor['id']) echo ' selected="selected" '; ?> value="<?= $kor['id'];?>"><?= htmlentities($kor['nev']); ?></option>

                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="megj">Megjegyzés</label>
                    <input id="megj" name="megj" type="text" placeholder="pl. Börgör" value="<?php if ($MuszakMasolas) echo $MasoltMuszak->megj; ?>" class="form-control">
                </div>
            </div>

            <div class="row" style="padding-right: 7%">
                <button class="btn btn-primary pull-right" name="kiiras" onclick="submitMuszak()" type="button">Műszak
                    kiírása
                </button>
            </div>
            <div style="text-align: center;">
                <p style="color: gray">A műszakokat kiírás után aktiválni kell a <a href="../jelentkezes">Jelentkezés</a> menüben!</p>
            </div>
        </form>
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
            alert("A műszakot sikeresen kiírtad!");
        else
            alert(escapeHtml(ret));
    }

    function callPHPPage(postdata)
    {
        $.post('kiir.php', postdata, HandlePHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }

    function submitMuszak()
    {
        callPHPPage({
            musznev: document.getElementById("musznev").value,
            idokezd: document.getElementById("idokezd").value,
            idoveg: document.getElementById("idoveg").value,
            letszam: document.getElementById("letszam").value,
            pont: document.getElementById("pont").value,
            mospont: document.getElementById("mospont").value,
            korid: document.getElementById("korid").value,
            megj: document.getElementById("megj").value
        });
    }

    $(document).ready(function ()
    {
        var bindDatetimePicker = function (id)
        {
            var date_input = $('input[name=' + id + ']'); //our date input has the name "idokezd"
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

        bindDatetimePicker("idokezd");
        bindDatetimePicker("idoveg");
    });
</script>


</body>
</html>