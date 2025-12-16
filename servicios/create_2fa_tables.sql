-- Tabla para códigos de verificación 2FA
CREATE TABLE IF NOT EXISTS `verification_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_spanish_ci NOT NULL,
  `type` enum('email','sms') COLLATE utf8mb4_spanish_ci DEFAULT 'email',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_verification_user` (`user_id`),
  CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Agregar campos de 2FA a la tabla usuarios
ALTER TABLE `usuarios` 
ADD COLUMN `two_factor_enabled` tinyint(1) DEFAULT '0',
ADD COLUMN `two_factor_method` enum('email','sms') COLLATE utf8mb4_spanish_ci DEFAULT 'email';

-- Tabla para roles y permisos
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Insertar roles por defecto
INSERT INTO `roles` (`nombre`, `descripcion`) VALUES
('administrador', 'Acceso completo al sistema'),
('supervisor', 'Supervisión de operaciones y reportes'),
('almacenista', 'Gestión de inventario y movimientos'),
('usuario', 'Acceso básico de consulta');

-- Tabla para permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `modulo` varchar(30) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Insertar permisos por defecto
INSERT INTO `permisos` (`nombre`, `descripcion`, `modulo`) VALUES
('ver_dashboard', 'Ver panel principal', 'dashboard'),
('gestionar_usuarios', 'Crear, editar y eliminar usuarios', 'usuarios'),
('gestionar_productos', 'Crear, editar y eliminar productos', 'productos'),
('gestionar_categorias', 'Crear, editar y eliminar categorías', 'categorias'),
('ver_movimientos', 'Ver movimientos de inventario', 'movimientos'),
('crear_movimientos', 'Crear movimientos de inventario', 'movimientos'),
('ver_reportes', 'Ver reportes del sistema', 'reportes'),
('generar_reportes', 'Generar nuevos reportes', 'reportes'),
('configurar_sistema', 'Acceso a configuración del sistema', 'configuracion'),
('gestionar_roles', 'Crear y asignar roles', 'roles'),
('ver_auditoria', 'Ver registro de auditoría', 'auditoria');

-- Tabla de relación roles-permisos
CREATE TABLE IF NOT EXISTS `rol_permisos` (
  `rol_id` int NOT NULL,
  `permiso_id` int NOT NULL,
  PRIMARY KEY (`rol_id`, `permiso_id`),
  KEY `fk_rol_permisos_permiso` (`permiso_id`),
  CONSTRAINT `fk_rol_permisos_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rol_permisos_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Asignar permisos a roles por defecto
-- Administrador: todos los permisos
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 1, id FROM permisos;

-- Supervisor: permisos de supervisión
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`) VALUES
(2, 1), (2, 5), (2, 7), (2, 8), (2, 11);

-- Almacenista: gestión de inventario
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`) VALUES
(3, 1), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7);

-- Usuario: solo consulta
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`) VALUES
(4, 1), (4, 5), (4, 7);

-- Tabla para órdenes de compra
CREATE TABLE IF NOT EXISTS `ordenes_compra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_orden` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `proveedor` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_pedido` date NOT NULL,
  `fecha_esperada` date DEFAULT NULL,
  `estado` enum('pendiente','recibida','cancelada','parcial') COLLATE utf8mb4_spanish_ci DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT '0.00',
  `usuario_id` int NOT NULL,
  `notas` text COLLATE utf8mb4_spanish_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_orden_unique` (`numero_orden`),
  KEY `fk_orden_usuario` (`usuario_id`),
  CONSTRAINT `fk_orden_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla para detalles de órdenes de compra
CREATE TABLE IF NOT EXISTS `orden_detalles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `orden_id` int NOT NULL,
  `material_id` int NOT NULL,
  `cantidad_pedida` int NOT NULL,
  `cantidad_recibida` int DEFAULT '0',
  `precio_unitario` decimal(8,2) DEFAULT '0.00',
  `subtotal` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_detalle_orden` (`orden_id`),
  KEY `fk_detalle_material` (`material_id`),
  CONSTRAINT `fk_detalle_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_material` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla para anomalías/novedades
CREATE TABLE IF NOT EXISTS `anomalias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` enum('discrepancia','daño','error_recepcion','error_envio','otro') COLLATE utf8mb4_spanish_ci NOT NULL,
  `prioridad` enum('baja','media','alta','critica') COLLATE utf8mb4_spanish_ci DEFAULT 'media',
  `estado` enum('abierta','en_proceso','resuelta','cerrada') COLLATE utf8mb4_spanish_ci DEFAULT 'abierta',
  `material_id` int DEFAULT NULL,
  `usuario_reporta` int NOT NULL,
  `usuario_asignado` int DEFAULT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  `notas_resolucion` text COLLATE utf8mb4_spanish_ci,
  PRIMARY KEY (`id`),
  KEY `fk_anomalia_material` (`material_id`),
  KEY `fk_anomalia_reporta` (`usuario_reporta`),
  KEY `fk_anomalia_asignado` (`usuario_asignado`),
  CONSTRAINT `fk_anomalia_material` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id_material`),
  CONSTRAINT `fk_anomalia_reporta` FOREIGN KEY (`usuario_reporta`) REFERENCES `usuarios` (`id_usuarios`),
  CONSTRAINT `fk_anomalia_asignado` FOREIGN KEY (`usuario_asignado`) REFERENCES `usuarios` (`id_usuarios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla para devoluciones
CREATE TABLE IF NOT EXISTS `devoluciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_devolucion` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `material_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `motivo` enum('defectuoso','incorrecto','no_requerido','vencido','otro') COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `estado` enum('pendiente','procesada','rechazada') COLLATE utf8mb4_spanish_ci DEFAULT 'pendiente',
  `usuario_solicita` int NOT NULL,
  `usuario_procesa` int DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_procesado` timestamp NULL DEFAULT NULL,
  `notas` text COLLATE utf8mb4_spanish_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_devolucion_unique` (`numero_devolucion`),
  KEY `fk_devolucion_material` (`material_id`),
  KEY `fk_devolucion_solicita` (`usuario_solicita`),
  KEY `fk_devolucion_procesa` (`usuario_procesa`),
  CONSTRAINT `fk_devolucion_material` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id_material`),
  CONSTRAINT `fk_devolucion_solicita` FOREIGN KEY (`usuario_solicita`) REFERENCES `usuarios` (`id_usuarios`),
  CONSTRAINT `fk_devolucion_procesa` FOREIGN KEY (`usuario_procesa`) REFERENCES `usuarios` (`id_usuarios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Mejorar tabla de materiales agregando precio
ALTER TABLE `materiales` 
ADD COLUMN `precio_unitario` decimal(8,2) DEFAULT '0.00',
ADD COLUMN `fecha_ultima_entrada` timestamp NULL DEFAULT NULL,
ADD COLUMN `proveedor_principal` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL;

-- Índices adicionales para mejor rendimiento
CREATE INDEX idx_movimientos_fecha ON movimientos(fecha);
CREATE INDEX idx_movimientos_tipo ON movimientos(tipo);
CREATE INDEX idx_materiales_stock ON materiales(stock);
CREATE INDEX idx_historial_fecha ON historial_acciones(fecha);
CREATE INDEX idx_usuarios_estado ON usuarios(estado);