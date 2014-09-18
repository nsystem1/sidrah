
--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;

CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `comment_like`
--

DROP TABLE IF EXISTS `comment_like`;

CREATE TABLE `comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;

CREATE TABLE `company` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `type` int(8) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8;


--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;

CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `title` varchar(350) NOT NULL,
  `content` text NOT NULL,
  `type` enum('meeting','wedding','death','baby_born','news') NOT NULL,
  `location` varchar(350) NOT NULL,
  `latitude` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `author_id` int(11) NOT NULL,
  `time` varchar(20) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;


--
-- Table structure for table `event_reaction`
--

DROP TABLE IF EXISTS `event_reaction`;

CREATE TABLE `event_reaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `reaction` enum('come','not_come') NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;


--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('idea','bug','praise') NOT NULL,
  `page` varchar(300) NOT NULL,
  `content` varchar(500) NOT NULL,
  `user_agent` varchar(300) NOT NULL,
  `http_referer` varchar(300) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page` (`page`),
  KEY `content` (`content`),
  KEY `user_agent` (`user_agent`),
  KEY `http_referer` (`http_referer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `hobby`
--

DROP TABLE IF EXISTS `hobby`;

CREATE TABLE `hobby` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Temporary table structure for view `inactive_users`
--

DROP TABLE IF EXISTS `inactive_users`;

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `inactive_users` (
  `user_id` tinyint NOT NULL,
  `first_login` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `married`
--

DROP TABLE IF EXISTS `married`;

CREATE TABLE `married` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `husband_id` int(8) NOT NULL,
  `wife_id` int(8) NOT NULL,
  `marital_status` enum('married','divorced','widow','widower') NOT NULL DEFAULT 'married',
  PRIMARY KEY (`id`),
  KEY `husband_id` (`husband_id`),
  KEY `wife_id` (`wife_id`)
) ENGINE=InnoDB AUTO_INCREMENT=803 DEFAULT CHARSET=utf8;


--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;

CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `type` enum('photo','video') NOT NULL,
  `name` varchar(500) NOT NULL,
  `size` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `title` varchar(350) NOT NULL,
  `description` text NOT NULL,
  `views` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8;


--
-- Table structure for table `media_comment`
--

DROP TABLE IF EXISTS `media_comment`;

CREATE TABLE `media_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;


--
-- Table structure for table `media_comment_like`
--

DROP TABLE IF EXISTS `media_comment_like`;

CREATE TABLE `media_comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;


--
-- Table structure for table `media_reaction`
--

DROP TABLE IF EXISTS `media_reaction`;

CREATE TABLE `media_reaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `reaction` enum('like') NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8;


--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;

CREATE TABLE `member` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `tribe_id` int(11) NOT NULL,
  `mother_id` int(8) NOT NULL DEFAULT '-1',
  `father_id` int(8) NOT NULL,
  `descenders` int(8) NOT NULL,
  `alive_descenders` int(8) NOT NULL,
  `name` varchar(150) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `fullname` varchar(350) NOT NULL,
  `gender` int(4) NOT NULL DEFAULT '1',
  `blood_type` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `pob` varchar(150) NOT NULL,
  `is_alive` tinyint(1) NOT NULL DEFAULT '1',
  `dod` date NOT NULL,
  `location` varchar(150) NOT NULL,
  `living` varchar(20) NOT NULL,
  `neighborhood` varchar(100) NOT NULL,
  `education` int(4) NOT NULL,
  `major` varchar(200) NOT NULL,
  `company_id` int(8) NOT NULL DEFAULT '-1',
  `job_title` varchar(250) NOT NULL,
  `salary` int(11) NOT NULL DEFAULT '0',
  `marital_status` int(4) NOT NULL DEFAULT '0',
  `mobile` int(11) NOT NULL,
  `phone_home` int(11) NOT NULL,
  `phone_work` int(11) NOT NULL,
  `fax` int(11) NOT NULL,
  `email` varchar(250) NOT NULL,
  `website` varchar(300) NOT NULL,
  `facebook` varchar(250) NOT NULL,
  `twitter` varchar(100) NOT NULL,
  `linkedin` varchar(200) NOT NULL,
  `flickr` varchar(200) NOT NULL,
  `flag` int(4) NOT NULL,
  `photo` varchar(300) NOT NULL,
  `cv` text NOT NULL,
  `notes` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `privacy_mother` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_partners` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_daughters` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_mobile` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_phone_home` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_phone_work` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_fax` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_email` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_dob` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_pob` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_dod` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_age` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_education` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_major` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_company` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_job_title` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_marital_status` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_blood_type` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_location` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_living` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_neighborhood` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_salary` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'admins',
  `privacy_website` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_facebook` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_twitter` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_linkedin` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_flickr` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_hobby` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `nickname` (`nickname`),
  KEY `fullname` (`fullname`),
  KEY `email` (`email`),
  KEY `job_title` (`job_title`),
  KEY `gender` (`gender`)
) ENGINE=InnoDB AUTO_INCREMENT=4518 DEFAULT CHARSET=utf8;


--
-- Table structure for table `member_hobby`
--

DROP TABLE IF EXISTS `member_hobby`;

CREATE TABLE `member_hobby` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `hobby_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `hobby_id` (`hobby_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1346 DEFAULT CHARSET=utf8;


--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;

CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('request_receive','request_reject','request_accept','password_change','committee_join_request_receive','committee_nominee','event_add','event_react_come','event_react_not_come','comment_response','comment_like','media_comment_response','media_comment_like','media_like','media_add') NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(300) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(300) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `content` (`content`),
  KEY `link` (`link`)
) ENGINE=InnoDB AUTO_INCREMENT=29926 DEFAULT CHARSET=utf8;


--
-- Table structure for table `prepared_relation`
--

DROP TABLE IF EXISTS `prepared_relation`;

CREATE TABLE `prepared_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `relation` text NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;


--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;

CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_key` varchar(10) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `phpscript` text NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `reason` text NOT NULL,
  `affected_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `executed` int(11) NOT NULL,
  `executed_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `random_key` (`random_key`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=1953 DEFAULT CHARSET=utf8;


--
-- Table structure for table `tagmember`
--

DROP TABLE IF EXISTS `tagmember`;

CREATE TABLE `tagmember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('event','media') NOT NULL,
  `content_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `width` int(11) NOT NULL DEFAULT '100',
  `height` int(11) NOT NULL DEFAULT '100',
  `top` int(11) NOT NULL,
  `left` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;


--
-- Table structure for table `tribe`
--

DROP TABLE IF EXISTS `tribe`;

CREATE TABLE `tribe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=348 DEFAULT CHARSET=utf8;


--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;


CREATE TABLE `user` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `usergroup` enum('user','moderator','admin') NOT NULL,
  `member_id` int(11) NOT NULL,
  `twitter_userid` varchar(32) NOT NULL,
  `twitter_oauth_token` varchar(300) NOT NULL,
  `twitter_oautho_secret` varchar(300) NOT NULL,
  `assigned_root_id` int(11) NOT NULL,
  `sms_received` tinyint(1) NOT NULL,
  `first_login` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_time` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`password`),
  KEY `username_2` (`username`),
  KEY `password` (`password`),
  KEY `usergroup` (`usergroup`)
) ENGINE=InnoDB AUTO_INCREMENT=2234 DEFAULT CHARSET=utf8;
