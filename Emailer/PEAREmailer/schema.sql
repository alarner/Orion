delimiter $$

CREATE TABLE `_orion_email_recipients` (
  `email_recipient_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(10) unsigned NOT NULL,
  `email` varchar(512) NOT NULL,
  `name` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`email_recipient_id`),
  UNIQUE KEY `email_id_email` (`email_id`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1$$

delimiter $$

CREATE TABLE `_orion_email_unsubscriptions` (
  `email` varchar(512) NOT NULL,
  `email_id` int(10) unsigned NOT NULL,
  `date_unsubscribed` datetime NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1$$

delimiter $$

CREATE TABLE `_orion_emails` (
  `email_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_name` varchar(255) DEFAULT NULL,
  `from_email` varchar(256) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `to_email` varchar(256) NOT NULL,
  `subject` varchar(512) NOT NULL,
  `template_path_text` varchar(512) DEFAULT NULL,
  `template_path_html` varchar(512) DEFAULT NULL,
  `template_params` text NOT NULL,
  `check_subscribed` tinyint(1) unsigned NOT NULL,
  `queued` tinyint(1) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_executed` datetime DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `hash` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  KEY `date_sent` (`date_executed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1$$
