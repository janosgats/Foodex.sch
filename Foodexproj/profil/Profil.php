<?php


namespace Profil;


class Profil
{
    private $Nev = "";
    private $UjMuszakJog = 0;
    public function __construct($nev, $ujmuszakjog)
    {
        $this->Nev = $nev;
        $this->UjMuszakJog = $ujmuszakjog;
    }

    public function getNev()
    {
        return $this->Nev;
    }
    public function getUjMuszakJog()
    {
        return $this->UjMuszakJog;
    }
}