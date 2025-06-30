-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-06-2025 a las 19:44:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
  `nombre_cat` varchar(25) DEFAULT NULL,
  `subcategoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `categorias`(`id_categorias`, `nombre_cat`, `subcategoria`) VALUES 
('1','Electronicos','Telefono'),
('2','Oficina','Papeleria'),
('3','Herramientas','Manuales'),
('4','Limpieza','Detergentes'),
('5','Seguridad','EPP');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `numc_doc` int(11) DEFAULT NULL,
  `nombres` varchar(20) DEFAULT NULL,
  `apellidos` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante`
--

CREATE TABLE `comprobante` (
  `id_comprobante` int(11) NOT NULL,
  `num_comprobante` varchar(10) DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `direccion` varchar(40) DEFAULT NULL,
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
  `descripcion_prod` varchar(120) DEFAULT NULL
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
  `nombre` varchar(150) DEFAULT NULL,
  `nif` varchar(50) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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
  `nombre_material` varchar(60) NOT NULL,
  `stock` int(11) DEFAULT NULL,
  `disponibilidad` tinyint(1) DEFAULT NULL,
  `minimo_alarma` tinyint(1) DEFAULT NULL,
  `fk_reporte` int(11) DEFAULT NULL,
  `fk_ubicacion` int(11) DEFAULT NULL,
  `conf_recibido` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id_material`, `id_categorias`, `nombre_material`, `stock`, `disponibilidad`, `minimo_alarma`, `fk_reporte`, `fk_ubicacion`, `conf_recibido`) VALUES
(1, 1, 'Smartphone Samsung Galaxy', 2, 1, 5, NULL, NULL, 'recibido'),
(2, 1, 'Laptop Dell Inspiron', 15, 1, 10, NULL, NULL, 'recibido'),
(3, 2, 'Papel Bond A4', 8, 1, 20, NULL, NULL, 'recibido'),
(4, 2, 'Marcadores Permanentes', 25, 1, 15, NULL, NULL, 'recibido'),
(5, 3, 'Martillo de Acero', 12, 1, 8, NULL, NULL, 'recibido'),
(6, 3, 'Destornillador Phillips', 3, 1, 10, NULL, NULL, 'recibido'),
(7, 4, 'Detergente Líquido', 18, 1, 12, NULL, NULL, 'recibido'),
(8, 4, 'Escoba Industrial', 4, 1, 8, NULL, NULL, 'recibido'),
(9, 5, 'Casco de Seguridad', 22, 1, 15, NULL, NULL, 'recibido'),
(10, 5, 'Guantes de Protección', 6, 1, 20, NULL, NULL, 'recibido');

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
  `notas` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `tipo`, `fecha`, `producto_id`, `cantidad`, `usuario_id`, `notas`, `creado_en`) VALUES
(1, 'entrada', '2025-06-26', 1, 10, 2, 'Compra de smartphones Samsung', '2025-06-26 00:24:24'),
(2, 'salida', '2025-06-26', 1, 8, 4, 'Venta de smartphones a cliente corporativo', '2025-06-26 00:31:37'),
(3, 'entrada', '2025-06-26', 3, 50, 4, 'Recepción de papel bond A4', '2025-06-26 00:32:13'),
(4, 'salida', '2025-06-26', 3, 42, 4, 'Consumo de papel en oficinas', '2025-06-26 00:40:18'),
(5, 'ajuste', '2025-06-26', 6, 7, 4, 'Corrección de inventario destornilladores', '2025-06-26 00:43:46'),
(6, 'entrada', '2025-06-27', 2, 5, 2, 'Compra de laptops Dell', '2025-06-27 08:15:30'),
(7, 'transferencia', '2025-06-27', 5, 3, 4, 'Traslado de martillos a almacén B', '2025-06-27 10:22:15'),
(8, 'salida', '2025-06-27', 8, 4, 2, 'Uso de escobas en limpieza', '2025-06-27 14:30:45'),
(9, 'entrada', '2025-06-28', 10, 30, 4, 'Recepción de guantes de protección', '2025-06-28 09:45:20'),
(10, 'salida', '2025-06-28', 10, 24, 2, 'Distribución de guantes a trabajadores', '2025-06-28 16:20:10');




-- --------------------------------------------------------
-- Estructura de tabla para la tabla `notificaciones`
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `notify_low_stock` tinyint(1) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 15,
  `notify_movements` tinyint(1) DEFAULT 0,
  `notify_email` tinyint(1) DEFAULT 0,
  `notification_emails` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `entidad` varchar(25) DEFAULT NULL,
  `id_material` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `fecha_actual` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT NULL,
  `tipo_incidente` varchar(20) DEFAULT NULL,
  `notificador` varchar(20) DEFAULT NULL,
  `estado` varchar(10) DEFAULT NULL,
  `prioridad` varchar(5) DEFAULT NULL,
  `soporte` int(11) DEFAULT NULL,
  `fk_seguimiento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_generados`
--

CREATE TABLE `reportes_generados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_reporte` varchar(50) DEFAULT NULL,
  `formato` varchar(10) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_generado` timestamp NOT NULL DEFAULT current_timestamp(),
  `archivo_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `fk_reporte` varchar(40) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `devolucion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id_subcategorias` int(11) NOT NULL,
  `fk_material` int(11) DEFAULT NULL,
  `nombre_subcategoria` varchar(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicaciones` int(11) NOT NULL,
  `nombre_ubicacion` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuarios` int(11) NOT NULL,
  `num_doc` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(25) NOT NULL,
  `rol` enum('administrador','usuario','almacenista','supervisor') NOT NULL DEFAULT 'usuario',
  `cargos` varchar(30) NOT NULL,
  `correo` varchar(64) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `num_telefono` varchar(10) NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `ultimo_conexion` datetime DEFAULT current_timestamp(),
  `estado` varchar(8) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `num_doc`, `nombre`, `apellido`, `rol`, `cargos`, `correo`, `contrasena`, `num_telefono`, `fecha_creacion`, `ultimo_conexion`, `estado`) VALUES
(1, 100000001, 'Admin', 'Sistema', 'administrador', 'Administrador general', 'admin@inventario.com', 'c93ccd78b2076528346216b3b2f701e6', '1234567890', '2025-06-09 20:52:31', '2025-06-16 21:39:53', 'ACTIVO'),
(3, 1234567890, 'dummie', 'dump', 'almacenista', 'almacenista local', 'dummie@arco.com', '0987654321', '1234567890', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'ACTIVO');

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
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_usuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
