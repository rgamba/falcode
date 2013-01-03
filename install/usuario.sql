/*
PHPlus 2.0
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `usuario`
-- ----------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '{"readonly":"true"}ID Usuario',
  `id_usuario_rol` int(11) unsigned DEFAULT NULL COMMENT '{"show":"nombre"}Rol',
  `usuario` varchar(255) DEFAULT NULL COMMENT 'Usuario',
  `pass` varchar(255) DEFAULT NULL COMMENT '{"hidden":"list"}Password',
  `nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre',
  `apellido_p` varchar(255) DEFAULT NULL COMMENT '{"hidden":"list"}Apellido paterno',
  `apellido_m` varchar(255) DEFAULT NULL COMMENT '{"hidden":"list"}Apellido materno',
  `email` varchar(100) CHARACTER SET latin1 DEFAULT NULL COMMENT '{"hidden":"list"}Email',
  `session_id` varchar(255) CHARACTER SET latin1 DEFAULT NULL COMMENT '{"hidden":"list"}ID Sesion Activa',
  `bloqueado` int(1) NOT NULL COMMENT '{"hidden":"list"}Bloqueado',
  `eliminado` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '{"hidden":"list"}Eliminado',
  `fecha_creacion` datetime DEFAULT NULL COMMENT '{"hidden":"list"}Fecha de creacion',
  `activo` int(1) NOT NULL DEFAULT '1' COMMENT '{"hidden":"list"}Activo',
  PRIMARY KEY (`id_usuario`),
  KEY `id_usuario_rol` (`id_usuario_rol`) USING BTREE,
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_usuario_rol`) REFERENCES `usuario_rol` (`id_usuario_rol`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of usuario
-- ----------------------------
INSERT INTO `usuario` VALUES ('1', '3', 'admin', 'admin', 'Admin', 'Admin', 'Admin', null, null, '0', '0', null, '1');

-- ----------------------------
-- Table structure for `usuario_permiso`
-- ----------------------------
DROP TABLE IF EXISTS `usuario_permiso`;
CREATE TABLE `usuario_permiso` (
  `id_usuario_permiso` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario_rol` int(11) unsigned DEFAULT NULL,
  `subdominio` varchar(100) DEFAULT NULL,
  `modo` enum('deny','allow') DEFAULT 'deny',
  `modulo` text,
  `accion` text,
  PRIMARY KEY (`id_usuario_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of usuario_permiso
-- ----------------------------
INSERT INTO `usuario_permiso` VALUES ('1', null, null, 'deny', null, null), ('2', null, null, 'allow', 'login', null), ('3', null, null, 'allow', 'image', null);

-- ----------------------------
-- Table structure for `usuario_rol`
-- ----------------------------
DROP TABLE IF EXISTS `usuario_rol`;
CREATE TABLE `usuario_rol` (
  `id_usuario_rol` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '{"readonly":"true"}ID Usuario Rol',
  `id_padre` int(11) unsigned DEFAULT NULL COMMENT 'ID Rol Padre',
  `nombre` varchar(255) DEFAULT NULL COMMENT 'Referencia',
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Tipo',
  PRIMARY KEY (`id_usuario_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of usuario_rol
-- ----------------------------
INSERT INTO `usuario_rol` VALUES ('1', null, 'public', 'Publico'), ('2', null, 'admin', 'Administrador'), ('3', null, 'super_admin', 'Super administrador');
