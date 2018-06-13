<?php


namespace Profil;


class Profil
{
    private $InternalID = '';
    private $Nev = '';
    private $UjMuszakJog = 0;

    public function __construct($internalid, $nev, $ujmuszakjog)
    {
        $this->InternalID = $internalid;
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

    public function getInternalID()
    {
        return $this->InternalID;
    }
}