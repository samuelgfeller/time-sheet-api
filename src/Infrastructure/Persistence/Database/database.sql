-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema time-sheet
-- -----------------------------------------------------
-- Originally created for the IPA to complete my apprenticeship as computer scientist

-- -----------------------------------------------------
-- Schema time-sheet
--
-- Originally created for the IPA to complete my apprenticeship as computer scientist
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `time-sheet` DEFAULT CHARACTER SET utf8 ;

USE `time-sheet` ;

-- -----------------------------------------------------
-- Table `time-sheet`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `time-sheet`.`user` (
                                                   `id` INT NOT NULL AUTO_INCREMENT,
                                                   `name` VARCHAR(200) NOT NULL,
                                                   `email` VARCHAR(254) NOT NULL,
                                                   `password` VARCHAR(300) NOT NULL,
                                                   `role` VARCHAR(50) NOT NULL DEFAULT 'user',
                                                   `updated_at` DATETIME NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                                                   `created_at` DATETIME NULL DEFAULT current_timestamp(),
                                                   `deleted_at` DATETIME NULL DEFAULT NULL,
                                                   PRIMARY KEY (`id`))
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `time-sheet`.`time_sheet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `time-sheet`.`time_sheet` (
                                                         `id` INT NOT NULL AUTO_INCREMENT,
                                                         `user_id` INT NOT NULL,
                                                         `start` DATETIME NULL DEFAULT current_timestamp(),
                                                         `stop` DATETIME NULL DEFAULT NULL,
                                                         `activity` VARCHAR(400) NULL DEFAULT NULL,
                                                         `deleted_at` DATETIME NULL DEFAULT NULL,
                                                         PRIMARY KEY (`id`),
                                                         INDEX `fk_time_sheet_user_idx` (`user_id` ASC),
                                                         CONSTRAINT `fk_time_sheet_user`
                                                             FOREIGN KEY (`user_id`)
                                                                 REFERENCES `time-sheet`.`user` (`id`)
                                                                 ON DELETE NO ACTION
                                                                 ON UPDATE NO ACTION)
    ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
