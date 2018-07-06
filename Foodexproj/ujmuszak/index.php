<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';

if (!isset($_SESSION['profilint_id']))
    Eszkozok\Eszk::RedirectUnderRoot('');

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Új műszak kiírása</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <link rel="stylesheet" href="../backgradient.css">
</head>

<body>


<!-- Special version of Bootstrap that is isolated to content wrapped in .bootstrap-iso -->
<link rel="stylesheet" href="../3rdparty/bootstrap-iso.css"/>
<!--<link rel="stylesheet" href="https://formden.com/static/cdn/bootstrap-iso.css"/>-->

<!--Font Awesome (added because you use icons in your prepend/append)-->
<link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css"/>

<!-- Inline CSS based on choices in "Settings" tab -->
<style>.bootstrap-iso .formden_header h2, .bootstrap-iso .formden_header p, .bootstrap-iso form {
        font-family: Arial, Helvetica, sans-serif;
        color: black
    }

    .bootstrap-iso form button, .bootstrap-iso form button:hover {
        color: white !important;
    }

    .asteriskField {
        color: red;
    }</style>


<a href="../profil" style="font-size: larger"> Vissza a profilba</a>
<br><br>

<!-- HTML Form (wrapped in a .bootstrap-iso div) -->
<div class="bootstrap-iso" style="background: transparent">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <form action="" class="form-horizontal" method="get">

                    <p style="display: inline;">Név: </p>
                    <input id="musznev" name="musznev" type="text" style="background: transparent"
                           placeholder="pl. Pizzásch 1">
                    <br><br>

                    <p style="display: inline">Létszám: </p>
                    <input id="letszam" name="letszam" type="text" style="background: transparent" placeholder="pl. 2">
                    <br><br>

                    <div class="form-group ">
                        <label class="control-label col-sm-2 requiredField" for="idokezd">Kezdet: </label>

                        <div class="col-sm-3">
                            <div class="input-group date">
                                <input class="form-control" id="idokezd" name="idokezd" style="background: transparent" placeholder="YYYY/MM/DD HH:mm"
                                       type="text"/>

                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="form-group ">
                        <label class="control-label col-sm-2 requiredField" for="idoveg">Vég: </label>

                        <div class="col-sm-3">
                            <div class="input-group">

                                <input class="form-control" id="idoveg" name="idoveg" style="background: transparent" placeholder="YYYY/MM/DD HH:mm"
                                       type="text"/>

                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <br>

                    <p style="display: inline">Közösségi pont: </p>
                    <input id="pont" name="pont" type="text" style="background: transparent" placeholder="pl. 3">
                    <br>
                    <br>
                    <p style="display: inline">Pont mosogatásért: </p>
                    <input id="mospont" name="pont" type="text" style="background: transparent" placeholder="pl. 0.5">
                    <br>
                    <br>

                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">

                            <button class="btn btn-primary " name="kiiras" style="background: transparent" onclick="submitMuszak()" type="button">
                                Műszak kiírása
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
<script src='http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js'></script>
<script
    src='http://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js'></script>


<link rel='stylesheet prefetch' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css'>
<link rel='stylesheet prefetch'
      href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>
<link rel='stylesheet prefetch' href='http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>


<!-- Include Date Range Picker -->
<!--<script type="text/javascript"-->
<!--        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>-->
<!--<link rel="stylesheet"-->
<!--      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>-->

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
            mospont: document.getElementById("mospont").value
        });
    }

</script>

<script>
    $(document).ready(function ()
    {
        var bindDatetimePicker = function (id)
        {
            var date_input = $('input[name=' + id + ']'); //our date input has the name "idokezd"
            var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
            date_input.datetimepicker({
                format: 'YYYY/MM/DD HH:mm',
                container: container,
                todayHighlight: true,
                autoclose: true,
                sideBySide: true,
                showTodayButton: true,
                locale: 'ru'
            });


        }


        bindDatetimePicker("idokezd");
        bindDatetimePicker("idoveg");
    })
</script>


</body>