SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE DATABASE `usuiusa0_shipverify` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `usuiusa0_shipverify`;

CREATE TABLE IF NOT EXISTS `AccessLevels` (
  `StatusID` int(11) NOT NULL AUTO_INCREMENT,
  `Status` varchar(30) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`StatusID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `BarcodeDefinitions` (
  `PartId` int(11) NOT NULL,
  `Size` int(11) NOT NULL DEFAULT '1',
  `BarcodeText` varchar(30) NOT NULL,
  `HRIText` varchar(40) NOT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  `CreatedDate` datetime NOT NULL,
  `LastModifiedDate` datetime NOT NULL,
  `UserId` int(11) NOT NULL,
  PRIMARY KEY (`PartId`),
  KEY `PartId` (`PartId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='This table should be used to store Barcode label templates f';

CREATE TABLE IF NOT EXISTS `CustomerList` (
  `Customerid` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerCode` varchar(50) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`Customerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

CREATE TABLE IF NOT EXISTS `PartRegex` (
  `PlexRegexID` int(11) NOT NULL AUTO_INCREMENT,
  `PartID` int(11) NOT NULL,
  `RegEx` varchar(255) DEFAULT NULL,
  `RegexID` int(11) NOT NULL,
  UNIQUE KEY `PlexRegexID` (`PlexRegexID`),
  KEY `PlexRegexID_2` (`PlexRegexID`),
  KEY `PlexRegexID_3` (`PlexRegexID`),
  KEY `PlexRegexID_4` (`PlexRegexID`),
  KEY `PartID` (`PartID`),
  KEY `RegexID` (`RegexID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

CREATE TABLE IF NOT EXISTS `PlexInvalidEntryLog` (
  `PlexInvalidEntryID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `PartInfo` varchar(255) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `ErrorReason` varchar(255) NOT NULL,
  PRIMARY KEY (`PlexInvalidEntryID`),
  KEY `PlexInvalidEntryID` (`PlexInvalidEntryID`),
  KEY `PlexInvalidEntryID_2` (`PlexInvalidEntryID`),
  KEY `PlexRecord` (`PlexRecord`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=222 ;

CREATE TABLE IF NOT EXISTS `PlexPartAlternatives` (
  `PlexPartAltID` int(11) NOT NULL AUTO_INCREMENT,
  `MainPartID` int(11) NOT NULL,
  `CorrespondingPartID` int(11) NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `InstantiateDate` datetime NOT NULL,
  `RetireDate` datetime DEFAULT NULL,
  PRIMARY KEY (`PlexPartAltID`),
  KEY `PlexPartAltID` (`PlexPartAltID`),
  KEY `PlexPartAltID_2` (`PlexPartAltID`),
  KEY `MainPartID` (`MainPartID`),
  KEY `CorrespondingPartID` (`CorrespondingPartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

CREATE TABLE IF NOT EXISTS `PlexPartErrors` (
  `PlexPartErrorID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `FixUser` int(11) NOT NULL,
  `FixTime` datetime NOT NULL,
  `ErrorPartNum` varchar(255) DEFAULT NULL,
  `ErrorPartSerialNum` varchar(255) NOT NULL,
  `ErrorPartID` int(11) NOT NULL,
  `Match_` tinyint(1) NOT NULL,
  `Duplicate` tinyint(1) NOT NULL,
  PRIMARY KEY (`PlexPartErrorID`),
  KEY `PlexPartErrorID` (`PlexPartErrorID`),
  KEY `PlexRecord` (`PlexRecord`),
  KEY `ErrorPartID` (`ErrorPartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=615 ;

CREATE TABLE IF NOT EXISTS `PlexPartRecords` (
  `PlexPartRecordID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `PartNum` varchar(255) DEFAULT NULL,
  `PartSerialNum` varchar(255) NOT NULL,
  `PartID` int(11) NOT NULL,
  `Match_` tinyint(1) NOT NULL,
  `Duplicate` tinyint(1) NOT NULL,
  PRIMARY KEY (`PlexPartRecordID`),
  KEY `PlexPartRecordID` (`PlexPartRecordID`),
  KEY `PlexRecord` (`PlexRecord`),
  KEY `PartID` (`PartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=78259 ;

CREATE TABLE IF NOT EXISTS `PlexParts` (
  `PartID` int(11) NOT NULL AUTO_INCREMENT,
  `PartNum` varchar(255) NOT NULL,
  `PartAdded` datetime NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `PartRetired` datetime DEFAULT NULL,
  `IntPartNum` tinyint(1) NOT NULL,
  PRIMARY KEY (`PartID`),
  KEY `PartID` (`PartID`),
  KEY `PartID_2` (`PartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=126 ;

CREATE TABLE IF NOT EXISTS `PlexRecordErrors` (
  `PlexRecordErrorID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `FixUser` int(11) NOT NULL,
  `FixTime` datetime NOT NULL,
  `ErrorPartNum` varchar(255) NOT NULL,
  `ErrorQTY` int(11) NOT NULL,
  `ErrorPartID` int(11) NOT NULL,
  PRIMARY KEY (`PlexRecordErrorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `PlexRecords` (
  `PlexRecordID` int(11) NOT NULL AUTO_INCREMENT,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `PlexSerialNum` varchar(255) NOT NULL,
  `PartNum` varchar(255) NOT NULL,
  `PartQty` int(11) NOT NULL,
  `PartID` int(11) NOT NULL,
  `Verified` tinyint(1) NOT NULL,
  `Locked` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`PlexRecordID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2273 ;

CREATE TABLE IF NOT EXISTS `PlexVerification` (
  `PlexVerificationID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `VerificationUser` int(11) NOT NULL,
  `PrintTime` datetime NOT NULL,
  PRIMARY KEY (`PlexVerificationID`),
  KEY `PlexVerificationID` (`PlexVerificationID`),
  KEY `PlexRecord` (`PlexRecord`),
  KEY `VerificationUser` (`VerificationUser`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2194 ;

CREATE TABLE IF NOT EXISTS `Regex` (
  `RegexID` int(11) NOT NULL AUTO_INCREMENT,
  `RegularExpression` varchar(255) NOT NULL,
  UNIQUE KEY `RegexID` (`RegexID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

CREATE TABLE IF NOT EXISTS `ShipTo` (
  `ShipToID` int(11) NOT NULL AUTO_INCREMENT,
  `ShipToName` varchar(30) NOT NULL,
  `ShipLoc` varchar(30) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  PRIMARY KEY (`ShipToID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

CREATE TABLE IF NOT EXISTS `ShippingPartErrors` (
  `ShippingPartErrorID` int(11) NOT NULL AUTO_INCREMENT,
  `ShippingRecordID` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `FixUser` int(11) NOT NULL,
  `FixTime` datetime NOT NULL,
  `ErrorPartNum` varchar(255) DEFAULT NULL,
  `ErrorPartSerialNum` varchar(255) NOT NULL,
  `ErrorPartID` int(11) NOT NULL,
  `Match_` tinyint(1) NOT NULL,
  `Duplicate` tinyint(1) NOT NULL,
  `Valid` tinyint(1) NOT NULL,
  PRIMARY KEY (`ShippingPartErrorID`),
  KEY `ShippingPartErrorID` (`ShippingPartErrorID`),
  KEY `ShippingRecordID` (`ShippingRecordID`),
  KEY `ErrorPartID` (`ErrorPartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=336 ;

CREATE TABLE IF NOT EXISTS `ShippingRecordErrors` (
  `ShippingRecordErrorID` int(11) NOT NULL AUTO_INCREMENT,
  `ShippingRecordID` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `FixUser` int(11) NOT NULL,
  `FixTime` datetime NOT NULL,
  `ErrorPartNum` varchar(255) NOT NULL,
  `ErrorQTY` int(11) NOT NULL,
  `ErrorPartID` int(11) NOT NULL,
  PRIMARY KEY (`ShippingRecordErrorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ShippingRecordParts` (
  `ShippingRecordPartID` int(11) NOT NULL AUTO_INCREMENT,
  `ShippingRecordID` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `PartNum` varchar(255) DEFAULT NULL,
  `PartSerialNum` varchar(255) NOT NULL,
  `PartID` int(11) NOT NULL,
  `Match_` tinyint(1) NOT NULL,
  `Duplicate` tinyint(1) NOT NULL,
  `Valid` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ShippingRecordPartID`),
  KEY `ShippingRecordPartID` (`ShippingRecordPartID`),
  KEY `ShippingRecordID` (`ShippingRecordID`),
  KEY `ScanUser` (`ScanUser`),
  KEY `PartID` (`PartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10672 ;

CREATE TABLE IF NOT EXISTS `ShippingRecords` (
  `ShippingRecordID` int(11) NOT NULL AUTO_INCREMENT,
  `PlexRecord` int(11) NOT NULL,
  `ScanUser` int(11) NOT NULL,
  `ScanTime` datetime NOT NULL,
  `PartNum` varchar(255) NOT NULL,
  `PartQty` int(11) NOT NULL,
  `PartID` int(11) NOT NULL,
  `Verified` tinyint(1) NOT NULL,
  `Locked` tinyint(1) NOT NULL,
  PRIMARY KEY (`ShippingRecordID`),
  KEY `ShippingRecordID` (`ShippingRecordID`),
  KEY `PlexRecord` (`PlexRecord`),
  KEY `ScanUser` (`ScanUser`),
  KEY `PartID` (`PartID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2054 ;

CREATE TABLE IF NOT EXISTS `ShippingVerification` (
  `ShippingVerificationID` int(11) NOT NULL AUTO_INCREMENT,
  `ShippingRecordID` int(11) NOT NULL,
  `VerificationUser` int(11) NOT NULL,
  `PrintTime` datetime NOT NULL,
  PRIMARY KEY (`ShippingVerificationID`),
  KEY `ShippingVerificationID` (`ShippingVerificationID`),
  KEY `ShippingRecordID` (`ShippingRecordID`),
  KEY `VerificationUser` (`VerificationUser`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2065 ;

CREATE TABLE IF NOT EXISTS `UserAccess` (
  `UserAccessID` int(11) NOT NULL AUTO_INCREMENT,
  `StatusID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  PRIMARY KEY (`UserAccessID`),
  KEY `UserAccessID` (`UserAccessID`),
  KEY `StatusID` (`StatusID`),
  KEY `UserID` (`UserID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;

CREATE TABLE IF NOT EXISTS `UserDB` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `PWD` varchar(64) CHARACTER SET utf8 NOT NULL,
  `salt` varchar(16) NOT NULL,
  `login` varchar(16) NOT NULL,
  `first_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `Active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserID` (`UserID`),
  KEY `UserID_2` (`UserID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
