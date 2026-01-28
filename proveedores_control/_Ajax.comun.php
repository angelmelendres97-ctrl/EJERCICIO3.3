<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleAsientoContable.class.php');


/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding(SISTEMA_CHARSET);
$xajax->configure('decodeUTF8Input',true);
/***************************************************/
//    FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//    Aqui registrar todas las funciones publicas del servidor ajax
//    Ejemplo,
//    $xajax->registerFunction("Nombre de la Funcion");
/***************************************************/
$xajax->registerFunction("genera_cabecera_formulario");
$xajax->registerFunction("generar");
$xajax->registerFunction("f_filtro_ejercicio");
$xajax->registerFunction("f_filtro_periodo");
$xajax->registerFunction("f_filtro_sucursal");
$xajax->registerFunction("verDiarioContable");
$xajax->registerFunction("cargarMes");
$xajax->registerFunction("cambioFiltroFecha");
$xajax->registerFunction("f_filtro_documento");
$xajax->registerFunction("f_filtro");
$xajax->registerFunction("f_filtro_ciudad");

?>