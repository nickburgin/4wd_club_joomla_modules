DROP TABLE IF EXISTS `#__gamerchandise_products`;
DROP TABLE IF EXISTS `#__gamerchandise_sales`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_gamerchandise.%');

DELETE FROM `#__categories` WHERE (extension LIKE 'com_gamerchandise%');
