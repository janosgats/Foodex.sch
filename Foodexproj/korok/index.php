<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../entitas/Szemely.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Személyek</title>

    <link rel="icon" href="../res/kepek/kilometerora_64.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">




    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="../res/stylesheet/default.css">
    <link rel="stylesheet" href="main.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>

<body>

<div class="container">

    <?php
    NavBar::echonavbar('szemelyek');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">

            <label for="exampleInputEmail1">Keresés:</label>
            <input onkeyup="keresesFgv(this);" type="text" class="form-control" id="kereses" aria-describedby="emailHelp" placeholder="Példa Béla">
            <small id="emailHelp" class="form-text text-muted">Kezdje gépelni a személy nevét!</small>

        </div>

        <div class="panel-body">
            <table class="table table-hover" id="szemelyektable">

            </table>
        </div>
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

    var tsorokdiv = document.getElementById('szemelyektable');
    function HandlePHPPageData(ret)
    {
        var out = '<thead><tr><th>Név</th> <th>Lakcím</th><th>Születési dátum</th></tr></thead>';
        out += '<tr><td><a href="../editszemely">+ Új személy felvétele</a></td></tr>';
        var fullres = JSON.parse(ret);


        fullres.forEach(function (row)
        {
            out += '<tr>';
            out += '<td>' + '<a href="../editszemely?szerk=1&szemid=' + row['id'] + '">' + escapeHtml(row['nev']) + '</a>'+ '</td>';
            out += '<td>' + escapeHtml(row['lakcim']) + '</td>';
            out += '<td>' + escapeHtml(row['szuldat']) + '</td>';
            out += '</tr>';
        });

        tsorokdiv.innerHTML = out;
    }

    function callPHPPage(postdata)
    {
        $.post('tabllekero.php', postdata, HandlePHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function keresesFgv(keresesmezo)
    {
        callPHPPage({
            keresett: keresesmezo.value
        });
    }
    function uresKereses()
    {
        callPHPPage({});
    }
    window.onload = function ()
    {
        uresKereses();
    };
</script>


</body>
</html>


