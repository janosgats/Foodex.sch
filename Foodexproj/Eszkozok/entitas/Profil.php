<?php


namespace Profil;


class Profil
{
    private $ID = '';
    private $InternalID = '';
    private $Nev = '';
    private $AdminJog = 0;
    private $MuszJelJog = 0;
    private $Email = '';
    private $SessionToken = 'NINCS';

    public function __construct($internalid, $nev, $adminjog, $email)
    {
        $this->InternalID = $internalid;
        $this->Nev = $nev;
        $this->AdminJog = $adminjog;
        $this->Email = $email;
    }

    public function GetID()
    {
        return $this->ID;
    }

    public function getInternalID()
    {
        return $this->InternalID;
    }

    public function getNev()
    {
        return $this->Nev;
    }

    public function getAdminJog()
    {
        return $this->AdminJog;
    }
    public function GetMuszJelJog()
    {
        return $this->MuszJelJog;
    }
    public function getEmail()
    {
        return $this->Email;
    }
    public function getSessionToken()
    {
            return (string)($this->SessionToken);
    }
}