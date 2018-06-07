<?php
session_start();

include_once '../Eszkozok/Eszk.php';
include_once 'Profil.php';

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

</head>

<body>


Bejelentkezve: <?php echo $AktProfil->getNev(); ?>
<form action="logout.php">
    <button class="button" type="submit">Kijelentkezés</button>
</form>

<?php
if($AktProfil->getUjMuszakJog() == 1)
{
    ?>
    <a href = "#"> Új műszak kiírása</a>
    <?php
}
?>
<br>
<a href = "#"> Jelentkezés műszakra</a>
</body>

</html>