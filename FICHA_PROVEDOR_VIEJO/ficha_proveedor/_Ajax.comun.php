<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
require_once (path(DIR_INCLUDE).'Clases/ValidadorCedulaRucEcuador2024.class.php');
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

$xajax->registerFunction("genera_formulario_cliente");
//Funcion enviar correo electronico
$xajax->registerFunction("enviar_mail");
//obtener grid de los ajuntos
$xajax->registerFunction("obtenerAdjuntosProveedorHTML");
//Funcion enviar email al dar click en el boton procesar
$xajax->registerFunction("enviaEmail");
//-------------------------------------------------
//INICIO FUNCIONES UAFE
//-------------------------------------------------
$xajax->registerFunction("obtenerAdjuntosUafeProveedorHTML");
$xajax->registerFunction("consultarAdjuntosUafe");
$xajax->registerFunction("eliminarArchivoUAFE");
$xajax->registerFunction("cambiarEstadoUafe");
$xajax->registerFunction("guardarAdjuntosUAFE");
$xajax->registerFunction("validarEstadoUAFEProveedor");
$xajax->registerFunction("notificarDocumentosUAFE");
$xajax->registerFunction("obtenerConteoProveedoresUafeVencida");
$xajax->registerFunction("recalcularEstadosUafeProveedores");


//-------------------------------------------------
//FIN FUNCIONES UAFE
//-------------------------------------------------


$xajax->registerFunction("consultarReporteCliente");
$xajax->registerFunction("cargar_ciudad");
$xajax->registerFunction("cargar_canton");
$xajax->registerFunction("listaCcli");
$xajax->registerFunction("listaCcliNombre");
$xajax->registerFunction("guardarCcli");
$xajax->registerFunction("listaProdServCliente");
$xajax->registerFunction("guardarProdServ");
$xajax->registerFunction("modificarProdServ");
$xajax->registerFunction("eliminarProdServ");
$xajax->registerFunction("cargarlistaProdServ");
$xajax->registerFunction("listaDsctoLinpCliente");
$xajax->registerFunction("guardarDsctoLinpCliente");
$xajax->registerFunction("modificarDsctoLinpCliente");
$xajax->registerFunction("eliminarDsctoLinpCliente");
$xajax->registerFunction("guardar_cliente");
$xajax->registerFunction("consultaExistenciaIden");
$xajax->registerFunction("cargar_lista_zona");
$xajax->registerFunction("update_cliente_frame");
$xajax->registerFunction("completa_ceros");
$xajax->registerFunction("seleccionaItem");
$xajax->registerFunction("genera_formulario_portafolio");
$xajax->registerFunction("editarCoa");
$xajax->registerFunction("editarCash");
$xajax->registerFunction("guardarPlantilla");
$xajax->registerFunction("reportePlantillas");
$xajax->registerFunction("cuentaAplicada");
$xajax->registerFunction("agrega_modifica_grid");
$xajax->registerFunction("mostrar_grid");
$xajax->registerFunction("elimina_detalle");
$xajax->registerFunction("detalleCentroCostos");
$xajax->registerFunction("validaTipoProve");
$xajax->registerFunction("agregarEntidad");
$xajax->registerFunction("reporteTelefonoCliente");
$xajax->registerFunction("updateEntidad");
$xajax->registerFunction("reporteEmailCliente");
$xajax->registerFunction("reporteDireCliente");
$xajax->registerFunction("eliminarEntidad");
$xajax->registerFunction("editarDireccion");

$xajax->registerFunction("FooterMap");

$xajax->registerFunction("agrega_modifica_gridAdj");
$xajax->registerFunction("guardarAdjuntos");
$xajax->registerFunction("consultarAdjuntos");
$xajax->registerFunction("elimina_detalleAdj");
$xajax->registerFunction("ingresar_proveedor_compras");
$xajax->registerFunction("eliminarCoa");
$xajax->registerFunction("consultar_cash");
$xajax->registerFunction("actualiza_cash");
$xajax->registerFunction("autocompletar_infomacion_cliente");
$xajax->registerFunction("eliminar_adj");
$xajax->registerFunction("verifica_tipo_mapa");
$xajax->registerFunction("guardar_ubicacion_clpv");

?>
