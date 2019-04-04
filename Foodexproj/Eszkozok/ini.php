<?php


namespace Eszkozok
{
    class GlobalServerInitParams
    {
        /**
         *Írd át a $RequireAuth értékét false-ra, ez után írd be egy tetszőleges belső olda url-jét (pl. hostcím/profil).
         *Így ha nem vagy bejelentkezve, a szerver a $DefaultIntID -vel be fog léptetni (Auth SCH-s átirányítás nélkül).
         **/
        public static $RequireAuth = true; //DEFAULT: true: csak bejelentkezett felhasználók láthatják a belsős oldalakat.
        public static $DefaultIntID = '020157b0-70a7-9315-a174-4dfd0602c5cf';//Használt internal ID (Auth SCH-s), ha a $RequireAuth paraméter false. (Kijelentkezés után frissül)
        //public static $DefaultIntID = 't1';
        //public static $DefaultIntID = 't2';
        //public static $DefaultIntID = '5df058f7-d76d-4b03-e9c8-65a78b27fb7e';

        public static $DevloginEnabled = true;
    }
}