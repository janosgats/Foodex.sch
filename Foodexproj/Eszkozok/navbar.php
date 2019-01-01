<?php

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . '/Eszk.php';
require_once __DIR__ . '/../profil/Profil.php';

class NavBar
{

    private static function act($targetName, $ActName)
    {
        if ($targetName == $ActName)
            echo ' class="active" ';
    }

    public static function echonavbar(\Profil\Profil $AktProfil, $ActMenu)
    {
        $rootURL = \Eszkozok\Eszk::GetRootURL();;

        ?>

<!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->

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
                    <a class="navbar-brand" href="<?php echo $rootURL; ?>profil"><img alt="Brand" src="<?php echo $rootURL; ?>res/kepek/favicon1.svg"
                                                                  style="height: 30px"></a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li<?php self::act('jelentkezes', $ActMenu); ?>><a href="<?php echo $rootURL; ?>jelentkezes">Jelentkezés műszakra <span class="sr-only">(current)</span></a></li>
                        <li<?php self::act('mosjelentk', $ActMenu); ?>><a href="<?php echo $rootURL; ?>pontok/userpont/?mosjelentk=1">Mosogattam!</a></li>
                        <li<?php self::act('pontok', $ActMenu); ?>><a href="<?php echo $rootURL; ?>pontok">Pontozás</a></li>
                        <?php
                        if ($AktProfil->getUjMuszakJog() == 1)
                        {
                            ?>
                            <li<?php self::act('ujmuszak', $ActMenu); ?>><a href="<?php echo $rootURL; ?>ujmuszak">Új műszak kiírása</a></li>
                            <li<?php self::act('settings', $ActMenu); ?>><a href="<?php echo $rootURL; ?>settings">Beállítások</a></li>
                            <?php
                        }
                        ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right p-t" style="margin-top: 8px">
                        <li>
                            <form action="<?php echo $rootURL; ?>profil/logout.php">
                                <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}
//echonavbar(Eszkozok\Eszk::GetBejelentkezettProfilAdat(), false);