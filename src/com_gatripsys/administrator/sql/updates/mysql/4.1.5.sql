ALTER TABLE `#__gatripsys_attendees` change `checked_out_time` `checked_out_time` DATETIME NULL;
ALTER TABLE `#__gatripsys_incidents` change `checked_out_time` `checked_out_time` DATETIME NULL;
ALTER TABLE `#__gatripsys_invoices` change `checked_out_time` `checked_out_time` DATETIME NULL;

ALTER TABLE `#__gatripsys_attendees` change `checked_out` `checked_out` INT(11)  NOT NULL DEFAULT 0;
ALTER TABLE `#__gatripsys_incidents` change `checked_out` `checked_out` INT(11)  NOT NULL DEFAULT 0;
ALTER TABLE `#__gatripsys_invoices` change `checked_out` `checked_out` INT(11)  NOT NULL DEFAULT 0;


