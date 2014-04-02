/*
 * Create Tables
 */

CREATE TABLE `tests_permissions` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Lft` INTEGER NOT NULL,
  `Rght` INTEGER NOT NULL,
  `Title` char(64) NOT NULL,
  `Description` text NOT NULL
);

CREATE TABLE `tests_rolepermissions` (
  `RoleID` INTEGER NOT NULL,
  `PermissionID` INTEGER NOT NULL,
  `AssignmentDate` INTEGER NOT NULL,
  PRIMARY KEY  (`RoleID`,`PermissionID`)
);

CREATE TABLE `tests_roles` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Lft` INTEGER NOT NULL,
  `Rght` INTEGER NOT NULL,
  `Title` varchar(128) NOT NULL,
  `Description` text NOT NULL
);

CREATE TABLE `tests_userroles` (
  `UserID` INTEGER NOT NULL,
  `RoleID` INTEGER NOT NULL,
  `AssignmentDate` INTEGER NOT NULL,
  PRIMARY KEY  (`UserID`,`RoleID`)
);

/*
 * Insert Initial Table Data
 */

INSERT INTO `tests_permissions` (`ID`, `Lft`, `Rght`, `Title`, `Description`)
VALUES (1, 0, 1, 'root', 'root');

INSERT INTO `tests_rolepermissions` (`RoleID`, `PermissionID`, `AssignmentDate`)
VALUES (1, 1, strftime('%s', 'now'));

INSERT INTO `tests_roles` (`ID`, `Lft`, `Rght`, `Title`, `Description`)
VALUES (1, 0, 1, 'root', 'root');

INSERT INTO `tests_userroles` (`UserID`, `RoleID`, `AssignmentDate`)
VALUES (1, 1, strftime('%s', 'now'));
