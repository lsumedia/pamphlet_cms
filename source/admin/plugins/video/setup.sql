CREATE TABLE IF NOT EXISTS `plugin_videomanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `live` int(11) DEFAULT NULL,
  `vod` int(11) DEFAULT NULL,
  `cover` int(11) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `thumbnail` varchar(150) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Video player link definitions';


CREATE TABLE IF NOT EXISTS `plugin_vod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `tags` varchar(50) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL COMMENT 'Tags seperated by spaces',
  `date` date NOT NULL,
  `description` text CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `type` varchar(20) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `url` varchar(300) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT NULL,
  `source` text CHARACTER SET utf16 COLLATE utf16_bin,
  `poster` varchar(2083) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `live` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='On-demand videos';


CREATE TABLE IF NOT EXISTS `plugin_vod_sources` (
  `video_id` int(11) NOT NULL COMMENT 'Parent video ID',
  `source_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique source ID',
  `src` varchar(2083) COLLATE latin1_general_ci NOT NULL COMMENT 'Video source URL',
  `type` varchar(50) COLLATE latin1_general_ci NOT NULL COMMENT 'Video MIME type',
  `res` varchar(10) COLLATE latin1_general_ci NOT NULL COMMENT 'Video vertical resolution',
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `source_id` (`source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Sources file for vod plugin';
