-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 13-08-2025 a las 19:46:48
-- Versión del servidor: 5.7.24
-- Versión de PHP: 8.2.14

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

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `nombre_cat` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `subcategorias` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `productos` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nombre_cat`, `subcategorias`, `estado`, `productos`) VALUES
(1, 'Electronicos', 0, 1, 0),
(2, 'Aji píque', 0, 0, 23);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `numc_doc` int(11) DEFAULT NULL,
  `nombres` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `apellidos` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante`
--

CREATE TABLE `comprobante` (
  `id_comprobante` int(11) NOT NULL,
  `num_comprobante` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `direccion` varchar(40) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `encargado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles`
--

CREATE TABLE `detalles` (
  `id_detalles` int(11) NOT NULL,
  `id_material` int(11) DEFAULT NULL,
  `id_comprobante` int(11) DEFAULT NULL,
  `cantidades` int(11) DEFAULT NULL,
  `stock_actual` int(11) DEFAULT NULL,
  `descripcion_prod` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documentos` int(11) NOT NULL,
  `fk_novedad` int(11) DEFAULT NULL,
  `fk_material` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nif` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id`, `nombre`, `nif`, `direccion`, `ciudad`, `telefono`, `email`, `logo`, `updated_at`) VALUES
(2, 'siii', '312321', 'Carrera 14', 'Barcelona', '3166678284', 'k3vinch3nl1@gmail.com', NULL, '2025-06-26 18:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fk_materiales_proveedores`
--

CREATE TABLE `fk_materiales_proveedores` (
  `id_material` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id_material` int(11) NOT NULL,
  `id_categorias` int(11) DEFAULT NULL,
  `nombre_material` varchar(60) COLLATE utf8mb4_spanish_ci NOT NULL,
  `stock` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT '0.00',
  `disponibilidad` tinyint(1) DEFAULT NULL,
  `minimo_alarma` tinyint(1) DEFAULT NULL,
  `fk_reporte` int(11) DEFAULT NULL,
  `fk_ubicacion` int(11) DEFAULT NULL,
  `conf_recibido` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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

CREATE TABLE `movimientos` (
  `id` int(11) NOT NULL,
  `tipo` enum('entrada','salida','ajuste') NOT NULL,
  `fecha` date NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `notas` text,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `tipo`, `fecha`, `producto_id`, `cantidad`, `usuario_id`, `notas`, `creado_en`) VALUES
(1, 'salida', '2025-06-26', 3, 23, 2, 'dsasfasfaf', '2025-06-26 05:24:24'),
(2, 'salida', '2025-06-26', 3, 23, 4, 'qeewqeqwe', '2025-06-26 05:31:37'),
(3, 'ajuste', '2025-06-30', 5, 12, 4, 'qqqq', '2025-06-26 05:32:13'),
(4, 'salida', '2025-06-26', 2, 12, 4, 'asdasdasd', '2025-06-26 05:40:18'),
(5, 'salida', '2025-06-30', 3, 23, 4, 'sdsdsd', '2025-06-26 05:43:46'),
(0, 'entrada', '2025-06-26', 1, 4, 3, 'entrada', '2025-06-26 23:05:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `notify_low_stock` tinyint(1) DEFAULT '0',
  `low_stock_threshold` int(11) DEFAULT '15',
  `notify_movements` tinyint(1) DEFAULT '0',
  `notify_email` tinyint(1) DEFAULT '0',
  `notification_emails` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `notify_low_stock`, `low_stock_threshold`, `notify_movements`, `notify_email`, `notification_emails`) VALUES
(0, 3, 1, 15, 0, 1, 'k3vinch3nl1@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `entidad` varchar(25) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_material` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `fecha_actual` datetime DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT NULL,
  `tipo_incidente` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `notificador` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `prioridad` varchar(5) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `soporte` int(11) DEFAULT NULL,
  `fk_seguimiento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_generados`
--

CREATE TABLE `reportes_generados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `tipo_reporte` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `formato` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_generado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archivo_url` text COLLATE utf8mb4_spanish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `fk_reporte` varchar(40) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `devolucion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id_subcategorias` int(11) NOT NULL,
  `fk_material` int(11) DEFAULT NULL,
  `nombre_subcategoria` varchar(35) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicaciones` int(11) NOT NULL,
  `nombre_ubicacion` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuarios` int(11) NOT NULL,
  `num_doc` int(11) NOT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(25) COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` enum('administrador','usuario','almacenista','supervisor') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario',
  `cargos` varchar(30) COLLATE utf8mb4_spanish_ci NOT NULL,
  `correo` varchar(64) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `num_telefono` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `ultimo_conexion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(8) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `num_doc`, `nombre`, `apellido`, `rol`, `cargos`, `correo`, `contrasena`, `num_telefono`, `fecha_creacion`, `ultimo_conexion`, `estado`) VALUES
(1, 100000001, 'Admin', 'Sistema', 'administrador', 'Administrador general', 'admin@inventario.com', 'c93ccd78b2076528346216b3b2f701e6', '1234567890', '2025-06-09 20:52:31', '2025-06-16 21:39:53', 'ACTIVO'),
(3, 1234567890, 'dummie', 'dump', 'almacenista', 'almacenista local', 'dummie@arco.com', '0987654321', '1234567890', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'ACTIVO'),
(4, 1107841204, 'santiago', 'garcia cardenas', 'usuario', 'sin definir', 'santiagogarcia0434@gmail.com', '$2y$10$2iayiml8JCLQz92/sXVh3..AQa/OF2Z9FhYyb/J3oZLWPixjtuC16', '0000000000', '2025-08-06 22:09:58', '2025-08-06 17:09:58', 'ACTIVO');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD PRIMARY KEY (`id_comprobante`),
  ADD KEY `fk_comprobante_encargado` (`encargado`),
  ADD KEY `fk_comprobante_cliente` (`cliente`);

--
-- Indices de la tabla `detalles`
--
ALTER TABLE `detalles`
  ADD PRIMARY KEY (`id_detalles`),
  ADD KEY `fk_detalles_comprobante` (`id_comprobante`),
  ADD KEY `fk_detalles_material` (`id_material`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documentos`),
  ADD KEY `fk_documentos_materiales` (`fk_material`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fk_materiales_proveedores`
--
ALTER TABLE `fk_materiales_proveedores`
  ADD PRIMARY KEY (`id_material`,`id_proveedor`),
  ADD KEY `fk_fk_materiales_proveedores_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id_material`),
  ADD UNIQUE KEY `nombre_material` (`nombre_material`),
  ADD KEY `fk_materiales_reportes` (`fk_reporte`),
  ADD KEY `fk_materiales_ubicaciones` (`fk_ubicacion`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD KEY `fk_materiales_proveedores_material` (`id_material`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reportes_seguimiento` (`fk_seguimiento`);

--
-- Indices de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`);

--
-- Indices de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id_subcategorias`),
  ADD KEY `fk_subcategorias_material` (`fk_material`),
  ADD KEY `fk_subcategorias_categorias` (`id_subcategorias`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicaciones`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuarios`),
  ADD UNIQUE KEY `num_doc_unique` (`num_doc`),
  ADD KEY `fk_usuarios_roles` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobante`
--
ALTER TABLE `comprobante`
  MODIFY `id_comprobante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles`
--
ALTER TABLE `detalles`
  MODIFY `id_detalles` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documentos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id_subcategorias` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id_ubicaciones` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
