CREATE TABLE IF NOT EXISTS `#__cmsocialconnect_connections` (
 `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `network_id` varchar(255) NOT NULL DEFAULT '',
  `network_user_id` varchar(255) NOT NULL DEFAULT '',
  `connected_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `#__cmsocialconnect_connections`
 ADD PRIMARY KEY (`id`), ADD KEY `idx_networkid` (`network_id`), ADD KEY `idx_userid` (`user_id`);

ALTER TABLE `#__cmsocialconnect_connections`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;