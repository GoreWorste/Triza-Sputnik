CREATE TABLE IF NOT EXISTS `#__cwhire_` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`jobcategory` int(11) unsigned NOT NULL,
`refname` varchar(255) NOT NULL,
`title` varchar(255) NOT NULL,
`location` varchar(255) NOT NULL,
`contract` varchar(255) NOT NULL,
`start_date` date NOT NULL,
`image` varchar(255) NOT NULL,
`shortdesc` text NOT NULL,
`description` text NOT NULL,
-- `introtext` text NOT NULL,
-- `fulltext` text NOT NULL,
`tasks` text NOT NULL,
`profile` text NOT NULL,
`perspective` text NOT NULL,
`contactinfo` text NOT NULL,
`homepage` tinyint(1) NOT NULL DEFAULT '0',
`onfacebook` tinyint(1) NOT NULL DEFAULT '0',
`facebook_id` varchar(255) NOT NULL,
`facebook_text` text NOT NULL,
`facebook_image` varchar(255) NOT NULL,
`state` tinyint(1) NOT NULL DEFAULT '1',
`ordering` int(11) NOT NULL,
`language` varchar(7) NOT NULL DEFAULT '*',
`checked_out` int(11) NOT NULL,
`checked_out_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
`created_by` int(11) NOT NULL,
`created` DATETIME NOT NULL ,
PRIMARY KEY (`id`),
KEY `jobcategory` (`jobcategory`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__cwhire_categories` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`recipient` varchar(100) NOT NULL,
`state` TINYINT(1)  NOT NULL DEFAULT '1',
`ordering` INT(11)  NOT NULL ,
`language` varchar(7) NOT NULL DEFAULT '*',
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
`created_by` INT(11)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

ALTER TABLE `#__cwhire_`
  ADD CONSTRAINT `#__cwhire__ibfk_1` FOREIGN KEY (`jobcategory`) REFERENCES `#__cwhire_categories` (`id`);
