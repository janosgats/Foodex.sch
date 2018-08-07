<?php

namespace Eszkozok
{
    class GlobalServerInitParams
    {
        /**
         *Írd át a $RequireAuth értékét false-ra, ez után írd be egy tetszőleges belső olda url-jét (pl. hostcím/profil).
         *Így ha nem vagy bejelentkezve, a szerver a $DefaultIntID -vel be fog léptetni (Auth SCH-s átirányítás nélkül).
         **/
        public static $RequireAuth = false; //true: csak bejelentkezett felhasználók láthatják a belsős oldalakat.
        public static $DefaultIntID = '4cbdfb46-5553-92c6-8536-e1d58c85cf76';//Használt internal ID (Auth SCH-s), ha a $RequireAuth paraméter false. (Kijelentkezés után frissül)
    }
}