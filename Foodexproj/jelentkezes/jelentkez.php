<?php

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
include_once '../3rdparty/reCaptcha/autoload.php';

include_once __DIR__ . '/../Eszkozok/SMTPSender.php';

function isReCaptchaValid()
{
    $secret = '***REMOVED***';

    if (IsURLParamSet('g-recaptcha-response'))
    {

        $recaptcha = new \ReCaptcha\ReCaptcha($secret);


        $resp = $recaptcha->verify(GetURLParam('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess())
        {
            return true;
        }
        else
        {
//            $errorstring = '';
//            foreach ($resp->getErrorCodes() as $code)
//            {
//                $errorstring .= $code . ' - ';
//            }

        }
    }
    return false;
}

function doJelentkezes()
{
    if (!isset($_SESSION['profilint_id']))
        Eszkozok\Eszk::RedirectUnderRoot('');

    if (!IsURLParamSet('g-recaptcha-response'))
        return;
//Ha a 'g-recaptcha-response' paraméter meg van adva, megy tovább az ellenőrzés és végrehajtás...


    if (!isReCaptchaValid())
        \Eszkozok\Eszk::dieToErrorPage('3211: A ReCaptcha megoldása (már) nem érvényes!');

    if (IsURLParamSet('muszid') && IsURLParamSet('muszmuv'))
    {
        $muszakID = GetURLParam('muszid');
        try
        {
            $conn = \Eszkozok\Eszk::initMySqliObject();

            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            if (GetURLParam('muszmuv') == 'felvesz')
            {

                $stmt = $conn->prepare("SELECT `ID` FROM `fxjelentk` WHERE `jelentkezo` = ? AND `muszid` = ? AND `status` = 1;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $intid = $_SESSION['profilint_id'];
                $stmt->bind_param('si', $intid, $muszakID);

                if ($stmt->execute())
                {

                }
                else
                    throw new \Exception('Az SQL parancs végrehajtása nem sikerült: Felvétel SELECT keresés');

                $result = $stmt->get_result();

                if ($result->num_rows == 0)
                {//Ha aktuálisan nincs felvéve neki ez a műszak

                    $stmt = $conn->prepare("INSERT INTO `fxjelentk` (`jelentkezo`, `muszid`, `status`, `jelido`) VALUES (?, ?, 1,NOW());");
                    if (!$stmt)
                        throw new \Exception('$stmt is \'false\'');

                    $intid = $_SESSION['profilint_id'];
                    $stmt->bind_param('si', $intid, $muszakID);

                    if ($stmt->execute())
                    {

                    }
                    else
                        throw new \Exception('Az SQL parancs végrehajtása nem sikerült: Felvétel INSERT');
                }

            }
            else if (GetURLParam('muszmuv') == 'lead')
            {
                $leadottMuszak = \Eszkozok\Eszk::getMuszakFromMuszakIdWithConn($muszakID, $conn);


                $eredetivarolista = \Eszkozok\Eszk::getJelentkezokListajaWithConn($muszakID, $conn);
                $eredetiKeret = array();

                for ($i = 0; $i < $leadottMuszak->letszam && $i < count($eredetivarolista); ++$i)
                {
                    $eredetiKeret[] = $eredetivarolista[$i];
                }

                $stmt = $conn->prepare("UPDATE `fxjelentk` SET `status` = 0, `leadido` = NOW() WHERE `jelentkezo` = ? AND `muszid` = ? AND `status` = 1;");
                if (!$stmt)
                    throw new \Exception('$stmt is \'false\'');

                $intid = $_SESSION['profilint_id'];
                $stmt->bind_param('si', $intid, $muszakID);

                if ($stmt->execute())
                {
                    $ujvarolista = \Eszkozok\Eszk::getJelentkezokListajaWithConn($muszakID, $conn);

                    $elobbreKerultek = array();//Nekik kell e-mailt küldeni

                    for ($i = 0; $i < $leadottMuszak->letszam && $i < count($ujvarolista) && $i < count($eredetiKeret); ++$i)
                    {
                        if (!in_array($ujvarolista[$i], $eredetiKeret))
                            $elobbreKerultek[] = $ujvarolista[$i];
                    }

                    $emailTomb = \Eszkozok\Eszk::getColumnAdatTombFromInternalIdTombWithConn($elobbreKerultek, 'email', $conn);
                    SMTPSender::SendVarolistaKeretbeKerultTomb($leadottMuszak->musznev, $emailTomb);
                }
                else
                    throw new \Exception('Az SQL parancs végrehajtása nem sikerült: Leadás');

            }
        }
        catch
        (\Exception $e)
        {
            \Eszkozok\Eszk::dieToErrorPage('3217: ' . $e->getMessage());
        }
        $conn->close();
    }

}