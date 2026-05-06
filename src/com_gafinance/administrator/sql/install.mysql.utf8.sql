CREATE TABLE IF NOT EXISTS `#__gafinance_transactions` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`user_id` INT(11)  NOT NULL ,
`tran_type` VARCHAR(255)  NOT NULL ,
`tran_date` DATE NULL,
`tran_amount` DECIMAL(11,4)  NOT NULL DEFAULT 0.0000,
`tran_desc` VARCHAR(255)  NULL ,
`tran_ref` VARCHAR(255)  NULL ,
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`tran_file` VARCHAR(1024)  NULL ,
`accnt_id` INT(11)  NOT NULL DEFAULT 1,
`gst_amt` DECIMAL(11,2)  NOT NULL DEFAULT 0.00,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_accounts` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`accnt_name` VARCHAR(255)  NULL ,
`accnt_bsb` VARCHAR(255)  NULL ,
`accnt_number` VARCHAR(255)  NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_invoices` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`client_id` INT(11)  NOT NULL DEFAULT 0,
`invoice_type` TEXT NULL ,
`invoice_desc` TEXT NULL ,
`invoice_cost` DECIMAL(11,2)  NOT NULL DEFAULT 0.00,
`inv_date` DATE NULL,
`paid_date` DATE NULL,
`accnt_id` INT(11)  NOT NULL DEFAULT 1,
`email_comment` TEXT NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_audit_trail` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`tran_id` INT(11)  NOT NULL DEFAULT 0,
`pre_record` TEXT NULL ,
`post_record` TEXT NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_busassets` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`depreciate` TINYINT(1)  NOT NULL DEFAULT 1,
`asset_name` VARCHAR(255)  NULL ,
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`model_no` VARCHAR(255)  NULL ,
`serial_no` VARCHAR(255)  NULL ,
`asset_img` VARCHAR(1024)  NULL ,
`asset_manual` VARCHAR(1024)  NULL ,
`asset_desc` TEXT  NULL,
`asset_date` DATE NULL ,
`asset_value` DECIMAL(11,2)  NOT NULL DEFAULT '0.00',
`dep_period` VARCHAR(255)  NULL ,
`dep_term` INT(11)  NOT NULL DEFAULT 0,
`comment` TEXT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_budgets` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`finyear` TINYINT(1)  NOT NULL DEFAULT 1,
`budget_year` YEAR NULL ,
`item_name` VARCHAR(255)  NULL ,
`item_desc` VARCHAR(255)  NULL ,
`item_date` DATE NULL ,
`item_value` DECIMAL(11,2)  NOT NULL DEFAULT '0.00',
`item_term` INT(11)  NOT NULL DEFAULT 0,
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`comment` TEXT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_patrons` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`name` VARCHAR(255)  NULL ,
`address` TEXT  NULL ,
`phone` VARCHAR(255)  NULL ,
`email` VARCHAR(255)  NULL ,
`cont_name` VARCHAR(255)  NULL ,
`cont_phone` VARCHAR(255)  NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gafinance_invtypes` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`modified_date` DATETIME NULL,
`invtype_name` VARCHAR(255)  NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Transaction','com_gafinance.transaction','{"special":{"dbtable":"#__gafinance_transactions","key":"id","type":"TransactionTable","prefix":"Joomla\\\\Component\\\\Gafinance\\\\Administrator\\\\Table\\\\"}}', CASE
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gafinance\/forms\/transaction.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gafinance.transaction')
) LIMIT 1;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Account','com_gafinance.account','{"special":{"dbtable":"#__gafinance_accounts","key":"id","type":"AccountTable","prefix":"Joomla\\\\Component\\\\Gafinance\\\\Administrator\\\\Table\\\\"}}', CASE 
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gafinance\/forms\/account.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gafinance.account')
) LIMIT 1;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Invoice','com_gafinance.invoice','{"special":{"dbtable":"#__gafinance_invoices","key":"id","type":"InvoiceTable","prefix":"Joomla\\\\Component\\\\Gafinance\\\\Administrator\\\\Table\\\\"}}', CASE 
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gafinance\/forms\/invoice.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gafinance.invoice')
) LIMIT 1;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Audit','com_gafinance.audit','{"special":{"dbtable":"#__gafinance_audit_trail","key":"id","type":"AuditTable","prefix":"Joomla\\\\Component\\\\Gafinance\\\\Administrator\\\\Table\\\\"}}', CASE 
                                WHEN 'rules' is null THEN ''
                                ELSE ''
                                END as rules, CASE 
                                WHEN 'field_mappings' is null THEN ''
                                ELSE ''
                                END as field_mappings, '{"formFile":"administrator\/components\/com_gafinance\/forms\/audit.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"comment"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gafinance.audit')
) LIMIT 1;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
SELECT * FROM ( SELECT 'Invoice Category','com_gafinance.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":   {"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '','Treasury SystemRouter::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}') AS tmp WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_gafinance.category')
) LIMIT 1;
