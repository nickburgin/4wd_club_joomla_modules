DROP TABLE IF EXISTS `#__gatripsys_trips`;
DROP TABLE IF EXISTS `#__gatripsys_attendees`;
DROP TABLE IF EXISTS `#__gatripsys_incidents`;
DROP TABLE IF EXISTS `#__gatripsys_invoices`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_gatripsys.%');
DELETE FROM `#__action_log_config` WHERE `type_alias` = 'com_gatripsys';
DELETE FROM `#__action_logs_extensions` WHERE `extension` = 'com_gatripsys';
