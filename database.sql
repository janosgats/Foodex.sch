-- Adminer 4.6.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `ertekelesek`;
CREATE TABLE `ertekelesek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ertekelo` varchar(120) COLLATE latin2_hungarian_ci NOT NULL,
  `ertekelt` varchar(120) COLLATE latin2_hungarian_ci NOT NULL,
  `muszid` bigint(20) DEFAULT NULL,
  `e_szoveg` text COLLATE latin2_hungarian_ci,
  `e_pontossag` float DEFAULT NULL,
  `e_penzkezeles` float DEFAULT NULL,
  `e_szakertelem` float DEFAULT NULL,
  `e_dughatosag` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ertekelo_ertekelt_muszid` (`ertekelo`,`ertekelt`,`muszid`),
  KEY `muszid` (`muszid`),
  CONSTRAINT `ertekelesek_ibfk_1` FOREIGN KEY (`muszid`) REFERENCES `fxmuszakok` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `fxaccok`;
CREATE TABLE `fxaccok` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `internal_id` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL COMMENT 'AuthSch internal_id',
  `nev` text CHARACTER SET latin2 COLLATE latin2_hungarian_ci COMMENT 'AuthSch DisplayName',
  `fxtag` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Fx kortag PEK szerint',
  `adminjog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Admin',
  `muszjeljog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Muszakra jelentkezhet',
  `pontlatjog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Lathatja masok pontszamat',
  `email` text CHARACTER SET latin2 COLLATE latin2_hungarian_ci,
  `session_token` varchar(124) CHARACTER SET latin2 COLLATE latin2_hungarian_ci DEFAULT NULL COMMENT 'Bejelentkezett session token',
  PRIMARY KEY (`ID`),
  KEY `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `fxjelentk`;
CREATE TABLE `fxjelentk` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `jelentkezo` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL COMMENT 'A jelentkezo ember internal_id-je ',
  `muszid` bigint(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1: jelentkezve, 0: lejelentkezve',
  `jelido` datetime DEFAULT NULL,
  `leadido` datetime DEFAULT NULL,
  `mosogat` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: mosogatott, 0: nem mosogatott',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `ID_2` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `fxmuszakok`;
CREATE TABLE `fxmuszakok` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `kiirta` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci DEFAULT NULL COMMENT 'A muszak kiirojanak internal_id-je',
  `musznev` varchar(250) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL,
  `korID` bigint(20) DEFAULT NULL,
  `aktiv` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0: inaktiv, 1: aktiv',
  `idokezd` datetime DEFAULT NULL,
  `idoveg` datetime DEFAULT NULL,
  `letszam` int(11) NOT NULL DEFAULT '1',
  `pont` float NOT NULL DEFAULT '2',
  `mospont` float NOT NULL DEFAULT '0.5',
  `megj` varchar(250) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL COMMENT 'megjegyzes',
  PRIMARY KEY (`ID`),
  KEY `korID` (`korID`),
  CONSTRAINT `fxmuszakok_ibfk_1` FOREIGN KEY (`korID`) REFERENCES `korok` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `globalsettings`;
CREATE TABLE `globalsettings` (
  `nev` varchar(200) COLLATE latin2_hungarian_ci NOT NULL,
  `ertek` text COLLATE latin2_hungarian_ci NOT NULL,
  PRIMARY KEY (`nev`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `kompenz`;
CREATE TABLE `kompenz` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `internal_id` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL,
  `pont` float NOT NULL DEFAULT '0',
  `megj` text CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL COMMENT 'megjegyzes',
  `ido` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `korertekelok`;
CREATE TABLE `korertekelok` (
  `ertekelo` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL,
  `korid` bigint(20) NOT NULL,
  UNIQUE KEY `ertekelo_korid` (`ertekelo`,`korid`),
  KEY `korid` (`korid`),
  CONSTRAINT `korertekelok_ibfk_1` FOREIGN KEY (`korid`) REFERENCES `korok` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `korertekelok_ibfk_2` FOREIGN KEY (`ertekelo`) REFERENCES `fxaccok` (`internal_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `korok`;
CREATE TABLE `korok` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nev` varchar(220) COLLATE latin2_hungarian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `channel` text COLLATE latin2_hungarian_ci NOT NULL,
  `message` text COLLATE latin2_hungarian_ci NOT NULL,
  `context` text COLLATE latin2_hungarian_ci NOT NULL,
  `level` int(11) NOT NULL,
  `level_name` text COLLATE latin2_hungarian_ci NOT NULL,
  `extra` text COLLATE latin2_hungarian_ci NOT NULL,
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `pontjeldelay`;
CREATE TABLE `pontjeldelay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `minpont` float NOT NULL,
  `delay` int(11) NOT NULL COMMENT 'sec',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `profilinfo`;
CREATE TABLE `profilinfo` (
  `int_id` varchar(120) COLLATE latin2_hungarian_ci NOT NULL,
  `kedv_vicc` text COLLATE latin2_hungarian_ci COMMENT 'Kedvenc vicc',
  PRIMARY KEY (`int_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


-- 2019-06-02 13:11:49
