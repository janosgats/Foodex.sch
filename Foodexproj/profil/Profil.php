<?php


namespace Profil;


class Profil
{
    private $InternalID = '';
    private $Nev = '';
    private $UjMuszakJog = 0;
    private $Email = '';

    public function __construct($internalid, $nev, $ujmuszakjog, $email)
    {
        $this->InternalID = $internalid;
        $this->Nev = $nev;
        $this->UjMuszakJog = $ujmuszakjog;
        $this->Email = $email;
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
    public function getEmail()
    {
        return $this->Email;
    }
}