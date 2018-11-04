<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once 'Profil.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if (IsURLParamSet('mprof'))
{
    $mprof_int_id = GetURLParam('mprof');
    $MegjProfil = \Eszkozok\Eszk::GetTaroltProfilAdat($mprof_int_id);
}
else
{
    $MegjProfil = $AktProfil;//TODO: Ezt URL paraméter alapján beállítani
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body style="background-color: #de520d">
<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1"
                        aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img alt="Brand" src="../res/kepek/FoodEx_logo.png"
                                                      style="height: 30px"></a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="../jelentkezes"> Jelentkezés műszakra <span class="sr-only">(current)</span></a></li>
                    <li><a href="../pontok/userpont/?mosjelentk=1"> Mosogattam!</a></li>
                    <li><a href="../pontok"> Pontozás</a></li>
                    <?php
                    if ($AktProfil->getUjMuszakJog() == 1)
                    {
                        ?>
                        <li><a href="../ujmuszak"> Új műszak kiírása</a></li>
                        <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right p-t" style="margin-top: 8px">
                    <li>
                        <form action="logout.php">
                            <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="jumbotron">
        <h1><?php echo $MegjProfil->getNev(); ?></h1>

        <p>Értesítési cím: <b><?php echo $MegjProfil->getEmail(); ?></b></p>
        <a style="cursor: pointer;" href="<?php echo '../pontok/userpont/?int_id=' . $MegjProfil->getInternalID(); ?>">
            <p>Pontok: <b><?php try
                    {
                        $buff = \Eszkozok\Eszk::GetAccPontok($MegjProfil->getInternalID());
                        echo $buff;
                    }
                    catch (\Exception $e)
                    {
                        echo 'N/A';
                    } ?> pont</b></p></a>

        <?php
        if ($AktProfil->getUjMuszakJog() == 1)
        {
            ?>
            <a class="btn btn-primary pull-right" name="kompenz" id="kompenz" style="margin-right: 10px"
               href="../ujkomp?<?php echo 'int_id=' . urlencode($MegjProfil->getInternalID()); ?>" type="button">Kompenzálás
            </a>

            <?php
        }
        ?>

    </div>

    <div class="panel panel-default">
        <div class="panel-heading">

            Kompenzációk
        </div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Pont</th>
                    <th>Megjegyzés</th>
                </tr>
                </thead>
                <?php
                try
                {
                    $conn = \Eszkozok\Eszk::initMySqliObject();
                    $stmt = $conn->prepare("SELECT `pont`, `megj` FROM `kompenz` WHERE `internal_id` = ?;");
                    if (!$stmt)
                        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                    $buffInt = $MegjProfil->getInternalID();
                    $stmt->bind_param('s', $buffInt);

                    if ($stmt->execute())
                    {
                        $resultKomp = $stmt->get_result();
                        if ($resultKomp->num_rows > 0)
                        {
                            while ($rowKomp = $resultKomp->fetch_assoc())
                            {
                                ?>

                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($rowKomp['pont']) . ' pont'; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($rowKomp['megj']); ?></td>

                                </tr>
                                <?php
                            }
                        }
                    }
                    else
                        throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
                }
                catch (\Exception $e)
                {
                }
                ?>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>

</body>
</html>