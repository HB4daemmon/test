DROP TABLE IF EXISTS `custom_delivery_number`;
CREATE TABLE `custom_delivery_number` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `day_of_week` int(1) NOT NULL,
  `time_range` int(2) NOT NULL,
  `delivery_number` int(10) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;