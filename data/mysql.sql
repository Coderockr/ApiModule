SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `api` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
USE `api` ;

-- -----------------------------------------------------
-- Table `api`.`cliente`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `api`.`cliente` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT ,
  `nome` VARCHAR(100) NOT NULL ,
  `login` VARCHAR(45) NOT NULL ,
  `senha` VARCHAR(255) NOT NULL ,
  `status` ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO' ,
  `criado` TIMESTAMP NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `api`.`token`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `api`.`token` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `cliente_id` SMALLINT NOT NULL ,
  `token` VARCHAR(255) NOT NULL ,
  `ip` VARCHAR(20) NULL ,
  `status` ENUM('VALIDO','INVALIDO') NULL DEFAULT 'VALIDO' ,
  `criado` TIMESTAMP NOT NULL ,
  INDEX `fk_token_cliente` (`cliente_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_token_cliente`
    FOREIGN KEY (`cliente_id` )
    REFERENCES `api`.`cliente` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `api`.`log`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `api`.`log` (
  `token_id` INT NOT NULL ,
  `recurso` VARCHAR(100) NOT NULL ,
  `criado` TIMESTAMP NULL  ,
  INDEX `fk_log_token1` (`token_id` ASC) ,
  CONSTRAINT `fk_log_token1`
    FOREIGN KEY (`token_id` )
    REFERENCES `api`.`token` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `api`.`permissao`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `api`.`permissao` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `cliente_id` SMALLINT NOT NULL ,
  `recurso` VARCHAR(100) NOT NULL DEFAULT '*' ,
  `criado` TIMESTAMP NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_permissao_cliente1` (`cliente_id` ASC) ,
  CONSTRAINT `fk_permissao_cliente1`
    FOREIGN KEY (`cliente_id` )
    REFERENCES `api`.`cliente` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
