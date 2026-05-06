CREATE TABLE IF NOT EXISTS `#__gatripsys_trips` (
`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` int  NOT NULL DEFAULT 0,
`state` tinyint(1)  NOT NULL DEFAULT 1,
`checked_out` int  NOT NULL DEFAULT 0,
`checked_out_time` datetime NULL,
`created_by` int  NOT NULL DEFAULT 0,
`created_date` datetime NULL,
`modified_by` int  NOT NULL DEFAULT 0,
`modified_date` datetime NULL,
`title` varchar(255)  NOT NULL DEFAULT 'No Title Set',
`rating` int  NOT NULL DEFAULT 0,
`leader` varchar(255)  NOT NULL DEFAULT 'TBC',
`trip_tec` int  NOT NULL DEFAULT 0,
`suited_for` int  NOT NULL DEFAULT 0,
`trip_type` int  NOT NULL DEFAULT 0,
`trip_img` varchar(255)  NULL,
`trip_plan` varchar(255)  NULL,
`insurance_cat` int  NOT NULL DEFAULT 0,
`regby_date` date NULL,
`min_no` int  NOT NULL DEFAULT 0,
`max_no` int  NOT NULL DEFAULT 0,
`dept_date` date NULL,
`ret_date` date NULL,
`dept_loc` varchar(255)  NOT NULL DEFAULT 'No Departure Set',
`ret_loc` varchar(255)  NOT NULL DEFAULT 'No Return Set',
`equip` text  NULL ,
`where_go` text NULL ,
`what_do` text NULL ,
`max_people` int  NOT NULL DEFAULT 0,
`details` text NULL ,
`public_view` tinyint(1)  NOT NULL DEFAULT 1,
`trip_cost` decimal(11,2) NOT NULL DEFAULT '0.00',
`comment` text NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT intO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Trip','com_gatripsys.trip','{"special":{"dbtable":"#__gatripsys_trips","key":"id","type":"TripTable","prefix":"Joomla\\\\Component\\\\Gatripsys\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gatripsys\/forms\/trip.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gatripsys.trip')
) LIMIT 1;

INSERT intO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
SELECT * FROM ( SELECT 'Trip Category','com_gatripsys.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":   {"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '','Treasury SystemRouter::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}') AS tmp WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gatripsys.category')
) LIMIT 1;

CREATE TABLE IF NOT EXISTS `#__gatripsys_attendees` (
`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` int  NOT NULL DEFAULT 0,
`state` tinyint(1)  NOT NULL DEFAULT 1,
`checked_out` int  NOT NULL DEFAULT 0,
`checked_out_time` datetime NULL,
`created_by` int  NOT NULL DEFAULT 0,
`created_date` datetime NULL,
`modified_by` int  NOT NULL DEFAULT 0,
`modified_date` datetime NULL,
`trip_id` int  NOT NULL DEFAULT 0,
`user_id` int  NOT NULL DEFAULT 0,
`approved_by` int  NOT NULL DEFAULT 0,
`in_party` int  NOT NULL DEFAULT 0,
`comment` text NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gatripsys_incidents` (
`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` int  NOT NULL DEFAULT 0,
`state` tinyint(1)  NOT NULL DEFAULT 1,
`checked_out` int  NOT NULL DEFAULT 0,
`checked_out_time` datetime NULL,
`created_by` int  NOT NULL DEFAULT 0,
`created_date` datetime NULL,
`modified_by` int  NOT NULL DEFAULT 0,
`modified_date` datetime NULL,
`trip_id` int  NOT NULL DEFAULT 0,
`user_id` int  NOT NULL DEFAULT 0,
`incid_date` datetime NULL,
`pers_involved` text NULL ,
`location` text NULL ,
`map_ref` text NULL ,
`gps_ref` text NULL ,
`witnesses` text NULL ,
`comment` text NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gatripsys_invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `checked_out` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime NULL,
  `created_date` datetime NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified_date` datetime NULL,
  `modified_by` int  NOT NULL  DEFAULT 0,
  `user_id` int NOT NULL DEFAULT 0,
  `att_id` int NOT NULL DEFAULT 0,
  `invoice_amt` decimal(11,2) NOT NULL DEFAULT 0.00,
  `paid_date` datetime NULL,
  `comment` text NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

