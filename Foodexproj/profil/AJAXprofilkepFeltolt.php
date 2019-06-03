<?php
ob_start();
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/PicturesHelper.php';

try
{
    \Eszkozok\LoginValidator::AccountSignedIn_ThrowsException();


    if ($_FILES["uj_profilkep"]['size'] > 14000000)
    {
        throw new Exception('Ez egy hatalmas fájl. Tölts fel kisebb képet! A tároláshoz úgyis le lesznek konvertálva.');
    }

    $img = $_FILES['uj_profilkep']['tmp_name'];

    if (($img_info = getimagesize($img)) == FALSE)
        throw new Exception('A fájl nem egy kép!');

    $srcwidth = $img_info[0];
    $srcheight = $img_info[1];


    switch ($img_info[2])
    {
        case IMAGETYPE_GIF  :
            $src = imagecreatefromgif($img);
            break;
        case IMAGETYPE_JPEG :
            $src = imagecreatefromjpeg($img);
            break;
        case IMAGETYPE_PNG  :
            $src = imagecreatefrompng($img);
            break;
        default :
            throw new Exception("Unknown filetype");
    }


    $exif = exif_read_data($img);
    if (!empty($exif['Orientation']))
    {
        switch ($exif['Orientation'])
        {
            case 3:
                $src = imagerotate($src, 180, 0);
                break;

            case 6:
                $src = imagerotate($src, -90, 0);

                $srcwidth = $img_info[1];
                $srcheight = $img_info[0];
                break;

            case 8:
                $src = imagerotate($src, 90, 0);

                $srcwidth = $img_info[1];
                $srcheight = $img_info[0];
                break;
        }
    }


    $srcWcenter = $srcwidth / 2;
    $srcHcenter = $srcheight / 2;

    $outwidth = 720;
    $outheight = 960;

    $outratio = $outwidth / $outheight;
    $srcratio = $srcwidth / $srcheight;


    $outImage = imagecreatetruecolor($outwidth, $outheight);

    if (abs($srcwidth - $outratio) < 0.0001)
    {//A két képarány megegyezik
        imagecopyresampled($outImage, $src, 0, 0, 0, 0, $outwidth, $outheight, $srcwidth, $srcheight);

    }
    else if ($srcratio > $outratio)
    {//A forrás szélesebb arányú, mint kéne
        $copywidth = $srcheight * $outratio;
        $copyheight = $srcheight;
        imagecopyresampled($outImage, $src, 0, 0, $srcWcenter - ($copywidth / 2), 0, $outwidth, $outheight, $copywidth, $copyheight);
    }
    else
    {//A forrás magasabb arányú, mint kéne
        $copywidth = $srcwidth;
        $copyheight = $srcwidth / $outratio;
        imagecopyresampled($outImage, $src, 0, 0, 0, $srcHcenter - ($copyheight / 2), $outwidth, $outheight, $copywidth, $copyheight);
    }


    $dst = __DIR__ . '/../vardata/profilkepek/';

    if (!file_exists($dst))
        if (!mkdir($dst, 0777, true))
            throw new Exception('Nem sikerült létrehozni a könyvtárakat.');

    imagejpeg($outImage, $dst . 'pic_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $_SESSION['profilint_id']) . '.jpg', 95);

    ob_clean();
    $ki = [];
    $ki['status'] = 'siker1234';
    $ki['newpicurl'] = \Eszkozok\PicturesHelper::getProfilePicURLForInternalID($_SESSION['profilint_id']);

    die(json_encode($ki));
}
catch (\Exception $e)
{
    ob_clean();
    $ki = [];
    $ki['status'] = 'err';
    $ki['error'] = $e->getMessage();

    die(json_encode($ki));
}