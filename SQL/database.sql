CREATE TABLE `tItems` (
   `tagid` VARCHAR(16) NOT NULL,
   `keyid` VARCHAR(64) UNIQUE NOT NULL,
   `description` VARCHAR(255) NOT NULL DEFAULT 'No data available',
   `image` BLOB NOT NULL DEFAULT 'data:image/png;base64,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',
   PRIMARY KEY (`tagid` ),
   CONSTRAINT CHK_tagid CHECK (LENGTH(tagid)>4),
   CONSTRAINT CHK_keyid CHECK (LENGTH(keyid)=64),
   CONSTRAINT CHK_description CHECK (LENGTH(description)>0),
   CONSTRAINT CHK_image CHECK (image LIKE 'data:image/%')
) 

CREATE TABLE `tUsers` (
  `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL
);

CREATE TABLE `tItems_History` (
   `histid` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   `dateTime` timestamp NOT NULL DEFAULT current_timestamp(),
   `tagid` VARCHAR(16) NOT NULL,
   `keyid` VARCHAR(64) NOT NULL,
   `description` VARCHAR(255) NOT NULL,
   `image` BLOB NOT NULL
)

CREATE TABLE `tScanEvents` (
  `eventId` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `dateTime` timestamp NOT NULL
  `tItems_tagid` VARCHAR(16) NOT NULL,
  `sourceIP` VARCHAR(40) NOT NULL,
  `clientData` BLOB,
  `acknowledge` TINYINT(1) NOT NULL DEFAULT 0
)

CREATE TRIGGER `SELF_UPDATE_HISTORY` BEFORE UPDATE ON `tItems` FOR EACH ROW BEGIN INSERT INTO tItems_History (tagid, keyid, description, image) SELECT tagid, keyid, description, image FROM tItems WHERE tagid = OLD.tagid; END
CREATE TRIGGER `SELF_DELETE_HISTORY` BEFORE DELETE ON `tItems` FOR EACH ROW BEGIN INSERT INTO tItems_History (tagid, keyid, description, image) SELECT tagid, keyid, description, image FROM tItems WHERE tagid = OLD.tagid; END;
