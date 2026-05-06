CREATE TABLE IF NOT EXISTS `#__gaforsale_fsitems` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
`ordering` INT(11)  NOT NULL  DEFAULT 0,
`state` TINYINT(1)  NOT NULL  DEFAULT 1,
`checked_out` INT(11)  NOT NULL  DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11) NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL  DEFAULT 0,
`modified_date` DATETIME NULL,
`user_id` INT(11)  NOT NULL  DEFAULT 0,
`item_desc` VARCHAR(255)   NULL ,
`item_image` VARCHAR(1024)   NULL ,
`item_details` TEXT  NULL ,
`item_price` DECIMAL(11,2)  NOT NULL  DEFAULT 0.00,
`neg_ono` TINYINT(1) NOT NULL DEFAULT 1,
`seller_contact` VARCHAR(255)  NULL ,
`seller_phone` VARCHAR(60)  NULL ,
`comments` TEXT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Forsale Item','com_gaforsale.fsitem','{"special":{"dbtable":"#__gafinance_fsitems","key":"id","type":"TransactionTable","prefix":"Joomla\\\\Component\\\\Gafinance\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gaforsale\/forms\/fsitem.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gaforsale.fsitem')
) LIMIT 1;
