<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once '../Eszkozok/param.php';
require_once '../Eszkozok/AJAXhost.php';



if (IsURLParamSet('megj_int_id'))
{
    \Eszkozok\LoginValidator::AccountSignedIn_RedirectsToRoot();

    if (GetURLParam('megj_int_id') != $_SESSION['profilint_id'])
        QuitHost('99210: ' . 'Ne faszkodj az internal id-vel, nem a tied :/');

    if (IsURLParamSet('vicctext'))
    {
        try
        {
            $vicctext = GetURLParam('vicctext');

            if (strlen($vicctext) > 2000)
                QuitHost('tulhosszuvicc');

            $conn = \Eszkozok\Eszk::initMySqliObject();

            $stmt = $conn->prepare('SELECT `int_id` FROM `profilinfo` WHERE `int_id` = ?;');

            $stmt->bind_param('s', $_SESSION['profilint_id']);

            if ($stmt->execute())
            {
                $result = $stmt->get_result();
                if ($result->num_rows == 0)
                {
                    $stmt = $conn->prepare('INSERT INTO `profilinfo` (`int_id`, `kedv_vicc`) VALUES (?, ?);');
                    $stmt->bind_param('ss', $_SESSION['profilint_id'], $vicctext);

                }
                else
                {
                    $stmt = $conn->prepare("UPDATE `profilinfo` SET `kedv_vicc` = ? WHERE `profilinfo`.`int_id` = ?");
                    $stmt->bind_param('ss', $vicctext, $_SESSION['profilint_id']);
                }

                if ($stmt->execute())
                {
                    QuitHost('siker345');
                }
                else
                {
                    throw new \Exception('$stmt->execute() (2) is false.');
                }
            }
            else
            {
                throw new \Exception('$stmt->execute() (1) is false.');
            }

        }
        catch (\Exception $e)
        {
            QuitHost('99213: ' . $e->getMessage());
        }
    }
}