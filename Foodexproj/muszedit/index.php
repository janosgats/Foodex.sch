<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once '../Eszkozok/entitas/Muszak.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

if (IsURLParamSet('muszid') == false)
    Eszkozok\Eszk::dieToErrorPage('19975: muszid URL param is not set!');
$muszidbuff = GetURLParam('muszid');

if ($muszidbuff == '')
    Eszkozok\Eszk::dieToErrorPage('19985: muszid URL param is empty!');


$SzerkMuszak = Eszkozok\Eszk::GetTaroltMuszakAdat($muszidbuff, true);

$Korok = array();

{
    $conn = Eszkozok\Eszk::initMySqliObject();

    if (!$conn)
        \Eszkozok\Eszk::dieToErrorPage('34254: $conn is false!');

    $stmt = $conn->prepare("SELECT * FROM korok ORDER BY nev ASC;");
    if ($stmt->execute())
    {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc())
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
    <title>Fx - Műszak szerkesztése</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>
</head>

<body style="background-color: #de520d">
<div class="container">

    <?php
    NavBar::echonavbar( '')
    ?>

    <div class="jumbotron" style="padding-top:10px">
        <form method="get">
            <div style="width: 100%; text-align: center">
                <h1 style="color: darkgray; font-family: 'Arial'; font-size: xx-large">Műszak szerkesztése</h1>
                <br><br>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="musznev">Név</label>
                    <input id="musznev" name="musznev" type="text" placeholder="pl. Pizzásch 1"
                           value="<?php echo $SzerkMuszak->musznev ?>" class="form-control">
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="letszam">Létszám</label>
                    <input id="letszam" name="letszam" type="text" placeholder="pl. 2"
                           value="<?php echo $SzerkMuszak->letszam ?>" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="idokezd">Kezdet</label>

                    <div class="input-group date">
                        <input type="text" class="form-control" id="idokezd" name="idokezd"
                               placeholder="YYYY-MM-DD HH:mm" value='<?php echo $SzerkMuszak->idokezd; ?>'/>
                        <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    </div>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="idoveg">Vég</label>

                    <div class="input-group date">
                        <input class="form-control" id="idoveg" name="idoveg" placeholder="YYYY-MM-DD HH:mm"
                               value="<?php echo $SzerkMuszak->idoveg; ?>"
                               type="text"/>

                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="pont">Pont</label>
                    <select id="pont" name="pont" class="form-control">
                        <option <?php if ($SzerkMuszak->pont == 1) echo ' selected="selected" '; ?> >1</option>
                        <option <?php if ($SzerkMuszak->pont == 2) echo ' selected="selected" '; ?>>2</option>
                        <option <?php if ($SzerkMuszak->pont == 3) echo ' selected="selected" '; ?>>3</option>
                    </select>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="mospont">Mosogatás pont</label>
                    <select id="mospont" name="pont" class="form-control">
                        <option <?php if ($SzerkMuszak->mospont == 0) echo ' selected="selected" '; ?>>0</option>
                        <option <?php if ($SzerkMuszak->mospont == 0.5) echo ' selected="selected" '; ?>>0.5</option>
                        <option <?php if ($SzerkMuszak->mospont == 1) echo ' selected="selected" '; ?>>1</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="korid">Értékelő kör</label>
                    <select id="korid" name="korid" class="form-control">
                        <option <?php if ($SzerkMuszak->korID == null) echo ' selected="selected" '; ?> value="NINCS">Nincs</option>

                        <?php
                        foreach ($Korok as $kor)
                        {
                            ?>

                            <option <?php if ($SzerkMuszak->korID == $kor['id']) echo ' selected="selected" '; ?> value="<?= $kor['id']; ?>"><?= htmlentities($kor['nev']); ?></option>

                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="megj">Megjegyzés</label>
                    <input id="megj" name="megj" type="text" placeholder="pl. Börgör"
                           value="<?php echo $SzerkMuszak->megj; ?>" class="form-control">
                </div>
            </div>

            <div class="row" style="padding-right: 7%">
                <button class="btn btn-primary pull-right" name="mentes" id="mentes" onclick="submitMuszak()" type="button">
                    Mentés
                </button>
                <button class="btn btn-danger pull-right" name="torles" id="torles" style="margin-right: 10px"
                        onclick="deleteMuszak()" type="button">Műszak törlése
                </button>
                <a class="btn btn-success pull-right" name="masolas" id="masolas" style="margin-right: 10px"
                   href="../ujmuszak?<?php echo 'muszmasol=1&muszid=' . urlencode($SzerkMuszak->ID); ?>" type="button">Műszak másolása
                </a>
            </div>
        </form>
    </div>
</div>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js'></script>

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

    function HandlePHPPageDataEdit(ret)
    {
        if (ret == "siker4567")
            alert("A műszakot sikeresen módosítottad!");
        else
            alert(escapeHtml(ret));
    }

    function callPHPPageEdit(postdata)
    {
        $.post('edit.php', postdata, HandlePHPPageDataEdit).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }


    function HandlePHPPageDataTorol(ret)
    {
        if (ret == "siker4567")
        {
            alert("A műszakot sikeresen törölted!");
            document.getElementById('mentes').style.display = "none";
            document.getElementById('torles').style.display = "none";
            document.getElementById('masolas').style.display = "none";
        }
        else
            alert(escapeHtml(ret));
    }

    function callPHPPageTorol(postdata)
    {
        $.post('edit.php', postdata, HandlePHPPageDataTorol).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }

    function submitMuszak()
    {
        callPHPPageEdit({
            musznev: document.getElementById("musznev").value,
            idokezd: document.getElementById("idokezd").value,
            idoveg: document.getElementById("idoveg").value,
            letszam: document.getElementById("letszam").value,
            pont: document.getElementById("pont").value,
            mospont: document.getElementById("mospont").value,
            korid: document.getElementById("korid").value,
            megj: document.getElementById("megj").value,
            muszid: <?php echo $SzerkMuszak->ID; ?>
        });
    }
    function deleteMuszak()
    {
        if (confirm('Biztosan törlöd a műszakot?'))
        {
            callPHPPageTorol({
                musztorles: 1,
                muszid: <?php echo $SzerkMuszak->ID; ?>
            });
        }

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