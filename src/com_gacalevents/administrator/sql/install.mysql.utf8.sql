CREATE TABLE IF NOT EXISTS `#__gacalevents_events` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT  NOT NULL DEFAULT 0,
`state` TINYINT  NOT NULL DEFAULT 1,
`checked_out` INT  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`cat_id` INT  NOT NULL DEFAULT 0,
`title` VARCHAR(255) NULL,
`brief_desc` VARCHAR(255) NULL,
`event_details` TEXT NULL ,
`inc_mod` TINYINT  NOT NULL DEFAULT 1,
`formal_event` TINYINT  NOT NULL DEFAULT 0,
`leader` VARCHAR(255) NULL,
`depart_point` INT  NOT NULL DEFAULT 0,
`depart_date` DATE NULL,
`return_date` DATE NULL,
`start_time` TIME NOT NULL DEFAULT "00:00:00",
`end_time` TIME NOT NULL DEFAULT "00:00:00",
`max_attend` INT  NOT NULL DEFAULT 0,
`allow_pub` TINYINT  NOT NULL DEFAULT 0,
`mbr_cost` DECIMAL(11,2)  NOT NULL DEFAULT "0.00",
`pub_cost` DECIMAL(11,2)  NOT NULL DEFAULT "0.00",
`comment` TEXT NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Event','com_gacalevents.event','{"special":{"dbtable":"#__gacalevents_events","key":"id","type":"EventTable","prefix":"GlennArkell\\\\Component\\\\Gacalevents\\\\Administrator\\\\Table\\\\"}}', CASE
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
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT  NOT NULL DEFAULT 0,
`state` TINYINT  NOT NULL DEFAULT 1,
`checked_out` INT  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`attendee` INT  NOT NULL DEFAULT 0,
`event` INT  NOT NULL DEFAULT 0,
`att_cat` INT  NOT NULL DEFAULT 0,
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
`paid` TINYINT  NOT NULL DEFAULT 0,
`paid_amt` DECIMAL(11,2)  NOT NULL DEFAULT '0.00',
`qty_att` INT  NOT NULL DEFAULT 1,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

