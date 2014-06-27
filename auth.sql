-- Adminer 3.7.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '-05:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `credenciales`;
CREATE TABLE `credenciales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(200) NOT NULL,
  `usr` varchar(45) NOT NULL,
  `time` varchar(11) NOT NULL,
  `cliente` varchar(200) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `ua` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `_email` varchar(45) NOT NULL,
  `clave` varchar(200) NOT NULL,
  `permisos` varchar(500) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `ultimo_ingreso` int(11) NOT NULL,
  `clave_pendiente` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2014-06-27 02:41:46