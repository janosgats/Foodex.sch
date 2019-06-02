<?php
/**
 * Created by PhpStorm.
 * User: gatsj
 * Date: 2019. 06. 02.
 * Time: 9:25
 */

namespace Eszkozok;


class Ertekeles
{
    public function __construct($DB_row = null)
    {
        if ($DB_row != null)
        {
            $this->ID = $DB_row['id'];
            $this->ertekelo = $DB_row['ertekelo'];
            $this->ertekelt = $DB_row['ertekelt'];
            $this->muszid = $DB_row['muszid'];
            $this->e_szoveg = $DB_row['e_szoveg'];
            $this->e_pontossag = $DB_row['e_pontossag'];
            $this->e_penzkezeles = $DB_row['e_penzkezeles'];
            $this->e_szakertelem = $DB_row['e_szakertelem'];
            $this->e_dughatosag = $DB_row['e_dughatosag'];
        }
    }

    public $ID = null;
    public $ertekelo = null;
    public $ertekelt = null;
    public $muszid = null;
    public $e_szoveg = null;
    public $e_pontossag = null;
    public $e_penzkezeles = null;
    public $e_szakertelem = null;
    public $e_dughatosag = null;
}