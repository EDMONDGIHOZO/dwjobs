CREATE TABLE `wdjobs_db`.`users` ( `id` INT NOT NULL AUTO_INCREMENT , `email` VARCHAR(250) NULL , `password` VARCHAR(250) NULL , `temp` VARCHAR(250) NULL DEFAULT NULL , `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `date_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = MyISAM;