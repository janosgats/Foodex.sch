<?php

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . '/Eszk.php';
require_once __DIR__ . '/LoginValidator.php';

class NavBar
{

    private static function act($targetName, $ActName)
    {
        if ($targetName == $ActName)
            echo ' class="active" ';
    }

    public static function echonavbar($ActMenu)
    {
        \Eszkozok\LoginValidator::AccountSignedIn_RedirectsToRoot();

        $rootURL = \Eszkozok\Eszk::GetRootURL();;

        ?>

        <!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->

        <link rel="stylesheet" href="<?= \Eszkozok\Eszk::GetRootURL(); ?>css/navbar_set_breakpoint.css">
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
                        <?php
                        if (\Eszkozok\LoginValidator::MuszJelJog_NOEXIT())
                        {
                            ?>
                            <li<?php self::act('jelentkezes', $ActMenu); ?>><a href="<?php echo $rootURL; ?>jelentkezes">Jelentkezés<span class="sr-only">(current)</span></a></li>
                            <?php
                        }
                        if (\Eszkozok\LoginValidator::FxTag_NOEXIT())
                        {
                            ?>
                            <li<?php self::act('mosjelentk', $ActMenu); ?>><a href="<?php echo $rootURL; ?>pontok/userpont/?mosjelentk=1">Mosogattam!</a></li>
                        <?php
                        }
                        if (\Eszkozok\LoginValidator::PontLatJog_NOEXIT())
                        {
                            ?>
                            <li<?php self::act('pontok', $ActMenu); ?>><a href="<?php echo $rootURL; ?>pontok">Pontok</a></li>
                            <?php
                        }
                        if (\Eszkozok\LoginValidator::AdminJog_NOEXIT())
                        {
                            ?>
                            <li<?php self::act('ujmuszak', $ActMenu); ?>><a href="<?php echo $rootURL; ?>ujmuszak">Új műszak</a></li>
                            <li<?php self::act('korok', $ActMenu); ?>><a href="<?php echo $rootURL; ?>korok">Körök</a></li>
                            <li<?php self::act('accok', $ActMenu); ?>><a href="<?php echo $rootURL; ?>accok">Accok</a></li>
                            <li<?php self::act('statisztikak', $ActMenu); ?>><a href="<?php echo $rootURL; ?>statisztikak">Statisztikák</a></li>
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