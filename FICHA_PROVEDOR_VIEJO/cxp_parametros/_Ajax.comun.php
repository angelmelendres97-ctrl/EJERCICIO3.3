<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
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

//	Funciones para crear forumlarios


$xajax->registerFunction("dibuja_adjuntos");
$xajax->registerFunction("guardar");
$xajax->registerFunction("consultar");


$xajax->registerFunction("agrega_modifica_gridAdj");
$xajax->registerFunction("elimina_detalleAdj");
$xajax->registerFunction("guardarAdjuntos");
$xajax->registerFunction("consultarAdjuntos");
$xajax->registerFunction("eliminar_adj");



$xajax->registerFunction("cod_ret_b");
$xajax->registerFunction("selectTret");




/***************************************************/
?>