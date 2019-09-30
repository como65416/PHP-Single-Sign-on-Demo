CREATE DATABASE `single_signon`;

USE `single_signon`;

CREATE TABLE `account` (
  `username` VARCHAR(40) NOT NULL,
  `password` VARCHAR(200) NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `account` (`username`, `password`) VALUES ('alice', '$2y$10$oZPGzEa.ZUgeVksP/VSvlOKXxbUn2/WOv/QdjXq3/JZqBfog02w2.');
INSERT INTO `account` (`username`, `password`) VALUES ('bob', '$2y$10$oZPGzEa.ZUgeVksP/VSvlOKXxbUn2/WOv/QdjXq3/JZqBfog02w2.');

CREATE TABLE `site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `host` varchar(40) NOT NULL,
  `verify_ticket_code` varchar(100) NOT NULL,
  `home_page_path` varchar(120) NOT NULL,
  `receive_code_path` varchar(120) NOT NULL,
  `logout_path` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site` (`id`, `name`, `host`, `verify_ticket_code`, `home_page_path`, `receive_code_path`, `logout_path`) VALUES (1, 'Website A', 'localhost:9012', 'RfmUtfRoeu', '/', '/login-by-sso.html', '/logout.html');

CREATE TABLE `account_site_permission` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(40) NOT NULL,
  `site_id` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('bob', '1');
INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('bob', '2');
INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('alice', '1');
