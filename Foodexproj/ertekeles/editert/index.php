<?php
session_start();


require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../../Eszkozok/entitas/Ertekeles.php';
require_once __DIR__ . '/../../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::Ertekelo_DiesToErrorrPage();

$ErtekeltProfil = null;
$ErtekeltMuszak = null;

$SzerkesztettErtekeles = null;//Ha NEM null, akkor szerkesztés történik, ha null, akkor létrehozás hozzáadás

$ErtekeltMuszid = null;
$Ertekelt_int_id = null;
$Ertekelo_int_id = null;

try
{
    if (!isset($_REQUEST['muszid']) || !isset($_REQUEST['ertekelt_int_id']))
        throw new \Exception('Hiányzó paraméterek.');

    if ($_REQUEST['muszid'] == '' || !is_numeric($_REQUEST['muszid']) || $_REQUEST['ertekelt_int_id'] == '')
        throw new \Exception('Hibás paraméterek.');

    $ErtekeltMuszid = $_REQUEST['muszid'];
    $Ertekelt_int_id = $_REQUEST['ertekelt_int_id'];
    $Ertekelo_int_id = $_SESSION['profilint_id'];

    $conn = \Eszkozok\Eszk::initMySqliObject();

    /////////ÉRTÉKELÉS FETCHELÉSE////////////

    $stmt = $conn->prepare("SELECT * FROM ertekelesek WHERE muszid = ? AND ertekelt = ? AND ertekelo = ?");
    $stmt->bind_param('iss', $ErtekeltMuszid, $Ertekelt_int_id, $Ertekelo_int_id);

    if (!$stmt->execute())
        throw new \Exception('$stmt->execute() is false: 1');

    $res = $stmt->get_result();

    if ($res->num_rows == 1)
    {
        $SzerkesztettErtekeles = new \Eszkozok\Ertekeles($res->fetch_assoc());
    }
    else if ($res->num_rows > 1)
        throw new \Exception('DB integrity fail: $res->num_rows > 1');


    /////////ÉRTÉKELT PROFIL és MŰSZAK FETCHELÉSE////////////

    if (($ErtekeltProfil = \Eszkozok\Eszk::GetTaroltProfilAdat($Ertekelt_int_id)) == null)
        throw new \Exception('Az értékelt profil nem található');

    if (($ErtekeltMuszak = \Eszkozok\Eszk::GetTaroltMuszakAdatWithConn($ErtekeltMuszid, false, $conn)) == null)
        throw new \Exception('Az értékelt műszak nem található');

}
catch (\Exception $e)
{
    \Eszkozok\Eszk::dieToErrorPage('67862: ' . $e->getMessage(), 'ertekeles');
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
    <title>Fx - Értékelés szerkesztése</title>

    <link rel="icon" href="../../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>

    <link rel='stylesheet' href='../../vendor/kartik-v/bootstrap-star-rating/css/star-rating.css'>
    <link rel='stylesheet' href='../../vendor/kartik-v/bootstrap-star-rating/themes/krajee-fas/theme.css'>


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <script src="../../vendor/kartik-v/bootstrap-star-rating/js/star-rating.js"></script>
    <script src="../../js/star-rating/locale-hu.js"></script>

</head>

<body style="background-color: #de520d">
<div class="container">

    <?php
    NavBar::echonavbar('ertekeles')
    ?>

    <div class="jumbotron" style="padding-top:10px">
        <form method="get">
            <div style="width: 100%; text-align: center">
                <h1 style="color: darkgray; font-family: 'Arial'; font-size: xx-large">
                    <?php
                    echo ($SzerkesztettErtekeles == null) ? 'Új értékelés írása' : 'Értékelés szerkesztése';
                    ?>
                </h1>
                <br><br>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="ertekeltnev">Értékelt Foodexes</label>
                    <input id="ertekeltnev" name="ertekeltnev" type="text"
                           value="<?php echo $ErtekeltProfil->getNev(); ?>" class="form-control" readonly>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="ertekeltnev">Értékelt Műszak</label>
                    <input id="ertekeltnev" name="ertekeltnev" type="text"
                           value="<?php echo $ErtekeltMuszak->musznev . ': ' . $ErtekeltMuszak->idokezd; ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="e_pontossag">Pontosság</label>
                    <input id="e_pontossag" name="e_pontossag" class="rating rating-loading" value="<?php if ($SzerkesztettErtekeles) echo htmlentities($SzerkesztettErtekeles->e_pontossag); ?>"
                           data-min="0" data-max="5" data-step="0.5" data-size="md" data-language="hu" data-theme="krajee-fas">
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="e_penzkezeles">Pénzkezelés</label>
                    <input id="e_penzkezeles" name="e_penzkezeles" class="rating rating-loading" value="<?php if ($SzerkesztettErtekeles) echo htmlentities($SzerkesztettErtekeles->e_penzkezeles); ?>"
                           data-min="0" data-max="5" data-step="0.5" data-size="md" data-language="hu" data-theme="krajee-fas">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 col-sm-12">
                    <label for="e_szakertelem">Szakértelem</label>
                    <input id="e_szakertelem" name="e_szakertelem" class="rating rating-loading" value="<?php if ($SzerkesztettErtekeles) echo htmlentities($SzerkesztettErtekeles->e_szakertelem); ?>"
                           data-min="0" data-max="5" data-step="0.5" data-size="md" data-language="hu" data-theme="krajee-fas">
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="e_dughatosag">Dugható</label>
                    <input id="e_dughatosag" name="e_dughatosag" class="rating rating-loading" value="<?php if ($SzerkesztettErtekeles) echo htmlentities($SzerkesztettErtekeles->e_dughatosag); ?>"
                           data-min="0" data-max="5" data-step="0.5" data-size="md" data-language="hu" data-theme="krajee-fas">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12 col-sm-12">
                    <label for="e_szoveg">Szöveges értékelés</label>
                    <input id="e_szoveg" name="e_szoveg" type="text"
                           value="<?php if ($SzerkesztettErtekeles) echo htmlentities($SzerkesztettErtekeles->e_szoveg); ?>" class="form-control">
                </div>
            </div>

            <div class="row" style="padding-right: 7%">

                <button class="btn btn-success pull-right" name="mentes" id="mentes" onclick="submitErtekeles()" type="button">
                    Értékelés Mentése
                </button>
                <?php
                if ($SzerkesztettErtekeles)
                {
                    ?>
                    <button class="btn btn-danger pull-right" name="torles" id="torles" style="margin-right: 10px"
                            onclick="deleteErtekeles()" type="button">Értékelés törlése
                    </button>
                    <?php
                }
                ?>

                <a href="../">
                    <button class="btn btn-info pull-right" name="megsem" id="megsem" style="margin-right: 10px" type="button">Mégsem</button>
                </a>

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

    function HandlePHPPageDataEdit(ret)
    {
        if (ret == "siker1234")
        {
            window.location.replace('<?= \Eszkozok\Eszk::GetRootURL() . 'ertekeles';?>');
        }
        else
            alert('Hiba: ' + ret);
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
        if (ret == "siker1234")
        {
            window.location.replace('<?= \Eszkozok\Eszk::GetRootURL() . 'ertekeles';?>');
        }
        else
            alert('Hiba: ' + ret);
    }

    function callPHPPageTorol(postdata)
    {
        $.post('edit.php', postdata, HandlePHPPageDataTorol).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }

    function submitErtekeles()
    {
        callPHPPageEdit({
            muvelet: '<?= ($SzerkesztettErtekeles == null)? 'letrehozas':'modositas'; ?>',
            muszid: '<?php echo htmlentities($ErtekeltMuszid); ?>',
            ertekelt_int_id: '<?php echo htmlentities($Ertekelt_int_id); ?>',
            e_pontossag: document.getElementById("e_pontossag").value,
            e_penzkezeles: document.getElementById("e_penzkezeles").value,
            e_szakertelem: document.getElementById("e_szakertelem").value,
            e_dughatosag: document.getElementById("e_dughatosag").value,
            e_szoveg: document.getElementById("e_szoveg").value
        });
    }

    function deleteErtekeles()
    {
        if (confirm('Biztosan törlöd az értékelést?'))
        {
            callPHPPageTorol({
                muvelet: 'torles',
                ertekeles_id: '<?php echo ($SzerkesztettErtekeles)?htmlentities($SzerkesztettErtekeles->ID):-1; ?>'
            });
        }

    }
</script>


</body>
</html>