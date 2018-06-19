<?php

include_once '../Eszkozok/Eszk.php';
include_once '../Eszkozok/param.php';
include_once '../3rdparty/reCaptcha/autoload.php';

function isReCaptchaValid()
{
    $secret = '***REMOVED***';

    if (IsParamSet('g-recaptcha-response'))
    {

        $recaptcha = new \ReCaptcha\ReCaptcha($secret);


        $resp = $recaptcha->verify(GetParam('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);

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

    if (!IsParamSet('g-recaptcha-response'))
        return;
//Ha a 'g-recaptcha-response' paraméter meg van adva, megy tovább az ellenőrzés és végrehajtás...


    if (!isReCaptchaValid())
        \Eszkozok\Eszk::dieToErrorPage('3211: A ReCaptcha megoldása (már) nem érvényes!');

    if (IsParamSet('muszid') && IsParamSet('muszmuv'))
    {
        $muszakID = GetParam('muszid');
        try
        {
            $conn = \Eszkozok\Eszk::initMySqliObject();

            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            if (GetParam('muszmuv') == 'felvesz')
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
            else if (GetParam('muszmuv') == 'lead')
            {
                $stmt = $conn->prepare("UPDATE `fxjelentk` SET `status` = 0, `leadido` = NOW() WHERE `jelentkezo` = ? AND `muszid` = ? AND `status` = 1;");
                if (!$stmt)
                    throw new \Exception('$stmt is \'false\'');

                $intid = $_SESSION['profilint_id'];
                $stmt->bind_param('si', $intid, $muszakID);

                if ($stmt->execute())
                {

                }
                else
                    throw new \Exception('Az SQL parancs végrehajtása nem sikerült: Leadás');

            }
        }
        catch (\Exception $e)
        {
            \Eszkozok\Eszk::dieToErrorPage('3217: ' . $e->getMessage());
        }
    }

}