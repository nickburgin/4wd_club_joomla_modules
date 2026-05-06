ALTER TABLE `#__gaforsale_fsitems` CHANGE `checked_out` `checked_out` INT NULL;

UPDATE `#__content_types` 
SET `table` = '{"special":{"dbtable":"#__gaforsale_fsitems","key":"id","type":"FsitemTable","prefix":"Joomla\\\\Component\\\\Gaforsale\\\\Administrator\\\\Table\\\\"}}'
WHERE `type_alias` = 'com_gaforsale.fsitem';
