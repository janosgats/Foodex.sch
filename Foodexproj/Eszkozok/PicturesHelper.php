<?php
/**
 * Created by PhpStorm.
 * User: gatsj
 * Date: 2019. 06. 02.
 * Time: 21:48
 */

namespace Eszkozok;

require_once __DIR__ . '/Eszk.php';


class PicturesHelper
{
    public static function getProfilePicURLForInternalID($int_id)
    {
        $expectedFileName = 'pic_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $int_id) . '.jpg';

        $expectedFile = __DIR__ . '/../vardata/profilkepek/' . $expectedFileName;
        if (file_exists($expectedFile))
        {
            return Eszk::GetRootURL() . 'vardata/profilkepek/' . $expectedFileName;
        }
        else
            return Eszk::GetRootURL() . 'res/kepek/default_profile_picture.jpg';
    }
}