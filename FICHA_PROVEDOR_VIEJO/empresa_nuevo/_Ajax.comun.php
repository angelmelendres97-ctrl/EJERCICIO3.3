<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
require_once (path(DIR_INCLUDE).'Clases/IntegracionesComerciales.class.php');
/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding('ISO-8859-1');
/***************************************************/
//	FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//	Aqui registrar todas las funciones publicas del servidor ajax
//	Ejemplo,
//	$xajax->registerFunction("Nombre de la Funcion");
/***************************************************/

$xajax->registerFunction("genera_formulario");
$xajax->registerFunction("seleccionarTran");
$xajax->registerFunction("guardar_tran");
$xajax->registerFunction("update_tran_frame");
$xajax->registerFunction("agregarEntidad");
$xajax->registerFunction("bodegas");

$xajax->registerFunction("consultar_infoxml");
$xajax->registerFunction("ingresar_detxml");
$xajax->registerFunction("guardar_detxml");

$xajax->registerFunction("consultar_infopdf");
$xajax->registerFunction("ingresar_detpdf");
$xajax->registerFunction("guardar_detpdf");


$xajax->registerFunction("sincronizar_base");
$xajax->registerFunction("validar_firma");

// sftp
$xajax->registerFunction("test_conexion_sftp_pichincha");

$xajax->registerFunction("cargar_prov");
$xajax->registerFunction("cargar_cant");
$xajax->registerFunction("cargar_ciud");
$xajax->registerFunction("cargar_parr");

$xajax->registerFunction("acciones_integracion");

/***************************************************/
?>