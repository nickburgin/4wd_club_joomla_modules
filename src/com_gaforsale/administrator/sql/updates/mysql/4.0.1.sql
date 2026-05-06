ALTER TABLE `#__gaforsale_fsitems` CHANGE `state` `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `checked_out` `checked_out` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `created_by` `created_by` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `created_date` `created_date` DATETIME NULL;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `comments` `comments` TEXT NULL;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `user_id` `user_id` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `item_image` `item_image` VARCHAR(1024)  NULL;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `item_details` `item_details` TEXT NULL;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `neg_ono` `neg_ono` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `item_price` `item_price` DECIMAL(11,2)  NOT NULL  DEFAULT 0.00;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `seller_contact` `seller_contact` VARCHAR(255)  NULL;
ALTER TABLE `#__gaforsale_fsitems` CHANGE `seller_phone` `seller_phone` VARCHAR(60)  NULL;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_gaforsale.%');

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
