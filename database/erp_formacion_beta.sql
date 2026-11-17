-- ERP Formación Base - beta 0.2.1
-- Base de datos completa para MySQL/MariaDB (XAMPP)
-- Funcionalidad actual: login, index y configuración de cuenta.
-- Nomenclatura del dominio en castellano.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `codigos_restablecimiento_contrasena`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `empresas`;
DROP TABLE IF EXISTS `migraciones`;

CREATE TABLE `migraciones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migracion` varchar(255) NOT NULL,
  `lote` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `persona_id_created` bigint unsigned DEFAULT NULL,
  `persona_id_updated` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(180) NOT NULL,
  `nombre_comercial` varchar(180) DEFAULT NULL,
  `cif` varchar(30) DEFAULT NULL,
  `correo_electronico` varchar(160) DEFAULT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `direccion` varchar(220) DEFAULT NULL,
  `codigo_postal` varchar(15) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) NOT NULL DEFAULT 'España',
  `nombre_aplicacion` varchar(80) NOT NULL DEFAULT 'ERP Formación',
  `color_principal` varchar(20) NOT NULL DEFAULT '#0d6efd',
  `ruta_logo` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `persona_id_created` bigint unsigned DEFAULT NULL,
  `persona_id_updated` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo_electronico` varchar(160) NOT NULL,
  `fecha_verificacion_correo` timestamp NULL DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_ultimo_acceso` timestamp NULL DEFAULT NULL,
  `token_recuerdo` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `persona_id_created` bigint unsigned DEFAULT NULL,
  `persona_id_updated` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_correo_electronico_unique` (`correo_electronico`),
  KEY `usuarios_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `usuarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `codigos_restablecimiento_contrasena` (
  `correo_electronico` varchar(255) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `persona_id_created` bigint unsigned DEFAULT NULL,
  `persona_id_updated` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`correo_electronico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migraciones` (`id`,`migracion`,`lote`,`created_at`,`updated_at`,`persona_id_created`,`persona_id_updated`) VALUES
(1,'2026_08_25_000001_crear_tabla_empresas',1,NOW(),NOW(),NULL,NULL),
(2,'2026_08_25_000002_crear_tabla_usuarios',1,NOW(),NOW(),NULL,NULL);

INSERT INTO `empresas` (`id`,`razon_social`,`nombre_comercial`,`cif`,`correo_electronico`,`telefono`,`direccion`,`codigo_postal`,`localidad`,`provincia`,`pais`,`nombre_aplicacion`,`color_principal`,`ruta_logo`,`activo`,`created_at`,`updated_at`,`persona_id_created`,`persona_id_updated`) VALUES
(1,'Academia Horizonte Demo S.L.','Horizonte Formación','B12345678','info@horizontedemo.local','910 000 000','Calle Formación, 12','28001','Madrid','Madrid','España','ERP Formación','#0d6efd',NULL,1,NOW(),NOW(),NULL,NULL);

-- admin@demo.local / Admin1234!
-- usuario@demo.local / Usuario1234!
INSERT INTO `usuarios` (`id`,`empresa_id`,`nombre`,`correo_electronico`,`contrasena`,`activo`,`fecha_ultimo_acceso`,`token_recuerdo`,`created_at`,`updated_at`,`persona_id_created`,`persona_id_updated`) VALUES
(1,1,'Administrador Demo','admin@demo.local','$2y$12$iD8d/H0knZefDLM/o/W3zebSlexNgA4cKEMMKRamIRHBN4VY1AY2m',1,NULL,NULL,NOW(),NOW(),NULL,NULL),
(2,1,'Usuario Demo','usuario@demo.local','$2y$12$6IYk0C2.XHK4xfSi3DBbvO2Kyq5Yauy8n6.a8bvepoorG6nvCtQza',1,NULL,NULL,NOW(),NOW(),NULL,NULL);

SET FOREIGN_KEY_CHECKS = 1;
