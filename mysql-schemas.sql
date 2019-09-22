CREATE DATABASE `single_signon`;

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
  `domain` varchar(40) NOT NULL,
  `verify_code_ticket` varchar(100) NOT NULL,
  `receive_code_url` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site` (`id`, `name`, `domain`, `verify_code_ticket`, `receive_code_url`) VALUES (1, 'Website A', 'localhost:9012', 'RfmUtfRoeu', 'http://localhost:9012/login-by-sso.html');

CREATE TABLE `account_site_permission` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(40) NOT NULL,
  `site_id` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('bob', '1');
INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('bob', '2');
INSERT INTO `account_site_permission` (`username`, `site_id`) VALUES ('alice', '1');
