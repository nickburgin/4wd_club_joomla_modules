CREATE TABLE IF NOT EXISTS `#__gatracklog_tracklogs` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`name` VARCHAR(255) NULL ,
`rating` INT(11)  NOT NULL DEFAULT 0,
`track_zone` INT(11)  NOT NULL DEFAULT 0,
`season_close` INT(11)  NOT NULL DEFAULT 0,
`file_format` VARCHAR(255) NULL ,
`trklog` TEXT NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gatracklog_trackcomments` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`user_id` INT(11)  NOT NULL DEFAULT 0,
`track_id` INT(11)  NOT NULL DEFAULT 0,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Tracklog','com_gatracklog.tracklog','{"special":{"dbtable":"#__gatracklog_tracklogs","key":"id","type":"TracklogTable","prefix":"GlennArkell\\\\Component\\\\Gatracklog\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gatracklog\/forms\/tracklog.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gatracklog.tracklog')
) LIMIT 1;

