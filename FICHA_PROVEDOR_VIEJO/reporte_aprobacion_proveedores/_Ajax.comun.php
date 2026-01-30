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
require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleInventario.class.php');

require_once(path(DIR_INCLUDE) . 'codigo_de_barras/nuevo_barcode/class/BCGFontFile.php');
require_once(path(DIR_INCLUDE) . 'codigo_de_barras/nuevo_barcode/class/BCGColor.php');
require_once(path(DIR_INCLUDE) . 'codigo_de_barras/nuevo_barcode/class/BCGDrawing.php');
require_once(path(DIR_INCLUDE) . 'codigo_de_barras/nuevo_barcode/class/BCGcode128.barcode.php');

include_once(path(DIR_INCLUDE).'comun.lib.rd.php');

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
//	Fuciones de lista de pedido


$xajax->registerFunction("consultar");
$xajax->registerFunction("guardar");
$xajax->registerFunction("borrar");
$xajax->registerFunction("editar");
$xajax->registerFunction("actualizar");
$xajax->registerFunction("guardar_proveedores");
$xajax->registerFunction("genera_formulario_pedido");
$xajax->registerFunction("cargarListaSucursal");
$xajax->registerFunction("cargarListaBodega");
$xajax->registerFunction("abre_modal_receta");
$xajax->registerFunction("realizar_egreso");
$xajax->registerFunction("recalcular_total");
$xajax->registerFunction("ver_prod_final");
$xajax->registerFunction("realizar_ingreso");
$xajax->registerFunction("verDiarioContable");
$xajax->registerFunction("genera_pdf_doc_compras");
$xajax->registerFunction("eliminar_movimiento");
$xajax->registerFunction("imprimir_ticket_html");
$xajax->registerFunction("informe_calidad");
$xajax->registerFunction("parametros");
$xajax->registerFunction("guardar_informe");
$xajax->registerFunction("finalizado_cargar_productos");


/***************************************************/
?>