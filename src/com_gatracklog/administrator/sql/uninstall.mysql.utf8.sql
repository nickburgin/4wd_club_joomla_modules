DROP TABLE IF EXISTS `#__gatracklog_tracklogs`;
DROP TABLE IF EXISTS `#__gatracklog_trackcomments`;

DELETE from `#__content_types` WHERE `type_alias` IN ('com_gatracklog.tracklog', 'com_gatracklog.category');
DELETE from `#__categories` WHERE `extension` = 'com_gatracklog';
