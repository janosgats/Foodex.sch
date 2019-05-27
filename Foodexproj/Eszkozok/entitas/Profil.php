<?php


namespace Profil;


class Profil
{
    private $ID = '';
    private $InternalID = '';
    private $Nev = '';
    private $FxTag = 0;
    private $AdminJog = 0;
    private $MuszJelJog = 0;
    private $PontLatJog = 0;
    private $Email = '';
    private $SessionToken = 'NINCS';

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
    public function getFxTag()
    {
        return $this->FxTag;
    }

    public function getAdminJog()
    {
        return $this->AdminJog;
    }
    public function GetMuszJelJog()
    {
        return $this->MuszJelJog;
    }
    public function GetPontLatJog()
    {
        return $this->PontLatJog;
    }
    public function getEmail()
    {
        return $this->Email;
    }
    public function getSessionToken()
    {
            return (string)($this->SessionToken);
    }

    /**
     * @param string $ID
     */
    public function setID($ID)
    {
        $this->ID = $ID;
    }

    /**
     * @param int $FxTag
     */
    public function setFxTag($FxTag)
    {
        $this->FxTag = $FxTag;
    }

    /**
     * @param string $InternalID
     */
    public function setInternalID($InternalID)
    {
        $this->InternalID = $InternalID;
    }

    /**
     * @param string $Nev
     */
    public function setNev($Nev)
    {
        $this->Nev = $Nev;
    }

    /**
     * @param int $AdminJog
     */
    public function setAdminJog($AdminJog)
    {
        $this->AdminJog = $AdminJog;
    }

    /**
     * @param int $MuszJelJog
     */
    public function setMuszJelJog($MuszJelJog)
    {
        $this->MuszJelJog = $MuszJelJog;
    }

    /**
     * @param string $Email
     */
    public function setEmail($Email)
    {
        $this->Email = $Email;
    }

    /**
     * @param string $SessionToken
     */
    public function setSessionToken($SessionToken)
    {
        $this->SessionToken = $SessionToken;
    }

    /**
     * @param int $PontLatJog
     */
    public function setPontLatJog($PontLatJog)
    {
        $this->PontLatJog = $PontLatJog;
    }
}