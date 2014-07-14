/*

MySQL Data Transfer

Source Host: localhost

Source Database: shop

Target Host: localhost

Target Database: shop

Date: 2014/7/14 10:06:41

*/



SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------

-- Table structure for shop_albumcate

-- ----------------------------

CREATE TABLE `shop_albumcate` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `par_id` int(4) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `cate_path` varchar(255) DEFAULT NULL,
  `sort` int(4) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `par_id` (`par_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albumcommentmeta

-- ----------------------------

CREATE TABLE `shop_albumcommentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albumcomments

-- ----------------------------

CREATE TABLE `shop_albumcomments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `ref_id` bigint(20) unsigned NOT NULL,
  `quote_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `email` varchar(200) NOT NULL,
  `author` varchar(100) NOT NULL,
  `reply_author` varchar(100) DEFAULT NULL,
  `author_ip` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_time` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ref_id` (`ref_id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albummeta

-- ----------------------------

CREATE TABLE `shop_albummeta` (
  `ameta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`ameta_id`),
  KEY `album_id` (`album_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albumplugins

-- ----------------------------

CREATE TABLE `shop_albumplugins` (
  `plugin_id` varchar(32) NOT NULL,
  `plugin_name` varchar(200) NOT NULL,
  `description` varchar(255) NOT NULL,
  `plugin_config` longtext,
  `local_ver` varchar(20) NOT NULL,
  `remote_ver` varchar(20) DEFAULT NULL,
  `available` enum('true','false') NOT NULL DEFAULT 'false',
  `author_name` varchar(100) DEFAULT NULL,
  `author_url` varchar(100) DEFAULT NULL,
  `author_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albums

-- ----------------------------

CREATE TABLE `shop_albums` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `cate_id` bigint(4) unsigned NOT NULL DEFAULT '0',
  `cover_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cover_ext` varchar(20) DEFAULT NULL,
  `comments_num` int(11) unsigned NOT NULL DEFAULT '0',
  `photos_num` int(11) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `up_time` int(11) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `priv_type` tinyint(1) NOT NULL DEFAULT '0',
  `priv_pass` varchar(100) DEFAULT NULL,
  `priv_question` varchar(255) DEFAULT NULL,
  `priv_answer` varchar(255) DEFAULT NULL,
  `desc` longtext,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `enable_comment` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cover_id` (`cover_id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_albumsetting

-- ----------------------------

CREATE TABLE `shop_albumsetting` (
  `name` varchar(50) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_photometa

-- ----------------------------

CREATE TABLE `shop_photometa` (
  `pmeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`pmeta_id`),
  KEY `photo_id` (`photo_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ----------------------------

-- Table structure for shop_photos

-- ----------------------------

CREATE TABLE `shop_photos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `album_id` bigint(20) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `hits` bigint(20) NOT NULL DEFAULT '0',
  `comments_num` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `taken_time` int(11) NOT NULL DEFAULT '0',
  `taken_y` smallint(4) NOT NULL DEFAULT '0',
  `taken_m` tinyint(2) NOT NULL DEFAULT '0',
  `taken_d` tinyint(2) NOT NULL DEFAULT '0',
  `create_y` smallint(4) NOT NULL DEFAULT '0',
  `create_m` tinyint(2) NOT NULL DEFAULT '0',
  `create_d` tinyint(2) NOT NULL DEFAULT '0',
  `desc` longtext,
  `exif` longtext,
  `tags` varchar(255) DEFAULT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;



-- ----------------------------

-- Records 

-- ----------------------------

INSERT INTO `shop_albumcate` VALUES ('16', '0', 'fsdfdsf', ',16,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('17', '16', '第三方多少付多少', ',16,17,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('18', '16', '梵蒂冈梵蒂冈反对', ',16,18,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('19', '18', '似懂非懂是否', ',16,18,19,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('20', '0', '8578787', ',20,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('21', '17', '7878', ',16,17,21,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('22', '0', 'sadsadas', ',22,', '100', '1');

INSERT INTO `shop_albumcate` VALUES ('23', '0', 'sdfdsf', ',23,', '100', '1');

INSERT INTO `shop_albums` VALUES ('4', 'sadsada', '0', '0', 'asdsad', '0', '0', '0', '0', null, '0', null, null, null, null, '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('5', 'rewrwere', '0', '0', null, '0', '0', '1405055289', '1405055289', 'werwer', '0', '', '', '', 'werwerwer', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('6', 'hgtrhfh', '0', '0', null, '0', '0', '1405055476', '1405055476', '', '0', '', '', '', 'fgdfgfdg', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('7', 'dfgdsfds', '0', '0', null, '0', '0', '1405055604', '1405055604', '', '0', '', '', '', 'gdfdsgfdsf', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('8', 'ggfhgfhgf', '0', '0', null, '0', '0', '1405055623', '1405055623', '', '0', '', '', '', 'fghgfhgfh', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('9', 'asdasdsad', '0', '0', null, '0', '0', '1405055702', '1405055702', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('10', 'dsfsdfdsf', '0', '0', null, '0', '0', '1405055721', '1405055721', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('11', 'fsfsdf', '0', '0', null, '0', '0', '1405055815', '1405055815', '', '0', '', '', '', 'sdfdsf', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('12', 'fgdgdfg', '0', '0', null, '0', '0', '1405055870', '1405055870', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('13', 'cxzcxzczx', '0', '0', null, '0', '0', '1405056044', '1405056044', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('14', 'sdsadsad', '0', '0', null, '0', '0', '1405056142', '1405056142', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('15', 'sdfdsfsd', '0', '0', null, '0', '0', '1405056212', '1405056212', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('16', 'sdfdsfdsf', '0', '0', null, '0', '0', '1405056249', '1405056249', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('17', 'sdsadas', '0', '0', null, '0', '0', '1405056844', '1405056844', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('18', 'asdsadsa', '0', '0', null, '0', '0', '1405056942', '1405056942', '', '0', '', '', '', 'asdsad', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('19', 'fgfdgfdg', '0', '0', null, '0', '0', '1405056953', '1405056953', '', '0', '', '', '', 'dfgdfg', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('20', 'fghgfhf', '0', '0', null, '0', '0', '1405056963', '1405056963', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('21', 'asdsadasd', '16', '0', null, '0', '0', '1405057694', '1405057694', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('22', 'sfsdf', '0', '0', null, '0', '0', '1405057703', '1405057703', '', '0', '', '', '', 'dsfsdfdsf', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('23', 'fdgfdgdfg', '21', '0', null, '0', '0', '1405057715', '1405057715', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('24', 'asdasd', '21', '0', null, '0', '0', '1405057726', '1405057726', '', '0', '', '', '', 'asdasdsa', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('25', '78', '0', '0', null, '0', '0', '1405058311', '1405058311', '', '0', '', '', '', '', '0', '1', '1');

INSERT INTO `shop_albums` VALUES ('26', 'dsfsdf', '0', '0', null, '0', '0', '1405058632', '1405058632', '', '0', '', '', '', 'sdfsdfds', '0', '1', '1');

