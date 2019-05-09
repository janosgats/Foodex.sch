<?php


namespace Profil;


class Profil
{
    private $InternalID = '';
    private $Nev = '';
    private $AdminJog = 0;
    private $Email = '';

    public function __construct($internalid, $nev, $adminjog, $email)
    {
        $this->InternalID = $internalid;
        $this->Nev = $nev;
        $this->AdminJog = $adminjog;
        $this->Email = $email;
    }

    public function getNev()
    {
        return $this->Nev;
    }

    public function getAdminJog()
    {
        return $this->AdminJog;
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