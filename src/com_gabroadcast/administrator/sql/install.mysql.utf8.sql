CREATE TABLE IF NOT EXISTS `#__gabroadcast_usernews` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL ,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL ,
`modified_date` DATETIME NULL ,
`cat_id` INT(11)  NOT NULL DEFAULT 0,
`fin_users_only` TINYINT(1) NOT NULL DEFAULT 1,
`news_subject` VARCHAR(255) NULL ,
`news_detail` TEXT NULL ,
`pending_bcast` TEXT NULL ,
`attach_file` VARCHAR(1024) NULL ,
`comment` TEXT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__gabroadcast_bcasts` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL DEFAULT 0,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL DEFAULT 0,
`checked_out_time` DATETIME NULL ,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`modified_by` INT(11)  NOT NULL DEFAULT 0,
`created_date` DATETIME NULL ,
`modified_date` DATETIME NULL ,
`attach_lab` VARCHAR(255) NULL ,
`attach_dir` VARCHAR(1024) NULL ,
`comment` TEXT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

