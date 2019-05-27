<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/entitas/Kor.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getAdminJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Accountok</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="main.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>

<body style="background-color: #de520d">

<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'accok');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">

            <label for="exampleInputEmail1">Keresés:</label>
            <input onkeyup="keresesFgv(this);" type="text" class="form-control" id="kereses" aria-describedby="emailHelp" placeholder="Pl. Végh Béla">
            <small id="emailHelp" class="form-text text-muted">Kezdd gépelni a tag nevét!</small>

        </div>

        <div class="panel-body">
            <table class="table table-hover" id="accoktable">

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

    var accoktable = document.getElementById('accoktable');
    function HandlePHPPageData(ret)
    {
        var fullres = JSON.parse(ret);

        var thead = jQuery.parseHTML('<thead><tr><th>Név</th> <th>Jogosultságok</th></tr></thead>')[0];
        var tbody = jQuery.parseHTML('<tbody></tbody>')[0];

        fullres.forEach(function (row)
        {
            var a_user_nev = jQuery.parseHTML('<a href="../profil/?mprof=' + row['internal_id'] + '"><div style="width: 100%">' + escapeHtml(row['nev']) + '</div></a>')[0];


            var i_toggle_adminjog = jQuery.parseHTML('<i id="i_toggle_adminjog' + row['internal_id'] + '" data-toggle="tooltip" data-container="table" data-placement="right"  title="Admin jog"  class="acc-jogosultsag ' + ((row['adminjog'] == 1) ? 'acc-jogosultsag-admin-true' : 'acc-jogosultsag-admin-false') + ' fas fa-2x fa-user-astronaut"></i>')[0];
            i_toggle_adminjog.onclick = function ()
            {
                    var r;

                    if (this.classList.contains('acc-jogosultsag-admin-true'))

                    {
                        r = confirm("Biztosan elveszed " + row['nev'] + " admin jogát?");
                        if (r == true)
                            submitSetAdminjog(row['internal_id'], 0)
                    }
                else
                    {
                        r = confirm("Biztosan adminná teszed őt: " + row['nev'] + "?");
                        if (r == true)
                            submitSetAdminjog(row['internal_id'], 1)
                    }
            };


            var td_user_nev = jQuery.parseHTML('<td></td>')[0];
            var td_toggle_adminjog = jQuery.parseHTML('<td></td>')[0];

            td_user_nev.appendChild(a_user_nev);
            td_toggle_adminjog.appendChild(i_toggle_adminjog);


            var tr = jQuery.parseHTML('<tr></tr>')[0];
            tr.appendChild(td_user_nev);
            tr.appendChild(td_toggle_adminjog);

            tbody.appendChild(tr);
        });
        accoktable.innerHTML = '';

        accoktable.appendChild(thead);
        accoktable.appendChild(tbody);

        $('[data-toggle="tooltip"]').tooltip();
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

    function HandleAJAXjogosultsagokPHPPageData(ret)
    {

        try
        {
//            console.log(ret);
            var fullres = JSON.parse(ret);

            if (fullres.status != 'siker3456')
                alert('Hiba: ' + fullres.error);
            else
            {
                if (fullres.adminjog == 1)
                {
                    $("#i_toggle_adminjog" + fullres.internal_id).addClass('acc-jogosultsag-admin-true').removeClass('acc-jogosultsag-admin-false');
                }
                else
                {
                    $("#i_toggle_adminjog" + fullres.internal_id).addClass('acc-jogosultsag-admin-false').removeClass('acc-jogosultsag-admin-true');
                }
            }
        }
        catch (e)
        {
            alert('Hiba: ' + e.message)
        }
    }
    function callPHPPageSetAdminjog(postdata)
    {
        $.post('AJAXjogosultsagok.php', postdata, HandleAJAXjogosultsagokPHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function submitSetAdminjog(internal_id, adminjog)
    {
        callPHPPageSetAdminjog({
            int_id: internal_id,
            adminjog: adminjog
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


