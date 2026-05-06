ALTER TABLE `#__gatripsys_trips` add `max_people` INT(11) NOT NULL DEFAULT 0 AFTER `max_no`;
ALTER TABLE `#__gatripsys_trips` drop `what_take`;
ALTER TABLE `#__gatripsys_trips` drop `cal_id`;
ALTER TABLE `#__gatripsys_trips` change `update_date` `modified_date` DATETIME NULL;
ALTER TABLE `#__gatripsys_attendees` change `update_date` `modified_date` DATETIME NULL;
ALTER TABLE `#__gatripsys_incidents` change `update_date` `modified_date` DATETIME NULL;



