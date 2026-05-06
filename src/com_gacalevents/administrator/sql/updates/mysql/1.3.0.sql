ALTER TABLE `#__gacalevents_events` CHANGE `state` `state` TINYINT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `ordering` `ordering` INT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `checked_out` `checked_out` INT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `created_by` `created_by` INT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `created_date` `created_date` DATETIME NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `modified_by` `modified_by` INT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `modified_date` `modified_date` DATETIME NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `comment` `comment` TEXT NULL;

ALTER TABLE `#__gacalevents_events` CHANGE `title` `title` VARCHAR(255) NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `brief_desc` `brief_desc` VARCHAR(255) NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `event_details` `event_details` TEXT NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `inc_mod` `inc_mod` TINYINT NOT NULL DEFAULT 1;
ALTER TABLE `#__gacalevents_events` CHANGE `formal_event` `formal_event` TINYINT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `leader` `leader` VARCHAR(255) NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `depart_point` `depart_point` INT NOT NULL DEFAULT 0;
ALTER TABLE `#__gacalevents_events` CHANGE `depart_date` `depart_date` DATETIME NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `return_date` `return_date` DATETIME NULL;
ALTER TABLE `#__gacalevents_events` CHANGE `allow_pub` `allow_pub` TINYINT NOT NULL DEFAULT 0;

ALTER TABLE `#__gacalevents_attendees` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL;
ALTER TABLE `#__gacalevents_attendees` CHANGE `created_date` `created_date` DATETIME NULL;
ALTER TABLE `#__gacalevents_attendees` CHANGE `modified_date` `modified_date` DATETIME NULL;

UPDATE `#__gacalevents_events` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
UPDATE `#__gacalevents_events` SET `created_date` = NULL WHERE `created_date` = '0000-00-00 00:00:00';
UPDATE `#__gacalevents_events` SET `modified_date` = NULL WHERE `modified_date` = '0000-00-00 00:00:00';
UPDATE `#__gacalevents_attendees` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
UPDATE `#__gacalevents_attendees` SET `created_date` = NULL WHERE `created_date` = '0000-00-00 00:00:00';
UPDATE `#__gacalevents_attendees` SET `modified_date` = NULL WHERE `modified_date` = '0000-00-00 00:00:00';
