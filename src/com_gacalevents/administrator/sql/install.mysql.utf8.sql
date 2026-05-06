CREATE TABLE IF NOT EXISTS `#__gacalevents_events` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`title` VARCHAR(255)  NOT NULL DEFAULT "",
`brief_desc` VARCHAR(255)  NOT NULL DEFAULT "",
`event_details` TEXT NULL ,
`inc_mod` TINYINT(1)  NOT NULL DEFAULT 1,
`formal_event` TINYINT(1)  NOT NULL DEFAULT 0,
`leader` VARCHAR(255)  NOT NULL DEFAULT "",
`depart_point` INT(11)  NOT NULL DEFAULT 0,
`depart_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`return_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`start_time` TIME NOT NULL DEFAULT "00:00:00",
`end_time` TIME NOT NULL DEFAULT "00:00:00",
`max_attend` INT(11)  NOT NULL DEFAULT 0,
`allow_pub` TINYINT(1)  NOT NULL DEFAULT 1,
`mbr_cost` DECIMAL(11,2)  NOT NULL DEFAULT "0.00",
`pub_cost` DECIMAL(11,2)  NOT NULL DEFAULT "0.00",
`comment` TEXT NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Event','com_gacalevents.event','{"special":{"dbtable":"#__gacalevents_events","key":"id","type":"EventTable","prefix":"GlennArkell\\\\Component\\\\Gatreasury\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gacalevents\/forms\/event.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"cat_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gacalevents.event')
) LIMIT 1;

CREATE TABLE IF NOT EXISTS `#__gacalevents_attendees` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`attendee` INT(11)  NOT NULL DEFAULT 0,
`event` INT(11)  NOT NULL DEFAULT 0,
`att_cat` INT(11)  NOT NULL DEFAULT 0,
`pub_title` VARCHAR(255)  NULL ,
`pub_name` VARCHAR(255)  NULL ,
`pub_fname` VARCHAR(255)  NULL ,
`pub_sname` VARCHAR(255)  NULL ,
`pub_partner` VARCHAR(255)  NULL ,
`position` VARCHAR(255)  NULL ,
`pub_address1` VARCHAR(255)  NULL ,
`pub_address2` VARCHAR(255)  NULL ,
`pub_address3` VARCHAR(255)  NULL ,
`pub_suburb` VARCHAR(255)  NULL ,
`pub_state` VARCHAR(255)  NULL ,
`pub_pcode` VARCHAR(255)  NULL ,
`pub_email` VARCHAR(255)  NULL ,
`pub_phone` VARCHAR(255)  NULL ,
`pub_from` VARCHAR(255)  NULL ,
`paid` TINYINT(1)  NOT NULL DEFAULT 0,
`paid_amt` DECIMAL(11,2)  NOT NULL DEFAULT '0.00',
`qty_att` INT(11)  NOT NULL DEFAULT 1,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

