DROP TABLE IF EXISTS `oauth_access_token`;
CREATE TABLE `oauth_access_token` (
  `id` int(32) NOT NULL AUTO_INCREMENT COMMENT 'token id',
  `client_id` varchar(64) NOT NULL COMMENT 'user id',
  `grant_type` varchar(32) NOT NULL,
  `user_id` int(10) DEFAULT NULL COMMENT 'user id',
  `token` varchar(64) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create time',
  `expires_at` timestamp ,
  `scopes` varchar(64),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;