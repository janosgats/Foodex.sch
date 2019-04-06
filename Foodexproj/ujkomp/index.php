<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');


$KompSzerkesztes = false;

$SzerkesztendoKomp;

$KompProfil;

if (IsURLParamSet('szerk') && GetURLParam('szerk') == 1)
{
    if (IsURLParamSet('kompid'))
    {
        $kompid = GetURLParam('kompid');

        $SzerkesztendoKomp = \Eszkozok\Eszk::GetTaroltKompenzAdat($kompid, true);

        $KompProfil = \Eszkozok\Eszk::GetTaroltProfilAdat($SzerkesztendoKomp->int_id);
        $KompSzerkesztes = true;
    }
    else
        Eszkozok\Eszk::dieToErrorPage('12344: A szerkesztendő kompenzáció nincs megadva.');
}

if (!$KompSzerkesztes)
{
    if (IsURLParamSet('int_id'))
    {
        $KompProfil = \Eszkozok\Eszk::GetTaroltProfilAdat(GetURLParam('int_id'));
    }
    else
        Eszkozok\Eszk::RedirectUnderRoot('');
}

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
    <title>Fx - Kompenzálás</title>

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
    <script src='../3rdparty/jquery.bootstrap-touchspin.js'></script>

</head>
<body style="background-color: #de520d">

<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, '');
    ?>


    <div class="jumbotron" style="padding-top: 5px">

        <h3><?php echo $KompProfil->getNev(); ?> Kompenzálása</h3>
        <hr>
        <form method="get">
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">

                    <label for="pont">Pont (Akár negatív)</label>

                    <input id="pont" type="text" value="<?php if ($KompSzerkesztes)
                    {
                        echo $SzerkesztendoKomp->pont;
                    }
                    else
                    {
                        echo '0';
                    } ?>" name="pont">
                    <script>
                        $("input[name='pont']").TouchSpin({
                            min: -999999,
                            max: 999999,
                            step: 0.1,
                            decimals: 2,
                            boostat: 0.2,
                            maxboostedstep: 0.5,
                            postfix: 'pont'
                        });
                    </script>
                </div>

                <div class="form-group col-md-6 col-sm-12">
                    <label for="megj">Megjegyzés</label>
                    <input id="megj" name="megj" type="text" placeholder="pl. Nem jelent meg a műszakon."
                           value="<?php if ($KompSzerkesztes)
                           {
                               echo $SzerkesztendoKomp->megj;
                           } ?>" class="form-control">
                </div>

            </div>

            <div class="row" style="padding-right: 7%">
                <?php
                if (!$KompSzerkesztes)
                {
                    ?>
                    <button class="btn btn-primary pull-right" name="submit" id="submit" onclick="submitKomp()" type="button">Kompenzálás</button>
                    <?php
                }
                else
                {
                    ?>
                    <button class="btn btn-primary pull-right" name="mentes" id="mentes" onclick="editKomp()" type="button">Mentés</button>
                    <button class="btn btn-danger pull-right" name="torles" id="torles" style="margin-right: 10px" onclick="deleteKomp()" type="button">Kompenzáció törlése</button>
                    <?php
                }
                ?>
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

    function HandlePHPPageDataSubmit(ret)
    {
        if (ret == "siker4567")
            document.location.href = "../profil/?mprof=<?php echo $KompProfil->getInternalID(); ?>";
        else
            alert(escapeHtml(ret));
    }
    function callPHPPageSubmit(postdata)
    {
        $.post('kiir.php', postdata, HandlePHPPageDataSubmit).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function submitKomp()
    {
        callPHPPageSubmit({
            muv: 'submit',
            int_id: '<?php echo $KompProfil->getInternalID(); ?>',
            pont: document.getElementById("pont").value,
            megj: document.getElementById("megj").value
        });
    }

</script>

<script>


    function HandlePHPPageDataEdit(ret)
    {
        if (ret == "siker4567")
            alert("A kompenzációt sikeresen módosítottad!");
        else
            alert(escapeHtml(ret));
    }
    function callPHPPageEdit(postdata)
    {
        $.post('kiir.php', postdata, HandlePHPPageDataEdit).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function editKomp()
    {
        callPHPPageEdit({
            muv: 'edit',
            kompid: '<?php echo $SzerkesztendoKomp->ID; ?>',
            int_id: '<?php echo $KompProfil->getInternalID(); ?>',
            pont: document.getElementById("pont").value,
            megj: document.getElementById("megj").value
        });
    }


    function HandlePHPPageDataDelete(ret)
    {
        if (ret == "siker4567")
        {
            alert("A kompenzációt sikeresen törölted!");
            document.getElementById('mentes').style.display = "none";
            document.getElementById('torles').style.display = "none";
        }
        else
            alert(escapeHtml(ret));
    }
    function callPHPPageDelete(postdata)
    {
        $.post('kiir.php', postdata, HandlePHPPageDataDelete).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function deleteKomp()
    {
        callPHPPageDelete({
            muv: 'delete',
            kompid: '<?php echo $SzerkesztendoKomp->ID; ?>'
        });
    }
</script>


</body>
</html>