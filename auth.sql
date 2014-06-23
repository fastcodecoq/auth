-- Adminer 3.7.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '-05:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `auth`;
CREATE DATABASE `auth` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `auth`;

DROP TABLE IF EXISTS `credenciales`;
CREATE TABLE `credenciales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(200) NOT NULL,
  `usr` varchar(45) NOT NULL,
  `ttl` int(11) NOT NULL,
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

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `_email`, `clave`, `permisos`, `fecha_creacion`, `ultimo_ingreso`, `clave_pendiente`) VALUES
(55,	'Administrador',	'admin@admin.com',	'64e1b8d34f425d19e1ee2ea7236d3028',	'$6Q3/x/TBWQac',	'{\"inve\":{\"r\":true,\"w\":true},\"fact\":{\"r\":true,\"w\":true},\"cont\":{\"r\":true,\"w\":true},\"clte\":{\"r\":true,\"w\":true},\"conf\":{\"r\":true,\"w\":true},\"rep\":{\"r\":true,\"w\":true},\"usr\":{\"r\":true,\"w\":true}}',	'2014-06-23',	1403505132,	-1);

-- 2014-06-23 02:13:49