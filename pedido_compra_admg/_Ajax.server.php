<?php

require("_Ajax.comun.php"); // No modificar esta linea	
include_once './mov_inv.inc.php';
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
function agregar_adjuntos_pedi($copedi, $idempresa, $idsucursal, $archivo){
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();


    $fecha = date('Y-m-d');
    $fecha_hora = date('Y-m-d H:i:s');
    $usuario_web = $_SESSION['U_ID'];

    $archivo = substr($archivo, 12);
    //$archivo = $fecha . '_' . $archivo;

    try{

        // commit
        $oCon->QueryT('BEGIN');

        $sql = "insert into comercial.adjuntos_pedido_compra
				(adj_cod_empr,
                adj_cod_sucu,
				adj_cod_pedi,
				adj_ruta_adj ,
                adj_created_at ,
				adj_user_created)
                values(
                    $idempresa, $idsucursal, $copedi, '$archivo', '$fecha_hora',$usuario_web)";
            $oCon->QueryT($sql);

            $oCon->QueryT('COMMIT');

            $oReturn->script("alertSwal('Agregado Correctamente', 'success');");
            $oReturn->script("modal_adjuntos_pedi($copedi, $idempresa, $idsucursal);");
            $oReturn->script("jsRemoveWindowLoad();");


    } catch (Exception $e) {

        $oReturn->alert($e->getMessage());
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;

}
/*ELIMINAR ADJUNTOS OC*/
function eliminar_adjunto_pedi($id,$codpedi, $idempresa, $idsucursal){
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();


    $fecha = date('Y-m-d');
    $fecha_hora = date('Y-m-d H:i:s');
    $usuario_web = $_SESSION['U_ID'];

    $archivo = substr($archivo, 12);
    $archivo = $fecha . '_' . $archivo;

    try{

        // commit
        $oCon->QueryT('BEGIN');

        $sql = "update comercial.adjuntos_pedido_compra
				set adj_est_deleted='S'
                where adj_cod_empr = $idempresa and adj_cod_sucu = $idsucursal and id=$id";
            $oCon->QueryT($sql);

            $oCon->QueryT('COMMIT');

            $oReturn->script("alertSwal('Eliminado Correctamente', 'success');");
            $oReturn->script("jsRemoveWindowLoad();");
            $oReturn->script("modal_adjuntos_pedi($codpedi, $idempresa, $idsucursal);");


    } catch (Exception $e) {

        $oReturn->alert($e->getMessage());
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;


}
  /*MODAL REVISION ORDEN DE COMPRA*/
function form_adjuntos_pedi( $codpedi, $idempresa, $idsucursal){
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo ();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo ();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();


        //TABLA ADJUNTOS CARGADOS ADJUNTOS
    $sHtmladj = '<table id="tbadjoc" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';

    $sHtmladj .= '<thead><tr>
                            <th style="width:8%;">No.</th>
                            <th>Adjunto</th>
                            <th style="width:28%;">Acciones</th>
                        </tr>
                </thead><tbody>';

    $sql_adj="SELECT id, adj_ruta_adj from comercial.adjuntos_pedido_compra where adj_cod_empr=$idempresa and adj_cod_sucu = $idsucursal and adj_cod_pedi=$codpedi and adj_est_deleted='N'";
    
    $a=1;
        if ($oIfx->Query($sql_adj)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $id_adj = $oIfx->f('id');
                    $ruta_adj = $oIfx->f('adj_ruta_adj');

                    $imagen='';
                    $ruta = '';
                     //ADJUNTOS
                     if (!empty($ruta_adj)) {

                        $ruta = "../../modulos/pedido_compra_admg/upload/$ruta_adj";
                        if (!file_exists($ruta)) {
                            $ruta="../../modulos/pedido_compra_admg/upload/$ruta_adj";
                        }


                        if (preg_match("/jpg|png|PNG|jpeg|gif/", $ruta)) {

                            $logo = '<div>
                        <img src="' . $ruta . '" class="img img-responsive">
                        </div>';

                            $imagen = '<a href="' . $ruta . '" target="_blank" >' . $logo . '</a>';
                        } else {

                            $imagen = '<a href="' . $ruta . '" target="_blank" >' . $ruta_adj . '</a>';
                        }
                    }

                    $btn_adj='<span class="btn btn-danger btn-xs" title="Eliminar" value="Eliminar" onClick="javascript:elimina_adjpedi(' . $id_adj . ', '.$codpedi.', '.$idempresa.', '.$idsucursal.');">
                                <i class="glyphicon glyphicon-remove"></i>
                                </span>';

                    $download = $ruta ? '<a href="' . $ruta . '" class="btn btn-default btn-xs" download><i class="fa fa-download"></i> Descargar</a>' : '';
                    $preview = $ruta ? '<a href="' . $ruta . '" class="btn btn-info btn-xs" target="_blank"><i class="fa fa-eye"></i> Previsualizar</a>' : '';

                    $sHtmladj .='<tr>';
                    $sHtmladj .='<td align="center">'.$a.'</td>';
                    $sHtmladj .='<td>'. $imagen .'</td>';
                    $sHtmladj .='<td class="adjuntos-table-actions" align="center">'.$preview.$download.$btn_adj.'</td>';
                    $sHtmladj .='</tr>';

                    $a++;
                }while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtmladj .='</tbody></table>';



    $campos = '<style>
                    .adjuntos-modern-header {
                        background: linear-gradient(135deg, #4b79a1, #283e51);
                        color: #fff;
                        padding: 16px 20px;
                        border-radius: 4px 4px 0 0;
                    }

                    .adjuntos-modern-card {
                        background: #f9fafc;
                        border: 1px solid #e7ecf1;
                        border-radius: 6px;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                        padding: 18px;
                        margin-bottom: 15px;
                    }

                    .adjuntos-modern-card h5 {
                        margin-top: 0;
                        color: #2c3e50;
                        font-weight: 600;
                    }

                    .adjuntos-table-actions .btn {
                        margin-right: 4px;
                        margin-bottom: 4px;
                    }
                </style>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="adjuntos-modern-card">
                            <h5><i class="fa fa-cloud-upload"></i> Subir adjuntos</h5>
                            <p class="text-muted" style="margin-bottom:10px;">Selecciona un archivo o imagen para asociarlo a la solicitud de compra.</p>
                            <div class="form-group">
                                <label class="control-label" for="archivo_guia">Archivo</label>
                                <input type="file" name="archivo_pedi" id="archivo_pedi" class="form-control" onchange="upload_image(id);">
                                <small class="help-block">Formatos permitidos: imágenes, PDF u otros documentos.</small>
                                <div class="upload-msg"></div>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-success" onclick="agregar_adjuntos_pedi('.$codpedi.', '.$idempresa.', '.$idsucursal.');">
                                    <i class="glyphicon glyphicon-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="adjuntos-modern-card">
                            <h5><i class="fa fa-folder-open"></i> Archivos adjuntos</h5>';

    $sHtmladj .= '</div></div>';
    $modal  ='
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="adjuntos-modern-header">
                            <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                            <h4 class="modal-title" style="margin:0;">Adjuntos – Pedido de Compra Nro. '.$codpedi.'</h4>
                            '.$botones.'
                        </div>
                        <div class="modal-body" style="background:#f4f6f9;">
                        <div class="table-responsive">';
    $modal .= $campos.$sHtmladj;
    $modal .='           </div>       </div>
                        <div class="modal-footer" style="background:#f9fafc;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';


        $oReturn->assign("ModalAdjPedi", "innerHTML", $modal);
        $oReturn->script("init('tbadjoc')");


    return $oReturn;
}
/**DETALLE DEL PEDIDO DE LA SOLICITUD DE MATERIALES*/
function detalle_pedido_req($id_req = '', $fil_dreq = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    unset($_SESSION['aDataGird']);

    $idempresa              =  $_SESSION['U_EMPRESA'];

    /* estoy aqui */

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Archivo', 'Codigo Requisicion', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');
    $oReturn = new xajaxResponse();

    $sql = "SELECT dreq_cod_dreq, dreq_cod_empr,    dreq_cod_requ,  dreq_cod_prod,
                      dreq_cod_bode,    dreq_cod_sucu,  dreq_can_dreq,
                      dreq_cod_unid, dreq_cod_lote , dreq_lote_fcad,
                      dreq_cant_acep, dreq_det_dreq
                      FROM saedreq where dreq_cod_empr=$idempresa and dreq_cod_requ=$id_req
                      and dreq_cod_dreq $fil_dreq";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $cont = 0;
            do {
                $archivo_real = '';
                $cantidad   = $oIfx->f('dreq_cant_acep');
                $costo      = 0;
                $idbodega   = $oIfx->f('dreq_cod_bode');
                $idproducto = $oIfx->f('dreq_cod_prod');
                $idunidad   = $oIfx->f('dreq_cod_unid');
                $detalle   = $oIfx->f('dreq_det_dreq');;
                $adjuntos   = '';
                $centro_costo   = '';

                $codigo_dreq = $oIfx->f('dreq_cod_dreq');

                if (!empty($adjuntos)) $archivo_real = '../' . $adjuntos;


                // cantidad
                $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                // centro dï¿½ costo
                $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $centro_costo, 100, 100, true);
                $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');

                // detalle
                $fu->AgregarCampoTexto($cont . '_det', 'Detalle', false, $detalle, 100, 100, true);

                // busqueda
                $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                            title = "Presione aqui para Buscar Centro Costo"
                                            style="cursor: hand !important; cursor: pointer !important;"
                                            onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                            align="bottom" />';


                $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[3]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;
                $aDataGrid[$cont][$aLabelGrid[6]] = $costo;
                $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo');  //$costo;
                $aDataGrid[$cont][$aLabelGrid[9]] = round($cantidad * $costo, 2);
                $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                                    onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                                    onMouseOut="javascript:nd(); return true;"
                                                                                                    title = "Presione aqui para Eliminar"
                                                                                                    style="cursor: hand !important; cursor: pointer !important;"
                                                                                                    onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                                    alt="Eliminar"
                                                                                                    align="bottom" />';
                $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;  //$costo;
                $aDataGrid[$cont][$aLabelGrid[12]] = normalizar_detalle_con_saltos($detalle);
                $aDataGrid[$cont][$aLabelGrid[13]] = $archivo_real;
                $aDataGrid[$cont][$aLabelGrid[14]] = $codigo_dreq;
                $aDataGrid[$cont]['Producto Auxiliar'] = 'No';
                $aDataGrid[$cont]['Codigo Auxiliar'] = '';
                $aDataGrid[$cont]['Descripcion Auxiliar'] = '';



                $cont++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $_SESSION['aDataGird'] = $aDataGrid;
    $sHtml = mostrar_grid($idempresa);
    $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
    $oReturn->script('totales_comp()');
    $oReturn->script('limpiar_prod()');
    $oReturn->script('cerrar_ventana();');
    $oReturn->script('refrescarBloqueoCampos();');

    return $oReturn;
}
//CALCULO DE SUBTOTALES INGRESO PRECIOS DE PROFORMA
function subtotal_prod($aForm = '', $pedi = 0, $clpv = 0, $sucu)
{
    global $DSN, $DSN_Ifx;
    session_start();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $sql = "select d.id_inv_dprof from comercial.inv_proforma_det d , comercial.inv_proforma i where d.id_inv_prof=i.id_inv_prof
	and i.inv_cod_pedi=$pedi and i.invp_cod_sucu=$sucu and d.invpd_cod_clpv=$clpv";

    $stotal = 0;
    $total = 0;

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $cod = $oCon->f('id_inv_dprof');
                $precio = $aForm[$cod . '_pt'];
                $stotal += $precio;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    //descuento
    $des = $aForm[$clpv . '_des'];
    if (empty($des)) {
        $des = 0;
    }
    //iva
    $iva = $aForm[$clpv . '_iv'];
    if (empty($iva)) {
        $iva = 0;
    }
    //otros cargos
    $cargos = $aForm[$clpv . '_ocar'];
    if (empty($cargos)) {
        $cargos = 0;
    }
    //total

    $total = ($stotal - $des) + $cargos + $iva;

    $eti = $clpv . '_st';
    $oReturn->assign("$eti", 'value', $stotal);

    $eti = $clpv . '_tot';
    $oReturn->assign("$eti", 'value', $total);


    return $oReturn;
}
/**FUNCION SALVAR PRECIOS DE PROFORMA */
function salvar($cod_pedi, $idempresa, $idsucursal, $proforma, $codapro, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    $oReturn = new xajaxResponse();

    $array            = $_SESSION['U_INV_PROFORMA_AUTO'];
    $user_web       = $_SESSION['U_ID'];
    $user_ifx       = $_SESSION['U_USER_INFORMIX'];

    $i = 1;
    $j = 1;

    $p = 1;

    $sql = "select empr_iva_empr from saeempr where empr_cod_empr=$idempresa";
    $empr_iva_empr = round(consulta_string($sql, 'empr_iva_empr', $oIfxA, 0));


    $sql = "SELECT i.id_inv_prof, i.invp_cod_sucu, i.invp_num_invp ,
                i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
                i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.inv_cod_pedi
                from comercial.inv_proforma i where
                i.invp_cod_empr 	= $idempresa and
                i.invp_cod_sucu 	= $idsucursal and
                i.invp_esta_invp 	= 'N' 
                and i.inv_cod_pedi=$cod_pedi ; ";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {

                $prod_cod       = $oCon->f('invp_cod_prod');
                $bode_cod       = $oCon->f('invp_cod_bode');
                $unid_cod       = $oCon->f('invp_unid_cod');
                $prbo_dis       = $oCon->f('invp_cant_stock');
                $pedido         = $oCon->f('invp_cant_real');
                $prod_nom       = $oCon->f('invp_nom_prod');
                $id_inv_prof    = $oCon->f('id_inv_prof');
                $proforma       = $oCon->f('invp_num_invp');
                $sucursal_proforma       = $oCon->f('invp_cod_sucu');

                $l = 1;
                $sqlprove = "SELECT invpd_cod_clpv, invpd_nom_clpv, invpd_ema_clpv, invpd_movil_clpv  
                               from comercial.inv_proforma i, comercial.inv_proforma_det d
                               where i.id_inv_prof=d.id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal  group by 1,2,3,4 order by 1";
                $x = 1;
                unset($array_clpv);
                if ($oConA->Query($sqlprove)) {
                    if ($oConA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                            $clpv_nom_clpv = $oConA->f('invpd_nom_clpv');
                            $correo_clpv = $oConA->f('invpd_ema_clpv');

                            $serial            = '';
                            $serial         = $l . '-' . $ppvpr_cod_clpv;

                            $sqlpro = "SELECT  d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                                                d.invpd_ema_clpv, d.invpd_costo_prod from comercial.inv_proforma_det d, comercial.inv_proforma i where
                                                                d.id_inv_prof=i.id_inv_prof and
                                                                d.id_inv_prof = $id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$ppvpr_cod_clpv 
                                                                ";
                            $ppvpr_pre_pac = consulta_string($sqlpro, 'invpd_costo_prod', $oConB, '');
                            $id_inv_dprof   = consulta_string($sqlpro, 'id_inv_dprof', $oConB, '');

                            $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $serial, $correo_clpv, $id_inv_dprof);

                            $l++;
                            $x++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }

                //DETALLE DE LOS PRODUCTOS
                $sprof = "SELECT invp_nom_prod,invp_cant_pedi, invp_unid_cod,invp_cod_prod,id_inv_prof from comercial.inv_proforma 
                                    where invp_num_invp = '$proforma' and inv_cod_pedi=$cod_pedi and invp_cod_sucu=$idsucursal order by id_inv_prof";
                unset($array_prod);
                $m = 1;
                if ($oConA->Query($sprof)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConB->Query($sqlprove)) {
                                if ($oConB->NumFilas() > 0) {
                                    do {

                                        $ppvpr_cod_clpv = $oConB->f('invpd_cod_clpv');

                                        $serialprod            = '';
                                        $serialprod         = $j . '-' . $ppvpr_cod_clpv;

                                        $array_prod[] = array($serialprod);
                                    } while ($oConB->SiguienteRegistro());

                                    $m++;
                                }
                            }

                            $j++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }

                ///SUB TOTAL
                $arraypro = array("Subtotal", "Descuento %", "IVA $empr_iva_empr%", "Otros Cargos", "Total");
                unset($array_pro);
                foreach ($arraypro as $val) {

                    if ($oConA->Query($sqlprove)) {
                        if ($oConA->NumFilas() > 0) {
                            do {

                                $clpvcod = $oConA->f('invpd_cod_clpv');
                                $serialtot = '';
                                $serialtot = $clpvcod;

                                $array_pro[] = array($serialtot);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }
                    $j++;
                } //CIERRE FOREACH ARRAY PRO


                //TERMINOS Y CONDICIONES

                $arrayter = array("Oferta Completa %", "Nro Contrato", "Cotizaciones Comprobadas", "Servicios Adicionales", "Experiencia Pasada", "Forma de Pago", "Validez de la oferta", "Tiempo de entrega", "Lugar de entrega", "Vendedor", "* Adjuntos");

                unset($array_ter);
                foreach ($arrayter as $val) {

                    if ($oConA->Query($sqlprove)) {
                        if ($oConA->NumFilas() > 0) {
                            do {

                                $clpvcod = $oConA->f('invpd_cod_clpv');
                                $sqlpro = "select  d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                            d.invpd_ema_clpv, d.invpd_costo_prod from comercial.inv_proforma_det d, comercial.inv_proforma i where
                                                        d.id_inv_prof=i.id_inv_prof and
                                                        d.id_inv_prof = $id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$clpvcod 
                                                        ";


                                $serialter = '';
                                $serialter = $clpvcod;
                                $array_ter[] = array($serialter);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }

                    $j++;
                } //CIERRE FOR ARRAY TERMINOS

                $oConA->Free();

                $i++;

                $array[] = array($cod_pedi, $array_clpv, $array_ter, $array_pro, $array_prod);
            } while ($oCon->SiguienteRegistro());
        }
    }

    $oConB->Free();

    //VALIDAICON PROVEEDORES
    $contprove = 0;


    //var_dump($array_clpv);exit;


    if (count($array) > 0) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            //foreach($array as $val){

            //$array_cl=$val[1];
            if (count($array_clpv) > 0) {
                foreach ($array_clpv as $val2) {
                    $ppvpr_cod_clpv = $val2[0];
                    $clpv_nom_clpv = $val2[2];
                    $serial         = $val2[3];
                    $correo_clpv    = $val2[4];
                    $id_inv_dprof   = $val2[5];
                    $check             = $aForm[$serial];
                    if (!empty($check)) {

                        $sqld = "select d.id_inv_dprof,d.invpd_adjunto from comercial.inv_proforma_det d , comercial.inv_proforma i 
                        where d.id_inv_prof=i.id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$ppvpr_cod_clpv";
                        if ($oConA->Query($sqld)) {
                            if ($oConA->NumFilas() > 0) {
                                do {
                                    $cod = $oConA->f('id_inv_dprof');

                                    //cantidad
                                    $cantd = $aForm[$cod . '_cantd'];
                                    if (empty($cantd)) {
                                        $cantd = 0;
                                    }


                                    //valor unitario
                                    $vunit = $aForm[$cod . '_vu'];
                                    if (empty($vunit)) {
                                        $vunit = 0;
                                    }
                                    //precio total
                                    $ptotal = $aForm[$cod . '_pt'];

                                    //subtotal
                                    $stotal = $aForm[$ppvpr_cod_clpv . '_st'];
                                    //iva
                                    $iva = $aForm[$ppvpr_cod_clpv . '_iv'];
                                    if (empty($iva)) {
                                        $iva = 0;
                                    }
                                    //descuento
                                    $des = $aForm[$ppvpr_cod_clpv . '_des'];
                                    if (empty($des)) {
                                        $des = 0;
                                    }
                                    //otros cargos
                                    $cargos = $aForm[$ppvpr_cod_clpv . '_ocar'];
                                    if (empty($cargos)) {
                                        $cargos = 0;
                                    }
                                    //total
                                    $total = $aForm[$ppvpr_cod_clpv . '_tot'];

                                    //oferta 
                                    $oferta = $aForm[$ppvpr_cod_clpv . '_ofcomp'];
                                    if (empty($oferta)) {
                                        $oferta = 0;
                                    }


                                    //cotizaciones comprobadas 
                                    $coti = strtoupper($aForm[$ppvpr_cod_clpv . '_ctz']);



                                    //servicios adicionales
                                    $sad = strtoupper($aForm[$ppvpr_cod_clpv . '_sad']);



                                    //experiencia pasada 
                                    $exp = strtoupper($aForm[$ppvpr_cod_clpv . '_exp']);

                                    //forma de pago 
                                    $fpago = strtoupper($aForm[$ppvpr_cod_clpv . '_fpag']);

                                    //validez de la oferta 
                                    $vorf = strtoupper($aForm[$ppvpr_cod_clpv . '_vorf']);

                                    //tiempo de entrega
                                    $plazo = strtoupper($aForm[$ppvpr_cod_clpv . '_pz']);

                                    //lugar de entrega
                                    $lugar = strtoupper($aForm[$ppvpr_cod_clpv . '_lug']);

                                    //vendedor
                                    $vendedor = strtoupper($aForm[$ppvpr_cod_clpv . '_vend']);

                                    //contrato
                                    $contrato = $aForm[$ppvpr_cod_clpv . '_contr'];
                                    if (empty($contrato)) {
                                        $contrato = 'NULL';
                                    }



                                    //adjuntos old
                                    $adjunto = $oConA->f('invpd_adjunto');


                                    //adjuntos new
                                    $adj = $aForm[$ppvpr_cod_clpv . '_adj'];



                                    if (empty($adjunto)) {

                                        //VALIDACION ADJUNTOS NUEVOS

                                        if (!empty($adj)) {
                                            $adj = substr($adj, 3);

                                            $arrayc = explode(":", $adj);
                                            if (count($arrayc) == 1) {
                                                $ran = $arrayc[0];
                                            } else {

                                                $ran = $adj;
                                            }


                                            $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod =  $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov = $cantd,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_adjunto='$ran',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov= $contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                            $oCon->QueryT($sqlpre);
                                        } else {

                                            $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov = $cantd,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov= $contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                            $oCon->QueryT($sqlpre);
                                        }
                                    }
                                    //SI EXISTEN ADJUNTOS CARGADOS
                                    else {


                                        if (!empty($adj)) {

                                            $adj = substr($adj, 3);

                                            $arrayc = explode(":", $adj);
                                            if (count($arrayc) == 1) {
                                                $ran = $arrayc[0];
                                            } else {

                                                $ran = $adj;
                                            }

                                            $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov = $cantd,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_adjunto='$ran',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov= $contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                            $oCon->QueryT($sqlpre);
                                        } else {

                                            $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov = $cantd,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov= $contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                            $oCon->QueryT($sqlpre);
                                        }
                                    }
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $contprove++;
                    } //CIERE IF CHECK

                }
            }


            //}


            if ($contprove > 0) {
                $oCon->QueryT('COMMIT');
                $oReturn->alert('Salvado Correctamente Precio Proforma');
                $oReturn->script("jsRemoveWindowLoad();");
                $oReturn->script('modal_proformas( \'' . $cod_pedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ');');
            } else {
                $oReturn->alert('Seleccione los proveedores');
                $oReturn->script("jsRemoveWindowLoad();");
            }
        } catch (Exception $e) {
            // rollback
            $oReturn->script("jsRemoveWindowLoad();");
            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert('Por favor realice una Busqueda...');
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;
}
/**FUNCION GUARDAR PRECIOS DE PROFORMA */
function guardar_proforma($cod_pedi, $idempresa, $idsucursal, $proforma, $codapro, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    $oReturn = new xajaxResponse();

    $array            = $_SESSION['U_INV_PROFORMA_AUTO'];
    $user_web       = $_SESSION['U_ID'];
    $user_ifx       = $_SESSION['U_USER_INFORMIX'];
    $fecha_mov = date('Y-m-d');

    $i = 1;
    $j = 1;
    $l = 1;
    $p = 1;

    $sql = "select empr_iva_empr from saeempr where empr_cod_empr=$idempresa";
    $empr_iva_empr = round(consulta_string($sql, 'empr_iva_empr', $oIfxA, 0));


    $sql = "SELECT i.id_inv_prof, i.invp_cod_sucu, i.invp_num_invp ,
    i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
    i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.inv_cod_pedi
    from comercial.inv_proforma i where
    i.invp_cod_empr 	= $idempresa and
    i.invp_cod_sucu 	= $idsucursal and
    i.invp_esta_invp 	= 'N' 
    and i.inv_cod_pedi=$cod_pedi ; ";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {

                $prod_cod       = $oCon->f('invp_cod_prod');
                $bode_cod       = $oCon->f('invp_cod_bode');
                $unid_cod       = $oCon->f('invp_unid_cod');
                $prbo_dis       = $oCon->f('invp_cant_stock');
                $pedido         = $oCon->f('invp_cant_real');
                $prod_nom       = $oCon->f('invp_nom_prod');
                $id_inv_prof    = $oCon->f('id_inv_prof');
                $proforma       = $oCon->f('invp_num_invp');
                $sucursal_proforma       = $oCon->f('invp_cod_sucu');

                $l = 1;
                $sqlprove = "SELECT invpd_cod_clpv, invpd_nom_clpv, invpd_ema_clpv, invpd_movil_clpv  
                   from comercial.inv_proforma i, comercial.inv_proforma_det d
                   where i.id_inv_prof=d.id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal  group by 1,2,3,4 order by 1";
                $x = 1;
                unset($array_clpv);
                if ($oConA->Query($sqlprove)) {
                    if ($oConA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                            $clpv_nom_clpv = $oConA->f('invpd_nom_clpv');
                            $correo_clpv = $oConA->f('invpd_ema_clpv');

                            $serial            = '';
                            $serial         = $l . '-' . $ppvpr_cod_clpv;

                            $sqlpro = "SELECT  d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                                    d.invpd_ema_clpv, d.invpd_costo_prod from comercial.inv_proforma_det d, comercial.inv_proforma i where
                                                    d.id_inv_prof=i.id_inv_prof and
                                                    d.id_inv_prof = $id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$ppvpr_cod_clpv 
                                                    ";
                            $ppvpr_pre_pac = consulta_string($sqlpro, 'invpd_costo_prod', $oConB, '');
                            $id_inv_dprof   = consulta_string($sqlpro, 'id_inv_dprof', $oConB, '');

                            $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $serial, $correo_clpv, $id_inv_dprof);

                            $l++;
                            $x++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }

                //DETALLE DE LOS PRODUCTOS
                $sprof = "SELECT invp_nom_prod,invp_cant_pedi, invp_unid_cod,invp_cod_prod,id_inv_prof from comercial.inv_proforma 
                        where invp_num_invp = '$proforma' and inv_cod_pedi=$cod_pedi and invp_cod_sucu=$idsucursal order by id_inv_prof";
                unset($array_prod);
                $m = 1;
                if ($oConA->Query($sprof)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConB->Query($sqlprove)) {
                                if ($oConB->NumFilas() > 0) {
                                    do {

                                        $ppvpr_cod_clpv = $oConB->f('invpd_cod_clpv');

                                        $serialprod            = '';
                                        $serialprod         = $j . '-' . $ppvpr_cod_clpv;

                                        $array_prod[] = array($serialprod);
                                    } while ($oConB->SiguienteRegistro());

                                    $m++;
                                }
                            }

                            $j++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }

                ///SUB TOTAL
                $arraypro = array("Subtotal", "Descuento %", "IVA $empr_iva_empr%", "Otros Cargos", "Total");
                unset($array_pro);
                foreach ($arraypro as $val) {

                    if ($oConA->Query($sqlprove)) {
                        if ($oConA->NumFilas() > 0) {
                            do {

                                $clpvcod = $oConA->f('invpd_cod_clpv');
                                $serialtot = '';
                                $serialtot = $clpvcod;

                                $array_pro[] = array($serialtot);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }
                    $j++;
                } //CIERRE FOREACH ARRAY PRO


                //TERMINOS Y CONDICIONES

                $arrayter = array("Oferta Completa %", "Nro Contrato", "Cotizaciones Comprobadas", "Servicios Adicionales", "Experiencia Pasada", "Forma de Pago", "Validez de la oferta", "Tiempo de entrega", "Lugar de entrega", "Vendedor", "* Adjuntos");

                unset($array_ter);
                foreach ($arrayter as $val) {

                    if ($oConA->Query($sqlprove)) {
                        if ($oConA->NumFilas() > 0) {
                            do {

                                $clpvcod = $oConA->f('invpd_cod_clpv');
                                $sqlpro = "select  d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                d.invpd_ema_clpv, d.invpd_costo_prod from comercial.inv_proforma_det d, comercial.inv_proforma i where
                                            d.id_inv_prof=i.id_inv_prof and
                                            d.id_inv_prof = $id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$clpvcod 
                                            ";


                                $serialter = '';
                                $serialter = $clpvcod;
                                $array_ter[] = array($serialter);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }

                    $j++;
                } //CIERRE FOR ARRAY TERMINOS

                $oConA->Free();

                $i++;

                $array[] = array($cod_pedi, $array_clpv, $array_ter, $array_pro, $array_prod);
            } while ($oCon->SiguienteRegistro());
        }
    }

    $oCon->Free();
    //VALIDAICON PROVEEDORES
    $contprove = 0;


    if (count($array) > 0) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            foreach ($array as $val) {

                $array_cl = $val[1];
                if (count($array_cl) > 0) {
                    foreach ($array_cl as $val2) {
                        $ppvpr_cod_clpv = $val2[0];
                        $clpv_nom_clpv = $val2[2];
                        $serial         = $val2[3];
                        $correo_clpv    = $val2[4];
                        $id_inv_dprof   = $val2[5];
                        $check             = $aForm[$serial];
                        if (!empty($check)) {

                            //ACTUALIZACION ESTADO PROFORMA

                            $sql = "update comercial.inv_proforma set invp_fmov_minv = '$fecha_mov' , 
                    invp_user_aprob 	= $user_web,  
                    invp_esta_invp   	= 'S' ,
                    invp_esta_oc   	= 'N',
                    invp_fmov_server 	= now() where
                    invp_cod_empr 		= $idempresa and
                    invp_cod_sucu  = $idsucursal and
                    inv_cod_pedi   = $cod_pedi ";
                            $oCon->QueryT($sql);

                            $sqld = "select d.id_inv_dprof,d.invpd_adjunto, i.invp_cant_real from comercial.inv_proforma_det d , comercial.inv_proforma i 
                        where d.id_inv_prof=i.id_inv_prof and i.inv_cod_pedi=$cod_pedi and i.invp_cod_sucu=$idsucursal and d.invpd_cod_clpv=$ppvpr_cod_clpv";
                            if ($oConA->Query($sqld)) {
                                if ($oConA->NumFilas() > 0) {
                                    do {
                                        $cod = $oConA->f('id_inv_dprof');


                                        //cantidad
                                        $cant_prof = $aForm[$cod . '_cantd'];
                                        if (empty($cant_prof)) {
                                            $cant_prof = 0;
                                        }


                                        //valor unitario
                                        $vunit = $aForm[$cod . '_vu'];
                                        if (empty($vunit)) {
                                            $vunit = 0;
                                        }

                                        if (round($vunit, 2) > 0 && round($cant_prof) <= 0) {
                                            $vunit = null;
                                            $error = 'Cantidad debe ser mayor a cero en: ' . $clpv_nom_clpv;
                                        }



                                        //precio total
                                        $ptotal = $aForm[$cod . '_pt'];

                                        //subtotal
                                        $stotal = $aForm[$ppvpr_cod_clpv . '_st'];
                                        //iva
                                        $iva = $aForm[$ppvpr_cod_clpv . '_iv'];
                                        if (empty($iva)) {
                                            $iva = 0;
                                        }
                                        //descuento
                                        $des = $aForm[$ppvpr_cod_clpv . '_des'];
                                        if (empty($des)) {
                                            $des = 0;
                                        }
                                        //otros cargos
                                        $cargos = $aForm[$ppvpr_cod_clpv . '_ocar'];
                                        if (empty($cargos)) {
                                            $cargos = 0;
                                        }
                                        //total
                                        $total = $aForm[$ppvpr_cod_clpv . '_tot'];

                                        //oferta 
                                        $oferta = $aForm[$ppvpr_cod_clpv . '_ofcomp'];
                                        if (empty($oferta)) {
                                            $oferta = 0;
                                        }


                                        //cotizaciones comprobadas 
                                        $coti = strtoupper($aForm[$ppvpr_cod_clpv . '_ctz']);



                                        //servicios adicionales
                                        $sad = strtoupper($aForm[$ppvpr_cod_clpv . '_sad']);



                                        //experiencia pasada 
                                        $exp = strtoupper($aForm[$ppvpr_cod_clpv . '_exp']);

                                        //forma de pago 
                                        $fpago = strtoupper($aForm[$ppvpr_cod_clpv . '_fpag']);

                                        //validez de la oferta 
                                        $vorf = strtoupper($aForm[$ppvpr_cod_clpv . '_vorf']);

                                        //tiempo de entrega
                                        $plazo = strtoupper($aForm[$ppvpr_cod_clpv . '_pz']);

                                        //lugar de entrega
                                        $lugar = strtoupper($aForm[$ppvpr_cod_clpv . '_lug']);

                                        //vendedor
                                        $vendedor = strtoupper($aForm[$ppvpr_cod_clpv . '_vend']);

                                        //contrato
                                        $contrato = $aForm[$ppvpr_cod_clpv . '_contr'];
                                        if (empty($contrato)) {
                                            $contrato = 'NULL';
                                        }


                                        //adjuntos old
                                        $adjunto = $oConA->f('invpd_adjunto');


                                        //adjuntos new
                                        $adj = $aForm[$ppvpr_cod_clpv . '_adj'];




                                        if (empty($adjunto)) {

                                            //VALIDACION ADJUNTOS NUEVOS

                                            if (!empty($adj)) {
                                                $adj = substr($adj, 3);

                                                $arrayc = explode(":", $adj);
                                                if (count($arrayc) == 1) {
                                                    $ran = $arrayc[0];
                                                } else {

                                                    $ran = $adj;
                                                }


                                                $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov= $cant_prof,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_adjunto='$ran',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov=$contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                                $oCon->QueryT($sqlpre);
                                            } else {

                                                $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov= $cant_prof,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov=$contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                                $oCon->QueryT($sqlpre);
                                            }
                                        }
                                        //SI EXISTEN ADJUNTOS CARGADOS
                                        else {


                                            if (!empty($adj)) {

                                                $adj = substr($adj, 3);

                                                $arrayc = explode(":", $adj);
                                                if (count($arrayc) == 1) {
                                                    $ran = $arrayc[0];
                                                } else {

                                                    $ran = $adj;
                                                }

                                                $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov= $cant_prof,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_adjunto='$ran',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov=$contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                                $oCon->QueryT($sqlpre);
                                            } else {

                                                $sqlpre = "update comercial.inv_proforma_det  set invpd_costo_prod = $vunit,
                                                    invpd_cun_dmov = $vunit,
                                                    invpd_cant_dmov= $cant_prof,
                                                    invp_ptotal_prof =$ptotal,
                                                    invp_iva_prof= $iva,
                                                    invp_desc_prof=$des,
                                                    invp_subt_prof=$stotal,
                                                    invp_total_prof=$total,
                                                    invp_oval_prof=$cargos,
                                                    invp_ofcom_prof=$oferta,
                                                    invp_ctzcom_prof='$coti',
                                                    invp_sadc_prof='$sad',
                                                    invp_exps_prof='$exp',
                                                    invpd_tent_prof='$plazo',
                                                    invpd_fpago_prof='$fpago',
                                                    invpd_vofer_prof='$vorf',
                                                    invpd_lug_entr='$lugar',
                                                    invpd_vend_prof='$vendedor',
                                                    invpd_ctr_prov=$contrato,
                                                    invpd_esta_invpd = 'S'
                                                    where
                                                    id_inv_dprof    = $cod";
                                                $oCon->QueryT($sqlpre);
                                            }
                                        }
                                    } while ($oConA->SiguienteRegistro());
                                }
                            }
                            $oConA->Free();

                            $contprove++;
                        } //CIERE IF CHECK

                    }
                }
            }


            if ($contprove > 0) {
                //ACTUALIZACION ESTADO ACCIONES PROFORMA
                $fecha_apro = date('Y-m-d H:i:s');

                $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud,   usuario, fecha) 
                values
                ($idempresa, $idsucursal, $codapro, '$cod_pedi',  $user_web, '$fecha_apro')";
                $oCon->QueryT($sqlapro);


                $oCon->QueryT('COMMIT');
                //$oIfx->QueryT('COMMIT WORK;'); 


                //CODIGO DE LA PROXIMA APROBACION
                $sql = "SELECT id from comercial.aprobaciones_compras 
            where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFAUT'";

                $cod_apro = consulta_string($sql, 'id', $oCon, 0);

                //MENSAJE DE CORREO Y WHATSAPP
                $mensaje = 'La proforma <b>N. ' . $proforma . '</b> ha sido ingresada<br>Requiere su revision y aprobacion';
                $text_envio = 'La proforma *N. ' . $proforma . '* ha sido ingresada\nRequiere su revision y aprobacion';

                // Instanciamos la clase NotificacionesCompras
                $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $proforma, '', '');
                // Enviar correo a los aprobadores
                $notifier->enviarCorreoAprobadores($mensaje);
                // Enviar WhatsApp a los aprobadores
                $notifier->enviarWhatsAppAprobadores($text_envio);


                $oReturn->script("alertSwal('Se Ingresaron Correctamente los precios de la Proforma: $proforma', 'success');");
                $oReturn->script("jsRemoveWindowLoad();");
                $oReturn->script('cerrarModalProfProv();');
                $oReturn->script("reporte_solicitudes()");
            } else {
                $oReturn->alert('Seleccione los proveedores');
                $oReturn->script("jsRemoveWindowLoad();");
            }
        } catch (Exception $e) {
            // rollback
            $oReturn->script("jsRemoveWindowLoad();");
            $oCon->QueryT('ROLLBACK');

            if (!empty($error)) {
                $oReturn->alert($error);
            } else {
                $oReturn->alert($e->getMessage());
            }
        }
    } else {
        $oReturn->alert('Por favor realice una Busqueda...');
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;
}
/*MODAL ORDENES DE COMPRA PROVEEDOR*/
function form_ordenes_proveedores($codpedi, $empresa, $sucursal)
{
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();


    $oReturn = new xajaxResponse();

    //VALIDACION POR PAIS
    $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];

    ///FORMATO ORDEN DE COMPRA

    $sql = "select defi_for_defi from saetran, saedefi where defi_cod_empr=tran_cod_empr 
    and defi_cod_tran=tran_cod_tran
    and tran_cod_modu=10 and tran_cod_tran like '002%' and tran_cod_empr=$empresa 
    and tran_cod_sucu=$sucursal";

    $cod_ftrn = consulta_string($sql, 'defi_for_defi', $oIfx, '');
    $ctrl_formato = 0;
    if (!empty($cod_ftrn)) {
        $sqlf = "select ftrn_ubi_web from saeftrn where ftrn_cod_ftrn=$cod_ftrn";
        $ubi_formato = consulta_string($sqlf, 'ftrn_ubi_web', $oIfx, '');

        if (!empty($ubi_formato)) {
            $ctrl_formato++;
            include_once('../../' . $ubi_formato . '');
        }
    }


    $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 90%; margin-bottom: 0px;" align="center">';
    $sHtml .= '<tr>
	<td class="info" >No.</td>
	<td class="info" align="center">Fecha</td>
	<td class="info" align="center">Orden No.</td>
	<td class="info" align="center">Proveedor</td>
	<td class="info" align="center">Imprimir</td>
	</tr>';

    $i = 1;
    $sql = "SELECT  d.invpd_cod_clpv,d.invpd_nom_clpv, i.invp_num_invp 
        from comercial.inv_proforma_det d, comercial.inv_proforma i 
        where
		i.invp_cod_empr 	= $empresa and i.invp_cod_sucu 	= $sucursal and 
        d.id_inv_prof=i.id_inv_prof and i.inv_cod_pedi='$codpedi' and d.invpd_esta_invpd = 'S' group by 1,2,3";

    if ($oConA->Query($sql)) {
        if ($oConA->NumFilas() > 0) {
            do {
                $codclpv = $oConA->f('invpd_cod_clpv');
                $nomclpv = $oConA->f('invpd_nom_clpv');
                $proforma = $oConA->f('invp_num_invp');

                $sql = "select minv_num_comp,minv_num_sec, minv_fec_ser from saeminv where  minv_cod_clpv=$codclpv
                                                 and minv_cod_empr=$empresa and minv_cod_sucu=$sucursal and minv_cm1_minv='$proforma';";
                $norden = consulta_string($sql, 'minv_num_sec', $oCon, '');
                $forden = consulta_string($sql, 'minv_fec_ser', $oCon, '');
                //$forden=date('d-m-Y',strtotime($forden));
                $ncomp = consulta_string($sql, 'minv_num_comp', $oCon, 0);



                if ($ctrl_formato != 0) {
                    formato_orden_compra($ncomp);
                    $ruta = '../../Include/orden_compra/ORDEN_COMPRA_' . $ncomp . '.pdf';
                } elseif ($ctrl_formato == 0) {

                    //PERU  
                    if ($S_PAIS_API_SRI == '51') {
                        include_once('../../Include/Formatos/comercial/orden_compra_peru.php');

                        formato_orden_compra($ncomp);
                        $ruta = '../../Include/orden_compra/ORDEN_COMPRA_' . $ncomp . '.pdf';
                    }
                    //ECUADOR 
                    else {
                        reporte_orden_compra($ncomp, '');
                        $ruta = '../../Include/orden_compra/' . $ncomp . '.pdf';
                    }
                }



                $orden = '<a href="' . $ruta . '" target="_blank"><div align="center"> <div class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-print"><span></div> </div></a>';
                if ($ncomp == 0) {
                    $orden = '<strong><font color="red">NO GENERADA</font></strong>';
                }

                $sHtml .= '<tr>
                                                            <td>' . $i . '</td>
                                                            <td align="center">' . $forden . '</td>
                                                            <td align="center">' . $norden . '</td>
                                                            <td>' . $nomclpv . '</td>
                                                            <td align="center">' . $orden . '</td>
													</tr>';

                $i++;
            } while ($oConA->SiguienteRegistro());
            $sHtml .= '</table>';
        }
    }
    $oConA->Free();


    $modal  = '<div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">ORDENES DE COMPRA - SOLICITUD NÂ°: ' . $codpedi . '</h4>
                        </div>
                        <div class="modal-body">';
    $modal .= $sHtml;
    $modal .= '          </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>  ';
    $oReturn->assign("ModalOrdenes", "innerHTML", $modal);

    return $oReturn;
}

//PDF CUADRO COMPARATIVO
function genera_pdf_cuadro($codpedi, $empresa, $sucursal)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;


    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    unset($_SESSION['pdf']);
    $oReturn = new xajaxResponse();


    $sql = "select ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
    ftrn_des_ftrn = 'CUADRO COMPARATIVO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$empresa";
    $ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfxA, '');

    //AUTRIZACION PROFORMA
    $estado_prof = 5;
    if (!empty($ubi)) {
        include_once('../../' . $ubi . '');
        $html = genera_cuadro_comparativo($codpedi, $empresa, $sucursal, 0, $estado_prof);
    } else {
        include_once('../../Include/Formatos/comercial/cuadro_comparativo_proveedores.php');
        $html = genera_cuadro_comparativo($codpedi, $empresa, $sucursal, 0, $estado_prof);
    }

    $_SESSION['pdf'] = $html;

    $oReturn->script('generar_pdf_compras()');

    return $oReturn;
}
//ELIMINAR PROVEEDORES
function eliminar_prove($idempresa = 0, $idsucursal = 0, $codclpv = 0, $codpedi = 0, $codapro = 0,  $codprod = '', $tipo)
{

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');

        $sql = "delete from comercial.clpv_pedi where clpe_cod_clpv=$codclpv and clpe_cod_pedi=$codpedi 
        and clpe_cod_empr=$idempresa and clpe_cod_sucu=$idsucursal and clpe_cod_prod='$codprod'";
        $oIfx->QueryT($sql);

        //ELIMINAR REGISTRO EN LA PROFORMA
        if ($tipo == 2) {
            $sqlinv = "select i.invp_num_invp,i.id_inv_prof from comercial.inv_proforma i where  
                                            i.invp_cod_empr 	= $idempresa and
                                            i.invp_cod_sucu     = $idsucursal and 
                                            i.inv_cod_pedi 	= '$codpedi' and
                                            i.invp_cod_prod ='$codprod' ";

            $id_inv_prof = consulta_string_func($sqlinv, 'id_inv_prof', $oIfx, '0');

            $sql = "delete from comercial.inv_proforma_det where invpd_cod_clpv=$codclpv and id_inv_prof=$id_inv_prof";
            $oIfx->QueryT($sql);
        }

        $oIfx->QueryT('COMMIT WORK;');

        $oReturn->script("alertSwal('Eliminado Correctamente...', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('modal_proformas( \'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ');');
    } catch (Exception $e) {
        // rollback
        $oReturn->script("jsRemoveWindowLoad();");
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
//BUSCADOR PROVEEDORES
function genera_busqueda_cliente($campo_like = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //VARIABLES DE SESION
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];


    // VARIABLES
    $user_vendedor =  $_SESSION['U_VENDEDOR'];

    $perfil =  $_SESSION['U_PERFIL'];

    if ($perfil == 1 || $perfil == 2) {
        $fil_vend = '';
    } elseif ($perfil != 1 || $perfil != 2) {
        if ($user_vendedor != '' && !empty($user_vendedor)) {
            $fil_vend = "and c.clpv_cod_vend='$user_vendedor'";
        } else {
            $fil_vend = '';
        }
    }


    // Ciudad
    unset($arrayCiud);
    $pais_cod = $_SESSION['U_PAIS_COD'];
    $sql = "select ciud_cod_ciud, ciud_nom_ciud from saeciud where ciud_cod_pais = '$pais_cod'  order by 2";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $arrayCiud[$oIfx->f('ciud_cod_ciud')] = $oIfx->f('ciud_nom_ciud');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $sql = "SELECT * from (
    select *,
    consulta.clpv_nom_clpv || ' ' || consulta.clpv_ruc_clpv  || ' ' || consulta.telefono  || ' ' || consulta.email AS coincidencias
    from (select 
    c.clpv_cod_clpv,
    c.clpv_nom_clpv, 
    c.clpv_ruc_clpv, 
    c.clpv_cod_ciud, 
    c.clpv_cod_vend, 
    c.clpv_dsc_prpg,
    c.clpv_pre_ven,
    c.clpv_est_clpv,
    c.clpv_dsc_clpv,	
    c.clpv_pro_pago, 
    c.clv_con_clpv,
    (select COALESCE(max(dire_dir_dire),'') from saedire where
                                        dire_cod_empr = c.clpv_cod_empr and
                                        dire_cod_clpv = c.clpv_cod_clpv limit 1 ) as direccion,
    (select COALESCE(max(tlcp_tlf_tlcp),'') from saetlcp where
                                        tlcp_cod_empr = c.clpv_cod_empr and
                                        tlcp_cod_clpv = c.clpv_cod_clpv and tlcp_tip_ticp='C'  ) as telefono,
    (select COALESCE(max(emai_ema_emai),'') from saeemai where
                                        emai_cod_empr = c.clpv_cod_empr and
                                        emai_cod_clpv = c.clpv_cod_clpv  ) as email
    FROM
    saeclpv c 
    where (c.clpv_cod_empr = $idempresa and
    c.clpv_clopv_clpv = 'PV' $fil_vend) and 
    (UPPER(c.clpv_ruc_clpv) like UPPER('%$campo_like%') or
    UPPER(c.clpv_nom_clpv) like UPPER('%$campo_like%'))
    order by c.clpv_nom_clpv limit 1000 ) as consulta ) as  consulta2
    where UPPER(coincidencias) like UPPER('%$campo_like%') limit 30";

    //    $oReturn->alert($sql);


    if ($oIfx->Query($sql)) {

        $sHtml = '<table id="tbclientes" class="table table-condensed table-responsive">';
        $sHtml .= '<thead>';

        $sHtml .= '<tr>
                    <th>No.</th>
                    <th>CODIGO</th>
                    <th>IDENTIFICACION</th>
                    <th>NOMBRE</th>
                    <th>EMAIL</th>
                    <th>TELEFONO</th>
                    <th>ESTADO</th>
                  </tr>';

        $sHtml .= '</thead>';
        $sHtml .= '<tbody>';

        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $codigo = ($oIfx->f('clpv_cod_clpv'));
                $paciente = ($oIfx->f('clpv_nom_clpv'));
                $paciente_ruc = ($oIfx->f('clpv_ruc_clpv'));
                $vendedor = $oIfx->f('clpv_cod_vend');
                $contacto = $oIfx->f('clpv_cot_clpv');
                $precio = round($oIfx->f('clpv_pre_ven'), 0);
                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');
                $clv_con_clpv = $oIfx->f('clv_con_clpv');
                $clpv_dsc_clpv = $oIfx->f('clpv_dsc_clpv');
                $clpv_dsc_prpg = $oIfx->f('clpv_dsc_prpg');
                $clpv_lim_cred = $oIfx->f('clpv_lim_cred');
                $clpv_cod_ciud = $oIfx->f('clpv_cod_ciud');
                $clpv_pro_pago = $oIfx->f('clpv_pro_pago');


                $dire = $oIfx->f('direccion');
                $telefono = $oIfx->f('telefono');
                $email = $oIfx->f('email');
                $celular = '';

                if ($clpv_est_clpv == 'A') {
                    $estado = 'ACTIVO';
                    $color = 'blue';
                } elseif ($clpv_est_clpv == 'P') {
                    $estado = 'PENDIENTE';
                    $color = 'green';
                } elseif ($clpv_est_clpv == 'S') {
                    $estado = 'SUSPENDIDO';
                    $color = 'red';
                } else {
                    $estado = '--';
                    $color = '';
                }


                $sHtml .= '<tr height="25"  
                            onclick="asignar_seg(' . $codigo . ', \'' . $paciente . '\', \'' . $paciente_ruc . '\', \'' . $dire . '\',
                                                \'' . $telefono . '\', \'' . $celular . '\', \'' . $vendedor . '\', \'' . $contacto . '\',  
						\'' . $precio . '\', \'' . $clpv_est_clpv . '\', \'' . $clv_con_clpv . '\',
                                                \'' . $clpv_dsc_clpv . '\', \'' . $clpv_dsc_prpg . '\',  \'' . $clpv_lim_cred . '\'
                                                ,  \'' . $arrayCiud[$clpv_cod_ciud] . '\',  \'' . $email . '\' ,' . $clpv_pro_pago . ')">
                            <td>' . $i . '</td>
                            <td>' . $codigo . '</td>       
                            <td>' . $paciente_ruc . '</td>                   
                            <td>' . $paciente . '</td>                       
                            <td>' . $email . '</td>                       
                            <td>' . $telefono . '</td>                       
                            <td>' . $estado . '</td> 
                            </tr>';
                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $sHtml .= '</tbody>';
    $sHtml .= '</table>';

    $oReturn->assign('divBusquedaSeg', 'innerHTML', $sHtml);
    $oReturn->assign('cliente_', 'focus()', '');
    $oReturn->script("jsRemoveWindowLoad();");
    $oReturn->script("init()");

    $oReturn->script("jsRemoveWindowLoad();");

    return $oReturn;
}
/**ORDEN COMPRA PROFORMA */
function orden_compra_proforma($codpedi, $codapro, $idempresa, $idsucursal, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    $oReturn = new xajaxResponse();


    $user_web  = $_SESSION['U_ID'];
    $fecha_mov = date('Y-m-d');
    $fecha_ifx = $fecha_mov;
    $tran_cod         = $aForm['transaccion'];
    $user_ifx       = $_SESSION['U_USER_INFORMIX'];

    //CORREO DE SOLICITANTE

    $sqlcorreo = "select usuario_email from comercial.usuario where usuario_id=$user_web";
    $mail_solicitante    = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');


    //CORREO ADMIN COMPRAS

    //PARAMETROS ENVIO DE CORREOS
    $ctrlcomp = 0;
    $sqlcorreo = "select  mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=20 limit 1";

    if ($oCon->Query($sqlcorreo)) {
        if ($oCon->NumFilas() > 0) {
            $mailenvio = $oCon->f('mail');
            $ctrlcomp++;
        }
    }
    $oCon->Free();
    if ($ctrlcomp == 0) {

        $sqlcorreo = "select mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=1 limit 1";

        if ($oCon->Query($sqlcorreo)) {
            if ($oCon->NumFilas() > 0) {
                $mailenvio = $oCon->f('mail');
                $password = $oCon->f('pass');
            }
        }
        $oCon->Free();
    }

    $i = 1;

    $sql = "select i.id_inv_prof, i.invp_num_invp ,
                    i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
                    i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.invp_det_prod
                    from comercial.inv_proforma i where
                    i.invp_cod_empr 	= $idempresa and
                    i.invp_cod_sucu 	= $idsucursal and
                    i.invp_esta_invp 	= 'S'  and
				    i.invp_esta_minv     = 'N' and
                    i.inv_cod_pedi  	= '$codpedi'
                    order by i.id_inv_prof; ";
    unset($array);
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $prod_cod       = $oCon->f('invp_cod_prod');
                $bode_cod       = $oCon->f('invp_cod_bode');
                $unid_cod       = $oCon->f('invp_unid_cod');
                $prbo_dis       = $oCon->f('invp_cant_stock');
                $pedido         = $oCon->f('invp_cant_real');
                $prod_nom       = $oCon->f('invp_nom_prod');
                $id_inv_prof    = $oCon->f('id_inv_prof');
                $invp_det_prod  = $oCon->f('invp_det_prod');
                $proforma  = $oCon->f('invp_num_invp');



                $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                    d.invpd_ema_clpv, d.invpd_costo_prod
                                    from comercial.inv_proforma_det d where
                                    d.id_inv_prof = $id_inv_prof and
								    d.invpd_esta_invpd = 'S'
                                    order by d.invpd_costo_prod";
                //$oReturn->alert($sql);
                unset($array_clpv);
                $x = 1;
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                            $ppvpr_pre_pac     = $oConA->f('invpd_costo_prod');
                            $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                            $correo_clpv    = $oConA->f('invpd_ema_clpv');
                            $id_inv_dprof   = $oConA->f('id_inv_dprof');

                            $serial            = '';
                            $serial         = $i . '-' . $ppvpr_cod_clpv;

                            $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $serial, $correo_clpv, $id_inv_dprof);


                            $x++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();

                $array[] = array($prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i, $prod_nom, $id_inv_prof,   $array_clpv);


                $i++;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    // TRANSACCIONALIDAD
    try {
        // commit
        $oCon->QueryT('BEGIN');
        //$oIfx->QueryT('BEGIN WORK;');
        unset($array_clpv_comp);

        $tmp = '';
        foreach ($array as $val) {
            //$prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv 						
            $prod_cod     = $val[0];
            $bode_cod     = $val[1];
            $unid_cod     = $val[2];
            $prbo_uco     = $val[3];
            $prbo_dis     = vacios($val[4], 0);
            $pedido       = $val[5];
            $i            = $val[6];
            $prod_nom     = $val[7];
            $id_inv_prof = $val[8];
            $array_cl   = $val[9];

            if (count($array_cl) > 0) {
                foreach ($array_cl as $val2) {
                    // $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof
                    $ppvpr_cod_clpv = $val2[0];
                    $ppvpr_pre_pac  = $val2[1];
                    $clpv_nom_clpv  = $val2[2];
                    $serial         = $val2[3];
                    $correo_clpv    = $val2[4];
                    $id_inv_dprof   = $val2[5];

                    $check             = $aForm[$serial . '_checkaut'];
                    $costo             = $aForm[$serial . '_c'];
                    $cantidad        = $aForm[$serial . '_ca'];

                    if (!empty($check)) {
                        $sql = "update comercial.inv_proforma set invp_fmov_minv = '$fecha_mov' , 
															invp_user_aprob 	= $user_web,  
															invp_esta_minv     	= 'S' ,
															invp_fmov_server 	= now() where
															invp_cod_empr 		= $idempresa and
															invp_cod_sucu 		= $idsucursal and
															id_inv_prof   		= $id_inv_prof ";
                        // $oReturn->alert($sql);
                        $oCon->QueryT($sql);

                        $sql = "update comercial.inv_proforma_det  set invpd_cun_dmov = $costo, 
																		invpd_cant_dmov = $cantidad ,
																		invpd_esta_invpd= 'S'	where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                        $oCon->QueryT($sql);

                        $tmp .= $id_inv_dprof . ',';
                    } else {
                        $sql = "update comercial.inv_proforma_det  set 
																		invpd_esta_invpd = 'N'	
																		where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                        $oCon->QueryT($sql);
                    }
                }
            }
        } // fin foreach
        $oCon->QueryT('COMMIT');
        $tmp .= '0';
        // PROFORMAS
        $sql = "select d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv
							from comercial.inv_proforma i, comercial.inv_proforma_det d where
							i.id_inv_prof   	= d.id_inv_prof and
							i.invp_cod_empr 	= $idempresa and
							i.invp_cod_sucu 	= $idsucursal and
							i.invp_esta_oc    	= 'S'  and
							i.inv_cod_pedi  	= '$codpedi' and
							d.invpd_esta_invpd 	= 'S' and
							d.id_inv_dprof in ( $tmp ) 
							group by 1,2,3
							order by 1 ";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $clpv_cod     = $oCon->f('invpd_cod_clpv');
                    $clpv_nom     = $oCon->f('invpd_nom_clpv');
                    $clpv_correo = $oCon->f('invpd_ema_clpv');

                    // ORDEN DE COMPRA
                    $class = new inventario_class();
                    unset($array_dat);
                    $array_dat = $class->saeminv(
                        $oIfx,
                        $oConA,
                        $idempresa,
                        $idsucursal,
                        $fecha_ifx,
                        $user_ifx,
                        'I',
                        $clpv_cod,
                        $proforma,
                        $proforma,
                        $user_web,
                        $proforma,
                        $tran_cod,
                        $clpv_correo
                    );

                    foreach ($array_dat as $val) {
                        $serial_minv = $val[0];
                        $idejer      = $val[1];
                        $idprdo      = $val[2];
                        $tran        = $val[3];
                        $hora        = $val[4];
                        $secu_minv   = $val[5];
                    } // fin forecah dat							

                    //$oReturn->alert($serial_minv);

                    $tabla = '';
                    $tabla .= '<table border="1">';
                    $tabla .= '<tr>';
                    $tabla .= '<td>N.-</td>';
                    $tabla .= '<td>Codigo</td>';
                    $tabla .= '<td>Producto</td>';
                    $tabla .= '<td>Detalle</td>';
                    $tabla .= '<td>Unidad</td>';
                    $tabla .= '<td>Cantidad</td>';
                    $tabla .= '<td>Costo</td>';
                    $tabla .= '</tr>';

                    $sql = "select d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv,
											i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, i.invp_unid_cod,
											d.invpd_cun_dmov, d.invpd_cant_dmov,  i.id_inv_prof ,  d.id_inv_dprof,
											invp_det_prod
											from comercial.inv_proforma i, comercial.inv_proforma_det d where
											i.id_inv_prof   	= d.id_inv_prof and
											i.invp_cod_empr 	= $idempresa and
											i.invp_cod_sucu 	= $idsucursal and
											i.invp_esta_invp 	= 'S'  and
											i.inv_cod_pedi  	= '$codpedi' and
											d.invpd_esta_invpd 	= 'S' and
											d.invpd_cod_clpv 	= $clpv_cod and
											d.id_inv_dprof in ( $tmp )";
                    $x = 1;
                    $total_minv = 0;

                    if ($oConA->Query($sql)) {
                        if ($oConA->NumFilas() > 0) {
                            do {
                                $bode_cod     = $oConA->f('invp_cod_bode');
                                $prod_cod     = $oConA->f('invp_cod_prod');
                                $prod_nom   = $oConA->f('invp_nom_prod');
                                $unid_cod   = $oConA->f('invp_unid_cod');
                                $costo      = $oConA->f('invpd_cun_dmov');
                                $cantidad   = $oConA->f('invpd_cant_dmov');
                                $id_inv_prof = $oConA->f('id_inv_prof');
                                $id_inv_dprof = $oConA->f('id_inv_dprof');
                                $invp_det_prod = $oConA->f('invp_det_prod');

                                $sql = "select unid_nom_unid from saeunid where 
														unid_cod_empr = $idempresa and
														unid_cod_unid = $unid_cod ";
                                $unid_nom = consulta_string_func($sql, 'unid_nom_unid', $oIfx, '0');


                                $sql = "update comercial.inv_proforma set invp_num_secu = '$proforma' , 
															invp_num_comp 		= $serial_minv  where
															invp_cod_empr 		= $idempresa and
															invp_cod_sucu 		= $idsucursal and
															id_inv_prof   		= $id_inv_prof ";
                                $oConB->QueryT($sql);

                                $sql = "update comercial.inv_proforma_det  set invpd_num_comp = $serial_minv, 
																		invpd_num_secu  = '$secu_minv' where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                                $oConB->QueryT($sql);

                                // SAEDMOV
                                $total_minv += $cantidad * $costo;
                                $bode_dest = 0;
                                $class->saedmov(
                                    $oIfx,
                                    $idempresa,
                                    $idsucursal,
                                    $fecha_ifx,
                                    $hora,
                                    $idejer,
                                    $idprdo,
                                    $serial_minv,
                                    $tran,
                                    $x,
                                    $bode_cod,
                                    $prod_cod,
                                    $unid_cod,
                                    $cantidad,
                                    $costo,
                                    'C',
                                    $bode_dest,
                                    '',
                                    '',
                                    ''
                                );



                                $tabla .= '<tr>';
                                $tabla .= '<td>' . $x . '</td>';
                                $tabla .= '<td>' . $prod_cod . '</td>';
                                $tabla .= '<td>' . $prod_nom . '</td>';
                                $tabla .= '<td>' . $invp_det_prod . '</td>';
                                $tabla .= '<td>' . $unid_nom . '</td>';
                                $tabla .= '<td>' . $cantidad . '</td>';
                                $tabla .= '<td>' . $costo . '</td>';
                                $tabla .= '</tr>';

                                $x++;
                            } while ($oConA->SiguienteRegistro());
                            $tabla .= '</table>';
                        }
                    } // fin


                    // UPDATE A LA MINV TOTAL 
                    $sql_update = "update saeminv set minv_tot_minv = $total_minv where
												minv_num_comp = $serial_minv and
												minv_cod_empr = $idempresa and
												minv_cod_sucu = $idsucursal and
												minv_cod_tran = '$tran' and
												minv_cod_modu = 10 and
												minv_cod_ejer = $idejer ";
                    $oConA->QueryT($sql_update);

                    ///ARRAY PROVEEDORES ORDENES DE COMPRA

                    $array_clpv_comp[] = array($clpv_cod, $serial_minv);
                } while ($oCon->SiguienteRegistro());
            }
        } // fin				
        //ACTUALIZACION ESTADO ACCIONES PROFORMA
        $fecha_apro = date('Y-m-d H:i:s');

        $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud,   usuario, fecha) 
                    values
                    ($idempresa, $idsucursal, $codapro, '$codpedi',  $user_web, '$fecha_apro')";
        $oCon->QueryT($sqlapro);


        $oCon->QueryT('COMMIT');
        //$oIfx->QueryT('COMMIT WORK;'); 



        if (count($array_clpv_comp) > 0) {

            $array_clpv_comp = array_unique($array_clpv_comp, SORT_REGULAR);

            //CODIGO DE LA PROXIMA APROBACION
            $sql = "SELECT id from comercial.aprobaciones_compras 
                    where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFOCO'";

            $cod_apro = consulta_string($sql, 'id', $oCon, 0);


            foreach ($array_clpv_comp as $clpv) {

                ///FORMATO ORDEN DE COMPRA

                $sql = "select defi_for_defi from saetran, saedefi where defi_cod_empr=tran_cod_empr 
                            and defi_cod_tran=tran_cod_tran
                            and tran_cod_modu=10 and tran_cod_tran like '002%' and tran_cod_empr=$idempresa 
                            and tran_cod_sucu=$idsucursal";

                $cod_ftrn = consulta_string($sql, 'defi_for_defi', $oIfx, '');
                $ctrl_formato = 0;
                if (!empty($cod_ftrn)) {
                    $sqlf = "select ftrn_ubi_web from saeftrn where ftrn_cod_ftrn=$cod_ftrn";
                    $ubi_formato = consulta_string($sqlf, 'ftrn_ubi_web', $oIfx, '');

                    if (!empty($ubi_formato)) {
                        $ctrl_formato++;
                        include_once('../../' . $ubi_formato . '');
                        formato_orden_compra($clpv[1]);
                        $ruta = DIR_FACTELEC . 'Include/orden_compra/ORDEN_COMPRA_' . $clpv[1] . '.pdf';
                    }
                }

                if ($ctrl_formato == 0) {

                    //VALIDACION POR PAIS
                    $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];
                    //PERU  
                    if ($S_PAIS_API_SRI == '51') {
                        include_once('../../Include/Formatos/comercial/orden_compra_peru.php');

                        formato_orden_compra($clpv[1]);
                        $ruta = DIR_FACTELEC . 'Include/orden_compra/ORDEN_COMPRA_' . $clpv[1] . '.pdf';
                    }
                    //ECUADOR 
                    else {
                        $ruta = reporte_orden_compra($clpv[1], '');
                    }
                }

                // Instanciamos la clase NotificacionesCompras
                $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $proforma, '', $ruta);

                // Enviar correo a los provedores
                $notifier->enviarCorreoProveedores($mail_solicitante, $clpv[0]);
                // Enviar WhatsApp a los proveedores
                $notifier->enviarWhatsAppProveedores($clpv[0]);
            }
        }





        $oReturn->script("alertSwal('Se Ingreso Correctamente la Orden de Compra: $secu_minv', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('cerrarModalProfProv();');
        $oReturn->script("reporte_solicitudes()");
    } catch (Exception $e) {
        // rollback
        $oReturn->script("jsRemoveWindowLoad();");
        $oCon->QueryT('ROLLBACK');
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
/**AUTORIZAR PRECIOS DE PROFORMA */
function autorizar_precios_proforma($codpedi, $codapro, $idempresa, $idsucursal, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    $oReturn = new xajaxResponse();


    $user_web       = $_SESSION['U_ID'];
    $fecha_mov = date('Y-m-d');

    $i = 1;

    $sql = "select i.id_inv_prof, i.invp_num_invp ,
                    i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
                    i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.invp_det_prod
                    from comercial.inv_proforma i where
                    i.invp_cod_empr 	= $idempresa and
                    i.invp_cod_sucu 	= $idsucursal and
                    i.invp_esta_invp 	= 'S'  and
				    i.invp_esta_oc     = 'N' and
                    i.inv_cod_pedi  	= '$codpedi'
                    order by i.id_inv_prof; ";
    unset($array);
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $prod_cod       = $oCon->f('invp_cod_prod');
                $bode_cod       = $oCon->f('invp_cod_bode');
                $unid_cod       = $oCon->f('invp_unid_cod');
                $prbo_dis       = $oCon->f('invp_cant_stock');
                $pedido         = $oCon->f('invp_cant_real');
                $prod_nom       = $oCon->f('invp_nom_prod');
                $id_inv_prof    = $oCon->f('id_inv_prof');
                $invp_det_prod  = $oCon->f('invp_det_prod');
                $secu_prof  = $oCon->f('invp_num_invp');



                $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                    d.invpd_ema_clpv, d.invpd_costo_prod
                                    from comercial.inv_proforma_det d where
                                    d.id_inv_prof = $id_inv_prof and
								    d.invpd_esta_invpd = 'S'
                                    order by d.invpd_costo_prod";
                //$oReturn->alert($sql);
                unset($array_clpv);
                $x = 1;
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                            $ppvpr_pre_pac     = $oConA->f('invpd_costo_prod');
                            $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                            $correo_clpv    = $oConA->f('invpd_ema_clpv');
                            $id_inv_dprof   = $oConA->f('id_inv_dprof');

                            $serial            = '';
                            $serial         = $i . '-' . $ppvpr_cod_clpv;

                            $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $serial, $correo_clpv, $id_inv_dprof);


                            $x++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();

                $array[] = array($prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i, $prod_nom, $id_inv_prof,   $array_clpv);


                $i++;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    // TRANSACCIONALIDAD
    try {
        // commit
        $oCon->QueryT('BEGIN');
        //$oIfx->QueryT('BEGIN WORK;');


        foreach ($array as $val) {
            //$prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv 						
            $prod_cod     = $val[0];
            $bode_cod     = $val[1];
            $unid_cod     = $val[2];
            $prbo_uco     = $val[3];
            $prbo_dis     = vacios($val[4], 0);
            $pedido       = $val[5];
            $i            = $val[6];
            $prod_nom     = $val[7];
            $id_inv_prof = $val[8];
            $array_cl   = $val[9];

            if (count($array_cl) > 0) {
                foreach ($array_cl as $val2) {
                    // $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof
                    $ppvpr_cod_clpv = $val2[0];
                    $ppvpr_pre_pac  = $val2[1];
                    $clpv_nom_clpv  = $val2[2];
                    $serial         = $val2[3];
                    $correo_clpv    = $val2[4];
                    $id_inv_dprof   = $val2[5];

                    $check             = $aForm[$serial . '_checkaut'];
                    $costo             = $aForm[$serial . '_c'];
                    $cantidad        = $aForm[$serial . '_ca'];


                    if (!empty($check)) {
                        $sql = "update comercial.inv_proforma set invp_fmov_minv = '$fecha_mov' , 
															invp_user_aprob 	= $user_web,  
															invp_esta_oc   	= 'S' ,
															invp_fmov_server 	= now() where
															invp_cod_empr 		= $idempresa and
															invp_cod_sucu 		= $idsucursal and
															id_inv_prof   		= $id_inv_prof ";
                        $oCon->QueryT($sql);

                        $sql = "update comercial.inv_proforma_det  set invpd_cun_dmov = $costo, 
																		invpd_cant_dmov  = $cantidad ,
																		invpd_esta_invpd = 'S'	
																		where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                        $oCon->QueryT($sql);
                    } else {
                        $sql = "update comercial.inv_proforma_det  set 
																		invpd_esta_invpd = 'N'	
																		where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                        $oCon->QueryT($sql);
                    }
                }
            }
        } // fin foreach

        //ACTUALIZACION ESTADO ACCIONES PROFORMA
        $fecha_apro = date('Y-m-d H:i:s');

        $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud,   usuario, fecha) 
                    values
                    ($idempresa, $idsucursal, $codapro, '$codpedi',  $user_web, '$fecha_apro')";
        $oCon->QueryT($sqlapro);


        $oCon->QueryT('COMMIT');
        //$oIfx->QueryT('COMMIT WORK;'); 


        //CODIGO DE LA PROXIMA APROBACION
        $sql = "SELECT id from comercial.aprobaciones_compras 
                where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFOCO'";

        $cod_apro = consulta_string($sql, 'id', $oCon, 0);

        ///FORMATO PERSONALIZADO CUADRO COMPARATIVO
        $sql = "select ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
                ftrn_des_ftrn = 'CUADRO COMPARATIVO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";
        $ubi = consulta_string($sql, 'ftrn_ubi_web', $oConA, '');

        //AUTRIZACION PROFORMA
        $estado_prof = 5;
        if (!empty($ubi)) {
            include_once('../../' . $ubi . '');
            $ruta = genera_cuadro_comparativo($codpedi, $idempresa, $idsucursal, 1, $estado_prof);
        } else {
            include_once('../../Include/Formatos/comercial/cuadro_comparativo_proveedores.php');
            $ruta = genera_cuadro_comparativo($codpedi, $idempresa, $idsucursal, 1, $estado_prof);
        }


        //MENSAJE DE CORREO Y WHATSAPP
        $mensaje = 'La proforma <b>N. ' . $secu_prof . '</b> ha sido autorizada<br>Proceda con la generacion de la orden de compra';
        $text_envio = 'La proforma *N. ' . $secu_prof . '* ha sido autorizada\nProceda con la generacion de la orden de compra';

        // Instanciamos la clase NotificacionesCompras
        $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $secu_prof, '', $ruta);
        // Enviar correo a los aprobadores
        $notifier->enviarCorreoAprobadores($mensaje);
        // Enviar WhatsApp a los aprobadores
        $notifier->enviarWhatsAppAprobadores($text_envio);


        $oReturn->script("alertSwal('Se Autorizo Correctamente la Proforma: $secu_prof', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('cerrarModalProfProv();');
        $oReturn->script("reporte_solicitudes()");
    } catch (Exception $e) {
        // rollback
        $oReturn->script("jsRemoveWindowLoad();");
        $oCon->QueryT('ROLLBACK');
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function get_producto_no_registrado($empresa, $sucursal)
{
    $oReturn = new xajaxResponse();

    // Ajusta si ya tienes $this->idempresa / $this->idsucursal
    $empresa  = (int)$empresa;
    $sucursal = (int)$sucursal;

    // Consulta de configuracion
    $sql = "
        SELECT cod_bode_pedi, cod_prod_pedi
        FROM comercial.parametro_inv
        WHERE empr_cod_empr = $empresa
          AND sucu_cod_sucu = $sucursal
        LIMIT 1;
    ";

    $row = consulta_string($sql); // la misma que ya usas en otras partes

    if ($row && isset($row['cod_bode_pedi'], $row['cod_prod_pedi'])) {

        $codBodega  = trim($row['cod_bode_pedi']);
        $codProducto = trim($row['cod_prod_pedi']);

        // Llamamos a una funcion JS para setear bodega y producto
        $oReturn->script("
            setProductoNoRegistradoDesdeConfig(
                '{$codBodega}',
                '{$codProducto}'
            );
        ");
    } else {
        $oReturn->script("
            alertSwal(
                'No existe configuracion de Producto no registrado para esta empresa/sucursal',
                'warning'
            );
        ");
    }

    return $oReturn;
}



/**INGRESAR PRECIOS DE PROFORMA */
function ingresar_precios_proforma($codpedi, $codapro, $idempresa, $idsucursal, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    $oReturn = new xajaxResponse();


    $user_web       = $_SESSION['U_ID'];
    $fecha_mov = date('Y-m-d');

    $i = 1;

    $sql = "select i.id_inv_prof, i.invp_num_invp ,
                    i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
                    i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.invp_det_prod
                    from comercial.inv_proforma i where
                    i.invp_cod_empr 	= $idempresa and
                    i.invp_cod_sucu 	= $idsucursal and
                    i.invp_esta_invp 	= 'N'  and
                    i.inv_cod_pedi  	= '$codpedi'
                    order by i.id_inv_prof; ";
    unset($array);
    $total       = 0;
    $tot_ret     = 0;
    $tot_pedi    = 0;
    $tot_stock = 0;
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $prod_cod       = $oCon->f('invp_cod_prod');
                $bode_cod       = $oCon->f('invp_cod_bode');
                $unid_cod       = $oCon->f('invp_unid_cod');
                $prbo_dis       = $oCon->f('invp_cant_stock');
                $pedido         = $oCon->f('invp_cant_real');
                $prod_nom       = $oCon->f('invp_nom_prod');
                $id_inv_prof    = $oCon->f('id_inv_prof');
                $invp_det_prod  = $oCon->f('invp_det_prod');
                $secu_prof  = $oCon->f('invp_num_invp');

                $bode_nom       = $array_bode[$bode_cod];
                $unid_nom       = $array_unid[$unid_cod];


                $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                    d.invpd_ema_clpv, d.invpd_costo_prod
                                    from comercial.inv_proforma_det d where
                                    d.id_inv_prof = $id_inv_prof 
                                    order by d.invpd_costo_prod  ";
                //$oReturn->alert($sql);
                unset($array_clpv);
                $x = 1;
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                            $ppvpr_pre_pac     = $oConA->f('invpd_costo_prod');
                            $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                            $correo_clpv    = $oConA->f('invpd_ema_clpv');
                            $id_inv_dprof   = $oConA->f('id_inv_dprof');

                            $serial            = '';
                            $serial         = $i . '-' . $ppvpr_cod_clpv;

                            $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $serial, $correo_clpv, $id_inv_dprof);


                            $x++;
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();

                $array[] = array($prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i, $prod_nom, $id_inv_prof,   $array_clpv);


                $i++;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    // TRANSACCIONALIDAD
    try {
        // commit
        $oCon->QueryT('BEGIN');
        //$oIfx->QueryT('BEGIN WORK;');


        foreach ($array as $val) {
            //$prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv 						
            $prod_cod     = $val[0];
            $bode_cod     = $val[1];
            $unid_cod     = $val[2];
            $prbo_uco     = $val[3];
            $prbo_dis     = vacios($val[4], 0);
            $pedido       = $val[5];
            $i            = $val[6];
            $prod_nom     = $val[7];
            $id_inv_prof = $val[8];
            $array_cl   = $val[9];

            if (count($array_cl) > 0) {
                foreach ($array_cl as $val2) {
                    // $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof
                    $ppvpr_cod_clpv = $val2[0];
                    $ppvpr_pre_pac  = $val2[1];
                    $clpv_nom_clpv  = $val2[2];
                    $serial         = $val2[3];
                    $correo_clpv    = $val2[4];
                    $id_inv_dprof   = $val2[5];

                    $check             = $aForm[$serial . '_checkprec'];
                    $costo             = $aForm[$serial . '_c'];
                    $cantidad        = $aForm[$serial . '_ca'];
                    $archivo        = substr($aForm[$serial . '_adj'], 3);

                    if (!empty($check)) {
                        $sql = "update comercial.inv_proforma set invp_fmov_minv = '$fecha_mov' , 
															invp_user_aprob 	= $user_web,  
															invp_esta_invp   	= 'S' ,
															invp_fmov_server 	= now() where
															invp_cod_empr 		= $idempresa and
															invp_cod_sucu 		= $idsucursal and
															id_inv_prof   		= $id_inv_prof ";
                        $oCon->QueryT($sql);

                        $sql = "update comercial.inv_proforma_det  set invpd_cun_dmov = $costo, 
																		invpd_cant_dmov  = $cantidad ,
																		invpd_esta_invpd = 'S'	,
																		invpd_adjunto    = '$archivo' 
																		where
																		id_inv_dprof    = $id_inv_dprof and
																		id_inv_prof     = $id_inv_prof ";
                        $oCon->QueryT($sql);
                    }
                }
            }
        } // fin foreach

        //ACTUALIZACION ESTADO ACCIONES PROFORMA
        $fecha_apro = date('Y-m-d H:i:s');

        $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud,   usuario, fecha) 
                    values
                    ($idempresa, $idsucursal, $codapro, '$codpedi',  $user_web, '$fecha_apro')";
        $oCon->QueryT($sqlapro);


        $oCon->QueryT('COMMIT');
        //$oIfx->QueryT('COMMIT WORK;'); 


        //CODIGO DE LA PROXIMA APROBACION
        $sql = "SELECT id from comercial.aprobaciones_compras 
                where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFAUT'";

        $cod_apro = consulta_string($sql, 'id', $oCon, 0);

        //MENSAJE DE CORREO Y WHATSAPP
        $mensaje = 'La proforma <b>N. ' . $secu_prof . '</b> ha sido ingresada<br>Requiere su revision y aprobacion';
        $text_envio = 'La proforma *N. ' . $secu_prof . '* ha sido ingresada\nRequiere su revision y aprobacion';

        // Instanciamos la clase NotificacionesCompras
        $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $secu_prof, '', '');
        // Enviar correo a los aprobadores
        $notifier->enviarCorreoAprobadores($mensaje);
        // Enviar WhatsApp a los aprobadores
        $notifier->enviarWhatsAppAprobadores($text_envio);


        $oReturn->script("alertSwal('Se Ingresaron Correctamente los precios de la Proforma: $secu_prof', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('cerrarModalProfProv();');
        $oReturn->script("reporte_solicitudes()");
    } catch (Exception $e) {
        // rollback
        $oReturn->script("jsRemoveWindowLoad();");
        $oCon->QueryT('ROLLBACK');
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
/*GUARDAR PROFORMA*/
function generar_proforma($codpedi, $codapro, $idempresa, $idsucursal, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();


    $user_web       =  $_SESSION['U_ID'];

    //CORREO DE SOLICITANTE

    $sqlcorreo = "select usuario_email from comercial.usuario where usuario_id=$user_web";
    $mail_solicitante    = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');


    //CORREO ADMIN COMPRAS

    //PARAMETROS ENVIO DE CORREOS
    $ctrlcomp = 0;
    $sqlcorreo = "select  mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=20 limit 1";

    if ($oCon->Query($sqlcorreo)) {
        if ($oCon->NumFilas() > 0) {
            $mailenvio = $oCon->f('mail');
            $ctrlcomp++;
        }
    }
    $oCon->Free();
    if ($ctrlcomp == 0) {

        $sqlcorreo = "select mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=1 limit 1";

        if ($oCon->Query($sqlcorreo)) {
            if ($oCon->NumFilas() > 0) {
                $mailenvio = $oCon->f('mail');
                $password = $oCon->f('pass');
            }
        }
        $oCon->Free();
    }

    //ARRAY DE PRODCUCTOS

    unset($array);


    ///SE VALIDA QUE ESTE SELECCIONADO AL MENOS UN PROVEEDOR Y QUE LAS CANTIDADES INGRESADAS SEAN MAYORES

    $ctrl_prove = 'N';

    //CONSULTAMOS LOS PRODUCTOS
    $i = 1;
    $sql = "SELECT  d.dped_cod_prod, d.dped_cod_bode, d.dped_cod_unid, d.dped_det_dped,
                pr.prbo_uco_prod, pr.prbo_dis_prod, sum(d.dped_can_apro) as cant
                from saepedi p,   saedped d, saeprbo pr  where
                pr.prbo_cod_prod = d.dped_cod_prod and
                pr.prbo_cod_bode = d.dped_cod_bode and
                p.pedi_cod_pedi  = d.dped_cod_pedi and
                p.pedi_cod_empr  = $idempresa and
                p.pedi_cod_sucu  = $idsucursal and
                p.pedi_est_pedi  = '2'  and
                d.dped_cod_empr  = $idempresa and
                d.dped_cod_sucu  = $idsucursal and
                dped_ban_dped    = '0' and
                pr.prbo_cod_empr = $idempresa and
                pr.prbo_cod_sucu = $idsucursal  and
                p.pedi_cod_pedi = '$codpedi' and
                p.pedi_est_prof  = 'N' 
                group by 1,2,3,4,5,6 order by 1 ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $prod_cod       = $oIfx->f('dped_cod_prod');
                $pedido         = $oIfx->f('cant');
                $unid_cod       = $oIfx->f('dped_cod_unid');
                $detalle  = $oIfx->f('dped_det_dped');
                $prbo_uco       = $oIfx->f('prbo_uco_prod');
                $prbo_dis       = $oIfx->f('prbo_dis_prod');
                $bode_cod       = $oIfx->f('dped_cod_bode');

                $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_sucu = $idsucursal and prod_cod_prod  = '$prod_cod' ";
                $prod_nom = consulta_string($sql, 'prod_nom_prod', $oIfxA, 0);

                $sql = "SELECT clpe_cod_clpv, clpe_nom_clpv, clpe_ema_clpv, clpe_pre_pac, clpe_tlf_clpv from comercial.clpv_pedi 
                                   where clpe_cod_pedi=$codpedi and clpe_cod_empr=$idempresa and clpe_cod_sucu= $idsucursal
                                   and clpe_cod_prod='$prod_cod'  ";
                unset($array_clpv);
                $x = 1;
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $ppvpr_cod_clpv = $oIfxA->f('clpe_cod_clpv');
                            $ppvpr_pre_pac     = $oIfxA->f('clpe_pre_pac');
                            $clpv_nom_clpv     = $oIfxA->f('clpe_nom_clpv');
                            $correo_clpv    = $oIfxA->f('clpe_ema_clpv');
                            $movil_clpv    = $oIfxA->f('clpe_tlf_clpv');

                            //VALIDACION CHECK PROVEEDORES
                            $eti = 'checkclpv_' . $i . '_' . $x;
                            $check = $aForm[$eti];
                            if ($check == 'S') {
                                $ctrl_prove = 'S';
                                $array_clpv[] = array($ppvpr_cod_clpv, $ppvpr_pre_pac, $clpv_nom_clpv, $eti, $correo_clpv, $movil_clpv);
                            }
                            $x++;
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                $array[] = array($prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i, $prod_nom,  $array_clpv, $detalle);

                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();



    if ($ctrl_prove == 'N') {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("alertSwal('Seleccione los Proveedores', 'warning');");
    } else {

        // TRANSACCIONALIDAD
        try {
            // commit
            $oCon->QueryT('BEGIN');

            //FECHA DE PEDIDO
            $sqlf = "select pedi_fec_pedi from saepedi where pedi_cod_pedi='$codpedi'
                and pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal";
            $fecha_ini = consulta_string_func($sqlf, 'pedi_fec_pedi', $oConA, '');
            $fecha_fin = $fecha_ini; //AL SER UN SOLO PEDIDO SE COLOCA AMBAS FECHAS IGUALES

            // SECUENCIAL
            $sql = "select max(i.invp_num_invp) as dato from comercial.inv_proforma i where  i.invp_cod_empr = $idempresa ";
            $secu_prof = consulta_string_func($sql, 'dato', $oCon, '0');
            $secu_prof = secuencial(2, '0', $secu_prof, 9);


            foreach ($array as $val) {
                $prod_cod = $val[0];
                $bode_cod = $val[1];
                $unid_cod = $val[2];
                $prbo_uco = $val[3];
                $prbo_dis = vacios($val[4], 0);
                $pedido   = $val[5];
                $i        = $val[6];
                $prod_nom = $val[7];
                $array_cl = $val[8];
                $detalle  = $val[9];
                $cant_real = $aForm[$i];

                if (round($cant_real > 0 && count($array_cl) > 0)) {


                    $sql_ultimo_id = "select max(id_inv_prof) as id_inv_prof from comercial.inv_proforma";
                    $ultimo_id = consulta_string($sql_ultimo_id, 'id_inv_prof', $oCon, '') + 1;

                    // CABECERA						
                    $sql = "insert into comercial.inv_proforma ( id_inv_prof, 	invp_cod_empr,		invp_cod_sucu,		invp_num_invp,		invp_fecha_ini,
														   invp_fecha_fin,		invp_cod_bode,		invp_cod_prod,		invp_nom_prod,
														   invp_unid_cod,		invp_cant_pedi,		invp_cant_stock,	invp_cant_real,
														   invp_fecha_server,	invp_usuario_id,	invp_esta_invp ,     invp_det_prod,  inv_cod_pedi)
												   values( $ultimo_id,		$idempresa,			$idsucursal,		'$secu_prof',		'$fecha_ini',
														   '$fecha_fin',		$bode_cod,			'$prod_cod',		'$prod_nom',
														   $unid_cod,			$pedido,			$prbo_dis,			$cant_real,
														   now(),				$user_web,			'N',          '$detalle',      '$codpedi') ";
                    $oCon->QueryT($sql);

                    // SERIAL
                    $sql = "select i.id_inv_prof from comercial.inv_proforma i where  
									i.invp_cod_empr 	= $idempresa and
									i.invp_cod_sucu 	= $idsucursal and
									i.invp_num_invp 	= '$secu_prof' and
									i.invp_fecha_ini 	= '$fecha_ini' and
									i.invp_fecha_fin 	= '$fecha_fin' and
									i.invp_cod_bode 	= $bode_cod and
									i.invp_cod_prod 	= '$prod_cod' ";
                    $id_inv_prof = consulta_string_func($sql, 'id_inv_prof', $oCon, '0');


                    if (count($array_cl) > 0) {
                        foreach ($array_cl as $val2) {
                            $ppvpr_cod_clpv = $val2[0];
                            $ppvpr_pre_pac  = $val2[1];
                            if (empty($ppvpr_pre_pac)) {
                                $ppvpr_pre_pac = 0;
                            }
                            $clpv_nom_clpv  = $val2[2];
                            $serial         = $val2[3];
                            $correo_clpv    = $val2[4];
                            $movil_clpv    = $val2[5];

                            $check             = $aForm[$serial];
                            if (!empty($check)) {

                                $sql_ultimo_id = "select max(id_inv_dprof) as id_inv_dprof from comercial.inv_proforma_det";
                                $ultimo_id = consulta_string($sql_ultimo_id, 'id_inv_dprof', $oCon, 0) + 1;

                                $sql = "insert into comercial.inv_proforma_det ( id_inv_dprof, id_inv_prof,		invpd_cod_clpv,			invpd_nom_clpv,
																			   invpd_ema_clpv,	invpd_costo_prod,		invpd_costo_real, invpd_movil_clpv )
																	    values( $ultimo_id,	$id_inv_prof,	$ppvpr_cod_clpv,		'$clpv_nom_clpv',
																			   '$correo_clpv',  $ppvpr_pre_pac, 0, '$movil_clpv' )";
                                $oCon->QueryT($sql);
                            }
                        }
                    }
                } //VALIDACION CANTIDADES					
            } // fin foreach


            ///ACTUALIZACION ESTADO DE PEDIDO PROFORMA

            $sql = "update saepedi set pedi_est_prof  = 'S' where
								pedi_cod_empr  = $idempresa and
								pedi_cod_sucu  = $idsucursal and
								pedi_est_pedi  = '2'  and
								pedi_cod_pedi  =  '$codpedi'
								and pedi_est_prof  = 'N'  ";
            $oCon->QueryT($sql);

            //ACTUALIZACION ESTADO ACCIONES PROFORMA
            $observaciones = 'PROFORMA: ' . $secu_prof;
            $fecha_apro = date('Y-m-d H:i:s');

            $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud,  observaciones, usuario, fecha) 
                    values
                    ($idempresa, $idsucursal, $codapro, '$codpedi', '$observaciones', $user_web, '$fecha_apro')";
            $oCon->QueryT($sqlapro);


            $oCon->QueryT('COMMIT');


            //CODIGO DE LA PROXIMA APROBACION
            $sql = "SELECT id from comercial.aprobaciones_compras 
                where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFPREC'";

            $cod_apro = consulta_string($sql, 'id', $oCon, 0);

            //MENSAJE DE CORREO Y WHATSAPP
            $mensaje = 'La proforma <b>N. ' . $secu_prof . '</b> ha sido generada<br>Proceda con el ingreso de los precios de las cotizaciones enviadas';
            $text_envio = 'La proforma *N. ' . $secu_prof . '* ha sido generada \nProceda con el ingreso de los precios de las cotizaciones enviadas';

            // Instanciamos la clase NotificacionesCompras
            $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $secu_prof, null, null);
            // Enviar correo a los aprobadores
            $notifier->enviarCorreoAprobadores($mensaje);
            // Enviar correo a los provedores
            $notifier->enviarCorreoProveedores($mail_solicitante, null);
            // Enviar WhatsApp a los aprobadores
            $notifier->enviarWhatsAppAprobadores($text_envio);
            // Enviar WhatsApp a los proveedores
            $notifier->enviarWhatsAppProveedores(null);


            $oReturn->script("alertSwal('Ingresado Correctamente la Proforma: $secu_prof', 'success');");
            $oReturn->script("jsRemoveWindowLoad();");
            $oReturn->script('cerrarModalProfProv();');
            $oReturn->script("reporte_solicitudes()");
        } catch (Exception $e) {
            // rollback
            $oReturn->script("jsRemoveWindowLoad();");
            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    }

    return $oReturn;
}
/*INGRESAR PROVEEDORES PROFORMA*/
function agregar_proveedor($codpedi, $codapro, $idempresa, $idsucursal, $tipo, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();

    $filprof = '';
    if ($tipo == 1) {
        $filprof = "and p.pedi_est_prof  = 'N'";
    } elseif ($tipo == 2) {
        $filprof = "and p.pedi_est_prof  = 'S'";
    }

    $user_web       =  $_SESSION['U_ID'];
    //CORREO DE SOLICITANTE

    $sqlcorreo = "select usuario_email from comercial.usuario where usuario_id=$user_web";
    $mail_solicitante    = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');


    //CORREO ADMIN COMPRAS

    //PARAMETROS ENVIO DE CORREOS
    $ctrlcomp = 0;
    $sqlcorreo = "select  mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=20 limit 1";

    if ($oCon->Query($sqlcorreo)) {
        if ($oCon->NumFilas() > 0) {
            $mailenvio = $oCon->f('mail');
            $ctrlcomp++;
        }
    }
    $oCon->Free();
    if ($ctrlcomp == 0) {

        $sqlcorreo = "select mail from comercial.config_email
        where id_empresa = $idempresa and id_tipo=1 limit 1";

        if ($oCon->Query($sqlcorreo)) {
            if ($oCon->NumFilas() > 0) {
                $mailenvio = $oCon->f('mail');
                $password = $oCon->f('pass');
            }
        }
        $oCon->Free();
    }

    try {


        //CONTROL SELECCION DE PRODUCTOS
        $ctrl_sel = 'N';
        $i = 1;
        $sql = "SELECT  d.dped_cod_prod, d.dped_cod_bode, d.dped_cod_unid,d.dped_det_dped, 
					pr.prbo_uco_prod, pr.prbo_dis_prod, sum(d.dped_can_apro) as cant
					from saepedi p,   saedped d, saeprbo pr  where
					pr.prbo_cod_prod = d.dped_cod_prod and
					pr.prbo_cod_bode = d.dped_cod_bode and
					p.pedi_cod_pedi  = d.dped_cod_pedi and
					p.pedi_cod_empr  = $idempresa and
					p.pedi_cod_sucu  = $idsucursal and
					p.pedi_est_pedi  = '2'  and
					d.dped_cod_empr  = $idempresa and
					d.dped_cod_sucu  = $idsucursal and
					dped_ban_dped    = '0' and
					pr.prbo_cod_empr = $idempresa and
					pr.prbo_cod_sucu = $idsucursal  and
					p.pedi_cod_pedi = '$codpedi' 
                    $filprof
					group by 1,2,3,4,5,6 order by 1 ";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $eti = "checkprov_$i";

                    $check_prod = $aForm[$eti];

                    if ($check_prod == 'S') {
                        $ctrl_sel = 'S';
                    }
                    $i++;
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        $oIfxA->Free();


        //SE VALIDA LA SELECCION DE PRODUCTOS

        if ($ctrl_sel != 'N') {

            //variables deL MODAL
            $clpv_nom         = $aForm['proveedor_nombre'];
            $clpv_cod         = $aForm['proveedor'];
            $clpv_correo     = trim($aForm['proveedor_correo']);
            $clpv_movil     = trim($aForm['proveedor_movil']);
            $ppvpr_pre_ult     = vacios($aForm['ppvpr_pre_ult'], 0);
            $ppvpr_pre_pac     = vacios($aForm['ppvpr_pre_pac'], 0);

            $ppvpr_dia_entr = 0;
            $ppvpr_val_merm = 0;

            // commit
            $oIfx->QueryT('BEGIN WORK;');


            $i = 1;
            $array_prove = array();

            //CONSULTAMOS LOS PRODUCTOS
            $sql = "SELECT  d.dped_cod_prod, d.dped_cod_bode, d.dped_cod_unid,d.dped_det_dped, 
					pr.prbo_uco_prod, pr.prbo_dis_prod, sum(d.dped_can_apro) as cant
					from saepedi p,   saedped d, saeprbo pr  where
					pr.prbo_cod_prod = d.dped_cod_prod and
					pr.prbo_cod_bode = d.dped_cod_bode and
					p.pedi_cod_pedi  = d.dped_cod_pedi and
					p.pedi_cod_empr  = $idempresa and
					p.pedi_cod_sucu  = $idsucursal and
					p.pedi_est_pedi  = '2'  and
					d.dped_cod_empr  = $idempresa and
					d.dped_cod_sucu  = $idsucursal and
					dped_ban_dped    = '0' and
					pr.prbo_cod_empr = $idempresa and
					pr.prbo_cod_sucu = $idsucursal  and
					p.pedi_cod_pedi = '$codpedi' 
                    $filprof
					group by 1,2,3,4,5,6 order by 1 ";
            if ($oIfxA->Query($sql)) {
                if ($oIfxA->NumFilas() > 0) {
                    do {
                        $prod_cod       = $oIfxA->f('dped_cod_prod');
                        $pedido         = $oIfxA->f('cant');
                        $unid_cod       = $oIfxA->f('dped_cod_unid');
                        $dped_det_dped  = $oIfxA->f('dped_det_dped');
                        $prbo_uco       = $oIfxA->f('prbo_uco_prod');
                        $prbo_dis       = $oIfxA->f('prbo_dis_prod');
                        $bode_cod       = $oIfxA->f('dped_cod_bode');


                        $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_sucu = $idsucursal and prod_cod_prod  = '$prod_cod' ";
                        $prod_nom = consulta_string($sql, 'prod_nom_prod', $oIfxB, 0);

                        $eti = "checkprov_$i";

                        $check_prod = $aForm[$eti];

                        if ($check_prod == 'S') {

                            // direccion
                            $sql = "select min( dire_dir_dire ) as dire  from saedire where
                                        dire_cod_empr = $idempresa and
                                        dire_cod_clpv = $clpv_cod ";
                            $direccion = acento_func(consulta_string_func($sql, 'dire', $oIfxB, ''));


                            //CONTROL PROVEEDOR INGRESADO
                            $sqlp = "SELECT count(*) as conteo from comercial.clpv_pedi where clpe_cod_pedi=$codpedi 
                                            and clpe_cod_clpv=$clpv_cod and clpe_cod_prod='$prod_cod'";
                            $valprod = consulta_string($sqlp, 'conteo', $oIfx, 0);

                            if ($valprod == 0) {

                                // SUCURSA DEL CLPV
                                $sql = "select clpv_cod_sucu from saeclpv where 
                                                    clpv_cod_empr = $idempresa and
                                                    clpv_cod_clpv = $clpv_cod ";
                                $sucu_clpv = consulta_string_func($sql, 'clpv_cod_sucu', $oIfx, 0);

                                $sql = "select prod_nom_prod from saeprod where 
                                                        prod_cod_empr = $idempresa and
                                                        prod_cod_sucu = $idsucursal and
                                                        prod_cod_prod = '$prod_cod' ";
                                $prod_nom = consulta_string_func($sql, 'prod_nom_prod', $oIfx, 0);

                                // PROVEEDOR - PRODUCTO

                                $sqlcl = "insert into comercial.clpv_pedi (clpe_cod_sucu, clpe_cod_empr, clpe_nom_clpv, clpe_cod_clpv, 
                                        clpe_dir_clpv, clpe_tlf_clpv, clpe_ema_clpv,clpe_cod_pedi, clpe_cod_prod, clpe_pre_pac, clpe_pre_ult) 
                                        values($idsucursal, $idempresa,'$clpv_nom',$clpv_cod,'$direccion','$clpv_movil','$clpv_correo',$codpedi, '$prod_cod', $ppvpr_pre_pac, $ppvpr_pre_ult)";
                                $oIfx->QueryT($sqlcl);

                                // CORREO PROVEEDOR
                                if (!empty($clpv_correo)) {

                                    $sqlema = "select count(*) cont from saeemai where emai_cod_clpv=$clpv_cod and trim(emai_ema_emai)='$clpv_correo'";
                                    $ctrl_correo = consulta_string($sqlema, 'cont', $oIfxB, 0);
                                    ///VALIDACION EXISTENCIA DEL CORREO
                                    if ($ctrl_correo == 0) {
                                        $sql = "insert into saeemai ( emai_cod_empr,			emai_cod_sucu,			emai_cod_clpv,			emai_ema_emai )
                                                values( $idempresa,				$sucu_clpv,			    $clpv_cod,				'$clpv_correo' ) ";
                                        $oIfx->QueryT($sql);
                                    }
                                }

                                //CELULAR PROVEEDOR
                                if (!empty($clpv_movil)) {

                                    $sqlema = "select count(*) cont from saetlcp where tlcp_cod_clpv=$clpv_cod and trim(tlcp_tlf_tlcp)='$clpv_movil'";
                                    $ctrl_movil = consulta_string($sqlema, 'cont', $oIfxB, 0);
                                    ///VALIDACION EXISTENCIA DEL MOVIL
                                    if ($ctrl_movil == 0) {
                                        $sql = "insert into saetlcp ( tlcp_cod_empr,			tlcp_cod_sucu,			tlcp_cod_clpv,			tlcp_tlf_tlcp , tlcp_tip_ticp)
                                                values( $idempresa,				$sucu_clpv,			    $clpv_cod,				'$clpv_movil', 'C') ";
                                        $oIfx->QueryT($sql);
                                    }
                                }

                                ///VALIDACION INGRESO DE UN PROVEEDOR DESDE EL MODULO PRECIOS DE PROFORMA

                                if ($tipo == 2) {

                                    $sqlinv = "select i.invp_num_invp,i.id_inv_prof from comercial.inv_proforma i where  
                                            i.invp_cod_empr 	= $idempresa and
                                            i.invp_cod_sucu     = $idsucursal and 
                                            i.inv_cod_pedi 	= '$codpedi' and
                                            i.invp_cod_prod ='$prod_cod' ";

                                    $id_inv_prof = consulta_string_func($sqlinv, 'id_inv_prof', $oIfx, '0');
                                    $id_prof = consulta_string_func($sqlinv, 'invp_num_invp', $oIfx, '0');

                                    //SECUENCIAL

                                    $sqld = "select max(id_inv_dprof) as ultimo from comercial.inv_proforma_det";
                                    $id_dprof = consulta_string_func($sqld, 'ultimo', $oIfx, '0') + 1;

                                    $sqldet = "insert into comercial.inv_proforma_det ( id_inv_dprof, id_inv_prof,		invpd_cod_clpv,			invpd_nom_clpv,
                                                invpd_ema_clpv,	invpd_movil_clpv, invpd_costo_prod,		invpd_costo_real )
                                            values($id_dprof, $id_inv_prof,	$clpv_cod,		'$clpv_nom',
                                                '$clpv_correo', '$clpv_movil',  $ppvpr_pre_pac,			0 )";

                                    $oIfx->QueryT($sqldet);

                                    array_push($array_prove, $clpv_cod);
                                }
                            } else {
                                $oReturn->alert("El proveedor ya se encuentra ingresado");
                                $oReturn->script("jsRemoveWindowLoad();");
                            }
                        }

                        $i++;
                    } while ($oIfxA->SiguienteRegistro());
                }
            }
            $oIfxA->Free();
            $oIfx->QueryT('COMMIT WORK;');


            if (count($array_prove) > 0) {

                $array_prove = array_unique($array_prove, SORT_REGULAR);

                //CODIGO DE LA PROXIMA APROBACION
                $sql = "SELECT id from comercial.aprobaciones_compras 
            where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFPREC'";

                $cod_apro = consulta_string($sql, 'id', $oCon, 0);
                // Instanciamos la clase NotificacionesCompras
                $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $idsucursal, $cod_apro, $id_prof, '', '');

                foreach ($array_prove as $clpv) {
                    // Enviar correo a los provedores
                    $notifier->enviarCorreoProveedores($mail_solicitante, $clpv);
                    // Enviar WhatsApp a los proveedores
                    $notifier->enviarWhatsAppProveedores($clpv);
                }
            }

            $oReturn->script("alertSwal('Ingresado Correctamente', 'success');");
            $oReturn->script("jsRemoveWindowLoad();");
            $oReturn->script('cerrarModalProveedor();');
            $oReturn->script('modal_proformas( \'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ');');
        } else {
            $oReturn->script("alertSwal('Seleccione al menos un producto', 'warning');");
        }
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
/*MODAL PROVEEDORES*/
function form_proveedores($codpedi, $codapro,  $idempresa, $idsucursal, $tipo)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();


    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

    // BODEGA
    $sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa ";
    unset($array_bode);
    $array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

    $sHtml = '';

    $sHtml .= '<div class="row">

    <div class="col-xs-12 col-sm-12 col-md-2">
           <div class="form-group">
               <label class="control-label" for="fpag_edit">Codigo</label>
               <input type="text" id="proveedor" name="proveedor" class="form-control input-sm" readonly/>
           </div>
    </div>

    <div class="col-xs-12 col-sm-12 col-md-6">
            <label class="control-label" for="proveedor_nombre">Proveedor</label>
           <div class="form-group input-group">
               <input onkeyup="autocompletar_prove(event, 1 ); form1.proveedor_nombre.value=form1.proveedor_nombre.value.toUpperCase();" type="text" id="proveedor_nombre" name="proveedor_nombre" class="form-control" value="" placeholder="DIGITE NOMBRE Y PRESIONE ENTER O F4">
               <span class="input-group-addon primary" style="cursor: pointer;" onclick="searchClientes(1);"><i class="fa fa-search"></i></span>
           </div>
    </div>

     <div class="col-xs-12 col-sm-12 col-md-4">
            <label class="control-label" for="ruc_prove">Identificacion:</label>
           <div class="form-group input-group">
               <input onkeyup="autocompletar_prove(event, 2);" type="text" id="ruc_prove" name="ruc_prove" class="form-control" value="" placeholder="DIGITE Y PRESIONE ENTER O F4">
               <span class="input-group-addon primary" style="cursor: pointer;" onclick="searchClientes(2);"><i class="fa fa-search"></i></span>
           </div>
    </div>
    
    
    </div>';

    $sHtml .= '<div class="row">

    <div class="col-xs-12 col-sm-12 col-md-6">
           <div class="form-group">
               <label class="control-label" for="proveedor_correo">E-mail:</label>
               <input type="text" id="proveedor_correo" name="proveedor_correo" class="form-control input-sm" placeholder="CORREO DEL PROVEEDOR"/>
           </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-3">
           <div class="form-group">
               <label class="control-label" for="proveedor_movil">Celular:</label>
               <input type="text" id="proveedor_movil" name="proveedor_movil" class="form-control input-sm" placeholder="MOVIL DEL PROVEEDOR"/>
           </div>
    </div>

    <div class="col-xs-12 col-sm-12 col-md-3">
           <div class="form-group">
               <label class="control-label" for="ppvpr_pre_ult">Precio Ultimo:</label>
               <input type="number" id="ppvpr_pre_ult" name="ppvpr_pre_ult" class="form-control input-sm"/>
           </div>
    </div>

        <div class="col-xs-12 col-sm-12 col-md-3">
           <div class="form-group">
               <label class="control-label" for="ppvpr_pre_pac">Precio Pactado</label>
               <input type="number" id="ppvpr_pre_pac" name="ppvpr_pre_pac" class="form-control input-sm"/>
           </div>
    </div>


    </div>';

    ///DETALLE - SELECCION DE LOS PRODUCTOS
    $filprof = '';
    if ($tipo == 1) {
        $filprof = "and p.pedi_est_prof  = 'N'";
    } elseif ($tipo == 2) {
        $filprof = "and p.pedi_est_prof  = 'S'";
    }
    $table_op = '<table id="tbprodprov"class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-top: 20px;" align="center">';
    $table_op .= '<thead><tr>
                            <th >NÂ°</th>
                            <th >Bodega</th>
                            <th >Codigo</th>
                            <th >Producto</th>
                            <th >Detalle</th>
                            <th >Unidad</th>
                            <th >Cantidad Aprobada</th> 						
                            <th >Stock</th>
                            <th >Seleccionar<br><input id="ch_prod" type="checkbox"  checked onclick="marcar(this, \'checkprov_\');"></th>
                    </tr></thead><tbody>';
    $i = 1;

    //CONSULTAMOS LOS PRODUCTOS
    $sql = "SELECT  d.dped_cod_prod, d.dped_cod_bode, d.dped_cod_unid,d.dped_det_dped, 
					pr.prbo_uco_prod, pr.prbo_dis_prod, sum(d.dped_can_apro) as cant
					from saepedi p,   saedped d, saeprbo pr  where
					pr.prbo_cod_prod = d.dped_cod_prod and
					pr.prbo_cod_bode = d.dped_cod_bode and
					p.pedi_cod_pedi  = d.dped_cod_pedi and
					p.pedi_cod_empr  = $idempresa and
					p.pedi_cod_sucu  = $idsucursal and
					p.pedi_est_pedi  = '2'  and
					d.dped_cod_empr  = $idempresa and
					d.dped_cod_sucu  = $idsucursal and
					dped_ban_dped    = '0' and
					pr.prbo_cod_empr = $idempresa and
					pr.prbo_cod_sucu = $idsucursal  and
					p.pedi_cod_pedi = '$codpedi' 
                    $filprof 
					group by 1,2,3,4,5,6 order by 1 ";
    $tot_pedi    = 0;
    $tot_stock = 0;
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $prod_cod       = $oIfx->f('dped_cod_prod');
                $pedido         = $oIfx->f('cant');
                $unid_cod       = $oIfx->f('dped_cod_unid');
                $dped_det_dped  = $oIfx->f('dped_det_dped');
                $prbo_uco       = $oIfx->f('prbo_uco_prod');
                $prbo_dis       = $oIfx->f('prbo_dis_prod');
                $bode_cod       = $oIfx->f('dped_cod_bode');


                $bode_nom       = $array_bode[$bode_cod];
                $unid_nom       = $array_unid[$unid_cod];

                $campo = '<input type="checkbox" id="checkprov_' . $i . '" name="checkprov_' . $i . '"  value="S" checked/>';

                $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_sucu = $idsucursal and prod_cod_prod  = '$prod_cod' ";
                $prod_nom = consulta_string($sql, 'prod_nom_prod', $oIfxA, 0);

                if ($sClass == 'off') $sClass = 'on';
                else $sClass = 'off';
                $table_op .= '<tr >';
                $table_op .= '<td align="right">' . $i . '</td>';
                $table_op .= '<td align="left">' . $bode_nom . '</td>';
                $table_op .= '<td align="left">' . $prod_cod . '</td>';
                $table_op .= '<td align="left">' . $prod_nom . '</td>';
                $table_op .= '<td align="left">' . $dped_det_dped . '</td>';
                $table_op .= '<td align="left">' . $unid_nom . '</td>';
                $table_op .= '<td align="right">' . $pedido . '</td>';
                $table_op .= '<td align="right">' . $prbo_dis . '</td>';
                $table_op .= '<td align="center">' . $campo . '</td>';
                $table_op .= '</tr>';

                $i++;
                $tot_pedi += $pedido;
                $tot_stock += $prbo_dis;
            } while ($oIfx->SiguienteRegistro());
            $table_op .= '<tbody><tfoot><tr height="20" class="' . $sClass . '"
                                                       onMouseOver="javascript:this.className=\'link\';"
                                                       onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
            $table_op .= '<td align="right"></td>';
            $table_op .= '<td align="right"></td>';
            $table_op .= '<td align="right"></td>';
            $table_op .= '<td align="right"></td>';
            $table_op .= '<td align="right"></td>';
            $table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
            $table_op .= '<td align="right" class="fecha_letra">' . $tot_pedi . '</td>';
            $table_op .= '<td align="right" class="fecha_letra">' . $tot_stock . '</td>';
            $table_op .= '<td align="right"></td>';
            $table_op .= '</tr></tfoot>';
            $table_op .= '</table>';
        }
    }
    $oIfx->Free();

    $modal  = '<div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">PROVEEDOR PRODUCTOS</h4>
                        </div>
                        <div class="modal-body">
                        <div class="table-responsive">';
    $modal .= $sHtml;
    $modal .= $table_op;
    $modal .= '</div></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"  onClick="agrega_proveedor(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $tipo . ')">Ingresar</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';


    $oReturn->assign("ModalProveedor", "innerHTML", $modal);
    $oReturn->script("init('tbprodprov')");

    return $oReturn;
}
/**MODAL ACCIONES PROFORMAS */
function form_proforma_proveedores($codpedi, $codapro, $idempresa, $idsucursal)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo();
    $oConA->DSN = $DSN;
    $oConA->Conectar();


    $oConB = new Dbo();
    $oConB->DSN = $DSN;
    $oConB->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $sqla = "SELECT nombre, tipo_aprobacion from comercial.aprobaciones_compras 
                where empresa=$idempresa and estado='S' and id=$codapro";

    $titulo_apro = consulta_string($sqla, 'nombre', $oConA, '');
    $tipo_aprobacion = consulta_string($sqla, 'tipo_aprobacion', $oConA, '');

    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

    // BODEGA
    $sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa ";
    unset($array_bode);
    $array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

    $titulo = '';
    ///MODULO PROFORMA A PROVEEDORES

    if ($tipo_aprobacion == 'PROFPROV') {


        $botones = '<div class="row">

    <div class="col-xs-12 col-sm-12 col-md-5">
              
                  <div style="margin-left:25px;" class="btn btn-success btn-sm" onclick="modal_prove( \'' . $codpedi . '\',\'' . $codapro . '\', ' . $idempresa . ', ' . $idsucursal . ', 1);">
                  Agregar Proveedor
                    <span class="glyphicon glyphicon-plus"  </span>
                  </div>	
               
    </div>

    </div>';

        $table_op = '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 95%; margin-top: 20px;" align="center">';
        $table_op .= '<tr>
                            <td class="info">N.-</td>
                            <td class="info">Bodega</td>
                            <td class="info">Codigo</td>
                            <td class="info">Producto</td>
                            <td class="info">Detalle</td>
                            <td class="info">Unidad</td>
                            <td class="info">Cantidad Aprobada</td> 						
                            <td class="info">Stock</td>
                            <td class="info">Cantidad a Solicitar</td>
                            <td class="info" align="center">Proveedor</td>
                    </tr>';
        $i = 1;

        //CONSULTAMOS LOS PRODUCTOS
        $sql = "SELECT  d.dped_cod_prod, d.dped_cod_bode, d.dped_cod_unid, d.dped_det_dped,
					pr.prbo_uco_prod, pr.prbo_dis_prod, sum(d.dped_can_apro) as cant
					from saepedi p,   saedped d, saeprbo pr  where
					pr.prbo_cod_prod = d.dped_cod_prod and
					pr.prbo_cod_bode = d.dped_cod_bode and
					p.pedi_cod_pedi  = d.dped_cod_pedi and
					p.pedi_cod_empr  = $idempresa and
					p.pedi_cod_sucu  = $idsucursal and
					p.pedi_est_pedi  = '2'  and
					d.dped_cod_empr  = $idempresa and
					d.dped_cod_sucu  = $idsucursal and
					dped_ban_dped    = '0' and
					pr.prbo_cod_empr = $idempresa and
					pr.prbo_cod_sucu = $idsucursal  and
					p.pedi_cod_pedi = '$codpedi' and
					p.pedi_est_prof  = 'N' 
					group by 1,2,3,4,5,6 order by 1 ";
        $tot_pedi    = 0;
        $tot_stock = 0;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $prod_cod       = $oIfx->f('dped_cod_prod');
                    $pedido         = $oIfx->f('cant');
                    $unid_cod       = $oIfx->f('dped_cod_unid');
                    $dped_det_dped  = $oIfx->f('dped_det_dped');
                    $prbo_uco       = $oIfx->f('prbo_uco_prod');
                    $prbo_dis       = $oIfx->f('prbo_dis_prod');
                    $bode_cod       = $oIfx->f('dped_cod_bode');


                    $bode_nom       = $array_bode[$bode_cod];
                    $unid_nom       = $array_unid[$unid_cod];

                    $fu->AgregarCampoNumerico($i, 'Cantidad|left', false, $pedido, 80, 20);
                    $campo_cant = '';

                    $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_sucu = $idsucursal and prod_cod_prod  = '$prod_cod' ";
                    $prod_nom = consulta_string($sql, 'prod_nom_prod', $oIfxA, 0);

                    if ($sClass == 'off') $sClass = 'on';
                    else $sClass = 'off';
                    $table_op .= '<tr height="20" class="' . $sClass . '"
                                                       onMouseOver="javascript:this.className=\'link\';"
                                                       onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    $table_op .= '<td align="right">' . $i . '</td>';
                    $table_op .= '<td align="left">' . $bode_nom . '</td>';
                    $table_op .= '<td align="left">' . $prod_cod . '</td>';
                    $table_op .= '<td align="left">' . $prod_nom . '</td>';
                    $table_op .= '<td align="left">' . $dped_det_dped . '</td>';
                    $table_op .= '<td align="left">' . $unid_nom . '</td>';
                    $table_op .= '<td align="right">' . $pedido . '</td>';
                    $table_op .= '<td align="right">' . $prbo_dis . '</td>';
                    $table_op .= '<td align="right">' . $fu->ObjetoHtml($i) . '</td>';

                    /// Proveedor
                    $table_op .= '<td align="right" valign="top">';
                    $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
                    $table_op .= '<tr>
                                                       <td class="info" style="width: 6%;" >N</td>
                                                       <td class="info" style="width: 10%;" >Codigo</td>
                                                       <td class="info" style="width: 24%;">Proveedor</td>
                                                       <td class="info" style="width: 20%;">Correo</td>
                                                       <td class="info" style="width: 10%;">Celular</td>
                                                       <td class="info" style="width: 10%;">Costo</td>
                                                       <td class="info" style="width: 10%;">Seleccionar</td>
                                                       <td class="info" style="width: 10%;">Eliminar</td>
                                                       
                                               </tr>';

                    /*$sql = "select ppvpr_cod_clpv, ppvpr_pre_pac , clpv_nom_clpv
                                               from saeppvpr, saeclpv where
                                               clpv_cod_clpv 	= ppvpr_cod_clpv and
                                               clpv_cod_empr   = $idempresa and
                                               clpv_clopv_clpv = 'PV' and
                                               ppvpr_cod_empr 	= $idempresa and
                                               ppvpr_cod_prod 	= '$prod_cod'  ";*/

                    $sql = "SELECT clpe_cod_clpv, clpe_nom_clpv, clpe_ema_clpv, clpe_pre_pac, clpe_tlf_clpv from comercial.clpv_pedi 
                                   where clpe_cod_pedi=$codpedi and clpe_cod_empr=$idempresa and clpe_cod_sucu= $idsucursal
                                   and clpe_cod_prod='$prod_cod'";
                    //unset($array_clpv);
                    $x = 1;
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $ppvpr_cod_clpv = $oIfxA->f('clpe_cod_clpv');
                                $clpv_nom_clpv = $oIfxA->f('clpe_nom_clpv');
                                $ppvpr_pre_pac = $oIfxA->f('clpe_pre_pac');
                                $correo_clpv = $oIfxA->f('clpe_ema_clpv');
                                $movil_clpv = $oIfxA->f('clpe_tlf_clpv');


                                $campo = '<input type="checkbox" id="checkclpv_' . $i . '_' . $x . '" name="checkclpv_' . $i . '_' . $x . '"  value="S" checked/>';


                                $table_op .= '<tr height="20" class="' . $sClass . '"
                                                                   onMouseOver="javascript:this.className=\'link\';"
                                                                   onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                                $table_op .= '<td align="right">' . $x . '</td>';
                                $table_op .= '<td align="right">' . $ppvpr_cod_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $clpv_nom_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $correo_clpv . '</td>';
                                $table_op .= '<td align="center" >' . $movil_clpv . '</td>';
                                $table_op .= '<td align="right">' . $ppvpr_pre_pac . '</td>';
                                $table_op .= '<td align="center">' . $campo . '</td>';
                                $table_op .= '<td  style="width: 10%;" align="right" title="Eliminar Proveedor">
                                                                <div class="btn btn-danger btn-sm" 
                                                                        onclick="elimina_prove( ' . $idempresa . ', ' . $idsucursal . ', ' . $ppvpr_cod_clpv . ',\'' . $codpedi . '\',' . $codapro . ',\'' . $prod_cod . '\', 1);">
                                                                        <span class="glyphicon glyphicon-REMOVE"></span>
                                                                    </div>						
                                                            </td>';
                                $table_op .= '</tr>';
                                $x++;
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();

                    $table_op .= '</table></td>';
                    $table_op .= '</tr>';

                    $i++;
                    $tot_pedi += $pedido;
                    $tot_stock += $prbo_dis;
                } while ($oIfx->SiguienteRegistro());
                $table_op .= '<tr height="20" class="' . $sClass . '"
                                                       onMouseOver="javascript:this.className=\'link\';"
                                                       onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_pedi . '</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_stock . '</td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '</tr>';
                $table_op .= '</table>';
            }
        }
        $oIfx->Free();
        $evento = '<button type="button" class="btn btn-primary"  onClick="genera_proforma(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ')">Ingresar Proforma</button>';
    }
    //MODULO PRECIOS DE PROFORMA
    elseif ($tipo_aprobacion == 'PROFPREC_OLD') {

        $botones = '<div class="row">

        <div class="col-xs-12 col-sm-12 col-md-5">
                
                    <div style="margin-left:25px;" class="btn btn-success btn-sm" onclick="modal_prove( \'' . $codpedi . '\',\'' . $codapro . '\', ' . $idempresa . ', ' . $idsucursal . ', 2);">
                    Agregar Proveedor
                        <span class="glyphicon glyphicon-plus"  </span>
                    </div>	
                
        </div>

        </div>';

        $table_op = '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 98%;margin-top: 20px;" align="center">';
        $table_op .= '<tr>
                            <td class="info">N.-</td>
                            <td class="info">Bodega</td>
                            <td class="info">Codigo</td>
                            <td class="info" >Producto</td>
                            <td class="info">Detalle</td>
                            <td class="info">Unidad</td>
                            <td class="info">Pedido</td> 						
                            <td class="info">Stock</td>
                            <td class="info" align="center">Proveedor</td>
                            <td align="center"><input type="checkbox" onclick="marcar(this, \'_checkprec\');" checked align="center" /></td>
                            
                    </tr>';
        $i = 1;

        $sql = "select i.id_inv_prof, i.invp_num_invp ,
                    i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
                    i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.invp_det_prod
                    from comercial.inv_proforma i where
                    i.invp_cod_empr 	= $idempresa and
                    i.invp_cod_sucu 	= $idsucursal and
                    i.invp_esta_invp 	= 'N'  and
                    i.inv_cod_pedi  	= '$codpedi'
                    order by i.id_inv_prof; ";
        unset($array);
        $total       = 0;
        $tot_ret     = 0;
        $tot_pedi    = 0;
        $tot_stock = 0;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $prod_cod       = $oCon->f('invp_cod_prod');
                    $bode_cod       = $oCon->f('invp_cod_bode');
                    $unid_cod       = $oCon->f('invp_unid_cod');
                    $prbo_dis       = $oCon->f('invp_cant_stock');
                    $pedido         = $oCon->f('invp_cant_real');
                    $prod_nom       = $oCon->f('invp_nom_prod');
                    $id_inv_prof    = $oCon->f('id_inv_prof');
                    $invp_det_prod  = $oCon->f('invp_det_prod');
                    $secu_prof      = $oCon->f('invp_num_invp');

                    $bode_nom       = $array_bode[$bode_cod];
                    $unid_nom       = $array_unid[$unid_cod];

                    $fu->AgregarCampoNumerico($i, 'Cantidad|left', false, $pedido, 80, 20, true);


                    if ($sClass == 'off') $sClass = 'on';
                    else $sClass = 'off';
                    $table_op .= '<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    $table_op .= '<td align="right">' . $i . '</td>';
                    $table_op .= '<td align="left">' . $bode_nom . '</td>';
                    $table_op .= '<td align="left">' . $prod_cod . '</td>';
                    $table_op .= '<td align="left">' . $prod_nom . '</td>';
                    $table_op .= '<td align="left">' . $invp_det_prod . '</td>';
                    $table_op .= '<td align="left">' . $unid_nom . '</td>';
                    $table_op .= '<td align="right">' . $pedido . '</td>';
                    $table_op .= '<td align="right">' . $prbo_dis . '</td>';

                    /// Proveedor
                    $table_op .= '<td align="right" valign="top">';
                    $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
                    $table_op .= '<tr>
                                            <td class="info" style="width: 6%;" >N.-</td>
                                            <td class="info" style="width: 6%;" >Codigo</td>
                                            <td class="info" style="width: 20%;">Proveedor</td>
                                            <td class="info" style="width: 20%;">Correo</td>
                                            <td class="info" style="width: 10%;">Celular</td>
                                            <td class="info" style="width: 10%;">Costo</td>
                                            <td class="info" style="width: 10%;">Costo OC</td>
                                            <td class="info" style="width: 10%;">Cantidad OC</td>
                                            <td class="info" style="width: 10%;" align="right">Adjunto</td>
                                            <td class="info" style="width: 5%;" align="right"></td>
                                            <td class="info" style="width: 10%;">Eliminar</td>
                                    </tr>';

                    $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                    d.invpd_ema_clpv, d.invpd_movil_clpv, d.invpd_costo_prod
                                    from comercial.inv_proforma_det d where
                                    d.id_inv_prof = $id_inv_prof
                                    order by d.invpd_costo_prod  ";
                    //$oReturn->alert($sql);
                    unset($array_clpv);
                    $x = 1;
                    if ($oConA->Query($sql)) {
                        if ($oConA->NumFilas() > 0) {
                            do {
                                $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                                $ppvpr_pre_pac     = $oConA->f('invpd_costo_prod');
                                $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                                $correo_clpv    = $oConA->f('invpd_ema_clpv');
                                $id_inv_dprof   = $oConA->f('id_inv_dprof');
                                $movil_clpv =      $oConA->f('invpd_movil_clpv');

                                $serial            = '';
                                $serial         = $i . '-' . $ppvpr_cod_clpv;

                                $fu->AgregarCampoCheck($serial . '_checkprec',  'S/N|left', false, 'N');
                                $fu->AgregarCampoNumerico($serial . '_c', 'Costo|left', false, $ppvpr_pre_pac, 60, 20);
                                $fu->AgregarCampoNumerico($serial . '_ca', 'Cantidad|left', false, $pedido, 60, 20);
                                $fu->AgregarCampoArchivo($serial . '_adj', 'Archivo|left', false, '', 100, 100, '');

                                //ULTIMO COSTO DE PRODCUTO

                                $sqlu = "select bode_nom_bode, prod_cod_prod,prod_nom_prod, prod_des_prod,   medi_des_medi, 
                                                prbo_smi_prod, prbo_sma_prod, prbo_ped_prod, prbo_dis_prod,
                                                prbo_fec_ucom, prbo_pco_prod, prbo_fec_uven, prbo_pve_prod, unid_sigl_unid, prbo_uco_prod
                                                from saeprod, saeprbo, saebode, saemedi, saeunid
                                                where prod_cod_prod = prbo_cod_prod
                                                and prod_cod_empr = prbo_cod_empr
                                                and prod_cod_sucu = prbo_cod_sucu
                                                and prbo_cod_bode = bode_cod_bode
                                                and prbo_cod_empr = bode_cod_empr
                                                and prod_cod_medi = medi_cod_medi
                                                and prod_cod_empr = medi_cod_empr
                                                and prbo_cod_unid = unid_cod_unid
                                                and prbo_cod_empr = unid_cod_empr
                                                and prod_cod_empr = $idempresa
                                                and prod_cod_sucu = $idsucursal
                                                and prbo_cod_bode = $bode_cod
                                                and prod_cod_prod = '$prod_cod' ";
                                $ultimo_costo = consulta_string($sqlu, 'prbo_uco_prod', $oIfxA, 0);


                                //if($x==1){
                                $fu->cCampos[$serial . '_checkprec']->xValor = 'S';
                                //}

                                //$array_clpv [] = array( $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof );

                                $table_op .= '<tr height="20" class="' . $sClass . '"
                                                        onMouseOver="javascript:this.className=\'link\';"
                                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                                $table_op .= '<td align="right">' . $x . '</td>';
                                $table_op .= '<td align="right">' . $ppvpr_cod_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $clpv_nom_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $correo_clpv . '</td>';
                                $table_op .= '<td align="center" >' . $movil_clpv . '</td>';
                                $table_op .= '<td align="right">' . $ultimo_costo . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_c') . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_ca') . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_adj') . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_checkprec') . '</td>';
                                $table_op .= '<td  style="width: 10%;" align="right" title="Eliminar Proveedor">
                                    <div class="btn btn-danger btn-sm" 
                                            onclick="elimina_prove( ' . $idempresa . ', ' . $idsucursal . ', ' . $ppvpr_cod_clpv . ',\'' . $codpedi . '\',' . $codapro . ',\'' . $prod_cod . '\', 2);">
                                            <span class="glyphicon glyphicon-REMOVE"></span>
                                        </div>						
                                </td>';
                                $table_op .= '</tr>';
                                $x++;
                            } while ($oConA->SiguienteRegistro());
                        }
                    }
                    $oConA->Free();

                    //$array [] = array( $prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv );

                    $table_op .= '</table></td>';
                    $table_op .= '</tr>';

                    $i++;
                    $tot_pedi += $pedido;
                    $tot_stock += $prbo_dis;
                } while ($oCon->SiguienteRegistro());
                $table_op .= '<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_pedi . '</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_stock . '</td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '</tr>';
                $table_op .= '</table>';
            } else {
                $table_op = '<span class="fecha_letra">Sin Datos...</span>';
            }
        }
        $oCon->Free();

        $titulo = 'Proforma NÂ°: ' . $secu_prof;

        $evento = '<button type="button" class="btn btn-primary"  onClick="ingresa_proforma(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ')">Ingresar Precios</button>';
    }
    //MODULO PRECIOS DE PROFORMA NUEVO
    elseif ($tipo_aprobacion == 'PROFPREC') {

        $cod_pedi = $codpedi;

        $i = 1;
        $j = 1;
        $l = 1;
        $p = 1;

        $sql = "select empr_iva_empr from saeempr where empr_cod_empr=$idempresa";
        $empr_iva_empr = round(consulta_string($sql, 'empr_iva_empr', $oIfxA, 0));

        //TABLA DE PROVEEDORES
        $table_op = '<br><table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
        $table_op .= '<tr><td class="info" align="center">LISTA DE PROVEEDORES</td></tr>';
        $table_op .= '<tr>';
        $table_op .= '<td align="right" valign="top">';
        $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';

        $table_op .= '<tr>';
        $table_op .= '<td class="info" align="center" rowspan="3" colspan="4"  >Presupuesto: <br>
        Aprobacion sobre incremento del presupuesto
        </td>';

        $sqlprove = "SELECT
                        invpd_cod_clpv,
                        invpd_nom_clpv,
                        invpd_ema_clpv,
                        invpd_movil_clpv,
                        invp_num_invp
                    FROM
                        comercial.inv_proforma i,
                        comercial.inv_proforma_det d 
                    WHERE
                        i.id_inv_prof = d.id_inv_prof 
                        AND i.inv_cod_pedi =$cod_pedi 
                        AND i.invp_cod_sucu =$idsucursal 
                    GROUP BY
                        1,
                        2,
                        3,
                        4,
                        5
                    ORDER BY
                        1";
        unset($array_clpv);
        if ($oCon->Query($sqlprove)) {
            if ($oCon->NumFilas() > 0) {
                do {

                    $proforma       = $oCon->f('invp_num_invp');
                    $ppvpr_cod_clpv = $oCon->f('invpd_cod_clpv');
                    $clpv_nom_clpv = $oCon->f('invpd_nom_clpv');
                    $correo_clpv = $oCon->f('invpd_ema_clpv');

                    $serial            = '';
                    $serial         = $i . '-' . $ppvpr_cod_clpv;

                    $fu->AgregarCampoCheck($serial,  'S/N|left', false, 'N');

                    $fu->cCampos[$serial]->xValor = 'S';

                    $array_clpv[] = array($ppvpr_cod_clpv, 0, $clpv_nom_clpv, $serial, $correo_clpv);

                    $table_op .= '<td class="info" align="center"   >Proveedor No. ' . $i . '</td>';
                    $table_op .= '<td></td>';
                    $table_op .= '<td></td>';
                    $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial) . '</td>';



                    $i++;
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $table_op .= '</tr>';

        //NOMBRE
        $table_op .= '<tr>';
        if ($oConA->Query($sqlprove)) {
            if ($oConA->NumFilas() > 0) {
                do {
                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                    $nprov = $oConA->f('invpd_nom_clpv');

                    $table_op .= '<td  colspan="4"><strong>Nombre:</strong> ' . $nprov . '</td>';
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();

        $table_op .= '</tr>';
        //PROFORMA
        $table_op .= '<tr>';
        if ($oConA->Query($sqlprove)) {
            if ($oConA->NumFilas() > 0) {
                do {
                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                    $table_op .= '<td  colspan="4"><strong>Proforma:</strong> ' . $proforma . '</td>';
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();
        $table_op .= '</tr>';

        //DIRECCION
        $table_op .= '<tr>';
        $table_op .= '<td align="center" colspan="2" rowspan="3">SI</td>';
        $table_op .= '<td align="center" rowspan="3">PRY/PRESUPUESTO</td>';
        $table_op .= '<td align="center" rowspan="3">NO</td>';
        if ($oConA->Query($sqlprove)) {
            if ($oConA->NumFilas() > 0) {
                do {
                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                    $sql = "select clpe_dir_clpv from comercial.clpv_pedi where clpe_cod_clpv=$ppvpr_cod_clpv and clpe_cod_pedi=$cod_pedi";
                    $dir = consulta_string($sql, 'clpe_dir_clpv', $oConB, '');

                    $table_op .= '<td  colspan="4"><strong>Direccion:</strong>' . $dir . ' </td>';
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();
        $table_op .= '</tr>';
        //E-MAIL
        $table_op .= '<tr>';

        if ($oConA->Query($sqlprove)) {
            if ($oConA->NumFilas() > 0) {
                do {

                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                    $correo_clpv = $oConA->f('invpd_ema_clpv');

                    $table_op .= '<td colspan="4" ><strong>e-mail:</strong> ' . $correo_clpv . '</td>';
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();
        $table_op .= '</tr>';

        //CONTACTO/CELULAR
        $table_op .= '<tr>';
        if ($oConA->Query($sqlprove)) {
            if ($oConA->NumFilas() > 0) {
                do {

                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                    $telf = $oConA->f('invpd_movil_clpv');

                    $table_op .= '<td colspan="4"><strong>Contacto/Celular:</strong>' . $telf . '</td>';
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();
        $table_op .= '</tr>';

        $table_op .= '<tr>';
        $table_op .= '<th align="center">Id</th>';
        $table_op .= '<th align="center">Cantidad</th>';
        $table_op .= '<th align="center" >Descripcion</th>';
        $table_op .= '<th align="center" >Medida</th>';

        if ($oConB->Query($sqlprove)) {
            if ($oConB->NumFilas() > 0) {
                do {
                    $table_op .= '<th ><strong>Oferta</strong></th>
                                                <th ><strong>Cantidad</strong></th>
                                                <th ><strong>Valor Unitario</strong></th>
                                                <th ><strong>Precio Total</strong></th>';
                } while ($oConB->SiguienteRegistro());
            }
        }
        $oConB->Free();

        $table_op .= '</tr>';


        /**CONSULTA PRECIOS DE LOS PRODUCTOS */
        //unset($array_prod);
        $m = 1;
        //DETALLE DE LOS PRODUCTOS
        $sprof = "SELECT invp_nom_prod,invp_cant_pedi, invp_unid_cod,invp_cod_prod,id_inv_prof from comercial.inv_proforma 
                    where invp_num_invp = '$proforma' and invp_cod_sucu=$idsucursal and inv_cod_pedi=$cod_pedi order by id_inv_prof";

        if ($oConA->Query($sprof)) {
            if ($oConA->NumFilas() > 0) {
                do {
                    $table_op .= '<tr>';
                    $prod_nom = $oConA->f('invp_nom_prod');
                    $cod_prod = $oConA->f('invp_cod_prod');

                    $cantidad = $oConA->f('invp_cant_pedi');

                    $idinv = $oConA->f('id_inv_prof');

                    //UNIDAD

                    $unid_cod = $oConA->f('invp_unid_cod');
                    $unid_nom = $array_unid[$unid_cod];

                    //CENTRO DE COSTO(PROYECTO)

                    $sqlccos = "SELECT dped_can_ped,dped_cod_ccos from saedped 
                                where dped_cod_pedi=$cod_pedi and dped_cod_sucu=$idsucursal 
                                and dped_cod_prod='$cod_prod' and dped_cod_dped not in(select dped_cod_dped from saedped where dped_est_dped ='1')";


                    $cos = consulta_string_func($sqlccos, 'dped_cod_ccos', $oIfxA, '');
                    $cant = consulta_string_func($sqlccos, 'dped_can_ped', $oIfxA, '');

                    $icant = intval($cant);


                    $sqlproy = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                                from saeccosn where ccosn_cod_ccosn= '$cos'";

                    if ($oIfxA->Query($sqlproy)) {
                        if ($oIfxA->NumFilas() > 0) {

                            $ccos = $oIfxA->f('ccosn_nom_ccosn');
                        }
                    }
                    $oIfxA->Free();

                    $serialprod         = $j;

                    $fu->AgregarCampoNumerico($serialprod . '_cant', 'Cantidad|left', false, $cantidad, 60, 20);
                    $fu->AgregarComandoAlPonerEnfoque($serialprod . '_cant', 'this.blur()');

                    $table_op .= '<td align="center">' . $m . '</td>';
                    $table_op .= '<td align="center">' . $fu->ObjetoHtml($serialprod . '_cant') . '</td>';
                    $table_op .= '<td align="center" >' . $ccos . '</td>';
                    $table_op .= '<td align="center" >' . $unid_nom . '</td>';

                    if ($oConB->Query($sqlprove)) {
                        if ($oConB->NumFilas() > 0) {
                            do {

                                $ppvpr_cod_clpv = $oConB->f('invpd_cod_clpv');

                                $sqprod = "SELECT id_inv_dprof,invpd_costo_prod,invp_ptotal_prof, invpd_cant_dmov
                                            from comercial.inv_proforma_det where invpd_cod_clpv=$ppvpr_cod_clpv and id_inv_prof= $idinv ";

                                $var = consulta_string_func($sqprod, 'invpd_costo_prod', $oIfxA, 0);
                                $pt = consulta_string_func($sqprod, 'invp_ptotal_prof', $oIfxA, 0);
                                $codprof = consulta_string_func($sqprod, 'id_inv_dprof', $oIfxA, 0);
                                $cant_dmov = consulta_string_func($sqprod, 'invpd_cant_dmov', $oIfxA, 0);



                                if (empty($var)) {

                                    $var = 0;
                                }
                                if (empty($pt)) {
                                    $pt = 0;
                                }

                                $serialprod            = '';
                                $serialprod         = $j . '-' . $ppvpr_cod_clpv;
                                $fu->AgregarCampoNumerico($codprof . '_vu', 'Vunitario|left', false, $var, 60, 20);
                                $fu->AgregarComandoAlEscribir($codprof . '_vu', 'totales(' . $codprof . ',' . $j . ',' . $cod_pedi . ',' . $ppvpr_cod_clpv . ',' . $idsucursal . ')');
                                $fu->AgregarComandoAlPonerEnfoque($codprof . '_vu', 'totales(' . $codprof . ',' . $j . ',' . $cod_pedi . ',' . $ppvpr_cod_clpv . ',' . $idsucursal . ')');
                                $fu->AgregarCampoNumerico($codprof . '_cantd', 'Cantidad|left', false, $cant_dmov, 60, 20);
                                $fu->AgregarComandoAlEscribir($codprof . '_cantd', 'totales(' . $codprof . ',' . $j . ',' . $cod_pedi . ',' . $ppvpr_cod_clpv . ',' . $idsucursal . ')');


                                $fu->AgregarCampoNumerico($codprof . '_pt', 'Ptotal|left', false, $pt, 60, 20);
                                $fu->AgregarComandoAlPonerEnfoque($codprof . '_pt', 'this.blur()');




                                //VALIDACION PRODUCTO - PROVEDOR

                                $color = '#7aef62';

                                if ($codprof == 0) {
                                    $color = '';
                                    $fu->AgregarComandoAlPonerEnfoque($codprof . '_vu', 'this.blur()');
                                    $fu->AgregarComandoAlPonerEnfoque($codprof . '_cantd', 'this.blur()');
                                }

                                //FIN VALIDAICON PRODUCTO - PROVEDOR



                                $table_op .= '	
                                        <td bgcolor="' . $color . '">' . $prod_nom . '</td>
                                        <td align="center">' . $fu->ObjetoHtml($codprof . '_cantd') . '</td>                                        
                                        <td align="center">' . $fu->ObjetoHtml($codprof . '_vu') . '</td>
                                        <td align="center">' . $fu->ObjetoHtml($codprof . '_pt') . '</td>';


                                //$array_prod [] = array($serialprod);

                            } while ($oConB->SiguienteRegistro());

                            $m++;
                        }
                    }
                    $oConB->Free();
                    $table_op .= '</tr>';

                    $j++;
                } while ($oConA->SiguienteRegistro());
            }
        }
        $oConA->Free();


        ///SUB TOTAL

        $arraypro = array("Subtotal", "Descuento %", "IVA $empr_iva_empr%", "Otros Cargos", "Total");
        //$k=1;
        //unset($array_pro);
        foreach ($arraypro as $val) {

            $table_op .= '<tr>';
            $table_op .= '<td colspan="4" align="center"></td>';

            if ($oConA->Query($sqlprove)) {
                if ($oConA->NumFilas() > 0) {
                    do {

                        $clpvcod = $oConA->f('invpd_cod_clpv');

                        $serialtot = '';
                        $serialtot = $clpvcod;

                        $sqlpro = "SELECT  d.invp_oval_prof, d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,
                                                   d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,
                                                   d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, 
                                                   d.invpd_cod_clpv, d.invpd_nom_clpv,
                                                   d.invpd_ema_clpv, d.invpd_costo_prod 
                                                   from comercial.inv_proforma_det d, comercial.inv_proforma i 
                                                   where
                                                   d.id_inv_prof=i.id_inv_prof and
                                                   i.inv_cod_pedi=$cod_pedi and 
                                                   i.invp_cod_sucu=$idsucursal 
                                                   and d.invpd_cod_clpv=$clpvcod ";
                        $table_op .= '<td></td>';
                        $table_op .= '<td></td>';

                        if ($val == "Subtotal") {

                            $sub = consulta_string($sqlpro, 'invp_subt_prof', $oConB, '');

                            if (empty($sub)) {
                                $sub = 0;
                            }
                            $fu->AgregarCampoNumerico($serialtot . '_st', '' . $val . '|left', false, $sub, 60, 20);
                            $fu->AgregarComandoAlPonerEnfoque($serialtot . '_st', 'this.blur()');

                            $table_op .= '<td coslpan="5" align="right">' . $val . '</td>
                                                <td align="center">' . $fu->ObjetoHtml($serialtot . '_st') . '</td>';
                        } elseif ($val == "Descuento %") {

                            $des = consulta_string($sqlpro, 'invp_desc_prof', $oConB, '');

                            if (empty($des)) {
                                $des = 0;
                            }

                            $fu->AgregarCampoNumerico($serialtot . '_des', '' . $val . '|left', false, $des, 60, 20);
                            $fu->AgregarComandoAlEscribir($serialtot . '_des', 'sdes(' . $clpvcod . ')');


                            $table_op .= '<td align="right">' . $val . '</td>
                                                <td align="center">' . $fu->ObjetoHtml($serialtot . '_des') . '</td>';
                        } elseif ($val == "IVA $empr_iva_empr%") {


                            $codprod = consulta_string($sqlpro, 'invp_cod_prod', $oConB, '');

                            $iva = consulta_string($sqlpro, 'invp_iva_prof', $oConB, 0);


                            $fu->AgregarCampoNumerico($serialtot . '_iv', '' . $val . '|left', false, $iva, 60, 20);
                            $fu->AgregarComandoAlPonerEnfoque($serialtot . '_iv', 'siva(' . $clpvcod . ' )');
                            $fu->AgregarComandoAlEscribir($serialtot . '_iv', 'siva(' . $clpvcod . ')');
                            $table_op .= '<td align="right">' . $val . '</td>
                                                <td align="center">' . $fu->ObjetoHtml($serialtot . '_iv') . '</td>';
                        } elseif ($val == "Otros Cargos") {

                            $carg = consulta_string($sqlpro, 'invp_oval_prof', $oConB, 0);

                            $fu->AgregarCampoNumerico($serialtot . '_ocar', '' . $val . '|left', false, $carg, 60, 20);
                            $fu->AgregarComandoAlPonerEnfoque($serialtot . '_ocar', 'siva(' . $clpvcod . ')');
                            $fu->AgregarComandoAlEscribir($serialtot . '_ocar', 'siva(' . $clpvcod . ')');



                            $table_op .= '<td align="right">' . $val . '</td>
                                                <td align="center">' . $fu->ObjetoHtml($serialtot . '_ocar') . '</td>';
                        } else {

                            $total = consulta_string($sqlpro, 'invp_total_prof', $oConB, '');
                            if (empty($total)) {
                                $total = 0;
                            }

                            $fu->AgregarCampoNumerico($serialtot . '_tot', '' . $val . '|left', false, $total, 60, 20);
                            $fu->AgregarComandoAlPonerEnfoque($serialtot . '_tot', 'this.blur()');
                            $table_op .= '<td align="right">' . $val . '</td>
                                                <td align="center">' . $fu->ObjetoHtml($serialtot . '_tot') . '</td>';
                        }

                        //$array_pro [] = array($serialtot);

                    } while ($oConA->SiguienteRegistro());
                }
            }
            $table_op .= '</tr>';
        } //CIERRE FOR SUBTOTALES


        //TERMINOS Y CONDICIONES

        $table_op .= '<tr><td colspan="4" align="center" style="background-color: red;"><font color ="#ffffff">Terminos y Condiciones</font></td>
                    
                </tr>';

        $arrayter = array("Oferta Completa %", "Nro Contrato", "Cotizaciones Comprobadas", "Servicios Adicionales", "Experiencia Pasada", "Forma de Pago", "Validez de la oferta", "Tiempo de entrega", "Lugar de entrega", "Vendedor", "* Adjuntos");

        //unset($array_ter);
        foreach ($arrayter as $val) {

            $table_op .= '<tr>';
            $table_op .= '<td colspan="4" align="center"></td>';

            if ($oConA->Query($sqlprove)) {
                if ($oConA->NumFilas() > 0) {
                    do {

                        $clpvcod = $oConA->f('invpd_cod_clpv');

                        $sqlpro = "SELECT  d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,
                                                   d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,
                                                   d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
                                                   d.invpd_ema_clpv, d.invpd_costo_prod, d.invpd_lug_entr, d.invpd_vend_prof, d.invpd_ctr_prov 
                                                   from comercial.inv_proforma_det d, comercial.inv_proforma i 
                                                   where
                                                   d.id_inv_prof=i.id_inv_prof and
                                                   i.inv_cod_pedi=$cod_pedi and 
                                                   i.invp_cod_sucu=$idsucursal and
                                                   d.invpd_cod_clpv=$clpvcod";


                        $serialter = '';
                        $serialter = $clpvcod;
                        if ($val == "Oferta Completa %") {

                            $ocom = intval(consulta_string($sqlpro, 'invp_ofcom_prof', $oConB, 0));

                            if (empty($ocom)) {
                                $ocom = '';
                            }

                            $fu->AgregarCampoNumerico($serialter . '_ofcomp', '' . $val . '|left', false, $ocom, 180, 20);
                            $table_op .= '<td align="center" colspan="2">' . $fu->ObjetoHtmlLBL($serialter . '_ofcomp') . '</td>';
                            $table_op .= '<td  align="center"colspan="2" > ' . $fu->ObjetoHtml($serialter . '_ofcomp') . '</td>';
                        } elseif ($val == "Nro Contrato") {

                            $contr = consulta_string($sqlpro, 'invpd_ctr_prov', $oConB, 0);

                            $optionContrato = '';
                            $sql = "SELECT id, ctr_num_ctr, ctr_fcad_ctr from comercial.contratos_compras 
                                    where ctr_cod_empr=$idempresa and ctr_cod_clpv=$clpvcod and ctr_est_ctr = 'S'
                                    order by ctr_fec_ctr desc";
                            if ($oConB->Query($sql)) {
                                if ($oConB->NumFilas() > 0) {
                                    do {
                                        if ($contr == $oConB->f('id')) {
                                            $optionContrato .= '<option value="' . $oConB->f('id') . '" selected="selected">' . $oConB->f('ctr_fcad_ctr') . '-' . $oConB->f('ctr_num_ctr') . '</option>';
                                        } else {
                                            $optionContrato .= '<option value="' . $oConB->f('id') . '">' . $oConB->f('ctr_fcad_ctr') . '-' . $oConB->f('ctr_num_ctr') . '</option>';
                                        }
                                    } while ($oConB->SiguienteRegistro());
                                }
                            }



                            $table_op .= '<td colspan="2" align="center"><label class="control-label" for="' . $serialter . '_contr">Nro Contrato:</label></td>';
                            $table_op .= '<td colspan="2" align="center" > 
                                                    <select id="' . $serialter . '_contr" name="' . $serialter . '_contr" class="form-control select2" style="width: 100%;" >
                                                        <option value="">Seleccione una opcion..</option>
                                                        ' . $optionContrato . '
                                                    </select>
                                                </td>';
                        } elseif ($val == "Cotizaciones Comprobadas") {

                            $ctz = consulta_string($sqlpro, 'invp_ctzcom_prof', $oConB, '');
                            if (empty($ctz)) {
                                $ctz = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_ctz', '' . $val . '|left', false, $ctz, 180, 20);



                            $eti = $serialter . '_ctz';
                            $table_op .= '<td  colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_ctz') . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2"><textarea class="form-control input-sm" name="' . $eti . '" id="' . $eti . '" rows="5" cols="20" required="true">' . $ctz . '</textarea></td>';
                        } elseif ($val == "Servicios Adicionales") {

                            $sad = consulta_string($sqlpro, 'invp_sadc_prof', $oConB, '');
                            if (empty($sad)) {
                                $sad = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_sad', '' . $val . '|left', false, $sad, 180, 20);

                            $eti = $serialter . '_sad';
                            $table_op .= '<td  colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_sad') . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2"><textarea class="form-control input-sm" name="' . $eti . '" id="' . $eti . '" rows="5" cols="20" required>' . $sad . '</textarea></td>';
                        } elseif ($val == "Experiencia Pasada") {

                            $exp = consulta_string($sqlpro, 'invp_exps_prof', $oConB, '');
                            if (empty($exp)) {
                                $exp = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_exp', '' . $val . '|left', false, $exp, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_exp', ' mayus(this)');
                            $table_op .= '<td colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_exp') . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2">' . $fu->ObjetoHtml($serialter . '_exp') . '</td>';
                        } elseif ($val == "Forma de Pago") {

                            $fpag = consulta_string($sqlpro, 'invpd_fpago_prof', $oConB, '');
                            if (empty($fpag)) {
                                $fpag = '';
                            }

                            $fu->AgregarCampoTexto($serialter . '_fpag', '' . $val . '|left', false, $fpag, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_fpag', ' mayus(this)');
                            $table_op .= '<td  colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_fpag') . '</td>';

                            $table_op .= '
                                <td align="center"colspan="2">' . $fu->ObjetoHtml($serialter . '_fpag') . '</td>';
                        } elseif ($val == "Validez de la oferta") {

                            //$vorf=utf8_decode($oConB->f('invpd_vofer_prof'));
                            $vorf = consulta_string($sqlpro, 'invpd_vofer_prof', $oConB, '');
                            if (empty($vorf)) {
                                $vorf = '';
                            }

                            $fu->AgregarCampoTexto($serialter . '_vorf', '' . $val . '|left', false, $vorf, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_vorf', ' mayus(this)');

                            $table_op .= '<td  colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_vorf') . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2">' . $fu->ObjetoHtml($serialter . '_vorf') . '</td>';
                        } elseif ($val == "Tiempo de entrega") {
                            //$pz=utf8_decode($oConB->f('invpd_tent_prof'));
                            $pz = consulta_string($sqlpro, 'invpd_tent_prof', $oConB, '');
                            if (empty($pz)) {
                                $pz = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_pz', '' . $val . '|left', false, $pz, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_pz', ' mayus(this)');

                            //$fu->AgregarCampoNumerico($serialter.'_pz', ''.$val.'|left', true,$pz, 60, 20);
                            $table_op .= '<td colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_pz') . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2">' . $fu->ObjetoHtml($serialter . '_pz') . '</td>';
                        } elseif ($val == "Lugar de entrega") {
                            $lug = consulta_string($sqlpro, 'invpd_lug_entr', $oConB, '');
                            if (empty($lug)) {
                                $lug = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_lug', '' . $val . '|left', false, $lug, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_lug', ' mayus(this)');

                            $table_op .= '<td colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_lug') . '</td>';
                            $table_op .= '<td align="center" colspan="2">' . $fu->ObjetoHtml($serialter . '_lug') . '</td>';
                        } elseif ($val == "Vendedor") {
                            $vend = consulta_string($sqlpro, 'invpd_vend_prof', $oConB, '');
                            if (empty($vend)) {
                                $vend = '';
                            }
                            $fu->AgregarCampoTexto($serialter . '_vend', '' . $val . '|left', false, $vend, 180, 200);
                            $fu->AgregarComandoAlEscribir($serialter . '_vend', ' mayus(this)');

                            $table_op .= '<td colspan="2" align="center">' . $fu->ObjetoHtmlLBL($serialter . '_vend') . '</td>';
                            $table_op .= '<td align="center" colspan="2">' . $fu->ObjetoHtml($serialter . '_vend') . '</td>';
                        } elseif ($val == "* Adjuntos") {

                            $adj = consulta_string($sqlpro, 'invpd_adjunto', $oConB, '');

                            if (empty($adj)) {
                                $adj = '<strong><font color="red">SIN CARGAR</font></strong>';
                            } else {
                                $adj = '<strong><font color="blue">CARGADOS</font></strong>
                                        <br>
                                        <div class="btn btn-primary btn-sm" onclick="adjuntos_prove( \'' . $adj . '\');">
                                            <span class="glyphicon glyphicon-paperclip"  </span>
                                        </div>';
                            }

                            $fu->AgregarCampoArchivo($serialter . '_adj', '' . $val . '|left', false, '', 100, 100);
                            $table_op .= '<td colspan="2" align="center">' . $val . '</td>';
                            $table_op .= '
                                <td align="center" colspan="2">' . $adj . '<br>' . $fu->ObjetoHtml($serialter . '_adj') . '</td>';
                        }
                        //$array_ter [] = array($serialter);
                    } while ($oConA->SiguienteRegistro());
                }
            }

            $table_op .= '</tr>';
            $j++;
        }
        $oConA->Free();
        $table_op .= '</table></td>';
        $table_op .= '</tr>';

        //$array [] = array( $cod_pedi, $array_clpv,$array_ter,$array_pro,$array_prod);
        $table_op .= '</table>';


        $botones = '<div class="btn btn-primary btn-sm" onclick="guardar_proforma( \'' . $codpedi . '\',' . $idempresa . ', ' . $idsucursal . ', \'' . $proforma . '\',\'' . $codapro . '\')">
                            <span class="glyphicon glyphicon-floppy-disk"></span>
                            Guardar
                            </div>										
                            
                            <div class="btn btn-info btn-sm" onclick="javascript:salvar( \'' . $codpedi . '\',' . $idempresa . ', ' . $idsucursal . ', \'' . $proforma . '\',\'' . $codapro . '\');" >
                                <span class="glyphicon glyphicon-saved"></span>
                                Salvar
                            </div>
                            <div class="btn btn-success btn-sm" onclick="modal_prove( \'' . $codpedi . '\',\'' . $codapro . '\', ' . $idempresa . ', ' . $idsucursal . ', 2);">
                                Agregar Proveedor
                                    <span class="glyphicon glyphicon-plus"  </span>
                                </div>
                            <div class="btn btn-danger btn-sm" onclick="elimina_prov();">
                                <span class="glyphicon glyphicon-remove"></span>
                                Eliminar Proveedor
                            </div>';

        $titulo = 'Proforma NÂ°: ' . $proforma;
    }
    //MODULO AUTORIZACION DE PROFORMAS
    elseif ($tipo_aprobacion == 'PROFAUT') {

        $botones = '<div class="row">
    <div class="form-group col-xs-12 col-sm-12 col-md-2 col-lg-2">
        <div style="margin-left:25px;" class="btn btn-danger btn-sm" onclick="imprime_cuadro( \'' . $codpedi . '\',' . $idempresa . ', ' . $idsucursal . ');">
                  Cuadro Comparativo
                    <span class="glyphicon glyphicon-print"  </span>
        </div>	
    </div>
      </div>';

        $table_op = '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 95%; margin-bottom: 0px;" align="center">';
        $table_op .= '<tr>
                        <td class="info">N.-</td>
						<td class="info">Bodega</td>
                        <td class="info">Codigo</td>
						<td class="info" style="width: 15%;">Producto</td>
						<td class="info">Detalle</td>
						<td class="info">Unidad</td>
						<td class="info">Pedido</td> 						
						<td class="info">Stock</td>
						<td class="info" align="center">Cantidad - Costo</td>
						<td align="center"><input type="checkbox" onclick="marcar(this, \'_checkaut\');" align="center"></td>
                </tr>';
        $i = 1;

        $sql = "SELECT i.id_inv_prof, i.invp_num_invp ,
				i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
				i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.invp_det_prod
				from comercial.inv_proforma i where
				i.invp_cod_empr 	= $idempresa and
				i.invp_cod_sucu 	= $idsucursal and
				i.invp_esta_invp 	= 'S'  and
				i.invp_esta_oc     = 'N' and
				i.inv_cod_pedi  	= '$codpedi'
				order by i.id_inv_prof; ";
        // $oReturn->alert('Buscando...'.$sql);
        unset($array);
        $total       = 0;
        $tot_ret     = 0;
        $tot_pedi    = 0;
        $tot_stock = 0;
        $tot_costo = 0;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $prod_cod       = $oCon->f('invp_cod_prod');
                    $bode_cod       = $oCon->f('invp_cod_bode');
                    $unid_cod       = $oCon->f('invp_unid_cod');
                    $prbo_dis       = $oCon->f('invp_cant_stock');
                    $pedido         = $oCon->f('invp_cant_real');
                    $prod_nom       = $oCon->f('invp_nom_prod');
                    $id_inv_prof    = $oCon->f('id_inv_prof');
                    $invp_det_prod  = $oCon->f('invp_det_prod');
                    $secu_prof      = $oCon->f('invp_num_invp');

                    $bode_nom       = $array_bode[$bode_cod];
                    $unid_nom       = $array_unid[$unid_cod];

                    $fu->AgregarCampoNumerico($i, 'Cantidad|left', false, $pedido, 80, 20, true);


                    if ($sClass == 'off') $sClass = 'on';
                    else $sClass = 'off';
                    $table_op .= '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    $table_op .= '<td align="right">' . $i . '</td>';
                    $table_op .= '<td align="left">' . $bode_nom . '</td>';
                    $table_op .= '<td align="left">' . $prod_cod . '</td>';
                    $table_op .= '<td align="left">' . $prod_nom . '</td>';
                    $table_op .= '<td align="left">' . $invp_det_prod . '</td>';
                    $table_op .= '<td align="left">' . $unid_nom . '</td>';
                    $table_op .= '<td align="right">' . $pedido . '</td>';
                    $table_op .= '<td align="right">' . $prbo_dis . '</td>';

                    /// Proveedor
                    $table_op .= '<td align="right" valign="top">';
                    $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
                    $table_op .= '<tr>
										<td class="info" style="width: 6%;" >N.-</td>
										<td style="display:none">Codigo	</td>
										<td class="info">Proveedor	</td>
										<td style="display:none">Correo		</td>
										<td style="display:none">Costo		</td>
										<td class="info" style="width: 36%;" align="center">Costo OC	</td>
										<td class="info" style="width: 30%;" align="center">Cantidad OC		</td>
										<td class="info" align="center">Total</td>
										<td class="info" style="width: 30%;" align="center">Adjuntos</td>
										<td colspan="2" class="info" style="width: 10%;" align="right">		</td>
								</tr>';

                    $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
								d.invpd_ema_clpv, d.invpd_movil_clpv, d.invpd_costo_prod, invpd_cun_dmov, invpd_cant_dmov, invpd_adjunto
								from comercial.inv_proforma_det d where
								d.id_inv_prof = $id_inv_prof and
								d.invpd_esta_invpd = 'S'
								order by d.invpd_costo_prod  ";
                    //$oReturn->alert($sql);
                    unset($array_clpv);
                    $x = 1;
                    if ($oConA->Query($sql)) {
                        if ($oConA->NumFilas() > 0) {
                            do {
                                $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                                $ppvpr_pre_pac     = $oConA->f('invpd_cun_dmov');
                                $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                                $correo_clpv    = $oConA->f('invpd_ema_clpv');
                                $id_inv_dprof   = $oConA->f('id_inv_dprof');
                                $cantidad       = $oConA->f('invpd_cant_dmov');
                                $invpd_adjunto  = $oConA->f('invpd_adjunto');

                                $ruta = '';
                                $lista_archivos = explode(':', $invpd_adjunto);

                                foreach ($lista_archivos as $key => $nombre_archivo) {
                                    $ruta = $ruta . '<a href="../../Include/Clases/Formulario/Plugins/reloj/' . $nombre_archivo . '"  target="_blank" >' . $nombre_archivo . '</a><br>';
                                }



                                $serial            = '';
                                $serial         = $i . '-' . $ppvpr_cod_clpv;
                                $fu->AgregarCampoCheck($serial . '_checkaut',  'S/N|left', false, 'N');
                                $fu->AgregarCampoNumerico($serial . '_c', 'Costo|left', false, $ppvpr_pre_pac, 60, 20);
                                $fu->AgregarCampoNumerico($serial . '_ca', 'Cantidad|left', false, $cantidad, 60, 20);

                                if ($x == 1) {
                                    $fu->cCampos[$serial . '_checkaut']->xValor = 'S';
                                }

                                //$array_clpv [] = array( $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof );

                                $table_op .= '<tr height="20" class="' . $sClass . '"
													onMouseOver="javascript:this.className=\'link\';"
													onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                                $table_op .= '<td align="right">' . $x . '</td>';
                                $table_op .= '<td align="right" style="display:none">' . $ppvpr_cod_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $clpv_nom_clpv . '</td>';
                                $table_op .= '<td align="left" style="display:none">' . $correo_clpv . '.</td>';
                                $table_op .= '<td align="right" style="display:none">' . $ppvpr_pre_pac . '</td>';
                                $table_op .= '<td align="right" >' . $fu->ObjetoHtml($serial . '_c') . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_ca') . '</td>';
                                $table_op .= '<td align="right">' . ($ppvpr_pre_pac * $cantidad) . '</td>';
                                $table_op .= '<td align="right">' . $ruta . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_checkaut') . '</td>';
                                $table_op .= '</tr>';

                                $x++;
                                $tot_costo += round(($ppvpr_pre_pac * $cantidad), 2);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }
                    $oConA->Free();

                    //$array [] = array( $prod_cod, $bode_cod, $unid_cod, $prbo_uco, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv );

                    $table_op .= '</table></td>';
                    $table_op .= '</tr>';

                    $i++;
                    $tot_pedi += $pedido;
                    $tot_stock += $prbo_dis;
                } while ($oCon->SiguienteRegistro());
                $table_op .= '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_pedi . '</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_stock . '</td>';
                $table_op .= '<td align="right" valign="top">';
                $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
                $table_op .= '<tr>
										<td class="info" style="width: 6%;" </td>
										<td style="display:none"></td>
										<td style="display:none"></td>
										<td style="display:none"></td>
										<td style="display:none"></td>
										<td style="width: 36%;" align="center"></td>
										<td style="width: 30%;" align="center"></td>
										<td align="right" class="fecha_letra">' . $tot_costo . '</td>
										<td style="width: 10%;" align="right"></td>
										<td style="width: 30%;" align="center"</td>
										<td style="width: 10%;" align="right">		</td>
								</tr>
								</table>
								</td>';
                $table_op .= '</tr>';
                $table_op .= '</table>';
            } else {
                $table_op = '<span class="fecha_letra">Sin Datos...</span>';
            }
        }
        $oCon->Free();
        $titulo = 'Proforma NÂ°: ' . $secu_prof;
        $evento = '<button type="button" class="btn btn-success"  onClick="autoriza_proforma(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ')">Autorizar Proforma</button>';
    }
    //MODULOS ORDEN DE COMPRA
    elseif ($tipo_aprobacion == 'PROFOCO') {



        $optionOC = '';

        $sql_oc = "SELECT tran_cod_tran, tran_des_tran
																			from saetran, saedefi where
																			tran_cod_tran = defi_cod_tran and
																			tran_cod_empr = $idempresa and
																			tran_cod_sucu = $idsucursal and
																			defi_cod_empr = $idempresa and
																			defi_cod_sucu = $idsucursal and
																			defi_tip_defi = '4'  and
                                    defi_cod_modu = 10 and defi_cod_tran not in ( select parm_tran_ord from saeparm where parm_cod_empr = $idempresa ) group by 1, 2 order by 2";
        if ($oCon->Query($sql_oc)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $optionOC .= '<option value="' . $oCon->f('tran_cod_tran') . '">' . ($oCon->f('tran_des_tran')) . '</option>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $botones = '<div class="row">
    <div class="form-group col-xs-12 col-sm-12 col-md-2 col-lg-2">
        <div style="margin-left:25px;" class="btn btn-danger btn-sm" onclick="imprime_cuadro( \'' . $codpedi . '\',' . $idempresa . ', ' . $idsucursal . ');">
                  Cuadro Comparativo
                    <span class="glyphicon glyphicon-print"  </span>
        </div>	
    </div>
        <div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
            <label class="control-label" for="transaccion">* Transaccion:</label>
            <select id="transaccion" name="transaccion" class="form-control select2" >
                ' . $optionOC . '
            </select>	
        </div>
      </div>';


        $table_op = '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
        $table_op .= '<tr>
                        <td class="info">N.-</td>
						<td class="info">Bodega</td>
                        <td class="info">Codigo</td>
						<td class="info">Producto</td>
						<td class="info">Detalle</td>
						<td class="info">Unidad</td>
						<td class="info">Pedido</td> 						
						<td class="info">Stock</td>
						<td class="info" align="center">Cantidad</td>
						<td align="center"><input type="checkbox" onclick="marcar(this, \'_checkaut\');" align="center"></td>
                </tr>';
        $i = 1;

        $sql = "SELECT i.id_inv_prof, i.invp_num_invp ,
				i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
				i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, invp_det_prod
				from comercial.inv_proforma i where
				i.invp_cod_empr 	= $idempresa and
				i.invp_cod_sucu 	= $idsucursal and
				i.invp_esta_invp 	= 'S'  and
				i.invp_esta_minv	= 'N' and 
				i.inv_cod_pedi  	= '$codpedi'
				order by i.id_inv_prof; ";
        // $oReturn->alert('Buscando...'.$sql);
        unset($array);
        $total       = 0;
        $tot_ret     = 0;
        $tot_pedi    = 0;
        $tot_stock = 0;
        $tot_costo = 0;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $prod_cod       = $oCon->f('invp_cod_prod');
                    $bode_cod       = $oCon->f('invp_cod_bode');
                    $unid_cod       = $oCon->f('invp_unid_cod');
                    $prbo_dis       = $oCon->f('invp_cant_stock');
                    $pedido         = $oCon->f('invp_cant_real');
                    $prod_nom       = $oCon->f('invp_nom_prod');
                    $id_inv_prof    = $oCon->f('id_inv_prof');
                    $invp_det_prod  = $oCon->f('invp_det_prod');
                    $secu_prof  = $oCon->f('invp_num_invp');
                    $bode_nom       = $array_bode[$bode_cod];
                    $unid_nom       = $array_unid[$unid_cod];

                    $fu->AgregarCampoNumerico($i, 'Cantidad|left', false, $pedido, 80, 20, true);


                    if ($sClass == 'off') $sClass = 'on';
                    else $sClass = 'off';
                    $table_op .= '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    $table_op .= '<td align="right">' . $i . '</td>';
                    $table_op .= '<td align="left">' . $bode_nom . '</td>';
                    $table_op .= '<td align="left">' . $prod_cod . '</td>';
                    $table_op .= '<td align="left">' . $prod_nom . '</td>';
                    $table_op .= '<td align="left">' . $invp_det_prod . '</td>';
                    $table_op .= '<td align="left">' . $unid_nom . '</td>';
                    $table_op .= '<td align="right">' . $pedido . '</td>';
                    $table_op .= '<td align="right">' . $prbo_dis . '</td>';

                    /// Proveedor
                    $table_op .= '<td align="right" valign="top">';
                    $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="font-size:12px;width: 100%; margin-bottom: 0px;" align="center">';
                    $table_op .= '<tr>
										<td class="info" style="width: 5%;" >N.-</td>
										<td class="info" style="width: 8%;" >Codigo</td>
										<td class="info" style="width: 15%;">Proveedor</td>
										<td class="info" style="width: 10%;">Correo</td>
                                        <td class="info" style="width: 10%;">Celular</td>
										<td class="info" style="width: 10%;">Costo</td>
										<td class="info" style="width: 15%;">Costo OC</td>
										<td class="info" style="width: 11%;">Cantidad OC</td>
										<td class="info" style="width: 12%;" align="center">Total</td>
										<td class="info" style="width: 4%;" align="right">					
										</td>
								</tr>';

                    $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
								d.invpd_ema_clpv, d.invpd_movil_clpv, d.invpd_costo_prod, invpd_cun_dmov, invpd_cant_dmov
								from comercial.inv_proforma_det d where
								d.id_inv_prof = $id_inv_prof and
								d.invpd_esta_invpd = 'S'
								order by d.invpd_costo_prod  ";
                    //$oReturn->alert($sql);
                    unset($array_clpv);
                    $x = 1;
                    if ($oConA->Query($sql)) {
                        if ($oConA->NumFilas() > 0) {
                            do {
                                $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                                $ppvpr_pre_pac     = $oConA->f('invpd_cun_dmov');
                                $clpv_nom_clpv     = $oConA->f('invpd_nom_clpv');
                                $correo_clpv    = $oConA->f('invpd_ema_clpv');
                                $id_inv_dprof   = $oConA->f('id_inv_dprof');
                                $cantidad       = $oConA->f('invpd_cant_dmov');
                                $movil_clpv =      $oConA->f('invpd_movil_clpv');

                                $serial            = '';
                                $serial         = $i . '-' . $ppvpr_cod_clpv;
                                $fu->AgregarCampoCheck($serial . '_checkaut',  'S/N|left', false, 'N');
                                $fu->AgregarCampoNumerico($serial . '_c', 'Costo|left', false, $ppvpr_pre_pac, 200, 200, true);
                                $fu->AgregarCampoNumerico($serial . '_ca', 'Cantidad|left', false, $cantidad, 200, 200, true);

                                //ULTIMO COSTO DE PRODCUTO

                                $sqlu = "select bode_nom_bode, prod_cod_prod,prod_nom_prod, prod_des_prod,   medi_des_medi, 
											prbo_smi_prod, prbo_sma_prod, prbo_ped_prod, prbo_dis_prod,
											prbo_fec_ucom, prbo_pco_prod, prbo_fec_uven, prbo_pve_prod, unid_sigl_unid, prbo_uco_prod
											from saeprod, saeprbo, saebode, saemedi, saeunid
											where prod_cod_prod = prbo_cod_prod
											and prod_cod_empr = prbo_cod_empr
											and prod_cod_sucu = prbo_cod_sucu
											and prbo_cod_bode = bode_cod_bode
											and prbo_cod_empr = bode_cod_empr
											and prod_cod_medi = medi_cod_medi
											and prod_cod_empr = medi_cod_empr
											and prbo_cod_unid = unid_cod_unid
											and prbo_cod_empr = unid_cod_empr
											and prod_cod_empr = $idempresa
											and prod_cod_sucu = $idsucursal
											and prbo_cod_bode = $bode_cod
											and prod_cod_prod = '$prod_cod' ";
                                $ultimo_costo = consulta_string($sqlu, 'prbo_uco_prod', $oIfxA, 0);


                                if ($x == 1) {
                                    $fu->cCampos[$serial . '_checkaut']->xValor = 'S';
                                }

                                //$array_clpv [] = array( $ppvpr_cod_clpv, $ppvpr_pre_pac , $clpv_nom_clpv, $serial, $correo_clpv , $id_inv_dprof );

                                $table_op .= '<tr height="20" class="' . $sClass . '"
													onMouseOver="javascript:this.className=\'link\';"
													onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                                $table_op .= '<td align="right">' . $x . '</td>';
                                $table_op .= '<td align="right" >' . $ppvpr_cod_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $clpv_nom_clpv . '</td>';
                                $table_op .= '<td align="left" >' . $correo_clpv . '</td>';
                                $table_op .= '<td align="center" >' . $movil_clpv . '</td>';
                                $table_op .= '<td align="right" >' . $ultimo_costo . '</td>';
                                $table_op .= '<td align="right" >' . $fu->ObjetoHtml($serial . '_c') . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_ca') . '</td>';
                                $table_op .= '<td align="right">' . ($ppvpr_pre_pac * $cantidad) . '</td>';
                                $table_op .= '<td align="right">' . $fu->ObjetoHtml($serial . '_checkaut') . '</td>';
                                $table_op .= '</tr>';

                                $x++;
                                $tot_costo += round(($ppvpr_pre_pac * $cantidad), 2);
                            } while ($oConA->SiguienteRegistro());
                        }
                    }
                    $oConA->Free();

                    //$array [] = array( $prod_cod, $bode_cod, $unid_cod, $ultimo_costo, $prbo_dis, $pedido, $i , $prod_nom, $id_inv_prof,   $array_clpv );

                    $table_op .= '</table></td>';
                    $table_op .= '<td></td>';
                    $table_op .= '</tr>';

                    $i++;
                    $tot_pedi += $pedido;
                    $tot_stock += $prbo_dis;
                } while ($oCon->SiguienteRegistro());
                $table_op .= '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right"></td>';
                $table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_pedi . '</td>';
                $table_op .= '<td align="right" class="fecha_letra">' . $tot_stock . '</td>';
                $table_op .= '<td align="right" valign="top">';
                $table_op .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
                $table_op .= '<tr>
										<td style="width: 10%;" ></td>
										<td style="width: 44%;"></td>
										<td style="width: 20%;"></td>
										<td style="width: 10%;"></td>
										<td style="width: 10%;"></td>
										<td style="width: 10%;" align="right" class="fecha_letra">' . $tot_costo . '</td>
										<td style="width: 10%;"></td>
										<td style="width: 10%;" align="right"></td>
								</tr>
								</table>
								</td>
								<td></td>';
                $table_op .= '</tr>';
                $table_op .= '</table>';
            } else {
                $table_op = '<span class="fecha_letra">Sin Datos...</span>';
            }
        }
        $oCon->Free();

        $titulo = 'Proforma NÂ°: ' . $secu_prof;
        $evento = '<button type="button" class="btn btn-success"  onClick="generar_orden_compra(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ')">Ingresar Orden de Compra</button>';
    }


    $modal  = '<div class="modal-dialog" style="width:90%; height:90%">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">' . $titulo_apro . ' - Solicitud de Compra NÂ°: ' . $codpedi . ' ' . $titulo . '</h4>
                        </div>
                        <div class="modal-body">
                        ' . $botones . '
                        <div class="table-responsive">';
    $modal .= $table_op;
    $modal .= '</div></div>
                        <div class="modal-footer">
                            ' . $evento . '
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';


    $oReturn->assign("ModalProfProv", "innerHTML", $modal);

    return $oReturn;
}
/*MODAL ANULAR PEDIDO*/
function form_anular($id, $empresa, $sucursal)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo();
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();



    $campos = '<style>
                    .anular-card {
                        background: #f9fafc;
                        border: 1px solid #e0e6ed;
                        border-radius: 6px;
                        padding: 15px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                    }
                    .anular-card h4 { margin-top: 0; color: #2c3e50; }
                </style>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="anular-card">
                            <h4><i class="fa fa-ban text-danger"></i> Motivo de anulación</h4>
                            <p class="text-muted">Describe brevemente la razón de la anulación para notificar al solicitante.</p>
                            <div class="form-group">
                                <label class="control-label" for="det_anula">* Motivo</label>
                                <textarea id="det_anula" name="det_anula" class="form-control" rows="5" cols="30" placeholder="Ingresa el motivo de anulación"></textarea>
                            </div>
                        </div>
                    </div>
                </div>';


    $sHtml = '';
    $sHtml .= '<div class="modal-dialog modal-lg" >
        <div class="modal-content">

            <div class="modal-header" style="background:#fbeaea; border-bottom:1px solid #f3c8c8;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 style="margin:0; color:#c0392b;">Anulación Solicitud de Compra: ' . $id . '</h4>
            </div>
            <div class="modal-body" style="background:#fff;">';

    $sHtml .= '' . $campos . '';


    $sHtml .= '</div>
                        <div class="modal-footer" style="background:#f9fafc;">
                            <button type="button" class="btn btn-danger" onclick="anula_solicitud(\'' . $id . '\', ' . $empresa . ', ' . $sucursal . ')"><i class="fa fa-ban"></i> Anular</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"  >Cerrar</button>
                        </div>

                </div>
            </div>';


    $oReturn->assign("ModalAnular", "innerHTML", $sHtml);

    return $oReturn;
}
/*ANULACION SOLICITUD*/
function anular_solicitud($preimp, $idempresa, $sucursal, $motivo)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();


    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();



    $fecha_anu = date('Y-m-d H:i:s');
    //USUARIO APROBADOR
    $usuario_web_anu = $_SESSION['U_ID'];

    //        ESTADOS
    //        0 PENDIENTE
    //        1 ANULADO
    //        2 AUTORIZADO
    //        3 DESACTIVADO
    //        4 PROCESADO


    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');


        $sql = "update saepedi set pedi_est_pedi = '1',
                    pedi_user_anu=$usuario_web_anu,
                    pedi_fec_anu='$fecha_anu',
                    pedi_det_anu ='$motivo'
                    where 
                    pedi_cod_empr = $idempresa and 
                    pedi_cod_sucu = $sucursal and
                    pedi_cod_pedi = '$preimp'";

        $oIfx->QueryT($sql);

        //USUARIO SOLICITUD
        $sqls = "SELECT pedi_user_web, pedi_are_soli from saepedi where pedi_cod_pedi='$preimp' and pedi_cod_empr=$idempresa and pedi_cod_sucu=$sucursal";
        $usuario_web = consulta_string_func($sqls, 'pedi_user_web', $oIfxA, 0);

        //CORREO DE SOLICITANTE
        $sqlcorreo = "SELECT  usuario_email from comercial.usuario where usuario_id=$usuario_web";
        $mail    = consulta_string_func($sqlcorreo, 'usuario_email', $oIfxA, '');

        // ENVIO DE CORREOS
        if (!empty($mail)) {
            $array_sol = array();
            array_push($array_sol, $mail);

            $mensaje = 'Su solicitud fue anulada por el aprobador <br><b>Motivo:</b><br>' . $motivo . '';

            $correoMsj = correo_compras_general('', $array_sol, $mensaje, '', '', intval($preimp), 'ANULACION SOLICITUD DE COMPRA');

            $oReturn->alert($correoMsj);
        } else {
            $oReturn->alert('USUARIO NO TIENE REGISTRADO UN CORREO');
        }



        $oIfx->QueryT('COMMIT WORK;');
        $oReturn->script("alertSwal('Se ha Anulado Correctamente la Solicitud', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('cerrarModalAnular();');
        $oReturn->script('reporte_solicitudes();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
/*MODAL IFRAME EDICION DE PEDIDOS*/
function form_editar($id, $empresa, $sucursal)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo();
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();

    $sesion = session_id();

    $frame = '<iframe src="../pedido_compra_v3/pedido_compra.php?sesionId=' . $sesion . '&codsol=\'' . $id . '\'&codEmpr=' . $empresa . '&codSucu=' . $sucursal . '" frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"></iframe>';
    $sHtml = '';
    $sHtml .= '<div class="modal-dialog " style="width:100%; height:100%" >
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4>SOLICITUD DE COMPRA: ' . $id . '</h4>
            </div>
            <div class="modal-body">';

    $sHtml .= '' . $frame . '';


    $sHtml .= '</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="reporte_solicitudes();" >Cerrar</button>

                        </div>
                  
                </div>
            </div>';


    $oReturn->assign("ModalEditar", "innerHTML", $sHtml);

    return $oReturn;
}
/*REPORTE COMRPAS ACCIONES*/
function reporte_solicitudes($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo();
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();


    $botones = '';
    unset($_SESSION['U_ARRAY']);

    // ESTADOS
    //        0 PENDIENTE
    //        1 ANULADO
    //        2 AUTORIZADO
    //        3 DESACTIVADO
    //        4 PROCESADO

    $usuario_web =  $_SESSION['U_ID'];

    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];


    // ==========================
    // Filtros enviados desde el formulario
    // ==========================
    $busqueda_general = '';
    $estado_filtro    = '';

    if (is_array($aForm)) {
        $busqueda_general = isset($aForm['buscadorSolicitudes'])
            ? trim($aForm['buscadorSolicitudes'])
            : '';
        $estado_filtro = isset($aForm['filtroEstado'])
            ? trim($aForm['filtroEstado'])
            : '';
    }

    // USUARIOS
    $sql = "select usuario_id, concat(usuario_apellido, ' ', usuario_nombre) as nombres from comercial.usuario where empresa_id = $idempresa ";
    unset($array_user);
    $array_user = array_dato($oIfx, $sql, 'usuario_id', 'nombres');


    //AREA SOLICITANTE
    $sql = "select area_cod_area, area_des_area from saearea where area_cod_empr = $idempresa ";
    unset($array_area);
    $array_area = array_dato($oIfx, $sql, 'area_cod_area', 'area_des_area');

    // BODEGAS Y SUCURSALES
    $sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa ";
    unset($array_bode);
    $array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

    $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa";
    unset($array_sucursal);
    $array_sucursal = array_dato($oIfx, $sql, 'sucu_cod_sucu', 'sucu_nom_sucu');

    ///VALIDACION AREAS APROBACION USUARIO

    ///AREA USUARIO

    $sql = "SELECT usuario_departamento, areas_apro_compras from comercial.usuario where usuario_id=$usuario_web";
    $codigo_area = consulta_string($sql, 'usuario_departamento', $oIfx, '');
    $areas_compras = consulta_string($sql, 'areas_apro_compras', $oIfx, '');

    $fil_area = "AND P.pedi_are_soli in( '$codigo_area'";

    //CONTROL PERMISOS APROBACION AREAS

    if ($areas_compras != '[]') {
        $fil_area .= ',';
        $string = trim($areas_compras, "[]");
        $array_pro = explode(",", $string);


        foreach ($array_pro as $apro) {
            $fil_area .= "'" . $apro . "',";
        }

        $fil_area = substr($fil_area, 0, strlen($fil_area) - 1);
    }
    $fil_area .= ')';

    try {

        // ==============================
        // CARD CONTENEDOR PRINCIPAL
        // ==============================
        $table_op  = '<div class="card card-primary card-outline" style="margin-top:10px;">';
        $table_op .= '  <div class="card-header">';
        $table_op .= '    <h3 class="card-title">Reporte de pedidos</h3>';
        $table_op .= '  </div>';
        $table_op .= '  <div class="card-body">';

        // ==============================
        // BUSCADOR Y FILTRO DE ESTADO
        // ==============================
        $table_op .= '    <div class="row" style="margin-bottom:15px;">';

        // Buscar
        $table_op .= '      <div class="col-sm-5">';
        $table_op .= '        <div class="form-group">';
        $table_op .= '          <label for="buscadorSolicitudes">Buscar</label>';
        $table_op .= '          <input type="text" class="form-control" id="buscadorSolicitudes"';
        $table_op .= '                 name="buscadorSolicitudes"';
        $table_op .= '                 placeholder="N° de pedido, sucursal, responsable, motivo, descripción, producto...">';
        $table_op .= '        </div>';
        $table_op .= '      </div>';

        // Estado
        $table_op .= '      <div class="col-sm-3">';
        $table_op .= '        <div class="form-group">';
        $table_op .= '          <label for="filtroEstado">Estado</label>';
        $table_op .= '          <select id="filtroEstado" name="filtroEstado" class="form-control">';
        $table_op .= '            <option value="">Todos</option>';
        $table_op .= '            <option value="0">NUEVO</option>';
        $table_op .= '            <option value="2">AUTORIZADO</option>';
        $table_op .= '            <option value="1">ANULADO</option>';
        $table_op .= '            <option value="3">DESACTIVADO</option>';
        $table_op .= '            <option value="4">PROCESADO</option>';
        $table_op .= '          </select>';
        $table_op .= '        </div>';
        $table_op .= '      </div>';

        // Botones
        $table_op .= '      <div class="col-sm-4">';
        $table_op .= '        <div class="form-group">';
        $table_op .= '          <label>&nbsp;</label>';
        $table_op .= '          <div class="btn-group btn-group-justified" style="width:100%;">';

        // Generar reporte
        $table_op .= '            <div class="btn-group">';
        $table_op .= '              <button type="button" onclick="reporte_solicitudes()" class="btn btn-success btn-block"';
        $table_op .= '                      id="btnGenerarReporteSolicitudes" title="Buscar">';
        $table_op .= '                <i class="fa fa-search"></i> Buscar';
        $table_op .= '              </button>';
        $table_op .= '            </div>';


        $table_op .= '          </div>';
        $table_op .= '        </div>';
        $table_op .= '      </div>';

        $table_op .= '    </div>';




        // ==============================
        // TABLA (DENTRO DE TABLE-RESPONSIVE)
        // ==============================
        // ==============================
        // Construcción dinámica del WHERE según filtros
        // ==============================
        $where = " WHERE p.pedi_cod_empr = $idempresa
             AND p.pedi_cod_sucu = $sucursal";

        // Filtro por estado
        if ($estado_filtro !== '') {
            $estado_int = (int)$estado_filtro;
            $where .= " AND p.pedi_est_pedi = '$estado_int'";
        }


        // Filtro por texto libre (número, responsable, motivo, detalle, productos)
        if ($busqueda_general !== '') {
            $busq = strtoupper(addslashes($busqueda_general));
            $condiciones = [];

            // Si el texto es numérico, puede ser el número de pedido
            if (is_numeric($busqueda_general)) {
                $condiciones[] = "p.pedi_cod_pedi = " . (int)$busqueda_general;
            }

            // Responsable, motivo, descripción
            $condiciones[] = "UPPER(p.pedi_res_pedi) LIKE '%$busq%'";
            $condiciones[] = "UPPER(p.pedi_det_pedi) LIKE '%$busq%'";
            $condiciones[] = "UPPER(p.pedi_des_cons) LIKE '%$busq%'";

            // Coincidencias en el detalle (código, descripción, nombre producto)
            $condiciones[] = "EXISTS (
                                SELECT 1
                                  FROM saedped d
                                  LEFT JOIN saeprod prd
                                         ON prd.prod_cod_empr = d.dped_cod_empr
                                        AND prd.prod_cod_sucu = d.dped_cod_sucu
                                        AND prd.prod_cod_prod = d.dped_cod_prod
                                 WHERE d.dped_cod_empr = p.pedi_cod_empr
                                   AND d.dped_cod_sucu = p.pedi_cod_sucu
                                   AND d.dped_cod_pedi = p.pedi_cod_pedi
                                   AND (
                                        UPPER(d.dped_cod_prod)   LIKE '%$busq%' OR
                                        UPPER(d.dped_det_dped)   LIKE '%$busq%' OR
                                        UPPER(prd.prod_nom_prod) LIKE '%$busq%'
                                   )
                              )";

            $where .= " AND (" . implode(' OR ', $condiciones) . ")";
        }

        $sql = "SELECT  p.pedi_cod_pedi, p.pedi_cod_clpv,   p.pedi_fec_pedi,
                         p.pedi_fec_entr , p.pedi_res_pedi,  p.pedi_det_pedi,
                         p.pedi_des_cons,  p.pedi_cod_anu, p.pedi_fec_anu, 
                         p.pedi_are_soli, p.pedi_est_pedi, p.pedi_user_anu,
                         p.pedi_user_web, p.pedi_est_prof,
                         p.pedi_pri_pedi as prioridad_pedido
                  FROM saepedi p
                  $where
                  ORDER BY 1 DESC";



        $i = 1;
        unset($array);

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {

                $table_op .= '    <div class="table-responsive">';
                $table_op .= '      <table id="theTable2" class="table table-striped table-bordered table-hover table-condensed tabla-centro" align="center" cellpadding="2" cellspacing="1" width="100%">';
                $table_op .= '        <thead>';
                $table_op .= '          <tr>';
                $table_op .= '            <th>N.-</th>';
                $table_op .= '            <th>Pedido</th>';
                $table_op .= '            <th>Sucursal</th>';
                $table_op .= '            <th>Fecha Pedido</th>';
                $table_op .= '            <th>Fecha Entrega</th>';
                $table_op .= '            <th>Elaborado Por</th>';
                $table_op .= '            <th>Motivo</th>';
                $table_op .= '            <th>Prioridad</th>';
                $table_op .= '            <th>Observaciones</th>';
                $table_op .= '            <th>Prioridad</th>';
                $table_op .= '            <th>Estado</th>';
                $table_op .= '            <th class="no-sort no-toggle">Detalle</th>';
                $table_op .= '            <th class="no-sort no-toggle">Imprimir</th>';
                $table_op .= '            <th class="no-sort no-toggle">Adjuntos</th>';
                $table_op .= '            <th class="no-sort no-toggle">Acciones</th>';

                $table_op .= '          </tr>';
                $table_op .= '        </thead>';
                $table_op .= '        <tbody>';

                if (!isset($sClass)) {
                    $sClass = 'off';
                }

                do {
                    $fec_pedi   = $oIfx->f('pedi_fec_pedi');
                    $fec_entr   = $oIfx->f('pedi_fec_entr');
                    $preimp     = $oIfx->f('pedi_cod_pedi');
                    $estado_pro = $oIfx->f('pedi_est_prof');

                    $cod_anu    = $oIfx->f('pedi_cod_anu');
                    $clpv_cod   = $oIfx->f('pedi_cod_clpv');

                    $responsa   = $oIfx->f('pedi_res_pedi');
                    $motivo     = $oIfx->f('pedi_det_pedi');
                    //$prioridad  = $oIfx->f('pedi_pri_pedi');
                    $prioridad  = trim((string) $oIfx->f('prioridad_pedido'));
                    $usuario_solicitud = $oIfx->f('pedi_user_web');

                    $observaciones = $oIfx->f('pedi_des_cons');
                    $estado        = $oIfx->f('pedi_est_pedi');
                    $prioridad     = strtoupper($oIfx->f('pedi_pri_pedi'));




                    if (!empty($cod_anu)) {
                        $observaciones = 'Referencia Solicitud Anulada: ' . $cod_anu . '<br>' . $observaciones;
                    }

                    $serial = $preimp . '_' . $clpv_cod;
                    $fu->AgregarCampoCheck($serial, 'Check', false, 'N');

                    // ============================
                    // VALIDACIÃ“N PERMISOS APROBACIÃ“N
                    // ============================
                    $event_edit = '';
                    $btn_edit   = 'disabled';

                    $cod_apro   = '';
                    $event_apro = '';
                    $btn_apro   = 'disabled';

                    $event_anu  = '';
                    $btn_anu    = 'disabled';

                    $msjapro    = '';

                    // PERMISOS APROBACIONES COMPRAS
                    $id_apro   = '';
                    $sql_apro  = "SELECT id, nombre, orden 
                              FROM comercial.aprobaciones_compras 
                              WHERE empresa = $idempresa 
                                AND estado = 'S' 
                                AND tipo_aprobacion = 'COMPRAS' 
                                /* AND id NOT IN (
                                    SELECT id_aprobacion 
                                    FROM comercial.aprobaciones_solicitud_compra 
                                    WHERE id_solicitud = '$preimp' 
                                      AND empresa = $idempresa 
                                      AND sucursal = $sucursal
                                ) */
                              ORDER BY orden ASC 
                              LIMIT 1";

                    if ($oCon->Query($sql_apro)) {
                        if ($oCon->NumFilas() > 0) {
                            do {
                                $id_apro = $oCon->f('id');
                                $title   = $oCon->f('nombre');
                                $orden   = $oCon->f('orden');

                                $sqlu = "SELECT aprobaciones_compras 
                                     FROM comercial.usuario 
                                     WHERE usuario_id = $usuario_web";
                                $apro_user = json_decode(consulta_string_func($sqlu, 'aprobaciones_compras', $oIfxA, ''));

                                if ($apro_user) {
                                    foreach ($apro_user as $apro) {
                                        if ($id_apro == $apro) {
                                            $cod_apro   = $id_apro;
                                            $event_apro = 'onClick="javascript:aprobar_solicitud(\'' . $preimp . '\',' . $cod_apro . ',' . $idempresa . ', ' . $sucursal . ')"';
                                            $btn_apro   = '';

                                            // ANULACIÃ“N
                                            $event_anu  = 'onClick="javascript:anular_solicitud(\'' . $preimp . '\',' . $idempresa . ', ' . $sucursal . ')"';
                                            $btn_anu    = '';
                                        }
                                    }
                                } else {
                                    $msjapro = '<span><font color="red">EL USUARIO NO TIENE CONFIGURADO PERMISOS PARA REALIZAR APROBACIONES</font></span>';
                                }

                                $ap++;
                            } while ($oCon->SiguienteRegistro());
                        }
                    }
                    $oCon->Free();

                    // ============================
                    // NOMBRE DEL APROBADOR PENDIENTE
                    // ============================
                    /* $aprobaciones_pendientes = '';
                    if ($estado != '2' && !empty($id_apro)) {
                        $id_apro = '"' . $id_apro . '"';
                        $sqlap   = "SELECT concat(usuario_apellido, ' ', usuario_nombre) as nombres 
                                FROM comercial.usuario 
                                WHERE usuario_activo = 'S'
                                  AND aprobaciones_compras != ''
                                  AND aprobaciones_compras IS NOT NULL 
                                  AND aprobaciones_compras::jsonb @> '[$id_apro]'::jsonb";
                        $aprobador_compras = consulta_string_func($sqlap, 'nombres', $oIfxA, '');

                        $aprobaciones_pendientes = '<div class="btn-danger text-center">' . $title . ' (' . $aprobador_compras . ')</div>';
                    } */

                    // ============================
                    // PERMISOS PROFORMAS
                    // ============================
                    /*  $event_prof = '';
                    $btn_prof   = 'disabled';

                    $sql_apro = "SELECT id, nombre, orden, tipo_aprobacion 
                             FROM comercial.aprobaciones_compras 
                             WHERE empresa = $idempresa 
                               AND estado = 'S' 
                               AND tipo_aprobacion IN ('PROFPROV','PROFPREC','PROFAUT','PROFOCO') 
                               AND id NOT IN (
                                   SELECT id_aprobacion 
                                   FROM comercial.aprobaciones_solicitud_compra 
                                   WHERE id_solicitud = '$preimp' 
                                     AND empresa = $idempresa 
                                     AND sucursal = $sucursal
                               )
                             ORDER BY orden ASC 
                             LIMIT 1";

                    if ($oCon->Query($sql_apro)) {
                        if ($oCon->NumFilas() > 0) {
                            do {
                                $id_apro          = $oCon->f('id');
                                $title            = $oCon->f('nombre');
                                $tipo_aprobacion  = $oCon->f('tipo_aprobacion');
                                $orden            = $oCon->f('orden');

                                $sqlu = "SELECT aprobaciones_compras 
                                     FROM comercial.usuario 
                                     WHERE usuario_id = $usuario_web";
                                $apro_user = json_decode(consulta_string_func($sqlu, 'aprobaciones_compras', $oIfxA, ''));

                                if ($apro_user) {
                                    foreach ($apro_user as $apro) {
                                        if ($id_apro == $apro) {
                                            $cod_apro   = $id_apro;
                                            $event_prof = 'onClick="javascript:modal_proformas(\'' . $preimp . '\',' . $cod_apro . ',' . $idempresa . ', ' . $sucursal . ')"';
                                            $btn_prof   = '';
                                        }
                                    }
                                } else {
                                    $msjapro = '<span><font color="red">EL USUARIO NO TIENE CONFIGURADO PERMISOS PARA REALIZAR PROFORMAS</font></span>';
                                }

                                $ap++;
                            } while ($oCon->SiguienteRegistro());
                        }
                    }
                    $oCon->Free();

                    if ($estado != '2' || $clpv_cod != 0) {
                        $event_prof = '';
                        $btn_prof   = 'disabled';
                    } */

                    // ============================
                    // ESTADO DE LA ÃšLTIMA APROBACIÃ“N
                    // ============================
                    /* $sqla      = "SELECT id, nombre, tipo_aprobacion 
                              FROM comercial.aprobaciones_compras 
                              WHERE empresa = $idempresa 
                                AND estado = 'S' 
                                AND id IN (
                                    SELECT id_aprobacion 
                                    FROM comercial.aprobaciones_solicitud_compra 
                                    WHERE id_solicitud = '$preimp' 
                                      AND empresa = $idempresa 
                                      AND sucursal = $sucursal 
                                    ORDER BY id DESC 
                                    LIMIT 1
                                )";
                    $estado_apro = consulta_string($sqla, 'nombre', $oIfxA, '');
                    $tipo_apro   = consulta_string($sqla, 'tipo_aprobacion', $oIfxA, '');

                    if (!empty($estado_apro)) {
                        $estado       = '<div class="btn-success text-center">' . $estado_apro . '</div>';
                        $estado_texto = $estado_apro;
                    }

                    if ($estado == '0') {
                        $estado = '<div class="btn-primary text-center">PENDIENTE</div>';


                        if ($usuario_web == $usuario_solicitud) {
                            $btn_edit  = '';
                            $event_edit = 'onClick="javascript:editar_solicitud(\'' . $preimp . '\',' . $idempresa . ', ' . $sucursal . ')"';
                        }
                    } */



                    /* ESTADOS */

                    // Texto por defecto
                    $estado_texto  = 'Pendiente';
                    $estado_color  = '#f0ad4e33'; // naranja suave por defecto

                    // 1 = Anulada
                    if ($estado == '1') {
                        $estado_texto = 'Anulada';
                        $estado_color = '#ffcccc'; // rojo suave
                        $btn_anu      = 'disabled';
                        $event_anu    = '';

                        // 2 = Autorizada
                    } elseif ($estado == '2') {
                        $estado_texto = 'Autorizada';
                        $estado_color = '#ccffcc'; // verde suave

                        // 3 = Desactivado
                    } elseif ($estado == '3') {
                        $estado_texto = 'Desactivado';
                        $estado_color = '#ddd'; // gris

                        // 4 = Procesado
                    } elseif ($estado == '4') {
                        $estado_texto = 'Procesado';
                        $estado_color = '#b3d9ff'; // azul suave
                    }

                    // ============================
                    // DETALLE (PRODUCTOS) Y SUCURSAL
                    // ============================


                    $sucursal_nombre = isset($array_sucursal[$sucursal]) ? $array_sucursal[$sucursal] : $sucursal;

                    if ($sClass == 'off') {
                        $sClass = 'on';
                    } else {
                        $sClass = 'off';
                    }

                    $table_op .= '        <tr height="20" class="' . $sClass . '"
                                                onMouseOver="this.className=\'link\';"
                                                onMouseOut="this.className=\'' . $sClass . '\';">';

                    // Sel, N, Pedido, Sucursal
                    $table_op .= '          <td align="right">' . $i . '</td>';
                    $table_op .= '          <td class="col-numero">' . $preimp . '</td>';
                    $table_op .= '          <td class="col-sucursal">' . $sucursal_nombre . '</td>';

                    // Fechas, Responsable, Motivo
                    $table_op .= '          <td>' . $fec_pedi . '</td>';
                    $table_op .= '          <td>' . $fec_entr . '</td>';
                    $table_op .= '          <td class="col-responsable">' . $responsa . '</td>';
                    $table_op .= '          <td class="col-motivo">' . $motivo . '</td>';

                    //Prioridad
                    //$table_op .= '          <td class="col-prioridad">' . ($prioridad ?: '-') . '</td>';
                    $table_op .= '          <td class="col-prioridad">' . ($prioridad === '' ? '-' : $prioridad) . '</td>';

                    // Observaciones
                    $table_op .= '          <td>' . $observaciones . '</td>';

                    // Prioridad
                    $table_op .= '          <td align="center">' . $prioridad . '</td>';

                    // Estado
                    $table_op .= '          <td align="center" class="col-estado" 
                                        style="background-color: ' . $estado_color . '; font-weight:bold;" 
                                        title="' . htmlspecialchars($estado_texto, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($estado_texto, ENT_QUOTES, 'UTF-8') .
                        '</td>';

                    // Detalle (boton)
                    $table_op .= '          <td align="center">
                                        <span class="btn btn-primary btn-sm" value="Detalle"   onClick="javascript:detalle_pedido(\'' . $preimp . '\',' . $idempresa . ', ' . $sucursal . ', 1);">
                                                <i class="glyphicon glyphicon-list"></i>
                                            </span>
                                </td>';

                    // Imprimir (boton)
                    $table_op .= '          <td align="center" ><span class="btn btn-warning btn-sm" value="Generar"   onClick="javascript:vista_previa_reporte(\'' . $preimp . '\', ' . $idempresa . ', ' . $sucursal . ')">
                                                    <i class="glyphicon glyphicon-print"></i>
                                                </span></td>';

                    // Adjuntos (boton)
                    $table_op .= '<td align="center">
                                    <span class="btn btn-success btn-sm" value="Adjuntos"   onClick="javascript:adjuntos_solicitud(\'' . $preimp . '\',' . $idempresa . ', ' . $sucursal . ', 1)">
                                                    <i class="glyphicon glyphicon-paperclip"></i>
                                                </span>
                                </td>';

                    // Acciones (botones aprobar/anular/etc.)

                    $table_op .= '<td align="left" width="12%">
                          <span class="btn btn-danger btn-sm" title="Anular Solicitud" ' . $event_anu . ' ' . $btn_anu . '>
                              <i class="glyphicon glyphicon-remove"></i>
                          </span>
                          <span class="btn btn-primary btn-sm" title="Cargar Adjuntos" onClick="javascript:modal_adjuntos_pedi(\'' . $preimp . '\',' . $idempresa . ', ' . $sucursal . ')">
                              <i class="glyphicon glyphicon-plus"></i>
                          </span>
                       </td>';


                    $table_op .= ' </tr>';

                    $i++;
                } while ($oIfx->SiguienteRegistro());

                $table_op .= '        </tbody>';
                $table_op .= '      </table>';
                $table_op .= '    </div>'; // .table-responsive

            } else {
                // Sin filas
                $table_op .= '    <div class="alert alert-info" style="margin-top:10px;">Sin pedidos...</div>';
            }
        }

        // Cerrar card-body y card
        $table_op .= '  </div>'; // card-body
        $table_op .= '</div>';    // card

        $oIfx->Free();
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }


    //$_SESSION['U_ARRAY'] = $array;

    $oReturn->assign("divReporte", "innerHTML", $table_op);
    $oReturn->script("jsRemoveWindowLoad();");
    $oReturn->script("init('theTable2')");
    return $oReturn;
}
/*REPORTE SOLCITUDES QUE TIENEN GENERADA ORDE DE COMPRA*/

/*MODAL ADJUNTOS*/
function adjuntos_solicitud($id, $empresa, $sucursal, $tipo)
{
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $oReturn = new xajaxResponse();


    $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 90%; margin-bottom: 0px;" align="center">';
    $sHtml .= '<tr>
	<td class="info" >No.</td>
	<td class="info">Codigo</td>
	<td class="info">Producto</td>
    <td class="info">Detalle</td>
	<td class="info" align="center">Adjuntos</td></tr>';


    ////CARGA DE ADJUNTOS////
    $k = 1;
    $sqlpedi = "select dped_prod_nom, dped_det_dped, dped_cod_prod, dped_adj_dped, dped_cod_auxiliar, dped_desc_auxiliar  from saedped where dped_cod_pedi='$id' and dped_cod_empr= $empresa and dped_cod_sucu=$sucursal";

    if ($oIfx->Query($sqlpedi)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $prod_cod = $oIfx->f('dped_cod_prod');
                $prod = $oIfx->f('dped_prod_nom');
                $codigoAuxiliar = trim($oIfx->f('dped_cod_auxiliar'));
                $descripcionAuxiliar = trim($oIfx->f('dped_desc_auxiliar'));
                $esAuxiliar = (!empty($codigoAuxiliar) || !empty($descripcionAuxiliar));
                $detallePlano = restaurar_saltos_linea_guardados($oIfx->f('dped_det_dped'));
                $detalle = formatear_detalle_para_mostrar($detallePlano);
                $adjuntos = $oIfx->f('dped_adj_dped');

                $codigoMostrado = $esAuxiliar && !empty($codigoAuxiliar) ? $codigoAuxiliar : $prod_cod;
                $productoMostrado = $esAuxiliar && !empty($descripcionAuxiliar) ? $descripcionAuxiliar : $prod;


                $sHtml .= '<tr>';
                $sHtml .= '<td align="center">' . $k . '</td>';
                $sHtml .= '<td align="center">' . $codigoMostrado . '</td>';
                $sHtml .= '<td align="center">' . $productoMostrado . '</td>';
                $sHtml .= '<td align="center">' . $detalle . '</td>';

                $sHtml .= '<td align="right" valign="top">';

                $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';

                if (!empty($adjuntos)) {

                    $array_adj = explode(':', $adjuntos);


                    $sHtml .= '<tr>
                                    <td class="info" >N.-</td>
                                    <td class="info" >Ruta</td>
                            </tr>';
                    foreach ($array_adj as $adj) {
                        if (!empty($adj)) {
                            $ruta = "../../Include/Clases/Formulario/Plugins/reloj/$adj";

                            if (file_exists($ruta)) {
                                $imagen = $ruta;
                            } else {
                                $imagen = '';
                            }
                            $logo = '';

                            if (preg_match("/jpg|png|PNG|jpeg|gif/", $ruta)) {

                                $logo = '<div>
                                        <img src="' . $imagen . '" style="
                                        width:200px;
                                        object-fit; contain;">
                                        </div>';


                                $sHtml .= '<tr>
                                            <td> ' . $i . '</td>
                                            <td><a href="' . $ruta . '" target="_blank" >' . $logo . '</a></td>
                                        </tr>';
                            } else {

                                $logo = '<div><a href="' . $ruta . '" target="_blank" >' . $adj . '</a></div>';

                                $sHtml .= '<tr>
                                            <td> ' . $i . '</td>
                                            <td> ' . $logo . '</td>
                                        </tr>';
                            }
                            $i++;
                        }
                    } //CIERRE FOR EACH

                } else {
                    $sHtml .= '<tr><td colspan="2" align="center"><font color="red"><stong>SIN ADJUNTOS</strong></font></td></tr>';
                }


                $sHtml .= '</table></td>';
                $sHtml .= '</tr>';
                $k++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sHtml .= '</table>';

    $modal  = '<div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">ADJUNTOS - SOLICITUD DE COMPRA: ' . $id . '</h4>
                        </div>
                        <div class="modal-body">
                        <div class="table-responsive">';
    $modal .= $sHtml;
    $modal .= '</div></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';

    if ($tipo == 1) {
        $oReturn->assign("ModalAdj", "innerHTML", $modal);
    } else {
        $oReturn->assign("ModalAdjOrd", "innerHTML", $modal);
    }


    return $oReturn;
}
/*MODAL DETALLE*/
function form_detalle($codpedi, $idempresa, $idsucursal, $tipo)
{
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();


    if ($tipo == 1) {
        $sHtml .= '<form id="form_cumplimiento" name="form_cumplimiento">';
        $sHtml .= '<table id="tbdetalle" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
    } else {
        $sHtml .= '<table id="tbdetalleord" class="table table-striped table-bordered table-hover table-condensed tabla-centro" style="width: 100%; margin-bottom: 0px;" align="center">';
    }
    $sHtml .= '<thead><tr>
                    <th >No.</th>
                    <th >Bodega</th>
                    <th >C&oacute;digo</th>
                    <th> Producto</th>
                    <th>Unidad</th>
                    <th >Detalle</th>
                    <th >Cantidad Solicitada</th>
                    <th>Tipo</th>
                    <th>C&oacute;digo Auxiliar</th>
                    <th>Descripci&oacute;n Auxiliar</th>
                    <th width="10%">Cant. Cumplida</th>
                    <th width="15%">Detalle Cumplimiento</th>
                    <th>Archivo</th>
                </tr>
                </thead><tbody>';


    $k = 1;
    $sqlpedi = "SELECT * from saedped where dped_cod_pedi='$codpedi' 
    and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";

    if ($oIfx->Query($sqlpedi)) {
                if ($oIfx->NumFilas() > 0) {
            do {
                $ped_cod = trim($oIfx->f('dped_cod_dped'));
                $codigoAuxiliar = trim($oIfx->f('dped_cod_auxiliar'));
                $descripcionAuxiliar = trim($oIfx->f('dped_desc_auxiliar'));
                $esAuxiliar = (!empty($codigoAuxiliar) || !empty($descripcionAuxiliar));

                //bodega
                $cbode = $oIfx->f('dped_cod_bode');
                $sqlbo = "select bode_nom_bode from saebode where bode_cod_bode=$cbode";
                $nbode = consulta_string_func($sqlbo, 'bode_nom_bode', $oIfxA, '');
                if ($esAuxiliar) {
                    $nbode = '';
                }

                //Codigo producto
                $citem = $oIfx->f('dped_cod_prod');
                //Nombre del producto
                $sqlitem = "select prod_nom_prod from saeprod where prod_cod_prod='$citem'";
                $nprod = consulta_string_func($sqlitem, 'prod_nom_prod', $oIfxA, 0);
                $unidadId = $oIfx->f('dped_cod_unid');
                $unidad = '';
                if (!empty($unidadId)) {
                    $sqlUnidad = "select unid_sigl_unid from saeunid where unid_cod_unid='$unidadId'";
                    $unidad = consulta_string_func($sqlUnidad, 'unid_sigl_unid', $oIfxA, '');
                }

                //detalle porducto
                $detallePlano = restaurar_saltos_linea_guardados($oIfx->f('dped_det_dped'));
                $det = formatear_detalle_para_mostrar($detallePlano);

                //cantidad y costos
                $cant = round($oIfx->f('dped_can_ped'), 2);
                $archivoAdjunto = trim($oIfx->f('dped_adj_dped'));
                $archivoHtml = '';
                if (!empty($archivoAdjunto)) {
                    $archivoHtml = '<a href="../' . htmlspecialchars($archivoAdjunto, ENT_QUOTES, 'UTF-8') . '" target="_blank">Ver archivo</a>';
                }

                $codigoMostrado = $esAuxiliar && !empty($codigoAuxiliar) ? $codigoAuxiliar : $citem;
                $nombreMostrado = $esAuxiliar && !empty($descripcionAuxiliar) ? $descripcionAuxiliar : $nprod;
                $tipoProducto = $esAuxiliar ? 'Producto no registrado' : 'Producto registrado';

                $cantCumplida = $oIfx->f('dped_cant_cumpl');
                $detalleCumplimiento = trim($oIfx->f('dped_det_cumpl'));
                
                // Obtener estado de bloqueo del pedido
                $bloqueo = 'N';
                $sqlPediInfo = "SELECT pedi_cumpl_bloq FROM saepedi WHERE pedi_cod_pedi='$codpedi' AND pedi_cod_empr=$idempresa AND pedi_cod_sucu=$idsucursal";
                $bloqueo = consulta_string_func($sqlPediInfo, 'pedi_cumpl_bloq', $oIfxA, 'N');
                
                $readonly = ($bloqueo == 'S') ? 'readonly disabled' : '';

                $inputCant = '<input type="number" name="cant_cumpl['.$ped_cod.']" value="'.$cantCumplida.'" class="form-control input-sm" min="0" max="'.$cant.'" onkeyup="validarCantidadCumplimiento(this, '.$cant.')" onblur="validarCantidadCumplimiento(this, '.$cant.')" '.$readonly.' style="width: 100%; text-align: right;">';
                
                $inputDet = '<input type="text" name="det_cumpl['.$ped_cod.']" value="'.$detalleCumplimiento.'" class="form-control input-sm" '.$readonly.' style="width: 100%;">';

                $sHtml .= '<tr>';
                $sHtml .= '<td align="center">' . $k . '</td>';
                $sHtml .= '<td align="center">' . $nbode . '</td>';
                $sHtml .= '<td align="center">' . $codigoMostrado . '</td>';
                $sHtml .= '<td align="center">' . $nombreMostrado . '</td>';
                $sHtml .= '<td align="center">' . $unidad . '</td>';
                $sHtml .= '<td align="center">' . $det . '</td>';
                $sHtml .= '<td align="center">' . $cant . '</td>';
                $sHtml .= '<td align="center">' . $tipoProducto . '</td>';
                $sHtml .= '<td align="center">' . $codigoAuxiliar . '</td>';
                $sHtml .= '<td align="center">' . $descripcionAuxiliar . '</td>';
                $sHtml .= '<td align="center">' . $inputCant . '</td>';
                $sHtml .= '<td align="center">' . $inputDet . '</td>';
                $sHtml .= '<td align="center">' . $archivoHtml . '</td>';
                $sHtml .= '</tr>';
                $k++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sHtml .= '</tbody>';

    $sHtml .= '</table>';
    if ($tipo == 1) {
        $sHtml .= '</form>';
    }

    // Calcular estado etiqueta
    $sqlEstado = "SELECT pedi_est_cumpl, pedi_cumpl_bloq FROM saepedi WHERE pedi_cod_pedi='$codpedi' AND pedi_cod_empr=$idempresa AND pedi_cod_sucu=$idsucursal";
    $estadoCumplimiento = consulta_string_func($sqlEstado, 'pedi_est_cumpl', $oIfxA, 'INCOMPLETO');
    $bloqueoGeneral = consulta_string_func($sqlEstado, 'pedi_cumpl_bloq', $oIfxA, 'N');

    $labelClass = 'label-danger';
    if ($estadoCumplimiento == 'COMPLETADO') $labelClass = 'label-success';
    else if ($estadoCumplimiento == 'PARCIALMENTE COMPLETADO') $labelClass = 'label-warning';
    
    $etiquetaEstado = '<span class="label ' . $labelClass . '" style="font-size: 14px; margin-left: 10px;">' . ($estadoCumplimiento ? $estadoCumplimiento : 'INCOMPLETO') . '</span>';
    
    $btnGuardar = '';
    if ($tipo == 1 && $bloqueoGeneral != 'S') {
        $btnGuardar = '<button type="button" class="btn btn-primary" onclick="guardar_cumplimiento(\''.$codpedi.'\', '.$idempresa.', '.$idsucursal.');">Guardar cumplimiento</button>';
    }

    $modal  = '
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">DETALLE - SOLICITUD DE COMPRA: ' . $codpedi . $etiquetaEstado . ' </h4>
                        </div>
                        <div class="modal-body">';
    $modal .= $sHtml;
    $modal .= '   </div>
                        <div class="modal-footer">
                        '.$btnGuardar.'
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';

    if ($tipo == 1) {
        $oReturn->assign("ModalDetalle", "innerHTML", $modal);
        $oReturn->script("init('tbdetalle')");
    } else {
        $oReturn->assign("ModalDetalleOrd", "innerHTML", $modal);
        $oReturn->script("init('tbdetalleord')");
    }


    return $oReturn;
}

function guardar_cumplimiento($codpedi, $idempresa, $idsucursal, $formData)
{
    global $DSN, $DSN_Ifx;
    $oReturn = new xajaxResponse();
    
    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    // Procesar datos del formulario
    parse_str($formData, $datos);
    $cant_cumpl = $datos['cant_cumpl'] ?? [];
    $det_cumpl = $datos['det_cumpl'] ?? [];

    $totalItems = 0;
    $itemsCompletos = 0;
    $itemsVacios = 0;

    // Obtener todas las cantidades originales en una sola consulta
    $mapaCantidades = array();
    $sqlOrig = "SELECT dped_cod_dped, dped_can_ped FROM saedped WHERE dped_cod_pedi='$codpedi' AND dped_cod_empr=$idempresa AND dped_cod_sucu=$idsucursal";
    if ($oIfx->Query($sqlOrig)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $mapaCantidades[trim($oIfx->f('dped_cod_dped'))] = floatval($oIfx->f('dped_can_ped'));
            } while ($oIfx->SiguienteRegistro());
        }
    }

    foreach ($cant_cumpl as $idDetalle => $cantidad) {
        $cantidad = floatval($cantidad);
        $detalle = isset($det_cumpl[$idDetalle]) ? trim($det_cumpl[$idDetalle]) : '';
        
        $cantOriginal = isset($mapaCantidades[$idDetalle]) ? $mapaCantidades[$idDetalle] : 0;

        // Validacion simple
        if ($cantidad < 0) $cantidad = 0;
        if ($cantidad > $cantOriginal) $cantidad = $cantOriginal;

        // Determinar estado de la linea
        $estadoLinea = 'N'; // No cumplido
        
        // Comparacion flotante segura
        $esCompleto = (abs($cantidad - $cantOriginal) < 0.0001);
        
        if ($esCompleto && $cantOriginal > 0) {
            $estadoLinea = 'S'; // Cumplido
            $itemsCompletos++;
        } elseif ($cantidad > 0) {
            $estadoLinea = 'P'; // Parcial
        } else {
            $itemsVacios++;
        }
        $totalItems++;

        // Actualizar detalle
        // Actualizar detalle (asegurar que sea del pedido y empresa actual para evitar colisiones)
        $sqlUpdateDet = "UPDATE saedped SET 
                        dped_cant_cumpl = $cantidad,
                        dped_det_cumpl = '$detalle',
                        dped_cumplido = '$estadoLinea'
                        WHERE dped_cod_dped = '$idDetalle' 
                        AND dped_cod_pedi = '$codpedi' 
                        AND dped_cod_empr = $idempresa 
                        AND dped_cod_sucu = $idsucursal";
        $oIfx->Query($sqlUpdateDet);
    }

    // Calcular estado global
    $estadoGlobal = 'INCOMPLETO';
    $bloqueo = 'N';

    if ($totalItems > 0) {
        if ($itemsCompletos == $totalItems) {
            $estadoGlobal = 'COMPLETADO';
        } elseif ($itemsVacios == $totalItems) {
            $estadoGlobal = 'INCOMPLETO';
        } else {
            $estadoGlobal = 'PARCIALMENTE COMPLETADO';
        }
        
        
        // Solo bloquear si el estado es COMPLETADO
        $bloqueo = ($estadoGlobal == 'COMPLETADO') ? 'S' : 'N';

        // Actualizar cabecera
        $sqlUpdateCab = "UPDATE saepedi SET 
                        pedi_est_cumpl = '$estadoGlobal',
                        pedi_cumpl_bloq = '$bloqueo'
                        WHERE pedi_cod_pedi = '$codpedi' 
                        AND pedi_cod_empr = $idempresa 
                        AND pedi_cod_sucu = $idsucursal";
        
        if($oIfx->Query($sqlUpdateCab)) {
            $oReturn->alert("Cumplimiento guardado exitosamente. Estado: " . $estadoGlobal);
            // Recargar el detalle para ver bloqueo y etiquetas
            $oReturn->script("xajax_form_detalle('$codpedi', $idempresa, $idsucursal, 1);");
        } else {
            $oReturn->alert("Error al guardar cumplimiento en cabecera.");
        }

    } else {
        // En teoria no deberia pasar si viene data, pero por seguridad
        $oReturn->alert("No se detectaron items para procesar. Verifique los datos.");
    }

    return $oReturn;
}
/*MODAL APROBACIONES*/
function form_aprobaciones($codpedi, $codapro, $idempresa, $idsucursal)
{
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $oReturn = new xajaxResponse();

    $sqla = "SELECT nombre from comercial.aprobaciones_compras 
                where empresa=$idempresa and estado='S' and id=$codapro";

    $titulo_apro = consulta_string($sqla, 'nombre', $oIfxA, '');

    $sHtml .= '<table id="tbcantapro" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin-bottom: 0px;" align="center">';
    $sHtml .= '<thead><tr>
                    <th >No.</th>
                    <th >Bodega</th>
                    <th >C&oacute;digo</th>
                    <th> Producto</th>
                    <th >Detalle</th>
                    <th >Centro de Costos</th>
                    <th >Cantidad Solicitada</th>
                    <th >Cantidad Aprobada</th>
                    <th >Costo</th>
                    <th >Total</th>
                </tr>
                </thead><tbody>';


    $k = 1;
    $sqlpedi = "SELECT * from saedped where dped_cod_pedi='$codpedi' 
    and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";

    if ($oIfx->Query($sqlpedi)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ped_cod = $oIfx->f('dped_cod_dped');
                //bodega
                $cbode = $oIfx->f('dped_cod_bode');
                $sqlbo = "select bode_nom_bode from saebode where bode_cod_bode=$cbode";
                $nbode = consulta_string_func($sqlbo, 'bode_nom_bode', $oIfxA, '');

                //Codigo producto
                $citem = $oIfx->f('dped_cod_prod');
                //Nombre del producto
                $sqlitem = "select prod_nom_prod from saeprod where prod_cod_prod='$citem'";
                $nprod = consulta_string_func($sqlitem, 'prod_nom_prod', $oIfxA, 0);
                $ccos = $oIfx->f('dped_cod_ccos');

                $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn='$ccos'";
                $centro = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfxA, '');

                //detalle porducto
                $det = $oIfx->f('dped_det_dped');


                //cantidad
                $cant = $oIfx->f('dped_can_ped');
                $cant = round($cant, 2);

                //cantidad aprobada
                $cantapro = $oIfx->f('dped_can_apro');
                if (empty($cantapro)) $cantapro = $cant;
                $cantapro = round($cantapro, 2);
                //costo
                $cos = $oIfx->f('dped_prc_dped');
                $cos = number_format($cos, 2);
                //total
                $total = $oIfx->f('dped_tot_dped');
                $total = number_format($total, 2);
                //presupuesto
                $pres = $oIfx->f('dped_pre_dped');
                $pres = number_format($pres, 2);

                $eti = 'cant_' . $codpedi . '_' . $ped_cod;
                $input_apro = '<input type="number" id="cant_' . $codpedi . '_' . $ped_cod . '" name="cant_' . $codpedi . '_' . $ped_cod . '"  onBlur ="valida_cant_ped(\'' . $eti . '\',' . $cant . ')" class="form-control" ' . $event . ' value="' . $cantapro . '" />';



                $sHtml .= '<tr>';
                $sHtml .= '<td align="center">' . $k . '</td>';
                $sHtml .= '<td align="center">' . $nbode . '</td>';
                $sHtml .= '<td align="center">' . ($codigoMostrado ?? $citem) . '</td>';
                $sHtml .= '<td align="center">' . ($nombreMostrado ?? $nprod) . '</td>';
                $sHtml .= '<td align="justify">' . $det . '</td>';
                $sHtml .= '<td align="center">' . $centro . '</td>';
                $sHtml .= '<td align="center">' . $cant . '</td>';
                $sHtml .= '<td align="center">' . $input_apro . '</td>';
                $sHtml .= '<td align="center">' . $cos . '</td>';
                $sHtml .= '<td align="center">' . $total . '</td>';
                $sHtml .= '</tr>';
                $k++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sHtml .= '</tbody>';

    $stotal = "select sum(dped_tot_dped) as total from saedped where dped_cod_pedi='$codpedi'  and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";
    $totalped = consulta_string_func($stotal, 'total', $oIfxA, 0);
    $totalped = number_format($totalped, 2);

    $stotal = "select sum(dped_pre_dped) as totalpre from saedped where dped_cod_pedi='$codpedi'  and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";
    $totalpre = consulta_string_func($stotal, 'totalpre', $oIfxA, 0);
    $totalpre = number_format($totalpre, 2);

    $sHtml .= '<tfoot><tr>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td></td>';
    $sHtml .= '<td align="right" class="fecha_letra"><b>Total:</b></td>';
    $sHtml .= '<td align="center" class="fecha_letra">' . $totalped . '</td>';
    $sHtml .= '<td align="center" class="fecha_letra">' . $totalpre . '</td>';
    $sHtml .= '</tr></tfoot>';

    $sHtml .= '</table>';


    $modal  = '<div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">' . $titulo_apro . ' - SOLICITUD DE COMPRA: ' . $codpedi . '</h4>
                        </div>
                        <div class="modal-body">
                        <div class="table-responsive">';
    $modal .= $sHtml;
    $modal .= '</div></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"  onClick="autorizar_solicitud(\'' . $codpedi . '\',' . $codapro . ', ' . $idempresa . ', ' . $idsucursal . ')">Autorizar</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';

    $oReturn->assign("ModalAprobaciones", "innerHTML", $modal);
    $oReturn->script("initTabla('tbcantapro')");

    return $oReturn;
}
/*AUTORIZACION SOLICITUDES*/
function autorizar_solicitud($codpedi, $codapro, $idempresa, $idsucursal, $aForm = '')
{
    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();


    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oIfxB = new Dbo();
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    //USUARIO APROBADOR
    $usuario_web = $_SESSION['U_ID'];
    $fecha = date('Y-m-d H:i:s');

    $oReturn = new xajaxResponse();

    try {

        // commit
        $oIfx->QueryT('BEGIN WORK;');



        //APROBACION DE CANTIDADES

        $sqlpedi = "SELECT dped_cod_dped from saedped where dped_cod_pedi='$codpedi' 
        and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";

        if ($oIfxA->Query($sqlpedi)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $ped_cod = $oIfxA->f('dped_cod_dped');
                    $cant_upt = $aForm['cant_' . $codpedi . '_' . $ped_cod];

                    if (empty($cant_upt)) $cant_upt = 0;

                    $sql = "update saedped set dped_can_apro=$cant_upt  
                          where dped_cod_dped=$ped_cod and dped_cod_pedi='$codpedi' 
                          and dped_cod_empr= $idempresa and dped_cod_sucu=$idsucursal";

                    $oIfx->QueryT($sql);
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        $oIfxA->Free();

        //ACTUaLIZACION USUARIO FECHA DE DE EDICION

        $sql = "update saepedi set pedi_dped_apro=$usuario_web,pedi_dped_fecapr='$fecha'  
        where pedi_cod_pedi='$codpedi' 
        and pedi_cod_empr= $idempresa and pedi_cod_sucu=$idsucursal";

        $oIfx->QueryT($sql);


        //AUTORIZACION APROBACION 
        $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud, usuario, fecha) 
                    values
                    ($idempresa, $idsucursal, $codapro, '$codpedi', $usuario_web, '$fecha')";
        $oIfx->QueryT($sqlapro);



        $sql = "SELECT pedi_are_soli from saepedi where pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal and pedi_cod_pedi='$codpedi'";
        $area = consulta_string($sql, 'pedi_are_soli', $oIfxA, '');

        //VALIDACION FORMATO PERSONALZIADO SOLICITUD DE COMPRAS

        $sql = "SELECT ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
                        ftrn_des_ftrn = 'PEDIDO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";

        $ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfxB, '');


        if (!empty($ubi)) {
            include_once('../../' . $ubi . '');
            $ruta = genera_pdf_doc_comp($codpedi, 1, $idempresa, $idsucursal);
        } else {
            $html = generar_pedido_compra_pdf($idempresa, $idsucursal, $codpedi);

            $docu = 'documento' . $codpedi . '.pdf';
            $ruta = DIR_FACTELEC . 'Include/archivos/' . $docu;

            $html2pdf = new HTML2PDF('P', 'A3', 'fr');
            $html2pdf->WriteHTML($html);
            $html2pdf->Output($ruta, 'F');
        }





        ///SE VALIDA SI LA SOLICITUD YA TIENE TODAS LAS APROBACIONES PARA ACTUALIZAR SU ESTADO

        $sql = "SELECT count(*) as apro from comercial.aprobaciones_compras where empresa= $idempresa and estado='S' and tipo_aprobacion='COMPRAS'";
        $cont_apro = consulta_string($sql, 'apro', $oIfx, 0);


        $sql = "SELECT count(*) as apro from comercial.aprobaciones_solicitud_compra 
                    where 
                    id_solicitud= '$codpedi' and empresa=$idempresa and sucursal=$idsucursal
                    and id_aprobacion in (select id from comercial.aprobaciones_compras where empresa=$idempresa and tipo_aprobacion='COMPRAS')";
        $cont_sol = consulta_string($sql, 'apro', $oIfx, 0);

        if ($cont_apro != 0 && ($cont_apro == $cont_sol)) {

            $sql = "update saepedi set pedi_est_pedi = '2' where 
                        pedi_cod_empr = $idempresa and
                        pedi_cod_sucu = $idsucursal and 
                        pedi_cod_pedi = '$codpedi' ";
            $oIfx->QueryT($sql);

            //CODIGO DE LA PROXIMA APROBACION
            $sql = "SELECT id from comercial.aprobaciones_compras 
                            where empresa=$idempresa and estado='S' and tipo_aprobacion ='PROFPROV'";

            $cod_apro = consulta_string($sql, 'id', $oIfxA, 0);

            //MENSAJE DE CORREO Y WHATSAPP
            $mensaje = 'La solicitud <b>N. ' . $codpedi . '</b> ha sido aprobada<br> Proceda con la generacion de la proforma';
            $text_envio = 'La solicitud *N. ' . $codpedi . '* ha sido aprobada \nProceda con la generacion de la proforma';
        } else {

            //SE CONSULTA EL CODIGO DE LA SIGUIENTE APROBACION

            //ORDEN SOLICITUD ACTUAL
            $sql = "SELECT orden from comercial.aprobaciones_compras where id=$codapro";
            $orden = consulta_string($sql, 'orden', $oIfxA, 0);

            //CODIGO DE LA PROXIMA APROBACION
            $sql = "SELECT id from comercial.aprobaciones_compras 
                            where empresa=$idempresa and estado='S' and tipo_aprobacion ='COMPRAS' and orden > $orden
                            order by orden asc limit 1";

            $cod_apro = consulta_string($sql, 'id', $oIfxA, 0);

            //MENSAJE DE CORREO Y WHATSAPP
            $mensaje = 'Se ha generado la siguiente solicitud <b>N. ' . $codpedi . '</b><br> Requiere su revision y aprobacion';
            $text_envio = 'Se ha generado la siguiente solicitud *N. ' . $codpedi . '*\nRequiere su revision y aprobacion';
        }


        // Instanciamos la clase NotificacionesCompras
        $notifier = new NotificacionesCompras($oIfx, $oIfxA, $oReturn, $idempresa, $idsucursal, $codapro, $codpedi, $area, $ruta);
        // Enviar correo a los aprobadores
        $notifier->enviarCorreoAprobadores($mensaje);
        // Enviar WhatsApp a los aprobadores
        $notifier->enviarWhatsAppAprobadores($text_envio);


        $oIfx->QueryT('COMMIT WORK;');

        $oReturn->script("alertSwal('Autorizado Correctamente', 'success');");
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("cerrarModalAprobaciones();");
        $oReturn->script("reporte_solicitudes();");
    } //CIERRE TRY
    catch (Exception $e) {
        // rollback
        $oReturn->script("jsRemoveWindowLoad();");
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
/**DETALLE DEL PEDIDO DE COMPRA A EDITAR */
function detalle_pedido($secuencial, $empresa, $sucursal)
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    unset($_SESSION['aDataGird']);

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Archivo', 'Codigo Requisicion', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');
    $oReturn = new xajaxResponse();

    $sql = "SELECT  dped_cod_dped,    dped_cod_pedi,  dped_cod_prod,
                    dped_cod_bode,    dped_cod_sucu,  dped_cod_empr,
                    dped_num_prdo,    dped_cod_ejer,  dped_cod_unid,
                    dped_can_ped,     dped_can_ent,
                    dped_prc_dped,    dped_ban_dped,  dped_costo_dped,
                    dped_tot_dped,    dped_prod_nom,  dped_cod_ccos,
                    dped_det_dped,dped_pre_dped , dped_adj_dped,
                    dped_cod_auxiliar, dped_desc_auxiliar
                    from saedped
                    where dped_cod_pedi='$secuencial' and dped_cod_empr=$empresa and dped_cod_sucu=$sucursal";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $cont = 0;
            do {
                $archivo_real = '';
                $cantidad   = $oIfx->f('dped_can_ped');
                $costo      = $oIfx->f('dped_costo_dped');
                $idbodega   = $oIfx->f('dped_cod_bode');
                $idproducto = $oIfx->f('dped_cod_prod');
                $idunidad   = $oIfx->f('dped_cod_unid');
                $detalle   = restaurar_saltos_linea_guardados($oIfx->f('dped_det_dped'));
                $adjuntos   = $oIfx->f('dped_adj_dped');
                $centro_costo   = $oIfx->f('dped_cod_ccos');
                $codigoAuxiliarDb = $oIfx->f('dped_cod_auxiliar');
                $descripcionAuxiliarDb = $oIfx->f('dped_desc_auxiliar');
                $tieneAuxiliar = (!empty($codigoAuxiliarDb) || !empty($descripcionAuxiliarDb));

                if (!empty($adjuntos)) $archivo_real = '../' . $adjuntos;


                // cantidad
                $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                // centro dï¿½ costo
                $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $centro_costo, 100, 100, true);
                $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                // detalle
                $fu->AgregarCampoTexto($cont . '_det', 'Detalle', false, $detalle, 100, 100, true);

                // busqueda
                $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                            title = "Presione aqui para Buscar Centro Costo"
                                            style="cursor: hand !important; cursor: pointer !important;"
                                            onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                            align="bottom" />';


                $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[3]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;
                $aDataGrid[$cont][$aLabelGrid[6]] = $costo;
                $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo');  //$costo;
                $aDataGrid[$cont][$aLabelGrid[9]] = round($cantidad * $costo, 2);
                $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                                    onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                                    onMouseOut="javascript:nd(); return true;"
                                                                                                    title = "Presione aqui para Eliminar"
                                                                                                    style="cursor: hand !important; cursor: pointer !important;"
                                                                                                    onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                                    alt="Eliminar"
                                                                                                    align="bottom" />';
                $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;  //$costo;
                $aDataGrid[$cont][$aLabelGrid[12]] = normalizar_detalle_con_saltos($detalle);
                $aDataGrid[$cont][$aLabelGrid[13]] = $archivo_real;
                $aDataGrid[$cont][$aLabelGrid[14]] = '';
                $aDataGrid[$cont]['Producto Auxiliar'] = $tieneAuxiliar ? 'SI' : 'No';
                $aDataGrid[$cont]['Codigo Auxiliar'] = $codigoAuxiliarDb;
                $aDataGrid[$cont]['Descripcion Auxiliar'] = $descripcionAuxiliarDb;

                $cont++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $_SESSION['aDataGird'] = $aDataGrid;
    $sHtml = mostrar_grid($empresa);
    $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
    $oReturn->script('totales_comp()');
    $oReturn->script('limpiar_prod()');
    $oReturn->script('cerrar_ventana();');
    $oReturn->script('refrescarBloqueoCampos();');

    return $oReturn;
}
/**CARGA DE DATOS DEL PEDIDO ANULADO*/
function carga_anulado($secuencial, $empresa, $sucursal)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN;


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    $omitirAprobaciones = false;


    $sql = "SELECT pedi_cod_pedi, pedi_fec_pedi, pedi_det_pedi,
                 pedi_des_cons, pedi_are_soli, pedi_lug_entr,
                 pedi_fec_entr, pedi_uso_pedi, pedi_tipo_pedi,
                 pedi_cod_clpv, pedi_tip_sol, pedi_pri_pedi,
                 COALESCE(pedi_omit_aprob, 'N') as pedi_omit_aprob
                 from saepedi
                 where pedi_cod_empr=$empresa and pedi_cod_sucu=$sucursal
                 and pedi_cod_pedi =  '$secuencial'";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {

            do {
                $secuencial = $oCon->f('pedi_cod_pedi');
                $motivo = $oCon->f('pedi_det_pedi');
                $fecha_pedido = $oCon->f('pedi_fec_pedi');
                $fecha_entrega = $oCon->f('pedi_fec_entr');
                $observaciones = $oCon->f('pedi_des_cons');
                $area = $oCon->f('pedi_are_soli');
                $lugar = $oCon->f('pedi_lug_entr');
                $uso = $oCon->f('pedi_uso_pedi');
                $tipo = $oCon->f('pedi_tip_sol');
                $prioridad = $oCon->f('pedi_pri_pedi');
                $clpv = $oCon->f('pedi_cod_clpv');
                $omitirAprobaciones = strtoupper($oCon->f('pedi_omit_aprob')) === 'S';
                $prioridad = $oCon->f('pedi_pri_pedi');


                $oReturn->assign("pedi_cod_anu", "value", $secuencial);
                $oReturn->assign("motivo", "value", $motivo);
                //$oReturn->assign("fecha_pedido","value",$fecha_pedido);
                //$oReturn->assign("fecha_entrega","value",$fecha_entrega);
                $oReturn->assign("observaciones", "value", $observaciones);
                $oReturn->assign("area", "value", $area);
                $oReturn->assign("lugar", "value", $lugar);
                $oReturn->assign("uso", "value", $uso);
                $oReturn->assign("tipo", "value", $tipo);
                $oReturn->assign("pedi_pri_pedi", "value", $prioridad);

                if ($clpv != 0) {

                    $sqlc = "SELECT clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv= $clpv
                                and clpv_cod_empr=$empresa";
                    $nombre = consulta_string($sqlc, 'clpv_nom_clpv', $oConA, '');
                    $ruc = consulta_string($sqlc, 'clpv_ruc_clpv', $oConA, '');

                    $oReturn->assign("cliente", "value", $clpv);
                    $oReturn->assign("cliente_nombre", "value", $nombre);
                    $oReturn->assign("ruc", "value", $ruc);
                }
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();

    $aprobadoresPedido = array();
    $pedidoConsulta = variantes_numero_pedido($secuencial);
    $pedidoConsultaDb = array_map(function ($codigo) {
        return str_replace("'", "''", $codigo);
    }, $pedidoConsulta);
    $pedidoFiltro = "'" . implode("','", array_unique($pedidoConsultaDb)) . "'";

    $sqlAprobadores = "SELECT ap.aprobador_id, ap.aprobador_nombre, ap.cargo_id, ap.cargo_nombre, ap.enviar, ap.empresa, ap.sucursal, COALESCE(ap.orden, ap.id) as orden, c.nombre as cargo_nombre_bd" .
        " FROM comercial.aprobador_pedido ap" .
        " LEFT JOIN comercial.aprobador_cargo c ON ap.cargo_id = c.id" .
        " WHERE ap.empresa = $empresa AND ap.sucursal = $sucursal AND ap.pedido IN ($pedidoFiltro)" .
        " ORDER BY COALESCE(ap.orden, ap.id) ASC, ap.id ASC";
    if ($oConA->Query($sqlAprobadores)) {
        if ($oConA->NumFilas() > 0) {
            do {
                $cargoNombre = $oConA->f('cargo_nombre');
                if (empty($cargoNombre)) {
                    $cargoNombre = $oConA->f('cargo_nombre_bd');
                }

                $aprobadoresPedido[] = array(
                    'id' => strtoupper($oConA->f('aprobador_id')),
                    'nombre' => strtoupper($oConA->f('aprobador_nombre')),
                    'grupoId' => $oConA->f('cargo_id'),
                    'grupoNombre' => strtoupper($cargoNombre),
                    'cargo' => strtoupper($cargoNombre),
                    'enviar' => strtoupper($oConA->f('enviar')) !== 'N',
                    'empresaId' => $oConA->f('empresa'),
                    'sucursalId' => $oConA->f('sucursal'),
                    'orden' => $oConA->f('orden')
                );
            } while ($oConA->SiguienteRegistro());
        }
    }

    $aprobadoresJson = json_encode($aprobadoresPedido);
    $omitirJs = $omitirAprobaciones ? 'true' : 'false';
    $oConA->Free();

    $oReturn->script("carga_detalle_pedido('$secuencial', $empresa, $sucursal);");
    $oReturn->script("establecerEstadoFormulario('creada');");
    $oReturn->script("aplicarEstadoPendiente();");
    $oReturn->script("restaurarAprobadoresGuardados($aprobadoresJson, $omitirJs);");

    $estadoNavegacion = obtener_estado_navegacion($empresa, $sucursal, $secuencial);
    $mostrarAnterior = $estadoNavegacion['anterior'] ? 'true' : 'false';
    $mostrarSiguiente = $estadoNavegacion['siguiente'] ? 'true' : 'false';
    $oReturn->script("actualizarEstadoNavegacion($mostrarAnterior, $mostrarSiguiente);");

    return $oReturn;
}
/**CARGA DE DATOS DEL PEDIDO */
function carga_pedido($secuencial, $empresa, $sucursal)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN;


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    $omitirAprobaciones = false;


    $sql = "SELECT pedi_cod_pedi, pedi_fec_pedi, pedi_det_pedi,
                 pedi_des_cons, pedi_are_soli, pedi_lug_entr,
                 pedi_fec_entr, pedi_uso_pedi, pedi_tipo_pedi,
                 pedi_cod_clpv, pedi_tip_sol, pedi_pri_pedi,
                 COALESCE(pedi_omit_aprob, 'N') as pedi_omit_aprob
                 from saepedi
                 where pedi_cod_empr=$empresa and pedi_cod_sucu=$sucursal
                 and pedi_cod_pedi =  '$secuencial'";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {

            do {
                $secuencial = $oCon->f('pedi_cod_pedi');
                $motivo = $oCon->f('pedi_det_pedi');
                $fecha_pedido = $oCon->f('pedi_fec_pedi');
                $fecha_entrega = $oCon->f('pedi_fec_entr');
                $observaciones = $oCon->f('pedi_des_cons');

                $codigoArea = $oCon->f('pedi_are_soli');

                $sqlare = "SELECT area_des_area from saearea where area_cod_area='$codigoArea' and area_cod_empr=$empresa";
                $area = consulta_string($sqlare, 'area_des_area', $oConA, '');

                $area = $oCon->f('pedi_are_soli');

                $lugar = $oCon->f('pedi_lug_entr');
                $uso = $oCon->f('pedi_uso_pedi');
                $tipo = $oCon->f('pedi_tip_sol');
                $prioridad = $oCon->f('pedi_pri_pedi');
                $clpv = $oCon->f('pedi_cod_clpv');
                $omitirAprobaciones = strtoupper($oCon->f('pedi_omit_aprob')) === 'S';
                $prioridad = $oCon->f('pedi_pri_pedi');


                $oReturn->assign("nota_compra", "value", $secuencial);
                $oReturn->assign("motivo", "value", $motivo);
                $oReturn->assign("fecha_pedido", "value", $fecha_pedido);
                $oReturn->assign("fecha_entrega", "value", $fecha_entrega);
                $oReturn->assign("observaciones", "value", $observaciones);
                $oReturn->assign("area", "value", $area);
                $oReturn->assign("codigoArea", "value", $codigoArea);
                $oReturn->assign("lugar", "value", $lugar);
                $oReturn->assign("uso", "value", $uso);
                $oReturn->assign("tipo", "value", $tipo);
                $oReturn->assign("pedi_pri_pedi", "value", $prioridad);

                if ($clpv != 0) {

                    $sqlc = "SELECT clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv= $clpv
                                and clpv_cod_empr=$empresa";
                    $nombre = consulta_string($sqlc, 'clpv_nom_clpv', $oConA, '');
                    $ruc = consulta_string($sqlc, 'clpv_ruc_clpv', $oConA, '');

                    $oReturn->assign("cliente", "value", $clpv);
                    $oReturn->assign("cliente_nombre", "value", $nombre);
                    $oReturn->assign("ruc", "value", $ruc);
                }
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();

    $aprobadoresPedido = array();
    $pedidoConsulta = variantes_numero_pedido($secuencial);
    $pedidoConsultaDb = array_map(function ($codigo) {
        return str_replace("'", "''", $codigo);
    }, $pedidoConsulta);
    $pedidoFiltro = "'" . implode("','", array_unique($pedidoConsultaDb)) . "'";

    $sqlAprobadores = "SELECT ap.aprobador_id, ap.aprobador_nombre, ap.cargo_id, ap.cargo_nombre, ap.enviar, ap.empresa, ap.sucursal, COALESCE(ap.orden, ap.id) as orden, c.nombre as cargo_nombre_bd" .
        " FROM comercial.aprobador_pedido ap" .
        " LEFT JOIN comercial.aprobador_cargo c ON ap.cargo_id = c.id" .
        " WHERE ap.empresa = $empresa AND ap.sucursal = $sucursal AND ap.pedido IN ($pedidoFiltro)" .
        " ORDER BY COALESCE(ap.orden, ap.id) ASC, ap.id ASC";
    if ($oConA->Query($sqlAprobadores)) {
        if ($oConA->NumFilas() > 0) {
            do {
                $cargoNombre = $oConA->f('cargo_nombre');
                if (empty($cargoNombre)) {
                    $cargoNombre = $oConA->f('cargo_nombre_bd');
                }

                $aprobadoresPedido[] = array(
                    'id' => strtoupper($oConA->f('aprobador_id')),
                    'nombre' => strtoupper($oConA->f('aprobador_nombre')),
                    'grupoId' => $oConA->f('cargo_id'),
                    'grupoNombre' => strtoupper($cargoNombre),
                    'cargo' => strtoupper($cargoNombre),
                    'enviar' => strtoupper($oConA->f('enviar')) !== 'N',
                    'empresaId' => $oConA->f('empresa'),
                    'sucursalId' => $oConA->f('sucursal'),
                    'orden' => $oConA->f('orden')
                );
            } while ($oConA->SiguienteRegistro());
        }
    }
    $oConA->Free();

    $aprobadoresJson = json_encode($aprobadoresPedido);
    $omitirJs = $omitirAprobaciones ? 'true' : 'false';

    $oReturn->script("carga_detalle_pedido('$secuencial', $empresa, $sucursal);");
    $oReturn->script("establecerEstadoFormulario('creada');");
    $oReturn->script("aplicarEstadoPendiente();");
    $oReturn->script("restaurarAprobadoresGuardados($aprobadoresJson, $omitirJs);");

    $estadoNavegacion = obtener_estado_navegacion($empresa, $sucursal, $secuencial);
    $mostrarAnterior = $estadoNavegacion['anterior'] ? 'true' : 'false';
    $mostrarSiguiente = $estadoNavegacion['siguiente'] ? 'true' : 'false';
    $oReturn->script("actualizarEstadoNavegacion($mostrarAnterior, $mostrarSiguiente);");

    return $oReturn;
}

function navegar_pedido($direccion, $secuencialActual, $empresa, $sucursal)
{
    global $DSN;

    $oReturn = new xajaxResponse();
    $empresa = intval($empresa);
    $sucursal = intval($sucursal);
    $secuencialActual = intval($secuencialActual);

    if ($empresa === 0 || $sucursal === 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("alertSwal('Seleccione empresa y sucursal para navegar entre pedidos');");
        return $oReturn;
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $comparador = '';
    $orden = 'ASC';

    if ($direccion === 'anterior' || $direccion === 'siguiente') {
        $comparador = ($direccion === 'anterior') ? '<' : '>';
        $orden = ($direccion === 'anterior') ? 'DESC' : 'ASC';
    } elseif ($direccion === 'ultimo') {
        $orden = 'DESC';
    }

    $filtroActual = ($comparador && $secuencialActual > 0) ? "AND pedi_cod_pedi::integer $comparador $secuencialActual" : '';

    $sql = "SELECT pedi_cod_pedi FROM saepedi
            WHERE pedi_cod_empr = $empresa
            AND pedi_cod_sucu = $sucursal
            AND pedi_est_pedi IN ('0','2')
            /* AND pedi_tipo_pedi NOT IN ('L') */
            $filtroActual
            ORDER BY pedi_cod_pedi::integer $orden
            LIMIT 1";

    $destino = consulta_string($sql, 'pedi_cod_pedi', $oCon, '');
    $oCon->Free();

    if (!empty($destino)) {
        $oReturn->script("xajax_carga_pedido('$destino', $empresa, $sucursal);");
    } else {
        $mensaje = 'No hay un pedido disponible en esa direccion.';
        $oReturn->script("alertSwal('$mensaje');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function buscar_pedido_por_numero($numero, $empresa, $sucursal)
{
    global $DSN;

    $oReturn = new xajaxResponse();
    $empresa = intval($empresa);
    $sucursal = intval($sucursal);
    $numero = trim($numero);

    if (empty($numero) || $empresa === 0 || $sucursal === 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("alertSwal('Ingrese un nÃºmero de pedido vÃ¡lido');");
        return $oReturn;
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $numeroDb = str_replace("'", "''", $numero);
    $sql = "SELECT pedi_cod_pedi FROM saepedi WHERE pedi_cod_empr = $empresa AND pedi_cod_sucu = $sucursal AND pedi_cod_pedi = '$numeroDb'";
    $destino = consulta_string($sql, 'pedi_cod_pedi', $oCon, '');
    $oCon->Free();

    if (!empty($destino)) {
        $oReturn->script("xajax_carga_pedido('$destino', $empresa, $sucursal);");
    } else {
        $oReturn->script("alertSwal('El pedido ingresado no existe');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function obtener_estado_navegacion($empresa, $sucursal, $secuencial)
{
    global $DSN;

    $estado = [
        'anterior' => false,
        'siguiente' => false
    ];

    $empresa = intval($empresa);
    $sucursal = intval($sucursal);
    $secuencial = intval($secuencial);

    if ($empresa === 0 || $sucursal === 0 || $secuencial === 0) {
        return $estado;
    }

    $condiciones = "pedi_cod_empr = $empresa AND pedi_cod_sucu = $sucursal AND pedi_est_pedi IN ('0','2') "; /* AND pedi_tipo_pedi NOT IN ('L') */

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $sqlAnterior = "SELECT pedi_cod_pedi FROM saepedi WHERE $condiciones AND pedi_cod_pedi::integer < $secuencial LIMIT 1";
    $sqlSiguiente = "SELECT pedi_cod_pedi FROM saepedi WHERE $condiciones AND pedi_cod_pedi::integer > $secuencial LIMIT 1";

    $estado['anterior'] = consulta_string($sqlAnterior, 'pedi_cod_pedi', $oCon, '') !== '';
    $estado['siguiente'] = consulta_string($sqlSiguiente, 'pedi_cod_pedi', $oCon, '') !== '';

    $oCon->Free();

    return $estado;
}

/*
MODAL PARA EDITAR PEDIDOS POR USUARIO
*/
function lista_pedidos()
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN;


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];




    $sHtml  = '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTA DE SOLICITUDES</h4>
		</div>
		<div class="modal-body">';



    $sHtml .= '
				<div class="form-row">
				<div class="col-md-12">
				
				<div class="table-responsive"><br><table id="tbprof" class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%; margin-top: 0px;" align="left">';
    $sHtml .= '<thead>
                                <tr>				
					<th align="center" >Nro</th>
                                        <th align="center" >Fecha</th>
                                        <th align="center" >Secuencial</th>
                                        <th align="center" >Motivo</th>
                                        <th align="center" >Seleccionar</th>			
				</tr>
                                        </thead>';
    $sHtml .= '<tbody>';


    $sql = "SELECT pedi_cod_pedi, pedi_fec_pedi, pedi_det_pedi from saepedi 
              where pedi_cod_empr=$idempresa and pedi_cod_sucu=$sucursal
              and pedi_est_pedi =  '0' and pedi_user_web=$usuario_web 
                and pedi_cod_pedi::integer not in (select id_solicitud::integer from comercial.aprobaciones_solicitud_compra where empresa=$idempresa and sucursal=$sucursal)
              order by pedi_cod_pedi::integer asc";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $i = 1;
            do {

                $secuencial = $oCon->f('pedi_cod_pedi');

                $motivo = $oCon->f('pedi_det_pedi');

                $fecha = $oCon->f('pedi_fec_pedi');
                $fecha = date('d-m-Y', strtotime($fecha));

                $edit = '<span class="btn btn-success btn-sm" title="Editar Pedido" value="Editar" onClick="editaPedido(\'' . $secuencial . '\', ' . $idempresa . ', ' . $sucursal . ');">
                                            <i class="glyphicon glyphicon-ok"></i>
                                        </span>';


                $sHtml .= '<tr>';
                $sHtml .= '<td align="center">' . $i . '</td>';
                $sHtml .= '<td align="center">' . $fecha . '</td>';
                $sHtml .= '<td align="center">' . $secuencial . '</td>';
                $sHtml .= '<td >' . $motivo . '</td>';
                $sHtml .= '<td align="center">' . $edit . '</td>';
                $sHtml .= '</tr>';
                $i++;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();



    $sHtml .= '</tbody>';
    $sHtml .= '</table></div></div></div>';


    $sHtml .= '</div>
				<div class="modal-footer">
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
				</div>
			</div>
		</div>';

    $oReturn->assign("ModalPedidos", "innerHTML", $sHtml);
    $oReturn->script("init('tbprof')");

    return $oReturn;
}
/*
MODAL PARA EDITAR PEDIDOS POR USUARIO
*/
function lista_pedidos_anulados()
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN;


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];



    $sHtml  = '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTA DE SOLICITUDES ANULADAS</h4>
		</div>
		<div class="modal-body">';



    $sHtml .= '
				<div class="form-row">
				<div class="col-md-12">
				
				<div class="table-responsive"><br><table id="tbanu" class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%; margin-top: 0px;" align="left">';
    $sHtml .= '<thead>
                                <tr>				
					<th align="center" >Nro</th>
                                        <th align="center" >Fecha Pedido</th>
                                        <th align="center" >Secuencial</th>
                                        <th align="center" >Motivo Anulacion</th>
                                        <th align="center" >Fecha Anulacion</th>
                                        <th align="center" >Anulado por:</th>
                                        <th align="center" >Seleccionar</th>			
				</tr>
                                        </thead>';
    $sHtml .= '<tbody>';


    $sql = "SELECT pedi_cod_pedi, pedi_fec_pedi, pedi_det_pedi, pedi_det_anu, pedi_user_anu, pedi_fec_anu from saepedi 
              where pedi_cod_empr=$idempresa and pedi_cod_sucu=$sucursal
              and pedi_est_pedi =  '1' and pedi_user_web=$usuario_web 
                and pedi_cod_pedi::integer not in 
                (select pedi_cod_anu::integer from saepedi where pedi_cod_empr=$idempresa and pedi_cod_sucu=$sucursal and pedi_cod_anu is not null)
              order by pedi_cod_pedi::integer asc";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $i = 1;
            do {

                $secuencial = $oCon->f('pedi_cod_pedi');

                $motivo = $oCon->f('pedi_det_pedi');

                $motivo_anu = $oCon->f('pedi_det_anu');

                $cod_user_anu = $oCon->f('pedi_user_anu');

                $fecha = $oCon->f('pedi_fec_pedi');
                $fecha = date('d-m-Y', strtotime($fecha));


                $fecha_anu = $oCon->f('pedi_fec_anu');
                if (!empty($fecha_anu)) {
                    $fecha_anu = date('d-m-Y', strtotime($fecha_anu));
                }

                $usuario_anu = '';
                if (!empty($cod_user_anu)) {
                    $sqlape = "select concat(usuario_apellido, ' ', usuario_nombre) as nombres from comercial.usuario where usuario_id=$cod_user_anu";
                    $usuario_anu = consulta_string($sqlape, 'nombres', $oConA, '');
                }


                $edit = '<span class="btn btn-success btn-sm" title="Editar Anulado" value="Editar" onClick="cargaAnulado(\'' . $secuencial . '\', ' . $idempresa . ', ' . $sucursal . ');">
                                            <i class="glyphicon glyphicon-ok"></i>
                                        </span>';


                $sHtml .= '<tr>';
                $sHtml .= '<td align="center">' . $i . '</td>';
                $sHtml .= '<td align="center">' . $fecha . '</td>';
                $sHtml .= '<td align="center">' . $secuencial . '</td>';
                $sHtml .= '<td >' . $motivo_anu . '</td>';
                $sHtml .= '<td >' . $fecha_anu . '</td>';
                $sHtml .= '<td >' . $usuario_anu . '</td>';
                $sHtml .= '<td align="center">' . $edit . '</td>';
                $sHtml .= '</tr>';
                $i++;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();



    $sHtml .= '</tbody>';
    $sHtml .= '</table></div></div></div>';


    $sHtml .= '</div>
				<div class="modal-footer">
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
				</div>
			</div>
		</div>';

    $oReturn->assign("ModalAnulados", "innerHTML", $sHtml);
    $oReturn->script("init('tbanu')");

    return $oReturn;
}
/*
  Herramientas de apoyo
 */
//BUSQUEDA PRODUCTO EDITAR

function buscar_productos($aForm = '')
{

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $empresa = $aForm['empresa'];
    $idempresa = $_SESSION['U_EMPRESA'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }

    $sucursal = $aForm['sucursal'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $bodega = $aForm['bodega'];

    $producto = trim($aForm['producto']);
    $filprod = "";

    if (!empty($producto)) {
        $producto = addslashes($producto);
        $filprod = "and (prod_nom_prod like upper('%$producto%') OR prod_cod_prod like upper('%$producto%') OR prod_des_prod like upper('%$producto%'))";
    }

    $codprod = trim($aForm['codigo_producto']);
    $filcodprod = "";




    $oReturn = new xajaxResponse();

    $sHtml .= '<div class="modal-dialog modal-lg" >
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">LISTADO DE PRODUCTOS </h4>
        </div>

        
    <div class="modal-body">';

    $sHtml .= '<div class="col-sm-12">
    <div class="col-sm-12">
        <br>
        <div class="panel panel-info">
        <div class="panel-heading text-center">
        Buscador de Productos
    </div>
            <div class="panel-body">
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col-sm-6 col-sm-offset-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" id="buscadorProductosModal" class="form-control" placeholder="Buscar productos">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">

    <table id="listprod"  class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">';
    $sHtml .= '<thead>';
    $sHtml .= ' <tr>
    <th align="center" bgcolor="#EBF0FA"><input type="checkbox" id="seleccionarTodoProductos" onclick="seleccionarTodosProductos(this.checked);"></th>
    <th align="left" bgcolor="#EBF0FA">Id</th>
    <th align="left" bgcolor="#EBF0FA">C&oacute;digo</th>
    <th align="left" bgcolor="#EBF0FA">Descripci&oacute;n</th>
    <th align="left" bgcolor="#EBF0FA">Unidad</th>
    <th align="left" bgcolor="#EBF0FA">Stock</th>

                </tr>';
    $sHtml .= '</thead>';
    $sHtml .= '<tbody>';



    $cont = 1;
    $sql = "select pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod , pr.prbo_pco_prod, pr.prbo_cod_unid
                    from saeprbo pr, saeprod p where
                    p.prod_cod_prod = pr.prbo_cod_prod and
                    p.prod_cod_empr = $empresa and
                    p.prod_cod_sucu = $sucursal and
                    pr.prbo_cod_empr = $idempresa and
                    pr.prbo_cod_bode = '$bodega' $filprod $filcodprod order by  2 limit 100";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codigo = $oIfx->f('prbo_cod_prod');
                $nombre = $oIfx->f('prod_nom_prod');
                $prbo_cod_unid = $oIfx->f('prbo_cod_unid');
                $costo         = round($oIfx->f('prbo_pco_prod'), 3);

                $sqlu = "select  unid_nom_unid  from saeunid where
                                unid_cod_empr = $empresa and
                                unid_cod_unid = '$prbo_cod_unid' ";
                $unidad = consulta_string($sqlu, 'unid_nom_unid', $oIfxA, '');



                /**
                 * CONSULTAR STOCK
                 */

                $sql_stock = "select  COALESCE( pr.prbo_dis_prod,'0' ) as stock
                from saeprod p, saeprbo pr where
                p.prod_cod_prod = pr.prbo_cod_prod and
                p.prod_cod_empr = $idempresa and
                p.prod_cod_sucu = $idsucursal and
                pr.prbo_cod_empr = $idempresa and
                pr.prbo_cod_bode = $bodega and
                p.prod_cod_prod = '$codigo'";
                $stock = consulta_string_func($sql_stock, 'stock', $oIfxA, 0);


                $stock = convert_number_format_jire($stock, 0);



                // stock pedido
                $sqlStockPedido = "select COALESCE(sum(dpef_cant_dfac),'0') as dpef_cant_dfac 
                from saepedf p, saedpef d 
                where
                p.pedf_cod_pedf = d.dpef_cod_pedf and
                p.pedf_cod_empr = $empresa and
                p.pedf_cod_sucu = $sucursal and
                p.pedf_est_fact = 'PE' and
                d.dpef_cod_bode = $bodega and
                d.dpef_cod_prod = '$codigo'";
                $reserva = consulta_string($sqlStockPedido, "dpef_cant_dfac", $oIfxA, 0);
                $reserva = number_format($reserva, 0);

                if ($cod_tpro == 1) {
                    $disponible = $stock;
                    $tipoProd = 'S';
                } else {
                    $disponible = $stock - $reserva;
                    $tipoProd = 'P';
                }



                $checkboxId = 'prod_sel_' . $cont;
                $rowData = ' data-codigo="' . htmlspecialchars($codigo, ENT_QUOTES) . '" data-nombre="' . htmlspecialchars($nombre, ENT_QUOTES) . '" data-costo="' . $costo . '" data-unidad="' . htmlspecialchars($prbo_cod_unid, ENT_QUOTES) . '" ';
                $sHtml .= '<tr class="producto-fila-modal" ' . $rowData . '>
                                <td align="center"><input type="checkbox" class="producto-seleccionado" id="' . $checkboxId . '" data-codigo="' . htmlspecialchars($codigo, ENT_QUOTES) . '" data-nombre="' . htmlspecialchars($nombre, ENT_QUOTES) . '" data-costo="' . $costo . '" data-unidad="' . htmlspecialchars($prbo_cod_unid, ENT_QUOTES) . '" onchange="actualizarSeleccionProductoVisual(this);"></td>
                                <td align="center">' . $cont . '</td>
                                <td class="producto-nombre-click" style="cursor:pointer; color:#2c3e50; font-weight:600;">' . $codigo . '</td>
                                <td class="producto-nombre-click" style="cursor:pointer; color:#2c3e50; font-weight:600;">' . $nombre . '</td>
                                <td align="center">' . $unidad . '</td>
                                <td align="center">' . $stock . '</td>
                                </tr>';


                $cont++;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oIfx->Free();




    $sHtml .= '</tbody>';
    $sHtml .= '</table></div></div></div></div>';


    $sHtml .= '          </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="agregarProductosSeleccionados();"><i class="fa fa-plus"></i> Agregar seleccionados</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>

                    </div>
                </div>
            </div>
         </div>';


    $oReturn->assign("ModalProd", "innerHTML", $sHtml);
    $oReturn->script("initTabla('listprod')");

    return $oReturn;
}

function genera_gridTMP($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $aAccion = null, $Totales = null, $aOrden = null)
{
    if (is_array($aData) && is_array($aLabel)) {
        $iLabel = count($aLabel);
        $iData = count($aData);
        $sClass = 'on';
        $sStyle = 'border:#999999 1px solid; padding:2px; width:' . $iAncho . '%';
        $sHtml = '';
        $sHtml .= '<fieldset style="' . $sStyle . '"><legend class="Titulo">' . $sTitulo . '</legend>';
        $sHtml .= '<form id="DataGrid">';
        $sHtml .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%">';
        $sHtml .= '<tr class="info" ><td colspan="' . $iLabel . '">Su consulta genero ' . $iData . ' registros de resultado</td></tr>';
        $sHtml .= '<tr>';
        // Genera Columnas de Grid
        for ($i = 0; $i < $iLabel; $i++) {
            $sLabel = explode('|', $aLabel[$i]);
            if ($sLabel[1] == '')
                //				$sHtml .= '<th class="diagrama" align="center">'.$sLabel[0].'</th>';
                if ($i == 6 || $i == 7) {
                    $sHtml .= '<td class="diagrama" align="center" style="display:none">' . $sLabel[0] . '</th>';
                } else {
                    $sHtml .= '<td class="diagrama" align="center">' . $sLabel[0] . '</th>';
                }
            else {
                if ($sLabel[1] == $aOrden[0]) {
                    if ($aOrden[1] == 'ASC') {
                        $sLabel[1] .= '|DESC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_down.png" align="absmiddle" />';
                    } else {
                        $sLabel[1] .= '|ASC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_up.png" align="absmiddle" />';
                    }
                } else {
                    $sImg = '';
                    $sLabel[1] .= '|ASC';
                }

                $sHtml .= '<th onClick="xajax_' . $sLabel[2] . '(xajax.getFormValues(\'form1\'),\'' . $sLabel[1] . '\')" 
								style="cursor: hand !important; cursor: pointer !important;" >' . $sLabel[0] . ' ';
                $sHtml .= $sImg;
                $sHtml .= '</th>';
            }
        }
        $sHtml .= '</tr>';
        // Genera Filas de Grid

        for ($i = 0; $i < $iData; $i++) {
            if ($sClass == 'off')
                $sClass = 'on';
            else
                $sClass = 'off';

            $sHtml .= '<tr class="' . $sClass . '" 
							onMouseOver="javascript:this.className=\'link\';" 
							onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
            for ($j = 0; $j < $iLabel; $j++)
                if (is_float($aData[$i][$aLabel[$j]]))
                    $sHtml .= '<td align="right">' . number_format($aData[$i][$aLabel[$j]], 2, ',', '.') . '</td>';
                else
                    //				$sHtml .= '<td align="left">'.$aData[$i][$aLabel[$j]].'</td>';
                    if ($j == 6 || $j == 7) {
                        $sHtml .= '<td align="left" style="display:none">' . $aData[$i][$aLabel[$j]] . '</td>';
                    } else {
                        $sHtml .= '<td align="left">' . $aData[$i][$aLabel[$j]] . '</td>';
                    }
            $sHtml .= '</tr>';
        }

        //Totales 
        $sHtml .= '<tr>';
        if (is_array($Totales)) {
            for ($i = 0; $i < $iLabel; $i++) {
                if ($i == 0)
                    $sHtml .= '<th class="total_reporte">Totales</th>';
                else {
                    if ($Totales[$i] == '')
                        if ($Totales[$i] == '0.00')
                            $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                        else
                            $sHtml .= '<th align="right"></th>';
                    else
                        $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                }
            }
        }

        $sHtml .= '</tr></table>';
        $sHtml .= '</form>';
        $sHtml .= '</fieldset>';
    }
    return $sHtml;
}

function genera_grid($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $aAccion = null, $Totales = null, $aOrden = null)
{
    if (is_array($aData) && is_array($aLabel)) {
        $iLabel = count($aLabel);
        $iData = count($aData);
        $sClass = 'on';
        $sStyle = 'border:#999999 1px solid; padding:2px; width:' . $iAncho . '%';
        $sHtml = '';

        $sHtml .= '<form id="DataGrid">';
        $sHtml .= '<table align="center" border="0" class="table table-hover table-bordered table-striped table-condensed" style="width: 98%; margin-bottom: 0px; font-size: 13px;">';
        $sHtml .= '<tr class="warning" ><td colspan="' . $iLabel . '">Su consulta genero ' . $iData . ' registros de resultado</td></tr>';
        $sHtml .= '<tr>';
        // Genera Columnas de Grid
        for ($i = 0; $i < $iLabel; $i++) {
            $sLabel = explode('|', $aLabel[$i]);
            if ($sLabel[1] == '')
                //				$sHtml .= '<th class="diagrama" align="center">'.$sLabel[0].'</th>';
                if ($i == 6 || $i == 7) {
                    $sHtml .= '<td class="info" align="center" style="display:none">' . $sLabel[0] . '</th>';
                } else {
                    $sHtml .= '<td class="info" align="center">' . $sLabel[0] . '</th>';
                }
            else {
                if ($sLabel[1] == $aOrden[0]) {
                    if ($aOrden[1] == 'ASC') {
                        $sLabel[1] .= '|DESC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_down.png" align="absmiddle" />';
                    } else {
                        $sLabel[1] .= '|ASC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_up.png" align="absmiddle" />';
                    }
                } else {
                    $sImg = '';
                    $sLabel[1] .= '|ASC';
                }

                $sHtml .= '<th onClick="xajax_' . $sLabel[2] . '(xajax.getFormValues(\'form1\'),\'' . $sLabel[1] . '\')" 
								style="cursor: hand !important; cursor: pointer !important;" >' . $sLabel[0] . ' ';
                $sHtml .= $sImg;
                $sHtml .= '</th>';
            }
        }
        $sHtml .= '</tr>';
        // Genera Filas de Grid

        for ($i = 0; $i < $iData; $i++) {
            if ($sClass == 'off')
                $sClass = 'on';
            else
                $sClass = 'off';

            $sHtml .= '<tr>';
            for ($j = 0; $j < $iLabel; $j++)
                if (is_float($aData[$i][$aLabel[$j]]))
                    $sHtml .= '<td align="right">' . number_format($aData[$i][$aLabel[$j]], 2, ',', '.') . '</td>';
                else
                    //				$sHtml .= '<td align="left">'.$aData[$i][$aLabel[$j]].'</td>';
                    if ($j == 6 || $j == 7) {
                        $sHtml .= '<td align="left" style="display:none">' . $aData[$i][$aLabel[$j]] . '</td>';
                    } else {
                        $sHtml .= '<td align="left">' . $aData[$i][$aLabel[$j]] . '</td>';
                    }
            $sHtml .= '</tr>';
        }

        //Totales 
        $sHtml .= '<tr>';
        if (is_array($Totales)) {
            for ($i = 0; $i < $iLabel; $i++) {
                if ($i == 0)
                    $sHtml .= '<th class="total_reporte">Totales</th>';
                else {
                    if ($Totales[$i] == '')
                        if ($Totales[$i] == '0.00')
                            $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                        else
                            $sHtml .= '<th align="right"></th>';
                    else
                        $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                }
            }
        }

        $sHtml .= '</tr></table>';
        $sHtml .= '</form>';
    }
    return $sHtml;
}

/* * **************************************************************** */
/* DF01 :: G E N E R A    F O R M U L A R I O    P E D I D O       */
/* * **************************************************************** */

function genera_formulario_pedido($sAccion = 'nuevo', $aForm = '', $cod_sol = 0, $empr_sol = 0, $sucu_sol = 0, $id_req = 0)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de session
    $empresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];
    $empl_nom = strtoupper($_SESSION['U_NOMBRE'] . ' ' . $_SESSION['U_APELLIDO']);

    //variables del formulario
    $idempresa = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $usuario_web =  $_SESSION['U_ID'];

    if (empty($idempresa)) {
        $idempresa = $empresa;
    }

    if (empty($idsucursal)) {
        $idsucursal = $sucursal;
    }


    ///FILTRO SUCURSAL SOLCIITUD DE REQUISICION
    $fil_sucu = '';
    if ($id_req != 0) {
        $sql = "SELECT requ_cod_sucu from saerequ where requ_cod_requ=$id_req";
        $requ_sucu = consulta_string($sql, 'requ_cod_sucu', $oIfx, 0);

        $fil_sucu = "and sucu_cod_sucu=$requ_sucu";
    }

    // D E T A L L E     D E S C R I P C I O N
    unset($_SESSION['aDataGird']);
    unset($aDataGrid);
    $aDataGrid = $_SESSION['aDataGird'];


    ///CREACION DE REGISTROS MODELO DE COMPRA NUEVO

    $sqlgein = "SELECT count(*) as conteo from comercial.aprobaciones_compras where empresa=$idempresa";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "INSERT INTO comercial.aprobaciones_compras (empresa, nombre, descripcion, estado, orden, envio_email_sn, tipo_aprobacion, envio_whts_sn) 
        VALUES ( $idempresa, 'APROBACION 1', 'APROBACION 1', 'S', 1, 'N', 'COMPRAS', 'N');";

        $oIfx->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo from comercial.aprobaciones_compras where empresa=$idempresa and tipo_aprobacion='PROFPROV'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "INSERT INTO comercial.aprobaciones_compras (empresa, nombre, descripcion, estado, orden, envio_email_sn, tipo_aprobacion, envio_whts_sn) 
        VALUES ( $idempresa, 'PROFORMA A PROVEEDORES', 'GENERACION DE LA PROFORMA', 'S', 1, 'N', 'PROFPROV', 'N');";
        $oIfx->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo from comercial.aprobaciones_compras where empresa=$idempresa and tipo_aprobacion='PROFPREC'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "INSERT INTO comercial.aprobaciones_compras (empresa, nombre, descripcion, estado, orden, envio_email_sn, tipo_aprobacion, envio_whts_sn) 
        VALUES ( $idempresa, 'PRECIOS PROFORMA', 'INGRESO DE LOS PRECIOS DE LA PROFORMA EN BASE A LAS COTIZACIONES DE LOS PROVEEDORES', 'S', 2, 'N', 'PROFPREC', 'N');";
        $oIfx->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo from comercial.aprobaciones_compras where empresa=$idempresa and tipo_aprobacion='PROFAUT'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "INSERT INTO comercial.aprobaciones_compras (empresa, nombre, descripcion, estado, orden, envio_email_sn, tipo_aprobacion, envio_whts_sn) 
        VALUES ($idempresa, 'AUTORIZACION PROFORMAS', 'AUTORIZACION PROFORMAS', 'S', 3, 'N', 'PROFAUT', 'N');";
        $oIfx->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo from comercial.aprobaciones_compras where empresa=$idempresa and tipo_aprobacion='PROFOCO'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "INSERT INTO comercial.aprobaciones_compras (empresa, nombre, descripcion, estado, orden, envio_email_sn, tipo_aprobacion, envio_whts_sn) 
        VALUES ($idempresa, 'ORDEN DE COMPRA', 'TRANSFORMACION DE LA PROFORMA EN ORDEN DE COMPRA', 'S', 4, 'N', 'PROFOCO', 'N');";
        $oIfx->QueryT($sqlalter);
    }



    ///DEPARTAMENTO USUARIO

    $sql = "SELECT usuario_departamento from comercial.usuario where usuario_id=$usuario_web";
    $codigo_area = consulta_string($sql, 'usuario_departamento', $oIfx, '');


    ///AREAS 

    $optionArea = '';

    $sql_area = "SELECT area_cod_area, area_des_area from saearea where area_cod_empr=$idempresa";

    if ($oIfx->Query($sql_area)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                if($codigo_area == $oIfx->f('area_cod_area')){
                    $optionArea .= '<option selected value="' . $oIfx->f('area_cod_area') . '">' . $oIfx->f('area_des_area') . '</option>';
                }
                else{
                    $optionArea .= '<option value="' . $oIfx->f('area_cod_area') . '">' . $oIfx->f('area_des_area') . '</option>';
                }

            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //VALIDACION ACCION SOLICITUD DE DE COMPRA


    /*$sql_apro = "SELECT id from comercial.aprobaciones_compras where tipo_aprobacion='SOLCOMPRAS'";
    $id_apro = consulta_string($sql_apro, 'id', $oIfx, '');

    if (!empty($id_apro)) {

        $id_apro = '"' . $id_apro . '"';

        $sqlap = "SELECT count(*) as cont
        FROM comercial.usuario 
        WHERE usuario_activo = 'S'
        AND usuario_id = $usuario_web
        AND  aprobaciones_compras!=''
        AND aprobaciones_compras IS NOT NULL 
        AND aprobaciones_compras::jsonb @> '[$id_apro]'::jsonb;";

        $ctrl_apro = consulta_string($sqlap, 'cont', $oIfx, 0);
        if ($ctrl_apro != 0) {
            $sql_area = "SELECT area_cod_area, area_des_area from saearea where area_cod_empr= $idempresa";
        }
    }*/



    



    $fechaPedidoDefault = date('Y') . '/' . date('m') . '/' . date('d');
    $fechaEntregaDefault = date('Y/m/d', strtotime('+7 days'));
    $prioridadesDisponibles = array('ALTA', 'MEDIA', 'BAJA');
    $prioridadDefault = 'MEDIA';
    $prioridadOpciones = '<option value="">Seleccione una opción</option>';
    foreach ($prioridadesDisponibles as $prioridad) {
        $sel = ($prioridad === $prioridadDefault) ? ' selected' : '';
        $prioridadOpciones .= '<option value="' . $prioridad . '"' . $sel . '>' . $prioridad . '</option>';
    }

    $codigoInformativo = '';
    $codigoGenerado = '';
    if (!empty($idempresa) && !empty($idsucursal)) {
        $codigoActual = pedf_num_preimp($idempresa, $idsucursal);
        $codigoInformativo = (int) $codigoActual + 1;
        $codigoGenerado = $codigoInformativo;
    }

    switch ($sAccion) {
        case 'nuevo':

            $ifu->AgregarCampoTexto('pedi_cod_anu', 'REFERENCIA PEDIDO No-|right', false, '', 100, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_anu', 'this.blur()');

            $ifu->AgregarCampoTexto('motivo', 'Motivo Solicitud|left', false, '', 380, 280, true);

            $ifu->AgregarCampoTexto('pedi_cod_pedi', 'Codigo|left', true, '', 50, 100, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_pedi', 'this.blur()');

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr order by 2", true, 170, 150);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');

            $ifu->AgregarCampoListaSQL('sucursal', 'Matriz|left', "", true, 170, 150, true);

            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, '', 120, 120, true);

            $ifu->AgregarCampoTexto('cliente_nombre', 'Proveedor|left', false, '', 380, 150, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');

            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';

            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, '', 50, 50, true);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');

            $ifu->AgregarCampoTexto('nota_compra', 'PEDIDO No-|right', false, '', 100, 200, true);

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Pedido|left', true, $fechaPedidoDefault);

            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, $fechaEntregaDefault);

            $ifu->AgregarCampoTexto('solicitado', 'Elaborado por|left', true, $empl_nom, 380, 280, true);
            $ifu->AgregarComandoAlPonerEnfoque('solicitado', 'this.blur()');

            $ifu->AgregarCampoTexto('motivo', 'Descripcion|left', false, '', 380, 280, true);

            //$ifu->AgregarCampoTexto('area', 'Area Solicitado|left', false, '', 380, 280, true);


            $ifu->AgregarCampoTexto('uso', 'Para uso de|left', false, '', 380, 380, true);

            $ifu->AgregarCampoTexto('lugar', 'Solicitado por|left', true, '', 380, 280, true);

            $ifu->AgregarCampoTexto('observaciones', 'Descripcion|left', false, '', 500, 1000, true);

            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto(' . $idempresa . ', event, 1 )');

            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto(' . $idempresa . ', event, 2)');

            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "", false, 170, 150, true);

            $ifu->AgregarCampoListaSQL(
                'unidad',
                'Unidad|left',
                "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa order by unid_nom_unid",
                false,
                170,
                150,
                true
            );

            $ifu->AgregarCampoOculto('costo', 'Costo');
            $ifu->cCampos['costo']->xValor = 0;

            $op = '';
            unset($_SESSION['aDataGird']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioReportePrueba", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $fu->AgregarCampoOculto('tipo_logistica', 'Tipo logÃ­stica');
            $fu->cCampos["tipo_logistica"]->xValor = 'L';

            $oReturn->script('cargar_sucursal();');

            $ifu->AgregarCampoTexto('detalle', 'Uso o Detalle|left', false, '', 170, 200, true);

            $fu->AgregarCampoListaSQL('tipo', 'Tipo|left', "select tpro_cod_tpro, tpro_des_tpro from saetpro where tpro_cod_empr = $idempresa and tpro_est_sn = 'S' order by tpro_des_tpro", true, 170, 150, true);


            //ADJUNTOS
            $ifu->AgregarCampoArchivo('archivo', 'Archivo|LEFT', false, '', 100, 100, '');



            break;
        case 'sucursal':

            $ifu->AgregarCampoTexto('pedi_cod_anu', 'REFERENCIA PEDIDO No-|right', false, '', 100, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_anu', 'this.blur()');

            $ifu->AgregarCampoTexto('pedi_cod_pedi', 'Codigo|left', true, '', 50, 100, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_pedi', 'this.blur()');

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr order by 2", true, 170, 150, true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');

            $ifu->AgregarCampoListaSQL('sucursal', 'Matriz|left', "select sucu_cod_sucu, sucu_nom_sucu from saesucu where
                                                                                      sucu_cod_empr = $idempresa $fil_sucu", true, 170, 150, true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_bodega();');

            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, '', 120, 120, true);

            $ifu->AgregarCampoTexto('cliente_nombre', 'Proveedor|left', false, '', 380, 150, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');
            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';

            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, '', 50, 50, true);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');

            $ifu->AgregarCampoTexto('nota_compra', 'PEDIDO No-|right', false, '', 100, 200, true);

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Pedido|left', true, $fechaPedidoDefault);

            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, $fechaEntregaDefault);

            $ifu->AgregarCampoTexto('solicitado', 'Elaborado por|left', true, $empl_nom, 380, 280, true);
            $ifu->AgregarComandoAlPonerEnfoque('solicitado', 'this.blur()');

            $ifu->AgregarCampoTexto('motivo', 'Descripcion|left', false, '', 380, 280, true);

            //$ifu->AgregarCampoTexto('area', 'Area Solicitado|left', false, '', 380, 280, true);


            $ifu->AgregarCampoTexto('uso', 'Para uso de|left', false, '', 380, 380, true);

            $ifu->AgregarCampoTexto('lugar', 'Solicitado por|left', true, '', 380, 280, true);

            $ifu->AgregarCampoTexto('observaciones', 'Descripcion|left', false, '', 500, 1000, true);

            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto(' . $idempresa . ', event, 1 )');

            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto(' . $idempresa . ', event, 2)');

            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "", false, 170, 150, true);

            $ifu->AgregarCampoListaSQL(
                'unidad',
                'Unidad|left',
                "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa order by unid_nom_unid",
                false,
                170,
                150,
                true
            );

            $ifu->AgregarCampoOculto('costo', 'Costo');
            $ifu->cCampos['costo']->xValor = 0;

            $op = '';
            unset($_SESSION['aDataGird']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioReportePrueba", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $fu->AgregarCampoOculto('tipo_logistica', 'Tipo logÃ­stica');
            $fu->cCampos["tipo_logistica"]->xValor = 'L';
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;
            $ifu->cCampos["empresa"]->xValor = $idempresa;
            // SUCURSAL SOLICITUD DE REQUISICION
            if ($id_req != 0) {
                $ifu->cCampos["sucursal"]->xValor = $requ_sucu;
            }

            $oReturn->script('cargar_bodega();');

            $ifu->AgregarCampoTexto('detalle', 'Uso o Detalle|left', false, '', 170, 200, true);


            $fu->AgregarCampoListaSQL('tipo', 'Tipo|left', "select tpro_cod_tpro, tpro_des_tpro from saetpro where tpro_cod_empr = $idempresa and tpro_est_sn = 'S' order by tpro_des_tpro", true, 170, 150, true);

            //ADJUNTOS
            $ifu->AgregarCampoArchivo('archivo', 'Archivo|LEFT', false, '', 100, 100, '');


            break;
        case 'bodega':
            // VARIABLES
            $ruc = $aForm['ruc'];
            $cliente_nom = $aForm['cliente_nombre'];
            $cliente = $aForm['cliente'];

            $ifu->AgregarCampoTexto('pedi_cod_anu', 'REFERENCIA PEDIDO No-|right', false, '', 100, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_anu', 'this.blur()');

            $ifu->AgregarCampoTexto('pedi_cod_pedi', 'Codigo|left', true, '', 50, 100, true);
            $ifu->AgregarComandoAlPonerEnfoque('pedi_cod_pedi', 'this.blur()');

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr where empr_cod_empr = $idempresa", true, 170, 150, true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');

            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', "select sucu_cod_sucu, sucu_nom_sucu from saesucu where
                                                                                      sucu_cod_empr = $idempresa $fil_sucu", true, 170, 150, true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_bodega();');

            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, $ruc, 120, 120, true);

            $ifu->AgregarCampoTexto('cliente_nombre', 'Proveedor|left', false, $cliente_nom, 380, 150, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');
            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';

            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, $cliente, 50, 50, true);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');

            $ifu->AgregarCampoTexto('nota_compra', 'PEDIDO No-|right', false, '', 100, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('nota_compra', 'this.blur()');

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Pedido|left', true, $fechaPedidoDefault);

            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, $fechaEntregaDefault);

            $ifu->AgregarCampoTexto('solicitado', 'Elaborado por|left', true, $empl_nom, 380, 280, true);
            $ifu->AgregarComandoAlPonerEnfoque('solicitado', 'this.blur()');

            $ifu->AgregarCampoTexto('motivo', 'Motivo Solicitud|left', false, '', 380, 280, true);

            // $ifu->AgregarCampoTexto('area', 'Area Solicitado|left', false, '', 380, 280, true);


            $ifu->AgregarCampoTexto('uso', 'Para uso de|left', false, '', 380, 380, true);

            $ifu->AgregarCampoTexto('lugar', 'Solicitado Por|left', true, '', 380, 280, true);

            $ifu->AgregarCampoTexto('observaciones', 'Descripcion|left', false, '', 500, 1000, true);
            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto( event, 1 )');

            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto( event, 2)');

            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);
            $ifu->AgregarComandoAlEscribir('cantidad', 'cargar_prod_grid(event);');

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "select  b.bode_cod_bode, b.bode_nom_bode from saebode b, saesubo s where
                                                                                  b.bode_cod_bode = s.subo_cod_bode and
                                                                                  b.bode_cod_empr = $idempresa and
                                                                                  s.subo_cod_empr = $idempresa and
                                                                                  s.subo_cod_sucu = $idsucursal ", false, 170, 150, true);
            $ifu->AgregarCampoListaSQL(
                'unidad',
                'Unidad|left',
                "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa order by unid_nom_unid",
                false,
                170,
                150,
                true
            );
            $ifu->AgregarCampoNumerico('costo', 'Costo|LEFT', true, 0, 50, 40, true);

            $op = '';
            unset($_SESSION['aDataGird']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioReportePrueba", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $fu->AgregarCampoOculto('tipo_logistica', 'Tipo logÃ­stica');
            $fu->cCampos["tipo_logistica"]->xValor = 'L';
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;
            $ifu->cCampos["empresa"]->xValor = $idempresa;
            $ifu->cCampos["bodega"]->xValor = 1;

            // SUCURSAL SOLICITUD DE REQUISICION
            if ($id_req != 0) {
                $ifu->cCampos["sucursal"]->xValor = $requ_sucu;
            }

            $ifu->AgregarCampoTexto('detalle', 'Uso o Detalle|left', false, '', 170, 200, true);

            $fu->AgregarCampoListaSQL('tipo', 'Tipo|left', "select tpro_cod_tpro, tpro_des_tpro from saetpro where tpro_cod_empr = $idempresa and tpro_est_sn = 'S' order by tpro_des_tpro", true, 170, 150, true);

            //ADJUNTOS
            $ifu->AgregarCampoArchivo('archivo', 'Archivo|LEFT', false, '', 100, 100, '');


            break;
    }



    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------
    $sql_empresa_presupuesto = "SELECT empr_con_pres from saeempr where empr_cod_empr = $idempresa";
    $empr_con_pres = consulta_string($sql_empresa_presupuesto, 'empr_con_pres', $oIfx, '');
    if ($empr_con_pres == 'S') {

        // SQL PARA TRAER EL PRESUPUESTO DE LA SEMANA ACTUAL; EL PRESUPUESTO ANUAL Y FECHA INICIO Y FIN DE SEMANA
        $numeroSemana = date("W");
        $sql_presupuesto_compras = "SELECT presupuesto_semana, fecha_ini, fecha_fin, presupuesto_general from presupuesto_compras where id_empresa = $idempresa and id_sucursal = $idsucursal and semana = $numeroSemana";
        $presupuesto_general = 0;
        $presupuesto_semana = 0;
        $fecha_ini = '';
        $fecha_fin = '';
        if ($oIfx->Query($sql_presupuesto_compras)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $presupuesto_general = $oIfx->f('presupuesto_general');
                    $presupuesto_semana = $oIfx->f('presupuesto_semana');
                    $fecha_ini = $oIfx->f('fecha_ini');
                    $fecha_fin = $oIfx->f('fecha_fin');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        if ($presupuesto_general > 0) {
            // SQL PARA TRAER LOS PEDIDOS YA REALIZADOS Y VERIFICAR SI EL COSTO APLICA O NO APLICA
            $sql_pedidos_semana = "SELECT  
                                            SUM((dped_can_ped * dped_prc_dped)) as costo_total 
                                        from 
                                            saedped
                                        where dped_cod_pedi in (
                                            select pedi_cod_pedi from saepedi where pedi_fec_pedi BETWEEN '$fecha_ini' and '$fecha_fin'
                                        )";
            $costo_total_pedidos = round(consulta_string($sql_pedidos_semana, 'costo_total', $oIfx, 0), 2);
        }


        // CALCULOS REFERENTES AL PRESUPUESTO DE COMPRAS
        $saldo_disponible = round($presupuesto_semana - $costo_total_pedidos, 2);
    }
    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------







    $sHtml .= '<div class="section-card section-card--main" id="tarjetaPrincipal">';
    $sHtml .= '<div class="section-card__header section-card__header--with-actions">';
    $sHtml .= '<h3 class="section-card__title"><i class="fa fa-file-text-o"></i> Pedido de Compra</h3>';
    $sHtml .= '<div class="section-card__actions">
                    <button type="button" class="btn btn-primary btn-sm btn-accion-superior" onclick="genera_formulario();">
                        <span class="glyphicon glyphicon-file"></span>
                        Nuevo
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-accion-superior" id="btnPrimeroPedido" title="Primer pedido" onclick="navegarPedido(\'primero\');">
                        <i class="fa fa-angle-double-left"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm btn-accion-superior" id="btnAnteriorPedido" title="Pedido anterior" onclick="navegarPedido(\'anterior\');">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm btn-accion-superior" id="btnSiguientePedido" title="Pedido siguiente" onclick="navegarPedido(\'siguiente\');">
                        <i class="fa fa-arrow-right"></i>
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-accion-superior" id="btnUltimoPedido" title="Ãšltimo pedido" onclick="navegarPedido(\'ultimo\');">
                        <i class="fa fa-angle-double-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm btn-accion-superior" id="btnEditarPedido" onclick="javascript:editar_pedido();" >
                        <span class="glyphicon glyphicon-pencil"></span>
                        Editar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm btn-accion-superior" id="btnDuplicarPedido" onclick="duplicarPedido();">
                        <i class="fa fa-copy"></i>
                        Duplicar
                    </button>
                    <button type="button" id ="btnGuardarPedidoSuperior" class="btn btn-success btn-sm btn-accion-superior" onclick="guardar_pedido( );">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        Guardar
                    </button>
                    <button type="button" id ="btnActualizarPedidoSuperior" class="btn btn-primary btn-sm btn-accion-superior" onclick="guardar_pedido( );">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        Actualizar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm btn-accion-superior btn-imprimir-pedido" id="btnImprimirPedido" title="Vista previa solicitud" onclick="javascript:vista_previa();">
                        <span class="glyphicon glyphicon-print"></span> Imprimir
                    </button>
                    <div class="input-group input-group-sm" style="max-width:200px; display:inline-flex; margin-left:8px;">
                        <input type="text" class="form-control" id="busquedaPedido" placeholder="Nro. pedido" onkeydown="buscarPedidoPorNumero(event);">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="abrirModalPedidos();" title="Buscar pedido">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </div>';
    $sHtml .= '</div>';

    $sHtml .= '<div class="section-card__body section-card__body--stacked">';

    $sHtml .= '<div class="section-card section-card--nested">
                    <div class="section-card__header">
                        <h4 class="section-card__title"><i class="fa fa-info-circle"></i> Cabecera</h4>
                    </div>
                    <div class="section-card__body section-card__body--spaced">
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('nota_compra') . $ifu->ObjetoHtml('nota_compra') . '</div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('empresa') . $ifu->ObjetoHtml('empresa') . '</div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('sucursal') . $ifu->ObjetoHtml('sucursal') . '</div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $fu->ObjetoHtmlLBL('tipo') . $fu->ObjetoHtml('tipo') . '</div>
                            </div>
                        </div>

                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Fecha de Pedido:</label>
                                    <input type="date" id="fecha_pedido" name="fecha_pedido"  step="1" value="' . date("Y-m-d") . '" class="form-control datepicker" onchange="actualizar_presupuesto()">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Fecha de Entrega:</label>
                                    <input type="date" id="fecha_entrega" name="fecha_entrega" step="1" value="' . date("Y-m-d", strtotime('+7 days')) . '" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="area">* Area:</label>
                                    <select id="area" name="area" class="form-control select2" required>
                                        ' . $optionArea . '
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="pedi_pri_pedi">* Prioridad</label>
                                    <select id="pedi_pri_pedi" name="pedi_pri_pedi" class="form-control" required>
                                        ' . $prioridadOpciones . '
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('solicitado') . $ifu->ObjetoHtml('solicitado') . '</div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('lugar') . $ifu->ObjetoHtml('lugar') . '</div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="motivo">Motivo</label>
                                    <textarea id="motivo"
                                            name="motivo"
                                            class="form-control"
                                            rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="observaciones">Observaciones o Descripcion</label>
                                    <textarea id="observaciones"
                                            name="observaciones"
                                            class="form-control"
                                            rows="2"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="row" style="margin-bottom: 10px;">
                        <!-- MOTIVO 
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="motivo">Motivo</label>
                                <textarea id="motivo"
                                        name="motivo"
                                        class="form-control"
                                        rows="4"></textarea>
                            </div>
                        </div>-->

                        <!-- DESCRIPCIÃ“N / OBSERVACIONES 
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="observaciones">Observaciones o Descripcion</label>
                                <textarea id="observaciones"
                                        name="observaciones"
                                        class="form-control"
                                        rows="4"></textarea>
                            </div>
                        </div>-->
                    </div>
                    <div class="row" style="display:none;">
                            <div class="col-sm-12 col-md-8">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('cliente_nombre') . '
                                    <div class="input-group">
                                        ' . $ifu->ObjetoHtml('cliente_nombre') . '
                                        <span class="input-group-btn">
                                            <input type="button" value="OK" onClick="javascript:cargar_prove()" style="width:45px; background-color:#EBEBEB" id="BuscaBtnCL" class="btn btn-default" />
                                        </span>
                                        <span class="input-group-btn">
                                            <span class="btn btn-link" style="text-decoration: underline;" onclick="cargar_portafolio(' . $idempresa . ', ' . $idsucursal . ');">[PRODUCTOS]</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">' . $ifu->ObjetoHtmlLBL('ruc') . $ifu->ObjetoHtml('ruc') . '</div>
                            </div>
                    </div>
    ';

    if ($empr_con_pres == 'S') {
        $sHtml .= '<div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-3 col-sm-6">
                            <label for="empresa">Presupuesto Semanal:</label><br>
                            <label id="div_presupuesto_semana">' . $presupuesto_semana . '</label>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="empresa">Presupuesto Utilizado:</label><br>
                            <label id="div_costo_total_pedidos">' . $costo_total_pedidos . '</label>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="empresa">Presupuesto Anual:</label><br>
                            <label id="div_presupuesto_general">' . $presupuesto_general . '</label>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="empresa">Saldo Disponible:</label><br>
                            <label id="div_saldo_disponible">' . $saldo_disponible . '</label>
                            <input type="number" class="form-control" id="saldo_disponible_input" name="saldo_disponible_input" value="' . $saldo_disponible . '" style="display: none">
                        </div>
                    </div>';
    }

    $sHtml .= '            </div>
                </div>';

    $sHtml .= '<div class="section-card section-card--nested">
                    <div class="section-card__header section-card__header--with-actions">
                        <h4 class="section-card__title"><i class="fa fa-check-square-o"></i> Aprobaciones</h4>
                        <div class="section-card__actions" id="accionesAprobaciones">
                            <label class="checkbox-inline" style="margin-right:10px;" id="opcionAprobaciones">
                                <input type="checkbox" id="omitirAprobaciones" onchange="toggleOmitirAprobaciones(this.checked);"> No enviar aprobadores
                            </label>
                            <button type="button" class="btn btn-default btn-sm" onclick="toggleSeccionAprobaciones();" title="Mostrar u ocultar aprobaciones">
                                <i id="iconoToggleAprobaciones" class="fa fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnAbrirGestionAprobaciones" onclick="abrirModalAprobaciones();">
                                <i class="fa fa-user-plus"></i> Agregar aprobacion
                            </button>
                        </div>
                    </div>
                    <div class="section-card__body" id="cuerpoSeccionAprobaciones">
                        <p class="text-muted" style="margin-bottom: 15px;">Gestiona los aprobadores de la solicitud o marca la casilla para omitir enviados.</p>
                        <div class="alert alert-info" id="avisoAprobacionesDesactivadas" style="display:none; margin-bottom:15px;">
                            Esta solicitud no se enviarÃ¡ a aprobadores.
                        </div>
                        <input type="hidden" id="aprobadoresSeleccionadosCampo" name="aprobadoresSeleccionadosCampo" value="" />
                        <input type="hidden" id="omitirAprobacionesCampo" name="omitirAprobacionesCampo" value="0" />
                        <div id="contenedorFirmasAprobadores"></div>
                    </div>
                </div>';

    $sHtml .= $op;

    $sHtml .= '<div class="section-card section-card--nested">
                    <div class="section-card__header section-card__header--with-actions">
                        <h4 class="section-card__title"><i class="fa fa-cubes"></i> Productos</h4>
                        <div class="section-card__actions">
                            <button class="btn btn-primary btn-sm" type="button" onClick="modal_cargar_archivo()">
                                <span class="glyphicon glyphicon-th-list"></span> Cargar por archivo
                            </button>
                        </div>
                    </div>
                    <div class="section-card__body section-card__body--spaced">
                        <div class="producto-form-panel producto-layout" style="background: #f8f9fb; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 15px;">
                            <div class="row fila-producto">
                                <div class="col-xs-12">
                                    <div class="producto-no-registrado-alerta">
                                        <label class="producto-no-registrado-label">
                                            <input type="checkbox" id="producto_no_registrado" name="producto_no_registrado" onchange="toggleProductoNoRegistrado(this.checked);" style="margin-right:8px;"> <strong>Producto no registrado</strong> (Ingrese manualmente los datos)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row fila-producto" id="filaProductoRegistrado">
                                <div class="col-xs-12 col-sm-3 col-md-3" id="columnaBodega">
                                    <div class="form-group">' . $ifu->ObjetoHtmlLBL('bodega') . $ifu->ObjetoHtml('bodega') . '</div>
                                </div>
                                <div class="col-xs-12 col-sm-5 col-md-4" id="camposProductoEstandar">
                                    <div class="form-group">' . $ifu->ObjetoHtmlLBL('producto') . '
                                        <div class="input-group">
                                            ' . $ifu->ObjetoHtml('producto') . '
                                            <span class="input-group-btn">
                                                <button id="botonBuscarProducto" class="btn btn-primary btn-sm" title="Buscar" type="button" value="Buscar Producto" onClick="javascript:cargar_prod_nom(2)">
                                                    <i class="glyphicon glyphicon-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-3" id="columnaCodigoProducto">
                                    <div class="form-group">' . $ifu->ObjetoHtmlLBL('codigo_producto') . $ifu->ObjetoHtml('codigo_producto') . '</div>
                                </div>
                                <div class="col-xs-12 col-sm-2 col-md-2" id="slotCantidadRegistrado">
                                    <div class="form-group" id="contenedorCantidad">' . $ifu->ObjetoHtmlLBL('cantidad') . $ifu->ObjetoHtml('cantidad') . '</div>
                                </div>
                                <div class="col-xs-12" style="display:none;">
                                    ' . $ifu->ObjetoHtml('costo') . '
                                </div>
                            </div>

                            <div class="row fila-producto" id="filaProductoNoRegistrado" style="display:none;">
                                <div class="col-xs-12 col-sm-4 col-md-4" id="camposProductoNoRegistrado">
                                    <div class="form-group">
                                        <label for="codigo_auxiliar">Codigo auxiliar</label>
                                        <input type="text" class="form-control" id="codigo_auxiliar" name="codigo_auxiliar" maxlength="50" placeholder="Ingrese codigo auxiliar">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-5 col-md-4">
                                    <div class="form-group">
                                        <label for="descripcion_auxiliar">Nombre</label>
                                        <input type="text" class="form-control" id="descripcion_auxiliar" name="descripcion_auxiliar" maxlength="150" placeholder="Describa el producto no registrado">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-3 col-md-4" id="slotCantidadNoRegistrado"></div>
                               
                            </div>

                           


                            <div class="row fila-producto" id="filaDetalleProducto">
    <div class="col-xs-12 col-sm-7 col-md-7">
        <div class="form-group">
            ' . $ifu->ObjetoHtmlLBL('detalle') . $ifu->ObjetoHtml('detalle') . '
        </div>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3" id="slotUnidadRegistrado">
        <div class="form-group" id="contenedorUnidad">
            ' . $ifu->ObjetoHtmlLBL('unidad') . $ifu->ObjetoHtml('unidad') . '
        </div>
    </div>

    <div class="col-xs-12 col-sm-2 col-md-2">
        <div class="form-group" id="slotArchivoPrincipal">
            <div id="wrapperArchivo">
                ' . $ifu->ObjetoHtmlLBL('archivo') . $ifu->ObjetoHtml('archivo') . '
                <small class="help-block">Cargar por archivo</small>
            </div>
        </div>
    </div>
</div>

<div class="row fila-producto">
    <div class="col-xs-12 text-right">
        <button class="btn btn-success btn-lg btn-agregar-producto"
                type="button"
                onclick="cargar_producto();">
            <i class="glyphicon glyphicon-plus"></i> Agregar
        </button>
    </div>
</div>




                        </div>
                        <div class="section-card section-card--secondary productos-agregados-card">
                            <div class="section-card__header">
                                <h4 class="section-card__title"><i class="fa fa-list-alt"></i> Productos agregados</h4>
                            </div>
                            <div class="section-card__body section-card__body--spaced">
                                <div id="divFormularioDetalle"></div>
                                <div id="divTotal"></div>
                                <div id="divFormularioTotal" class="table-responsive"></div>
                                <div id="divFormularioDetalle2" class="table-responsive" style="margin-bottom: 120px;"></div>
                            </div>
                        </div>
                        <div class="text-right acciones-guardado-productos" style="margin: 20px 5px;">
                            <button type="button" id ="btnGuardarPedidoInferior" class="btn btn-success btn-lg" onclick="guardar_pedido( );">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                Guardar
                            </button>
                            <button type="button" id ="btnActualizarPedidoInferior" class="btn btn-primary btn-lg" onclick="guardar_pedido( );">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>';

    $sHtml .= '</div>';
    $sHtml .= '<div style="display:none">' . $fu->ObjetoHtml('ctrl') . '</div>';
    $sHtml .= '</div>';
    $sHtml .= '</fieldset>';




    // ------------------------------------------------------------------------------------------
    // Cargar productos del modulo punto reorden 
    // ------------------------------------------------------------------------------------------

    $productos_prod_reorden = $_SESSION['CodigosRecetaReorden'];

    if (!empty($productos_prod_reorden)) {
        $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');

        $lista_producto_cantidad_array = explode("]", $productos_prod_reorden);
        foreach ($lista_producto_cantidad_array as $key => $lista_producto_cantidad) {


            $lista_producto_cantidad_array2 = explode(",", $lista_producto_cantidad);
            $cod_prod = $lista_producto_cantidad_array2[0];
            $cantidad = $lista_producto_cantidad_array2[1];
            $cod_bode = $lista_producto_cantidad_array2[2];




            if (!empty($cod_prod)) {
                $sql_ultimo_costo = "SELECT prbo_uco_prod, prbo_cod_prod, prbo_cta_inv, prbo_cta_ideb
                                        from saeprbo 
                                        where prbo_cod_prod = '$cod_prod'
                                        and prbo_cod_bode = $cod_bode
                                        ";
                $prbo_uco_prod = consulta_string_func($sql_ultimo_costo, 'prbo_uco_prod', $oIfxB, 0);
                $prbo_cod_prod = consulta_string_func($sql_ultimo_costo, 'prbo_cod_prod', $oIfxB, 0);
                $prbo_cta_inv = consulta_string_func($sql_ultimo_costo, 'prbo_cta_inv', $oIfxB, '');
                $prbo_cta_ideb = consulta_string_func($sql_ultimo_costo, 'prbo_cta_ideb', $oIfxB, '');
                $costo_prod = $prbo_uco_prod;


                $descuento = 0;
                $descuento_2 = 0;
                $descuento_general = 0;
                $iva = 0;
                $cuenta_inv = $prbo_cta_inv;
                $cuenta_iva = $prbo_cta_ideb;
                $detalle = 'PRODUCTOS PEDIDO DE COMPRAS';
                $ccosn = '';



                // Existe producto bodega
                if (!empty($prbo_cod_prod)) {

                    // saeprod

                    $sql = "SELECT  p.prod_cod_prod,   pr.prbo_cod_unid,  COALESCE(pr.prbo_iva_porc,0) as prbo_iva_porc   ,
                                    COALESCE(pr.prbo_ice_porc,0) as prbo_ice_porc,
                                    COALESCE( pr.prbo_dis_prod,0 ) as stock, prod_cod_tpro,
                                    p.prod_nom_prod
                                    from saeprod p, saeprbo pr where
                                    p.prod_cod_prod  = pr.prbo_cod_prod and
                                    p.prod_cod_empr  = $idempresa and
                                    p.prod_cod_sucu  = $idsucursal and
                                    pr.prbo_cod_empr = $idempresa and
                                    pr.prbo_cod_bode = $cod_bode and
                                    p.prod_cod_prod  = '$cod_prod' ";





                    if ($oIfxB->Query($sql)) {
                        if ($oIfxB->NumFilas() > 0) {
                            $idproducto = $oIfxB->f('prod_cod_prod');
                            $prod_nom = $oIfxB->f('prod_nom_prod');
                            $idunidad   = $oIfxB->f('prbo_cod_unid');
                        } else {
                            $idproducto = '';
                            $prod_nom = '';
                            $idunidad   = '';
                        }
                    }
                    $oIfxB->Free();


                    // TOTAL
                    $total_fac  = 0;
                    $dsc1       = ($costo_prod * $cantidad * $descuento) / 100;
                    $dsc2       = ((($costo_prod * $cantidad) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_general > 0) {
                        // descto general
                        $dsc3           = ((($costo_prod * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                        $total_fact_tmp = ((($costo_prod * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp            = ((($costo_prod * $cantidad) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp = ((($costo_prod * $cantidad) - ($dsc1 + $dsc2)));
                        $tmp            = $total_fact_tmp;
                    }

                    $total_fac = round($total_fact_tmp, 2);

                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                    } else {
                        $total_con_iva = $total_fac;
                    }


                    $cont = count($aDataGrid);

                    // cantidad
                    $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                    $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo_prod, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                    // centro dï¿½ costo
                    $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, '', 100, 100, true);
                    $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');

                    // busqueda
                    $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                title = "Presione aqui para Buscar Centro Costo"
                                style="cursor: hand !important; cursor: pointer !important;"
                                onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                align="bottom" />';


                    $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                    $aDataGrid[$cont][$aLabelGrid[1]] = $cod_bode;
                    $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
                    $aDataGrid[$cont][$aLabelGrid[3]] = $prod_nom;
                    $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                    $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;  //$cantidad;
                    $aDataGrid[$cont][$aLabelGrid[6]] = $costo_prod; //costo;
                    $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad'); //iva
                    $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo'); // desc1
                    $aDataGrid[$cont][$aLabelGrid[9]] = round($cantidad * $costo_prod, 2); // dec2
                    $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                        onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                        onMouseOut="javascript:nd(); return true;"
                                                                                        title = "Presione aqui para Eliminar"
                                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                                        onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                        alt="Eliminar"
                                                                                        align="bottom" />';
                    $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;;
                    $aDataGrid[$cont][$aLabelGrid[12]] = $detalle;
                    $aDataGrid[$cont][$aLabelGrid[13]] = '';
                    $aDataGrid[$cont][$aLabelGrid[14]] = '';
                }
            }
        }
    }




    $_SESSION['aDataGird'] = $aDataGrid;
    $sHtml_data_prod = mostrar_grid($idempresa);
    $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml_data_prod);
    $oReturn->script('totales_comp()');
    $oReturn->script('cerrar_ventana();');


    // ------------------------------------------------------------------------------------------
    // FIN Cargar productos del modulo punto reorden 
    // ------------------------------------------------------------------------------------------


    $oReturn->assign("divFormularioCabecera", "innerHTML", $sHtml);
    $oReturn->assign("divReporte", "innerHTML", "");
    $oReturn->assign("divAbono", "innerHTML", "");
    $oReturn->assign("cliente_nombre", "placeholder", "ESCRIBA NOMBRE DEL PROVEEDOR Y PRESIONE ENTER O F4 ....");
    $oReturn->assign("producto", "placeholder", "ESCRIBA EL PRODUCTO Y PRESIONE ENTER O F4 ....");
    $oReturn->assign("codigo_producto", "placeholder", "ESCRIBA EL CODIGO Y PRESIONE ENTER O F4....");
    if (!empty($codigoGenerado)) {
        $oReturn->script("try{document.getElementById('nota_compra').placeholder='Estimado: $codigoGenerado';}catch(e){}");
        $oReturn->script("console.log('Codigo informativo sugerido para el nuevo pedido', '$codigoGenerado');");
    }
    $oReturn->script("try{var campoNota=document.getElementById('nota_compra');if(campoNota){campoNota.setAttribute('inputmode','numeric');campoNota.setAttribute('pattern','[0-9]*');}}catch(e){}");
    $oReturn->assign("cliente_nombre", "focus()", "");
    $oReturn->script("establecerEstadoFormulario('creando');");
    $oReturn->script("toggleOmitirAprobaciones(false);");
    $oReturn->script('configurarSeccionAprobaciones();');
    $oReturn->script('ajustarCampoTipo();');
    $oReturn->script('configurarCamposMultilinea();');
    $oReturn->script('configurarCamposProducto();');
    $oReturn->script('toggleProductoNoRegistrado(false);');

    ///VALIDAICON EDIICON DE PEDIDO MODAL
    if ($cod_sol != 0) {
        $oReturn->script("editaPedido('$cod_sol',$empr_sol, $sucu_sol)");
    }
    //FIN VALIDACION EDICION PEDIDO MODAL

    //VALIDACION PROCESO SOLICITUD DE MATERIALES

    if ($id_req != 0) {
        $oReturn->script("carga_detalle_pedido_req();");
    }
    return $oReturn;
}

function actualizar_presupuesto($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $idempresa = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];


    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------
    $sql_empresa_presupuesto = "SELECT empr_con_pres from saeempr where empr_cod_empr = $idempresa";
    $empr_con_pres = consulta_string($sql_empresa_presupuesto, 'empr_con_pres', $oIfx, '');
    if ($empr_con_pres == 'S') {

        // SQL PARA TRAER EL PRESUPUESTO DE LA SEMANA ACTUAL; EL PRESUPUESTO ANUAL Y FECHA INICIO Y FIN DE SEMANA
        // $numeroSemana = date("W");
        $fecha_pedido = new DateTime($aForm['fecha_pedido']);
        $numeroSemana = $fecha_pedido->format('W');

        $sql_presupuesto_compras = "SELECT presupuesto_semana, fecha_ini, fecha_fin, presupuesto_general from presupuesto_compras where id_empresa = $idempresa and id_sucursal = $idsucursal and semana = $numeroSemana";
        $presupuesto_general = 0;
        $presupuesto_semana = 0;
        $fecha_ini = '';
        $fecha_fin = '';
        if ($oIfx->Query($sql_presupuesto_compras)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $presupuesto_general = $oIfx->f('presupuesto_general');
                    $presupuesto_semana = $oIfx->f('presupuesto_semana');
                    $fecha_ini = $oIfx->f('fecha_ini');
                    $fecha_fin = $oIfx->f('fecha_fin');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        // SQL PARA TRAER LOS PEDIDOS YA REALIZADOS Y VERIFICAR SI EL COSTO APLICA O NO APLICA
        if ($presupuesto_general > 0) {
            $sql_pedidos_semana = "SELECT  
                                    SUM((dped_can_ped * dped_prc_dped)) as costo_total 
                                from 
                                    saedped
                                where dped_cod_pedi in (
                                    select pedi_cod_pedi from saepedi where pedi_fec_pedi BETWEEN '$fecha_ini' and '$fecha_fin'
                                )";
            $costo_total_pedidos = round(consulta_string($sql_pedidos_semana, 'costo_total', $oIfx, 0), 2);
        }

        // CALCULOS REFERENTES AL PRESUPUESTO DE COMPRAS
        $saldo_disponible = round($presupuesto_semana - $costo_total_pedidos, 2);
    }
    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------


    $oReturn->assign("div_presupuesto_semana", "innerHTML", $presupuesto_semana);
    $oReturn->assign("div_costo_total_pedidos", "innerHTML", $costo_total_pedidos);
    $oReturn->assign("div_presupuesto_general", "innerHTML", $presupuesto_general);
    $oReturn->assign("div_saldo_disponible", "innerHTML", $saldo_disponible);

    return $oReturn;
}

/* * ************************************* */
/* DF01 :: G U A R D A      P E D I D O */
/* * ************************************* */

function normalizar_numero_pedido($pedido)
{
    $soloDigitos = preg_replace('/[^0-9]/', '', (string)$pedido);
    $normalizado = ltrim($soloDigitos, '0');

    return $normalizado === '' ? '0' : $normalizado;
}

function variantes_numero_pedido($pedido)
{
    $normalizado = normalizar_numero_pedido($pedido);
    $padded = str_pad($normalizado, 9, '0', STR_PAD_LEFT);

    return array_values(array_unique(array($normalizado, $padded)));
}

function guardar_aprobadores_pedido($oCnx, $pedido, $empresa, $sucursal, $aprobadoresJson)
{
    if (empty($pedido) || empty($empresa) || empty($sucursal)) {
        return;
    }

    $pedidoVariantes = variantes_numero_pedido($pedido);

    $aprobadores = json_decode($aprobadoresJson, true);
    if (!is_array($aprobadores)) {
        $aprobadores = array();
    }

    $pedidoDb = str_replace("'", "''", normalizar_numero_pedido($pedido));
    $pedidosDb = array_map(function ($codigo) {
        return str_replace("'", "''", $codigo);
    }, $pedidoVariantes);
    $pedidoCondicion = "'" . implode("','", $pedidosDb) . "'";

    $oCnx->QueryT("DELETE FROM comercial.aprobador_pedido WHERE pedido IN ($pedidoCondicion) AND empresa = $empresa AND sucursal = $sucursal;");
    $rolesRegistrados = array();
    $aprobadoresOrdenados = array();

    foreach ($aprobadores as $indice => $aprobador) {
        $aprobadorId = isset($aprobador['id']) ? str_replace("'", "''", strtoupper($aprobador['id'])) : '';
        $aprobadorNombre = isset($aprobador['nombre']) ? str_replace("'", "''", strtoupper($aprobador['nombre'])) : '';
        $cargoId = isset($aprobador['grupoId']) && is_numeric($aprobador['grupoId']) ? intval($aprobador['grupoId']) : 'NULL';
        $cargoNombre = '';
        $enviar = isset($aprobador['enviar']) && $aprobador['enviar'] === false ? 'N' : 'S';
        $ordenAprobador = isset($aprobador['orden']) ? intval($aprobador['orden']) : ($indice + 1);

        if (!empty($aprobador['cargo'])) {
            $cargoNombre = $aprobador['cargo'];
        } elseif (!empty($aprobador['grupoNombre'])) {
            $cargoNombre = $aprobador['grupoNombre'];
        }

        $cargoNombre = str_replace("'", "''", strtoupper($cargoNombre));
        if (empty($aprobadorId) && empty($aprobadorNombre)) {
            continue;
        }

        $claveRol = !empty($aprobadorId) ? 'APROBADOR_' . $aprobadorId : 'APROBADOR_' . $aprobadorNombre . '_' . $cargoId . '_' . $cargoNombre;
        if (isset($rolesRegistrados[$claveRol])) {
            continue;
        }

        $rolesRegistrados[$claveRol] = true;

        $aprobadoresOrdenados[] = array(
            'aprobadorId' => $aprobadorId,
            'aprobadorNombre' => $aprobadorNombre,
            'cargoId' => $cargoId,
            'cargoNombre' => $cargoNombre,
            'enviar' => $enviar,
            'orden' => $ordenAprobador > 0 ? $ordenAprobador : ($indice + 1)
        );
    }

    usort($aprobadoresOrdenados, function ($a, $b) {
        return ($a['orden'] ?? PHP_INT_MAX) <=> ($b['orden'] ?? PHP_INT_MAX);
    });

    foreach ($aprobadoresOrdenados as $indice => $aprobador) {
        $ordenPersistido = $indice + 1;
        $aprobadorId = $aprobador['aprobadorId'];
        $aprobadorNombre = $aprobador['aprobadorNombre'];
        $cargoId = $aprobador['cargoId'];
        $cargoNombre = $aprobador['cargoNombre'];
        $enviar = $aprobador['enviar'];

        $sqlInsert = "INSERT INTO comercial.aprobador_pedido (empresa, sucursal, pedido, aprobador_id, aprobador_nombre, cargo_id, cargo_nombre, enviar, orden)"
            . " VALUES ($empresa, $sucursal, '$pedidoDb', '$aprobadorId', '$aprobadorNombre', $cargoId, '$cargoNombre', '$enviar', $ordenPersistido);";
        $oCnx->QueryT($sqlInsert);
    }
}
function guarda_pedido($opcion_tmp, $aForm = '', $idReq = 0)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    //      VARIABLES
    $idempresa = $aForm['empresa'];
    $usuario_ifx = $_SESSION['U_USER_INFORMIX'];
    $usuario_web = $_SESSION['U_ID'];
    $sucursal = $aForm['sucursal'];
    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn->script("console.log('Payload recibido para actualizar el pedido', " . json_encode($aForm) . ");");

    $oReturn->script("console.log('Payload recibido para guardar el pedido', " . json_encode($aForm) . ");");


    $contdata = count($aDataGrid);
    $total_compra = $aForm['total_fac'];
    $tipo_logistica = !empty($aForm['tipo_logistica']) ? $aForm['tipo_logistica'] : 'L';
    $tipo_solicitud = $aForm['tipo'];
    $omitirAprobaciones = !empty($aForm['omitirAprobacionesCampo']) && $aForm['omitirAprobacionesCampo'] == '1';
    $valorOmitirAprobaciones = $omitirAprobaciones ? 'S' : 'N';

    //CORREO DE SOLICITANTE

    $sqlcorreo = "select usuario_email, usuario_movil from comercial.usuario where usuario_id=$usuario_web";
    $mail_solicitante    = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');
    $cel_user_solicitante    = consulta_string_func($sqlcorreo, 'usuario_movil', $oCon, '');



    // CONTROL DEL VALOR DE LA COMPRA
    $sql = "select  usua_val_mcom  from saeusua where usua_cod_usua = $usuario_ifx ";
    $val_compra = consulta_string_func($sql, 'usua_val_mcom', $oIfx, 0);

    if ($tipo_logistica == 'M') {
        $val_compra = 10000000;
    }

    if ($contdata > 0) {
        if ($contdata <= 500) {
            if ($total_compra <= $val_compra) {
                // TRANSACCIONALIDAD
                try {

                    // commit
                    $oIfx->QueryT('BEGIN WORK;');
                    // VARIABLEAS
                    $prov = $aForm['cliente'];
                    $prov_nom = $aForm['cliente_nombre'];
                    $ruc = $aForm['ruc'];
                    $fecha_pedido = fecha_informix_func($aForm['fecha_pedido']);
                    $idprdo = (substr($aForm['fecha_pedido'], 5, 2)) * 1;
                    $anio = substr($aForm['fecha_pedido'], 0, 4);
                    $fecha_entrega = fecha_informix_func($aForm['fecha_entrega']);
                    $solicitado = strtoupper($aForm['solicitado']);
                    $motivo = strtoupper($aForm['motivo']);
                    $prioridad = isset($aForm['prioridad']) ? strtoupper(trim($aForm['prioridad'])) : 'MEDIA';
                    if (!in_array($prioridad, array('ALTA','MEDIA','BAJA'))) { $prioridad = 'MEDIA'; }
                    $area = $aForm['area'];
                    $uso = strtoupper($aForm['uso']);
                    $lugar = strtoupper($aForm['lugar']);
                    $observacion = strtoupper($aForm['observaciones']);
                    $prioridad = isset($aForm['pedi_pri_pedi']) ? strtoupper(trim($aForm['pedi_pri_pedi'])) : '';
                    if (!in_array($prioridad, array('ALTA', 'MEDIA', 'BAJA'), true)) {
                        $oReturn->alert('Seleccione una prioridad válida.');
                        $oReturn->assign("ctrl", "value", 1);
                        $oReturn->script("jsRemoveWindowLoad();");
                        return $oReturn;
                    }

                    //CODIGO REFERENCIA PEDIDO ANULADO
                    //$pedi_cod_anu = $aForm['pedi_cod_anu'];
                    $pedi_cod_anu = isset($aForm['pedi_cod_anu']) && $aForm['pedi_cod_anu'] !== ''
                    ? (int)$aForm['pedi_cod_anu']
                    : 0;

                    if (!empty($aForm['pedi_cod_anu'])) {
                        $pedi_cod_anu = (int)$aForm['pedi_cod_anu'];   // número real
                    } else {
                        $pedi_cod_anu = 'NULL';                        // literal SQL
                    }

                    if (empty($prov)) {
                        $prov = 0;
                    }

                    $fecha_ejer = $anio . '-12-31';
                    $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
                    $idejer = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 1);

                    // O B T E N E R     L A     F E C H A      D E L     S E R V I D O R
                    $fecha_servidor = date("Y-m-d");

                    // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
                    $sql2 = "SELECT empl_cod_empl FROM comercial.usuario WHERE usuario_id = $usuario_web ";
                    $empleado = consulta_string_func($sql2, 'empl_cod_empl', $oIfx, '');

                    // formato
                    $sql_formato = "select ftrn_cod_ftrn from saeftrn where
                                                    ftrn_cod_empr = $idempresa and
                                                    ftrn_cod_modu = 10 and
                                                    ftrn_des_ftrn = 'PEDIDO' ";
                    $formato = consulta_string_func($sql_formato, 'ftrn_cod_ftrn', $oIfx, 0);

                    // spreimpreso
                    //$secuencial = pedf_num_preimp($idempresa, $sucursal);

                    $pedi_est_pedi = '0';
                    if ($tipo_logistica == 'M') {
                        //	$pedi_est_pedi = 2;
                    }

                    $sql_maxminv = "select max(pedi_cod_pedi) as maximo from saepedi";
                    $ultimo_id = consulta_string($sql_maxminv, 'maximo', $oIfx, 0)+1;

                    // cabecera SAEPEDi informix
                    $sql_cab = "insert into saepedi( pedi_cod_pedi, pedi_cod_sucu,     pedi_cod_empr,
                                                               pedi_cod_empl,     pedi_cod_clpv,     pedi_cod_ftrn,
                                                               pedi_cod_usua,     pedi_num_prdo,     pedi_cod_ejer,
                                                               pedi_ban_pedi,     pedi_res_pedi,     pedi_det_pedi,
                                                               pedi_fec_pedi,     pedi_fec_entr,     pedi_est_pedi,
                                                               pedi_are_soli,     pedi_lug_entr,     pedi_uso_pedi,
                                                               pedi_des_cons,     pedi_user_web,     pedi_fech_server,
                                                               pedi_tipo_pedi,    pedi_tip_sol,    pedi_cod_anu,     
                                                               pedi_omit_aprob,   pedi_pri_pedi      )
                                                        values( $ultimo_id, $sucursal,         $idempresa,
                                                               '$empleado',       '$prov' ,           '$formato',
                                                                $usuario_ifx,     $idprdo,           $idejer,
                                                                0,                '$solicitado',     '$motivo',
                                                               '$fecha_pedido',   '$fecha_entrega',  '$pedi_est_pedi',
                                                               '$area' ,          '$lugar',          '$uso',
                                                               '$observacion',     $usuario_web,     '$fecha_servidor',
                                                            '$tipo_logistica',  '$tipo_solicitud',  $pedi_cod_anu, '$valorOmitirAprobaciones', '$prioridad') RETURNING pedi_cod_pedi;";
                    $oIfx->QueryT($sql_cab);
                    $secuencial = $oIfx->ResRow['pedi_cod_pedi'];


                    // ingreso a la saedped
                    $x = 1;
                    $j = 0;
                    foreach ($aDataGrid as $aValues) {
                        $bodega = $aValues['Bodega'];
                        $prod = $aValues['Codigo Item'];
                        $unidad = $aValues['Unidad'];
                        $cant  = isset($aForm[$j . '_cantidad']) ? trim($aForm[$j . '_cantidad']) : '0';
                        $costo = isset($aForm[$j . '_costo'])    ? trim($aForm[$j . '_costo'])    : '0';
                        $total = isset($aValues['Total'])        ? trim($aValues['Total'])        : '0';

                        if ($cant === '' || !is_numeric($cant)) {
                            $cant = 0;
                        }
                        if ($costo === '' || !is_numeric($costo)) {
                            $costo = 0;
                        }
                        if ($total === '' || !is_numeric($total)) {
                            $total = 0;
                        }

                        // Si dped_cod_ccos es numérico en la tabla, también conviene normalizarlo:
                        $ccos = isset($aForm[$j . '_ccos']) ? trim($aForm[$j . '_ccos']) : '';
                        $detalleOriginal = isset($aForm[$j . '_det']) ? $aForm[$j . '_det'] : '';
                        $detalleNormalizado = normalizar_detalle_con_saltos($detalleOriginal);
                        $detalle = trim(str_replace("'", "''", $detalleNormalizado));
                        $archivo = isset($aValues['Archivo']) ? $aValues['Archivo'] : '';
                        $cod_dreq = $aValues['Codigo Requisicion'] ?? '';
                        $codigo_auxiliar = trim(str_replace("'", "''", $aValues['Codigo Auxiliar'] ?? ''));
                        $descripcion_auxiliar = trim(str_replace("'", "''", $aValues['Descripcion Auxiliar'] ?? ''));
                        $esAuxiliar = ($aValues['Producto Auxiliar'] ?? 'No') === 'SI';

                        if (!empty($archivo)) {
                            $archivo = substr($archivo, 3);
                        }

                        if ($esAuxiliar && empty($detalle) && !empty($descripcion_auxiliar)) {
                            $detalle = $descripcion_auxiliar;
                        }

                        $cero = 0;
                        //PRODUCTO
                        $sql_prod = "select prod_nom_prod from saeprod where
                                                                        prod_cod_empr = $idempresa and
                                                                        prod_cod_sucu = $sucursal and
                                                                        prod_cod_prod = '$prod' ";
                        $prod_nom_prod = consulta_string_func($sql_prod, 'prod_nom_prod', $oIfx, '');
                        if ($esAuxiliar && !empty($descripcion_auxiliar)) {
                            $prod_nom_prod = $descripcion_auxiliar;
                        }
                        if ($esAuxiliar && !empty($codigo_auxiliar)) {
                            $prod_nom_prod = trim($codigo_auxiliar . ' - ' . $prod_nom_prod);
                        }
                        $prod_nom_prod = trim(str_replace("'", "''", ($prod_nom_prod)));

                        $sql_d = "insert into saedped(dped_cod_dped,    dped_cod_pedi,  dped_cod_prod,
                                                    dped_cod_bode,    dped_cod_sucu,  dped_cod_empr,
                                                    dped_num_prdo,    dped_cod_ejer,  dped_cod_unid,
                                                    dped_can_ped,     dped_can_ent,   dped_can_pen,
                                                    dped_can_apro,
                                                    dped_prc_dped,    dped_ban_dped,  dped_costo_dped,
                                                    dped_tot_dped,    dped_prod_nom,  dped_cod_ccos,
                                                    dped_det_dped, dped_adj_dped, dped_cod_auxiliar, dped_desc_auxiliar) ";

                        $sql_d .= "VALUES (";
                        $sql_d .= " $x,";
                        $sql_d .= " '$secuencial',";
                        $sql_d .= " '$prod',";
                        $sql_d .= " '$bodega',";
                        $sql_d .= " '$sucursal',";
                        $sql_d .= " '$idempresa',";
                        $sql_d .= " '$idprdo',";
                        $sql_d .= " '$idejer',";
                        $sql_d .= " '$unidad',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$cero',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$costo',";
                        $sql_d .= " '$cero',";
                        $sql_d .= " '$costo',";
                        $sql_d .= " '$total',";
                        $sql_d .= " '$prod_nom_prod',";
                        $sql_d .= " '$ccos',";
                        $sql_d .= " '$detalle',";
                        $sql_d .= " '$archivo',";
                        $sql_d .= " '$codigo_auxiliar',";
                        $sql_d .= " '$descripcion_auxiliar' ";
                        $sql_d .= ");";
                        $oIfx->QueryT($sql_d);


                        ///ACTUALIZACION ESTADO SAEDREQ SOLICITUD DE MATERIALES CCDC
                        if ($idReq != 0 && !empty($cod_dreq)) {
                            $sql = "update saedreq set dreq_est_rev = 'S', dreq_cod_pedi=$secuencial, dreq_cod_dped=$x  where
                                                                   dreq_cod_dreq = $cod_dreq";
                            $oIfx->QueryT($sql);
                        }
                        ///FIN ACTULIZACION ESTADO
                        $x++;
                        $j++;
                    }

                    if (!$omitirAprobaciones) {
                        guardar_aprobadores_pedido($oIfx, $secuencial, $idempresa, $sucursal, $aForm['aprobadoresSeleccionadosCampo'] ?? '');
                    } else {
                        guardar_aprobadores_pedido($oIfx, $secuencial, $idempresa, $sucursal, '[]');
                    }

                    $oIfx->QueryT('COMMIT WORK;');
                    $oReturn->alert('Pedido de Compra Ingresado Correctamente....');
                    $oReturn->script("console.log('Pedido guardado correctamente', {codigo: '$secuencial', items: $contdata});");
                    $oReturn->assign("nota_compra", "value", $secuencial);
                    $oReturn->script("setEstadoPendiente('creada');");
                    $oReturn->script("xajax_carga_pedido('$secuencial', $idempresa, $sucursal);");


                    ///METODOS SOLICITUD DE MATERIALES
                    if ($idReq != 0) {

                        $usuario_web = $_SESSION['U_ID'];
                        $fecha = date('Y-m-d H:i:s');

                        $sql = "SELECT id from comercial.aprobaciones_compras 
                                where empresa=$idempresa and estado='S' and tipo_aprobacion = 'SOLCOMPRAS'";
                        $cod_apro = consulta_string($sql, 'id', $oIfx, 0);

                        $sql = "SELECT requ_cod_sucu from saerequ where requ_cod_requ=$idReq";
                        $requ_sucu = consulta_string($sql, 'requ_cod_sucu', $oIfx, 0);

                        ///VALIDACION ESTADO DE LOS ITEMS DE LA SOLCITUD DE MATERIALES

                        $sqldr = "SELECT count(*) as conteo from saedreq where dreq_cod_requ = $idReq and dreq_est_aprob   = 'S'";
                        $items_sol = consulta_string($sqldr, 'conteo', $oIfx, 0);

                        $sqldr = "SELECT count(*) as conteo from saedreq where dreq_cod_requ = $idReq and dreq_est_aprob   = 'S'
                    and dreq_est_rev = 'S'";
                        $items_rev = consulta_string($sqldr, 'conteo', $oIfx, 0);

                        if ($items_sol != 0 && ($items_sol == $items_rev)) {

                            $sqlapro = "INSERT INTO comercial.aprobaciones_solicitud_compra  (empresa, sucursal, id_aprobacion,id_solicitud, usuario, fecha) 
                                    values
                                    ($idempresa, $requ_sucu, $cod_apro, '$idReq', $usuario_web, '$fecha')";
                            $oIfx->QueryT($sqlapro);
                            $oReturn->script("cerrarModales();");
                        } else {

                            $oReturn->script("cerrarModalCompra($idReq, $cod_apro, $idempresa, $requ_sucu );");
                        }
                    }

                    ///FIN METODOS SOLICITUD DE MATERIALES

                    unset($_SESSION['pdf']);

                    //VALIDACION FORMATO PERSONALZIADO SOLICITUD DE COMPRAS

                    $sql = "SELECT ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
                     ftrn_des_ftrn = 'PEDIDO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";

                    $ubi = consulta_string($sql, 'ftrn_ubi_web', $oConA, '');


                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ruta = genera_pdf_doc_comp($secuencial, 1, $idempresa, $sucursal);
                    } else {
                        $html = generar_pedido_compra_pdf($idempresa, $sucursal, $secuencial);
                        $docu = 'documento' . $secuencial . '.pdf';
                        $ruta = DIR_FACTELEC . 'Include/archivos/' . $docu;

                        $html2pdf = new HTML2PDF('P', 'A3', 'fr');
                        $html2pdf->WriteHTML($html);
                        $html2pdf->Output($ruta, 'F');
                    }


                    //CONSULTAMOS EL CODIGO DE LA PRIMERA APROBACION

                    $sql = "SELECT id from comercial.aprobaciones_compras 
                    where empresa=$idempresa and estado='S' and tipo_aprobacion='COMPRAS' order by orden asc limit 1";
                    $cod_apro = consulta_string($sql, 'id', $oCon, 0);

                    //MENSAJE DE CORREO Y WHATSAPP
                    $mensaje = 'Se ha generado la siguiente solicitud <b>N. ' . $secuencial . '</b><br> Requiere su revision y aprobacion';
                    $text_envio = 'Se ha generado la siguiente solicitud *N. ' . $secuencial . '*\nRequiere su revision y aprobacion';

                    // Instanciamos la clase NotificacionesCompras
                    $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $sucursal, $cod_apro, $secuencial, $area, $ruta);

                    // Enviar correo al solicitante
                    $notifier->enviarCorreoSolicitante($mail_solicitante);

                    // Enviar correo a los aprobadores
                    $notifier->enviarCorreoAprobadores($mensaje);

                    // Enviar WhatsApp al solicitante
                    $notifier->enviarWhatsAppSolicitante($cel_user_solicitante);

                    // Enviar WhatsApp a los aprobadores
                    $notifier->enviarWhatsAppAprobadores($text_envio);

                    $productos_prod_reorden = $_SESSION['CodigosRecetaReorden'];
                    if (!empty($productos_prod_reorden)) {
                        $oReturn->script("actualizar_estado_punto_reorden('$secuencial', 'PED', '$fecha_pedido', '$fecha_entrega')");
                    }
                    $oReturn->assign("ctrl", "value", 1);
                    $oReturn->script("jsRemoveWindowLoad();");
                } catch (Exception $e) {
                    // rollback
                    $oIfx->QueryT('ROLLBACK WORK;');
                    $oReturn->alert($e->getMessage());
                    $oReturn->assign("ctrl", "value", 1);
                    $oReturn->script("console.error('Error al guardar el pedido', " . json_encode($e->getMessage()) . ");");
                    $oReturn->script("jsRemoveWindowLoad();");
                }
            } else {
                $oReturn->alert('Sobrepaso el limite de Compra con ' . ($total_compra - $val_compra));
                $oReturn->assign("ctrl", "value", 1);
                $oReturn->script("console.warn('Pedido no guardado: valor supera el lÃ­mite de compra');");
                $oReturn->script("jsRemoveWindowLoad();");
            }
        } else {
            $oReturn->alert('!!!!....Solo puede realizar el Pedido hasta 500 items.....!!!!!');
            $oReturn->assign("ctrl", "value", 1);
            $oReturn->script("jsRemoveWindowLoad();");
        }
    } else {
        $oReturn->alert('!!!!....Por favor selecciona un producto....!!!!!');
        $oReturn->assign("ctrl", "value", 1);
        $oReturn->script("console.warn('No se pudo guardar el pedido porque no hay productos en el detalle');");
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;
}

function actualiza_pedido($id_pedido, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    //      VARIABLES
    $idempresa = $aForm['empresa'];
    $usuario_ifx = $_SESSION['U_USER_INFORMIX'];
    $usuario_web = $_SESSION['U_ID'];
    $sucursal = $aForm['sucursal'];
    $aDataGrid = $_SESSION['aDataGird'];
    $contdata = count($aDataGrid);
    $total_compra = $aForm['total_fac'];
    $tipo_logistica = !empty($aForm['tipo_logistica']) ? $aForm['tipo_logistica'] : 'L';
    $tipo_solicitud = $aForm['tipo'];
    $omitirAprobaciones = !empty($aForm['omitirAprobacionesCampo']) && $aForm['omitirAprobacionesCampo'] == '1';
    $valorOmitirAprobaciones = $omitirAprobaciones ? 'S' : 'N';

    //CORREO DE SOLICITANTE

    $sqlcorreo = "select usuario_email, usuario_movil from comercial.usuario where usuario_id=$usuario_web";
    $mail_solicitante    = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');
    $cel_user_solicitante    = consulta_string_func($sqlcorreo, 'usuario_movil', $oCon, '');


    // CONTROL DEL VALOR DE LA COMPRA
    $sql = "select  usua_val_mcom  from saeusua where usua_cod_usua = $usuario_ifx ";
    $val_compra = consulta_string_func($sql, 'usua_val_mcom', $oIfx, 0);

    if ($tipo_logistica == 'M') {
        $val_compra = 10000000;
    }

    if ($contdata > 0) {
        if ($contdata <= 500) {
            if ($total_compra <= $val_compra) {
                // TRANSACCIONALIDAD
                try {

                    // commit
                    $oIfx->QueryT('BEGIN WORK;');
                    // VARIABLEAS
                    $prov = $aForm['cliente'];
                    $prov_nom = $aForm['cliente_nombre'];
                    $ruc = $aForm['ruc'];
                    $fecha_pedido = fecha_informix_func($aForm['fecha_pedido']);
                    $idprdo = (substr($aForm['fecha_pedido'], 5, 2)) * 1;
                    $anio = substr($aForm['fecha_pedido'], 0, 4);
                    $fecha_entrega = fecha_informix_func($aForm['fecha_entrega']);
                    $solicitado = $aForm['solicitado'];
                    $motivo = $aForm['motivo'];
                    $area = $aForm['area'];
                    $uso = $aForm['uso'];
                    $lugar = $aForm['lugar'];
                    $observacion = $aForm['observaciones'];
                    $prioridad = isset($aForm['pedi_pri_pedi']) ? strtoupper(trim($aForm['pedi_pri_pedi'])) : '';
                    if (!in_array($prioridad, array('ALTA', 'MEDIA', 'BAJA'), true)) {
                        $oReturn->alert('Seleccione una prioridad válida.');
                        $oReturn->assign("ctrl", "value", 1);
                        $oReturn->script("jsRemoveWindowLoad();");
                        return $oReturn;
                    }

                    if (empty($prov)) {
                        $prov = 0;
                    }

                    $fecha_ejer = $anio . '-12-31';
                    $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
                    $idejer = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 1);

                    // O B T E N E R     L A     F E C H A      D E L     S E R V I D O R
                    $fecha_servidor = date("Y-m-d");

                    // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
                    $sql2 = "SELECT empl_cod_empl FROM comercial.usuario WHERE usuario_id = $usuario_web ";
                    $empleado = consulta_string_func($sql2, 'empl_cod_empl', $oIfx, '');

                    // formato
                    $sql_formato = "select ftrn_cod_ftrn from saeftrn where
                                                    ftrn_cod_empr = $idempresa and
                                                    ftrn_cod_modu = 10 and
                                                    ftrn_des_ftrn = 'PEDIDO' ";
                    $formato = consulta_string_func($sql_formato, 'ftrn_cod_ftrn', $oIfx, 0);

                    // spreimpreso
                    // $secuencial = pedf_num_preimp($idempresa, $sucursal);

                    $pedi_est_pedi = '0';
                    if ($tipo_logistica == 'M') {
                        //	$pedi_est_pedi = 2;
                    }



                    $sqlu = "UPDATE saepedi set pedi_fec_pedi='$fecha_pedido', pedi_det_pedi='$motivo',
                        pedi_des_cons='$observacion', pedi_are_soli='$area', pedi_lug_entr='$lugar', pedi_pri_pedi='$prioridad',
                        pedi_fec_entr= '$fecha_entrega', pedi_uso_pedi='$uso', pedi_tipo_pedi='$tipo_logistica', pedi_tip_sol='$tipo_solicitud',
                        pedi_cod_clpv='$prov', pedi_fech_server='$fecha_servidor', pedi_omit_aprob='$valorOmitirAprobaciones', pedi_pri_pedi='$prioridad' where pedi_cod_empr=$idempresa and pedi_cod_sucu=$sucursal
                        and pedi_cod_pedi =  '$id_pedido'";
                    $oIfx->QueryT($sqlu);

                    $secuencial = $id_pedido;

                    //ELIMINAMOS EL DETALLE DEL PEDIDO

                    $sqld = "DELETE from saedped where dped_cod_pedi='$id_pedido' and dped_cod_empr=$idempresa and dped_cod_sucu=$sucursal";
                    $oIfx->QueryT($sqld);

                    // ingreso a la saedped
                    $x = 1;
                    $j = 0;
                    foreach ($aDataGrid as $aValues) {
                        $bodega = $aValues['Bodega'];
                        $prod = $aValues['Codigo Item'];
                        $unidad = $aValues['Unidad'];
                        $cant  = isset($aForm[$j . '_cantidad']) ? trim($aForm[$j . '_cantidad']) : '0';
                        $costo = isset($aForm[$j . '_costo'])    ? trim($aForm[$j . '_costo'])    : '0';
                        $total = isset($aValues['Total'])        ? trim($aValues['Total'])        : '0';

                        if ($cant === '' || !is_numeric($cant)) {
                            $cant = 0;
                        }
                        if ($costo === '' || !is_numeric($costo)) {
                            $costo = 0;
                        }
                        if ($total === '' || !is_numeric($total)) {
                            $total = 0;
                        }

                        // Si dped_cod_ccos es numérico en la tabla, también conviene normalizarlo:
                        $ccos = isset($aForm[$j . '_ccos']) ? trim($aForm[$j . '_ccos']) : '';
                        $detalleOriginal = isset($aForm[$j . '_det']) ? $aForm[$j . '_det'] : '';
                        $detalleNormalizado = normalizar_detalle_con_saltos($detalleOriginal);
                        $detalle = trim(str_replace("'", "''", $detalleNormalizado));
                        $archivo = isset($aValues['Archivo']) ? $aValues['Archivo'] : '';
                        $cod_dreq = $aValues['Codigo Requisicion'] ?? '';
                        $codigo_auxiliar = trim(str_replace("'", "''", $aValues['Codigo Auxiliar'] ?? ''));
                        $descripcion_auxiliar = trim(str_replace("'", "''", $aValues['Descripcion Auxiliar'] ?? ''));
                        $esAuxiliar = ($aValues['Producto Auxiliar'] ?? 'No') === 'SI';

                        if (!empty($archivo)) {
                            $archivo = substr($archivo, 3);
                        }

                        if ($esAuxiliar && empty($detalle) && !empty($descripcion_auxiliar)) {
                            $detalle = $descripcion_auxiliar;
                        }

                        $cero = 0;
                        //PRODUCTO
                        $sql_prod = "select prod_nom_prod from saeprod where
                                                                        prod_cod_empr = $idempresa and
                                                                        prod_cod_sucu = $sucursal and
                                                                        prod_cod_prod = '$prod' ";
                        $prod_nom_prod = consulta_string_func($sql_prod, 'prod_nom_prod', $oIfx, '');
                        if ($esAuxiliar && !empty($descripcion_auxiliar)) {
                            $prod_nom_prod = $descripcion_auxiliar;
                        }
                        if ($esAuxiliar && !empty($codigo_auxiliar)) {
                            $prod_nom_prod = trim($codigo_auxiliar . ' - ' . $prod_nom_prod);
                        }
                        $prod_nom_prod = trim(str_replace("'", "''", ($prod_nom_prod)));

                        $sql_d = "insert into saedped(dped_cod_dped,    dped_cod_pedi,  dped_cod_prod,
                                                    dped_cod_bode,    dped_cod_sucu,  dped_cod_empr,
                                                    dped_num_prdo,    dped_cod_ejer,  dped_cod_unid,
                                                    dped_can_ped,     dped_can_ent,   dped_can_pen,
                                                    dped_can_apro,
                                                    dped_prc_dped,    dped_ban_dped,  dped_costo_dped,
                                                    dped_tot_dped,    dped_prod_nom,  dped_cod_ccos,
                                                    dped_det_dped, dped_adj_dped, dped_cod_auxiliar, dped_desc_auxiliar) ";

                        $sql_d .= "VALUES (";
                        $sql_d .= " $x,";
                        $sql_d .= " '$secuencial',";
                        $sql_d .= " '$prod',";
                        $sql_d .= " '$bodega',";
                        $sql_d .= " '$sucursal',";
                        $sql_d .= " '$idempresa',";
                        $sql_d .= " '$idprdo',";
                        $sql_d .= " '$idejer',";
                        $sql_d .= " '$unidad',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$cero',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$cant',";
                        $sql_d .= " '$costo',";
                        $sql_d .= " '$cero',";
                        $sql_d .= " '$costo',";
                        $sql_d .= " '$total',";
                        $sql_d .= " '$prod_nom_prod',";
                        $sql_d .= " '$ccos',";
                        $sql_d .= " '$detalle',";
                        $sql_d .= " '$archivo',";
                        $sql_d .= " '$codigo_auxiliar',";
                        $sql_d .= " '$descripcion_auxiliar' ";
                        $sql_d .= ");";
                        $oIfx->QueryT($sql_d);
                        $x++;
                        $j++;
                    }

                    if (!$omitirAprobaciones) {
                        guardar_aprobadores_pedido($oIfx, $secuencial, $idempresa, $sucursal, $aForm['aprobadoresSeleccionadosCampo'] ?? '');
                    } else {
                        guardar_aprobadores_pedido($oIfx, $secuencial, $idempresa, $sucursal, '[]');
                    }

                    $oIfx->QueryT('COMMIT WORK;');
                    $oReturn->alert('Pedido de Compra Actualizado Correctamente....');
                    $oReturn->script("console.log('Pedido actualizado correctamente', {codigo: '$secuencial', items: $contdata});");
                    //$oReturn->assign("nota_compra", "value", $secuencial);

                    unset($_SESSION['pdf']);

                    //VALIDACION FORMATO PERSONALZIADO SOLICITUD DE COMPRAS

                    $sql = "SELECT ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
                     ftrn_des_ftrn = 'PEDIDO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";

                    $ubi = consulta_string($sql, 'ftrn_ubi_web', $oConA, '');


                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ruta = genera_pdf_doc_comp($secuencial, 1, $idempresa, $sucursal);
                    } else {
                        $html = generar_pedido_compra_pdf($idempresa, $sucursal, $secuencial);
                        $docu = 'documento' . $secuencial . '.pdf';
                        $ruta = DIR_FACTELEC . 'Include/archivos/' . $docu;

                        $html2pdf = new HTML2PDF('P', 'A3', 'fr');
                        $html2pdf->WriteHTML($html);
                        $html2pdf->Output($ruta, 'F');
                    }


                    //CONSULTAMOS EL CODIGO DE LA PRIMERA APROBACION

                    $sql = "SELECT id from comercial.aprobaciones_compras 
                    where empresa=$idempresa and estado='S' and tipo_aprobacion='COMPRAS' order by orden asc limit 1";
                    $cod_apro = consulta_string($sql, 'id', $oCon, 0);

                    //MENSAJE DE CORREO Y WHATSAPP
                    $mensaje = 'Se ha generado la siguiente solicitud <b>N. ' . $secuencial . '</b><br> Requiere su revision y aprobacion';
                    $text_envio = 'Se ha generado la siguiente solicitud *N. ' . $secuencial . '*\nRequiere su revision y aprobacion';

                    // Instanciamos la clase NotificacionesCompras
                    $notifier = new NotificacionesCompras($oCon, $oConA,  $oReturn, $idempresa, $sucursal, $cod_apro, $secuencial, $area, $ruta);

                    // Enviar correo al solicitante
                    $notifier->enviarCorreoSolicitante($mail_solicitante);

                    // Enviar correo a los aprobadores
                    $notifier->enviarCorreoAprobadores($mensaje);

                    // Enviar WhatsApp al solicitante
                    $notifier->enviarWhatsAppSolicitante($cel_user_solicitante);

                    // Enviar WhatsApp a los aprobadores
                    $notifier->enviarWhatsAppAprobadores($text_envio);


                    $productos_prod_reorden = $_SESSION['CodigosRecetaReorden'];
                    if (!empty($productos_prod_reorden)) {
                        $oReturn->script("actualizar_estado_punto_reorden('$secuencial', 'PED', '$fecha_pedido', '$fecha_entrega')");
                    }

                    $oReturn->assign("ctrl", "value", 1);

                    $oReturn->script("jsRemoveWindowLoad();");
                    $oReturn->script("setEstadoPendiente('creada');");
                    $oReturn->script("xajax_carga_pedido('$secuencial', $idempresa, $sucursal);");
                } catch (Exception $e) {
                    // rollback
                    $oIfx->QueryT('ROLLBACK WORK;');
                    $oReturn->alert($e->getMessage());
                    $oReturn->assign("ctrl", "value", 1);
                    $oReturn->script("console.error('Error al actualizar el pedido', " . json_encode($e->getMessage()) . ");");
                    $oReturn->script("jsRemoveWindowLoad();");
                }
            } else {
                $oReturn->alert('Sobrepaso el limite de Compra con ' . ($total_compra - $val_compra));
                $oReturn->assign("ctrl", "value", 1);
                $oReturn->script("console.warn('Pedido no actualizado: valor supera el lÃ­mite de compra');");
                $oReturn->script("jsRemoveWindowLoad();");
            }
        } else {
            $oReturn->alert('!!!!....Solo puede realizar el Pedido hasta 500 items.....!!!!!');
            $oReturn->assign("ctrl", "value", 1);
            $oReturn->script("jsRemoveWindowLoad();");
        }
    } else {
        $oReturn->alert('!!!!....Por favor selecciona un producto....!!!!!');
        $oReturn->assign("ctrl", "value", 1);
        $oReturn->script("console.warn('No se pudo actualizar el pedido porque no hay productos en el detalle');");
        $oReturn->script("jsRemoveWindowLoad();");
    }

    return $oReturn;
}
/* * ****************************************** */
/*   M O S T R A R     D A T A    G R I D    */
/* * ***************************************** */
function extraer_correlativo_auxiliar($codigo)
{
    if (preg_match('/(\d+)$/', (string) $codigo, $coincidencias)) {
        return (int) $coincidencias[1];
    }

    return 0;
}

function construir_codigo_auxiliar($idempresa, $idsucursal, $fecha, $aDataGrid = array())
{
    $anio = !empty($fecha) ? substr($fecha, 0, 4) : date('Y');
    $prefijo = 'Y' . substr($anio, -2);
    $secuencialBase = (int) pedf_num_preimp($idempresa, $idsucursal);
    $secuencialBase = $secuencialBase > 0 ? $secuencialBase + 1 : 1;

    $codigosExistentes = array();
    if (is_array($aDataGrid)) {
        foreach ($aDataGrid as $fila) {
            if (!empty($fila['Codigo Auxiliar'])) {
                $codigosExistentes[] = extraer_correlativo_auxiliar($fila['Codigo Auxiliar']);
            }
        }
    }

    $correlativo = $secuencialBase;
    while (in_array($correlativo, $codigosExistentes, true)) {
        $correlativo++;
    }

    return $prefijo . ' ' . str_pad($correlativo, 5, '0', STR_PAD_LEFT);
}

function generar_codigo_auxiliar($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    $empresa = isset($aForm['empresa']) ? $aForm['empresa'] : 0;
    $sucursal = isset($aForm['sucursal']) ? $aForm['sucursal'] : 0;
    $fecha = isset($aForm['fecha_pedido']) ? $aForm['fecha_pedido'] : date('Y-m-d');
    $dataGrid = isset($_SESSION['aDataGird']) ? $_SESSION['aDataGird'] : array();

    if ($empresa && $sucursal) {
        $codigo = construir_codigo_auxiliar($empresa, $sucursal, $fecha, $dataGrid);
        $oReturn->assign('codigo_auxiliar', 'value', $codigo);
        $oReturn->assign('codigo_auxiliar', 'placeholder', $codigo);
    }

    return $oReturn;
}

function normalizar_detalle_con_saltos($detalle)
{
    $detalle = (string) $detalle;
    $detalle = str_replace(["\r\n", "\r"], "\n", $detalle);
    $detalle = trim($detalle);
    $detalle = mb_strtoupper($detalle, 'UTF-8');
    $detalle = str_replace('<', '&lt;', $detalle);
    return str_replace("\n", '<br />', $detalle);
}

function restaurar_saltos_linea_guardados($detalle)
{
    $detalle = trim((string) $detalle);
    $detalle = str_replace(['<br />', '<br/>', '<br>'], "\n", $detalle);
    $detalle = str_replace('\\n', "\n", $detalle);
    $detalle = str_replace(["\r\n", "\r"], "\n", $detalle);
    return trim(str_replace('&lt;', '<', $detalle));
}

function formatear_detalle_para_mostrar($detallePlano)
{
    $detallePlano = trim(preg_replace("/[\r\n]+/", "\n", (string) $detallePlano));
    $detallePlano = htmlspecialchars($detallePlano, ENT_QUOTES, 'UTF-8');
    return str_replace("\n", '<br>', $detallePlano);
}

function agrega_modifica_grid($nTipo = 0, $descuento_general = 0, $codigo_prod = '', $aForm = '', $id = '', $cant_update = 0, $bode_up = 0, $costo_up = 0, $ccos_up = '', $detalle_up = '', $unidad_up = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $aDataGrid = $_SESSION['aDataGird'];

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Archivo', 'Codigo Requisicion', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');
    $oReturn = new xajaxResponse();

    $idempresa = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $decimal = 6;
    $prod_nom = '';

    // ------------------------
    // Datos del formulario
    // ------------------------
    $cantidad        = $aForm['cantidad'];
    $codigo_producto = $aForm['codigo_producto'];
    $idbodega        = $aForm['bodega'];
    $costo           = $aForm['costo'];
    $detalle         = normalizar_detalle_con_saltos(strtoupper($aForm['detalle']));
    $archivo_real    = $aForm['archivo'];
    $unidad_form     = isset($aForm['unidad']) ? $aForm['unidad'] : '';

    $codigo_auxiliar      = isset($aForm['codigo_auxiliar']) ? strtoupper(trim($aForm['codigo_auxiliar'])) : '';
    $descripcion_auxiliar = isset($aForm['descripcion_auxiliar']) ? strtoupper(trim($aForm['descripcion_auxiliar'])) : '';

    // El check viene del formulario (checkbox producto_no_registrado)
    $producto_no_registrado = !empty($aForm['producto_no_registrado']);

    // Si es producto no registrado, traemos la config:
    // cod_bode_pedi = bodega por defecto
    // cod_prod_pedi = producto por defecto
    if ($producto_no_registrado) {
        $sql_param = "
        SELECT cod_bode_pedi, cod_prod_pedi
        FROM comercial.parametro_inv
        WHERE empr_cod_empr = $idempresa
          AND sucu_cod_sucu = $idsucursal
        LIMIT 1
    ";
        if ($oCnx->Query($sql_param) && $oCnx->NumFilas() > 0) {
            $idbodega_cfg = $oCnx->f('cod_bode_pedi');
            $codigo_cfg   = $oCnx->f('cod_prod_pedi');

            // Sobrescribimos bodega y codigo de producto con la configuracion
            if (!empty($idbodega_cfg)) {
                $idbodega = $idbodega_cfg;
                $aForm['bodega'] = $idbodega_cfg;
            }
            if (!empty($codigo_cfg)) {
                $codigo_producto = $codigo_cfg;
                $aForm['codigo_producto'] = $codigo_cfg;
            }
        }
        $codigosExistentes = array();
        if (is_array($aDataGrid)) {
            foreach ($aDataGrid as $fila) {
                if (!empty($fila['Codigo Auxiliar'])) {
                    $codigosExistentes[] = extraer_correlativo_auxiliar($fila['Codigo Auxiliar']);
                }
            }
        }

        $codigoSugerido = construir_codigo_auxiliar($idempresa, $idsucursal, $aForm['fecha_pedido'], $aDataGrid);
        $correlativoIngresado = extraer_correlativo_auxiliar($codigo_auxiliar);

        if (empty($codigo_auxiliar) || $correlativoIngresado === 0 || in_array($correlativoIngresado, $codigosExistentes, true)) {
            $codigo_auxiliar = $codigoSugerido;
        }

        $oReturn->assign('codigo_auxiliar', 'value', $codigo_auxiliar);
        $oReturn->assign('codigo_auxiliar', 'placeholder', $codigo_auxiliar);
    }



    if ($nTipo == 1) {
        //actualiza
        $cantidad = $cant_update;
        $codigo_producto = $codigo_prod;
        $idbodega = $bode_up;
        $costo = $costo_up;
        $ccos = $ccos_up;
        $detalle = normalizar_detalle_con_saltos($detalle_up);
        if ($unidad_up !== '') {
            $unidad_form = $unidad_up;
        }
    }

    // saeprod
    $sql = "select  p.prod_cod_prod,   pr.prbo_cod_unid,  COALESCE(pr.prbo_iva_porc,0) as prbo_iva_porc   ,
                    COALESCE(pr.prbo_ice_porc,0) as prbo_ice_porc,
                    COALESCE( pr.prbo_dis_prod,0 ) as stock, prod_cod_tpro
                    from saeprod p, saeprbo pr where
                    p.prod_cod_prod = pr.prbo_cod_prod and
                    p.prod_cod_empr = $idempresa and
                    p.prod_cod_sucu = $idsucursal and
                    pr.prbo_cod_empr = $idempresa and
                    pr.prbo_cod_bode = $idbodega and
                    p.prod_cod_prod = '$codigo_producto' ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $idproducto = $oIfx->f('prod_cod_prod');
            $prod_nom   = strtoupper($oIfx->f('prod_nom_prod'));
            $idunidad   = $oIfx->f('prbo_cod_unid');
        } else {
            $idproducto = '';
            $prod_nom   = '';
            $idunidad   = '';
        }
    }
    $oIfx->Free();

    if ($producto_no_registrado) {
        $idproducto = $codigo_producto;
        $prod_nom = $descripcion_auxiliar ?: $prod_nom;
        if ($codigo_auxiliar) {
            $prod_nom = trim($codigo_auxiliar . ' - ' . $prod_nom);
        }
        if (empty(trim($detalle))) {
            $detalle = $prod_nom;
        }
    }

    if (!empty($unidad_form)) {
        $idunidad = $unidad_form;
    }

    if (empty($idunidad)) {
        $sqlUnidadDefault = "select unid_cod_unid from saeunid where unid_cod_empr = $idempresa order by unid_nom_unid limit 1";
        $idunidad = consulta_string($sqlUnidadDefault, 'unid_cod_unid', $oIfx, $idunidad);
    }



    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------
    $sql_empresa_presupuesto = "SELECT empr_con_pres from saeempr where empr_cod_empr = $idempresa";
    $empr_con_pres = consulta_string($sql_empresa_presupuesto, 'empr_con_pres', $oIfx, '');

    $ingresa_data = 'N';
    if ($empr_con_pres == 'S') {

        // SQL PARA TRAER EL PRESUPUESTO DE LA SEMANA ACTUAL; EL PRESUPUESTO ANUAL Y FECHA INICIO Y FIN DE SEMANA
        // $numeroSemana = date("W");
        $fecha_pedido = new DateTime($aForm['fecha_pedido']);
        $numeroSemana = $fecha_pedido->format('W');

        $sql_presupuesto_compras = "SELECT presupuesto_semana, fecha_ini, fecha_fin, presupuesto_general from presupuesto_compras where id_empresa = $idempresa and id_sucursal = $idsucursal and semana = $numeroSemana";
        $presupuesto_general = 0;
        $presupuesto_semana = 0;
        $fecha_ini = '';
        $fecha_fin = '';
        if ($oIfx->Query($sql_presupuesto_compras)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $presupuesto_general = $oIfx->f('presupuesto_general');
                    $presupuesto_semana = $oIfx->f('presupuesto_semana');
                    $fecha_ini = $oIfx->f('fecha_ini');
                    $fecha_fin = $oIfx->f('fecha_fin');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        // SQL PARA TRAER LOS PEDIDOS YA REALIZADOS Y VERIFICAR SI EL COSTO APLICA O NO APLICA
        if ($presupuesto_general > 0) {
            $sql_pedidos_semana = "SELECT  
                                    SUM((dped_can_ped * dped_prc_dped)) as costo_total 
                                from 
                                    saedped
                                where dped_cod_pedi in (
                                    select pedi_cod_pedi from saepedi where pedi_fec_pedi BETWEEN '$fecha_ini' and '$fecha_fin'
                                )";
            $costo_total_pedidos = round(consulta_string($sql_pedidos_semana, 'costo_total', $oIfx, 0), 2);
        }

        // CALCULOS EL COSTO DEL aDataGrid PARA VERIFICAR QUE NO SOBREPASE EL PRESUPUESTO
        $precio_productos_pc = 0;

        foreach ($aDataGrid as $keyaData => $DataGrid) {
            $costo_total = $DataGrid['Total'];
            $precio_productos_pc += $costo_total;
        }
        $costo_total_pedidos += $precio_productos_pc;
        $costo_total_pedidos += round($cantidad * $costo, 2);

        // CALCULOS REFERENTES AL PRESUPUESTO DE COMPRAS
        $saldo_disponible = round($presupuesto_semana - $costo_total_pedidos, 2);

        // DECICION PARA INGRESAR O NO EL PRODUCTO
        if ($saldo_disponible >= 0) {
            $ingresa_data = 'S';
        }
    } else {
        $ingresa_data = 'S';
    }
    // -----------------------------------------------------------------------------------------------------------
    // Obtenemos el presupuesto semanal de cada producto y verificamos si excede o no excede
    // -----------------------------------------------------------------------------------------------------------



    if ($ingresa_data == 'S') {
        if ($nTipo == 0) {

            //GUARDA LOS DATOS DEL DETALLE
            $cont = count($aDataGrid);
            // cantidad
            $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
            $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

            $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
            $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

            // centro dï¿½ costo
            $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, '', 100, 100, true);
            $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');

            // detalle
            $detalleCampo = restaurar_saltos_linea_guardados($detalle);
            $fu->AgregarCampoTexto($cont . '_det', 'Detalle', false, $detalleCampo, 100, 100, true);

            // busqueda
            $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                    title = "Presione aqui para Buscar Centro Costo"
                                    style="cursor: hand !important; cursor: pointer !important;"
                                    onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                    align="bottom" />';

            $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
            $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
            $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
            $aDataGrid[$cont][$aLabelGrid[3]] = $prod_nom ?: $idproducto;
            $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
            $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;
            $aDataGrid[$cont][$aLabelGrid[6]] = $costo;
            $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
            $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo');  //$costo;
            $aDataGrid[$cont][$aLabelGrid[9]] = round($cantidad * $costo, 2);
            $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                            onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                            onMouseOut="javascript:nd(); return true;"
                                                                                            title = "Presione aqui para Eliminar"
                                                                                            style="cursor: hand !important; cursor: pointer !important;"
                                                                                            onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                            alt="Eliminar"
                                                                                            align="bottom" />';
            $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;  //$costo;
            $aDataGrid[$cont][$aLabelGrid[12]] = $detalle;
            $aDataGrid[$cont][$aLabelGrid[13]] = $archivo_real;
            $aDataGrid[$cont][$aLabelGrid[14]] = '';
            $aDataGrid[$cont]['Producto Auxiliar'] = $producto_no_registrado ? 'SI' : 'No';
            $aDataGrid[$cont]['Codigo Auxiliar'] = $codigo_auxiliar;
            $aDataGrid[$cont]['Descripcion Auxiliar'] = $descripcion_auxiliar;
        } elseif ($nTipo == 1) {
            //MODIFICA Y EXTRAE LOS DATOS DEL DATAGRID A LA VENTANA  DETALLE
            // cantidad
            $fu->AgregarCampoNumerico($id . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
            $fu->AgregarComandoAlCambiarValor($id . '_cantidad', 'cargar_update_cant(\'' . $id . '\');');

            // costo
            $fu->AgregarCampoNumerico($id . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
            $fu->AgregarComandoAlCambiarValor($id . '_costo', 'cargar_update_cant(\'' . $id . '\');');

            // centro de costo
            $fu->AgregarCampoTexto($id . '_ccos', 'Centro Costo', false, $ccos, 100, 100, true);
            $fu->AgregarComandoAlEscribir($id . '_ccos', 'centro_costo_22( \'' . $id . '_ccos' . '\', event );');

            // detalle
            $detalleCampo = restaurar_saltos_linea_guardados($detalle);
            $fu->AgregarCampoTexto($id . '_det', 'Uso y Detalle', false, $detalleCampo, 100, 100, true);


            // busqueda
            $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                    title = "Presione aqui para Buscar Centro Costo"
                                    style="cursor: hand !important; cursor: pointer !important;"
                                    onclick="javascript:centro_costo_22_btn( \'' . $id . '_ccos' . '\' );"
                                    align="bottom" />';

            $aDataGrid[$id][$aLabelGrid[0]] = floatval($id);
            $aDataGrid[$id][$aLabelGrid[1]] = $idbodega;
            $aDataGrid[$id][$aLabelGrid[2]] = $idproducto;
            $aDataGrid[$id][$aLabelGrid[3]] = $prod_nom ?: $idproducto;
            $aDataGrid[$id][$aLabelGrid[4]] = $idunidad;
            $aDataGrid[$id][$aLabelGrid[5]] = $cantidad;
            $aDataGrid[$id][$aLabelGrid[6]] = $costo;
            $aDataGrid[$id][$aLabelGrid[7]] = $fu->ObjetoHtml($id . '_cantidad');  //$cantidad;
            $aDataGrid[$id][$aLabelGrid[8]] = $fu->ObjetoHtml($id . '_costo');  //$costo;
            $aDataGrid[$id][$aLabelGrid[9]] = round($cantidad * $costo, 2);
            $aDataGrid[$id][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                            title = "Presione aqui para Eliminar"
                                                                                            style="cursor: hand !important; cursor: pointer !important;"
                                                                                            onclick="javascript:xajax_elimina_detalle(' . $id . ');"
                                                                                            alt="Eliminar"
                                                                                            align="bottom" />';
            $aDataGrid[$id][$aLabelGrid[11]] = $fu->ObjetoHtml($id . '_ccos') . $busq;  // centro costo
            $aDataGrid[$id][$aLabelGrid[12]] =  $detalle;
            //$aDataGrid[$id][$aLabelGrid[13]] = '';
            //$aDataGrid[$id][$aLabelGrid[14]] = '';
            $aDataGrid[$id]['Producto Auxiliar'] = $producto_no_registrado ? 'SI' : 'No';
            $aDataGrid[$id]['Codigo Auxiliar'] = $codigo_auxiliar;
            $aDataGrid[$id]['Descripcion Auxiliar'] = $descripcion_auxiliar;
        }
        $_SESSION['aDataGird'] = $aDataGrid;
        $sHtml = mostrar_grid($idempresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script('totales_comp()');
        $oReturn->script('limpiar_prod()');
        $oReturn->script('cerrar_ventana();');
    } else {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("Swal.fire({
            title: '<h3>Supera el valor del presupuesto semanal con: $saldo_disponible</h3>',
            width: 600,
            type: 'error',   
            timer: 2000   ,
            showConfirmButton: false
            })");
    }


    return $oReturn;
}


function obtener_parametros_producto_no_registrado($empresa, $sucursal)
{
    $sql = "
        SELECT cod_bode_pedi, cod_prod_pedi
        FROM comercial.parametro_inv
        WHERE empr_cod_empr = $empresa
        AND sucu_cod_sucu = $sucursal
        LIMIT 1;
    ";

    $row = consulta_string($sql); // ya lo usas en otros lados

    $oReturn = new xajaxResponse();

    if ($row) {
        $oReturn->script("
            setProductoNoRegistrado('{$row['cod_bode_pedi']}', '{$row['cod_prod_pedi']}');
        ");
    } else {
        $oReturn->script("alertSwal('No estÃ¡ configurado el codigo para productos no registrados');");
    }

    return $oReturn;
}

$xajax->register(XAJAX_FUNCTION, "obtener_parametros_producto_no_registrado");

function obtener_unidad_producto($codigoProducto, $empresa, $sucursal, $bodega)
{
    global $DSN_Ifx;

    $empresa = (int) $empresa;
    $sucursal = (int) $sucursal;
    $bodega = (int) $bodega;
    $codigoProducto = trim($codigoProducto);

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $sql = "select prbo_cod_unid from saeprbo where prbo_cod_empr = $empresa and prbo_cod_sucu = $sucursal and prbo_cod_bode = $bodega and prbo_cod_prod = '$codigoProducto' limit 1";
    $unidad = consulta_string($sql, 'prbo_cod_unid', $oIfx, '');

    $oReturn = new xajaxResponse();

    if (!empty($unidad)) {
        $oReturn->script("seleccionarUnidadProducto('$unidad');");
    }

    return $oReturn;
}

$xajax->register(XAJAX_FUNCTION, "obtener_unidad_producto");


/// actualiza grid producto
function actualiza_grid($id, $aForm)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();

    // variables
    $cantidad = $aForm[$id . '_cantidad'];
    $costo = $aForm[$id . '_costo'];
    $producto = $aDataGrid[$id]['Codigo Item'];
    $bodega = $aDataGrid[$id]['Bodega'];
    $centro_costo = $aForm[$id . '_ccos'];
    $detalle = $aForm[$id . '_det'];
    $unidadSeleccionada = isset($aForm[$id . '_unidad']) ? $aForm[$id . '_unidad'] : $aDataGrid[$id]['Unidad'];
    $oReturn->script('cargar_update_grid(\'' . $id . '\', \'' . $producto . '\', \'' . $cantidad . '\' , \'' . $bodega . '\', \'' . $costo . '\', \'' . $centro_costo . '\', \'' . $detalle . '\', \'' . $unidadSeleccionada . '\'  )');
    return $oReturn;
}

function agrega_modifica_grid_update($descuento_general, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();

    $idempresa = $aForm['empresa'];
    $cont = count($aDataGrid);
    $matriz = array();
    unset($matriz);

    if ($cont > 0) {
        $j = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            $total_fact = 0;
            $i = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0) {                    //id
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 1) {              //bodega
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 2) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $prod = $aVal;
                    $i++;
                } elseif ($aux == 3) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 4) {              //unidad
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 5) {              //cantidad
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 6) {              //costo
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 7) {              //cantidad
                    $cant = $aForm[$j . '_cantidad'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 8) {              //costo
                    $costo = $aForm[$j . '_costo'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 9) {             // total
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 10) {             // eliminar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 11) {              //centro costo
                    $costo = $aForm[$j . '_ccos'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 12) {             // detalle
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } else {
                    $matriz[$j][$i] = $aVal;
                    $i++;
                }
                $aux++;
            }
            $j++;
        }

        unset($_SESSION['aDataGird']);
        // generacion del grid actualizado
        $aDataGrid = $_SESSION['aDataGird'];
        $aLabelGrid = array(
            'Id',
            'Bodega',
            'Codigo Item',
            'Descripcion',
            'Unidad',
            'Cantidad Tmp',
            'Costo Tmp',
            'Cantidad',
            'Costo',
            'Total',
            'Eliminar',
            'Centro Costo',
            'Detalle',
            'Archivo',
            'Codigo Requisicion',
            'Producto Auxiliar',
            'Codigo Auxiliar',
            'Descripcion Auxiliar'
        );

        for ($x = 0; $x <= ($j - 1); $x++) {
            for ($y = 0; $y <= $i; $y++) {
                $aDataGrid[$x][$aLabelGrid[$y]] = $matriz[$x][$y];
            }
        }

        $_SESSION['aDataGird'] = $aDataGrid;
        $sHtml = mostrar_grid($idempresa);
        $oReturn->script('totales_comp()');
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
    }

    return $oReturn;
}

function mostrar_grid($idempresa)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $aDataGrid = $_SESSION['aDataGird'];
    $aLabelGrid = array('Eliminar', 'Bodega', 'Producto auxiliar (SI/No)', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Detalle', 'Archivo');

    $unidadesDisponibles = array();
    $sqlUnidades = 'select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = ? order by unid_sigl_unid';
    if ($oIfx->Query($sqlUnidades, array($idempresa))) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codigoUnidad = $oIfx->f('unid_cod_unid');
                $siglaUnidad = $oIfx->f('unid_sigl_unid');
                if (!empty($codigoUnidad)) {
                    $unidadesDisponibles[$codigoUnidad] = $siglaUnidad;
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $cont = 0;
    foreach ($aDataGrid as $aValues) {
        $cod_prod = $aValues['Codigo Item'];
        $bodegaId = $aValues['Bodega'];
        $unidadId = $aValues['Unidad'];

        $aDatos[$cont]['Eliminar'] = '<div align="center">' . $aValues['Eliminar'] . '</div>';

        $esAuxiliar = ($aValues['Producto Auxiliar'] ?? 'No') === 'SI';
        $codigoAuxiliar = trim($aValues['Codigo Auxiliar'] ?? '');
        $descripcionAuxiliar = trim($aValues['Descripcion Auxiliar'] ?? '');

        $aDatos[$cont]['Bodega'] = '';
        if (!$esAuxiliar) {
            $sql = 'select bode_nom_bode from saebode where bode_cod_bode = ? and bode_cod_empr = ?';
            $data = array($bodegaId, $idempresa);
            if ($oIfx->Query($sql, $data)) {
                $aDatos[$cont]['Bodega'] = $oIfx->f('bode_nom_bode');
            }
            $oIfx->Free();
        }

        $aDatos[$cont]['Producto auxiliar (SI/No)'] = $esAuxiliar ? 'SI' : 'No';
        $aDatos[$cont]['Codigo Item'] = ($esAuxiliar && $codigoAuxiliar) ? $codigoAuxiliar : $cod_prod;

        $sql = 'select prod_nom_prod from saeprod where prod_cod_prod = ?';
        $data = array($cod_prod);
        if ($oIfx->Query($sql, $data))
            $producto = $oIfx->f('prod_nom_prod');
        $oIfx->Free();
        if ($esAuxiliar && $descripcionAuxiliar) {
            $producto = $descripcionAuxiliar;
        } elseif (empty($producto) && !empty($aValues['Descripcion'])) {
            $producto = $aValues['Descripcion'];
        }
        $aDatos[$cont]['Descripcion'] = $producto;

        $unidadSelectName = $cont . '_unidad';
        $unidadSelect = '<select class="form-control input-sm" name="' . $unidadSelectName . '" id="' . $unidadSelectName . '" onchange="cargar_update_cant(\'' . $cont . '\');">';
        if (!empty($unidadId) && !isset($unidadesDisponibles[$unidadId])) {
            $unidadSelect .= '<option value="' . htmlspecialchars($unidadId, ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($unidadId, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        foreach ($unidadesDisponibles as $idUnidad => $siglaUnidad) {
            $selected = ($idUnidad == $unidadId) ? ' selected' : '';
            $unidadSelect .= '<option value="' . htmlspecialchars($idUnidad, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($siglaUnidad, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $unidadSelect .= '</select>';
        $aDatos[$cont]['Unidad'] = $unidadSelect;

        $aDatos[$cont]['Cantidad Tmp'] = '<div align="right">' . $aValues['Cantidad Tmp'] . '</div>';
        $aDatos[$cont]['Costo Tmp'] = '<div align="right">' . $aValues['Costo Tmp'] . '</div>';
        $aDatos[$cont]['Cantidad'] = '<div align="right">' . $aValues['Cantidad'] . '</div>';
        $detalle = isset($aValues['Detalle']) ? $aValues['Detalle'] : '';
        $detalleConSaltos = restaurar_saltos_linea_guardados($detalle);
        $detalleVisible = formatear_detalle_para_mostrar($detalleConSaltos);
        $detallePlano = htmlspecialchars($detalleConSaltos, ENT_QUOTES, 'UTF-8');
        $aDatos[$cont]['Detalle'] = '<div class="detalle-texto-grid">' . $detalleVisible . '</div>' .
            '<input type="hidden" name="' . $cont . '_det" id="' . $cont . '_det" value="' . $detallePlano . '">';
        $aDatos[$cont]['Archivo'] = $aValues['Archivo'];
        $cont++;
    }
    return genera_grid($aDatos, $aLabelGrid, 'Lista de Productos', 95, true);
}

function cancelar_pedido()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $aDataGrid = $_SESSION['aDataGird'];
    $aDataPrueba = $_SESSION['aDataPrueba'];
    unset($_SESSION['aDataGird']);
    unset($_SESSION['aDataPrueba']);
    $sScript = "xajax_genera_formulario_pedido();";
    $oReturn = new xajaxResponse();
    $oReturn->clear("divFormularioDetalle", "innerHTML");
    $oReturn->script($sScript);
    return $oReturn;
}

function elimina_detalle($id = null)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Archivo', 'Codigo Requisicion', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');
    $aDataGrid = $_SESSION['aDataGird'];
    $contador = count($aDataGrid);
    if ($contador > 1) {
        unset($aDataGrid[$id]);
        $_SESSION['aDataGird'] = $aDataGrid;
        $oReturn->script('cargar_grid();');;
    } else {
        unset($aDataGrid[0]);
        $_SESSION['aDataGird'] = $aDatos;
        $sHtml = "";
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales_comp()');
    }

    return $oReturn;
}

function cargar_grid($descuento_general, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();
    $idempresa = $aForm['empresa'];
    $cont = count($aDataGrid);
    $matriz = array();
    unset($matriz);
    if ($cont > 0) {
        $j = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            $total_fact = 0;
            $i = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0) {                    //id
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 1) {              //bodega
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 2) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $prod = $aVal;
                    $i++;
                } elseif ($aux == 3) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 4) {              //unidad
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 5) {              //cantidad
                    $cant = $aVal;
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 6) {              //costo
                    $costo = $aVal;
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 7) {              //cantidad
                    $fu->AgregarCampoNumerico($j . '_cantidad', 'Cantidad|LEFT', false, $cant, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($j . '_cantidad', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_cantidad');
                    $i++;
                } elseif ($aux == 8) {              //costo
                    $fu->AgregarCampoNumerico($j . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($j . '_costo', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_costo');
                    $i++;
                } elseif ($aux == 9) {             // total
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 10) {             // eliminar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 11) {              //centro de costo
                    // busqueda
                    $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                                        title = "Presione aqui para Buscar Centro Costo"
                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                        onclick="javascript:centro_costo_22_btn( \'' . $j . '_ccos' . '\' );"
                                                        align="bottom" />';
                    $costo = $aForm[$j . '_ccos'];
                    $fu->AgregarCampoTexto($j . '_ccos', 'Centro Costo|LEFT', false, $costo, 100, 100, true);
                    $fu->AgregarComandoAlEscribir($j . '_ccos', 'centro_costo_22(\'' . $j . '_ccos' . '\', event );');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_ccos') . $busq;
                    $i++;
                } else {
                    $matriz[$j][$i] = $aVal;
                    $i++;
                }
                $aux++;
            }
            $j++;
        }

        unset($_SESSION['aDataGird']);
        // generacion del grid actualizado
        $aDataGrid = $_SESSION['aDataGird'];
        $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Detalle', 'Archivo', 'Codigo Requisicion', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');

        for ($x = 0; $x <= ($j - 1); $x++) {
            for ($y = 0; $y <= $i; $y++) {
                $aDataGrid[$x][$aLabelGrid[$y]] = $matriz[$x][$y];
            }
        }

        $_SESSION['aDataGird'] = $aDataGrid;
        $sHtml = mostrar_grid($idempresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales_comp()');
        $oReturn->script('refrescarBloqueoCampos();');
    }

    return $oReturn;
}

function total_grid($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();
    $oReturn->assign("divTotal", "");

    return $oReturn;
}

// UTILIDADES
function pedf_num_preimp($idempresa, $id_sucursal)
{
    //Definiciones
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    // max de la saencrs
    $sql = "select max(pedi_cod_pedi) as maximo from saepedi where
                    pedi_cod_empr = $idempresa and
                    pedi_cod_sucu = $id_sucursal   ";
    $secuencial = consulta_string_func($sql, 'maximo', $oIfx, 0);
    $secuencial = $secuencial + 1 - 1;
    $secuencial_real = secuencial(2, '', $secuencial, 9);

    return $secuencial_real;
}

function cargar_productos($aForm = '', $idbodega = '', $empresa = '', $sucursal = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGird'];

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad Tmp', 'Costo Tmp', 'Cantidad', 'Costo', 'Total', 'Eliminar', 'Centro Costo', 'Producto Auxiliar', 'Codigo Auxiliar', 'Descripcion Auxiliar');

    //$aLabelGrid = $_SESSION['aLabelGirdProd'];
    //$idEmpresa = $_SESSION['U_EMPRESA'];
    //$idSucursal = $_SESSION['U_SUCURSAL'];
    $cliente = $aForm['cliente'];

    $array_depacho = $_SESSION['ARRAY_DESPACHO'];
    $count = count($array_depacho);


    if ($count > 0) {
        foreach ($array_depacho as $val) {
            $i             = $val[0];
            $prod         = $val[1];
            $bode         = $val[2];
            $iva        = $val[3];
            //$cantidad	= $aForm[$i . '_cant_' . $prod];
            $cantidad    = $aForm[$i . '_total_' . $prod];
            $precio        = $aForm[$i . '_precio_' . $prod];
            $costo        = $aForm[$i . '_costo_' . $prod];

            if ($cantidad > 0) {
                $cont = count($aDataGrid);
                // saeprod
                $sql = "select  p.prod_cod_prod,   pr.prbo_cod_unid,  COALESCE(pr.prbo_iva_porc,0) as prbo_iva_porc   ,
							COALESCE(pr.prbo_ice_porc,0) as prbo_ice_porc,
							COALESCE( pr.prbo_dis_prod,0 ) as stock, prod_cod_tpro,
							pr.prbo_cta_inv, pr.prbo_cta_ideb
							from saeprod p, saeprbo pr where
							p.prod_cod_prod = pr.prbo_cod_prod and
							p.prod_cod_empr = $empresa and
							p.prod_cod_sucu = $sucursal and
							pr.prbo_cod_empr = $empresa and
							pr.prbo_cod_bode = $bode and
							p.prod_cod_prod = '$prod'";
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        $idproducto = $oIfx->f('prod_cod_prod');
                        $idunidad     = $oIfx->f('prbo_cod_unid');
                        $cuenta_inv = $oIfx->f('prbo_cta_inv');
                        $cuenta_iva = $oIfx->f('prbo_cta_ideb');
                    } else {
                        $idproducto    = '';
                        $idunidad    = '';
                        $cuenta_inv = '';
                        $cuenta_iva = '';
                    }
                }
                $oIfx->Free();

                $descuento = 0;
                $descuento_2 = 0;
                $descuento_general = 0;

                // TOTAL
                $total_fac = 0;
                $dsc1 = ($costo * $cantidad * $descuento) / 100;
                $dsc2 = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                if ($descuento_general > 0) {
                    // descto general
                    $dsc3 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                    $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                    $tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                } else {
                    // sin descuento general
                    $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    $tmp = $total_fact_tmp;
                }

                $total_fac = round($total_fact_tmp, 2);

                // total con iva
                if ($iva > 0) {
                    $total_con_iva = round((($total_fac * $iva)  / 100), 2) + $total_fac;
                } else {
                    $total_con_iva = $total_fac;
                }

                // cantidad
                $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                // costo
                $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                // iva
                $fu->AgregarCampoNumerico($cont . '_iva', 'Iva|LEFT', false, $iva, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                // descto1
                $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, $descuento, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                // descto2
                $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, $descuento_2, 40, 40, true);
                $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                // centro de costo
                $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $ccos, 100, 100, true);
                $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');

                // busqueda
                $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                title = "Presione aqui para Buscar Centro Costo"
                                style="cursor: hand !important; cursor: pointer !important;"
                                onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                align="bottom" />';

                $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                $aDataGrid[$cont][$aLabelGrid[1]] = $bode;
                $aDataGrid[$cont][$aLabelGrid[2]] = $prod;
                $aDataGrid[$cont][$aLabelGrid[3]] = $prod;
                $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;
                $aDataGrid[$cont][$aLabelGrid[6]] = $costo;
                $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;                
                $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                $aDataGrid[$cont][$aLabelGrid[9]] = $total_fac; // dec2
                $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                        onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                        onMouseOut="javascript:nd(); return true;"
                                                                                        title = "Presione aqui para Eliminar"
                                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                                        onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                        alt="Eliminar"
                                                                                        align="bottom" />';
                $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;  // centro costo

                $_SESSION['aDataGird'] = $aDataGrid;
                $sHtml = mostrar_grid($empresa);
                $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
            } //fin if cantidad
        }
        $oReturn->script('totales_comp();');
        $oReturn->script('cerrar_ventana();');
    } else {
        $oReturn->alert('No existen datos para generar Grid...');
    }
    return $oReturn;
}
/*GENERACION PDF SOLICITUD DE COMPRA EDICION*/
function genera_pdf_doc_reporte($pedi, $aForm = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    unset($_SESSION['pdf']);


    $idempresa  = isset($aForm['empresa']) && $aForm['empresa'] !== '' ? (int)$aForm['empresa'] : (isset($_SESSION['U_EMPRESA']) ? (int)$_SESSION['U_EMPRESA'] : 0);
    $idsucursal = isset($aForm['sucursal']) && $aForm['sucursal'] !== '' ? (int)$aForm['sucursal'] : (isset($_SESSION['U_SUCURSAL']) ? (int)$_SESSION['U_SUCURSAL'] : 0);
    $oReturn = new xajaxResponse();

    $sql = "select ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
    ftrn_des_ftrn = 'PEDIDO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";

    $ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfxA, '');


    if (!empty($ubi)) {
        include_once('../../' . $ubi . '');
        $html = genera_pdf_doc_comp($pedi, 0, $idempresa, $idsucursal);
    } else {
        $html = generar_pedido_compra_pdf($idempresa, $idsucursal, $pedi);
    }

    $_SESSION['pdf'] = $html;
    if (!empty($ubi)) {
        $oReturn->script('generar_pdf_compras()');
    } else {
        $oReturn->script('generar_pdf()');
    }
    return $oReturn;
}
/*GENERACION PDF SOLICITUD DE COMPRA*/
function genera_pdf_doc($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    unset($_SESSION['pdf']);
    $oReturn = new xajaxResponse();

    $idempresa  = isset($aForm['empresa']) && $aForm['empresa'] !== '' ? (int)$aForm['empresa'] : (isset($_SESSION['U_EMPRESA']) ? (int)$_SESSION['U_EMPRESA'] : 0);
    $idsucursal = isset($aForm['sucursal']) && $aForm['sucursal'] !== '' ? (int)$aForm['sucursal'] : (isset($_SESSION['U_SUCURSAL']) ? (int)$_SESSION['U_SUCURSAL'] : 0);
    $nota_compra   = $aForm['nota_compra'];

    $sql = "select ftrn_ubi_web from saeftrn where ftrn_cod_modu = 10 and
    ftrn_des_ftrn = 'PEDIDO'  and ftrn_ubi_web is not null and ftrn_cod_empr=$idempresa";

    $ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfxA, '');
    if (!empty($ubi)) {
        include_once('../../' . $ubi . '');
        //$diario = generar_pedido_compra_pdf($idempresa, $idsucursal, $nota_compra);
        $diario = genera_pdf_doc_comp($nota_compra, 2, $idempresa, $idsucursal);
    } else {
        $diario = generar_pedido_compra_pdf($idempresa, $idsucursal, $nota_compra);
    }


    $_SESSION['pdf'] = $diario;
    if (!empty($ubi)) {
        $oReturn->script('generar_pdf_compras()');
    } else {
        $oReturn->script('generar_pdf()');
    }

    return $oReturn;
}

// ---------------------------------------------------------------------------------------------------------
// FUNCIONES PARA CARGAR POR ARCHIVO LOS PRODUCTOS
// ---------------------------------------------------------------------------------------------------------

function modal_cargar_archivo($aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];


    try {

        $sHtml = '';
        $sHtml .= ' <div  class="col-md-12 text-center" style="margin-top: 50px; margin-bottom: 10px; border: 2px solid black !important; padding: 30px; border-style: dotted !important;">                                
            
                        <div class="row">
                            <div class="col-md-12" style="margin-bottom: 20px">
                                <h4><b>Cargar Archivo con Ingresos</b><h4>
                            </div>
                            <div class="col-md-4">
                                <input type="file" name="archivo47" id="archivo47" onchange="upload_image(id);" required>
                                <div class="upload-msg"></div>
                            </div>
                            <div class="col-md-1">
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Archivo Ejemplo</label><br>
                                    <div style="text-align:left;">
                                        <a href="compra_import.txt" download="compra_import.txt" id="txt">
                                            <span class="glyphicon glyphicon-download"></span> Ejemplo Archivo
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                            </div>
                            <div class="col-md-4">
                                <div class="btn btn-primary btn-sm" onclick="consultar();" style="width: 100%">
                                    <span class="glyphicon glyphicon-search"></span>
                                    Consultar
                                </div>
                            </div>
                        </div>
                    </div>';


        $modal = '<div id="mostrarmodal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">CARGAR INGRESOS POR ARCHIVO</h4>
                            </div>
                            <div class="modal-body">';
        $modal .= $sHtml;
        $modal .= ' 
                    <div class="btn btn-white btn-sm" style="width: 20%">
                    </div>
                    <div id="div_procesar" class="btn btn-primary btn-sm" onclick="processar_archivo();" style="width: 20%; display: none">
                        <span class="glyphicon glyphicon-search"></span>
                        PROCESAR
                    </div>
                    <div id="divFormularioDetalle3" class="table-responsive" style="margin-bottom: 120px;"></div>';
        $modal .= '          </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                 </div>';

        $oReturn->assign("divFormularioTotal", "innerHTML", $modal);
        $oReturn->script("abre_modal();");
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function cargar_ord_compra_respaldo($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();


    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucursal'];
    $mostrar_procesar = 'S';


    //////////////

    try {

        // DATOS
        // BODEGA
        $sql = "select bode_cod_bode, bode_nom_bode from saesubo, saebode where
                        bode_cod_bode = subo_cod_bode and
                        bode_cod_empr = $idempresa and
                        subo_cod_empr = $idempresa and
                        subo_cod_sucu = $idsucursal ";
        unset($array_bode);
        unset($array_bode_cod);
        $array_bode     = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_nom_bode');
        $array_bode_cod = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_cod_bode');


        // PRODUCTO
        $sql = "SELECT   
                    prod_cod_prod, 
                    prod_nom_prod,
                    max(prbo_costo_prod) as prbo_costo_prod
                from 
                    saeprod 
                    left join saeprbo 
                        on prod_cod_prod = prbo_cod_prod
                where
                prod_cod_empr = $idempresa and
                prod_cod_sucu = $idsucursal
                group by 1,2    
                ";

        unset($array_prod);
        unset($array_prod_nom);
        unset($array_prod_costo);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $prod_cod_prod = $oIfx->f('prod_cod_prod');
                    $prod_nom_prod = $oIfx->f('prod_nom_prod');
                    $prbo_costo_prod = $oIfx->f('prbo_costo_prod');
                    $array_prod[$prod_cod_prod] = $prod_cod_prod;
                    $array_prod_nom[$prod_cod_prod] = $prod_nom_prod;
                    $array_prod_costo[$prod_cod_prod] = $prbo_costo_prod;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();



        // CENTRO DE COSTO
        $sql = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                from saeccosn where
                ccosn_cod_empr = $idempresa and
                ccosn_mov_ccosn = 1 order by 2";

        unset($array_prec);
        unset($array_prec_cod);
        $array_prec     = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_nom_ccosn');
        $array_prec_cod = array_dato($oIfx, $sql, 'ccosn_cod_ccosn', 'ccosn_cod_ccosn');

        $archivo = $aForm['archivo47'];

        // archivo txt
        $archivo_real = substr($archivo, 12);
        list($xxxx, $exten) = explode(".", $archivo_real);

        if ($exten == 'txt') {
            $nombre_archivo = "upload/" . $archivo_real;

            $file       = fopen($nombre_archivo, "r");
            $datos      = file($nombre_archivo);
            $NumFilas   = count($datos);

            $table_cab  = '<br><br>';
            $table_cab  = '<h4>Lista del archivo exportado</h4>';
            $table_cab .= '<table class="table table-bordered table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">';
            $table_cab .= '<tr>
                                            <td class="success" style="width: 4.5%;">N.-</td>
                                            <td class="success" style="width: 4.5%;">BODEGA</td>
                                            <td class="success" style="width: 4.5%;">CODIGO PRODUCTO</td>
                                            <td class="success" style="width: 9.5%;">PRODUCTO</td>
                                            <td class="success" style="width: 4.5%;">CANTIDAD</td>
                                            <td class="success" style="width: 4.5%;">DETALLE</td>
                                            <td class="success" style="width: 4.5%;">LOTE SERIE</td>
                                            <td class="success" style="width: 4.5%;">FECHA ELA</td>
                                            <td class="success" style="width: 4.5%;">FECHA CAD</td>
                                            <td class="success" style="width: 4.5%;">COSTO</td>';

            $cont = 0;
            $cont_pvp = 0;

            /*BODEGA    CODIGO  PRODUCTO    CANTIDAD    DETALLE LOTE/SERIE  FECHA ELAB  FECHA CADUCI    COSTO */

            $table_cab .= '</tr>';
            $x = 1;
            // $oReturn->alert('Buscando ...');
            unset($array);
            foreach ($datos as $val) {
                /*BODEGA    CODIGO  PRODUCTO    CANTIDAD    DETALLE LOTE/SERIE  FECHA ELAB  FECHA CADUCI    COSTO */
                list(
                    $bode_cod,
                    $prod_cod,
                    $prod_nom,
                    $cantidad,
                    $detalle,
                    $lote_serie,
                    $fela,
                    $fecad,
                    $costo
                ) = explode("	", $val);

                if ($x > 1 && !empty($bode_cod)) {

                    $table_cab .= '<tr>';
                    $table_cab .= '<td>' . ($x - 1) . '</td>';
                    if (!empty($array_bode[trim($bode_cod)])) {
                        $table_cab .= '<td>' . $array_bode[trim($bode_cod)] . '</td>';
                    } else {
                        $table_cab .= '<td style="background:yellow">' . $bode_cod . '</td>';
                        $mostrar_procesar = 'N';
                    }

                    if (!empty($array_prod[trim($prod_cod)])) {
                        $table_cab .= '<td>' . $array_prod[$prod_cod] . '</td>';
                        $prod_nom = $array_prod_nom[$prod_cod];
                    } else {
                        $table_cab .= '<td style="background:yellow">' . $prod_cod . '</td>';
                        $mostrar_procesar = 'N';
                    }

                    if (empty($prod_nom)) {
                        $table_cab .= '<td style="background:yellow">' . $prod_cod . ' (Producto no existe)</td>';
                        $mostrar_procesar = 'N';
                    } else {
                        $table_cab .= '<td>' . $prod_nom . '</td>';
                    }

                    $table_cab .= '<td align="right">' . $cantidad . '</td>';


                    $table_cab .= '<td align="left">' . $detalle . '</td>';
                    $table_cab .= '<td align="left">' . $lote_serie . '</td>';
                    $table_cab .= '<td align="left">' . $fela . '</td>';
                    $table_cab .= '<td align="left">' . $fecad . '</td>';



                    if ($costo == 0) {
                        $costo = $array_prod_costo[$prod_cod];
                    }

                    if (empty($costo)) {
                        $costo = 0;
                    }

                    $table_cab .= '<td>' . $costo . '</td>';


                    $table_cab .= '</tr>';
                }
                $x++;
            }

            $table_cab .= "</table>";

            if ($mostrar_procesar == 'S') {
                $oReturn->script("mostrar_procesar()");
            } else {
                $oReturn->script("ocultar_procesar()");
            }

            $oReturn->assign("divFormularioDetalle2", "innerHTML", $table_cab);
            $oReturn->assign("divFormularioDetalle3", "innerHTML", $table_cab);
        } else {
            $oReturn->script("Swal.fire({
                                            title: '<h3><strong>!!!!....Archivo Incorrecto, por favor subir Archivo con extension .txt...!!!!!</strong></h3>',
                                            width: 800,
                                            type: 'error',   
                                            timer: 3000   ,
                                            showConfirmButton: false
                                            })");
            $oReturn->assign("divFormularioDetalle2", "innerHTML", '');
            $oReturn->assign("divFormularioDetalle3", "innerHTML", '');
        }
    } catch (Exception $ex) {
        $oReturn->alert($ex->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function cargar_ord_compra($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();


    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucursal'];


    unset($_SESSION['aDataGird']);



    //////////////



    try {

        // DATOS
        // BODEGA
        $sql = "select bode_cod_bode, bode_nom_bode from saesubo, saebode where
                        bode_cod_bode = subo_cod_bode and
                        bode_cod_empr = $idempresa and
                        subo_cod_empr = $idempresa and
                        subo_cod_sucu = $idsucursal ";
        unset($array_bode);
        unset($array_bode_cod);
        $array_bode     = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_nom_bode');
        $array_bode_cod = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_cod_bode');


        // PRODUCTO
        $sql = "SELECT   
                    prod_cod_prod, 
                    prod_nom_prod,
                    max(prbo_costo_prod) as prbo_costo_prod
                from 
                    saeprod 
                    left join saeprbo 
                        on prod_cod_prod = prbo_cod_prod
                where
                prod_cod_empr = $idempresa and
                prod_cod_sucu = $idsucursal
                group by 1,2    
                ";

        unset($array_prod);
        unset($array_prod_nom);
        unset($array_prod_costo);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $prod_cod_prod = $oIfx->f('prod_cod_prod');
                    $prod_nom_prod = $oIfx->f('prod_nom_prod');
                    $prbo_costo_prod = $oIfx->f('prbo_costo_prod');
                    $array_prod[$prod_cod_prod] = $prod_cod_prod;
                    $array_prod_nom[$prod_cod_prod] = $prod_nom_prod;
                    $array_prod_costo[$prod_cod_prod] = $prbo_costo_prod;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();


        // CENTRO DE COSTO
        $sql = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                from saeccosn where
                ccosn_cod_empr = $idempresa and
                ccosn_mov_ccosn = 1 order by 2";

        unset($array_prec);
        unset($array_prec_cod);
        $array_prec     = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_nom_ccosn');
        $array_prec_cod = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_cod_ccosn');

        $archivo = $aForm['archivo47'];

        // archivo txt
        $archivo_real = substr($archivo, 12);
        list($xxxx, $exten) = explode(".", $archivo_real);

        if ($exten == 'txt') {
            $nombre_archivo = "upload/" . $archivo_real;

            $file       = fopen($nombre_archivo, "r");
            $datos      = file($nombre_archivo);
            $NumFilas   = count($datos);

            unset($aDataGrid);
            unset($aDataPrecio);
            $aDataGrid  = $_SESSION['aDataGird'];
            $aDataPrecio  = $_SESSION['aDataGird_PRECIO'];
            $aLabelGrid = $_SESSION['aLabelGirdProd_INV_MRECO'];


            $aLabelGrid = array(
                'Id',
                'Bodega',
                'Codigo Item',
                'Descripcion',
                'Unidad',
                'Cantidad',
                'Costo',
                'Iva',
                'Dscto 1',
                'Dscto 2',
                'Dscto Gral',
                'Total',
                'Total Con Iva',
                'Modificar',
                'Eliminar',
                'Cuenta',
                'Cuenta Iva',
                'Centro Costo',
                'Cuenta Gasto',
                'Detalle',
                'Lote/Serie',
                'Elaboracion',
                'Caduca',
                'MAC'
            );


            $cont = 0;
            $cont_pvp = 0;
            $datos_txt = explode("	", $datos[0]);
            foreach ($datos_txt as $val1) {
                if ($cont > 9) {
                    $cont_pvp++;
                }
                $cont++;
            }



            $x = 1;
            $oReturn->alert('Buscando ...');
            unset($array);
            foreach ($datos as $val) {
                /*BODEGA	    CODIGO	        PRODUCTO	    CANTIDAD	        CENTRO DE COSTO	        FOB
                        */





                list(
                    $bode_cod,
                    $prod_cod,
                    $prod_nom,
                    $cantidad,
                    $detalle,
                    $lote_serie,
                    $fela,
                    $fecad,
                    $costo
                ) = explode("	", $val);

                if ($costo == 0) {
                    $costo = $array_prod_costo[$prod_cod];
                }

                if (empty($costo)) {
                    $costo = 0;
                }

                $costo_limpio = str_replace(',', '.', $costo);
                $bode_cod = trim($bode_cod);
                $prod_cod = trim($prod_cod);
                $prod_nom = trim($prod_nom);


                if ($x > 1 && !empty($bode_cod)) {

                    $cantidad             = $cantidad;
                    $codigo_barra         = '';
                    $codigo_producto     = $prod_cod;
                    $costo                = $costo_limpio;
                    $iva                 = 0;
                    $iva                = 0;
                    $idbodega             = $array_bode_cod[$bode_cod];
                    $descuento             = 0;
                    $descuento_2         = 0;



                    $sql_prod_nom = "select prod_nom_prod from saeprod where prod_cod_prod = '$prod_cod'";
                    $prod_nom = consulta_string($sql_prod_nom, 'prod_nom_prod', $oIfx, '');

                    $sql_prbo_cuentas = "SELECT 
                                            prbo_cta_inv, prbo_cta_ideb, prbo_cod_unid 
                                        from saeprbo 
                                        where 
                                            prbo_cod_prod = '$prod_cod'
                                            and prbo_cod_empr = $idempresa
                                            and prbo_cod_bode = $idbodega
                                            and prbo_cod_sucu = $idsucursal
                                            ";

                    if ($oIfx->Query($sql_prbo_cuentas)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $prbo_cod_unid = $oIfx->f('prbo_cod_unid');
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    $idunidad = $prbo_cod_unid;



                    $descuento_general = 0;
                    // TOTAL
                    $total_fac     = 0;
                    $dsc1         = ($costo * $cantidad * $descuento) / 100;
                    $dsc2         = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_general > 0) {
                        // descto general
                        $dsc3                 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp                 = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                        $tmp                 = $total_fact_tmp;
                    }

                    $total_fac = round($total_fact_tmp, 2);

                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                    } else {
                        $total_con_iva = $total_fac;
                    }




                    //GUARDA LOS DATOS DEL DETALLE
                    $cont = count($aDataGrid);
                    // cantidad
                    $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                    $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40, true);
                    $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                    // centro dï¿½ costo
                    $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, '', 100, 100, true);
                    $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');

                    // busqueda
                    $busq = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/viewmag.png"
                                    title = "Presione aqui para Buscar Centro Costo"
                                    style="cursor: hand !important; cursor: pointer !important;"
                                    onclick="javascript:centro_costo_22_btn( \'' . $cont . '_ccos' . '\' );"
                                    align="bottom" />';

                    $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                    $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                    $aDataGrid[$cont][$aLabelGrid[2]] = $codigo_producto;
                    $aDataGrid[$cont][$aLabelGrid[3]] = $codigo_producto;
                    $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                    $aDataGrid[$cont][$aLabelGrid[5]] = $cantidad;
                    $aDataGrid[$cont][$aLabelGrid[6]] = $costo;
                    $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                    $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_costo');  //$costo;
                    $aDataGrid[$cont][$aLabelGrid[9]] = round($cantidad * $costo, 2);
                    $aDataGrid[$cont][$aLabelGrid[10]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                            onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                            onMouseOut="javascript:nd(); return true;"
                                                                                            title = "Presione aqui para Eliminar"
                                                                                            style="cursor: hand !important; cursor: pointer !important;"
                                                                                            onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                            alt="Eliminar"
                                                                                            align="bottom" />';
                    $aDataGrid[$cont][$aLabelGrid[11]] = $fu->ObjetoHtml($cont . '_ccos') . $busq;  //$costo;
                    $aDataGrid[$cont][$aLabelGrid[12]] = $detalle;
                    $aDataGrid[$cont][$aLabelGrid[13]] = '';
                    $aDataGrid[$cont][$aLabelGrid[14]] = '';
                }
                $x++;
            }


            $_SESSION['aDataGird'] = $aDataGrid;
            $sHtml = mostrar_grid($idempresa);
            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
            $oReturn->script('totales_comp();');
            $oReturn->script('limpiar_prod()');
            $oReturn->script('cerrar_modal();');
            $oReturn->script('cerrar_ventana();');
        } else {
            $oReturn->script("Swal.fire({
                                            title: '<h3><strong>!!!!....Archivo Incorrecto, por favor subir Archivo con extension .txt...!!!!!</strong></h3>',
                                            width: 800,
                                            type: 'error',   
                                            timer: 3000   ,
                                            showConfirmButton: false
                                            })");
            $oReturn->assign("divFormularioDetalle", "innerHTML", '');
        }
    } catch (Exception $ex) {
        $oReturn->alert($ex->getMessage());
    }
    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}
// ---------------------------------------------------------------------------------------------------------
// FIN FUNCIONES PARA CARGAR POR ARCHIVO LOS PRODUCTOS
// ---------------------------------------------------------------------------------------------------------

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* GESTION DE APROBADORES                                                                   */
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

function obtener_catalogo_aprobadores($empresaId, $sucursalId)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);
    $payload = array('cargos' => array(), 'aprobadores' => array());

    if ($empresaId > 0 && $sucursalId > 0) {
        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $sqlCargos = "SELECT c.id, c.nombre, c.empresa, c.sucursal, c.estado, c.orden, e.empr_nom_empr AS empresa_nombre, s.sucu_nom_sucu AS sucursal_nombre
                        FROM comercial.aprobador_cargo c
                        INNER JOIN saeempr e ON e.empr_cod_empr = c.empresa
                        INNER JOIN saesucu s ON s.sucu_cod_sucu = c.sucursal AND s.sucu_cod_empr = c.empresa
                        WHERE c.empresa = $empresaId AND c.sucursal = $sucursalId
                        ORDER BY c.estado DESC, COALESCE(c.orden, c.id), c.id";

        if ($oIfx->Query($sqlCargos)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $payload['cargos'][] = array(
                        'id' => strval($oIfx->f('id')),
                        'nombre' => strtoupper($oIfx->f('nombre')),
                        'empresaId' => strval($oIfx->f('empresa')),
                        'sucursalId' => strval($oIfx->f('sucursal')),
                        'estado' => $oIfx->f('estado'),
                        'orden' => $oIfx->f('orden'),
                        'empresaNombre' => strtoupper($oIfx->f('empresa_nombre')),
                        'sucursalNombre' => strtoupper($oIfx->f('sucursal_nombre'))
                    );
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sqlAprobadores = "SELECT a.id, a.nombre, a.cargo_id, a.empresa, a.sucursal,
                                c.nombre AS cargo_nombre, e.empr_nom_empr AS empresa_nombre, s.sucu_nom_sucu AS sucursal_nombre
                            FROM comercial.aprobador a
                            INNER JOIN comercial.aprobador_cargo c ON c.id = a.cargo_id
                            INNER JOIN saeempr e ON e.empr_cod_empr = a.empresa
                            INNER JOIN saesucu s ON s.sucu_cod_sucu = a.sucursal AND s.sucu_cod_empr = a.empresa
                            WHERE a.estado = 'S' AND a.empresa = $empresaId AND a.sucursal = $sucursalId
                            ORDER BY COALESCE(c.orden, c.id), a.nombre";

        if ($oIfx->Query($sqlAprobadores)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $payload['aprobadores'][] = array(
                        'id' => strval($oIfx->f('id')),
                        'nombre' => strtoupper($oIfx->f('nombre')),
                        'grupoId' => strval($oIfx->f('cargo_id')),
                        'grupoNombre' => strtoupper($oIfx->f('cargo_nombre')),
                        'cargo' => strtoupper($oIfx->f('cargo_nombre')),
                        'empresaId' => strval($oIfx->f('empresa')),
                        'sucursalId' => strval($oIfx->f('sucursal')),
                        'empresa' => strtoupper($oIfx->f('empresa_nombre')),
                        'sucursal' => strtoupper($oIfx->f('sucursal_nombre'))
                    );
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }

    $oReturn->script("actualizarDatosAprobadores(" . json_encode($payload, JSON_UNESCAPED_UNICODE) . ");");
    $oReturn->script("jsRemoveWindowLoad();");

    return $oReturn;
}

function guardar_cargo_aprobador($empresaId, $sucursalId, $nombre, $cargoId = 0)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);
    $cargoId = intval($cargoId);
    $nombre = trim($nombre);
    $nombreMayus = strtoupper($nombre);

    if ($empresaId == 0 || $sucursalId == 0 || empty($nombre)) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Complete empresa, sucursal y nombre del cargo', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $nombreDb = addslashes($nombreMayus);
    $sqlValida = "SELECT count(*) as total FROM comercial.aprobador_cargo
                    WHERE empresa = $empresaId AND sucursal = $sucursalId AND UPPER(nombre) = UPPER('$nombreDb')";
    if ($cargoId > 0) {
        $sqlValida .= " AND id <> $cargoId";
    }

    $duplicado = consulta_string($sqlValida, 'total', $oIfx, 0);
    if ($duplicado > 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Ya existe un cargo con ese nombre en la sucursal seleccionada', 'error');");
        return $oReturn;
    }

    if ($cargoId > 0) {
        $sql = "UPDATE comercial.aprobador_cargo SET nombre = '$nombreDb' WHERE id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId";
    } else {
        $sqlOrden = "SELECT COALESCE(MAX(orden), 0) + 1 AS orden FROM comercial.aprobador_cargo WHERE empresa = $empresaId AND sucursal = $sucursalId";
        $nuevoOrden = consulta_string($sqlOrden, 'orden', $oIfx, 1);
        $sql = "INSERT INTO comercial.aprobador_cargo (empresa, sucursal, nombre, estado, orden) VALUES ($empresaId, $sucursalId, '$nombreDb', 'S', $nuevoOrden)";
    }

    if ($oIfx->QueryT($sql)) {
        $oReturn->script("mostrarMensajeAprobaciones('Cargo guardado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo guardar el cargo', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function guardar_orden_cargos($empresaId, $sucursalId, $ordenJson)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);
    $ordenes = json_decode($ordenJson, true);

    if ($empresaId == 0 || $sucursalId == 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Seleccione empresa y sucursal antes de guardar el orden', 'error');");
        return $oReturn;
    }

    if (!is_array($ordenes) || empty($ordenes)) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('No hay cargos para guardar el orden', 'error');");
        return $oReturn;
    }

    $idsValidos = array();
    foreach ($ordenes as $idCargo) {
        if (is_numeric($idCargo)) {
            $idsValidos[] = intval($idCargo);
        }
    }

    if (empty($idsValidos)) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('No se identificaron cargos válidos para guardar el orden', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $sqlCase = "UPDATE comercial.aprobador_cargo SET orden = CASE id ";
    $ordenActual = 1;
    foreach ($idsValidos as $idCargo) {
        $sqlCase .= "WHEN $idCargo THEN $ordenActual ";
        $ordenActual++;
    }
    $sqlCase .= "END WHERE empresa = $empresaId AND sucursal = $sucursalId AND id IN (" . implode(',', $idsValidos) . ")";

    if ($oIfx->QueryT($sqlCase)) {
        $oReturn->script("mostrarMensajeAprobaciones('Orden guardado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo guardar el orden de cargos', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function eliminar_cargo_aprobador($cargoId, $empresaId, $sucursalId)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $cargoId = intval($cargoId);
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);

    if ($cargoId == 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo identificar el cargo a eliminar', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx->QueryT("UPDATE comercial.aprobador SET estado = 'N' WHERE cargo_id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId");
    $resultado = $oIfx->QueryT("UPDATE comercial.aprobador_cargo SET estado = 'N' WHERE id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId");

    if ($resultado) {
        $oReturn->script("mostrarMensajeAprobaciones('Cargo eliminado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo eliminar el cargo', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function restaurar_cargo_aprobador($cargoId, $empresaId, $sucursalId)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $cargoId = intval($cargoId);
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);

    if ($cargoId == 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo identificar el cargo a restaurar', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx->QueryT("UPDATE comercial.aprobador SET estado = 'S' WHERE cargo_id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId");
    $resultado = $oIfx->QueryT("UPDATE comercial.aprobador_cargo SET estado = 'S' WHERE id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId");

    if ($resultado) {
        $oReturn->script("mostrarMensajeAprobaciones('Cargo restaurado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo restaurar el cargo', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function guardar_aprobador_modal($empresaId, $sucursalId, $cargoId, $nombre, $aprobadorId = 0)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);
    $cargoId = intval($cargoId);
    $aprobadorId = intval($aprobadorId);
    $nombre = trim($nombre);
    $nombreMayus = strtoupper($nombre);

    if ($empresaId == 0 || $sucursalId == 0 || $cargoId == 0 || empty($nombre)) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Complete los datos del aprobador antes de guardar', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $cargoValido = consulta_string("SELECT count(*) as total FROM comercial.aprobador_cargo WHERE id = $cargoId AND empresa = $empresaId AND sucursal = $sucursalId AND estado = 'S'", 'total', $oIfx, 0);
    if ($cargoValido == 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Seleccione un cargo vÃ¡lido para el aprobador', 'error');");
        return $oReturn;
    }

    $nombreDb = addslashes($nombreMayus);
    $sqlValida = "SELECT count(*) as total FROM comercial.aprobador
                    WHERE empresa = $empresaId AND sucursal = $sucursalId AND UPPER(nombre) = UPPER('$nombreDb')";
    if ($aprobadorId > 0) {
        $sqlValida .= " AND id <> $aprobadorId";
    }

    $duplicado = consulta_string($sqlValida, 'total', $oIfx, 0);
    if ($duplicado > 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('Ya existe un aprobador con ese nombre en la sucursal seleccionada', 'error');");
        return $oReturn;
    }

    if ($aprobadorId > 0) {
        $sql = "UPDATE comercial.aprobador SET nombre = '$nombreDb', cargo_id = $cargoId WHERE id = $aprobadorId AND empresa = $empresaId AND sucursal = $sucursalId";
    } else {
        $sql = "INSERT INTO comercial.aprobador (empresa, sucursal, cargo_id, nombre, estado) VALUES ($empresaId, $sucursalId, $cargoId, '$nombreDb', 'S')";
    }

    if ($oIfx->QueryT($sql)) {
        $oReturn->script("mostrarMensajeAprobaciones('Aprobador guardado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo guardar el aprobador', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function eliminar_aprobador_modal($aprobadorId, $empresaId, $sucursalId)
{
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();
    $aprobadorId = intval($aprobadorId);
    $empresaId = intval($empresaId);
    $sucursalId = intval($sucursalId);

    if ($aprobadorId == 0) {
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo identificar el aprobador a eliminar', 'error');");
        return $oReturn;
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $resultado = $oIfx->QueryT("UPDATE comercial.aprobador SET estado = 'N' WHERE id = $aprobadorId AND empresa = $empresaId AND sucursal = $sucursalId");

    if ($resultado) {
        $oReturn->script("mostrarMensajeAprobaciones('Aprobador eliminado correctamente', 'success');");
        $oReturn->script("xajax_obtener_catalogo_aprobadores($empresaId, $sucursalId);");
    } else {
        $oReturn->script("mostrarMensajeAprobaciones('No se pudo eliminar el aprobador', 'error');");
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
