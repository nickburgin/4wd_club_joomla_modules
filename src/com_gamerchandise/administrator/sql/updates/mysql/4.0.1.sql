ALTER TABLE `#__gamerchandise_sales` ADD `order_ref` INT(11) NOT NULL DEFAULT 0 AFTER `ord_paid`;

ALTER TABLE `#__gamerchandise_products` ENGINE=InnoDB;
ALTER TABLE `#__gamerchandise_products` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__gamerchandise_sales` ENGINE=InnoDB;
ALTER TABLE `#__gamerchandise_sales` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
