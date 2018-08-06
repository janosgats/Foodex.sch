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

    <link rel="icon" href="../res/kepek/FoodEx_logo.png">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<body style="background-color: #de520d">
<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"
                        aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img alt="Brand" src="../res/kepek/FoodEx_logo.png"></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="../jelentkezes"> Jelentkezés műszakra <span class="sr-only">(current)</span></a></li>
                    <li><a href="../pontok/userpont/?mosjelentk=1"> Mosogattam!</a></li>
                    <li><a href="../pontok"> Pontozás</a></li>
                    <?php
                    if ($AktProfil->getUjMuszakJog() == 1) {
                        ?>
                        <li><a href="../ujmuszak"> Új műszak kiírása</a></li>
                        <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <form action="logout.php">
                            <button type="submit" class="btn btn-default">Kijelentkezés</button>
                        </form>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <div class="jumbotron">
        <h1>Hello Foodexes!</h1>
        <p>Neved: <b><?php echo $AktProfil->getNev(); ?></b></p>
        <p>Értesítési címed: <b><?php echo $AktProfil->getEmail(); ?></b></p>
    </div>
</div>
</body>

</html>