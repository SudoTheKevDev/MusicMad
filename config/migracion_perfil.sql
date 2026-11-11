-- ============================================================
--  MusicMad – Migración: perfil de usuario
--  config/migracion_perfil.sql
--
--  Agrega a la tabla `usuarios` los campos necesarios para la
--  etiqueta/tarjeta de perfil (avatar, biografía y color de
--  acento). Ejecutar una sola vez sobre la base de datos ya
--  existente (bd_rutaPc-web.sql / if0_42109283_musicmad).
-- ============================================================

ALTER TABLE `usuarios`
  ADD COLUMN `avatar` VARCHAR(60) NOT NULL DEFAULT 'img/avatares/Avatares0.png' AFTER `apellido`,
  ADD COLUMN `bio` VARCHAR(280) NOT NULL DEFAULT '' AFTER `avatar`,
  ADD COLUMN `color_perfil` VARCHAR(7) NOT NULL DEFAULT '#00d4ff' AFTER `bio`;
