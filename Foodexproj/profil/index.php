<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once 'Profil.php';

if (!isset($_SESSION['profilint_id']))
    Eszkozok\Eszk::RedirectUnderRoot('');

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <link rel="stylesheet" href="../backgradient.css">
</head>

<body>


Bejelentkezve: <?php echo $AktProfil->getNev(); ?>
<br>

<?php
if($AktProfil->getUjMuszakJog() == 1)
{
    ?>
    <a href = "../ujmuszak"> Új műszak kiírása</a>
    <?php
}
?>
<br>
<a href = "../jelentkezes"> Jelentkezés műszakra</a>
<br>
<a href = "../pontok/userpont/?mosjelentk=1"> Mosogattam!</a>
<br>
<a href = "../pontok"> Pontok megtekintése</a>

<br>
<p>Értesítési címed: <b><?php echo $AktProfil->getEmail(); ?></b></p>
<br>
<br>
<form action="logout.php">
    <button class="button" style="background: transparent" type="submit" >Kijelentkezés</button>
</form>
</body>

</html>