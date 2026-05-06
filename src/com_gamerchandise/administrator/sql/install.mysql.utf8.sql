CREATE TABLE IF NOT EXISTS `#__gamerchandise_products` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME  NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME  NULL ,
`modified_date` DATETIME  NULL ,
`prod_name` VARCHAR(255)   NULL ,
`prod_code` VARCHAR(255)   NULL ,
`prod_img` VARCHAR(255)   NULL ,
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`gender` TINYINT(1)  NOT NULL DEFAULT 1,
`cost` DECIMAL(11,2)  NOT NULL DEFAULT 0.00,
`price` DECIMAL(11,2)  NOT NULL DEFAULT 0.00,
`soh` INT(11)  NOT NULL DEFAULT 0,
`prod_desc` TEXT  NULL ,
`comment` TEXT  NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Transaction','com_gamerchandise.product','{"special":{"dbtable":"#__gamerchandise_products","key":"id","type":"TransactionTable","prefix":"GlennArkell\\\\Component\\\\Gamerchandise\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gamerchandise\/forms\/product.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gamerchandise.product')
) LIMIT 1;

CREATE TABLE IF NOT EXISTS `#__gamerchandise_sales` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME  NULL ,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME  NULL ,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME  NULL ,
`user_id` INT(11)  NOT NULL DEFAULT 0,
`prod_id` INT(11)  NOT NULL DEFAULT 0,
`cat_colour_id` INT(11)  NOT NULL DEFAULT 0,
`cat_size_id` INT(11)  NOT NULL DEFAULT 0,
`order_qty` INT(11)  NOT NULL DEFAULT 0,
`date_required` DATETIME  NULL,
`date_delivered` DATETIME  NULL,
`part_qty` INT(11)  NOT NULL DEFAULT 0,
`part_delivered` DATETIME  NULL,
`ord_amt` DECIMAL(11,2)  NOT NULL DEFAULT 0.00,
`ord_paid` TINYINT(1)  NOT NULL DEFAULT 0,
`order_ref` INT(11)  NOT NULL DEFAULT 0,
`paid_date` DATETIME  NULL ,
`comment` TEXT  NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Transaction','com_gamerchandise.sale','{"special":{"dbtable":"#__gamerchandise_sales","key":"id","type":"TransactionTable","prefix":"GlennArkell\\\\Component\\\\Gamerchandise\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gamerchandise\/forms\/sale.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gamerchandise.sale')
) LIMIT 1;

