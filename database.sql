-- Adminer 4.6.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `fxaccok`;
CREATE TABLE `fxaccok` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `internal_id` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci DEFAULT NULL COMMENT 'AuthSch internal_id',
  `nev` text CHARACTER SET latin2 COLLATE latin2_hungarian_ci COMMENT 'AuthSch DisplayName',
  `fxtag` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Fx kortag PEK szerint',
  `adminjog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Admin',
  `muszjeljog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Muszakra jelentkezhet',
  `pontlatjog` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Lathatja masok pontszamat',
  `email` text CHARACTER SET latin2 COLLATE latin2_hungarian_ci,
  `session_token` varchar(124) CHARACTER SET latin2 COLLATE latin2_hungarian_ci DEFAULT NULL COMMENT 'Bejelentkezett session token',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `internal_id` (`internal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `fxjelentk`;
CREATE TABLE `fxjelentk` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `jelentkezo` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL COMMENT 'A jelentkezo ember internal_id-je ',
  `muszid` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1: jelentkezve, 0: lejelentkezve',
  `jelido` datetime DEFAULT NULL,
  `leadido` datetime DEFAULT NULL,
  `mosogat` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1: mosogatott, 0: nem mosogatott',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `ID_2` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=606 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `fxmuszakok`;
CREATE TABLE `fxmuszakok` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `kiirta` varchar(120) CHARACTER SET latin2 COLLATE latin2_hungarian_ci DEFAULT NULL COMMENT 'A muszak kiirojanak internal_id-je',
  `musznev` varchar(250) CHARACTER SET latin2 COLLATE latin2_hungarian_ci NOT NULL,
  `korID` int(11) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=latin1;


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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `korok`;
CREATE TABLE `korok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(220) COLLATE latin2_hungarian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=891 DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `pontjeldelay`;
CREATE TABLE `pontjeldelay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `minpont` float NOT NULL,
  `delay` int(11) NOT NULL COMMENT 'sec',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


DROP TABLE IF EXISTS `profilinfo`;
CREATE TABLE `profilinfo` (
  `int_id` varchar(120) COLLATE latin2_hungarian_ci NOT NULL,
  `kedv_vicc` text COLLATE latin2_hungarian_ci COMMENT 'Kedvenc vicc',
  PRIMARY KEY (`int_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_hungarian_ci;


-- 2019-05-27 20:42:26
