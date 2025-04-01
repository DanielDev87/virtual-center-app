-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-04-2025 a las 18:03:26
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
-- Base de datos: `tdeaplication`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda`
--

CREATE TABLE `agenda` (
  `id_agenda` int(11) NOT NULL,
  `id_etiqueta` int(11) NOT NULL,
  `nombre_agenda` text NOT NULL,
  `correo_agenda` text NOT NULL,
  `img_agenda` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacoras`
--

CREATE TABLE `bitacoras` (
  `id_bitacora` int(11) NOT NULL,
  `id_agenda` int(11) DEFAULT NULL,
  `rol_bit` text DEFAULT NULL,
  `act_bit` text DEFAULT NULL,
  `horas_bit` varchar(3) DEFAULT NULL,
  `obs_bit` text DEFAULT NULL,
  `url_bit` text DEFAULT NULL,
  `fecha_bit` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cactividades`
--

CREATE TABLE `cactividades` (
  `id_actividad` int(11) NOT NULL,
  `tipo_actividad` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `desc_actividad` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `fecha_actividad` date NOT NULL,
  `hora_actividad` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clab_notas`
--

CREATE TABLE `clab_notas` (
  `id_clab_notas` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL,
  `tipo_nota` int(2) NOT NULL,
  `titulo_nota` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion_nota` text NOT NULL,
  `fecha_nota` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_cursos`
--

CREATE TABLE `comentarios_cursos` (
  `id_comentario_c` int(30) NOT NULL,
  `id_seguimiento` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL,
  `comentario` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_tarea` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `desc_html_formmms`
--

CREATE TABLE `desc_html_formmms` (
  `id_desc_html` int(11) NOT NULL,
  `id_form_med` int(11) NOT NULL,
  `desc_html_med` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id_etiqueta` int(11) NOT NULL,
  `nombre_etiqueta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etpro_curso`
--

CREATE TABLE `etpro_curso` (
  `id_etpro_curso` int(11) NOT NULL,
  `area_etpc` int(11) DEFAULT NULL,
  `nombre_etpc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_info_programa`
--

CREATE TABLE `tdea_info_programa` (
  `id_infoP` int(11) NOT NULL,
  `nombre_programa` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `desc_programa` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `img_programa` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_mas_html`
--

CREATE TABLE `tdea_mas_html` (
  `id_mas_html` int(11) NOT NULL,
  `mas_html` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_noticias`
--

CREATE TABLE `tdea_noticias` (
  `id_noticia` int(2) NOT NULL,
  `titulo_noticia` text DEFAULT NULL,
  `descripcion_noticia` text DEFAULT NULL,
  `fecha_noticia` text DEFAULT NULL,
  `enlace_noticia` text NOT NULL,
  `img_noticia` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_podcast`
--

CREATE TABLE `tdea_podcast` (
  `id_pod` int(11) NOT NULL,
  `campo1_pod` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_programa`
--

CREATE TABLE `tdea_programa` (
  `id_programa` int(11) NOT NULL,
  `hora_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `lunes_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `martes_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `miercoles_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `jueves_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `viernes_prog` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_repr`
--

CREATE TABLE `tdea_repr` (
  `id_repr` int(2) NOT NULL,
  `url_repr` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_usuarios`
--

CREATE TABLE `tdea_usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(30) NOT NULL,
  `correo_usuario` varchar(30) NOT NULL,
  `pw_usuario` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_vid_prin`
--

CREATE TABLE `tdea_vid_prin` (
  `id_vidprin` int(2) NOT NULL,
  `ifr_vidprin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `form_mediaciones`
--

CREATE TABLE `form_mediaciones` (
  `id_form_med` int(11) NOT NULL,
  `fase_form_med` text DEFAULT NULL,
  `ticket_form_med` text DEFAULT NULL,
  `fech_llegada` text DEFAULT NULL,
  `fech_asig` text DEFAULT NULL,
  `fech_entr` text DEFAULT NULL,
  `dise_visual` text DEFAULT NULL,
  `prod_audvisual` text DEFAULT NULL,
  `prod_radial` text NOT NULL,
  `darrollo_web` text DEFAULT NULL,
  `desc_form_med` longtext DEFAULT NULL,
  `solicitante_form_med` text DEFAULT NULL,
  `area_form_med` text DEFAULT NULL,
  `respon_proceso` text DEFAULT NULL,
  `estado_form_med` text DEFAULT NULL,
  `obs_form_med` longtext DEFAULT NULL,
  `estadoms_form_med` text DEFAULT NULL,
  `link_src` text DEFAULT NULL,
  `cod_stado` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id_institucion` int(11) NOT NULL,
  `nombre_institucion` varchar(50) NOT NULL,
  `siglas_institucion` varchar(30) NOT NULL,
  `logo_ins` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_tareas`
--

CREATE TABLE `lista_tareas` (
  `id_tarea` int(11) NOT NULL,
  `id_superadmin` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL,
  `nom_tarea` text DEFAULT NULL,
  `desc_tarea` longtext DEFAULT NULL,
  `fe_asignacion` text DEFAULT NULL,
  `estado_tarea` int(2) DEFAULT NULL,
  `fe_terminado` text DEFAULT NULL,
  `url_tarea` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tipo_tarea` int(2) DEFAULT NULL,
  `fe_entrega` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `on_course` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `nombre_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `programa_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `facultad_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `unidades_curso` varchar(3) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `id_tipo_material` int(11) DEFAULT NULL,
  `tipo_solicitud` varchar(30) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `asesor_curso` varchar(30) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `estado_ms` int(2) DEFAULT NULL,
  `nd_ticket` int(20) DEFAULT NULL,
  `prioridad` int(2) DEFAULT NULL,
  `estado_gn` int(2) DEFAULT NULL,
  `comentario_ad` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `dise_visual` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `prod_audiovisual` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `desarrollo_web` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_disvisual` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_disvisual` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_disvisual` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_proav` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_proav` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_proav` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_dweb` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_dweb` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_dweb` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_revcurri` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fenvio` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentrega` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `observa_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `correcciones_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fnueva_entrega` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fprogra_plataforma` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `dias_retraso` int(2) DEFAULT NULL,
  `url_seg` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `plataforma_curso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_ascurso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_ascurso` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_cestilo` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_cestilo` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_cestilo` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `responsable_lms` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fasig_lms` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `fentre_lms` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `gn_status` int(4) DEFAULT NULL,
  `desc_status` text CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `superadmin`
--

CREATE TABLE `superadmin` (
  `id_superadmin` int(11) NOT NULL,
  `nombre_super` varchar(30) NOT NULL,
  `apellido_super` varchar(30) NOT NULL,
  `doc_super` varchar(20) NOT NULL,
  `pw_super` varchar(20) NOT NULL,
  `fecha_super` date NOT NULL,
  `hora_super` time NOT NULL,
  `correo_superadmin` text NOT NULL,
  `ui_super` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `superadmin`
--

INSERT INTO `superadmin` (`id_superadmin`, `nombre_super`, `apellido_super`, `doc_super`, `pw_super`, `fecha_super`, `hora_super`, `correo_superadmin`, `ui_super`) VALUES
(10, 'Super', 'Usuario', '0000', '0', '2019-12-04', '18:05:02', 'user@root.com', 0),
(12, 'Yadir', 'Ortega', '1035832052', '0', '2020-07-15', '01:36:57', 'yfortegar@tdea.edu.co', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_material`
--

CREATE TABLE `tipo_material` (
  `id_tipo_material` int(11) NOT NULL,
  `nombre_tipo_material` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_activity_task`
--

CREATE TABLE `tdea_activity_task` (
  `id_activity` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `desc_act` text NOT NULL,
  `fecha_act` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_ajustecurso_cpm`
--

CREATE TABLE `tdea_ajustecurso_cpm` (
  `id_ajustecurso_cpm` int(14) NOT NULL,
  `id_info_ajuste` int(11) NOT NULL,
  `estado_ajustecurso_cpm` int(2) NOT NULL,
  `observacion_ajustecurso_cpm` longtext NOT NULL,
  `url_ajustecurso_cpm` text DEFAULT NULL,
  `sugerencia_ajustecurso_cpm` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_alerta_admin`
--

CREATE TABLE `tdea_alerta_admin` (
  `id_alerta_admin` int(11) NOT NULL,
  `tipo_alerta_admin` int(3) NOT NULL,
  `estado_alerta_admin` int(2) NOT NULL,
  `descripcion_alerta_admin` longtext NOT NULL,
  `url_alerta_admin` text NOT NULL,
  `fecha_alerta_admin` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notify_alerta_admin` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_alerta_usuarios`
--

CREATE TABLE `tdea_alerta_usuarios` (
  `id_alerta_usuario` int(11) NOT NULL,
  `id_tdea_usuario` int(11) NOT NULL,
  `tipo_alerta_usuario` int(3) NOT NULL,
  `estado_alerta_usuario` int(2) NOT NULL,
  `descripcion_alerta_usuario` longtext NOT NULL,
  `url_alerta_usuario` longtext NOT NULL,
  `fecha_alerta_usuario` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notify_alerta_usuario` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_alerta_visitantes`
--

CREATE TABLE `tdea_alerta_visitantes` (
  `id_alerta_visitante` int(11) NOT NULL,
  `id_tdea_visitante` int(11) NOT NULL,
  `tipo_alerta_visitante` int(3) NOT NULL,
  `estado_alerta_visitante` int(2) NOT NULL,
  `descripcion_alerta_visitante` longtext NOT NULL,
  `url_alerta_visitante` longtext NOT NULL,
  `fecha_alerta_visitante` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notify_alerta_visitante` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_bitacoras_usuario`
--

CREATE TABLE `tdea_bitacoras_usuario` (
  `id_bitacora` int(11) NOT NULL,
  `id_tdea_usuario` int(11) NOT NULL,
  `nombre_bitacora` text NOT NULL,
  `descripcion_bitacora` text NOT NULL,
  `inicio_fecha_hora` text NOT NULL,
  `fin_fecha_hora` text NOT NULL,
  `color_bitacora` text NOT NULL,
  `colortext_bitacora` text NOT NULL,
  `diferencia_horas` time DEFAULT NULL,
  `todo_dia` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_calificaciones_cpm`
--

CREATE TABLE `tdea_calificaciones_cpm` (
  `id_calif_cpm` int(11) NOT NULL,
  `id_cpm` int(11) NOT NULL,
  `valor_calif_cpm` int(2) NOT NULL,
  `comentario_calif_cpm` text DEFAULT NULL,
  `fecha_calif_cpm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_clab_cpm`
--

CREATE TABLE `tdea_clab_cpm` (
  `id_clab_cpm` int(22) NOT NULL,
  `id_cpm` int(11) NOT NULL,
  `ticket_cpm` bigint(14) NOT NULL,
  `id_tdea_usuario` int(11) NOT NULL,
  `fecha` text NOT NULL,
  `date_tdea_colab_cpm` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_comentarios_cursos`
--

CREATE TABLE `tdea_comentarios_cursos` (
  `id_comentario_curso` int(14) NOT NULL,
  `id_tdea_curso` int(11) DEFAULT NULL,
  `id_tdea_usuario` int(11) DEFAULT NULL,
  `comentario_curso` longtext DEFAULT NULL,
  `fecha_comentario_curso` text DEFAULT NULL,
  `date_comentario` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_cursos`
--

CREATE TABLE `tdea_cursos` (
  `id_tdea_curso` int(11) NOT NULL,
  `id_cpm` int(11) DEFAULT NULL,
  `ticket_cpm` varchar(14) DEFAULT NULL,
  `nombre_curso` text NOT NULL,
  `facultad_curso` int(11) NOT NULL,
  `programa_curso` int(11) NOT NULL,
  `tipo_solicitud_curso` int(2) NOT NULL,
  `tipo_curso` int(2) NOT NULL,
  `creditos_curso` int(2) DEFAULT NULL,
  `horas_curso` int(3) DEFAULT NULL,
  `fecha_solicitud_curso` text DEFAULT NULL,
  `fecha_limite_curso` text DEFAULT NULL,
  `nombre_experto_curso` text DEFAULT NULL,
  `correo_experto_curso` text DEFAULT NULL,
  `enlace_planeacion_curso` text DEFAULT NULL,
  `observaciones_curso` longtext DEFAULT NULL,
  `prioridad_curso` int(1) DEFAULT NULL,
  `responsable_asesoria_pedagogica` int(11) DEFAULT NULL,
  `fecha_asig_asesoria` text DEFAULT NULL,
  `fecha_entre_asesoria` text DEFAULT NULL,
  `responsable_correccion_estilo` int(11) DEFAULT NULL,
  `fecha_asig_correccion` text DEFAULT NULL,
  `fecha_entre_correccion` text DEFAULT NULL,
  `responsable_diseno_visual` int(11) DEFAULT NULL,
  `fecha_asig_diseno` text DEFAULT NULL,
  `fecha_entre_diseno` text DEFAULT NULL,
  `responsable_multimedia_curso` int(11) DEFAULT NULL,
  `fecha_asig_multimedia` text DEFAULT NULL,
  `fecha_entre_multimedia` text DEFAULT NULL,
  `responsable_radial_curso` int(11) DEFAULT NULL,
  `fecha_asig_radial` text DEFAULT NULL,
  `fecha_entre_radial` text DEFAULT NULL,
  `responsable_desarrollo_curso` int(11) DEFAULT NULL,
  `fecha_asig_desarrollo` text DEFAULT NULL,
  `fecha_entre_desarrollo` text DEFAULT NULL,
  `responsable_integracion_curso` int(11) DEFAULT NULL,
  `fecha_asig_integracion` text DEFAULT NULL,
  `fecha_entre_integracion` text DEFAULT NULL,
  `setup_cpm` int(2) DEFAULT NULL,
  `enlace_curso` text DEFAULT NULL,
  `estado_curso` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_desarrollo`
--

CREATE TABLE `tdea_desarrollo` (
  `id_desarrollo` int(11) NOT NULL,
  `id_cpm` int(11) NOT NULL,
  `tipo_desarrollo` varchar(40) NOT NULL,
  `fecha_entrega_desarrollo` text NOT NULL,
  `url_archivos_desarrollo` text DEFAULT NULL,
  `url_producto_desarrollo` text DEFAULT NULL,
  `info_desarrollo` text NOT NULL,
  `subarea_desarrollo` int(11) NOT NULL,
  `fecha_solicitud_desarrollo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_emisora`
--

CREATE TABLE `tdea_emisora` (
  `id_emisora` int(11) NOT NULL,
  `id_cpm` int(11) NOT NULL,
  `tipo_solicitud_emisora` text NOT NULL,
  `fecha_entrega_emisora` text NOT NULL,
  `url_indicaciones_emisora` text NOT NULL,
  `observacion_emisora` text NOT NULL,
  `producto_emisora` text DEFAULT NULL,
  `subarea_emisora` int(11) NOT NULL,
  `fecha_solicitud_emisora` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_facultades`
--

CREATE TABLE `tdea_facultades` (
  `id_tdea_facultad` int(11) NOT NULL,
  `nombre_tdea_facultad` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_info_ajustecurso_cpm`
--

CREATE TABLE `tdea_info_ajustecurso_cpm` (
  `id_info_ajuste` int(11) NOT NULL,
  `id_cpm` int(11) DEFAULT NULL,
  `ticket_cpm` bigint(14) DEFAULT NULL,
  `facultad_iac_cpm` int(11) DEFAULT NULL,
  `programa_iac_cpm` int(11) DEFAULT NULL,
  `tipo_curso_iac_cpm` int(2) DEFAULT NULL,
  `nombre_curso_iac_cpm` text DEFAULT NULL,
  `fecha_entrega_iac_cpm` text DEFAULT NULL,
  `enlace_iac_cpm` text DEFAULT NULL,
  `observacion_iac_cpm` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_log_cpm`
--

CREATE TABLE `tdea_log_cpm` (
  `id_log_cpm` int(14) NOT NULL,
  `id_tdea_visitante` int(11) DEFAULT NULL,
  `ticket_cpm` bigint(14) DEFAULT NULL,
  `log_cpm` longtext DEFAULT NULL,
  `fecha_log_cpm` text DEFAULT NULL,
  `date_log_cpm` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_mediaciones_curso`
--

CREATE TABLE `tdea_mediaciones_curso` (
  `id_mediacion_curso` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `diseno_visual` int(1) DEFAULT NULL,
  `prod_multimedial` int(1) DEFAULT NULL,
  `prod_radial` int(1) DEFAULT NULL,
  `desarrollo_web` int(1) DEFAULT NULL,
  `total_elementos` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_monitores`
--

CREATE TABLE `tdea_monitores` (
  `id_monitor` int(11) NOT NULL,
  `nombre_monitor` text NOT NULL,
  `correo_monitor` text NOT NULL,
  `cargo_monitor` text NOT NULL,
  `estado_monitor` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_cpm`
--

CREATE TABLE `tdea_cpm` (
  `id_cpm` int(11) NOT NULL,
  `ticket_cpm` bigint(14) NOT NULL,
  `titulo_cpm` varchar(50) NOT NULL,
  `tipo_cpm` int(2) NOT NULL,
  `num_resume_cpm` int(2) NOT NULL,
  `estado_cpm` int(1) NOT NULL,
  `creado_cpm` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_cpm` text DEFAULT NULL,
  `propiedad_cpm` int(1) DEFAULT NULL,
  `solicitante_cpm` int(11) NOT NULL,
  `url_solicitante_cpm` text DEFAULT NULL,
  `info_solicitante_cpm` longtext DEFAULT NULL,
  `mediador_cpm` int(11) DEFAULT NULL,
  `info_mediador_cpm` longtext DEFAULT NULL,
  `check_points_cpm` int(3) DEFAULT NULL COMMENT 'Puntos checkeados para progreso',
  `all_points_cpm` int(3) DEFAULT NULL COMMENT 'Total de puntos para progreso',
  `calificacion_cpm` int(1) DEFAULT NULL COMMENT 'Comprobar calificacion',
  `prioridad_cpm` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_multimedia`
--

CREATE TABLE `tdea_multimedia` (
  `id_multimedia` int(11) NOT NULL,
  `id_cpm` int(11) DEFAULT NULL,
  `tipo_solicitud_multimedia` int(11) NOT NULL,
  `fecha_entrega_multimedia` text NOT NULL,
  `url_guion_multimedia` text NOT NULL,
  `observacion_multimedia` longtext DEFAULT NULL,
  `camara_multimedia` int(2) DEFAULT NULL,
  `microfono_multimedia` int(2) DEFAULT NULL,
  `luces_multimedia` int(2) DEFAULT NULL,
  `sharepoint_multimedia` text DEFAULT NULL,
  `otra_url_multimedia` text DEFAULT NULL,
  `fecha_creacion_multimedia` text NOT NULL,
  `subarea_multimedia` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_notepad`
--

CREATE TABLE `tdea_notepad` (
  `id_note` int(11) NOT NULL,
  `id_superadmin` int(11) NOT NULL,
  `titulo_note` text NOT NULL,
  `tipo_note` int(2) NOT NULL,
  `desc_note` text NOT NULL,
  `fech_note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_programas`
--

CREATE TABLE `tdea_programas` (
  `id_tdea_programa` int(11) NOT NULL,
  `id_tdea_facultad` int(11) NOT NULL,
  `nombre_tdea_programa` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_recursos_interactivos`
--

CREATE TABLE `tdea_recursos_interactivos` (
  `id_recurso` int(11) NOT NULL,
  `id_cpm` int(11) NOT NULL,
  `id_tdea_programa` int(11) NOT NULL,
  `url_recurso` text NOT NULL,
  `fecha_entrega` text NOT NULL,
  `info_recurso` text NOT NULL,
  `url_producto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_spoof_user`
--

CREATE TABLE `tdea_spoof_user` (
  `id_spoof` int(11) NOT NULL,
  `id_superadmin` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_transmisiones`
--

CREATE TABLE `tdea_transmisiones` (
  `id_transmision` int(11) NOT NULL,
  `id_cpm` int(11) DEFAULT NULL,
  `fecha_transmision` text NOT NULL,
  `hora_transmision` text NOT NULL,
  `lugar_transmision` text NOT NULL,
  `nombre_evento_transmision` text NOT NULL,
  `observaciones_transmision` longtext NOT NULL,
  `camaras_transmision` int(2) DEFAULT NULL,
  `microfonos_transmision` int(2) DEFAULT NULL,
  `luces_transmision` int(2) DEFAULT NULL,
  `sharepoint_transmision` longtext DEFAULT NULL,
  `fecha_solicitud_transmision` text NOT NULL,
  `subarea_transmision` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_usuarios`
--

CREATE TABLE `tdea_usuarios` (
  `id_tdea_usuario` int(11) NOT NULL,
  `id_etiqueta` int(11) NOT NULL,
  `nombre_tdea_usuario` varchar(40) NOT NULL,
  `correo_tdea_usuario` text NOT NULL,
  `img_tdea_usuario` text DEFAULT NULL,
  `pw_tdea_usuario` varchar(20) DEFAULT NULL,
  `tipo_tdea_usuario` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tdea_usuarios`
--

INSERT INTO `tdea_usuarios` (`id_tdea_usuario`, `id_etiqueta`, `nombre_tdea_usuario`, `correo_tdea_usuario`, `img_tdea_usuario`, `pw_tdea_usuario`, `tipo_tdea_usuario`) VALUES
(5, 7, 'Usuario Prueba', 'users@correo.com', NULL, 'judaz', 0),
(16, 1, 'Daniel Agudelo', 'daniel@correo.com', NULL, '12345', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_validaciones_curso`
--

CREATE TABLE `tdea_validaciones_curso` (
  `id_validacion_curso` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `valor_val1` int(2) NOT NULL,
  `comentario_val1` text DEFAULT NULL,
  `valor_val2` int(2) NOT NULL,
  `comentario_val2` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdea_visitantes`
--

CREATE TABLE `tdea_visitantes` (
  `id_tdea_visitante` int(11) NOT NULL,
  `nombre_tdea_visitante` varchar(30) NOT NULL,
  `cargo_tdea_visitante` text NOT NULL,
  `correo_tdea_visitante` text NOT NULL,
  `estado_tdea_visitante` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nombre_user` varchar(30) NOT NULL,
  `apellido_user` varchar(30) NOT NULL,
  `email_user` varchar(50) NOT NULL,
  `pw_user` varchar(20) NOT NULL,
  `fecha_user` date NOT NULL,
  `hora_user` time NOT NULL,
  `rol_user` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `nombre_user`, `apellido_user`, `email_user`, `pw_user`, `fecha_user`, `hora_user`, `rol_user`) VALUES
(5, 'Usuario', 'Editor', 'invitado@tdea.edu.co', '0', '2019-08-14', '00:48:47', '0');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id_agenda`);

--
-- Indices de la tabla `bitacoras`
--
ALTER TABLE `bitacoras`
  ADD PRIMARY KEY (`id_bitacora`);

--
-- Indices de la tabla `cactividades`
--
ALTER TABLE `cactividades`
  ADD PRIMARY KEY (`id_actividad`);

--
-- Indices de la tabla `clab_notas`
--
ALTER TABLE `clab_notas`
  ADD PRIMARY KEY (`id_clab_notas`);

--
-- Indices de la tabla `comentarios_cursos`
--
ALTER TABLE `comentarios_cursos`
  ADD PRIMARY KEY (`id_comentario_c`);

--
-- Indices de la tabla `desc_html_formmms`
--
ALTER TABLE `desc_html_formmms`
  ADD PRIMARY KEY (`id_desc_html`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id_etiqueta`);

--
-- Indices de la tabla `etpro_curso`
--
ALTER TABLE `etpro_curso`
  ADD PRIMARY KEY (`id_etpro_curso`);

--
-- Indices de la tabla `tdea_info_programa`
--
ALTER TABLE `tdea_info_programa`
  ADD PRIMARY KEY (`id_infoP`);

--
-- Indices de la tabla `tdea_mas_html`
--
ALTER TABLE `tdea_mas_html`
  ADD PRIMARY KEY (`id_mas_html`);

--
-- Indices de la tabla `tdea_noticias`
--
ALTER TABLE `tdea_noticias`
  ADD PRIMARY KEY (`id_noticia`);

--
-- Indices de la tabla `tdea_podcast`
--
ALTER TABLE `tdea_podcast`
  ADD PRIMARY KEY (`id_pod`);

--
-- Indices de la tabla `tdea_programa`
--
ALTER TABLE `tdea_programa`
  ADD PRIMARY KEY (`id_programa`);

--
-- Indices de la tabla `tdea_repr`
--
ALTER TABLE `tdea_repr`
  ADD PRIMARY KEY (`id_repr`);

--
-- Indices de la tabla `tdea_usuarios`
--
ALTER TABLE `tdea_usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `tdea_vid_prin`
--
ALTER TABLE `tdea_vid_prin`
  ADD PRIMARY KEY (`id_vidprin`);

--
-- Indices de la tabla `form_mediaciones`
--
ALTER TABLE `form_mediaciones`
  ADD PRIMARY KEY (`id_form_med`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id_institucion`);

--
-- Indices de la tabla `lista_tareas`
--
ALTER TABLE `lista_tareas`
  ADD PRIMARY KEY (`id_tarea`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`);

--
-- Indices de la tabla `superadmin`
--
ALTER TABLE `superadmin`
  ADD PRIMARY KEY (`id_superadmin`);

--
-- Indices de la tabla `tipo_material`
--
ALTER TABLE `tipo_material`
  ADD PRIMARY KEY (`id_tipo_material`);

--
-- Indices de la tabla `tdea_activity_task`
--
ALTER TABLE `tdea_activity_task`
  ADD PRIMARY KEY (`id_activity`);

--
-- Indices de la tabla `tdea_ajustecurso_cpm`
--
ALTER TABLE `tdea_ajustecurso_cpm`
  ADD PRIMARY KEY (`id_ajustecurso_cpm`);

--
-- Indices de la tabla `tdea_alerta_admin`
--
ALTER TABLE `tdea_alerta_admin`
  ADD PRIMARY KEY (`id_alerta_admin`);

--
-- Indices de la tabla `tdea_alerta_usuarios`
--
ALTER TABLE `tdea_alerta_usuarios`
  ADD PRIMARY KEY (`id_alerta_usuario`);

--
-- Indices de la tabla `tdea_alerta_visitantes`
--
ALTER TABLE `tdea_alerta_visitantes`
  ADD PRIMARY KEY (`id_alerta_visitante`);

--
-- Indices de la tabla `tdea_bitacoras_usuario`
--
ALTER TABLE `tdea_bitacoras_usuario`
  ADD PRIMARY KEY (`id_bitacora`);

--
-- Indices de la tabla `tdea_calificaciones_cpm`
--
ALTER TABLE `tdea_calificaciones_cpm`
  ADD PRIMARY KEY (`id_calif_cpm`);

--
-- Indices de la tabla `tdea_clab_cpm`
--
ALTER TABLE `tdea_clab_cpm`
  ADD PRIMARY KEY (`id_clab_cpm`);

--
-- Indices de la tabla `tdea_comentarios_cursos`
--
ALTER TABLE `tdea_comentarios_cursos`
  ADD PRIMARY KEY (`id_comentario_curso`);

--
-- Indices de la tabla `tdea_cursos`
--
ALTER TABLE `tdea_cursos`
  ADD PRIMARY KEY (`id_tdea_curso`);

--
-- Indices de la tabla `tdea_desarrollo`
--
ALTER TABLE `tdea_desarrollo`
  ADD PRIMARY KEY (`id_desarrollo`);

--
-- Indices de la tabla `tdea_emisora`
--
ALTER TABLE `tdea_emisora`
  ADD PRIMARY KEY (`id_emisora`);

--
-- Indices de la tabla `tdea_facultades`
--
ALTER TABLE `tdea_facultades`
  ADD PRIMARY KEY (`id_tdea_facultad`);

--
-- Indices de la tabla `tdea_info_ajustecurso_cpm`
--
ALTER TABLE `tdea_info_ajustecurso_cpm`
  ADD PRIMARY KEY (`id_info_ajuste`);

--
-- Indices de la tabla `tdea_log_cpm`
--
ALTER TABLE `tdea_log_cpm`
  ADD PRIMARY KEY (`id_log_cpm`);

--
-- Indices de la tabla `tdea_mediaciones_curso`
--
ALTER TABLE `tdea_mediaciones_curso`
  ADD PRIMARY KEY (`id_mediacion_curso`);

--
-- Indices de la tabla `tdea_monitores`
--
ALTER TABLE `tdea_monitores`
  ADD PRIMARY KEY (`id_monitor`);

--
-- Indices de la tabla `tdea_cpm`
--
ALTER TABLE `tdea_cpm`
  ADD PRIMARY KEY (`id_cpm`);

--
-- Indices de la tabla `tdea_multimedia`
--
ALTER TABLE `tdea_multimedia`
  ADD PRIMARY KEY (`id_multimedia`);

--
-- Indices de la tabla `tdea_notepad`
--
ALTER TABLE `tdea_notepad`
  ADD PRIMARY KEY (`id_note`);

--
-- Indices de la tabla `tdea_programas`
--
ALTER TABLE `tdea_programas`
  ADD PRIMARY KEY (`id_tdea_programa`);

--
-- Indices de la tabla `tdea_recursos_interactivos`
--
ALTER TABLE `tdea_recursos_interactivos`
  ADD PRIMARY KEY (`id_recurso`);

--
-- Indices de la tabla `tdea_spoof_user`
--
ALTER TABLE `tdea_spoof_user`
  ADD PRIMARY KEY (`id_spoof`);

--
-- Indices de la tabla `tdea_transmisiones`
--
ALTER TABLE `tdea_transmisiones`
  ADD PRIMARY KEY (`id_transmision`);

--
-- Indices de la tabla `tdea_usuarios`
--
ALTER TABLE `tdea_usuarios`
  ADD PRIMARY KEY (`id_tdea_usuario`);

--
-- Indices de la tabla `tdea_validaciones_curso`
--
ALTER TABLE `tdea_validaciones_curso`
  ADD PRIMARY KEY (`id_validacion_curso`);

--
-- Indices de la tabla `tdea_visitantes`
--
ALTER TABLE `tdea_visitantes`
  ADD PRIMARY KEY (`id_tdea_visitante`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id_agenda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacoras`
--
ALTER TABLE `bitacoras`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cactividades`
--
ALTER TABLE `cactividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clab_notas`
--
ALTER TABLE `clab_notas`
  MODIFY `id_clab_notas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios_cursos`
--
ALTER TABLE `comentarios_cursos`
  MODIFY `id_comentario_c` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `desc_html_formmms`
--
ALTER TABLE `desc_html_formmms`
  MODIFY `id_desc_html` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id_etiqueta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etpro_curso`
--
ALTER TABLE `etpro_curso`
  MODIFY `id_etpro_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_info_programa`
--
ALTER TABLE `tdea_info_programa`
  MODIFY `id_infoP` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_mas_html`
--
ALTER TABLE `tdea_mas_html`
  MODIFY `id_mas_html` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_noticias`
--
ALTER TABLE `tdea_noticias`
  MODIFY `id_noticia` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_podcast`
--
ALTER TABLE `tdea_podcast`
  MODIFY `id_pod` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_programa`
--
ALTER TABLE `tdea_programa`
  MODIFY `id_programa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_repr`
--
ALTER TABLE `tdea_repr`
  MODIFY `id_repr` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_usuarios`
--
ALTER TABLE `tdea_usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_vid_prin`
--
ALTER TABLE `tdea_vid_prin`
  MODIFY `id_vidprin` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `form_mediaciones`
--
ALTER TABLE `form_mediaciones`
  MODIFY `id_form_med` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lista_tareas`
--
ALTER TABLE `lista_tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `id_superadmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tipo_material`
--
ALTER TABLE `tipo_material`
  MODIFY `id_tipo_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_activity_task`
--
ALTER TABLE `tdea_activity_task`
  MODIFY `id_activity` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_ajustecurso_cpm`
--
ALTER TABLE `tdea_ajustecurso_cpm`
  MODIFY `id_ajustecurso_cpm` int(14) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_alerta_admin`
--
ALTER TABLE `tdea_alerta_admin`
  MODIFY `id_alerta_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_alerta_usuarios`
--
ALTER TABLE `tdea_alerta_usuarios`
  MODIFY `id_alerta_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_alerta_visitantes`
--
ALTER TABLE `tdea_alerta_visitantes`
  MODIFY `id_alerta_visitante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_bitacoras_usuario`
--
ALTER TABLE `tdea_bitacoras_usuario`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_calificaciones_cpm`
--
ALTER TABLE `tdea_calificaciones_cpm`
  MODIFY `id_calif_cpm` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_clab_cpm`
--
ALTER TABLE `tdea_clab_cpm`
  MODIFY `id_clab_cpm` int(22) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_comentarios_cursos`
--
ALTER TABLE `tdea_comentarios_cursos`
  MODIFY `id_comentario_curso` int(14) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_cursos`
--
ALTER TABLE `tdea_cursos`
  MODIFY `id_tdea_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_desarrollo`
--
ALTER TABLE `tdea_desarrollo`
  MODIFY `id_desarrollo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_emisora`
--
ALTER TABLE `tdea_emisora`
  MODIFY `id_emisora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_facultades`
--
ALTER TABLE `tdea_facultades`
  MODIFY `id_tdea_facultad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_info_ajustecurso_cpm`
--
ALTER TABLE `tdea_info_ajustecurso_cpm`
  MODIFY `id_info_ajuste` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_log_cpm`
--
ALTER TABLE `tdea_log_cpm`
  MODIFY `id_log_cpm` int(14) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_mediaciones_curso`
--
ALTER TABLE `tdea_mediaciones_curso`
  MODIFY `id_mediacion_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_monitores`
--
ALTER TABLE `tdea_monitores`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_cpm`
--
ALTER TABLE `tdea_cpm`
  MODIFY `id_cpm` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_multimedia`
--
ALTER TABLE `tdea_multimedia`
  MODIFY `id_multimedia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_notepad`
--
ALTER TABLE `tdea_notepad`
  MODIFY `id_note` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_programas`
--
ALTER TABLE `tdea_programas`
  MODIFY `id_tdea_programa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_recursos_interactivos`
--
ALTER TABLE `tdea_recursos_interactivos`
  MODIFY `id_recurso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_spoof_user`
--
ALTER TABLE `tdea_spoof_user`
  MODIFY `id_spoof` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_transmisiones`
--
ALTER TABLE `tdea_transmisiones`
  MODIFY `id_transmision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_usuarios`
--
ALTER TABLE `tdea_usuarios`
  MODIFY `id_tdea_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tdea_validaciones_curso`
--
ALTER TABLE `tdea_validaciones_curso`
  MODIFY `id_validacion_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tdea_visitantes`
--
ALTER TABLE `tdea_visitantes`
  MODIFY `id_tdea_visitante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;