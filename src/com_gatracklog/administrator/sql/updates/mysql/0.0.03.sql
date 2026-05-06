ALTER TABLE `#__gatracklog_tracklogs` ADD `season_close` INT(11) NOT NULL AFTER `rating`;
ALTER TABLE `#__gatracklog_tracklogs` ADD `track_zone` INT(11) NOT NULL AFTER `rating`;

CREATE TABLE IF NOT EXISTS `#__gatracklog_trackcomments` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`created_date` DATETIME NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`update_date` DATETIME NOT NULL ,
`user_id` INT(11)  NOT NULL ,
`track_id` INT(11)  NOT NULL ,
`comment` TEXT NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `content_history_options`)
SELECT * FROM ( SELECT 'Trackcomment','com_gatracklog.trackcomment','{"special":{"dbtable":"#__gatracklog_trackcomments","key":"id","type":"Trackcomment","prefix":"Track CommentTable"}}', '{"formFile":"administrator\/components\/com_gatracklog\/models\/forms\/trackcomment.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gatracklog.trackcomment')
) LIMIT 1;
