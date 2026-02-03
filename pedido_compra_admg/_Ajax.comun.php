<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
require_once (path(DIR_INCLUDE).'Clases/GeneraPedidoCompra.class.php');
require_once (path(DIR_INCLUDE).'html2pdf_v4.03/html2pdf.class.php');
include_once(path(DIR_INCLUDE).'Clases/NotificacionesCompras.class.php');


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
$xajax->registerFunction("genera_formulario_pedido");

$xajax->registerFunction("agrega_modifica_grid");
$xajax->registerFunction("agrega_modifica_grid_update");
$xajax->registerFunction("actualiza_grid");
$xajax->registerFunction("mostrar_grid");
$xajax->registerFunction("cancelar_pedido");
$xajax->registerFunction("elimina_detalle");
$xajax->registerFunction("total_grid");
$xajax->registerFunction("cargar_grid");
$xajax->registerFunction("guarda_pedido");
$xajax->registerFunction("cargar_productos");
$xajax->registerFunction("genera_pdf_doc");
$xajax->registerFunction("buscar_productos");
$xajax->registerFunction('get_producto_no_registrado');
$xajax->registerFunction("actualizar_presupuesto");
$xajax->registerFunction("modal_cargar_archivo");
$xajax->registerFunction("cargar_ord_compra_respaldo");
$xajax->registerFunction("cargar_ord_compra");
$xajax->registerFunction("lista_pedidos");
$xajax->registerFunction("navegar_pedido");
$xajax->registerFunction("buscar_pedido_por_numero");
$xajax->registerFunction("carga_pedido");
$xajax->registerFunction("detalle_pedido");
$xajax->registerFunction("actualiza_pedido");
$xajax->registerFunction("lista_pedidos_anulados");
$xajax->registerFunction("carga_anulado");
$xajax->registerFunction("reporte_solicitudes");
$xajax->registerFunction("adjuntos_solicitud");
$xajax->registerFunction("form_detalle");
$xajax->registerFunction("genera_pdf_doc_reporte");
$xajax->registerFunction("form_aprobaciones");
$xajax->registerFunction("autorizar_solicitud");
$xajax->registerFunction("form_editar");
$xajax->registerFunction("form_anular");
$xajax->registerFunction("anular_solicitud");
$xajax->registerFunction("form_proforma_proveedores");
$xajax->registerFunction("form_proveedores");
$xajax->registerFunction("agregar_proveedor");
$xajax->registerFunction("genera_busqueda_cliente");
$xajax->registerFunction("generar_proforma");
$xajax->registerFunction("ingresar_precios_proforma");
$xajax->registerFunction("autorizar_precios_proforma");
$xajax->registerFunction("orden_compra_proforma");
$xajax->registerFunction("eliminar_prove");
$xajax->registerFunction("genera_pdf_cuadro");
$xajax->registerFunction("reporte_solicitudes_orden_compra");
$xajax->registerFunction("form_ordenes_proveedores");
$xajax->registerFunction("detalle_pedido_req");
$xajax->registerFunction("subtotal_prod");
$xajax->registerFunction("salvar");
$xajax->registerFunction("guardar_proforma");
$xajax->registerFunction("obtener_catalogo_aprobadores");
$xajax->registerFunction("guardar_cargo_aprobador");
$xajax->registerFunction("guardar_orden_cargos");
$xajax->registerFunction("eliminar_cargo_aprobador");
$xajax->registerFunction("restaurar_cargo_aprobador");
$xajax->registerFunction("guardar_aprobador_modal");
$xajax->registerFunction("eliminar_aprobador_modal");
$xajax->registerFunction("generar_codigo_auxiliar");
$xajax->registerFunction("imprimirPedidoActual");
$xajax->registerFunction("form_adjuntos_pedi");
$xajax->registerFunction("agregar_adjuntos_pedi");
$xajax->registerFunction("eliminar_adjunto_pedi");
$xajax->registerFunction("guardar_cumplimiento");

/***************************************************/
?>
