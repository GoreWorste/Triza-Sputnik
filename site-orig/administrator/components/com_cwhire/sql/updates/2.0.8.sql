ALTER TABLE `#__cwhire_` ADD `language` varchar(7) NOT NULL  DEFAULT '*' AFTER `ordering`;
ALTER TABLE `#__cwhire_categories` ADD `language` varchar(7) NOT NULL  DEFAULT '*' AFTER `ordering`;
-- ALTER TABLE `#__cwhire_` CHANGE `description` `fulltext` TEXT NOT NULL;
