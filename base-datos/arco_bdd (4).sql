-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-12-2025 a las 22:00:12
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `arco_bdd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categorias` int NOT NULL AUTO_INCREMENT,
  `nombre_cat` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `subcategorias` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `productos` int DEFAULT '0',
  PRIMARY KEY (`id_categorias`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nombre_cat`, `subcategorias`, `estado`, `productos`) VALUES
(5, 'lifesteal', 'irritante', 1, 10),
(8, 'champu de coco', 'peligroso', 1, 100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `numc_doc` int DEFAULT NULL,
  `nombres` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `apellidos` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante`
--

DROP TABLE IF EXISTS `comprobante`;
CREATE TABLE IF NOT EXISTS `comprobante` (
  `id_comprobante` int NOT NULL AUTO_INCREMENT,
  `num_comprobante` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `cliente` int DEFAULT NULL,
  `direccion` varchar(40) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `encargado` int DEFAULT NULL,
  PRIMARY KEY (`id_comprobante`),
  KEY `fk_comprobante_encargado` (`encargado`),
  KEY `fk_comprobante_cliente` (`cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles`
--

DROP TABLE IF EXISTS `detalles`;
CREATE TABLE IF NOT EXISTS `detalles` (
  `id_detalles` int NOT NULL AUTO_INCREMENT,
  `id_material` int DEFAULT NULL,
  `id_comprobante` int DEFAULT NULL,
  `cantidades` int DEFAULT NULL,
  `stock_actual` int DEFAULT NULL,
  `descripcion_prod` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_detalles`),
  KEY `fk_detalles_comprobante` (`id_comprobante`),
  KEY `fk_detalles_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

DROP TABLE IF EXISTS `documentos`;
CREATE TABLE IF NOT EXISTS `documentos` (
  `id_documentos` int NOT NULL AUTO_INCREMENT,
  `fk_novedad` int DEFAULT NULL,
  `fk_material` int DEFAULT NULL,
  PRIMARY KEY (`id_documentos`),
  KEY `fk_documentos_materiales` (`fk_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

DROP TABLE IF EXISTS `empresa`;
CREATE TABLE IF NOT EXISTS `empresa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nif` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id`, `nombre`, `nif`, `direccion`, `ciudad`, `telefono`, `email`, `logo`, `updated_at`) VALUES
(2, 'siii', '312321', 'Carrera 14', 'Barcelona', '3166678284', 'k3vinch3nl1@gmail.com', NULL, '2025-06-26 18:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fk_materiales_proveedores`
--

DROP TABLE IF EXISTS `fk_materiales_proveedores`;
CREATE TABLE IF NOT EXISTS `fk_materiales_proveedores` (
  `id_material` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL,
  PRIMARY KEY (`id_material`,`id_proveedor`),
  KEY `fk_fk_materiales_proveedores_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_acciones`
--

DROP TABLE IF EXISTS `historial_acciones`;
CREATE TABLE IF NOT EXISTS `historial_acciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo_accion` varchar(50) COLLATE utf8mb3_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb3_spanish_ci NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `historial_acciones`
--

INSERT INTO `historial_acciones` (`id`, `usuario_id`, `tipo_accion`, `descripcion`, `fecha`) VALUES
(1, 11, 'crear', 'Agregó la categoría: papeles', '2025-07-29 14:53:01'),
(2, 11, 'agregar_producto', 'Agregó el producto ID: 2 — Nombre: refrigeradora', '2025-07-29 14:53:35'),
(3, 11, 'crear', 'Agregó la categoría: dfsdfsf', '2025-07-29 15:02:13'),
(4, 11, 'eliminar', 'Eliminó la categoría ID: 4', '2025-07-29 15:02:34'),
(5, 11, 'agregar_producto', 'Agregó el producto ID: 3 — Nombre: Casco de Seguridad', '2025-07-29 15:12:43'),
(6, 11, 'editar_producto', 'Editó el producto ID: 3 — Nuevo nombre: corasa bruta', '2025-07-29 15:23:29'),
(7, 11, 'desactivado', 'Desactivó el producto ID: 3 — Nombre: corasa bruta', '2025-07-29 15:30:31'),
(8, 11, 'entrada', 'Se agregaron 345 unidades de aguijon llameante', '2025-07-29 15:33:26'),
(9, 11, 'desactivado', 'Desactivó el producto ID: 3 — Nombre: corasa bruta', '2025-07-29 15:40:58'),
(10, 11, 'eliminar', 'Eliminó la categoría ID: 2', '2025-07-29 15:41:05'),
(11, 11, 'eliminar_producto', 'Eliminó el producto ID: 4 — Nombre: aguijon llameante', '2025-07-29 15:41:11'),
(12, 11, 'crear', 'Agregó la categoría: quemaduras', '2025-07-29 15:42:04'),
(13, 11, 'entrada', 'Se agregaron 500 unidades de escudo infernal', '2025-07-29 15:42:30'),
(14, 11, 'editar_producto', 'Editó el producto ID: 2 — Nuevo nombre: tostadora', '2025-07-29 16:15:40'),
(15, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:28:48'),
(16, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:28:54'),
(17, 11, 'editar_producto', 'Editó el producto ID: 1 — Nuevo nombre: canela', '2025-07-29 16:29:05'),
(18, 11, 'editar_producto', 'Editó el producto ID: 1 — Nuevo nombre: canela', '2025-07-29 16:29:17'),
(19, 11, 'eliminar', 'Eliminó la categoría ID: 1', '2025-07-29 16:29:32'),
(20, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:29:37'),
(21, 11, 'crear', 'Agregó la categoría: Electrópajas 3mil', '2025-07-29 16:30:00'),
(22, 11, 'editar', 'Editó la categoría ID: 6 — Nuevo nombre: Electrópajas 3mil', '2025-07-29 16:30:12'),
(23, 11, 'editar_producto', 'Editó el producto ID: 1 — Nuevo nombre: canela', '2025-07-29 16:30:45'),
(24, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:30:57'),
(25, 11, 'eliminar', 'Eliminó la categoría ID: 6', '2025-07-29 16:31:01'),
(26, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:31:06'),
(27, 11, 'eliminar', 'Eliminó la categoría ID: 3', '2025-07-29 16:31:14'),
(28, 11, 'desactivado', 'Desactivó el producto ID: 3 — Nombre: corasa bruta', '2025-07-29 16:31:18'),
(29, 11, 'desactivado', 'Desactivó el producto ID: 2 — Nombre: tostadora', '2025-07-29 16:31:24'),
(30, 11, 'editar_producto', 'Editó el producto ID: 3 — Nuevo nombre: corasa bruta', '2025-07-29 16:31:40'),
(31, 11, 'desactivado', 'Desactivó el producto ID: 3 — Nombre: corasa bruta', '2025-07-29 16:31:44'),
(32, 11, 'editar', 'Editó la categoría ID: 5 — Nuevo nombre: lifesteal', '2025-07-29 16:32:02'),
(33, 11, 'desactivado', 'Desactivó el producto ID: 3 — Nombre: corasa bruta', '2025-07-29 16:32:06'),
(34, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:32:08'),
(35, 11, 'crear', 'Agregó la categoría: salpicon', '2025-07-29 16:33:37'),
(36, 11, 'editar_producto', 'Editó el producto ID: 1 — Nuevo nombre: canela', '2025-07-29 16:33:48'),
(37, 11, 'eliminar', 'Eliminó la categoría ID: 7', '2025-07-29 16:33:52'),
(38, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:33:55'),
(39, 11, 'desactivado', 'Desactivó el producto ID: 1 — Nombre: canela', '2025-07-29 16:36:10'),
(40, 11, 'entrada', 'Se agregaron 11 unidades de refrigeradora', '2025-07-29 16:39:02'),
(41, 11, 'eliminar_producto', 'Eliminó el producto ID: 6 — Nombre: refrigeradora', '2025-07-29 16:39:07'),
(42, 3, 'crear', 'Agregó la categoría: champu de coco', '2025-07-29 20:25:48'),
(43, 3, 'editar_producto', 'Editó el producto ID: 1 — Nuevo nombre: canela', '2025-07-29 20:25:58'),
(44, 3, 'editar_producto', 'Editó el producto ID: 2 — Nuevo nombre: tostadora', '2025-07-29 20:26:03'),
(45, 3, 'editar', 'Editó la categoría ID: 5 — Nuevo nombre: lifesteal', '2025-07-29 20:29:46'),
(46, 3, 'editar', 'Editó la categoría ID: 8 — Nuevo nombre: champu de coco', '2025-07-29 20:29:54'),
(47, 3, 'entrada', 'Se agregaron 20 unidades de papa aborrajada', '2025-07-29 20:55:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

DROP TABLE IF EXISTS `materiales`;
CREATE TABLE IF NOT EXISTS `materiales` (
  `id_material` int NOT NULL AUTO_INCREMENT,
  `id_categorias` int DEFAULT NULL,
  `nombre_material` varchar(60) COLLATE utf8mb4_spanish_ci NOT NULL,
  `stock` int DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT '0.00',
  `disponibilidad` tinyint(1) DEFAULT NULL,
  `minimo_alarma` tinyint(1) DEFAULT NULL,
  `fk_reporte` int DEFAULT NULL,
  `fk_ubicacion` int DEFAULT NULL,
  `conf_recibido` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_material`),
  UNIQUE KEY `nombre_material` (`nombre_material`),
  KEY `fk_materiales_reportes` (`fk_reporte`),
  KEY `fk_materiales_ubicaciones` (`fk_ubicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id_material`, `id_categorias`, `nombre_material`, `stock`, `precio`, `disponibilidad`, `minimo_alarma`, `fk_reporte`, `fk_ubicacion`, `conf_recibido`) VALUES
(2, 1, 'CAJITA', 5, '70000.00', 1, 5, NULL, NULL, 'recibido'),
(4, 2, 'CAJA', 57, '24.00', 1, 5, NULL, NULL, 'recibido'),
(6, 1, 'hola', 85, '41000.00', 1, 5, NULL, NULL, 'recibido');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

DROP TABLE IF EXISTS `movimientos`;
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` int NOT NULL,
  `tipo` enum('entrada','salida','ajuste') COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `usuario_id` int NOT NULL,
  `notas` text COLLATE utf8mb4_general_ci,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `tipo`, `fecha`, `producto_id`, `cantidad`, `usuario_id`, `notas`, `creado_en`) VALUES
(1, 'salida', '2025-06-26', 3, 23, 2, 'dsasfasfaf', '2025-06-26 05:24:24'),
(2, 'salida', '2025-06-26', 3, 23, 4, 'qeewqeqwe', '2025-06-26 05:31:37'),
(3, 'ajuste', '2025-06-30', 5, 12, 4, 'qqqq', '2025-06-26 05:32:13'),
(4, 'salida', '2025-06-26', 2, 12, 4, 'asdasdasd', '2025-06-26 05:40:18'),
(5, 'salida', '2025-06-30', 3, 23, 4, 'sdsdsd', '2025-06-26 05:43:46'),
(0, 'entrada', '2025-06-26', 1, 4, 3, 'entrada', '2025-06-26 23:05:30'),
(0, 'entrada', '2025-07-29', 3, 1500, 11, 'se dañaron gvon', '2025-07-29 19:48:03'),
(0, 'salida', '2025-07-28', 1, 2, 3, 'se fue mk', '2025-07-29 20:22:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `notify_low_stock` tinyint(1) DEFAULT '0',
  `low_stock_threshold` int DEFAULT '15',
  `notify_movements` tinyint(1) DEFAULT '0',
  `notify_email` tinyint(1) DEFAULT '0',
  `notification_emails` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `notify_low_stock`, `low_stock_threshold`, `notify_movements`, `notify_email`, `notification_emails`) VALUES
(0, 3, 1, 15, 0, 1, 'k3vinch3nl1@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE IF NOT EXISTS `proveedor` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `entidad` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_material` int DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`),
  KEY `fk_materiales_proveedores_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

DROP TABLE IF EXISTS `reportes`;
CREATE TABLE IF NOT EXISTS `reportes` (
  `id_reporte` int NOT NULL AUTO_INCREMENT,
  `fecha_actual` datetime DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT NULL,
  `tipo_incidente` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `notificador` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `prioridad` varchar(5) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `soporte` int DEFAULT NULL,
  `fk_seguimiento` int DEFAULT NULL,
  PRIMARY KEY (`id_reporte`),
  KEY `fk_reportes_seguimiento` (`fk_seguimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_generados`
--

DROP TABLE IF EXISTS `reportes_generados`;
CREATE TABLE IF NOT EXISTS `reportes_generados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `tipo_reporte` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `formato` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_generado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archivo_url` text COLLATE utf8mb4_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `reportes_generados`
--

INSERT INTO `reportes_generados` (`id`, `titulo`, `descripcion`, `tipo_reporte`, `formato`, `fecha_inicio`, `fecha_fin`, `fecha_generado`, `archivo_url`) VALUES
(1, 'Reporte de Prueba', 'Este es un reporte de prueba', 'movimientos', 'pdf', '2024-01-01', '2024-01-31', '2025-06-26 23:56:49', 'test.pdf'),
(3, 'Test Reporte 2025-06-27 21:06:25', 'Notas de prueba', 'inventario', 'PDF', '2024-01-01', '2024-12-31', '2025-06-27 19:06:25', 'reportes/test_685eebb1af2e4.pdf'),
(4, 'Reporte de Inventario Actual', '', 'inventario', 'pdf', '2025-05-27', '2025-06-27', '2025-06-28 00:57:01', 'reportes/685f3ddd794b9.pdf'),
(5, 'Reporte de Movimientos de Inventario', '', 'movimientos', 'pdf', '2025-06-26', '2025-06-24', '2025-06-28 00:57:42', 'reportes/685f3e069d21f.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

DROP TABLE IF EXISTS `seguimiento`;
CREATE TABLE IF NOT EXISTS `seguimiento` (
  `id_seguimiento` int NOT NULL AUTO_INCREMENT,
  `fk_reporte` varchar(40) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `devolucion` int NOT NULL,
  PRIMARY KEY (`id_seguimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

DROP TABLE IF EXISTS `subcategorias`;
CREATE TABLE IF NOT EXISTS `subcategorias` (
  `id_subcategorias` int NOT NULL AUTO_INCREMENT,
  `fk_material` int DEFAULT NULL,
  `nombre_subcategoria` varchar(35) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_subcategorias`),
  KEY `fk_subcategorias_material` (`fk_material`),
  KEY `fk_subcategorias_categorias` (`id_subcategorias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

DROP TABLE IF EXISTS `ubicaciones`;
CREATE TABLE IF NOT EXISTS `ubicaciones` (
  `id_ubicaciones` int NOT NULL AUTO_INCREMENT,
  `nombre_ubicacion` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_ubicaciones`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuarios` int NOT NULL AUTO_INCREMENT,
  `num_doc` int NOT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(25) COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` enum('administrador','usuario','almacenista','supervisor') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario',
  `cargos` varchar(30) COLLATE utf8mb4_spanish_ci NOT NULL,
  `correo` varchar(64) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `num_telefono` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `ultimo_conexion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(8) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_usuarios`),
  UNIQUE KEY `num_doc_unique` (`num_doc`),
  KEY `fk_usuarios_roles` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `num_doc`, `nombre`, `apellido`, `rol`, `cargos`, `correo`, `contrasena`, `num_telefono`, `fecha_creacion`, `ultimo_conexion`, `estado`) VALUES
(1, 100000001, 'Admin', 'Sistema', 'administrador', 'Administrador general', 'admin@inventario.com', '12345678', '1234567890', '2025-06-09 20:52:31', '2025-06-16 21:39:53', 'ACTIVO'),
(3, 1234567890, 'dummie', 'dump', 'almacenista', 'almacenista local', 'dummie@arco.com', '0987654321', '1234567890', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'ACTIVO');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD CONSTRAINT `fk_comprobante_cliente` FOREIGN KEY (`cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_comprobante_encargado` FOREIGN KEY (`encargado`) REFERENCES `usuarios` (`num_doc`);

--
-- Filtros para la tabla `detalles`
--
ALTER TABLE `detalles`
  ADD CONSTRAINT `fk_detalles_comprobante` FOREIGN KEY (`id_comprobante`) REFERENCES `comprobante` (`id_comprobante`),
  ADD CONSTRAINT `fk_detalles_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `fk_documentos_materiales` FOREIGN KEY (`fk_material`) REFERENCES `materiales` (`id_material`);

--
-- Filtros para la tabla `fk_materiales_proveedores`
--
ALTER TABLE `fk_materiales_proveedores`
  ADD CONSTRAINT `fk_fk_materiales_proveedores_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`),
  ADD CONSTRAINT `fk_fk_materiales_proveedores_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`);

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `fk_materiales_reportes` FOREIGN KEY (`fk_reporte`) REFERENCES `reportes` (`id_reporte`),
  ADD CONSTRAINT `fk_materiales_ubicaciones` FOREIGN KEY (`fk_ubicacion`) REFERENCES `ubicaciones` (`id_ubicaciones`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reportes_seguimiento` FOREIGN KEY (`fk_seguimiento`) REFERENCES `seguimiento` (`id_seguimiento`);

--
-- Filtros para la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `fk_subcategorias_categorias` FOREIGN KEY (`id_subcategorias`) REFERENCES `categorias` (`id_categorias`),
  ADD CONSTRAINT `fk_subcategorias_material` FOREIGN KEY (`fk_material`) REFERENCES `materiales` (`id_material`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
