/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE='' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS ev02 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE ev02;

ALTER TABLE usuarios
  ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

/*Table structure for table apuntes */
DROP TABLE IF EXISTS apuntes;
CREATE TABLE apuntes (
  id int(11) NOT NULL AUTO_INCREMENT,
  titulo varchar(75) NOT NULL,
  contenido text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/*Table structure for table bitacora */
DROP TABLE IF EXISTS bitacora;
CREATE TABLE bitacora (
  FECHA_HORA varchar(255) DEFAULT NULL COMMENT 'para registrar el momento en que ocurre',
  PAGINA varchar(255) DEFAULT NULL COMMENT 'quien lo hizo',
  TABLA varchar(255) DEFAULT NULL COMMENT 'rutina donde lo efectuo',
  TIPO_CONSULTA varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='para guardar registro de consulta a la base';

/*Table structure for table generales */
DROP TABLE IF EXISTS generales;
CREATE TABLE generales (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  carnet varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  correo varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  cum varchar(12) DEFAULT NULL,
  imagen blob DEFAULT NULL,
  fechacreacion datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/*Table structure for table notas */
DROP TABLE IF EXISTS notas;
CREATE TABLE notas (
  id_notas int(11) NOT NULL AUTO_INCREMENT,
  carnet varchar(32) DEFAULT NULL,
  materia varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  nota1 decimal(10,2) DEFAULT NULL,
  nota2 decimal(10,2) DEFAULT NULL,
  nota3 decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (id_notas)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/*Table structure for table usuarios */
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  usuario varchar(32) DEFAULT NULL,
  contrasena varchar(32) DEFAULT NULL,
  email varchar(32) DEFAULT NULL,
  nombres varchar(64) DEFAULT NULL,
  apellidos varchar(64) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO usuarios(usuario, contrasena, email, nombres, apellidos) 
VALUES ('admin', 'admin', 'mvfuentes@gmail.com', 'Administrador', 'General');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
