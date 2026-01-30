<?php
require("_Ajax.comun.php"); // No modificar esta linea


function cargar_cant($aForm = '', $op = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $provincia = $aForm['empr_cod_prov'] ? $aForm['empr_cod_prov'] : 0;

    $parroquia = $aForm['empr_cod_parr'];

    if (!empty($parroquia)) {
        $oReturn->script('limpiar_lista_by_id(`empr_cod_parr`);');
    }
    try {
        $oReturn->script('limpiar_lista_by_id(`empr_cod_cant`);');


        $sql = "select cant_cod_cant, cant_des_cant ,cant_cod_prov from saecant where
                cant_cod_prov = '$provincia' order by cant_des_cant";

        $i = 0;
        $msn = "-- Seleccione una Opcion --";

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $id = $oIfx->f('cant_cod_cant');
                    $ciud = $oIfx->f('cant_des_cant');
                    $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',\'' . $id . '\', \'' . $ciud . '\' )'));
                    $i++;
                } while ($oIfx->SiguienteRegistro());
                $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',"", \'' . $msn . '\' )'));
            } else {
                // $oReturn->script('limpiar_lista();');
                $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',"", \'' . $msn . '\' )'));
            }
        }
        $oIfx->Free();
    } catch (Exception $e) {

        $oReturn->alert("error: " . $e->getMessage());
    }
    return $oReturn;
}



function cargar_parr($aForm = '', $op = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $canton = $aForm['empr_cod_cant'];
    $ciudad = $aForm['empr_cod_ciud'];
    $parroquia = $aForm['empr_cod_parr'];

    // if (empty($parroquia)){
    //     return $oReturn;
    // }

    $filtro_parr = "";
    if (!empty($canton)) {
        $filtro_parr = " where parr_cod_cant = '$canton' ";
    }

    $sql = "SELECT parr_cod_parr, parr_des_parr,parr_cod_ciud, parr_cod_cant from saeparr  $filtro_parr order by parr_des_parr";

    // echo "$sql";exit;

    $i = 0;
    $msn = "-- Seleccione una Opcion --";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('parr_cod_parr');
                $ciud = $oIfx->f('parr_des_parr');
                $oReturn->script(('anadir_elemento_comun_parroquia(' . $i . ',\'' . $id . '\', \'' . $ciud . '\' )'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $oReturn->script(('anadir_elemento_comun_parroquia(' . $i . ',"", \'' . $msn . '\' )'));
        } else {
            // $oReturn->script('limpiar_lista_by_id(`parroquia`);');

            $oReturn->script(('anadir_elemento_comun_parroquia(' . $i . ',"", \'' . $msn . '\' )'));
        }
    }
    $oIfx->Free();

    return $oReturn;
}

function cargar_ciud($aForm = '', $op = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $canton = $aForm['empr_cod_cant'];
    $provincia = $aForm['empr_cod_prov'];

    $oReturn->script('limpiar_lista_by_id(`empr_cod_ciud`);');
    // $oReturn->script('limpiar_lista_by_id(`empr_cod_parr`);');

    $sql = "SELECT ciud_cod_ciud, ciud_nom_ciud, ciud_cod_prov from saeciud where ciud_cod_prov = '$provincia'";
    // echo $sql;exit;
    $i = 0;
    $msn = "-- Seleccione una Opcion --";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('ciud_cod_ciud');
                $ciud = $oIfx->f('ciud_nom_ciud');
                $oReturn->script(('anadir_elemento_comun(' . $i . ',\'' . $id . '\', \'' . $ciud . '\' )'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        } else {
            // $oReturn->script('limpiar_lista_by_id(`ciudad`);');

            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        }
    }
    $oIfx->Free();


    return $oReturn;
}

function test_conexion_sftp_pichincha($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();


    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];

    $oReturn = new xajaxResponse();

    // var_dump($aForm);exit;


    try {

        $empr_bpi_sftp_sn    = val_check_inv($aForm['empr_bpi_sftp_sn'], 'S', 'N');

        if ($empr_bpi_sftp_sn == 'N') {
            throw new Exception("Habilite el check");
        }

        $empr_bpi_sftp_user         = $aForm['empr_bpi_sftp_user'];
        $empr_bpi_sftp_ip           = $aForm['empr_bpi_sftp_ip'];
        $empr_bpi_sftp_port         = $aForm['empr_bpi_sftp_port'];

        $empr_bpi_sftp_ppk_f_dir    = explode('/', $aForm['empr_bpi_sftp_ppk_f_dir']);
        $empr_bpi_sftp_remote_dir   = $aForm['empr_bpi_sftp_remote_dir'];
        $empr_bpi_sftp_local_dir    = $aForm['empr_bpi_sftp_local_dir'];


        $arr_len = count($empr_bpi_sftp_ppk_f_dir);
        $empr_bpi_sftp_ppk_f_dir    = $empr_bpi_sftp_ppk_f_dir[($arr_len - 1)];

        // // Importamos la clase para realizar los envios por sftp del pichincha
        include('../int_solicitud_pago/cron/sftp.class.php');
        $new_sftp = new NewSFTP();

        $empr_bpi_sftp_ppk_f_dir = "../../Include/Clases/Formulario/Plugins/reloj/$empr_bpi_sftp_ppk_f_dir";
        $mensaje = '';


        $server = $empr_bpi_sftp_ip . ':' . $empr_bpi_sftp_port;
        $login_result   = $new_sftp->login_check($server, $empr_bpi_sftp_user, $empr_bpi_sftp_ppk_f_dir);

        if (array_key_exists('login', $login_result)) {
            $login_sftp     = $login_result['login'];

            $type_login       = $login_sftp['type'];
            $message_login    = $login_sftp['message'];
            $data_login       = $login_sftp['data'];
            $estado_login     = $login_sftp['estado'];
        }
        if (array_key_exists('error', $login_result)) {
            $error_sftp = $login_result['error'];

            $type_error       = $error_sftp['type'];
            $message_error    = $error_sftp['message'];
            $data_error       = $error_sftp['data'];
            $estado_error     = $error_sftp['estado'];
        }

        $mensaje    = ($login_sftp ? $message_login : "") . ($error_sftp ? ':' . $message_error : "");

        $oReturn->alert($mensaje);
    } catch (Exception $e) {

        $oReturn->alert("error: " . $e->getMessage());
    }



    return $oReturn;
}


function guardar_detxml($id, $titulo, $detalle, $orden, $estado, $exe, $xml, $pdf)
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];
    $fecha_hora = date('Y-m-d H:i:s');

    $titulo = trim($titulo);
    $detalle = trim($detalle);

    if (!empty($idempresa)) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            if ($exe == 1) {
                $sql = "update saeixml
				set 
                    ixml_tit_ixml='$titulo' ,
                    ixml_det_ixml='$detalle' ,
                    ixml_ord_ixml ='$orden' ,
                    ixml_est_ixml='$estado', 
                    ixml_updated_at ='$fecha_hora',
				    ixml_user_updated =$usuario_web,
                    ixml_sn_xml = '$xml',
                    ixml_sn_pdf = '$pdf'
				    where id=$id";
            } else {
                $sql = "update saeixml
				set 
				
				ixml_deleted_at ='$fecha_hora',
				  ixml_user_deleted =$usuario_web,
				  ixml_est_deleted ='N'
				  where id=$id";
            }


            $oCon->QueryT($sql);
            $oCon->QueryT('COMMIT');
            if ($exe == 1) {
                $oReturn->alert('Actualizado Correctamente');
            } else {
                $oReturn->alert('Eliminado Correctamente');
            }
        } catch (Exception $e) {

            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert("Su sesion finalizo vuelva a ingresar");
        $oReturn->script("recargar_formulario();");
    }

    $oReturn->script("consulta_infoxml();");

    return $oReturn;
}

function guardar_detpdf($id, $titulo, $detalle, $formato, $orden, $estado, $exe)
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];
    $fecha_hora = date('Y-m-d H:i:s');

    $titulo = trim($titulo);
    $detalle = trim(nl2br($detalle));


    if (!empty($idempresa)) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            if ($exe == 1) {
                $sql = "update saeipdf
				set 
				ipdf_tit_ipdf='$titulo' ,
				ipdf_det_ipdf='$detalle' ,
                ipdf_tip_ipdf=$formato ,
				ipdf_ord_ipdf ='$orden' ,
				ipdf_est_ipdf='$estado', 
				ipdf_updated_at ='$fecha_hora',
				  ipdf_user_updated =$usuario_web
				  where id=$id";
            } else {
                $sql = "update saeipdf
				set 
				
				ipdf_deleted_at ='$fecha_hora',
				  ipdf_user_deleted =$usuario_web,
				  ipdf_est_deleted ='N'
				  where id=$id";
            }


            $oCon->QueryT($sql);
            $oCon->QueryT('COMMIT');
            if ($exe == 1) {
                $oReturn->alert('Actualizado Correctamente');
            } else {
                $oReturn->alert('Eliminado Correctamente');
            }
        } catch (Exception $e) {

            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert("Su sesion finalizo vuelva a ingresar");
        $oReturn->script("recargar_formulario();");
    }

    $oReturn->script("consulta_infopdf();");

    return $oReturn;
}

function get_tag_config()
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    try {

        $sql_geo = "SELECT * from (SELECT 
                                    id, 
                                    pais_cod_pais, 
                                    pais_etiq_pais, 
                                    pais_tbl_direc,
                                    pais_etiq_est, 
                                    case WHEN pais_tbl_direc = 'saeprov' then 1 
                                    WHEN pais_tbl_direc = 'saecant' then 2 
                                    WHEN pais_tbl_direc = 'saeciud' then 3 
                                    WHEN pais_tbl_direc = 'saeparr' then 4 
                                    end as orden,
                                    case 
                                    when pais_tbl_direc like 'saeprov' then 'prov_cod_prov'
                                    when pais_tbl_direc like 'saecant' then 'cant_cod_cant'
                                    when pais_tbl_direc like 'saeciud' then 'ciud_cod_ciud'
                                    when pais_tbl_direc like 'saeparr' then 'parr_cod_parr'
                                    end as table_row_id,
                                    case 
                                    when pais_tbl_direc like 'saeprov' then 'prov_des_prov'
                                    when pais_tbl_direc like 'saecant' then 'cant_des_cant'
                                    when pais_tbl_direc like 'saeciud' then 'ciud_nom_ciud'
                                    when pais_tbl_direc like 'saeparr' then 'parr_des_parr'
                                    end as table_row_nombre
                                FROM comercial.pais_etiq_contr 
                                WHERE pais_etiq_est = 'A'
                                                        ) geoconfig ORDER BY orden;";

        $arrayGeo = array();
        if ($oIfx->Query($sql_geo)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arraySucu);
                do {

                    $arrayGeo[$oIfx->f('pais_cod_pais')][$oIfx->f('orden')] = array(
                        "table" => $oIfx->f('pais_tbl_direc'),
                        "tag" => $oIfx->f('pais_etiq_pais'),
                        "orden" => $oIfx->f('orden'),
                        "col_id_nom" => $oIfx->f('table_row_id'),
                        "col_des_nom" => $oIfx->f('table_row_nombre')
                    );
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        return $arrayGeo;
    } catch (Exception $e) {
        return array();
    }
}

function ingresar_detxml($empresa, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];
    $titulo = trim($aForm['empr_tit_xml']);
    $detalle = trim($aForm['empr_det_xml']);
    $empresa = $aForm['empr_cod_empr'];
    $fecha_hora = date('Y-m-d H:i:s');

    $xml_sn = trim($aForm['sn_xml']);
    if (empty($xml_sn)) $xml_sn = 'N';

    $pdf_sn = trim($aForm['sn_pdf']);
    if (empty($pdf_sn)) $pdf_sn = 'N';





    if (!empty($idempresa)) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            $sqlord = "select ixml_ord_ixml from saeixml where ixml_cod_empr=$empresa order by 1 desc limit 1";
            $orden = intval(consulta_string($sqlord, 'ixml_ord_ixml', $oCon, 0)) + 1;
            $sql = "insert into saeixml
				(ixml_cod_empr,
				ixml_tit_ixml ,
				ixml_det_ixml ,
				ixml_est_ixml ,
				ixml_ord_ixml ,
				ixml_user_web ,
				ixml_created_at ,
				ixml_user_created,
                ixml_sn_xml,
                ixml_sn_pdf)
                values(
                    $empresa, '$titulo', '$detalle','S',$orden,
                    $usuario_web, '$fecha_hora',$usuario_web,'$xml_sn', '$pdf_sn')";
            $oCon->QueryT($sql);

            $oCon->QueryT('COMMIT');
            $oReturn->alert('Ingresado Correctamente');
        } catch (Exception $e) {

            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert("Su sesion finalizo vuelva a ingresar");
        $oReturn->script("recargar_formulario();");
    }

    $oReturn->script("consulta_infoxml();");

    return $oReturn;
}


function ingresar_detpdf($empresa, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];
    $titulo = trim($aForm['empr_tit_pdf']);
    $detalle = trim(nl2br($aForm['empr_det_pdf']));
    $tipo = $aForm['empr_tip_pdf'];
    $empresa = $aForm['empr_cod_empr'];
    $fecha_hora = date('Y-m-d H:i:s');


    if (!empty($idempresa)) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            $sqlord = "select ipdf_ord_ipdf from saeipdf where ipdf_cod_empr=$empresa order by 1 desc limit 1";
            $orden = intval(consulta_string($sqlord, 'ipdf_ord_ipdf', $oCon, 0)) + 1;
            $sql = "insert into saeipdf
				(ipdf_cod_empr,
				ipdf_tit_ipdf ,
				ipdf_det_ipdf ,
                ipdf_tip_ipdf ,
				ipdf_est_ipdf ,
				ipdf_ord_ipdf ,
				ipdf_user_web ,
				ipdf_created_at ,
				ipdf_user_created)
                values(
                    $empresa, '$titulo', '$detalle','$tipo','S',$orden,
                    $usuario_web, '$fecha_hora',$usuario_web)";
            $oCon->QueryT($sql);

            $oCon->QueryT('COMMIT');
            $oReturn->alert('Ingresado Correctamente');
        } catch (Exception $e) {

            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert("Su sesion finalizo vuelva a ingresar");
        $oReturn->script("recargar_formulario();");
    }

    $oReturn->script("consulta_infopdf();");

    return $oReturn;
}


function consultar_infoxml($empresa)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $table_op = '';
    $table_op .= '<table id="tbxml" class="table table-striped table-bordered table-hover table-condensed table-responsive"  align="center">';
    $table_op .= '<thead>';
    $table_op .= '<tr><th colspan="9">LISTA DE ETIQUETAS XML</th></tr>
                    <tr>
                        <th class="success" style="color: #00859B; font-weight: bold">ID</th>
                        <th class="success" style="color: #00859B; font-weight: bold">TITULO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DETALLE</th>
                        <th class="success" style="color: #00859B; font-weight: bold">MOSTRAR PDF</th>
                        <th class="success" style="color: #00859B; font-weight: bold">MOSTRAR XML</th>
                        <th class="success" style="color: #00859B; font-weight: bold">ORDEN</th>
                        <th class="success" style="color: #00859B; font-weight: bold">ESTADO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">EDITAR</th> 
						<th class="success" style="color: #00859B; font-weight: bold">ELIMINAR</th>  
                    </tr>					
        				</thead>';
    $table_op .= '<tbody>';


    $sql = "select * from saeixml where ixml_cod_empr=$empresa and ixml_est_deleted ='S' order by ixml_ord_ixml";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $idItem = $oCon->f('id');
                $titulo = $oCon->f('ixml_tit_ixml');
                $detalle = $oCon->f('ixml_det_ixml');

                $orden = $oCon->f('ixml_ord_ixml');
                $pdf = $oCon->f('ixml_sn_pdf');
                if ($pdf == 'S') {
                    $valpdf = 'checked';
                } else {
                    $valpdf = '';
                }
                $xml = $oCon->f('ixml_sn_xml');
                if ($xml == 'S') {
                    $valxml = 'checked';
                } else {
                    $valxml = '';
                }
                $estado = $oCon->f('ixml_est_ixml');
                if ($estado == 'S') {
                    $val = 'checked';
                } else {
                    $val = '';
                }

                $img = '<span class="btn btn-primary btn-sm" title="Editar" value="Editar" onClick="javascript:edita_eli_detxml(' . $idItem . ',1);">
				<i class="glyphicon glyphicon-floppy-disk"></i>
				</span>';
                $eli = '<span class="btn btn-danger btn-sm" title="Eliminar" value="Eliminar" onClick="javascript:edita_eli_detxml(' . $idItem . ',2);">
				<i class="glyphicon glyphicon-remove"></i>
				</span>';
                $table_op .= '<tr>';
                $table_op .= '<td align="center">' . $idItem . '</td>';
                $table_op .= '<td> <input type="text"  id="tit_' . $idItem . '" name="tit_' . $idItem . '"  value="' . $titulo . '" class="form-control"  /> </td>';
                $table_op .= '<td><textarea name="det_' . $idItem . '"  id="det_' . $idItem . '" rows="5" cols="30">' . $detalle . '</textarea> </td>';
                $table_op .= '<td align="center"><input type="checkbox" id="pdf_' . $idItem . '" name="pdf_' . $idItem . '" ' . $valpdf . '/></td>';
                $table_op .= '<td align="center"><input type="checkbox" id="xml_' . $idItem . '" name="xml_' . $idItem . '" ' . $valxml . '/></td>';
                $table_op .= '<td align="center"><input type="number"  id="ord_' . $idItem . '" name="ord_' . $idItem . '"  value="' . $orden . '" class="form-control"  /> </td>';
                $table_op .= '<td align="center"><input type="checkbox" id="est_' . $idItem . '" name="est_' . $idItem . '" ' . $val . '/></td>';
                $table_op .= '<td align="center">' . $img . '</td>';
                $table_op .= '<td align="center">' . $eli . '</td>';
                $table_op .= '</tr>';
            } while ($oCon->SiguienteRegistro());

            $table_op .= '</tbody>';
            $table_op .= '</table>';
        } else {
            $table_op = '<div style="color:red;" align="center"><span>NO SE ENCONTRO REGISTROS</span></div>';
        }
    }
    $oCon->Free();


    $oReturn->assign("divFormularioIxml", "innerHTML", $table_op);

    return $oReturn;
}


function consultar_infopdf($empresa)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    $idsucursal = $_SESSION['U_SUCURSAL'];


    $table_op = '';
    $table_op .= '<table id="tbxml" class="table table-striped table-bordered table-hover table-condensed table-responsive"  align="center">';
    $table_op .= '<thead>';
    $table_op .= '<tr><th colspan="8">LISTA DE ETIQUETAS RIDE PDF</th></tr>
                    <tr>
                        <th class="success" style="color: #00859B; font-weight: bold">ID</th>
                        <th class="success" style="color: #00859B; font-weight: bold">TITULO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DETALLE</th>
                        <th class="success" style="color: #00859B; font-weight: bold">FORMATO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">ORDEN</th>
                        <th class="success" style="color: #00859B; font-weight: bold">ESTADO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">EDITAR</th> 
						<th class="success" style="color: #00859B; font-weight: bold">ELIMINAR</th>  
                    </tr>					
        				</thead>';
    $table_op .= '<tbody>';


    $sql = "select * from saeipdf where ipdf_cod_empr=$empresa and ipdf_est_deleted ='S' order by ipdf_ord_ipdf";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $idItem = $oCon->f('id');
                $titulo = $oCon->f('ipdf_tit_ipdf');
                $detalle = $oCon->f('ipdf_det_ipdf');
                $detalle = str_replace('<br />', "\n", $detalle);
                $formato = $oCon->f('ipdf_tip_ipdf');
                $estado = $oCon->f('ipdf_est_ipdf');
                $orden = $oCon->f('ipdf_ord_ipdf');


                $optionFormatos = '';
                $sql = "select 
                emifa_cod_emifa,
                emifa_tip_doc,
                emifa_alia_emifa
                from saeemifa 
                where emifa_cod_empr = $empresa
                and emifa_cod_sucu = $idsucursal
                and emifa_tip_doc in ('FAC', 'BOL')
                and emifa_est_emifa = 'S'";
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConA->f('emifa_cod_emifa') == $formato) {
                                $optionFormatos .= '<option value="' . $oConA->f('emifa_cod_emifa') . '" selected>' . $oConA->f('emifa_alia_emifa') . '</option>';
                            } else {
                                $optionFormatos .= '<option value="' . $oConA->f('emifa_cod_emifa') . '">' . $oConA->f('emifa_alia_emifa') . '</option>';
                            }
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();


                if ($estado == 'S') {
                    $val = 'checked';
                } else {
                    $val = '';
                }

                $img = '<span class="btn btn-primary btn-sm" title="Editar" value="Editar" onClick="javascript:edita_eli_detpdf(' . $idItem . ',1);">
				<i class="glyphicon glyphicon-floppy-disk"></i>
				</span>';
                $eli = '<span class="btn btn-danger btn-sm" title="Eliminar" value="Eliminar" onClick="javascript:edita_eli_detpdf(' . $idItem . ',2);">
				<i class="glyphicon glyphicon-remove"></i>
				</span>';
                $table_op .= '<tr>';
                $table_op .= '<td align="center">' . $idItem . '</td>';
                $table_op .= '<td> <input type="text"  id="tit_' . $idItem . '" name="tit_' . $idItem . '"  value="' . $titulo . '" class="form-control"  /> </td>';
                $table_op .= '<td><textarea name="det_' . $idItem . '"  id="det_' . $idItem . '" rows="5" cols="30">' . $detalle . '</textarea> </td>';
                $table_op .= '<td><select id="tip_' . $idItem . '" name="tip_' . $idItem . '" class="form-control select2" required>
                <option value="">..Seleccione una Opcion..</option>
                    ' . $optionFormatos . '
                </select></td>';
                $table_op .= '<td align="center"><input type="number"  id="ord_' . $idItem . '" name="ord_' . $idItem . '"  value="' . $orden . '" class="form-control"  /> </td>';
                $table_op .= '<td align="center"><input type="checkbox" id="est_' . $idItem . '" name="est_' . $idItem . '" ' . $val . '/></td>';
                $table_op .= '<td align="center">' . $img . '</td>';
                $table_op .= '<td align="center">' . $eli . '</td>';
                $table_op .= '</tr>';
            } while ($oCon->SiguienteRegistro());

            $table_op .= '</tbody>';
            $table_op .= '</table>';
        } else {
            $table_op = '<div style="color:red;" align="center"><span>NO SE ENCONTRO REGISTROS</span></div>';
        }
    }
    $oCon->Free();


    $oReturn->assign("divFormularioIpdf", "innerHTML", $table_op);

    return $oReturn;
}

function genera_formulario($sAccion = 'nuevo', $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $empr_cod_pais         = $_SESSION['U_PAIS_COD'];


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    //CAMPO NUEVO CLINICO


    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'id_dato' AND TABLE_NAME = 'datos_clpv' AND table_schema='clinico'";
    $ctrL_clinico = consulta_string($sqlgein, 'conteo', $oIfx, 0);


    //TABLA DE GEOREFERENCIA VENDEDORES Y TRANSPORTISTAS
    $sql = "SELECT table_name FROM information_schema.columns 
                WHERE table_name='georeferencia_usuario' 
                AND table_schema = 'public'";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $table_name = $oCon->f('table_name');
        }
    }
    $oCon->Free();

    if (empty($table_name)) {
        $sqlCreateTable = 'CREATE TABLE "public"."georeferencia_usuario" (
                            "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
                            INCREMENT 1
                            MINVALUE  1
                            MAXVALUE 2147483647
                            START 1
                            CACHE 1
                            ),
                                "id_user" int4 NOT NULL,
                                "id_modulo" int4 NOT NULL,
                                "latitud" varchar(150) NOT NULL,
                                "longitud" varchar(150) NOT NULL,
                                "tipo" varchar(1) NOT NULL,
                                "fecha" timestamp(6) NOT NULL,
                                "fecha_server" timestamp(6) NOT NULL
                            );';
        $oCon->QueryT($sqlCreateTable);

        $sqlClavePrimaria = 'ALTER TABLE "public"."georeferencia_usuario" ADD CONSTRAINT "georeferencia_usuario_pkey" PRIMARY KEY ("id");';
        $oCon->QueryT($sqlClavePrimaria);
    }


    /**GUIAS BOOSTRA P**/

    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_cod_medi' AND TABLE_NAME = 'saeguia'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saeguia add guia_cod_medi varchar(30);";
        $oCon->QueryT($sqlalter);
    }

    /**DEPOSITO EN B64 EN LA FXFP MAXIMO 1MB */
    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'fxfp_b64_img' AND TABLE_NAME = 'saefxfp'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saefxfp add fxfp_b64_img TEXT;";
        $oCon->QueryT($sqlalter);
    }


    /* AGREGAR A TABLA CLIENTES DE CONSUMIDOR FINAL EL CAMPO DE ESTADO CIVIL */
    $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'estado_civil' AND TABLE_NAME = 'clientes' and table_schema = 'comercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table comercial.clientes add estado_civil varchar(25);";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpag_cod_user' AND TABLE_NAME = 'saedpag'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saedpag add  dpag_cod_user int";
        $oCon->QueryT($sqlalter);
    }

    // agregamos en el detalle de saedpef el tipo del producto
    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpef_tipo_prod' AND TABLE_NAME = 'saedpef'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saedpef add  dpef_tipo_prod varchar(5)";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpag_num_tarj' AND TABLE_NAME = 'saedpag'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saedpag add  dpag_num_tarj varchar(100)";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'alias' AND TABLE_NAME = 'conf_cuentas'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table conf_cuentas add  alias varchar(100)";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'subindice' AND TABLE_NAME = 'conf_cuentas'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table conf_cuentas add  subindice varchar(100)";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_gmaps_sn' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saeempr add empr_gmaps_sn varchar(1) default 'S';";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmcc_cod_dmcc' AND TABLE_NAME = 'det_cierre_caja' and table_schema='comercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table comercial.det_cierre_caja add dmcc_cod_dmcc int default null";
        $oCon->QueryT($sqlalter);
    }

    //INGRESO DE ID MONEDA PARA PRE CIERRE MULTIMONEDA
    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'id_moneda' AND TABLE_NAME = 'det_cierre_caja' and table_schema='comercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table comercial.det_cierre_caja add id_moneda int default 0";
        $oCon->QueryT($sqlalter);
    }



    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'val_depo' AND TABLE_NAME = 'cierre_caja' and table_schema='comercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table comercial.cierre_caja add val_depo float;";
        $oCon->QueryT($sqlalter);
    }



    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'detalle_deposito' AND TABLE_NAME = 'cierre_depo' and table_schema='comercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER table comercial.cierre_depo add detalle_deposito varchar(500);";
        $oCon->QueryT($sqlalter);
    }



    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpag_cod_mone' AND TABLE_NAME = 'saedpag'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saedpag add  dpag_cod_mone int";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_cod_parr' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saeempr add column empr_cod_parr int;";
        $oCon->QueryT($sqlalter);
    }

    // ---------------------------------------------------------------------------------------------------------------------
    // nuevas columnas en la saeprod para almcenar si el codigo de barras va a relacionarse con las unidades de caja
    // ---------------------------------------------------------------------------------------------------------------------
    $sqlgein = "SELECT count(*) as conteo
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE COLUMN_NAME = 'prod_barra_sn_unid' AND TABLE_NAME = 'saeprod'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saeprod add prod_barra_sn_unid varchar(10) default 'S';";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE COLUMN_NAME = 'prod_alterno_sn_unid' AND TABLE_NAME = 'saeprod'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saeprod add prod_alterno_sn_unid varchar(10) default 'S';";
        $oCon->QueryT($sqlalter);
    }
    // ---------------------------------------------------------------------------------------------------------------------
    // FIN nuevas columnas en la saeprod para almcenar si el codigo de barras va a relacionarse con las unidades de caja
    // ---------------------------------------------------------------------------------------------------------------------

    $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'clpv_ret_sn' AND TABLE_NAME = 'saeclpv'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER table saeclpv add column clpv_ret_sn varchar(1) set default 'N';";
        $oCon->QueryT($sqlalter);
        $oCon->Free();
    }

    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_cod_medi' AND TABLE_NAME = 'saeguia'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saeguia add guia_cod_medi varchar(30);";
        $oCon->QueryT($sqlalter);
    }

    // ---------------------------------------------------------------------------------------------------------
    // Creamos la tabla en caso de que no exista para el presupuesto de compras inventario Adrian
    // ---------------------------------------------------------------------------------------------------------
    $sqlinfpresu = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'presupuesto_compras' and table_schema='public'";
    $ctralterpresu = consulta_string($sqlinfpresu, 'conteo', $oCon, 0);
    if ($ctralterpresu == 0) {
        $sqltbpresu = "CREATE TABLE presupuesto_compras (
                                    id serial,
                                    id_empresa integer DEFAULT NULL,
                                    id_sucursal integer DEFAULT NULL,
                                    anio integer DEFAULT NULL,
                                    mes integer DEFAULT NULL,
                                    semana integer DEFAULT NULL,
                                    fecha_ini date DEFAULT NULL,
                                    fecha_fin date DEFAULT NULL,
                                    presupuesto_general float DEFAULT NULL,
                                    presupuesto_semana float DEFAULT NULL,
                                    cotizacion float DEFAULT NULL,
                                    usuario integer DEFAULT NULL,
                                    fecha_server timestamp DEFAULT NULL,
                                    usuario_act integer DEFAULT NULL,
                                    fecha_actualiza timestamp DEFAULT NULL
                            );";
        $oCon->QueryT($sqltbpresu);
    }
    // ---------------------------------------------------------------------------------------------------------
    // Creamos la tabla en caso de que no exista para el presupuesto de compras inventario Adrian
    // ---------------------------------------------------------------------------------------------------------


    /************** AGREGACION DE LA COLUMNA CLPV_TEC_SN A LA TABLA SAECLPV */

    $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'clpv_tec_sn' AND TABLE_NAME = 'saeclpv'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeclpv ADD COLUMN clpv_tec_sn VARCHAR(2);";
        $oCon->QueryT($sqlalter);
    }

    /************** AGREGACION DE LA COLUMNA dpag_fech_oper A LA TABLA DPAG */


    $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpag_fech_oper' AND TABLE_NAME = 'saedpag'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saedpag ADD COLUMN dpag_fech_oper timestamp;";
        $oCon->QueryT($sqlalter);
    }

    //ALTER CONTROL AUX PEMP
    $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pemp_val_aux' AND TABLE_NAME = 'saepemp'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saepemp ADD COLUMN pemp_val_aux numeric(18,2) DEFAULT 0;";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'amem_hor_e35' AND TABLE_NAME = 'saeamem'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeamem ADD COLUMN amem_hor_e35 numeric(6,2) DEFAULT 0";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'amem_hor_e125' AND TABLE_NAME = 'saeamem'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeamem ADD COLUMN amem_hor_e125 numeric(6,2) DEFAULT 0";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'amem_hor_e200' AND TABLE_NAME = 'saeamem'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeamem ADD COLUMN amem_hor_e200 numeric(6,2) DEFAULT 0";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'amem_hor_aux' AND TABLE_NAME = 'saeamem'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeamem ADD COLUMN amem_hor_aux numeric(6,2) DEFAULT 0";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'amem_hor_aux2' AND TABLE_NAME = 'saeamem'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    //echo $ctralter;exit;
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeamem ADD COLUMN amem_hor_aux2 numeric(6,2) DEFAULT 0";
        $oCon->QueryT($sqlalter);
    }

    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'prod_comp_prod' AND TABLE_NAME = 'saeprod'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeprod ADD COLUMN prod_comp_prod varchar(255);";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_sms_remitente' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeempr ADD COLUMN empr_sms_remitente varchar(255);";
        $oCon->QueryT($sqlalter);
    }



    $sqlalter = "ALTER TABLE saeamem
  ALTER COLUMN amem_hor_e00 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e025 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e35 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e050 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e100 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e125 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e150 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_e200 TYPE numeric(6,2),
  ALTER COLUMN amem_hor_lice TYPE numeric(6,2);";
    $oIfx->QueryT($sqlalter);


    $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_bus_pers' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "ALTER TABLE saeempr ADD COLUMN empr_bus_pers varchar(1) DEFAULT 'N';";
        $oCon->QueryT($sqlalter);
    }

    /*
    $sql_dpag = "ALTER TABLE saedpag ADD COLUMN IF NOT EXISTS dpag_fech_oper timestamp;";
    $oCon->QueryT($sql_dpag);
    */


    /* $sql_clpv_size = "alter table saeclpv 
                    alter column clpv_obs_clpv type varchar(200) 
                    using clpv_obs_clpv::varchar(200);";
    $oCon->QueryT($sql_clpv_size); */



    try {

        //lectura sucia
        //////////////

        $idempresa = $_SESSION['U_EMPRESA'];
        $idsucursal = $_SESSION['U_SUCURSAL'];
        $codigo_pais = $_SESSION['S_PAIS_API_SRI'];

        //FORMATOS RIDE FACTURACION
        $optionRide = '';
        $sqlride = "select ftrn_cod_ftrn, ftrn_des_ftrn from saeftrn where ftrn_cod_modu=7 and ftrn_cod_empr=$idempresa order by 2";
        if ($oCon->Query($sqlride)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $optionRide .= '<option value="' . $oCon->f('ftrn_cod_ftrn') . '">' . $oCon->f('ftrn_des_ftrn') . '</option>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //VALIDACION EMPRESAS DE PERU FROAMTO RIDE FACTURAS, BOLETAS DE VENTA NCRE
        $sql_nombre_empresa = "SELECT split_part(empr_nom_empr,' ',1) as nombre_empresa from saeempr where empr_cod_empr= $idempresa";
        $nombre_empresa = consulta_string($sql_nombre_empresa, 'nombre_empresa', $oCon, $idempresa);
        $oCon->Free();





        //VALIDACION EMPRESAS DE PERU - COLOMBIA FORMATO RIDE FACTURAS, BOLETAS DE VENTA NCRE

        $htmlpais = '';


        if ($codigo_pais == '51' || $codigo_pais == '57') {
            $optionFormatos = '';
            $sql = "select 
            emifa_cod_emifa,
            emifa_tip_doc,
            emifa_alia_emifa
            from saeemifa 
            where emifa_cod_empr = $idempresa
            and emifa_cod_sucu = $idsucursal
            and emifa_tip_doc in ('FAC', 'BOL')
            and emifa_est_emifa = 'S'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $optionFormatos .= '<option value="' . $oCon->f('emifa_cod_emifa') . '">' . $oCon->f('emifa_alia_emifa') . '</option>';
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            $htmlpais = ' <tr>
            <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">INFORMACION ADICIONAL  RIDE FACTURACION</td>
       </tr>';

            $htmlpais .= '<tr> 
        <td>Titulo</td>
        <td><input type="text" id="empr_tit_pdf" name="empr_tit_pdf"/></td>
        
        <td>Detalle:</td>
        <td colpan="2"><textarea name="empr_det_pdf"  id="empr_det_pdf" rows="5" cols="30"></textarea></td>
        <td>                     
        <label class="control-label" for="empr_tip_pdf">* Tipo Formato:</label> <select id="empr_tip_pdf" name="empr_tip_pdf" class="form-control select2" required>
        <option value="">..Seleccione una Opcion..</option>
            ' . $optionFormatos . '
        </select></td>
        </tr>
        <tr>
        <td>Consultar:</td>
        <td><span class="btn btn-primary btn-sm" title="Consultar" value="Consultar" onClick="javascript:consulta_infopdf();">
        <i class="glyphicon glyphicon-search"></i>
        </span></td>
        <td>Ingresar:</td>
        <td><span class="btn btn-success btn-sm" title="Ingresar" value="Ingresar" onClick="javascript:ingresa_detpdf();">
        <i class="glyphicon glyphicon-plus"></i>
        </span></td>
        <td>&nbsp;&nbsp;</td>
        <td>&nbsp;&nbsp;</td>
        </tr>';
        }

        switch ($sAccion) {
            case 'nuevo':

                //CAMPO UNICODIGO CLINICA
                $ifu->AgregarCampoTexto('empr_cod_uni', 'UNICODIGO|left', false, '', 250, 1000, true);
                //CAMPOS INFO XML

                $ifu->AgregarCampoTexto('empr_tit_xml', 'Titulo|left', false, '', 250, 1000, true);

                //FORMATOS PERSONALIZADOS

                $ifu->AgregarCampoCheck('empr_rol_sn', 'Rol Pagos|left', false, 'N', 250, 1000, true);

                $ifu->AgregarCampoCheck('empr_tip_comp', 'Tipo Comprob FAC S/N|left', false, 'N', true);
                $ifu->AgregarCampoCheck('empr_tip_agri', 'FAC Agricola S/N|left', false, 'N', true);

                $ifu->AgregarCampoTexto('empr_dig_celu', 'Número de dígitos celular|left', false, '', 250, 200, true);
                $ifu->AgregarCampoCheck('empr_for_rdep', 'Formulario 107 S/N|left', false, 'N', true);

                //CORREOS

                $ifu->AgregarCampoCheck('empr_ema_sn', 'Correo Pruebas S/N|left', false, 'N', true);
                $ifu->cCampos['empr_ema_sn']->xValor = 'S';
                $ifu->AgregarComandoAlCambiarValor('empr_ema_sn', 'carga_correo();');

                $ifu->AgregarCampoCheck('empr_cfe_contr', 'Considerar contrato en facturacion electronica|left', false, 'N', true);
                $ifu->cCampos['empr_cfe_contr']->xValor = 'S';

                $ifu->AgregarCampoCheck('empr_mone_fxfp', 'Guardar moneda en forma de pago|left', false, 'N', true);
                $ifu->cCampos['empr_mone_fxfp']->xValor = 'S';

                $ifu->AgregarCampoCheck('empr_asum_igtf', 'Asume IGTF|left', false, 'N', true);
                $ifu->cCampos['empr_asum_igtf']->xValor = 'S';

                $ifu->AgregarCampoTexto('empr_ema_test', 'Correo|left', false, '', 250, 200, true);


                $ifu->AgregarCampoTexto('empr_cod_empr', 'Codigo|left', false, '', 100, 5, true);
                $ifu->AgregarComandoAlPonerEnfoque('empr_cod_empr', 'this.blur()');
                $ifu->AgregarCampoTexto('empr_cod_aduna', 'Codigo Aduana|left', false, '', 100, 100, true);
                $ifu->AgregarCampoTexto('empr_cod_cesa', 'Codigo CESA|left', false, '', 100, 100, true);
                $ifu->AgregarCampoCheck('empr_conta_sn', 'Obligado a Llevar Contabilidad|left', false, 'N', true);
                $ifu->AgregarCampoTexto('empr_nom_empr', 'Nombre|left', true, '', 550, 200, true);
                $ifu->AgregarCampoTexto('empr_nomcome_empr', 'Nombre Comercial|left', true, '', 550, 200, true);
                $ifu->AgregarCampoListaSQL('empr_pais_ruc', 'Pais Validacion RUC|left', "SELECT saepais.pais_cod_pais,  
																						  saepais.pais_des_pais FROM saepais ", true, 250, 200, true);
                $ifu->AgregarCampoCheck('empr_con_pres', 'Presupuesto S/N|left', false, 'N');
                $ifu->AgregarCampoNumerico('empr_iva_empr', 'Iva|left', false, '', 50, 50, true);

                $ifu->AgregarCampoCheck('empr_bus_pers', 'Buscador personalizado S/N|left', false, 'N');

                //--------------------------------------------------------------------------------------------------------------
                //INICIO PARAMETRO VALIDACION DOCUMENTOS UAFE
                //--------------------------------------------------------------------------------------------------------------
                $ifu->AgregarCampoCheck('emmpr_uafe_cprov', 'Control Documentos UAFE S/N|left', false, 'N', true);


                $label_prov = 'Provincia';
                if ($codigo_pais == '51') {
                    $label_prov = 'Departamento';
                }

                $label_cant = 'Canton';
                if ($codigo_pais == '51') {
                    $label_parr = 'Provincia';
                }

                $label_ciud = 'Ciudad';
                if ($codigo_pais == '51') {
                    $label_ciud = 'Provincia';
                }

                $label_parr = 'Parroquia';
                if ($codigo_pais == '51') {
                    $label_parr = 'Distrito';
                }


                $arrayGeo = get_tag_config();
                $tr_geo = '<tr>';
                // print_r($arrayGeo[$empr_cod_pais]);

                $valid_prov = 0;
                $valid_cant = 0;
                foreach ($arrayGeo[$empr_cod_pais] as $key_conf => $etiqueta_condig) {
                    $table          = $etiqueta_condig['table'];
                    $tag            = $etiqueta_condig['tag'];
                    $orden          = $etiqueta_condig['orden'];
                    $col_id_nom     = $etiqueta_condig['col_id_nom'];
                    $col_des_nom    = $etiqueta_condig['col_des_nom'];


                    switch ($table) {
                        case "saeprov":
                            $valid_prov = 1;
                            $label_prov = $tag;
                            $ifu->AgregarCampoListaSQL('empr_cod_prov', '' . $label_prov . '|left', "SELECT prov_cod_prov,  prov_des_prov FROM saeprov order by  prov_des_prov", true, '200', true);
                            $ifu->AgregarComandoAlCambiarValor('empr_cod_prov', 'cargar_cant();');

                            $tr_geo .=   '<td>' . $ifu->ObjetoHtmlLBL('empr_cod_prov') . '</td>
                                        <td>' . $ifu->ObjetoHtml('empr_cod_prov') . '</td>';
                            break;
                        case "saecant":
                            $valid_cant = 1;
                            // if(!$valid_prov){
                            //     break;
                            // }

                            $label_cant = $tag;
                            $ifu->AgregarCampoListaSQL('empr_cod_cant', '' . $label_cant . '|left', "SELECT cant_cod_cant, cant_des_cant  from saecant ", true, '200', true);
                            $ifu->AgregarComandoAlCambiarValor('empr_cod_cant', 'cargar_ciud();');
                            $tr_geo .=   '<td>' . $ifu->ObjetoHtmlLBL('empr_cod_cant') . '</td>
                                        <td>' . $ifu->ObjetoHtml('empr_cod_cant') . '</td>';

                            break;
                        case "saeciud":
                            // if(!$valid_prov){
                            //     break;
                            // }                        
                            $label_ciud = $tag;
                            $ifu->AgregarCampoListaSQL('empr_cod_ciud', '' . $label_ciud . '|left', "SELECT ciud_cod_ciud, ciud_nom_ciud  from saeciud ", true, '200', true);
                            $ifu->AgregarComandoAlCambiarValor('empr_cod_ciud', 'cargar_parr();');
                            $tr_geo .=   '<td>' . $ifu->ObjetoHtmlLBL('empr_cod_ciud') . '</td>
                                        <td>' . $ifu->ObjetoHtml('empr_cod_ciud') . '</td>';

                            break;
                        case "saeparr":
                            // if(!$valid_cant){
                            //     break;
                            // }

                            $label_parr = $tag;
                            $ifu->AgregarCampoListaSQL('empr_cod_parr', '' . $label_parr . '|left', "SELECT parr_cod_parr, parr_des_parr  from saeparr ", false, '200', true);
                            $tr_geo .=   '<td>' . $ifu->ObjetoHtmlLBL('empr_cod_parr') . '</td>
                                        <td>' . $ifu->ObjetoHtml('empr_cod_parr') . '</td>';
                            break;
                    }
                }
                $tr_geo .= '</tr>';

                $ifu->AgregarCampoTexto('empr_dir_empr', 'Direcccion|left', true, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_ruc_empr', 'Ruc|left', false, '', 250, 200, true);

                $ifu->AgregarCampoTexto('empr_cpo_empr', 'Codigo Postal|left', false, '', 180, 8, true);
                $ifu->AgregarCampoTexto('empr_mai_empr', 'E-mail|left', false, '', 550, 200, true);

                $ifu->AgregarCampoTexto('empr_tel_resp', 'Telefono|left', false, '', 250, 250, true);
                $ifu->AgregarCampoCheck('empr_cta_extr', 'Cuentas Extranjera S/N|left', false, 'N');
                $ifu->AgregarCampoTexto('empr_fax_empr', 'Fax|left', false, '', 180, 250, true);

                $ifu->AgregarCampoTexto('empr_repres', 'Representante|left', false, '', 450, 250, true);
                $ifu->AgregarCampoListaSQL('empr_tip_iden', 'Identificacion|left', "SELECT saetide.tide_cod_tide,  saetide.tide_des_tide  
																						FROM saetide ", true, 250, 200, true);
                $ifu->AgregarCampoTexto('empr_ced_repr', 'Cedula Representante|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_num_dire', 'Dir. Representante|left', false, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_ema_repr', 'Email Representante|left', false, '', 550, 250, true);

                $ifu->AgregarCampoTexto('empr_ema_comp', 'Email Comprobantes|left', false, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_nom_cont', 'Contador|left', false, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_ruc_cont', 'Ruc Contador|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_lic_cont', 'Licencia Contador|left', false, '', 200, 250, true);

                $ifu->AgregarCampoCheck('empr_tip_empr', 'Contribuyente Especial S/N|left', false, 'N');
                $ifu->AgregarCampoTexto('empr_num_resu', 'N. Resolucion|left', false, '', 200, 250, true);


                $ifu->AgregarCampoTexto('empr_ac1_empr', 'Actividad Principal|left', false, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_ac2_empr', 'Actividad Secundaria|left', false, '', 550, 250, true);
                $ifu->AgregarCampoTexto('empr_ac3_empr', 'Actividad Adicional|left', false, '', 550, 250, true);
                $ifu->AgregarCampoCheck('empr_rete_sn', 'Retencion S/N|left', false, 'N');
                $ifu->AgregarCampoCheck('empr_rimp_sn', 'Rimpe S/N|left', false, 'N');

                //JEFE DE TALENTO HUMANO
                $ifu->AgregarCampoTexto('empr_rrhh_nom', 'Jefe de Talento Humano|left', false, '', 550, 250, true);

                //CONFIGURACION FIRMA ELECTRONICA
                $ifu->AgregarCampoArchivo('empr_nom_toke', 'Firma electronica|LEFT', false, '', 100, 100, '', true);
                $ifu->AgregarCampoPassword('empr_pass_toke', 'Password Firma|LEFT', false, '', 100, 100, true);

                $ifu->AgregarCampoArchivo('empr_path_logo', 'Direccion Logotipo|LEFT', false, '', 100, 100, '', true);
                $ifu->AgregarCampoArchivo('empr_cuad_logo', 'Direccion Logocuadro|LEFT', false, '', 100, 100, '', true);
                $ifu->AgregarCampoTexto('empr_web_color', 'Color Principal|left', false, '', 100, 250, true);
                $ifu->AgregarCampoTexto('empr_web_color2', 'Color Secundario|left', false, '', 100, 250, true);


                $ifu->AgregarCampoNumerico('empr_tie_espera', 'Tiempo Maximo Espera|left', false, '', 100, 250, true);
                $ifu->AgregarCampoTexto('empr_num_estab', 'N. Establecimientos ATS|left', false, '', 100, 250, true);
                /*INICIO - VALIDA IDENTIFICAION **/
                $ifu->AgregarCampoCheck('empr_ws_iden_sn', 'Web Service S/N|left', false, 'N');
                $ifu->cCampos['empr_ws_iden_sn']->xValor = 'N';

                $ifu->AgregarCampoTexto('empr_ws_iden_url', 'Web Service URL|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_ws_iden_token', 'Web Service Token|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_ws_iden_renueva', 'Web Service renueva|left', false, '', 200, 250, true);
                /**FIN VALIDA IDENTIFICAION**/

                /*INICIO - WS TIPO DE CAMBIO **/
                $ifu->AgregarCampoCheck('estado_sn', 'Activo S/N|left', false, 'N');
                $ifu->cCampos['estado_sn']->xValor = 'N';
                $ifu->AgregarCampoTexto('nombre_integracion', 'Nombre Integración|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('ambiente', 'Ambiente|left', false, '', 200, 250, true);
                /**FIN WS TIPO DE CAMBIO**/


                $ifu->AgregarCampoCheck('empr_ws_sri_sn', 'Web Service S/N|left', false, 'N');
                $ifu->cCampos['empr_ws_sri_sn']->xValor = 'N';

                $ifu->AgregarCampoTexto('empr_ws_sri_url', 'Web Service URL|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_token_api', 'Web Service Token|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_key_maps', 'Web Service Token Maps|left', false, '', 200, 250, true);

                $ifu->AgregarCampoLista('empr_tip_impr', 'Tipo impresión factura|left', true, 200, 250, true);
                $ifu->AgregarOpcionCampoLista('empr_tip_impr', 'Tirilla', 1);
                $ifu->AgregarOpcionCampoLista('empr_tip_impr', 'Ride', 2);
                $ifu->cCampos['empr_tip_impr']->xValor = 1;


                $ifu->AgregarCampoTexto('empr_dataico_id', 'Dataico ID|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_dataico_token', 'Dataico Token|left', false, '', 200, 250, true);

                // CAMPOS SIIGO
                $ifu->AgregarCampoCheck('empr_siigo_sn', 'SIIGO API S/N|left', false, 'N');
                $ifu->cCampos['empr_siigo_sn']->xValor = 'N';

                $ifu->AgregarCampoCheck('empr_siigo_autoenvio', 'SIIGO ENVIO AUTOMATICO DIAN S/N|left', false, 'N');
                $ifu->cCampos['empr_siigo_autoenvio']->xValor = 'N';

                $ifu->AgregarCampoCheck('empr_siigo_autoenvio_mail', 'SIIGO ENVIO AUTOMATICO MAIL S/N|left', false, 'N');
                $ifu->cCampos['empr_siigo_autoenvio_mail']->xValor = 'N';

                $ifu->AgregarCampoTexto('empr_siigo_api_url', 'SIIGO API URL BASE|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_siigo_username', 'SIIGO USERNAME|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_siigo_access_token', 'SIGO ACCESS TOKEN|left', false, '', 200, 250, true);


                //CAMPOS OPEN PAY

                $ifu->AgregarCampoCheck('empr_openpay_sn', 'OPENPAY S/N|left', false, 'N');
                $ifu->cCampos['empr_openpay_sn']->xValor = 'N';
                $ifu->AgregarCampoTexto('empr_openpay_api_url', 'OPENPAY API URL BASE|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_openpay_idempresa', 'OPENPAY ID EMPRESA|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_openpay_publick', 'OPENPAY ACCESS PUBLIC KEY|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_openpay_privatek', 'OPENPAY ACCESS PRIVADA KEY|left', false, '', 200, 250, true);


                $ifu->AgregarCampoTexto('empr_siigo_partnerid', 'PARTNER ID|left', false, '', 200, 250, true);




                $ifu->AgregarCampoCheck('empr_servi_sn', 'Web Service S/N|left', false, 'N');
                $ifu->cCampos['empr_servi_sn']->xValor = 'N';

                $ifu->AgregarCampoTexto('empr_servi_url', 'Web Service URL|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_servi_user', 'Web Service USER|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_servi_pass', 'Web Service PASSWORD|left', false, '', 200, 250, true);

                $ifu->AgregarCampoTexto('empr_enti_code', 'Codigo entidad|left', false, '', 200, 250, true);

                // Parametros de configuracion de integracion con el ws de laarcourier

                $ifu->AgregarCampoCheck('empr_laar_sn', 'Web Service S/N|left', false, 'N');
                $ifu->cCampos['empr_laar_sn']->xValor = 'N';

                $ifu->AgregarCampoCheck('empr_gmaps_sn', 'Usa Google Maps S/N|left', false, 'N');
                $ifu->cCampos['empr_gmaps_sn']->xValor = 'N';

                $ifu->AgregarCampoTexto('empr_laar_url', 'Web Service URL|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_laar_user', 'Web Service USER|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_laar_pass', 'Web Service PASSWORD|left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_laar_cod', 'Courier code (unique)|left', false, '', 200, 250, true);

                //  Parametros para configurar las credenciales sftp (banco pichincha)

                $ifu->AgregarCampoCheck('empr_bpi_sftp_sn', 'SFTP S/N|left', false, 'N');
                $ifu->cCampos['empr_bpi_sftp_sn']->xValor = 'N';
                $ifu->cCampos['empr_bpi_sftp_remote_dir']->xValor = "/Cash/ECUADOR/$nombre_empresa/RECAUDO/IN";
                $ifu->cCampos['empr_bpi_sftp_remote_dir']->xValor = "/archivos/pichincha/sftp/$nombre_empresa/RECAUDO/OUT";

                $ifu->AgregarCampoTexto('empr_bpi_sftp_user', 'Usuario SFTP |left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_bpi_sftp_ip', 'IP SFTP |left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_bpi_sftp_port', 'Puerto SFTP |left', false, '', 200, 250, true);
                $ifu->AgregarCampoArchivo('empr_bpi_sftp_ppk_f_dir', 'Directorio Archivo PPK |LEFT', false, '', 200, 250, '', true);
                $ifu->AgregarCampoTexto('empr_bpi_sftp_remote_dir', 'Directorio de carga (remota) SFTP |LEFT', false, '', 200, 200,  true);
                $ifu->AgregarCampoTexto('empr_bpi_sftp_local_dir', 'Directorio de descarga (remota) SFTP |LEFT', false, '', 200, 200,  true);

                // api OZMAP
                $ifu->AgregarCampoCheck('empr_ozmap_sn', 'API OZMAP S/N|left', false, 'N');
                $ifu->cCampos['empr_ozmap_sn']->xValor = 'N';
                $ifu->AgregarCampoTexto('empr_ozmap_url', 'URL |left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_ozmap_user', 'Usuario SFTP |left', false, '', 200, 250, true);
                $ifu->AgregarCampoTexto('empr_ozmap_pass', 'Clave |LEFT', false, '', 200, 250, '');
                $ifu->AgregarCampoTexto('empr_ozmap_api_token', 'API Token |LEFT', false, '', 200, 250, '');




                break;
        }

        $sHtml = '<table border="0" class="table table-striped table-condensed" align="center" style="width: 100%;">
                        <tr>
                            <td colspan="6" align="left">
                                <div class="btn-group">
                                    <div class="btn btn-primary btn-sm" onclick="genera_formulario();">
                                        <span class="glyphicon glyphicon-file"></span>
                                        Nuevo
                                    </div>
                                    <div class="btn btn-primary btn-sm" onclick="guardar();">
                                        <span class="glyphicon glyphicon-floppy-disk"></span>
                                        Guardar
                                    </div>
                                </div>
                        </tr>';
        $sHtml .= '<tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">DATOS EMPRESA NUEVA</td>
                    </tr>
                    <tr class="msgFrm">
                        <td colspan="6" align="center">Los campos con * son de ingreso obligatorio</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_cod_empr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_cod_empr') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('empr_cod_aduna') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_cod_aduna') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('empr_cod_cesa') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_cod_cesa') . '</td>	
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_conta_sn') . '</td>
						<td align="left">' . $ifu->ObjetoHtml('empr_conta_sn') . '</td>
                        <td colspan="4" align="left">
                        SI<input type="radio" class="custom-control-input" name="obliconta_sn" id="1" style="width:15px;height:15px" checked value="S">
                        &nbsp;&nbsp;NO<input type="radio" class="custom-control-input" name="obliconta_sn" id="2" style="width:15px;height:15px" value="N"></td>    
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_nom_empr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_nom_empr') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                            <td>' . $ifu->ObjetoHtmlLBL('empr_nomcome_empr') . '</td>
                            <td colspan="5">' . $ifu->ObjetoHtml('empr_nomcome_empr') . '</td>
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_pais_ruc') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_pais_ruc') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_con_pres') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_con_pres') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_iva_empr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_iva_empr') . '</td>	
                    </tr>';
        $sHtml .= $tr_geo;
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_dir_empr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_dir_empr') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ruc_empr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_ruc_empr') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('empr_cpo_empr') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('empr_cpo_empr') . '</td>
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_mai_empr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_mai_empr') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_tel_resp') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_tel_resp') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_cta_extr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_cta_extr') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_fax_empr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_fax_empr') . '</td>	
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_repres') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_repres') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_tip_iden') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_tip_iden') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('empr_ced_repr') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('empr_ced_repr') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_num_dire') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_num_dire') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ema_repr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_ema_repr') . '</td>
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ema_comp') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_ema_comp') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_nom_cont') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_nom_cont') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ruc_cont') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_ruc_cont') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('empr_lic_cont') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('empr_lic_cont') . '</td>
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_tip_empr') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_tip_empr') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_num_resu') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_num_resu') . '</td>	
						<td>Fecha Resolucion:</td>
                        <td><input type="date" name = "empr_fec_resu" id="empr_fec_resu"></td>	
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ac1_empr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_ac1_empr') . '</td>
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_ac2_empr') . '</td>
                        <td colspan="5">' . $ifu->ObjetoHtml('empr_ac2_empr') . '</td>
                    </tr>';

        $sHtml .= '<tr>
					     <td>' . $ifu->ObjetoHtmlLBL('empr_ac3_empr') . '</td>
					     <td colspan="5">' . $ifu->ObjetoHtml('empr_ac3_empr') . '</td>
				   </tr>';

        $sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empr_rimp_sn') . '</td>
						<td>' . $ifu->ObjetoHtml('empr_rimp_sn') . '</td>	
						<td>' . $ifu->ObjetoHtmlLBL('empr_rete_sn') . ' </td>
						<td>' . $ifu->ObjetoHtml('empr_rete_sn') . '</td>	
					</tr>';

        $sHtml .= '<tr>
				   		 <td>' . $ifu->ObjetoHtmlLBL('empr_rrhh_nom') . '</td>
				   		<td colspan="5">' . $ifu->ObjetoHtml('empr_rrhh_nom') . '</td>
			 </tr>	
		<tr>
			 <td >' . $ifu->ObjetoHtmlLBL('empr_tie_espera') . '</td>
			 <td>' . $ifu->ObjetoHtml('empr_tie_espera') . '</td>
			 <td>' . $ifu->ObjetoHtmlLBL('empr_num_estab') . '</td>
			 <td colspan="3">' . $ifu->ObjetoHtml('empr_num_estab') . '</td>
		            </tr>				
					<tr>
					<td>' . $ifu->ObjetoHtmlLBL('empr_ema_sn') . '</td>
					<td>' . $ifu->ObjetoHtml('empr_ema_sn') . '</td>
					<td>' . $ifu->ObjetoHtmlLBL('empr_ema_test') . '</td>
					<td colspan="3">' . $ifu->ObjetoHtml('empr_ema_test') . '</td>
					</tr>
		            </tr>				
					<tr>
					<td>' . $ifu->ObjetoHtmlLBL('empr_cfe_contr') . '</td>
					<td>' . $ifu->ObjetoHtml('empr_cfe_contr') . '</td>
                    <td>' . $ifu->ObjetoHtmlLBL('empr_mone_fxfp') . '</td>
					<td>' . $ifu->ObjetoHtml('empr_mone_fxfp') . '</td>
                    <div id="div_empr_asum_igtf">
                    <td>' . $ifu->ObjetoHtmlLBL('empr_asum_igtf') . '</td>
					<td>' . $ifu->ObjetoHtml('empr_asum_igtf') . '</td>
                    </div>
					</tr>';
        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">INFORMACION BANCARIA FACTURAS</td>
				   </tr>';

        $sHtml .= '<tr>
                   <td>Información Bancaria S/N</td>
                   <td><input type="checkbox" id="empr_cta_sn" name="empr_cta_sn" value="S" onclick="habilita_cta();"/></td>
                   <td>Cuenta Bancaria:</td>
                   <td><textarea name="empr_det_cta"  id="empr_det_cta" rows="5" cols="30" disabled></textarea></td>
              </tr>';

        $sHtml .= '<tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">INFORMACION ADICIONAL RIDE FACTURAS</td>
                </tr>';

        $sHtml .= '<tr>
                    <td>Información Adicional S/N</td>
                    <td><input type="checkbox" id="empr_rinf_sn" name="empr_rinf_sn" value="S" onclick="habilita_rinf();"/></td>
                    <td>Detalle:</td>
                    <td><textarea name="empr_det_rinf"  id="empr_det_rinf" rows="5" cols="30" disabled></textarea></td>
                </tr>';
        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION FIRMA ELECTRONICA</td>
				   </tr>';

        //empr_rol_sn


        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_nom_toke') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_nom_toke') . '</td>
                        

						<td>' . $ifu->ObjetoHtmlLBL('empr_pass_toke') . '</td>
						<td colspan="2">' . $ifu->ObjetoHtml('empr_pass_toke') . '
						<span class="btn btn-success btn-sm" title="Ingresar" value="Ingresar" onClick="javascript:validar_firma();">
                            Validar Firma <i class="glyphicon glyphicon-refresh"></i>
                        </span> <div id="divValidarFirma"></div></td>
						
                    </tr>';



        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">DATOS EMPRESA LOGOS</td>
				   </tr>';

        //empr_rol_sn


        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_path_logo') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_path_logo') . '</td>

						<td>' . $ifu->ObjetoHtmlLBL('empr_cuad_logo') . '</td>
						<td>' . $ifu->ObjetoHtml('empr_cuad_logo') . '</td>

						<td>' . $ifu->ObjetoHtmlLBL('empr_web_color') . '</td>
						<td>' . $ifu->ObjetoHtml('empr_web_color') . '</td>

						
                    </tr>';

        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_web_color2') . '</td>
						<td>' . $ifu->ObjetoHtml('empr_web_color2') . '</td>
                    </tr>';

        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">WEB SERVICE ENTIDAD TRIBUTARIA </td>
				</tr>';
        $sHtml .= '<tr> 

				    <td>' . $ifu->ObjetoHtmlLBL('empr_ws_sri_sn') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_ws_sri_sn') . '</td>
				    
				    <td>' . $ifu->ObjetoHtmlLBL('empr_ws_sri_url') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_ws_sri_url') . '</td>

                    <td>' . $ifu->ObjetoHtmlLBL('empr_token_api') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_token_api') . '</td>

					</tr>';

        $sHtml .= ' <tr>
                            <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">WEB SERVICE VALIDA IDENTIFICACION</td>
                        </tr>';
        $sHtml .= '<tr> 
    
                            <td>' . $ifu->ObjetoHtmlLBL('empr_ws_iden_sn') . '</td>
                            <td>' . $ifu->ObjetoHtml('empr_ws_iden_sn') . '</td>
                            
                            <td>' . $ifu->ObjetoHtmlLBL('empr_ws_iden_url') . '</td>
                            <td>' . $ifu->ObjetoHtml('empr_ws_iden_url') . '</td>

        
                            <td>' . $ifu->ObjetoHtmlLBL('empr_ws_iden_token') . '</td>
                            <td>' . $ifu->ObjetoHtml('empr_ws_iden_token') . '</td>
                        </tr>';

        $sHtml .= '<tr> 
                            <td>' . $ifu->ObjetoHtmlLBL('empr_ws_iden_renueva') . '</td>
                            <td>' . $ifu->ObjetoHtml('empr_ws_iden_renueva') . '</td>
    
                        </tr>';



        $sHtml .= ' <tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">INTEGRACIONES COMERCIALES</td>
                    </tr>';

        $boton_add_integracion = '<span class="btn btn-success btn-sm" title="add_integration" onClick="acciones_integracion(1);">
                                <i class="glyphicon glyphicon-plus"></i>
                            </span>';
        $boton_list_integracion = '<span class="btn btn-success btn-sm" title="add_integration" onClick="acciones_integracion(2);">
                                <i class="glyphicon glyphicon-th-list"></i>
                            </span>';

        $sHtml .= '<tr> 
                        <td> Agregar nueva integracion </td>
                        <td>' . $boton_add_integracion . '</td>
                        <td> Listar integraciones</td>
                        <td>' . $boton_list_integracion . '</td>
                    </tr>';

        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">WEB SERVICE SERVIENTREGA </td>
				</tr>';
        $sHtml .= '<tr> 

				    <td>' . $ifu->ObjetoHtmlLBL('empr_servi_sn') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_servi_sn') . '</td>
				    
				    <td>' . $ifu->ObjetoHtmlLBL('empr_servi_url') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_servi_url') . '</td>

                    <td>' . $ifu->ObjetoHtmlLBL('empr_servi_user') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_servi_user') . '</td>

                </tr>
                <tr>
				    <td>' . $ifu->ObjetoHtmlLBL('empr_servi_pass') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_servi_pass') . '</td>
				</tr>';


        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">CODIGO ENTIDAD </td>
				</tr>';
        $sHtml .= '<tr> 
				    
				    <td>' . $ifu->ObjetoHtmlLBL('empr_enti_code') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_enti_code') . '</td>

                </tr>';


        $sHtml .= '
                    <tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">WEB SERVICE LAARCOURIER </td>
                    </tr>';
        $sHtml .= '<tr> 
    
                        <td>' . $ifu->ObjetoHtmlLBL('empr_laar_sn') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_laar_sn') . '</td>
                        
                        <td>' . $ifu->ObjetoHtmlLBL('empr_laar_url') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_laar_url') . '</td>
    
                        <td>' . $ifu->ObjetoHtmlLBL('empr_laar_user') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_laar_user') . '</td>
                    </tr>

                    <tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_laar_pass') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_laar_pass') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_laar_cod') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_laar_cod') . '</td>
    
                    </tr>';

        // Configuracion parametros SFTP (Bancon Pichincha)

        $sHtml .= '
                    <tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">SFTP BANCO DEL PICHINCHA </td>
                    </tr>';
        $sHtml .= '<tr> 
    
                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_sn') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_sn') . '</td>
                        
                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_user') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_user') . '</td>
    
                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_ip') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_ip') . '</td>
                    </tr>

                    <tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_port') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_port') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_ppk_f_dir') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_ppk_f_dir') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_remote_dir') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_remote_dir') . '</td>
    
                    </tr>
                    
                    
                    <tr>
                        <td>' . $ifu->ObjetoHtmlLBL('empr_bpi_sftp_local_dir') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_bpi_sftp_local_dir') . '
                        <span class="btn btn-success btn-sm" title="test_conexion" value="TEST" onClick="javascript:prueba_conexion_sftp_bpi();">
                            Prueba de conexion 
                            <i class="glyphicon glyphicon-refresh"></i>
                        </span> 
                        <div id="TEST_CONNECTION">
                        </div></td>

                   
                        ';

        $sHtml .= '
                    <tr>
                        <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">OZMAP </td>
                    </tr>';

        $sHtml .= '<tr> 
    
                    <td>' . $ifu->ObjetoHtmlLBL('empr_ozmap_sn') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_ozmap_sn') . '</td>
                    
                    <td>' . $ifu->ObjetoHtmlLBL('empr_ozmap_url') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_ozmap_url') . '</td>

                    <td>' . $ifu->ObjetoHtmlLBL('empr_ozmap_user') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_ozmap_user') . '</td>
                </tr>
                
                <tr> 
    
                    <td>' . $ifu->ObjetoHtmlLBL('empr_ozmap_pass') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_ozmap_pass') . '</td>
                    
                    <td>' . $ifu->ObjetoHtmlLBL('empr_ozmap_api_token') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_ozmap_api_token') . '</td>
                </tr>';

        $sHtml .= '
                <tr>
                    <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">COMPARTIR REDES SOCIALES </td>
                </tr>';
        $sHtml .= '<tr> 

				    <td><label class="control-label" for="empr_rsoc_empr">Boton Compartir:</label></td>
				    <td><input type="checkbox"  id="empr_rsoc_empr" name="empr_rsoc_empr" value="S" /></td>
				    
				    <td><label class="control-label" for="empr_frso_empr">Formato:</label></td>
				    <td>                   
                        <select id="empr_frso_empr" name="empr_frso_empr" class="form-control select2" style="width:100%;">
                        <option value="">..Seleccione una Opcion..</option>
                            ' . $optionRide . '
                        </select>
                    </td>

                    <td></td>
				    <td></td>

					</tr>';

        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">WEB SERVICE GOOGLE MAPS </td>
				</tr>';
        $sHtml .= '<tr> 

                    <td>' . $ifu->ObjetoHtmlLBL('empr_key_maps') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_key_maps') . '</td>

                    <td>' . $ifu->ObjetoHtmlLBL('empr_gmaps_sn') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_gmaps_sn') . '</td>
                    <td>Al no seleccionar el check se habilitara el mapa gratuito del sistema</td>

					</tr>';
        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION DATAICO</td>
				</tr>';
        $sHtml .= '<tr> 

				    <td>' . $ifu->ObjetoHtmlLBL('empr_dataico_id') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_dataico_id') . '</td>
				    
				    <td>' . $ifu->ObjetoHtmlLBL('empr_dataico_token') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_dataico_token') . '</td>

					</tr>';

        // CAMPOS SIIGO

        $sHtml .= '
                <tr>
                    <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION SIIGO</td>
                </tr>';
        $sHtml .= '<tr> 

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_sn') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_sn') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_autoenvio') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_autoenvio') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_autoenvio_mail') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_autoenvio_mail') . '</td>
                        
                        
                    </tr>
                    <tr> 
                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_api_url') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_api_url') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_username') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_username') . '</td>
                    </tr>

                    <tr> 

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_access_token') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_access_token') . '</td>

                        <td>' . $ifu->ObjetoHtmlLBL('empr_siigo_partnerid') . '</td>
                        <td>' . $ifu->ObjetoHtml('empr_siigo_partnerid') . '</td>

                    </tr>
                    
                    ';

        // CAMPOS OPENPAY

        $sHtml .= '
                <tr>
                    <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION OPEN PAY</td>
                </tr>';
        $sHtml .= '<tr> 

                    <td>' . $ifu->ObjetoHtmlLBL('empr_openpay_sn') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_openpay_sn') . '</td>
                    
                    <td>' . $ifu->ObjetoHtmlLBL('empr_openpay_api_url') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_openpay_api_url') . '</td>
                </tr>
                <tr> 

                    <td>' . $ifu->ObjetoHtmlLBL('empr_openpay_idempresa') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_openpay_idempresa') . '</td>

                    <td>' . $ifu->ObjetoHtmlLBL('empr_openpay_publick') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_openpay_publick') . '</td>

                </tr>
                <tr> 

                    <td>' . $ifu->ObjetoHtmlLBL('empr_openpay_privatek') . '</td>
                    <td>' . $ifu->ObjetoHtml('empr_openpay_privatek') . '</td>

                </tr>
                ';


        $sHtml .= '
				<tr>
					<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">FORMATOS PERSONALIZADOS</td>
				</tr>';
        $sHtml .= '<tr>
				<td>' . $ifu->ObjetoHtmlLBL('empr_rol_sn') . '</td>
				<td>' . $ifu->ObjetoHtml('empr_rol_sn') . '</td>
				
				<td>' . $ifu->ObjetoHtmlLBL('empr_tip_impr') . '</td>
				<td>' . $ifu->ObjetoHtml('empr_tip_impr') . '</td>
				<td>' . $ifu->ObjetoHtmlLBL('empr_tip_agri') . $ifu->ObjetoHtml('empr_tip_agri') . '</td>
				<td>' . $ifu->ObjetoHtmlLBL('empr_tip_comp') . $ifu->ObjetoHtml('empr_tip_comp') . '</td>

					</tr>
                    <tr id="config_facturador_peru"></tr>
                    <tr id="config_facturador_peru_2"></tr>';
        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">LEYENDAS  PDF - XML DOCUMENTOS ELECTRONICOS</td>
				   </tr>';

        $sHtml .= '<tr> 

				    <td>' . $ifu->ObjetoHtmlLBL('empr_tit_xml') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_tit_xml') . '</td>
				    
				    <td>Detalle:</td>
				    <td><textarea name="empr_det_xml"  id="empr_det_xml" rows="5" cols="30"></textarea></td>
					<td>Mostrar en PDF</td>
                    <td><input type="checkbox" id="sn_pdf" name="sn_pdf" value="S"/></td>
                    
					</tr>

					<tr>
                    <td>Mostrar en XML</td>
                    <td><input type="checkbox" id="sn_xml" name="sn_xml" value="S"/></td>
					<td>Consultar:</td>
					<td><span class="btn btn-primary btn-sm" title="Consultar" value="Consultar" onClick="javascript:consulta_infoxml();">
					<i class="glyphicon glyphicon-search"></i>
					</span></td>
					<td>Ingresar:</td>
					<td><span class="btn btn-success btn-sm" title="Ingresar" value="Ingresar" onClick="javascript:ingresa_detxml();">
					<i class="glyphicon glyphicon-plus"></i>
					</span></td>
					</tr>';
        $sHtml .= $htmlpais;

        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION TALENTO HUMANO</td>
				   </tr>';

        $sHtml .= '<tr> 
				    <td>Dígitos celular empleado</td>
				    <td>' . $ifu->ObjetoHtml('empr_dig_celu') . '</td>
				    <td>Campos Formulario 107 S/N</td>
				    <td>' . $ifu->ObjetoHtml('empr_for_rdep') . ' </td>
					</tr>';

        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">CONFIGURACION BUSCADOR</td>
				   </tr>';

        $sHtml .= '<tr> 
                    <td>' . $ifu->ObjetoHtmlLBL('empr_bus_pers') . '</td>
				    <td>' . $ifu->ObjetoHtml('empr_bus_pers') . '</td>
					</tr>';

        //--------------------------------------------------------------------------------------------------------------
        //INICIO MOSTRAR EL CHECKBOX VISUAL PÁRA VALIDAR LOS DOPCUMENTOS UAFE
        //--------------------------------------------------------------------------------------------------------------

        $sHtml .= '<tr>
						<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">PARAMETROS COMPRAS PROVEEDORES</td>
				   </tr>';

        $sHtml .= '<tr> 
                    <td>' . $ifu->ObjetoHtmlLBL('emmpr_uafe_cprov') . '</td>
				    <td>' . $ifu->ObjetoHtml('emmpr_uafe_cprov') . '</td>
					</tr>';

        //--------------------------------------------------------------------------------------------------------------
        //FIN MOSTRAR EL CHECKBOX VISUAL PÁRA VALIDAR LOS DOPCUMENTOS UAFE
        //--------------------------------------------------------------------------------------------------------------

        if ($ctrL_clinico != 0) {
            $sHtml .= '<tr>
                <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">INFORMACION CLINICAS/HOSPITALES</td>
            </tr>';
            $sHtml .= '<tr> 
            <td>' . $ifu->ObjetoHtmlLBL('empr_cod_uni') . '</td>
            <td>' . $ifu->ObjetoHtml('empr_cod_uni') . '</td>
            </tr>';
        }

        //CONSULTA REGISTROS DE LA TABLA INFO XML


        $sHtml .= '</table>';

        $oReturn->assign("divFormularioCli", "innerHTML", $sHtml);
        $oReturn->script("sincronizar_base_script();");

        //$oReturn->assign("divReporteCli", "innerHTML", $table);

    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function seleccionarTran($aForm = '', $id = 0)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx2 = new Dbo;
    $oIfx2->DSN = $DSN_Ifx;
    $oIfx2->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $ifu = new Formulario;


    //variables de session

    try {

        //lectura sucia
        //////////////

        $sql = "select  *	from saeempr where				
					empr_cod_empr  = '$id'  ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $empr_cod_empr = $oIfx->f('empr_cod_empr');
                $empr_cod_aduna = $oIfx->f('empr_cod_aduna');
                $empr_cod_cesa = $oIfx->f('empr_cod_cesa');
                $empr_conta_sn = $oIfx->f('empr_conta_sn');
                $empr_nom_empr = $oIfx->f('empr_nom_empr');
                $empr_pais_ruc = $oIfx->f('empr_pais_ruc');
                $empr_con_pres = $oIfx->f('empr_con_pres');
                $empr_iva_empr = $oIfx->f('empr_iva_empr');
                $empr_cod_prov = $oIfx->f('empr_cod_prov');
                $empr_cod_ciud = $oIfx->f('empr_cod_ciud');
                $empr_dir_empr = $oIfx->f('empr_dir_empr');
                $empr_ruc_empr = $oIfx->f('empr_ruc_empr');
                $empr_cpo_empr = $oIfx->f('empr_cpo_empr');
                $empr_mai_empr = $oIfx->f('empr_mai_empr');
                $empr_tel_resp = $oIfx->f('empr_tel_resp');
                $empr_cta_extr = $oIfx->f('empr_cta_extr');

                $empr_fax_empr = $oIfx->f('empr_fax_empr');
                $empr_repres = $oIfx->f('empr_repres');
                $empr_tip_iden = $oIfx->f('empr_tip_iden');
                $empr_ced_repr = $oIfx->f('empr_ced_repr');
                $empr_num_dire = $oIfx->f('empr_num_dire');
                $empr_ema_repr = $oIfx->f('empr_ema_repr');
                $empr_ema_comp = $oIfx->f('empr_ema_comp');

                $empr_nom_cont = $oIfx->f('empr_nom_cont');
                $empr_ruc_cont = $oIfx->f('empr_ruc_cont');
                $empr_lic_cont = $oIfx->f('empr_lic_cont');
                $empr_tip_empr = $oIfx->f('empr_tip_empr');
                $empr_num_resu = $oIfx->f('empr_num_resu');
                $empr_ac1_empr = $oIfx->f('empr_ac1_empr');
                $empr_ac2_empr = $oIfx->f('empr_ac2_empr');
                $empr_ac3_empr = $oIfx->f('empr_ac3_empr');
                //jefe talento humano
                $empr_rrhh_nom = $oIfx->f('empr_rrhh_nom');
                $empr_nom_toke = $oIfx->f('empr_nom_toke');
                $empr_pass_toke = $oIfx->f('empr_pass_toke');
                $empr_path_logo = $oIfx->f('empr_path_logo');
                $empr_img_rep = $oIfx->f('empr_img_rep');
                $empr_web_color = $oIfx->f('empr_web_color');
                $empr_web_color2 = $oIfx->f('empr_web_color2');
                $empr_tie_espera = $oIfx->f('empr_tie_espera');;
                $empr_num_estab = $oIfx->f('empr_num_estab');
                $empr_fec_resu = ($oIfx->f('empr_fec_resu'));
                $empr_ema_sn = $oIfx->f('empr_ema_sn');
                $empr_cfe_contr = $oIfx->f('empr_cfe_contr');
                $empr_mone_fxfp = $oIfx->f('empr_mone_fxfp');
                $empr_asum_igtf = $oIfx->f('empr_asum_igtf');
                $empr_ema_test = $oIfx->f('empr_ema_test');
                $empr_rol_sn = $oIfx->f('empr_rol_sn');
                $empr_tip_comp = $oIfx->f('empr_tip_comp');
                $empr_tip_agri = $oIfx->f('empr_tip_agri');
                $empr_cod_parr = $oIfx->f('empr_cod_parr');
                $empr_cod_cant = $oIfx->f('empr_cod_cant');

                $empr_ws_sri_sn = $oIfx->f('empr_ws_sri_sn');
                $empr_ws_sri_url = $oIfx->f('empr_ws_sri_url');
                $empr_token_api = $oIfx->f('empr_token_api');


                $empr_ws_iden_sn = $oIfx->f('empr_ws_iden_sn');
                $empr_ws_iden_url = $oIfx->f('empr_ws_iden_url');
                $empr_ws_iden_token = $oIfx->f('empr_ws_iden_token');
                $empr_ws_iden_renueva = $oIfx->f('empr_ws_iden_renueva');




                $empr_key_maps = $oIfx->f('empr_key_maps');
                $empr_tip_impr = $oIfx->f('empr_tip_impr');
                $empr_dig_celu = $oIfx->f('empr_dig_celu');
                $empr_for_rdep = $oIfx->f('empr_for_rdep');
                $empr_cod_pais = $oIfx->f('empr_cod_pais');
                $empr_cod_ftdr = $oIfx->f('empr_cod_ftdr');
                $empr_nomcome_empr = $oIfx->f('empr_nomcome_empr');

                $empr_servi_sn = $oIfx->f('empr_servi_sn');
                $empr_servi_url = $oIfx->f('empr_servi_url');
                $empr_servi_user = $oIfx->f('empr_servi_user');
                $empr_servi_pass = $oIfx->f('empr_servi_pass');

                $empr_enti_code = $oIfx->f('empr_enti_code');

                // informacion de los pararemtros de integracion con el ws de laarcourier
                $empr_laar_sn = $oIfx->f('empr_laar_sn');
                $empr_gmaps_sn = $oIfx->f('empr_gmaps_sn');

                $empr_laar_url = $oIfx->f('empr_laar_url');
                $empr_laar_user = $oIfx->f('empr_laar_user');
                $empr_laar_pass = $oIfx->f('empr_laar_pass');
                $empr_laar_cod = $oIfx->f('empr_laar_cod');

                $empr_cod_uni = $oIfx->f('empr_cod_uni');
                //campo de campo parametros empresa
                $emmpr_uafe_cprov = $oIfx->f('emmpr_uafe_cprov');


                // CAPTURA DE VALORES SFTP BANCO PICHINCHA

                $empr_bpi_sftp_sn           = $oIfx->f('empr_bpi_sftp_sn');

                $empr_bpi_sftp_user         = $oIfx->f('empr_bpi_sftp_user');
                $empr_bpi_sftp_ip           = $oIfx->f('empr_bpi_sftp_ip');
                $empr_bpi_sftp_port         = $oIfx->f('empr_bpi_sftp_port');
                $empr_bpi_sftp_ppk_f_dir    = $oIfx->f('empr_bpi_sftp_ppk_f_dir');
                $empr_bpi_sftp_remote_dir   = $oIfx->f('empr_bpi_sftp_remote_dir');
                $empr_bpi_sftp_local_dir    = $oIfx->f('empr_bpi_sftp_local_dir');

                // OZMANP 
                $empr_ozmap_sn          = $oIfx->f('empr_ozmap_sn');

                $empr_ozmap_url         = $oIfx->f('empr_ozmap_url');
                $empr_ozmap_user        = $oIfx->f('empr_ozmap_user');
                $empr_ozmap_pass        = $oIfx->f('empr_ozmap_pass');
                $empr_ozmap_api_token   = $oIfx->f('empr_ozmap_api_token');

                $empr_sn_conta = $oIfx->f('empr_sn_conta');

                //BUSCADOR
                $empr_bus_pers = $oIfx->f('empr_bus_pers');
                //nuevo campo parametros compras proveedores
                $emmpr_uafe_cprov = $oIfx->f('emmpr_uafe_cprov');

                $empr_rsoc_empr = $oIfx->f('empr_rsoc_empr');
                if ($empr_rsoc_empr == 'S') {
                    $oReturn->assign('empr_rsoc_empr', 'checked', true);
                } else {
                    $oReturn->assign('empr_rsoc_empr', 'checked', false);
                }


                $empr_frso_empr = $oIfx->f('empr_frso_empr');
                $oReturn->assign('empr_frso_empr', 'value', $empr_frso_empr);


                $empr_cta_sn = $oIfx->f('empr_cta_sn');
                if ($empr_cta_sn == 'S') {
                    $oReturn->assign('empr_cta_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_cta_sn', 'checked', false);
                }

                $empr_det_cta = $oIfx->f('empr_det_cta');
                $empr_det_cta = str_replace('<br />', "\n", $empr_det_cta);
                $oReturn->assign('empr_det_cta', 'value', $empr_det_cta);

                $empr_rinf_sn = $oIfx->f('empr_rinf_sn');
                if ($empr_rinf_sn == 'S') {
                    $oReturn->assign('empr_rinf_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_rinf_sn', 'checked', false);
                }

                $empr_det_rinf = $oIfx->f('empr_det_rinf');
                $empr_det_rinf = str_replace('<br />', "\n", $empr_det_rinf);
                $oReturn->assign('empr_det_rinf', 'value', $empr_det_rinf);



                $sql = "SELECT pais_codigo_inter from comercial.pais_etiq_imp where pais_cod_pais = $empr_cod_pais LIMIT 1";
                $pais_codigo_inter = consulta_string($sql, 'pais_codigo_inter', $oIfx2, 0);

                //config_facturador_peru
                if ($pais_codigo_inter == '51') {
                    $sHtmlPeruFact_1 = '<td align="center" class="bg-primary" id="lgTitulo_frame" colspan="6">FACTURADOR PERÚ</td>';
                    $sHtmlPeruFact_2 = '<td colspan="3">Seleccione un facturador:</td>
                                        <td colspan="3">
                                            <select id="empr_cod_ftdr" name="empr_cod_ftdr" class="form-control input-sm">
                                                <option value="1">Jireh</option>
                                                <option value="2">Apufact</option>
                                                <option value="3">OSE</option>
                                            </select>
                                    </td>';

                    $oReturn->assign("config_facturador_peru", "innerHTML", $sHtmlPeruFact_1);
                    $oReturn->assign("config_facturador_peru_2", "innerHTML", $sHtmlPeruFact_2);

                    $oReturn->assign('empr_cod_ftdr', 'value', $empr_cod_ftdr);
                }

                if ($pais_codigo_inter != '58') {
                    $oReturn->script('$("#div_empr_asum_igtf").hide()');
                }


                $oReturn->assign('empr_nomcome_empr', 'value', $empr_nomcome_empr);
                $oReturn->assign('empr_tip_impr', 'value', $empr_tip_impr);

                $oReturn->assign('empr_cod_cant', 'value', $empr_cod_cant);

                //				$oReturn->alert($empr_tip_impr);


                if ($empr_for_rdep == 'S') {
                    $oReturn->assign('empr_for_rdep', 'checked', true);
                } else {
                    $oReturn->assign('empr_for_rdep', 'checked', false);
                }

                if ($empr_ws_sri_sn == 'S') {
                    $oReturn->assign('empr_ws_sri_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_ws_sri_sn', 'checked', false);
                }

                if ($empr_ws_iden_sn == 'S') {
                    $oReturn->assign('empr_ws_iden_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_ws_iden_sn', 'checked', false);
                }

                $oReturn->assign('empr_servi_url', 'value', $empr_servi_url);
                $oReturn->assign('empr_enti_code', 'value', $empr_enti_code);
                $oReturn->assign('empr_servi_user', 'value', $empr_servi_user);
                $oReturn->assign('empr_servi_pass', 'value', $empr_servi_pass);


                if ($empr_servi_sn == 1) {
                    $oReturn->assign('empr_servi_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_servi_sn', 'checked', false);
                }

                // asignacion de valores a la vista laarcourier
                $oReturn->assign('empr_laar_url', 'value', $empr_laar_url);
                $oReturn->assign('empr_laar_user', 'value', $empr_laar_user);
                $oReturn->assign('empr_laar_pass', 'value', $empr_laar_pass);
                $oReturn->assign('empr_laar_cod', 'value', $empr_laar_cod);


                if ($empr_sn_conta == 'S') {
                    $oReturn->script('document.form1.obliconta_sn[0].checked = true');
                } else {
                    $oReturn->script('document.form1.obliconta_sn[1].checked = true');
                }




                if ($empr_laar_sn == "S") {
                    $oReturn->assign('empr_laar_sn', 'checked', true);
                }
                if ($empr_laar_sn == "N") {
                    $oReturn->assign('empr_laar_sn', 'checked', false);
                }

                // asignacion de valores a la vista SFTP BANCO DEL PICHINCHA
                $oReturn->assign('empr_bpi_sftp_user', 'value', $empr_bpi_sftp_user);
                $oReturn->assign('empr_bpi_sftp_ip', 'value', $empr_bpi_sftp_ip);
                $oReturn->assign('empr_bpi_sftp_port', 'value', $empr_bpi_sftp_port);
                $oReturn->assign('empr_bpi_sftp_ppk_f_dir', 'value', $empr_bpi_sftp_ppk_f_dir);
                $oReturn->assign('empr_bpi_sftp_remote_dir', 'value', $empr_bpi_sftp_remote_dir);
                $oReturn->assign('empr_bpi_sftp_local_dir', 'value', $empr_bpi_sftp_local_dir);

                if ($empr_bpi_sftp_sn == 'S') {
                    $oReturn->assign('empr_bpi_sftp_sn', 'checked', true);
                }
                if ($empr_bpi_sftp_sn == "N") {
                    $oReturn->assign('empr_bpi_sftp_sn', 'checked', false);
                }

                // asignacion de valores a la vista ozmap
                $oReturn->assign('empr_ozmap_url', 'value', $empr_ozmap_url);
                $oReturn->assign('empr_ozmap_user', 'value', $empr_ozmap_user);
                $oReturn->assign('empr_ozmap_pass', 'value', $empr_ozmap_pass);
                $oReturn->assign('empr_ozmap_api_token', 'value', $empr_ozmap_api_token);

                if (trim($empr_ozmap_sn) == 'S') {
                    $oReturn->assign('empr_ozmap_sn', 'checked', true);
                }

                if (trim($empr_ozmap_sn) == "N") {
                    $oReturn->assign('empr_ozmap_sn', 'checked', false);
                }


                if ($empr_gmaps_sn == "S") {
                    $oReturn->assign('empr_gmaps_sn', 'checked', true);
                }
                if ($empr_gmaps_sn == "N") {
                    $oReturn->assign('empr_gmaps_sn', 'checked', false);
                }

                $oReturn->assign('empr_ws_sri_url', 'value', $empr_ws_sri_url);


                $oReturn->assign('empr_ws_iden_url', 'value', $empr_ws_iden_url);
                $oReturn->assign('empr_ws_iden_token', 'value', $empr_ws_iden_token);
                $oReturn->assign('empr_ws_iden_renueva', 'value', $empr_ws_iden_renueva);


                $oReturn->assign('empr_token_api', 'value', $empr_token_api);
                $oReturn->assign('empr_key_maps', 'value', $empr_key_maps);


                $empr_dataico_id = $oIfx->f('empr_dataico_id');
                $empr_dataico_token = $oIfx->f('empr_dataico_token');
                $oReturn->assign('empr_dataico_id', 'value', $empr_dataico_id);
                $oReturn->assign('empr_dataico_token', 'value', $empr_dataico_token);

                // SIIGO ASIGNACION DE VALORES
                $empr_siigo_sn              = $oIfx->f('empr_siigo_sn');
                $empr_siigo_autoenvio       = $oIfx->f('empr_siigo_autoenvio');
                $empr_siigo_autoenvio_mail  = $oIfx->f('empr_siigo_autoenvio_mail');
                $empr_siigo_api_url         = $oIfx->f('empr_siigo_api_url');
                $empr_siigo_username        = $oIfx->f('empr_siigo_username');
                $empr_siigo_access_token    = $oIfx->f('empr_siigo_access_token');
                $empr_siigo_partnerid       = $oIfx->f('empr_siigo_partnerid');

                $oReturn->assign('empr_siigo_sn', 'checked', ($empr_siigo_sn == 'S' ? true : false));
                $oReturn->assign('empr_siigo_autoenvio', 'checked', ($empr_siigo_autoenvio == 'S' ? true : false));
                $oReturn->assign('empr_siigo_autoenvio_mail', 'checked', ($empr_siigo_autoenvio_mail == 'S' ? true : false));
                $oReturn->assign('empr_siigo_api_url', 'value', $empr_siigo_api_url);
                $oReturn->assign('empr_siigo_username', 'value', $empr_siigo_username);
                $oReturn->assign('empr_siigo_access_token', 'value', $empr_siigo_access_token);
                $oReturn->assign('empr_siigo_partnerid', 'value', $empr_siigo_partnerid);

                $empr_rimp_sn = $oIfx->f('empr_rimp_sn');
                $empr_rete_sn = $oIfx->f('empr_rete_sn');

                //OPENPAY ASIGNACION DE VALORES
                $empr_openpay_sn = $oIfx->f('empr_openpay_sn');
                $empr_openpay_api_url = $oIfx->f('empr_openpay_api_url');
                $empr_openpay_idempresa = $oIfx->f('empr_openpay_idempresa');
                $empr_openpay_publick = $oIfx->f('empr_openpay_publick');
                $empr_openpay_privatek = $oIfx->f('empr_openpay_privatek');
                $oReturn->assign('empr_openpay_sn', 'value', $empr_openpay_sn);
                $oReturn->assign('empr_openpay_api_url', 'value', $empr_openpay_api_url);
                $oReturn->assign('empr_openpay_idempresa', 'value', $empr_openpay_idempresa);
                $oReturn->assign('empr_openpay_publick', 'value', $empr_openpay_publick);
                $oReturn->assign('empr_openpay_privatek', 'value', $empr_openpay_privatek);


                //$oReturn->alert($empr_fec_resu);
                $oReturn->assign('empr_cod_uni', 'value', $empr_cod_uni);

                $oReturn->assign('empr_dig_celu', 'value', $empr_dig_celu);
                $oReturn->assign('empr_cod_empr', 'value', $empr_cod_empr);
                $oReturn->assign('empr_cod_aduna', 'value', $empr_cod_aduna);
                $oReturn->assign('empr_cod_cesa', 'value', $empr_cod_cesa);
                $oReturn->assign('empr_nom_empr', 'value', $empr_nom_empr);
                $oReturn->assign('empr_pais_ruc', 'value', $empr_pais_ruc);
                $oReturn->assign('empr_iva_empr', 'value', $empr_iva_empr);
                $oReturn->assign('empr_cod_prov', 'value', $empr_cod_prov);

                $oReturn->assign('empr_cod_ciud', 'value', $empr_cod_ciud);

                $oReturn->assign('empr_dir_empr', 'value', $empr_dir_empr);
                $oReturn->assign('empr_ruc_empr', 'value', $empr_ruc_empr);
                $oReturn->assign('empr_cpo_empr', 'value', $empr_cpo_empr);
                $oReturn->assign('empr_mai_empr', 'value', $empr_mai_empr);

                $oReturn->assign('empr_tel_resp', 'value', $empr_tel_resp);
                $oReturn->assign('empr_fax_empr', 'value', $empr_fax_empr);
                $oReturn->assign('empr_repres', 'value', $empr_repres);

                $oReturn->assign('empr_tip_iden', 'value', $empr_tip_iden);
                $oReturn->assign('empr_ced_repr', 'value', $empr_ced_repr);
                $oReturn->assign('empr_num_dire', 'value', $empr_num_dire);

                $oReturn->assign('empr_ema_repr', 'value', $empr_ema_repr);
                $oReturn->assign('empr_ema_comp', 'value', $empr_ema_comp);
                $oReturn->assign('empr_nom_cont', 'value', $empr_nom_cont);
                $oReturn->assign('empr_ruc_cont', 'value', $empr_ruc_cont);
                $oReturn->assign('empr_lic_cont', 'value', $empr_lic_cont);
                $oReturn->assign('empr_num_resu', 'value', $empr_num_resu);

                $oReturn->assign('empr_ac1_empr', 'value', $empr_ac1_empr);
                $oReturn->assign('empr_ac2_empr', 'value', $empr_ac2_empr);
                $oReturn->assign('empr_ac3_empr', 'value', $empr_ac3_empr);

                $oReturn->assign('empr_rrhh_nom', 'value', $empr_rrhh_nom);

                $oReturn->assign('empr_nom_toke', 'value', $empr_nom_toke);
                $oReturn->assign('empr_pass_toke', 'value', $empr_pass_toke);


                $oReturn->assign('empr_path_logo', 'value', $empr_path_logo);
                $oReturn->assign('empr_cuad_logo', 'value', $empr_img_rep);
                $oReturn->assign('empr_web_color', 'value', $empr_web_color);
                $oReturn->assign('empr_web_color2', 'value', $empr_web_color2);
                $oReturn->assign('empr_tie_espera', 'value', $empr_tie_espera);
                $oReturn->assign('empr_num_estab', 'value', $empr_num_estab);
                $oReturn->assign('empr_fec_resu', 'value', $empr_fec_resu);

                if ($empr_rimp_sn == 'S') {
                    $oReturn->assign('empr_rimp_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_rimp_sn', 'checked', false);
                }

                if ($empr_rete_sn == 'S') {
                    $oReturn->assign('empr_rete_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_rete_sn', 'checked', false);
                }

                if ($empr_conta_sn == 'S') {
                    $oReturn->assign('empr_conta_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_conta_sn', 'checked', false);
                }

                if ($empr_con_pres == 'S') {
                    $oReturn->assign('empr_con_pres', 'checked', true);
                } else {
                    $oReturn->assign('empr_con_pres', 'checked', false);
                }

                if ($empr_cta_extr == 'S') {
                    $oReturn->assign('empr_cta_extr', 'checked', true);
                } else {
                    $oReturn->assign('empr_cta_extr', 'checked', false);
                }

                if ($empr_tip_empr == 'S') {
                    $oReturn->assign('empr_tip_empr', 'checked', true);
                } else {
                    $oReturn->assign('empr_tip_empr', 'checked', false);
                }

                if ($empr_ema_sn == 'S') {
                    $oReturn->assign('empr_ema_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_ema_sn', 'checked', false);
                    $oReturn->script('carga_correo();');
                }
                $oReturn->assign('empr_ema_test', 'value', $empr_ema_test);

                if ($empr_cfe_contr == 'S') {
                    $oReturn->assign('empr_cfe_contr', 'checked', true);
                } else {
                    $oReturn->assign('empr_cfe_contr', 'checked', false);
                }

                if ($empr_mone_fxfp == 'S') {
                    $oReturn->assign('empr_mone_fxfp', 'checked', true);
                } else {
                    $oReturn->assign('empr_mone_fxfp', 'checked', false);
                }
                if ($empr_asum_igtf == 'S') {
                    $oReturn->assign('empr_asum_igtf', 'checked', true);
                } else {
                    $oReturn->assign('empr_asum_igtf', 'checked', false);
                }

                if ($empr_rol_sn == 'S') {
                    $oReturn->assign('empr_rol_sn', 'checked', true);
                } else {
                    $oReturn->assign('empr_rol_sn', 'checked', false);
                }
                if ($empr_tip_comp == 1) {
                    $oReturn->assign('empr_tip_comp', 'checked', true);
                } else {
                    $oReturn->assign('empr_tip_comp', 'checked', false);
                }
                if ($empr_tip_agri == 1) {
                    $oReturn->assign('empr_tip_agri', 'checked', true);
                } else {
                    $oReturn->assign('empr_tip_agri', 'checked', false);
                }

                if ($empr_bus_pers == 'S') {
                    $oReturn->assign('empr_bus_pers', 'checked', true);
                } else {
                    $oReturn->assign('empr_bus_pers', 'checked', false);
                }

                //$emmpr_uafe_cprov = $oIfx->f('emmpr_uafe_cprov');

                    
                if ($emmpr_uafe_cprov == 't' || $emmpr_uafe_cprov == '1') {  
                    $oReturn->assign('emmpr_uafe_cprov', 'checked', true);
                } else {
                    $oReturn->assign('emmpr_uafe_cprov', 'checked', false);
                }

            }
        }
        $oIfx->Free();
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("consulta_infoxml();");
    $oReturn->script("consulta_infopdf();");
    $oReturn->script("habilita_cta_edit('$empr_cta_sn');");
    $oReturn->script("habilita_rinf_edit('$empr_rinf_sn');");


    return $oReturn;
}

function guardar_tran($aForm = '')
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

    $oReturn = new xajaxResponse();

    $user_ifx = $_SESSION['U_USER_INFORMIX'];

    //LECTURA SUCIA
    //////////////

    $empr_cod_empr = $aForm['empr_cod_empr'];
    $empr_cod_aduna = $aForm['empr_cod_aduna'];
    $empr_cod_cesa = $aForm['empr_cod_cesa'];
    $empr_conta_sn = val_check_inv($aForm['empr_conta_sn'], 'S', 'N');
    $empr_nom_empr = $aForm['empr_nom_empr'];
    $empr_pais_ruc = $aForm['empr_pais_ruc'];
    $empr_con_pres = val_check_inv($aForm['empr_con_pres'], 'S', 'N');
    $empr_iva_empr = $aForm['empr_iva_empr'];
    $empr_cod_prov = $aForm['empr_cod_prov'];
    $empr_cod_ciud = $aForm['empr_cod_ciud'];
    $empr_cod_parr = $aForm['empr_cod_parr'];
    $empr_dir_empr = $aForm['empr_dir_empr'];
    $empr_ruc_empr = $aForm['empr_ruc_empr'];
    $empr_cpo_empr = $aForm['empr_cpo_empr'];
    $empr_mai_empr = $aForm['empr_mai_empr'];
    $empr_tel_resp = $aForm['empr_tel_resp'];
    $empr_cta_extr = val_check_inv($aForm['empr_cta_extr'], 'S', 'N');

    $empr_fax_empr = $aForm['empr_fax_empr'];
    $empr_repres = $aForm['empr_repres'];
    $empr_tip_iden = $aForm['empr_tip_iden'];
    $empr_ced_repr = $aForm['empr_ced_repr'];
    $empr_num_dire = $aForm['empr_num_dire'];
    $empr_ema_repr = $aForm['empr_ema_repr'];
    $empr_ema_comp = $aForm['empr_ema_comp'];

    $empr_nom_cont = $aForm['empr_nom_cont'];
    $empr_ruc_cont = $aForm['empr_ruc_cont'];
    $empr_lic_cont = $aForm['empr_lic_cont'];
    $empr_tip_empr = val_check_inv($aForm['empr_tip_empr'], 'S', 'N');
    $empr_num_resu = $aForm['empr_num_resu'];
    $empr_lic_cont = $aForm['empr_lic_cont'];
    $empr_ac1_empr = $aForm['empr_ac1_empr'];
    $empr_ac2_empr = $aForm['empr_ac2_empr'];
    $empr_ac3_empr = $aForm['empr_ac3_empr'];
    $empr_cod_cant = $aForm['empr_cod_cant'] ? $aForm['empr_cod_cant'] : 0;

    //Varibale del formulario jefe de talento humano
    $empr_rrhh_nom = $aForm['empr_rrhh_nom'];
    ////////////////////////////////////////7

    $empr_path_logo = $aForm['empr_path_logo'];
    $empr_cuad_logo = $aForm['empr_cuad_logo'];

    $empr_web_color = $aForm['empr_web_color'];
    $empr_web_color2 = $aForm['empr_web_color2'];


    $empr_tie_espera = $aForm['empr_tie_espera'];
    $empr_num_estab = $aForm['empr_num_estab'];
    $empr_fec_resu = $aForm['empr_fec_resu'];

    $empr_rimp_sn = val_check_inv($aForm['empr_rimp_sn'], 'S', 'N');
    $empr_rete_sn = val_check_inv($aForm['empr_rete_sn'], 'S', 'N');

    $empr_ema_sn = $aForm['empr_ema_sn'];
    $empr_cfe_contr = $aForm['empr_cfe_contr'];
    $empr_mone_fxfp = $aForm['empr_mone_fxfp'];
    $empr_asum_igtf = $aForm['empr_asum_igtf'];

    $empr_ema_test = $aForm['empr_ema_test'];

    if (!$empr_opc_empr) {
        $empr_opc_empr = 0;
    }

    if ($empr_fec_resu == '//') {
        $empr_fec_resu = date('Y-m-d');
    }
    if (!$empr_fec_resu) {
        $empr_fec_resu = date('Y-m-d');
    }
    if (!$empr_iva_empr) {
        $empr_iva_empr = 0;
    }
    if (!$empr_tie_espera) {
        $empr_tie_espera = 0;
    }
    if (!$empr_cod_codigo) {
        $empr_cod_codigo = 0;
    }

    $empr_rol_sn = $aForm['empr_rol_sn'];

    if (empty($empr_rol_sn)) {
        $empr_rol_sn = 'N';
    } else {
        $empr_rol_sn = 'S';
    }


    $empr_bus_pers = $aForm['empr_bus_pers'];

    if (empty($empr_bus_pers)) {
        $empr_bus_pers = 'N';
    } else {
        $empr_bus_pers = 'S';
    }

    //Parametro compras proveedores boleano
    $emmpr_uafe_cprov = !empty($aForm['emmpr_uafe_cprov']) ? 'true' : 'false';


    $empr_for_rdep = $aForm['empr_for_rdep'];

    if (empty($empr_for_rdep)) {
        $empr_for_rdep = 'N';
    } else {
        $empr_for_rdep = 'S';
    }

    $empr_tip_comp = $aForm['empr_tip_comp'];
    $empr_tip_agri = $aForm['empr_tip_agri'];


    if (empty($empr_tip_comp)) {
        $empr_tip_comp = 0;
    } else {
        $empr_tip_comp = 1;
    }


    if (empty($empr_tip_agri)) {
        $empr_tip_agri = 0;
    } else {
        $empr_tip_agri = 1;
    }

    $empr_ws_sri_url = $aForm['empr_ws_sri_url'];
    $empr_token_api = $aForm['empr_token_api'];
    $empr_key_maps = $aForm['empr_key_maps'];
    $empr_ws_sri_sn = $aForm['empr_ws_sri_sn'];

    if (empty($empr_ws_sri_sn)) {
        $empr_ws_sri_sn = 'N';
    } else {
        $empr_ws_sri_sn = 'S';
    }

    $empr_ws_iden_sn = $aForm['empr_ws_iden_sn'];
    if (empty($empr_ws_iden_sn)) {
        $empr_ws_iden_sn = 'N';
    } else {
        $empr_ws_iden_sn = 'S';
    }

    $empr_ws_iden_url = $aForm['empr_ws_iden_url'];
    $empr_ws_iden_token = $aForm['empr_ws_iden_token'];
    $empr_ws_iden_renueva = $aForm['empr_ws_iden_renueva'] ? $aForm['empr_ws_iden_renueva'] : 30;


    $empr_servi_url = $aForm['empr_servi_url'];
    $empr_servi_user = $aForm['empr_servi_user'];
    $empr_servi_pass = $aForm['empr_servi_pass'];
    $empr_servi_sn = $aForm['empr_servi_sn'];
    $empr_enti_code = $aForm['empr_enti_code'];

    if (empty($empr_servi_sn)) {
        $empr_servi_sn = 0;
    } else {
        $empr_servi_sn = 1;
    }

    // Valores de laarcourier
    $empr_laar_url = $aForm['empr_laar_url'];
    $empr_laar_user = $aForm['empr_laar_user'];
    $empr_laar_pass = $aForm['empr_laar_pass'];
    $empr_laar_cod = $aForm['empr_laar_cod'];

    $empr_laar_sn = val_check_inv($aForm['empr_laar_sn'], 'S', 'N');

    // Valores de SFTP BANCO PICHINCHA
    $empr_bpi_sftp_user         = $aForm['empr_bpi_sftp_user'];
    $empr_bpi_sftp_ip           = $aForm['empr_bpi_sftp_ip'];
    $empr_bpi_sftp_port         = $aForm['empr_bpi_sftp_port'];
    $empr_bpi_sftp_ppk_f_dir    = $aForm['empr_bpi_sftp_ppk_f_dir'];
    $empr_bpi_sftp_remote_dir    = $aForm['empr_bpi_sftp_remote_dir'];
    $empr_bpi_sftp_local_dir    = $aForm['empr_bpi_sftp_local_dir'];

    $empr_bpi_sftp_sn           = val_check_inv($aForm['empr_bpi_sftp_sn'], 'S', 'N');

    // OZMAP
    $empr_ozmap_url         = $aForm['empr_ozmap_url'];
    $empr_bpi_sftp_port     = $aForm['empr_ozmap_user'];
    $empr_ozmap_pass        = $aForm['empr_ozmap_pass'];
    $empr_ozmap_api_token   = $aForm['empr_ozmap_api_token'];
    $empr_ozmap_sn          = val_check_inv($aForm['empr_ozmap_sn'], 'S', 'N');



    $empr_gmaps_sn = val_check_inv($aForm['empr_gmaps_sn'], 'S', 'N');


    $empr_dig_celu = $aForm['empr_dig_celu'] ? $aForm['empr_dig_celu'] : 0;
    $empr_for_rdep = $aForm['empr_for_rdep'];

    if (empty($empr_for_rdep)) {
        $empr_for_rdep = 'N';
    } else {
        $empr_for_rdep = 'S';
    }

    if (empty($empr_cod_parr)) {
        $empr_cod_parr = 'NULL';
    }

    $empr_dataico_id = $aForm['empr_dataico_id'];
    $empr_dataico_token = $aForm['empr_dataico_token'];

    // SIIGO VALORES A GUARDAR
    $empr_siigo_sn              = val_check_inv($aForm['empr_siigo_sn'], 'S', 'N');
    $empr_siigo_autoenvio       = val_check_inv($aForm['empr_siigo_autoenvio'], 'S', 'N');
    $empr_siigo_autoenvio_mail  = val_check_inv($aForm['empr_siigo_autoenvio_mail'], 'S', 'N');
    $empr_siigo_api_url         = $aForm['empr_siigo_api_url'];
    $empr_siigo_username        = $aForm['empr_siigo_username'];
    $empr_siigo_access_token    = $aForm['empr_siigo_access_token'];
    $empr_siigo_partnerid       = $aForm['empr_siigo_partnerid'];

    //OPENPAY VALORES A GUARDAR
    $empr_openpay_sn = $aForm['empr_openpay_sn'];
    $empr_openpay_api_url = $aForm['empr_openpay_api_url'];
    $empr_openpay_idempresa = $aForm['empr_openpay_idempresa'];
    $empr_openpay_publick = $aForm['empr_openpay_publick'];
    $empr_openpay_privatek = $aForm['empr_openpay_privatek'];


    $empr_nomcome_empr = $aForm['empr_nomcome_empr'];

    //CAMPOS COMPARTIR REDES SOCIALES

    $empr_frso_empr = $aForm['empr_frso_empr'];
    if (empty($empr_frso_empr)) {
        $empr_frso_empr = 'NULL';
    }
    $empr_rsoc_empr = $aForm['empr_rsoc_empr'];
    if (empty($empr_rsoc_empr)) {
        $empr_rsoc_empr = 'N';
        $empr_frso_empr = 'NULL';
    }

    //CAMPOS INFORMACION BANCARIA
    $empr_cta_sn = $aForm['empr_cta_sn'];
    if (empty($empr_cta_sn)) {
        $empr_cta_sn = 'N';
    }
    $empr_det_cta = trim(nl2br($aForm['empr_det_cta']));
    $empr_det_cta = $empr_det_cta != '' ? "'" . $empr_det_cta . "'" : 'NULL';

    //CAMPO ADICIONAL RIDE

    $empr_rinf_sn = $aForm['empr_rinf_sn'];
    if (empty($empr_rinf_sn)) {
        $empr_rinf_sn = 'N';
    }
    $empr_det_rinf = trim(nl2br($aForm['empr_det_rinf']));
    $empr_det_rinf = $empr_det_rinf != '' ? "'" . $empr_det_rinf . "'" : 'NULL';

    $empr_sn_conta = $aForm['obliconta_sn'];

    $empr_cod_uni = $aForm['empr_cod_uni'];


    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');

        // SAESUCU
        $sql = "insert into saeempr ( empr_cod_ciud,		empr_nom_empr,		empr_dir_empr,		empr_ruc_empr,
									  empr_cpo_empr,		empr_mai_empr,		empr_repres,		empr_opc_empr,
									  empr_tel_resp,		empr_fax_empr,		empr_ced_repr,		empr_num_dire,
									  empr_nom_cont,		empr_ruc_cont,		empr_lic_cont,		empr_tip_empr,
									  empr_num_resu,		empr_fec_resu,		empr_tip_iden,		empr_cod_prov,
									  empr_cod_cant,		empr_ema_repr,		empr_ac1_empr,		empr_ac2_empr,
									  empr_nom_mzon,		empr_dir_mzon,		empr_pais_ruc,		empr_iva_empr,
									  empr_con_pres,		empr_cta_extr,		empr_tipo_empr,		empr_cod_codigo,
									  empr_num_estab,		empr_conta_sn,		empr_path_logo,		empr_tie_espera,
									  empr_ema_comp,		empr_cod_cesa,		empr_cod_aduna,		empr_cm1_empr,
									  empr_cm2_empr,		empr_sitio_web,		empr_menu_sucu,		empr_mdse_pcon 	,
									  empr_cod_pais,        empr_img_rep,       empr_web_color,     empr_web_color2,
									  empr_ema_sn,          empr_ema_test,      empr_ac3_empr,      empr_rrhh_nom,
                                      empr_rol_sn,          empr_rimp_sn,       empr_rete_sn,       empr_ws_sri_sn, 

                                      empr_ws_iden_sn,      empr_ws_iden_url,   empr_ws_iden_token, empr_ws_iden_renueva,

                                      empr_ws_sri_url,      empr_tip_comp,      empr_tip_agri,      empr_dig_celu,
                                      empr_laar_sn,         empr_laar_url,      empr_laar_user,     empr_laar_pass, 
                                      empr_laar_cod,
                                      empr_bpi_sftp_sn,     empr_bpi_sftp_user, empr_bpi_sftp_ip,   empr_bpi_sftp_port, 
                                      empr_bpi_sftp_ppk_f_dir,
                                      empr_bpi_sftp_remote_dir,
                                      empr_bpi_sftp_local_dir,  
                                      empr_ozmap_sn,        empr_ozmap_url,     empr_ozmap_user,    empr_ozmap_pass,
                                      empr_ozmap_api_token,
                                      empr_for_rdep,        empr_dataico_id,        empr_dataico_token, 

                                      empr_siigo_sn,        empr_siigo_autoenvio,       empr_siigo_autoenvio_mail, empr_siigo_api_url,   
                                      empr_siigo_username,  empr_siigo_access_token,    empr_siigo_partnerid,

                                      empr_openpay_sn, empr_openpay_api_url, empr_openpay_idempresa, empr_openpay_publick, empr_openpay_privatek,
                                      empr_nomcome_empr, 
                                      empr_cod_parr,        empr_token_api,     empr_key_maps,      empr_rsoc_empr,
                                      empr_frso_empr,       empr_cta_sn,        empr_det_cta,       empr_gmaps_sn, empr_bus_pers, emmpr_uafe_cprov,
                                      empr_sn_conta, empr_cfe_contr,  empr_rinf_sn, empr_det_rinf, empr_cod_uni, empr_mone_fxfp, empr_asum_igtf )
							  values( '$empr_cod_ciud',		'$empr_nom_empr',	'$empr_dir_empr',	  '$empr_ruc_empr',
									  '$empr_cpo_empr',		'$empr_mai_empr',	'$empr_repres'  ,	  $empr_opc_empr,
									  '$empr_tel_resp',	    '$empr_fax_empr',	'$empr_ced_repr',	  '$empr_num_dire',
									  '$empr_nom_cont',		'$empr_ruc_cont',   '$empr_lic_cont',     '$empr_tip_empr',
									  '$empr_num_resu',		'$empr_fec_resu',   '$empr_tip_iden',     '$empr_cod_prov',
									  '$empr_cod_cant',		'$empr_ema_repr',   '$empr_ac1_empr',     '$empr_ac2_empr',
									  '$empr_nom_mzon',		'$empr_dir_mzon',   '$empr_pais_ruc',     $empr_iva_empr,
									  '$empr_con_pres',		'$empr_cta_extr',   '$empr_tipo_empr',    $empr_cod_codigo,
									  '$empr_num_estab',	'$empr_conta_sn',	'$empr_path_logo',	  $empr_tie_espera,
									  '$empr_ema_comp',		'$empr_cod_cesa',	'$empr_cod_aduna',	  '$empr_cm1_empr',
									  '$empr_cm2_empr',		'$empr_sitio_web',	'$empr_menu_sucu',	  'S',
									  $empr_pais_ruc,       '$empr_cuad_log',   '$empr_web_color',    '$empr_web_color2',
									  '$empr_ema_sn',       '$empr_ema_test',   '$empr_ac3_empr',     '$empr_rrhh_nom',
                                      '$empr_rol_sn',       '$empr_rimp_sn',    '$empr_rete_sn',      '$empr_ws_sri_sn',

                                      '$empr_ws_iden_sn',   '$empr_ws_iden_url',    '$empr_ws_iden_token',  '$empr_ws_iden_renueva',


                                      '$empr_ws_sri_url',    $empr_tip_comp,     $empr_tip_agri,       $empr_dig_celu,
                                      '$empr_laar_sn',      '$empr_laar_url',   '$empr_laar_user',    '$empr_laar_pass',
                                      '$empr_laar_cod',  
                                      '$empr_bpi_sftp_sn',  '$empr_bpi_sftp_user','$empr_bpi_sftp_ip','$empr_bpi_sftp_port',
                                      '$empr_bpi_sftp_ppk_f_dir',  
                                      '$empr_bpi_sftp_remote_dir',  
                                      '$empr_bpi_sftp_local_dir',  
                                      '$empr_ozmap_sn',     '$empr_ozmap_url',  '$empr_ozmap_user',     '$empr_ozmap_pass',
                                      '$empr_ozmap_api_token',                                    
                                      '$empr_for_rdep',     '$empr_dataico_id', '$empr_dataico_token',

                                      '$empr_siigo_sn',         '$empr_siigo_autoenvio',    '$empr_siigo_autoenvio_mail','$empr_siigo_api_url',
                                      '$empr_siigo_username',   '$empr_siigo_access_token', '$empr_siigo_partnerid',

                                      '$empr_openpay_sn', '$empr_openpay_api_url', '$empr_openpay_idempresa', '$empr_openpay_publick', '$empr_openpay_privatek',
                                      '$empr_nomcome_empr', 
                                      $empr_cod_parr,       '$empr_token_api',  '$empr_key_maps',      '$empr_rsoc_empr',
                                      $empr_frso_empr,      '$empr_cta_sn',     $empr_det_cta,     '$empr_gmaps_sn', '$empr_bus_pers', $emmpr_uafe_cprov,
                                      '$empr_sn_conta', '$empr_cfe_contr', '$empr_rinf_sn', $empr_det_rinf, '$empr_cod_uni', '$empr_mone_fxfp', '$empr_asum_igtf')";
        
        // print_r($sql);exit;

        $oIfx->QueryT($sql);

        // SERIAL SAEEMPR
        $sql = "select empr_cod_empr from saeempr where 
						empr_cod_ciud = '$empr_cod_ciud' and
						empr_nom_empr = '$empr_nom_empr' and
						empr_ruc_empr = '$empr_ruc_empr' and
						empr_ruc_cont = '$empr_ruc_cont' ";
        $empr_cod_empr = consulta_string_func($sql, 'empr_cod_empr', $oIfx, '0');

        $oReturn->assign('empr_cod_empr', 'value', $empr_cod_empr);

        $oIfx->QueryT('COMMIT WORK;');
        $oReturn->alert('Ingresado Correctamente...');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function update_tran_frame($aForm = '')
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

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $user_ifx = $_SESSION['U_USER_INFORMIX'];

    //LECTURA SUCIA
    //////////////

    $empr_cod_empr = $aForm['empr_cod_empr'];
    $empr_cod_aduna = $aForm['empr_cod_aduna'];
    $empr_cod_cesa = $aForm['empr_cod_cesa'];
    $empr_conta_sn = val_check_inv($aForm['empr_conta_sn'], 'S', 'N');
    $empr_nom_empr = $aForm['empr_nom_empr'];
    $empr_pais_ruc = $aForm['empr_pais_ruc'];
    $empr_con_pres = val_check_inv($aForm['empr_con_pres'], 'S', 'N');
    $empr_iva_empr = $aForm['empr_iva_empr'];
    $empr_cod_prov = $aForm['empr_cod_prov'];
    $empr_cod_ciud = $aForm['empr_cod_ciud'];
    $empr_cod_parr = $aForm['empr_cod_parr'];
    $empr_dir_empr = $aForm['empr_dir_empr'];
    $empr_ruc_empr = $aForm['empr_ruc_empr'];
    $empr_cpo_empr = $aForm['empr_cpo_empr'];
    $empr_mai_empr = $aForm['empr_mai_empr'];
    $empr_tel_resp = $aForm['empr_tel_resp'];
    $empr_cta_extr = val_check_inv($aForm['empr_cta_extr'], 'S', 'N');

    $empr_fax_empr = $aForm['empr_fax_empr'];
    $empr_repres = $aForm['empr_repres'];
    $empr_tip_iden = $aForm['empr_tip_iden'];
    $empr_ced_repr = $aForm['empr_ced_repr'];
    $empr_num_dire = $aForm['empr_num_dire'];
    $empr_ema_repr = $aForm['empr_ema_repr'];
    $empr_ema_comp = $aForm['empr_ema_comp'];
    $empr_cod_cant = $aForm['empr_cod_cant'] ? $aForm['empr_cod_cant'] : 0;

    $empr_nom_cont = $aForm['empr_nom_cont'];
    $empr_ruc_cont = $aForm['empr_ruc_cont'];
    $empr_lic_cont = $aForm['empr_lic_cont'];
    $empr_tip_empr = val_check_inv($aForm['empr_tip_empr'], 'S', 'N');
    $empr_num_resu = $aForm['empr_num_resu'];
    $empr_lic_cont = $aForm['empr_lic_cont'];
    $empr_ac1_empr = $aForm['empr_ac1_empr'];
    $empr_ac2_empr = $aForm['empr_ac2_empr'];
    $empr_ac3_empr = $aForm['empr_ac3_empr'];
    $empr_rrhh_nom = $aForm['empr_rrhh_nom'];
    $empr_path_logo = $aForm['empr_path_logo'];
    $empr_cuad_logo = $aForm['empr_cuad_logo'];
    $empr_web_color = $aForm['empr_web_color'];
    $empr_web_color2 = $aForm['empr_web_color2'];
    $empr_tie_espera = $aForm['empr_tie_espera'];
    $empr_num_estab = $aForm['empr_num_estab'];
    $empr_tip_impr = $aForm['empr_tip_impr'];

    $empr_nom_toke = $aForm['empr_nom_toke'];
    $empr_pass_toke = $aForm['empr_pass_toke'];

    $empr_fec_resu = fecha_informix_func($aForm['empr_fec_resu']);

    $empr_rimp_sn = val_check_inv($aForm['empr_rimp_sn'], 'S', 'N');
    $empr_rete_sn = val_check_inv($aForm['empr_rete_sn'], 'S', 'N');
    if (!$empr_opc_empr) {
        $empr_opc_empr = 0;
    }
    if (!$empr_cod_codigo) {
        $empr_cod_codigo = 0;
    }
    $empr_ema_sn = $aForm['empr_ema_sn'];
    $empr_cfe_contr = $aForm['empr_cfe_contr'];
    $empr_mone_fxfp = $aForm['empr_mone_fxfp'];
    $empr_asum_igtf = $aForm['empr_asum_igtf'];

    $empr_ema_test = $aForm['empr_ema_test'];

    $empr_rol_sn = $aForm['empr_rol_sn'];

    if (empty($empr_rol_sn)) {
        $empr_rol_sn = 'N';
    } else {
        $empr_rol_sn = 'S';
    }

    $empr_bus_pers = $aForm['empr_bus_pers'];

    if (empty($empr_bus_pers)) {
        $empr_bus_pers = 'N';
    } else {
        $empr_bus_pers = 'S';
    }
    
    //Parametro compras proveedores

    $emmpr_uafe_cprov = !empty($aForm['emmpr_uafe_cprov']) ? 'true' : 'false';



    $empr_tip_comp = $aForm['empr_tip_comp'];
    $empr_tip_agri = $aForm['empr_tip_agri'];

    if (empty($empr_tip_comp)) {
        $empr_tip_comp = 0;
    } else {
        $empr_tip_comp = 1;
    }

    if (empty($empr_tip_agri)) {
        $empr_tip_agri = 0;
    } else {
        $empr_tip_agri = 1;
    }

    $empr_ws_sri_url = $aForm['empr_ws_sri_url'];
    $empr_token_api = $aForm['empr_token_api'];
    $empr_key_maps = $aForm['empr_key_maps'];
    $empr_ws_sri_sn = $aForm['empr_ws_sri_sn'];

    if (empty($empr_ws_sri_sn)) {
        $empr_ws_sri_sn = 'N';
    } else {
        $empr_ws_sri_sn = 'S';
    }


    $empr_ws_iden_sn = $aForm['empr_ws_iden_sn'];

    if (empty($empr_ws_iden_sn)) {
        $empr_ws_iden_sn = 'N';
    } else {
        $empr_ws_iden_sn = 'S';
    }

    $empr_ws_iden_url = $aForm['empr_ws_iden_url'];
    $empr_ws_iden_token = $aForm['empr_ws_iden_token'];
    $empr_ws_iden_renueva = $aForm['empr_ws_iden_renueva'] ? $aForm['empr_ws_iden_renueva'] : 30;


    $empr_servi_url = $aForm['empr_servi_url'];
    $empr_servi_user = $aForm['empr_servi_user'];
    $empr_servi_pass = $aForm['empr_servi_pass'];
    $empr_servi_sn = $aForm['empr_servi_sn'];
    $empr_enti_code = $aForm['empr_enti_code'];

    if (empty($empr_servi_sn)) {
        $empr_servi_sn = 0;
    } else {
        $empr_servi_sn = 1;
    }

    // datos a actualizar laarcourier
    $empr_laar_url = $aForm['empr_laar_url'];
    $empr_laar_user = $aForm['empr_laar_user'];
    $empr_laar_pass = $aForm['empr_laar_pass'];
    $empr_laar_cod = $aForm['empr_laar_cod'];


    $empr_laar_sn = val_check_inv($aForm['empr_laar_sn'], 'S', 'N');

    // datos a actualizar SFTP BANCO DEL PICHINCHA
    $empr_bpi_sftp_user         = $aForm['empr_bpi_sftp_user'];
    $empr_bpi_sftp_ip           = $aForm['empr_bpi_sftp_ip'];
    $empr_bpi_sftp_port         = $aForm['empr_bpi_sftp_port'];
    $empr_bpi_sftp_ppk_f_dir    = $aForm['empr_bpi_sftp_ppk_f_dir'];
    $empr_bpi_sftp_remote_dir    = $aForm['empr_bpi_sftp_remote_dir'];
    $empr_bpi_sftp_local_dir    = $aForm['empr_bpi_sftp_local_dir'];


    $empr_bpi_sftp_sn = val_check_inv($aForm['empr_bpi_sftp_sn'], 'S', 'N');

    // datos a actualizar OZMAP
    $empr_ozmap_sn          = val_check_inv($aForm['empr_ozmap_sn'], 'S', 'N');
    $empr_ozmap_url         = $aForm['empr_ozmap_url'];
    $empr_ozmap_user        = $aForm['empr_ozmap_user'];
    $empr_ozmap_pass        = $aForm['empr_ozmap_pass'];
    $empr_ozmap_api_token   = $aForm['empr_ozmap_api_token'];



    $empr_gmaps_sn = val_check_inv($aForm['empr_gmaps_sn'], 'S', 'N');

    $empr_dig_celu = $aForm['empr_dig_celu'];
    $empr_for_rdep = $aForm['empr_for_rdep'];

    $empr_dataico_id = $aForm['empr_dataico_id'];
    $empr_dataico_token = $aForm['empr_dataico_token'];

    // SIIGO VALORES A ACTUALIZAR
    $empr_siigo_sn = val_check_inv($aForm['empr_siigo_sn'], 'S', 'N');
    $empr_siigo_autoenvio = val_check_inv($aForm['empr_siigo_autoenvio'], 'S', 'N');
    $empr_siigo_autoenvio_mail = val_check_inv($aForm['empr_siigo_autoenvio_mail'], 'S', 'N');

    $empr_siigo_api_url = $aForm['empr_siigo_api_url'];
    $empr_siigo_username = $aForm['empr_siigo_username'];
    $empr_siigo_access_token = $aForm['empr_siigo_access_token'];
    $empr_siigo_partnerid = $aForm['empr_siigo_partnerid'];


    //OPENPAY VALORES A ACTUALIZAR
    $empr_openpay_sn = $aForm['empr_openpay_sn'];
    $empr_openpay_api_url = $aForm['empr_openpay_api_url'];
    $empr_openpay_idempresa = $aForm['empr_openpay_idempresa'];
    $empr_openpay_publick = $aForm['empr_openpay_publick'];
    $empr_openpay_privatek = $aForm['empr_openpay_privatek'];

    if (empty($empr_openpay_sn)) {
        $empr_openpay_sn = 'N';
    } else {
        $empr_openpay_sn = 'S';
    }

    $empr_nomcome_empr = $aForm['empr_nomcome_empr'];

    $empr_cod_ftdr = $aForm['empr_cod_ftdr'];

    if (empty($empr_for_rdep)) {
        $empr_for_rdep = 'N';
    } else {
        $empr_for_rdep = 'S';
    }

    if (empty($empr_cod_ftdr)) {
        $empr_cod_ftdr = 1;
    }

    if (empty($empr_cod_parr)) {
        $empr_cod_parr = 'NULL';
    }

    if (empty($empr_cod_ciud)) {
        $empr_cod_ciud = 'NULL';
    }

    //CAMPOS COMPARTIR REDES SOCIALES

    $empr_frso_empr = $aForm['empr_frso_empr'];
    if (empty($empr_frso_empr)) {
        $empr_frso_empr = 'NULL';
    }
    $empr_rsoc_empr = $aForm['empr_rsoc_empr'];
    if (empty($empr_rsoc_empr)) {
        $empr_rsoc_empr = 'N';
        $empr_frso_empr = 'NULL';
    }

    //CAMPOS INFORMACION BANCARIA
    $empr_cta_sn = $aForm['empr_cta_sn'];
    if (empty($empr_cta_sn)) {
        $empr_cta_sn = 'N';
    }
    $empr_det_cta = trim(nl2br($aForm['empr_det_cta']));
    $empr_det_cta = $empr_det_cta != '' ? "'" . $empr_det_cta . "'" : 'NULL';


    //CAMPOS INFORMACION ADICIONAL RIDE
    $empr_rinf_sn = $aForm['empr_rinf_sn'];
    if (empty($empr_rinf_sn)) {
        $empr_rinf_sn = 'N';
    }
    $empr_det_rinf = trim(nl2br($aForm['empr_det_rinf']));
    $empr_det_rinf = $empr_det_rinf != '' ? "'" . $empr_det_rinf . "'" : 'NULL';



    $empr_sn_conta = $aForm['obliconta_sn'];

    $empr_cod_uni = $aForm['empr_cod_uni'];


    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');

        // SAETRAN
        $sql = "update saeempr set  empr_cod_ciud   = $empr_cod_ciud,
									empr_nom_empr   = '$empr_nom_empr',
									empr_dir_empr   = '$empr_dir_empr',		
									empr_ruc_empr   = '$empr_ruc_empr',
								    empr_cpo_empr   = '$empr_cpo_empr',
									empr_mai_empr   = '$empr_mai_empr',
									empr_repres     = '$empr_repres',
									empr_opc_empr   = $empr_opc_empr,
								    empr_tel_resp   = '$empr_tel_resp',
									empr_fax_empr   = '$empr_fax_empr',
									empr_ced_repr   = '$empr_ced_repr',
									empr_num_dire   = '$empr_num_dire',
								    empr_nom_cont   = '$empr_nom_cont',
									empr_ruc_cont   = '$empr_ruc_cont',
									empr_lic_cont   = '$empr_lic_cont',
									empr_tip_empr   = '$empr_tip_empr',
									empr_cod_pais   =  $empr_pais_ruc,
								    empr_num_resu   = '$empr_num_resu',
									empr_rimp_sn    = '$empr_rimp_sn',
                                    empr_cod_parr   = $empr_cod_parr,
									empr_tip_impr    = '$empr_tip_impr',
                                    empr_cod_ftdr   = $empr_cod_ftdr,
									empr_rete_sn    = '$empr_rete_sn',";

        if (!empty($empr_fec_resu) && $empr_fec_resu != '//') {
            $sql .= " empr_fec_resu   = '$empr_fec_resu',";
        }

        $sql .= " 				empr_tip_iden   = '$empr_tip_iden',
									empr_cod_prov   = '$empr_cod_prov',
								    empr_cod_cant   = '$empr_cod_cant',
									empr_ema_repr   = '$empr_ema_repr',
									empr_ac1_empr   = '$empr_ac1_empr',
									empr_ac2_empr   = '$empr_ac2_empr',
								    empr_nom_mzon   = '$empr_nom_mzon',
									empr_dir_mzon   = '$empr_dir_mzon',
									empr_pais_ruc   = '$empr_pais_ruc',
									empr_iva_empr   = $empr_iva_empr,
								    empr_con_pres   = '$empr_con_pres',
									empr_cta_extr   = '$empr_cta_extr',
									empr_tipo_empr  = '$empr_tipo_empr',
									empr_cod_codigo = $empr_cod_codigo,
								    empr_num_estab  = '$empr_num_estab',
									empr_conta_sn   = '$empr_conta_sn',
									empr_path_logo  = '$empr_path_logo',
									empr_tie_espera = $empr_tie_espera,
								    empr_ema_comp   = '$empr_ema_comp',		
									empr_cod_cesa   = '$empr_cod_cesa',
									empr_cod_aduna  = '$empr_cod_aduna',
									empr_cm1_empr   = '$empr_cm1_empr',
								    empr_cm2_empr   = '$empr_cm2_empr',
									empr_sitio_web  = '$empr_sitio_web',
									empr_img_rep  = '$empr_cuad_logo',
									empr_web_color  = '$empr_web_color',
									empr_web_color2  = '$empr_web_color2',
									empr_menu_sucu  = '$empr_menu_sucu',
									empr_ema_sn='$empr_ema_sn',
									empr_cfe_contr='$empr_cfe_contr',
                                    empr_mone_fxfp='$empr_mone_fxfp',
                                    empr_asum_igtf='$empr_asum_igtf',
									empr_ema_test='$empr_ema_test',
									empr_ac3_empr   = '$empr_ac3_empr',
									empr_rrhh_nom   = '$empr_rrhh_nom',
									empr_rol_sn     = '$empr_rol_sn',
									empr_tip_comp     = '$empr_tip_comp',
									empr_tip_agri     = '$empr_tip_agri',
									empr_ws_sri_sn  = '$empr_ws_sri_sn',

									empr_ws_iden_sn  = '$empr_ws_iden_sn',
									empr_ws_iden_url  = '$empr_ws_iden_url',
									empr_ws_iden_token  = '$empr_ws_iden_token',
									empr_ws_iden_renueva  = '$empr_ws_iden_renueva',


									empr_servi_sn  = '$empr_servi_sn',
									empr_servi_url  = '$empr_servi_url',
									empr_servi_user  = '$empr_servi_user',
									empr_servi_pass  = '$empr_servi_pass',
									empr_enti_code  = '$empr_enti_code',

                                    empr_laar_sn  = '$empr_laar_sn',
                                    empr_gmaps_sn  = '$empr_gmaps_sn',
									empr_laar_url  = '$empr_laar_url',
									empr_laar_user  = '$empr_laar_user',
									empr_laar_pass  = '$empr_laar_pass',
									empr_laar_cod  = '$empr_laar_cod',

                                    empr_bpi_sftp_sn        = '$empr_bpi_sftp_sn',
									empr_bpi_sftp_user      = '$empr_bpi_sftp_user',
									empr_bpi_sftp_ip        = '$empr_bpi_sftp_ip',
									empr_bpi_sftp_port      = '$empr_bpi_sftp_port',
									empr_bpi_sftp_ppk_f_dir = '$empr_bpi_sftp_ppk_f_dir',
									empr_bpi_sftp_remote_dir = '$empr_bpi_sftp_remote_dir',
									empr_bpi_sftp_local_dir = '$empr_bpi_sftp_local_dir',

                                    empr_ozmap_sn           = '$empr_ozmap_sn',
									empr_ozmap_url          = '$empr_ozmap_url',
									empr_ozmap_user         = '$empr_ozmap_user',
									empr_ozmap_pass         = '$empr_ozmap_pass',
									empr_ozmap_api_token    = '$empr_ozmap_api_token',


									empr_ws_sri_url = '$empr_ws_sri_url',
									empr_token_api = '$empr_token_api',
									empr_key_maps = '$empr_key_maps',
									empr_dataico_id = '$empr_dataico_id',
									empr_dataico_token = '$empr_dataico_token',

									empr_siigo_sn               = '$empr_siigo_sn',
									empr_siigo_autoenvio        = '$empr_siigo_autoenvio',
									empr_siigo_autoenvio_mail   = '$empr_siigo_autoenvio_mail',
									empr_siigo_api_url          = '$empr_siigo_api_url',
									empr_siigo_username         = '$empr_siigo_username',
									empr_siigo_access_token     = '$empr_siigo_access_token',
									empr_siigo_partnerid        = '$empr_siigo_partnerid',
                                    

                                    empr_openpay_sn = '$empr_openpay_sn',
                                    empr_openpay_api_url = '$empr_openpay_api_url',
                                    empr_openpay_idempresa = '$empr_openpay_idempresa',
                                    empr_openpay_publick = '$empr_openpay_publick',
                                    empr_openpay_privatek = '$empr_openpay_privatek',

									empr_dig_celu = $empr_dig_celu,
									empr_for_rdep = '$empr_for_rdep',
                                    empr_nomcome_empr = '$empr_nomcome_empr',
                                    empr_nom_toke = '$empr_nom_toke',
                                    empr_pass_toke = '$empr_pass_toke',
                                    empr_rsoc_empr = '$empr_rsoc_empr',
                                    empr_frso_empr = $empr_frso_empr,
                                    empr_cta_sn    = '$empr_cta_sn',
                                    empr_det_cta   = $empr_det_cta,
                                    empr_bus_pers  = '$empr_bus_pers',
                                    empr_sn_conta  = '$empr_sn_conta',
                                    empr_rinf_sn    = '$empr_rinf_sn',
                                    empr_det_rinf   = $empr_det_rinf,
                                    empr_cod_uni    = '$empr_cod_uni',
                                    emmpr_uafe_cprov = $emmpr_uafe_cprov
								    where 
								    empr_cod_empr   = $empr_cod_empr ";
        

        //print_r($sql);exit;
        $oIfx->QueryT($sql);
        $oIfx->QueryT('COMMIT WORK;');
        $oReturn->alert('Actualizado Correctamente...');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function bodegas($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucu_cod_origen'];
    unset($_SESSION['U_ARRAY_SBODE']);

    try {

        //lectura sucia
        //////////////

        $table .= '<br>';
        $table .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 50%; margin-top: 20px;" align="center">';
        $table .= '<tr>
								<td align="center">N.-</td>
								<td align="center">Serial</td>
								<td align="center">Bodega</td>
								<td align="center"></td>
						</tr>';

        $sql = "select bode_cod_bode, bode_nom_bode from saebode, saesubo where
					bode_cod_bode = subo_cod_bode and
					bode_cod_empr = $idempresa and
					subo_cod_empr = $idempresa and
					subo_cod_sucu = $idsucursal
					order by 1  ";
        unset($array_bode);
        $i = 1;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $bode_cod = $oIfx->f('bode_cod_bode');
                    $bode_nom = $oIfx->f('bode_nom_bode');

                    $array_bode[] = array($bode_cod, $bode_nom);

                    $fu->AgregarCampoCheck($bode_cod, '|left', false, 'N');

                    if ($sClass == 'off')
                        $sClass = 'on';
                    else
                        $sClass = 'off';
                    $table .= '<tr height="25" class="' . $sClass . '"
														onMouseOver="javascript:this.className=\'link\';"
														onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    $table .= '<td align="right"  >' . $i . '</td>';
                    $table .= '<td align="right"   >' . $bode_cod . '</td>';
                    $table .= '<td align="left"  >' . $bode_nom . '</td>';
                    $table .= '<td align="right"  >' . $fu->ObjetoHtml($bode_cod) . '</td>';
                    $table .= '</tr>';

                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        $table .= '</table>';

        $_SESSION['U_ARRAY_SBODE'] = $array_bode;
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    $oReturn->assign("bode", "innerHTML", $table);

    return $oReturn;
}

function actualiza_sp_sri_web($oCon)
{


    //SP ATS 
    $sqlalter = "
    CREATE OR REPLACE FUNCTION \"public\".\"sp_ats_sri_web\"(\"in_empr\" int4, \"in_ejer\" int4, \"in_mes\" int4, \"in_anio\" int4, \"in_user\" int4, \"in_op\" int4, \"in_iva\" int4)
    RETURNS \"pg_catalog\".\"varchar\" AS \$BODY\$

-- ats saefprv
declare fprv_cod_sucu_o integer;
fprv_cod_clpv_o integer;
fprv_cre_fisc_o varchar(20);
clv_con_clpv_o varchar(20);
clpv_ruc_clpv_o varchar(20);
clpv_cod_tprov_o varchar(20);
clpv_par_rela_o varchar(20);
fprv_apl_conv_o varchar(20);
fprv_pag_exte_o varchar(20);
tran_tip_comp_o varchar(20);
fprv_fec_emis_o date;
fprv_fec_emis1_o date;
estab_o varchar(20);
ptoemi_o varchar(20);
fprv_num_fact_o varchar(100);
fprv_num_auto_o varchar(100);
basenogralva_o decimal(16,2);
baseimponible_o decimal(16,2);
baseimpgrav_o decimal(16,2);
montoice_o decimal(16,2);
fprv_val_viva_o decimal(16,2);
porc1_o decimal(16,2);
porc2_o decimal(16,2);
val1_o decimal(16,2);
val2_o decimal(16,2);
fprv_cod_tpago_o varchar(20);
fprv_cod_fpagop_o varchar(20);
estab_ret_o varchar(20);
ptoemi_ret_o varchar(20);
fprv_aut_rete_o varchar(100);
fprv_fec_rete_o date;
fprv_num_rete_o varchar(100);
fprv_cod_tran_o varchar(20);
fprv_num_seri_o varchar(20);
fprv_mnt_noi_o varchar(20);
fprv_val_iva1_o  decimal(16,2);
valretservicios_o  decimal(16,2);
valretserv100_o  decimal(16,2);
fprv_cod_rtf1_o  varchar(20);
fprv_val_bas1_o decimal(16,2);
fprv_por_ret1_o decimal(16,2);
fprv_val_ret1_o decimal(16,2);
fprv_cod_rtf2_o varchar(20);
fprv_val_bas2_o decimal(16,2);
fprv_por_ret2_o decimal(16,2);
fprv_val_ret2_o decimal(16,2);
clpv_cod_pais_o integer;
fprv_auto_sri_o varchar(100);
fprv_val_noi_o decimal(16,2);
fprv_val_exe_o decimal(16,2);
fprv_reg_fis_o varchar(2);
fprr_tot_imp_o decimal(16,2);

-- rete fprv
ret_cta_ret_o varchar(100);
ret_porc_ret_o decimal(16,2);
ret_bas_imp_o decimal(16,2);
ret_valor_o decimal(16,2);
ret_num_ret_cp varchar(50);

msn varchar(100);

-- ats inventario
sucu_cod_sucu_in integer;
minv_cod_clpv_in integer;
clv_con_clpv_in varchar(20);
clpv_ruc_clpv_in varchar(50);
clpv_cod_tprov_in varchar(50);
clpv_par_rela_in varchar(20);
minv_apl_conv_in varchar(20); 
minv_pag_exte_in varchar(20);
minv_cod_tran_in varchar(20);
tran_tip_comp_in varchar(20);
minv_fmov_in date;
estab_in varchar(20);
ptoemi_in varchar(20);
minv_fac_prov_in varchar(100);
fecha_emi_in date;
minv_aut_usua_in varchar(100);
valoretbienes10_in decimal(16,2);
valoretservicios20_in decimal(16,2);
valoretbienes30_in decimal(16,2);
valoretservicios70_in decimal(16,2);
valretserv100_in decimal(16,2);
minv_iva_valo_in decimal(16,2);
minv_tot_minv_in decimal(16,2);
con_iva_in  decimal(16,2);
sin_iva_in  decimal(16,2);
minv_val_ice_in  decimal(16,2);
minv_cod_tpago_in  varchar(20);
minv_cod_fpagop_in  varchar(20);
asto_fec date;
ret_num_ret_inv varchar(50);
estab_ret_inv varchar(6);
ptoemi_ret_inv varchar(16);
auto_ret_inv varchar(255);
minv_auto_sri_in varchar(100);
minv_val_noi_in  decimal(16,2);
minv_val_exe_in  decimal(16,2);
minv_reg_fis_in varchar(2);
minv_num_caja_in  varchar(100);
minv_prec_caja_in  varchar(100);
minv_ser_docu_in varchar(10);

-- ats venta
fact_cod_clpv_v integer;
clv_con_clpv_v varchar(20);
clpv_ruc_clpv_v varchar(20);
baseimpgrav_v decimal(16,2);
montoiva_v decimal(16,2);
montoice_v decimal(16,2);
factura_v varchar(100);
valoretiva_v decimal(16,2);
valoretrenta_v decimal(16,2);
baseimponible_v decimal(16,2);
basenograv_v decimal(16,2);
clpv_par_rela_v varchar(20);
aprobsri_v varchar(20);


-- ats debito
fact_cod_clpv_d integer;
clv_con_clpv_d varchar(20);
clpv_ruc_clpv_d varchar(20);
baseimpgrav_d decimal(16,2);
montoiva_d decimal(16,2);
factura_d varchar(100);
valoretiva_d decimal(16,2);
valoretrenta_d decimal(16,2);
baseimponible_d decimal(16,2);
aprobsri_d varchar(20);
aprobsri_f varchar(20);
begin

delete from tmp_ats_sis_inv ;
delete from tmp_ats_sis;
delete from tmp_ats_sis_vent;
delete from tmp_ats_sis_deb;
delete from tmp_ats_sis_ret;
delete from tmp_ats_sis_flor;

if in_op > 0 and in_op < 2 then		
for fprv_cod_sucu_o, fprv_cre_fisc_o, clv_con_clpv_o, fprv_cod_clpv_o, clpv_ruc_clpv_o, clpv_cod_tprov_o, clpv_par_rela_o, fprv_apl_conv_o, fprv_pag_exte_o, fprv_cod_tran_o, tran_tip_comp_o, fprv_fec_emis_o, estab_o, ptoemi_o, fprv_num_seri_o, fprv_num_fact_o, fprv_fec_emis1_o, fprv_num_auto_o, fprv_mnt_noi_o, basenogralva_o, baseimponible_o, baseimpgrav_o, montoice_o, fprv_val_viva_o, fprv_val_iva1_o, valretservicios_o, valretserv100_o, porc1_o, porc2_o, val1_o, val2_o, fprv_cod_tpago_o, fprv_cod_fpagop_o, fprv_cod_rtf1_o, fprv_val_bas1_o, fprv_por_ret1_o, fprv_val_ret1_o, fprv_cod_rtf2_o, fprv_val_bas2_o, fprv_por_ret2_o, fprv_val_ret2_o, estab_ret_o, ptoemi_ret_o, fprv_aut_rete_o, fprv_fec_rete_o, fprv_num_rete_o, clpv_cod_pais_o, fprv_auto_sri_o, fprv_val_noi_o, fprv_val_exe_o, fprv_reg_fis_o, fprr_tot_imp_o
-- compras ats saefprv
in SELECT F.FPRV_COD_SUCU, F.FPRV_CRE_FISC,  C.CLV_CON_CLPV,  F.FPRV_COD_CLPV,
                    F.FPRV_RUC_PROV AS CLPV_RUC_CLPV,  C.clpv_cod_tprov ,  C.clpv_par_rela,   
                    F.fprv_apl_conv, F.fprv_pag_exte , F.FPRV_COD_TRAN,
                    (  select trans_tip_comp from saetran where
                            tran_cod_empr = in_empr  and
                            tran_cod_modu = 4 and
                            tran_cod_tran =   F.FPRV_COD_TRAN  GROUP BY 1 ) AS TRAN_TIP_COMP,  
                    F.FPRV_FEC_EMIS,

                    ( substring( F.FPRV_NUM_SERI from 1 for 3 ) ) AS ESTAB,
                    ( substring( F.FPRV_NUM_SERI from 4 for 6 ) ) AS PTOEMI,

                    F.FPRV_NUM_SERI,   F.FPRV_NUM_FACT, F.FPRV_FEC_EMIS,
                    F.FPRV_NUM_AUTO,   F.FPRV_MNT_NOI,
                    round( ( CASE
                    WHEN F.FPRV_MNT_NOI = 'S' THEN ( coalesce(F.FPRV_VAL_GRA0,0) + coalesce(F.FPRV_VAL_GR0S,0) )
                    ELSE
                    0
                    END ) ,2) AS BASENOGRALVA, 

                    round( ( CASE
                    WHEN F.FPRV_MNT_NOI = 'S'      THEN 0
                    ELSE
                        ( coalesce(F.FPRV_VAL_GRA0,0) + coalesce( F.FPRV_VAL_GR0S,0 ) )
                    END ) ,2) AS BASEIMPONIBLE,

                    round(( coalesce( ( coalesce( F.fprv_val_grbs,0) + coalesce( F.fprv_val_grab,0) ),0)  ),2)  AS BASEIMPGRAV,
                    round( ( coalesce( ( F.FPRV_VAL_VICE ),0) ),2)  AS MONTOICE,
                    round( ( coalesce(F.FPRV_VAL_VIVA,0)),2) as FPRV_VAL_VIVA,
                    round( ( coalesce( ( coalesce( F.FPRV_VAL_IVA1,0) ),0)),2)  as FPRV_VAL_IVA1,

                    round ( ( CASE
                                WHEN F.FPRV_POR_IVA2= 70
                            THEN ( F.FPRV_VAL_IVA2 )
                                ELSE                          0
                            END ) ,2)  AS VALRETSERVICIOS,

                    round ( ( CASE
                                WHEN ( ( COALESCE(F.FPRV_POR_IVA2,0)  =  100 )  or ( COALESCE(F.fprv_por_iva1,0) = 100 ) )                                   THEN (  COALESCE( F.FPRV_VAL_IVA2,0)  +   COALESCE( F.FPRV_VAL_IVA1 ,0) )
                                ELSE
                                0
                                END ),2)  AS VALRETSERV100,   round((COALESCE(F.FPRV_POR_IVA1,0)),2) AS porc1,
                    round((COALESCE(F.FPRV_POR_IVA2,0)),2) AS porc2,
                    round((COALESCE(F.FPRV_VAL_IVA1,0)),2) AS val1,
                    round((COALESCE(F.FPRV_VAL_IVA2,0)),2) AS val2,
                    C.CLPV_COD_TPAGO,
                    C.CLPV_COD_FPAGOP,       F.fprv_cod_rtf1,
                    F.fprv_val_bas1,
                    F.fprv_por_ret1,
                    F.fprv_val_ret1,

                    F.fprv_cod_rtf2,
                    F.fprv_val_bas2,    F.fprv_por_ret2,
                    F.fprv_val_ret2,

                    ( substring( F.fprv_ser_rete from 1 for 3 ) ) AS ESTAB_RET,
                    ( substring( F.fprv_ser_rete from 4 for 6 ) ) AS PTOEMI_RET,     fprv_aut_rete,   fprv_fec_rete, fprv_num_rete, c.clpv_cod_paisp, F.FPRV_AUTO_SRI,
                    F.fprv_val_noi, F.fprv_val_exe, F.fprv_reg_fis,
                    (select sum(round((coalesce(fr.fprr_val_grab, 0) + coalesce(fr.fprr_val_gras,0)) + (coalesce(fr.fprr_val_gra0 , 0) + coalesce(fr.fprr_val_grs0,0)),2)) as fprr_tot_imp from saefprr fr where fr.fprr_fac_fprv like CONCAT('%',F.FPRV_NUM_FACT,'%') and
                                                                        fr.fprr_cod_empr = in_empr and 
                                                                        fr.fprr_cod_ejer = in_ejer and
                                                                        fr.fprr_clpv_fprv = F.FPRV_COD_CLPV and
                                                                        fr.fprr_cod_empr = F.FPRV_COD_EMPR and
                                                                        fr.fprr_cod_ejer = F.FPRV_COD_EJER) AS FPRR_TOT_IMP
                                        FROM SAEFPRV F, SAECLPV C WHERE
                    C.CLPV_COD_CLPV = F.FPRV_COD_CLPV AND
                    C.CLPV_COD_EMPR = in_empr  AND
                    F.FPRV_COD_EMPR = in_empr  AND       F.FPRV_COD_EJER = in_ejer AND
                    EXTRACT(MONTH FROM F.FPRV_FEC_EMIS) = in_mes loop

insert into tmp_ats_sis (  empr_cod_empr , sucu_cod_sucu, ejer_cod_ejer ,  anio_id ,  prdo_num_prdo , fprv_cod_clpv ,  fprv_cre_fisc , clv_con_clpv , clpv_ruc_clpv,
        tran_tip_comp , fprv_fec_emis , 	estab ,   ptoemi ,  fprv_num_fact ,  fprv_num_auto ,  basenogralva ,  baseimponible ,  baseimpgrav ,
        montoice ,  fprv_val_viva , porc1 , porc2 ,	val1 ,  val2 , fprv_cod_tpago ,	fprv_cod_fpagop , estab_ret ,	ptoemi_ret ,  fprv_aut_rete ,
        fprv_fec_rete , fprv_num_rete , user_cod_user, fprv_cod_rtf1 , fprv_cod_rtf2  , clpv_cod_pais, clpv_cod_tprov, clpv_par_rela,  fprv_apl_conv, fprv_pag_exte, fprv_auto_sri, 
        fprv_val_noi, fprv_val_exe, fprv_reg_fis, fprr_tot_imp, fprv_num_seri) 
        values( in_empr,  fprv_cod_sucu_o,  in_ejer,   in_anio, in_mes,      fprv_cod_clpv_o,    fprv_cre_fisc_o ,  clv_con_clpv_o ,   clpv_ruc_clpv_o ,
            tran_tip_comp_o ,   fprv_fec_emis_o,  estab_o ,  ptoemi_o ,    fprv_num_fact_o ,     fprv_num_auto_o ,  basenogralva_o ,  baseimponible_o ,  baseimpgrav_o ,
            montoice_o ,       fprv_val_viva_o ,        porc1_o ,  porc2_o ,  val1_o , val2_o ,    fprv_cod_tpago_o ,   fprv_cod_fpagop_o , estab_ret_o ,  ptoemi_ret_o , fprv_aut_rete_o,
            fprv_fec_rete_o , fprv_num_rete_o ,  in_user  ,     fprv_cod_rtf1_o  , fprv_cod_rtf2_o, clpv_cod_pais_o, clpv_cod_tprov_o, clpv_par_rela_o  , fprv_apl_conv_o , fprv_pag_exte_o, fprv_auto_sri_o,
            fprv_val_noi_o, fprv_val_exe_o, fprv_reg_fis_o, fprr_tot_imp_o, fprv_num_seri_o);
end loop;

-- SQLINES DEMO *** PRAS
for fprv_cod_clpv_o, fprv_cod_sucu_o, fprv_num_fact_o, clpv_ruc_clpv_o, fprv_num_seri_o
in select fprv_cod_clpv, sucu_cod_sucu,  fprv_num_fact,  clpv_ruc_clpv, fprv_num_seri
                        from tmp_ats_sis where    empr_cod_empr = in_empr and
                        ejer_cod_ejer = in_ejer and prdo_num_prdo = in_mes and
                        user_cod_user = in_user loop
for ret_cta_ret_o, ret_porc_ret_o, ret_bas_imp_o, ret_valor_o, ret_num_ret_cp 

            in SELECT  R.RET_CTA_RET,   round(R.RET_PORC_RET,2) as ret_porc_ret  , 
                round( R.RET_BAS_IMP,2) as ret_bas_imp,
                round( R.RET_VALOR,2) as ret_valor , R.ret_num_ret
                FROM SAERET R, SAEASTO A, SAEFPRV FP WHERE   A.ASTO_COD_ASTO = R.RETE_COD_ASTO AND               
FP.FPRV_COD_ASTO = R.RETE_COD_ASTO AND
            FP.FPRV_COD_ASTO = R.RETE_COD_ASTO AND
            FP.FPRV_COD_EMPR = R.ASTO_COD_EMPR AND
            FP.FPRV_COD_SUCU = R.ASTO_COD_SUCU AND
            FP.FPRV_COD_EJER = R.ASTO_COD_EJER AND
            FP.FPRV_NUM_FACT = R.RET_NUM_FACT AND
            FP.FPRV_COD_CLPV = R.RET_COD_CLPV AND  
                    A.ASTO_COD_EMPR = in_empr AND
                    A.ASTO_COD_EJER = in_ejer AND
                    A.ASTO_NUM_PRDO = in_mes AND
                    A.ASTO_COD_SUCU = fprv_cod_sucu_o  AND
                    A.ASTO_EST_ASTO <> 'AN' AND
                    R.RET_CRE_ML >= 0  AND		R.ASTO_COD_EMPR =  in_empr AND      
                    R.ASTO_COD_EJER =  in_ejer AND
                    R.ASTO_NUM_PRDO =  in_mes AND
                    R.RET_COD_CLPV =   fprv_cod_clpv_o  AND            
                    R.RET_NUM_FACT =  fprv_num_fact_o  AND
                    FP.FPRV_NUM_SERI = fprv_num_seri_o AND
                    R.ret_cta_ret in ( select tret_cod from saetret WHERE
                                                tret_cod_empr =  in_empr and             
                                                tret_ban_retf in ('IR','RI') and
                                                tret_ban_crdb = 'CR') loop
    

            insert into tmp_ats_sis_ret ( empr_cod_empr , sucu_cod_sucu , ejer_cod_ejer , anio_id , prdo_num_prdo , 
                                        ret_cod_clpv ,	ret_num_fact , ret_cta_ret , ret_porc_ret , 
                                        ret_bas_imp , ret_valor, user_cod_user , tipo,  ret_num_ret, estab, ptoemi )
                        values ( in_empr, fprv_cod_sucu_o,  in_ejer, in_anio, in_mes,  fprv_cod_clpv_o,   fprv_num_fact_o ,   
                                ret_cta_ret_o,  ret_porc_ret_o,  ret_bas_imp_o , ret_valor_o, in_user, '1' , ret_num_ret_cp,
                                fprv_num_seri_o, ptoemi_o);
end loop;
end loop;

-- SQLINES DEMO *** TARIO
for sucu_cod_sucu_in, minv_cod_clpv_in, clv_con_clpv_in, clpv_ruc_clpv_in, clpv_cod_tprov_in, clpv_par_rela_in, minv_apl_conv_in, minv_pag_exte_in, minv_cod_tran_in, tran_tip_comp_in, minv_fmov_in, estab_in, ptoemi_in, minv_fac_prov_in, minv_ser_docu_in, fecha_emi_in, minv_aut_usua_in, valoretbienes10_in, valoretservicios20_in, valoretbienes30_in, valoretservicios70_in, valretserv100_in, minv_iva_valo_in, minv_tot_minv_in, con_iva_in, sin_iva_in, minv_val_ice_in, minv_cod_tpago_in, minv_cod_fpagop_in, clpv_cod_pais_o, minv_auto_sri_in, minv_val_noi_in, minv_val_exe_in, minv_reg_fis_in, minv_num_caja_in, minv_prec_caja_in

in SELECT M.minv_cod_sucu, M.minv_cod_clpv, C.clv_con_clpv, C.clpv_ruc_clpv, C.clpv_cod_tprov,  
                C.clpv_par_rela,   M.minv_apl_conv, m.minv_pag_exte, M.MINV_COD_TRAN ,
                (  select DEFI_TIP_COMP from saedefi where  defi_cod_empr = in_empr and
                        defi_cod_modu = 10 and defi_cod_tran = M.MINV_COD_TRAN  GROUP BY 1 ) AS TRAN_TIP_COMP,
                M.minv_fmov,
                ( substring( M.minv_ser_docu  from 1 for 3 ) ) AS ESTAB,
                ( substring( M.minv_ser_docu from 4 for 6 ) ) AS PTOEMI,                  
                M.minv_fac_prov,  M.minv_ser_docu, ( M.minv_fmov ) as fecha_emi,  M.minv_aut_usua,
                
                (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                        asto_cod_empr = in_empr and    
                        asto_cod_ejer = in_ejer and
                        asto_num_prdo = in_mes and
                        ret_porc_ret = '10' and
                        -- SQLINES DEMO *** ' and               ret_cod_clpv =  M.MINV_COD_CLPV and                            
                        ret_num_fact =  M.minv_fac_prov and
                        rete_cod_asto = M.minv_comp_cont) As VALORETBIENES10,
                        
                (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                        asto_cod_empr = in_empr and    
                        asto_cod_ejer = in_ejer and
                        asto_num_prdo = in_mes and
                        ret_porc_ret = '20' and
                        -- SQLINES DEMO *** ' and
                        ret_cod_clpv =  M.MINV_COD_CLPV and                            
                        ret_num_fact =  M.minv_fac_prov  and
                        rete_cod_asto = M.minv_comp_cont) As VALORETSERVICIOS20,
                        
                (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                        asto_cod_empr = in_empr and    
                        asto_cod_ejer = in_ejer and
                        asto_num_prdo = in_mes and
                        ret_porc_ret = '30' and             -- SQLINES DEMO *** ' and
                        ret_cod_clpv =  M.MINV_COD_CLPV and                            
                        ret_num_fact =  M.minv_fac_prov  and
                        rete_cod_asto = M.minv_comp_cont) As VALORETBIENES30,      (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                        asto_cod_empr = in_empr and           
                        asto_cod_ejer = in_ejer and    
                        asto_num_prdo =  in_mes and
                        ret_porc_ret = '70' and
                        -- SQLINES DEMO *** ' and
                        ret_cod_clpv =  M.MINV_COD_CLPV and
                        ret_num_fact=  M.minv_fac_prov  and
                        rete_cod_asto = M.minv_comp_cont) AS  VALORETSERVICIOS70,						
                (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                        asto_cod_empr =  in_empr and
                        asto_cod_ejer =  in_ejer and
                        asto_num_prdo =  in_mes and   
                        ret_porc_ret = '100' and
                        -- SQLINES DEMO *** 25'  and
                        ret_cod_clpv  =  M.MINV_COD_CLPV and
                        ret_num_fact  =  M.minv_fac_prov  and
                        rete_cod_asto = M.minv_comp_cont) AS VALRETSERV100,

                round(coalesce(M.minv_iva_valo,0),2) as minv_iva_valo,

                round(( M.minv_tot_minv - coalesce(M.minv_dge_valo,0) + coalesce( M.minv_otr_valo,0 ) + coalesce( M.minv_fle_minv,0)  ),2)  as minv_tot_minv,  
                

                round(( select sum(dmov_cto_dmov) as base_grava 
									from  saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc > 0 and
									dmov_num_comp = M.minv_num_comp	 ),2) as con_iva ,
                        round(( select COALESCE(sum(dmov_cto_dmov), '0') as base_nograva from saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc = 0 and
									dmov_num_comp = M.minv_num_comp ) ,2 ) as sin_iva ,
                
                
                round(  coalesce( M.minv_val_ice,0),2  ) AS minv_val_ice,
                C.clpv_cod_tpago,
                C.clpv_cod_fpagop, C.clpv_cod_paisp, M.minv_auto_sri, M.minv_val_noi, M.minv_val_exe, M.minv_reg_fis, M.minv_num_caja, M.minv_prec_caja
                                FROM SAEMINV M, SAECLPV C  WHERE
                C.CLPV_COD_CLPV = M.MINV_COD_CLPV AND
                M.MINV_COD_EMPR =  in_empr AND
                M.MINV_COD_EJER = in_ejer  AND
                M.MINV_NUM_PRDO = in_mes AND              
                M.MINV_COD_TRAN IN  ( SELECT D.DEFI_COD_TRAN   FROM SAEDEFI D, SAETRAN T WHERE
                                                T.TRAN_COD_TRAN = D.DEFI_COD_TRAN AND              	
                                                D.DEFI_COD_MODU = 10 AND                                           		 	D.DEFI_COD_EMPR = in_empr AND                                    	
                                                D.DEFI_TIP_DEFI = '0' AND
                                                D.DEFI_TIP_COMP in( '01'  , '02', '03')  AND                               T.TRAN_COD_EMPR = in_empr ) AND
                M.MINV_COD_EJER =  in_ejer  AND
                M.MINV_NUM_PRDO = in_mes AND
                M.MINV_EST_MINV <> '0' loop	
    insert into tmp_ats_sis_inv (  empr_cod_empr  ,sucu_cod_sucu , 	ejer_cod_ejer ,  id_anio ,  prdo_num_prdo ,   minv_cod_clpv ,  
                                clv_con_clpv ,  clpv_ruc_clpv ,	minv_cod_tran ,  tran_tip_comp ,  minv_fmov ,  estab ,  	
                                ptoemi ,  	minv_fac_prov ,  minv_ser_docu,  fecha_emi , 	minv_aut_usua ,	valoretbienes10 , 	valoretservicios20,
                                valoretbienes30 , 	valoretservicios70 , 	
                                valretserv100 ,    minv_iva_valo ,   minv_tot_minv ,   con_iva ,	sin_iva ,	minv_val_ice ,  
                                minv_cod_tpago ,   minv_cod_fpagop ,user_cod_user , clpv_cod_pais, clpv_cod_tprov, 
                                clpv_par_rela  , minv_apl_conv, minv_pag_exte, minv_auto_sri, minv_val_noi, minv_val_exe, minv_reg_fis, minv_num_caja, minv_prec_caja) 
        values( in_empr,   sucu_cod_sucu_in,  in_ejer, in_anio,  in_mes,  minv_cod_clpv_in ,  clv_con_clpv_in ,  clpv_ruc_clpv_in ,  
            minv_cod_tran_in ,   tran_tip_comp_in ,   minv_fmov_in , estab_in ,  ptoemi_in ,  minv_fac_prov_in ,  minv_ser_docu_in, fecha_emi_in ,   
            minv_aut_usua_in ,   valoretbienes10_in ,   valoretservicios20_in, valoretbienes30_in ,   valoretservicios70_in , valretserv100_in , minv_iva_valo_in ,  
            minv_tot_minv_in ,  con_iva_in  ,  sin_iva_in  ,   minv_val_ice_in  ,  minv_cod_tpago_in  ,  minv_cod_fpagop_in  , 
            in_user, clpv_cod_pais_o , clpv_cod_tprov_in, clpv_par_rela_in , minv_apl_conv_in, minv_pag_exte_in, minv_auto_sri_in,
            minv_val_noi_in, minv_val_exe_in, minv_reg_fis_in, minv_num_caja_in, minv_prec_caja_in);

end loop;

ptoemi_ret_inv := '';

-- SQLINES DEMO *** RIO
for fprv_cod_clpv_o, fprv_cod_sucu_o, fprv_num_fact_o, clpv_ruc_clpv_o, fprv_num_seri_o
in select minv_cod_clpv, sucu_cod_sucu,  minv_fac_prov  ,  clpv_ruc_clpv, minv_ser_docu
            from tmp_ats_sis_inv where    
            empr_cod_empr = in_empr and
            ejer_cod_ejer = in_ejer and	prdo_num_prdo = in_mes and
            user_cod_user = in_user loop
for ret_cta_ret_o, auto_ret_inv, estab_ret_inv, ptoemi_ret_inv, ret_porc_ret_o, ret_bas_imp_o, ret_valor_o, asto_fec, ret_num_ret_inv 
    in SELECT  R.RET_CTA_RET, R.RET_AUT_RET, 
    ( R.RET_SER_RET  ) AS estab_ret,
    ( R.RET_SER_RET  ) AS  ptoemi_ret,
    round(R.RET_PORC_RET,2) as ret_porc_ret, 
                round( R.RET_BAS_IMP,2) as ret_bas_imp,
                round( R.RET_VALOR,2) as ret_valor , A.ASTO_FEC_ASTO, R.ret_num_ret
                                FROM SAERET R, SAEASTO A, SAEMINV M 
                WHERE   A.ASTO_COD_ASTO = R.RETE_COD_ASTO AND    
                M.MINV_COMP_CONT = R.RETE_COD_ASTO AND
                M.MINV_COD_EMPR = R.ASTO_COD_EMPR AND
                M.MINV_COD_SUCU = R.ASTO_COD_SUCU AND
                M.MINV_COD_EJER = R.ASTO_COD_EJER AND
                M.MINV_FAC_PROV = R.RET_NUM_FACT AND
                M.MINV_COD_CLPV = R.RET_COD_CLPV AND  
                A.ASTO_COD_EMPR = in_empr AND
                A.ASTO_COD_EJER = in_ejer AND
                A.ASTO_NUM_PRDO = in_mes AND
                A.ASTO_COD_SUCU = fprv_cod_sucu_o  AND
                A.ASTO_EST_ASTO <> 'AN' AND
                R.RET_CRE_ML >= 0  AND      
                R.ASTO_COD_EMPR =  in_empr AND      
                R.ASTO_COD_EJER =  in_ejer AND
                R.ASTO_NUM_PRDO =  in_mes AND
                R.RET_COD_CLPV =   fprv_cod_clpv_o  AND            
                R.RET_NUM_FACT =  fprv_num_fact_o  AND
                M.MINV_SER_DOCU = fprv_num_seri_o AND
                R.ret_cta_ret in ( select tret_cod from saetret WHERE
                                            tret_cod_empr =  in_empr and             
                                            tret_ban_retf in ('IR','RI') and
                                            tret_ban_crdb = 'CR') loop
    insert into tmp_ats_sis_ret ( empr_cod_empr , sucu_cod_sucu , ejer_cod_ejer , anio_id , prdo_num_prdo , 
                                ret_cod_clpv ,	ret_num_fact , ret_cta_ret , ret_porc_ret , 
                                ret_bas_imp , ret_valor, user_cod_user , tipo, fec_emis_ret,  ret_num_ret, estab,  ptoemi ,  auto)
                values ( in_empr, fprv_cod_sucu_o, in_ejer, in_anio, in_mes,  fprv_cod_clpv_o,   fprv_num_fact_o ,   
ret_cta_ret_o,  ret_porc_ret_o,  ret_bas_imp_o , ret_valor_o, in_user, '2', asto_fec, ret_num_ret_inv, fprv_num_seri_o, ptoemi_ret_inv, auto_ret_inv);
end loop;
end loop;





--ATS VENTAS SAEFACT
for clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v

in select character_length(f.fact_ruc_clie) as clpv_con_clpv,  f.fact_ruc_clie, f.fact_aprob_sri, round (( coalesce ((sum (  coalesce(f.fact_con_miva,0)) ),0)),2) as  baseimpgrav,
        round (( coalesce ((sum (  coalesce(f.fact_iva,0 )) ),0)),2)as  montoiva,
        round (( coalesce ((sum (  coalesce(f.fact_sin_miva,0) ) ),0)),2) as  baseimponible,  
count( f.fact_num_preimp ) as factura ,            
        0, 0, 
        round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)+    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
from saefact f where   
f.fact_cod_empr = in_empr and
f.fact_est_fact <> 'AN' and
EXTRACT(YEAR FROM f.fact_fech_fact )  = in_anio and                
EXTRACT(MONTH FROM f.fact_fech_fact )  = in_mes and
f.fact_tip_docu = 'F' and
f.fact_fon_fact in ( select para_fac_cxc   from saepara where	
                            para_cod_empr = in_empr)  and       
(f.fact_tip_vent not in ( '41','99'))
group by f.fact_ruc_clie, f.fact_aprob_sri order by  f.fact_ruc_clie loop

insert into tmp_ats_sis_vent (  empr_cod_empr ,  ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
        fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,montoiva ,
        factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv, tipo, fact_aprob_sri ) 
        values( in_empr,     in_ejer,  in_anio,  in_mes,  1,   clv_con_clpv_v ,  
            clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
            factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'F',  aprobsri_v);


end loop;


-- SQLINES DEMO *** NSUMIDOR FINAL

-- consumidor final
update saepven set pven_con_clpv = '07'  where pven_cod_clie = '78506' and coalesce(pven_con_clpv,'ru') = 'ru';
update saepven set pven_con_clpv = '07'  where pven_ruc_rcuen = '9999999999999'  and coalesce(pven_con_clpv,'ru') = 'ru';

-- ruc
update saepven set pven_con_clpv = '01'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) = 13 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';

-- cedula
update saepven set pven_con_clpv = '02'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) = 10 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';

-- pasaporte
update saepven set pven_con_clpv = '03'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen)< 10 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';
update saepven set pven_con_clpv = '03'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) > 10 and character_length(pven_ruc_rcuen) < 13 and pven_ruc_rcuen <>'9999999999999' and pven_cod_clie <> '78506';

-- CONSUMIDOR FINAL
for clv_con_clpv_v, factura_v, baseimpgrav_v, montoiva_v, baseimponible_v, valoretiva_v, valoretrenta_v, basenograv_v	
in select   p.pven_con_clpv, 
count( p.pven_num_pven ) as factura ,round (( coalesce ((sum (   p.pven_val_civa ) ),0)),2) as  baseimpgrav,
round (( coalesce ((sum (   p.pven_iva_pven ) ),0)),2) as  montoiva,
round (( coalesce ((sum (   p.pven_val_siva ) ),0)),2) as  baseimponible,
(  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr AND                          
                                ASTO_COD_EJER =   in_ejer AND
                                ASTO_NUM_PRDO =  in_mes AND             
                                RETE_RUCI_BENF =   p.pven_ruc_rcuen AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE
                                                            tret_cod_empr = in_empr and                                            
                                                            tret_ban_retf = 'RI' and
                                                            tret_ban_crdb = 'DB' ) ) as valoretiva,
(   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr AND
                                ASTO_COD_EJER =   in_ejer AND                                  
                                ASTO_NUM_PRDO = in_mes  AND 
                                RETE_RUCI_BENF  =   p.pven_ruc_rcuen AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE 
                                                    tret_cod_empr = in_empr and  
                                                    tret_ban_retf = 'IR' and
                                                    tret_ban_crdb = 'DB' )  ) as valoretrenta,
round (( coalesce ((sum (  coalesce( pven_fle_pven,0)    +    coalesce( p.pven_otr_pven,0)  +   coalesce( p.pven_fin_pven,0)  ) ),0)),2) as  basenograv
            from saepven p where
p.pven_cod_empr =  in_empr and
p.pven_est_pven <>  'A' and
EXTRACT(YEAR FROM p.pven_fec_pven ) = in_anio  and
EXTRACT(MONTH FROM p.pven_fec_pven ) = in_mes and
            p.pven_tip_pven = 'F' and
p.pven_con_clpv = '07'
group by  p.pven_con_clpv , 6, 7  order by 1 loop

insert into tmp_ats_sis_vent (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
        clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,  montoiva ,
        factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv ,tipo ) 
        values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  '9999999999999' ,  baseimpgrav_v ,  montoiva_v ,
            factura_v ,  valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v, 'P' );


end loop;


-- SQLINES DEMO *** RUC - PASAPORTE
for clpv_ruc_clpv_v, clv_con_clpv_v, factura_v, baseimpgrav_v, montoiva_v, baseimponible_v, valoretiva_v, valoretrenta_v, basenograv_v	
in select   p.pven_ruc_rcuen , p.pven_con_clpv, 
count( p.pven_num_pven ) as factura ,
round (( coalesce ((sum (   p.pven_val_civa ) ),0)),2) as  baseimpgrav,
round (( coalesce ((sum (   p.pven_iva_pven ) ),0)),2) as montoiva,
round (( coalesce ((sum (   p.pven_val_siva ) ),0)),2) as  baseimponible,
(  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr  AND                
                                ASTO_COD_EJER =   in_ejer  AND
                                ASTO_NUM_PRDO =  in_mes  AND
                                RETE_RUCI_BENF =   p.pven_ruc_rcuen AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE                                                            
                                tret_cod_empr = in_empr and
                                                            tret_ban_retf = 'RI' and         
                                                            tret_ban_crdb = 'DB' )   ) as valoretiva,
            (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr AND
                                ASTO_COD_EJER =   in_ejer AND
                                ASTO_NUM_PRDO = in_mes  AND
                                RETE_RUCI_BENF  =   p.pven_ruc_rcuen AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE                                                 
                                                            tret_cod_empr = in_empr and
                                                            tret_ban_retf = 'IR' and
                                                            tret_ban_crdb = 'DB' )) as valoretrenta,
round (( coalesce ((sum (  coalesce( pven_fle_pven,0)    +    coalesce( p.pven_otr_pven,0)  +   coalesce( p.pven_fin_pven,0)  ) ),0)),2) as  basenograv
            from saepven p where
p.pven_cod_empr =  in_empr and
p.pven_est_pven <>  'A' and
EXTRACT(YEAR FROM p.pven_fec_pven ) =  in_anio and
EXTRACT(MONTH FROM p.pven_fec_pven ) = in_mes  and     p.pven_tip_pven = 'F' and
p.pven_con_clpv <> '07'
group by  p.pven_ruc_rcuen , p.pven_con_clpv , 7, 8  order by 1 loop

insert into tmp_ats_sis_vent (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
        clv_con_clpv ,  clpv_ruc_clpv ,baseimpgrav ,  montoiva ,
        factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv , tipo ) 
        values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  
        baseimpgrav_v ,  montoiva_v ,factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'P' );


end loop;


-- VENTAS SAEFPAK
for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_f, montoiva_v, baseimpgrav_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v	

in select  f.fpak_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, f.fpak_aprob_sri,
    round (( coalesce ((sum (  f.fpak_iva_natu ) ),0)),2) as  montoiva,
    -- SQLINES DEMO *** (  ( f.fpak_iva_natu *100 ) / 12  ) , 0) ),2) as baseimpgrav,
    -- SQLINES DEMO *** ( f.fpak_sub_natu -  ( f.fpak_iva_natu *100 ) / 12  ) , 0)  ),2) as  baseimponible,
    sum(round( ( CASE
            WHEN f.fpak_val_iva > 0 THEN ( f.fpak_sub_natu )
                    ELSE
                    0
        END ) ,2))AS baseimpgrav,
    sum(round( ( CASE
            WHEN f.fpak_val_iva > 0 THEN 0
                    ELSE
                    f.fpak_sub_natu
                    END ) ,2)) AS baseimponible,
                    
    count ( f.fpak_num_fact ) as factura,
    ( SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                    ASTO_COD_EMPR =  in_empr AND
                                    ASTO_COD_EJER =  in_ejer AND
                                ASTO_NUM_PRDO =  in_mes  AND
                                    RET_COD_CLPV =   f.fpak_cod_clpv  AND
                                    ret_cta_ret in ( select tret_cod from saetret WHERE   
                                                                tret_cod_empr = in_empr  and     
                                                                tret_ban_retf = 'RI' and
                                                                tret_ban_crdb = 'DB' )   ) as valoretiva,  

    (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE                       	   
                ASTO_COD_EMPR =  in_empr  AND
            ASTO_COD_EJER =  in_ejer AND
            ASTO_NUM_PRDO =  in_mes  AND
            RET_COD_CLPV =   f.fpak_cod_clpv  AND
            ret_cta_ret in ( select tret_cod from saetret WHERE

                                    tret_cod_empr =in_empr  and 
                                    tret_ban_retf = 'IR' and
                                    tret_ban_crdb = 'DB' )  ) as valoretrenta,
    0 as basenograv
    from saefpak f, saeclpv c where 
c.clpv_cod_clpv = f.fpak_cod_clpv and
c.clpv_cod_empr = in_empr and
c.clpv_clopv_clpv = 'CL' and
f.fpak_cod_empr = in_empr and
f.fpak_cod_tven in ('B', 'D') and
EXTRACT(YEAR FROM f.fpak_fec_pack )  = in_anio and
EXTRACT(MONTH FROM f.fpak_fec_pack )  = in_mes and
f.fpak_cod_efac <> 3
group by f.fpak_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, f.fpak_aprob_sri order by c.clpv_ruc_clpv loop


insert into tmp_ats_sis_flor (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
        clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,  montoiva ,
        factura ,	valoretiva ,  valoretrenta , user_cod_user,baseimponible ,  basenograv, fpak_aprob_sri, fact_cod_clpv  ) 
        values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
            factura_v ,  valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v,  aprobsri_f, fact_cod_clpv_v);


end loop;

-- ATS DEBITO
for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, clpv_par_rela_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v


in select  f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri,
                    round (( coalesce ((sum (  f.fact_con_miva ) ),0)),2) as  baseimpgrav,
                    round (( coalesce ((sum (  f.fact_iva )),0)),2) as  montoiva,
        round (( coalesce ((sum (  f.fact_sin_miva ) ),0)),2)as  baseimponible,
                    count( f.fact_num_preimp ) as factura ,
                    (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr  AND
                                ASTO_COD_EJER =  in_ejer  AND
                                ASTO_NUM_PRDO =  in_mes  AND RET_COD_CLPV =   f.fact_cod_clpv  AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE
                                                            tret_cod_empr = in_empr   and                               
                                                            tret_ban_retf = 'RI' and
                                                            tret_ban_crdb = 'DB' )   ) as valoretiva,
                    (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                ASTO_COD_EMPR =  in_empr  AND
                                ASTO_COD_EJER =  in_ejer  AND                                 
                                ASTO_NUM_PRDO =  in_mes  AND
                                RET_COD_CLPV =   f.fact_cod_clpv  AND
                                ret_cta_ret in ( select tret_cod from saetret WHERE                                   
                                                        tret_cod_empr = in_empr  and                            
                                                        tret_ban_retf = 'IR' and
                                                    tret_ban_crdb = 'DB' )  ) as valoretrenta,
    round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)    +    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
                    from saefact f, saeclpv c where
                    c.clpv_cod_clpv = f.fact_cod_clpv and
                    c.clpv_cod_empr = in_empr   and
                    c.clpv_clopv_clpv = 'CL' and
                    f.fact_cod_empr =  in_empr  and
                    f.fact_est_fact <> 'AN' and
                    f.fact_cod_ejer = in_ejer and
                    f.fact_num_prdo = in_mes  and
        f.fact_fon_fact in(  select max(para_ndb_cxc) as para_ndb_cxc  from saepara where
            para_cod_empr = in_empr)  and
        (  f.fact_tip_vent <> '41' )     
    group by f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri order by c.clpv_ruc_clpv loop 
insert into tmp_ats_sis_deb (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
        fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv , clpv_par_rela,  baseimpgrav ,  montoiva ,
        factura ,	valoretiva ,  valoretrenta , user_cod_user, baseimponible , basenograv, fact_aprob_sri) 
        values( in_empr,     in_ejer,  in_anio,  in_mes,  fact_cod_clpv_v ,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  clpv_par_rela_v,
                baseimpgrav_v ,  montoiva_v , factura_v ,  valoretiva_v ,   valoretrenta_v , in_user , baseimponible_v, basenograv_v,
                aprobsri_v);


end loop;


end if;


msn := 'OK';
return msn;
end 
;
\$BODY\$
LANGUAGE plpgsql VOLATILE
COST 100";
    $oCon->QueryT($sqlalter);

    //SP ATS SEMESTRAL


    $sqlalter = "
            CREATE OR REPLACE FUNCTION \"public\".\"sp_ats_sri_web_v2\"(\"in_empr\" int4, \"in_ejer\" int4, \"in_mes\" int4, \"in_anio\" int4, \"in_user\" int4, \"in_op\" int4, \"in_iva\" int4,\"in_vent\" varchar, \"in_tip_vent\" int4)
            RETURNS \"pg_catalog\".\"varchar\" AS \$BODY\$

    -- ats saefprv
    declare fprv_cod_sucu_o integer;
    fprv_cod_clpv_o integer;
    fprv_cre_fisc_o varchar(20);
    clv_con_clpv_o varchar(20);
    clpv_ruc_clpv_o varchar(20);
    clpv_cod_tprov_o varchar(20);
    clpv_par_rela_o varchar(20);
    fprv_apl_conv_o varchar(20);
    fprv_pag_exte_o varchar(20);
    tran_tip_comp_o varchar(20);
    fprv_fec_emis_o date;
    fprv_fec_emis1_o date;
    estab_o varchar(20);
    ptoemi_o varchar(20);
    fprv_num_fact_o varchar(100);
    fprv_num_auto_o varchar(100);
    basenogralva_o decimal(16,2);
    baseimponible_o decimal(16,2);
    baseimpgrav_o decimal(16,2);
    montoice_o decimal(16,2);
    fprv_val_viva_o decimal(16,2);
    porc1_o decimal(16,2);
    porc2_o decimal(16,2);
    val1_o decimal(16,2);
    val2_o decimal(16,2);
    fprv_cod_tpago_o varchar(20);
    fprv_cod_fpagop_o varchar(20);
    estab_ret_o varchar(20);
    ptoemi_ret_o varchar(20);
    fprv_aut_rete_o varchar(100);
    fprv_fec_rete_o date;
    fprv_num_rete_o varchar(100);
    fprv_cod_tran_o varchar(20);
    fprv_num_seri_o varchar(20);
    fprv_mnt_noi_o varchar(20);
    fprv_val_iva1_o  decimal(16,2);
    valretservicios_o  decimal(16,2);
    valretserv100_o  decimal(16,2);
    fprv_cod_rtf1_o  varchar(20);
    fprv_val_bas1_o decimal(16,2);
    fprv_por_ret1_o decimal(16,2);
    fprv_val_ret1_o decimal(16,2);
    fprv_cod_rtf2_o varchar(20);
    fprv_val_bas2_o decimal(16,2);
    fprv_por_ret2_o decimal(16,2);
    fprv_val_ret2_o decimal(16,2);
    clpv_cod_pais_o integer;
    fprv_auto_sri_o varchar(100);
    fprv_val_noi_o decimal(16,2);
    fprv_val_exe_o decimal(16,2);
    fprv_reg_fis_o varchar(2);
    fprr_tot_imp_o decimal(16,2);

    -- rete fprv
    ret_cta_ret_o varchar(100);
    ret_porc_ret_o decimal(16,2);
    ret_bas_imp_o decimal(16,2);
    ret_valor_o decimal(16,2);
    ret_num_ret_cp varchar(50);

    msn varchar(100);

    -- ats inventario
    sucu_cod_sucu_in integer;
    minv_cod_clpv_in integer;
    clv_con_clpv_in varchar(20);
    clpv_ruc_clpv_in varchar(50);
    clpv_cod_tprov_in varchar(50);
    clpv_par_rela_in varchar(20);
    minv_apl_conv_in varchar(20); 
    minv_pag_exte_in varchar(20);
    minv_cod_tran_in varchar(20);
    tran_tip_comp_in varchar(20);
    minv_fmov_in date;
    estab_in varchar(20);
    ptoemi_in varchar(20);
    minv_fac_prov_in varchar(100);
    fecha_emi_in date;
    minv_aut_usua_in varchar(100);
    valoretbienes10_in decimal(16,2);
    valoretservicios20_in decimal(16,2);
    valoretbienes30_in decimal(16,2);
    valoretservicios70_in decimal(16,2);
    valretserv100_in decimal(16,2);
    minv_iva_valo_in decimal(16,2);
    minv_tot_minv_in decimal(16,2);
    con_iva_in  decimal(16,2);
    sin_iva_in  decimal(16,2);
    minv_val_ice_in  decimal(16,2);
    minv_cod_tpago_in  varchar(20);
    minv_cod_fpagop_in  varchar(20);
    asto_fec date;
    ret_num_ret_inv varchar(50);
    estab_ret_inv varchar(6);
    ptoemi_ret_inv varchar(16);
    auto_ret_inv varchar(255);
    minv_auto_sri_in varchar(100);
    minv_val_noi_in  decimal(16,2);
    minv_val_exe_in  decimal(16,2);
    minv_reg_fis_in varchar(2);
    minv_num_caja_in  varchar(100);
    minv_prec_caja_in  varchar(100);
    minv_ser_docu_in varchar(10);

    -- ats venta
    fact_cod_clpv_v integer;
    clv_con_clpv_v varchar(20);
    clpv_ruc_clpv_v varchar(20);
    baseimpgrav_v decimal(16,2);
    montoiva_v decimal(16,2);
    montoice_v decimal(16,2);
    factura_v varchar(100);
    valoretiva_v decimal(16,2);
    valoretrenta_v decimal(16,2);
    baseimponible_v decimal(16,2);
    basenograv_v decimal(16,2);
    clpv_par_rela_v varchar(20);
    aprobsri_v varchar(20);


    -- ats debito
    fact_cod_clpv_d integer;
    clv_con_clpv_d varchar(20);
    clpv_ruc_clpv_d varchar(20);
    baseimpgrav_d decimal(16,2);
    montoiva_d decimal(16,2);
    factura_d varchar(100);
    valoretiva_d decimal(16,2);
    valoretrenta_d decimal(16,2);
    baseimponible_d decimal(16,2);
    aprobsri_d varchar(20);
    aprobsri_f varchar(20);
    begin
    if in_op > 0 and in_op < 2 then		
    for fprv_cod_sucu_o, fprv_cre_fisc_o, clv_con_clpv_o, fprv_cod_clpv_o, clpv_ruc_clpv_o, clpv_cod_tprov_o, clpv_par_rela_o, fprv_apl_conv_o, fprv_pag_exte_o, fprv_cod_tran_o, tran_tip_comp_o, fprv_fec_emis_o, estab_o, ptoemi_o, fprv_num_seri_o, fprv_num_fact_o, fprv_fec_emis1_o, fprv_num_auto_o, fprv_mnt_noi_o, basenogralva_o, baseimponible_o, baseimpgrav_o, montoice_o, fprv_val_viva_o, fprv_val_iva1_o, valretservicios_o, valretserv100_o, porc1_o, porc2_o, val1_o, val2_o, fprv_cod_tpago_o, fprv_cod_fpagop_o, fprv_cod_rtf1_o, fprv_val_bas1_o, fprv_por_ret1_o, fprv_val_ret1_o, fprv_cod_rtf2_o, fprv_val_bas2_o, fprv_por_ret2_o, fprv_val_ret2_o, estab_ret_o, ptoemi_ret_o, fprv_aut_rete_o, fprv_fec_rete_o, fprv_num_rete_o, clpv_cod_pais_o, fprv_auto_sri_o, fprv_val_noi_o, fprv_val_exe_o, fprv_reg_fis_o, fprr_tot_imp_o
        -- compras ats saefprv
        in SELECT F.FPRV_COD_SUCU, F.FPRV_CRE_FISC,  C.CLV_CON_CLPV,  F.FPRV_COD_CLPV,
                            F.FPRV_RUC_PROV AS CLPV_RUC_CLPV,  C.clpv_cod_tprov ,  C.clpv_par_rela,   
                            F.fprv_apl_conv, F.fprv_pag_exte , F.FPRV_COD_TRAN,
                            (  select trans_tip_comp from saetran where
                                    tran_cod_empr = in_empr  and
                                    tran_cod_modu = 4 and
                                    tran_cod_tran =   F.FPRV_COD_TRAN  GROUP BY 1 ) AS TRAN_TIP_COMP,  
                            F.FPRV_FEC_EMIS,

                            ( substring( F.FPRV_NUM_SERI from 1 for 3 ) ) AS ESTAB,
                            ( substring( F.FPRV_NUM_SERI from 4 for 6 ) ) AS PTOEMI,

                            F.FPRV_NUM_SERI,   F.FPRV_NUM_FACT, F.FPRV_FEC_EMIS,
                            F.FPRV_NUM_AUTO,   F.FPRV_MNT_NOI,
                            round( ( CASE
                            WHEN F.FPRV_MNT_NOI = 'S' THEN ( coalesce(F.FPRV_VAL_GRA0,0) + coalesce(F.FPRV_VAL_GR0S,0) )
                            ELSE
                            0
                            END ) ,2) AS BASENOGRALVA, 

                            round( ( CASE
                            WHEN F.FPRV_MNT_NOI = 'S'      THEN 0
                            ELSE
                                ( coalesce(F.FPRV_VAL_GRA0,0) + coalesce( F.FPRV_VAL_GR0S,0 ) )
                            END ) ,2) AS BASEIMPONIBLE,

                            round(( coalesce( ( coalesce( F.fprv_val_grbs,0) + coalesce( F.fprv_val_grab,0) ),0)  ),2)  AS BASEIMPGRAV,
                            round( ( coalesce( ( F.FPRV_VAL_VICE ),0) ),2)  AS MONTOICE,
                            round( ( coalesce(F.FPRV_VAL_VIVA,0)),2) as FPRV_VAL_VIVA,
                            round( ( coalesce( ( coalesce( F.FPRV_VAL_IVA1,0) ),0)),2)  as FPRV_VAL_IVA1,

                            round ( ( CASE
                                        WHEN F.FPRV_POR_IVA2= 70
                                    THEN ( F.FPRV_VAL_IVA2 )
                                        ELSE                          0
                                    END ) ,2)  AS VALRETSERVICIOS,

                            round ( ( CASE
                                        WHEN ( ( COALESCE(F.FPRV_POR_IVA2,0)  =  100 )  or ( COALESCE(F.fprv_por_iva1,0) = 100 ) )                                   THEN (  COALESCE( F.FPRV_VAL_IVA2,0)  +   COALESCE( F.FPRV_VAL_IVA1 ,0) )
                                        ELSE
                                        0
                                        END ),2)  AS VALRETSERV100,   round((COALESCE(F.FPRV_POR_IVA1,0)),2) AS porc1,
                            round((COALESCE(F.FPRV_POR_IVA2,0)),2) AS porc2,
                            round((COALESCE(F.FPRV_VAL_IVA1,0)),2) AS val1,
                            round((COALESCE(F.FPRV_VAL_IVA2,0)),2) AS val2,
                            C.CLPV_COD_TPAGO,
                            C.CLPV_COD_FPAGOP,       F.fprv_cod_rtf1,
                            F.fprv_val_bas1,
                            F.fprv_por_ret1,
                            F.fprv_val_ret1,

                            F.fprv_cod_rtf2,
                            F.fprv_val_bas2,    F.fprv_por_ret2,
                            F.fprv_val_ret2,

                            ( substring( F.fprv_ser_rete from 1 for 3 ) ) AS ESTAB_RET,
                            ( substring( F.fprv_ser_rete from 4 for 6 ) ) AS PTOEMI_RET,     fprv_aut_rete,   fprv_fec_rete, fprv_num_rete, c.clpv_cod_paisp, F.FPRV_AUTO_SRI,
                            F.fprv_val_noi, F.fprv_val_exe, F.fprv_reg_fis,
                            (select sum(round((coalesce(fr.fprr_val_grab, 0) + coalesce(fr.fprr_val_gras,0)) + (coalesce(fr.fprr_val_gra0 , 0) + coalesce(fr.fprr_val_grs0,0)),2)) as fprr_tot_imp from saefprr fr where fr.fprr_fac_fprv like CONCAT('%',F.FPRV_NUM_FACT,'%') and
                                                                                fr.fprr_cod_empr = in_empr and 
                                                                                fr.fprr_cod_ejer = in_ejer and
                                                                                fr.fprr_clpv_fprv = F.FPRV_COD_CLPV and
                                                                                fr.fprr_cod_empr = F.FPRV_COD_EMPR and
                                                                                fr.fprr_cod_ejer = F.FPRV_COD_EJER) AS FPRR_TOT_IMP
                                                FROM SAEFPRV F, SAECLPV C WHERE
                            C.CLPV_COD_CLPV = F.FPRV_COD_CLPV AND
                            C.CLPV_COD_EMPR = in_empr  AND
                            F.FPRV_COD_EMPR = in_empr  AND       F.FPRV_COD_EJER = in_ejer AND
                            EXTRACT(MONTH FROM F.FPRV_FEC_EMIS) = in_mes loop

        insert into tmp_ats_sis (  empr_cod_empr , sucu_cod_sucu, ejer_cod_ejer ,  anio_id ,  prdo_num_prdo , fprv_cod_clpv ,  fprv_cre_fisc , clv_con_clpv , clpv_ruc_clpv,
                tran_tip_comp , fprv_fec_emis , 	estab ,   ptoemi ,  fprv_num_fact ,  fprv_num_auto ,  basenogralva ,  baseimponible ,  baseimpgrav ,
                montoice ,  fprv_val_viva , porc1 , porc2 ,	val1 ,  val2 , fprv_cod_tpago ,	fprv_cod_fpagop , estab_ret ,	ptoemi_ret ,  fprv_aut_rete ,
                fprv_fec_rete , fprv_num_rete , user_cod_user, fprv_cod_rtf1 , fprv_cod_rtf2  , clpv_cod_pais, clpv_cod_tprov, clpv_par_rela,  fprv_apl_conv, fprv_pag_exte, fprv_auto_sri, 
                fprv_val_noi, fprv_val_exe, fprv_reg_fis, fprr_tot_imp, fprv_num_seri) 
                values( in_empr,  fprv_cod_sucu_o,  in_ejer,   in_anio, in_mes,      fprv_cod_clpv_o,    fprv_cre_fisc_o ,  clv_con_clpv_o ,   clpv_ruc_clpv_o ,
                    tran_tip_comp_o ,   fprv_fec_emis_o,  estab_o ,  ptoemi_o ,    fprv_num_fact_o ,     fprv_num_auto_o ,  basenogralva_o ,  baseimponible_o ,  baseimpgrav_o ,
                    montoice_o ,       fprv_val_viva_o ,        porc1_o ,  porc2_o ,  val1_o , val2_o ,    fprv_cod_tpago_o ,   fprv_cod_fpagop_o , estab_ret_o ,  ptoemi_ret_o , fprv_aut_rete_o,
                    fprv_fec_rete_o , fprv_num_rete_o ,  in_user  ,     fprv_cod_rtf1_o  , fprv_cod_rtf2_o, clpv_cod_pais_o, clpv_cod_tprov_o, clpv_par_rela_o  , fprv_apl_conv_o , fprv_pag_exte_o, fprv_auto_sri_o,
                    fprv_val_noi_o, fprv_val_exe_o, fprv_reg_fis_o, fprr_tot_imp_o, fprv_num_seri_o);
    end loop;

    -- SQLINES DEMO *** PRAS
    for fprv_cod_clpv_o, fprv_cod_sucu_o, fprv_num_fact_o, clpv_ruc_clpv_o, fprv_num_seri_o
        in select fprv_cod_clpv, sucu_cod_sucu,  fprv_num_fact,  clpv_ruc_clpv, fprv_num_seri
                                from tmp_ats_sis where    empr_cod_empr = in_empr and
                                ejer_cod_ejer = in_ejer and prdo_num_prdo = in_mes and
                                user_cod_user = in_user loop
        for ret_cta_ret_o, ret_porc_ret_o, ret_bas_imp_o, ret_valor_o, ret_num_ret_cp 
        
                    in SELECT  R.RET_CTA_RET,   round(R.RET_PORC_RET,2) as ret_porc_ret  , 
                        round( R.RET_BAS_IMP,2) as ret_bas_imp,
                        round( R.RET_VALOR,2) as ret_valor , R.ret_num_ret
                        FROM SAERET R, SAEASTO A, SAEFPRV FP WHERE   A.ASTO_COD_ASTO = R.RETE_COD_ASTO AND               
    FP.FPRV_COD_ASTO = R.RETE_COD_ASTO AND
                    FP.FPRV_COD_ASTO = R.RETE_COD_ASTO AND
                    FP.FPRV_COD_EMPR = R.ASTO_COD_EMPR AND
                    FP.FPRV_COD_SUCU = R.ASTO_COD_SUCU AND
                    FP.FPRV_COD_EJER = R.ASTO_COD_EJER AND
                    FP.FPRV_NUM_FACT = R.RET_NUM_FACT AND
                    FP.FPRV_COD_CLPV = R.RET_COD_CLPV AND  
                            A.ASTO_COD_EMPR = in_empr AND
                            A.ASTO_COD_EJER = in_ejer AND
                            A.ASTO_NUM_PRDO = in_mes AND
                            A.ASTO_COD_SUCU = fprv_cod_sucu_o  AND
                            A.ASTO_EST_ASTO <> 'AN' AND
                            R.RET_CRE_ML >= 0  AND		R.ASTO_COD_EMPR =  in_empr AND      
                            R.ASTO_COD_EJER =  in_ejer AND
                            R.ASTO_NUM_PRDO =  in_mes AND
                            R.RET_COD_CLPV =   fprv_cod_clpv_o  AND            
                            R.RET_NUM_FACT =  fprv_num_fact_o  AND
                            FP.FPRV_NUM_SERI = fprv_num_seri_o AND
                            R.ret_cta_ret in ( select tret_cod from saetret WHERE
                                                        tret_cod_empr =  in_empr and             
                                                        tret_ban_retf in ('IR','RI') and
                                                        tret_ban_crdb = 'CR') loop
            

                    insert into tmp_ats_sis_ret ( empr_cod_empr , sucu_cod_sucu , ejer_cod_ejer , anio_id , prdo_num_prdo , 
                                                ret_cod_clpv ,	ret_num_fact , ret_cta_ret , ret_porc_ret , 
                                                ret_bas_imp , ret_valor, user_cod_user , tipo,  ret_num_ret, estab, ptoemi )
                                values ( in_empr, fprv_cod_sucu_o,  in_ejer, in_anio, in_mes,  fprv_cod_clpv_o,   fprv_num_fact_o ,   
                                        ret_cta_ret_o,  ret_porc_ret_o,  ret_bas_imp_o , ret_valor_o, in_user, '1' , ret_num_ret_cp,
                                        fprv_num_seri_o, ptoemi_o);
        end loop;
    end loop;

    -- SQLINES DEMO *** TARIO
    for sucu_cod_sucu_in, minv_cod_clpv_in, clv_con_clpv_in, clpv_ruc_clpv_in, clpv_cod_tprov_in, clpv_par_rela_in, minv_apl_conv_in, minv_pag_exte_in, minv_cod_tran_in, tran_tip_comp_in, minv_fmov_in, estab_in, ptoemi_in, minv_fac_prov_in, minv_ser_docu_in, fecha_emi_in, minv_aut_usua_in, valoretbienes10_in, valoretservicios20_in, valoretbienes30_in, valoretservicios70_in, valretserv100_in, minv_iva_valo_in, minv_tot_minv_in, con_iva_in, sin_iva_in, minv_val_ice_in, minv_cod_tpago_in, minv_cod_fpagop_in, clpv_cod_pais_o, minv_auto_sri_in, minv_val_noi_in, minv_val_exe_in, minv_reg_fis_in, minv_num_caja_in, minv_prec_caja_in

        in SELECT M.minv_cod_sucu, M.minv_cod_clpv, C.clv_con_clpv, C.clpv_ruc_clpv, C.clpv_cod_tprov,  
                        C.clpv_par_rela,   M.minv_apl_conv, m.minv_pag_exte, M.MINV_COD_TRAN ,
                        (  select DEFI_TIP_COMP from saedefi where  defi_cod_empr = in_empr and
                                defi_cod_modu = 10 and defi_cod_tran = M.MINV_COD_TRAN  GROUP BY 1 ) AS TRAN_TIP_COMP,
                        M.minv_fmov,
                        ( substring( M.minv_ser_docu  from 1 for 3 ) ) AS ESTAB,
                        ( substring( M.minv_ser_docu from 4 for 6 ) ) AS PTOEMI,                  
                        M.minv_fac_prov,  M.minv_ser_docu, ( M.minv_fmov ) as fecha_emi,  M.minv_aut_usua,
                        
                        (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                                asto_cod_empr = in_empr and    
                                asto_cod_ejer = in_ejer and
                                asto_num_prdo = in_mes and
                                ret_porc_ret = '10' and
                                -- SQLINES DEMO *** ' and               ret_cod_clpv =  M.MINV_COD_CLPV and                            
                                ret_num_fact =  M.minv_fac_prov and
                                rete_cod_asto = M.minv_comp_cont) As VALORETBIENES10,
                                
                        (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                                asto_cod_empr = in_empr and    
                                asto_cod_ejer = in_ejer and
                                asto_num_prdo = in_mes and
                                ret_porc_ret = '20' and
                                -- SQLINES DEMO *** ' and
                                ret_cod_clpv =  M.MINV_COD_CLPV and                            
                                ret_num_fact =  M.minv_fac_prov  and
                                rete_cod_asto = M.minv_comp_cont) As VALORETSERVICIOS20,
                                
                        (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                                asto_cod_empr = in_empr and    
                                asto_cod_ejer = in_ejer and
                                asto_num_prdo = in_mes and
                                ret_porc_ret = '30' and             -- SQLINES DEMO *** ' and
                                ret_cod_clpv =  M.MINV_COD_CLPV and                            
                                ret_num_fact =  M.minv_fac_prov  and
                                rete_cod_asto = M.minv_comp_cont) As VALORETBIENES30,      (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                                asto_cod_empr = in_empr and           
                                asto_cod_ejer = in_ejer and    
                                asto_num_prdo =  in_mes and
                                ret_porc_ret = '70' and
                                -- SQLINES DEMO *** ' and
                                ret_cod_clpv =  M.MINV_COD_CLPV and
                                ret_num_fact=  M.minv_fac_prov  and
                                rete_cod_asto = M.minv_comp_cont) AS  VALORETSERVICIOS70,						
                        (  select  round((coalesce(sum(ret_valor),0)),2) as ret_valor from saeret where
                                asto_cod_empr =  in_empr and
                                asto_cod_ejer =  in_ejer and
                                asto_num_prdo =  in_mes and   
                                ret_porc_ret = '100' and
                                -- SQLINES DEMO *** 25'  and
                                ret_cod_clpv  =  M.MINV_COD_CLPV and
                                ret_num_fact  =  M.minv_fac_prov  and
                                rete_cod_asto = M.minv_comp_cont) AS VALRETSERV100,

                        round(coalesce(M.minv_iva_valo,0),2) as minv_iva_valo,
                        round(( M.minv_tot_minv - coalesce(M.minv_dge_valo,0) + coalesce( M.minv_otr_valo,0 ) + coalesce( M.minv_fle_minv,0)  ),2)  as minv_tot_minv,  
                        
                        round(( select sum(dmov_cto_dmov) as base_grava 
									from  saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc > 0 and
									dmov_num_comp = M.minv_num_comp	 ),2) as con_iva ,
                        round(( select COALESCE(sum(dmov_cto_dmov), '0') as base_nograva from saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc = 0 and
									dmov_num_comp = M.minv_num_comp ) ,2 ) as sin_iva ,
                        round(  coalesce( M.minv_val_ice,0),2  ) AS minv_val_ice,
                        C.clpv_cod_tpago,
                        C.clpv_cod_fpagop, C.clpv_cod_paisp, M.minv_auto_sri, M.minv_val_noi, M.minv_val_exe, M.minv_reg_fis, M.minv_num_caja, M.minv_prec_caja
                                        FROM SAEMINV M, SAECLPV C  WHERE
                        C.CLPV_COD_CLPV = M.MINV_COD_CLPV AND
                        M.MINV_COD_EMPR =  in_empr AND
                        M.MINV_COD_EJER = in_ejer  AND
                        M.MINV_NUM_PRDO = in_mes AND              
                        M.MINV_COD_TRAN IN  ( SELECT D.DEFI_COD_TRAN   FROM SAEDEFI D, SAETRAN T WHERE
                                                        T.TRAN_COD_TRAN = D.DEFI_COD_TRAN AND              	
                                                        D.DEFI_COD_MODU = 10 AND                                           		 	D.DEFI_COD_EMPR = in_empr AND                                    	
                                                        D.DEFI_TIP_DEFI = '0' AND
                                                        D.DEFI_TIP_COMP in( '01'  , '02', '03')  AND                               T.TRAN_COD_EMPR = in_empr ) AND
                        M.MINV_COD_EJER =  in_ejer  AND
                        M.MINV_NUM_PRDO = in_mes AND
                        M.MINV_EST_MINV <> '0' loop	
            insert into tmp_ats_sis_inv (  empr_cod_empr  ,sucu_cod_sucu , 	ejer_cod_ejer ,  id_anio ,  prdo_num_prdo ,   minv_cod_clpv ,  
                                        clv_con_clpv ,  clpv_ruc_clpv ,	minv_cod_tran ,  tran_tip_comp ,  minv_fmov ,  estab ,  	
                                        ptoemi ,  	minv_fac_prov ,  minv_ser_docu,  fecha_emi , 	minv_aut_usua ,	valoretbienes10 , 	valoretservicios20,
                                        valoretbienes30 , 	valoretservicios70 , 	
                                        valretserv100 ,    minv_iva_valo ,   minv_tot_minv ,   con_iva ,	sin_iva ,	minv_val_ice ,  
                                        minv_cod_tpago ,   minv_cod_fpagop ,user_cod_user , clpv_cod_pais, clpv_cod_tprov, 
                                        clpv_par_rela  , minv_apl_conv, minv_pag_exte, minv_auto_sri, minv_val_noi, minv_val_exe, minv_reg_fis, minv_num_caja, minv_prec_caja) 
                values( in_empr,   sucu_cod_sucu_in,  in_ejer, in_anio,  in_mes,  minv_cod_clpv_in ,  clv_con_clpv_in ,  clpv_ruc_clpv_in ,  
                    minv_cod_tran_in ,   tran_tip_comp_in ,   minv_fmov_in , estab_in ,  ptoemi_in ,  minv_fac_prov_in ,  minv_ser_docu_in, fecha_emi_in ,   
                    minv_aut_usua_in ,   valoretbienes10_in ,   valoretservicios20_in, valoretbienes30_in ,   valoretservicios70_in , valretserv100_in , minv_iva_valo_in ,  
                    minv_tot_minv_in ,  con_iva_in  ,  sin_iva_in  ,   minv_val_ice_in  ,  minv_cod_tpago_in  ,  minv_cod_fpagop_in  , 
                    in_user, clpv_cod_pais_o , clpv_cod_tprov_in, clpv_par_rela_in , minv_apl_conv_in, minv_pag_exte_in, minv_auto_sri_in,
                    minv_val_noi_in, minv_val_exe_in, minv_reg_fis_in, minv_num_caja_in, minv_prec_caja_in);

    end loop;

    ptoemi_ret_inv := '';

    -- SQLINES DEMO *** RIO
    for fprv_cod_clpv_o, fprv_cod_sucu_o, fprv_num_fact_o, clpv_ruc_clpv_o, fprv_num_seri_o
        in select minv_cod_clpv, sucu_cod_sucu,  minv_fac_prov  ,  clpv_ruc_clpv, minv_ser_docu
                    from tmp_ats_sis_inv where    
                    empr_cod_empr = in_empr and
                    ejer_cod_ejer = in_ejer and	prdo_num_prdo = in_mes and
                    user_cod_user = in_user loop
        for ret_cta_ret_o, auto_ret_inv, estab_ret_inv, ptoemi_ret_inv, ret_porc_ret_o, ret_bas_imp_o, ret_valor_o, asto_fec, ret_num_ret_inv 
            in SELECT  R.RET_CTA_RET, R.RET_AUT_RET, 
            ( R.RET_SER_RET  ) AS estab_ret,
            ( R.RET_SER_RET  ) AS  ptoemi_ret,
            round(R.RET_PORC_RET,2) as ret_porc_ret, 
                        round( R.RET_BAS_IMP,2) as ret_bas_imp,
                        round( R.RET_VALOR,2) as ret_valor , A.ASTO_FEC_ASTO, R.ret_num_ret
                                        FROM SAERET R, SAEASTO A, SAEMINV M 
                        WHERE   A.ASTO_COD_ASTO = R.RETE_COD_ASTO AND    
                        M.MINV_COMP_CONT = R.RETE_COD_ASTO AND
                        M.MINV_COD_EMPR = R.ASTO_COD_EMPR AND
                        M.MINV_COD_SUCU = R.ASTO_COD_SUCU AND
                        M.MINV_COD_EJER = R.ASTO_COD_EJER AND
                        M.MINV_FAC_PROV = R.RET_NUM_FACT AND
                        M.MINV_COD_CLPV = R.RET_COD_CLPV AND  
                        A.ASTO_COD_EMPR = in_empr AND
                        A.ASTO_COD_EJER = in_ejer AND
                        A.ASTO_NUM_PRDO = in_mes AND
                        A.ASTO_COD_SUCU = fprv_cod_sucu_o  AND
                        A.ASTO_EST_ASTO <> 'AN' AND
                        R.RET_CRE_ML >= 0  AND      
                        R.ASTO_COD_EMPR =  in_empr AND      
                        R.ASTO_COD_EJER =  in_ejer AND
                        R.ASTO_NUM_PRDO =  in_mes AND
                        R.RET_COD_CLPV =   fprv_cod_clpv_o  AND            
                        R.RET_NUM_FACT =  fprv_num_fact_o  AND
                        M.MINV_SER_DOCU = fprv_num_seri_o AND
                        R.ret_cta_ret in ( select tret_cod from saetret WHERE
                                                    tret_cod_empr =  in_empr and             
                                                    tret_ban_retf in ('IR','RI') and
                                                    tret_ban_crdb = 'CR') loop
            insert into tmp_ats_sis_ret ( empr_cod_empr , sucu_cod_sucu , ejer_cod_ejer , anio_id , prdo_num_prdo , 
                                        ret_cod_clpv ,	ret_num_fact , ret_cta_ret , ret_porc_ret , 
                                        ret_bas_imp , ret_valor, user_cod_user , tipo, fec_emis_ret,  ret_num_ret, estab,  ptoemi ,  auto)
                        values ( in_empr, fprv_cod_sucu_o, in_ejer, in_anio, in_mes,  fprv_cod_clpv_o,   fprv_num_fact_o ,   
        ret_cta_ret_o,  ret_porc_ret_o,  ret_bas_imp_o , ret_valor_o, in_user, '2', asto_fec, ret_num_ret_inv, fprv_num_seri_o, ptoemi_ret_inv, auto_ret_inv);
        end loop;
    end loop;





    --ATS VENTAS SAEFACT

    --VALIDACION ATS SEMESTRAL

IF in_tip_vent = 1 and in_vent='S' then

    for clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v

    in select character_length(f.fact_ruc_clie) as clpv_con_clpv,  f.fact_ruc_clie, f.fact_aprob_sri, round (( coalesce ((sum (  coalesce(f.fact_con_miva,0)) ),0)),2) as  baseimpgrav,
                round (( coalesce ((sum (  coalesce(f.fact_iva,0 )) ),0)),2)as  montoiva,
                round (( coalesce ((sum (  coalesce(f.fact_sin_miva,0) ) ),0)),2) as  baseimponible,  
    count( f.fact_num_preimp ) as factura ,            
                0, 0, 
                round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)+    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
    from saefact f where   
    f.fact_cod_empr = in_empr and
    f.fact_est_fact <> 'AN' and
    EXTRACT(YEAR FROM f.fact_fech_fact )  = in_anio and                
    EXTRACT(MONTH FROM f.fact_fech_fact )  in (1,2,3,4,5,6) and
    f.fact_tip_docu = 'F' and
    f.fact_fon_fact in ( select para_fac_cxc   from saepara where	
                                    para_cod_empr = in_empr)  and       
    (f.fact_tip_vent not in ('41','99'))
    group by f.fact_ruc_clie, f.fact_aprob_sri order by  f.fact_ruc_clie loop

    insert into tmp_ats_sis_vent (  empr_cod_empr ,  ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv, tipo, fact_aprob_sri ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  1,   clv_con_clpv_v ,  
                    clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
                    factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'F',  aprobsri_v);

    end loop;

    elsif in_tip_vent =2 and in_vent='S' then

    for clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v

    in select character_length(f.fact_ruc_clie) as clpv_con_clpv,  f.fact_ruc_clie, f.fact_aprob_sri, round (( coalesce ((sum (  coalesce(f.fact_con_miva,0)) ),0)),2) as  baseimpgrav,
                round (( coalesce ((sum (  coalesce(f.fact_iva,0 )) ),0)),2)as  montoiva,
                round (( coalesce ((sum (  coalesce(f.fact_sin_miva,0) ) ),0)),2) as  baseimponible,  
    count( f.fact_num_preimp ) as factura ,            
                0, 0, 
                round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)+    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
    from saefact f where   
    f.fact_cod_empr = in_empr and
    f.fact_est_fact <> 'AN' and
    EXTRACT(YEAR FROM f.fact_fech_fact )  = in_anio and                
    EXTRACT(MONTH FROM f.fact_fech_fact )  in (7,8,9,10,11,12) and
    f.fact_tip_docu = 'F' and
    f.fact_fon_fact in ( select para_fac_cxc   from saepara where	
                                    para_cod_empr = in_empr)  and       
    (f.fact_tip_vent not in ('41','99'))
    group by f.fact_ruc_clie, f.fact_aprob_sri order by  f.fact_ruc_clie loop

    insert into tmp_ats_sis_vent (  empr_cod_empr ,  ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv, tipo, fact_aprob_sri ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  1,   clv_con_clpv_v ,  
                    clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
                    factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'F',  aprobsri_v);

    end loop;

    elsif in_tip_vent =0 then

    for clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v

    in select character_length(f.fact_ruc_clie) as clpv_con_clpv,  f.fact_ruc_clie, f.fact_aprob_sri, round (( coalesce ((sum (  coalesce(f.fact_con_miva,0)) ),0)),2) as  baseimpgrav,
                round (( coalesce ((sum (  coalesce(f.fact_iva,0 )) ),0)),2)as  montoiva,
                round (( coalesce ((sum (  coalesce(f.fact_sin_miva,0) ) ),0)),2) as  baseimponible,  
    count( f.fact_num_preimp ) as factura ,            
                0, 0, 
                round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)+    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
    from saefact f where   
    f.fact_cod_empr = in_empr and
    f.fact_est_fact <> 'AN' and
    EXTRACT(YEAR FROM f.fact_fech_fact )  = in_anio and                
    EXTRACT(MONTH FROM f.fact_fech_fact )  = in_mes and
    f.fact_tip_docu = 'F' and
    f.fact_fon_fact in ( select para_fac_cxc   from saepara where	
                                    para_cod_empr = in_empr)  and       
    (f.fact_tip_vent not in ('41','99'))
    group by f.fact_ruc_clie, f.fact_aprob_sri order by  f.fact_ruc_clie loop

    insert into tmp_ats_sis_vent (  empr_cod_empr ,  ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv, tipo, fact_aprob_sri ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  1,   clv_con_clpv_v ,  
                    clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
                    factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'F',  aprobsri_v);

    end loop;
    
end if;


    -- SQLINES DEMO *** NSUMIDOR FINAL

    -- consumidor final
    update saepven set pven_con_clpv = '07'  where pven_cod_clie = '78506' and coalesce(pven_con_clpv,'ru') = 'ru';
    update saepven set pven_con_clpv = '07'  where pven_ruc_rcuen = '9999999999999'  and coalesce(pven_con_clpv,'ru') = 'ru';

    -- ruc
    update saepven set pven_con_clpv = '01'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) = 13 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';

    -- cedula
    update saepven set pven_con_clpv = '02'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) = 10 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';

    -- pasaporte
    update saepven set pven_con_clpv = '03'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen)< 10 and pven_ruc_rcuen <> '9999999999999' and pven_cod_clie <> '78506';
    update saepven set pven_con_clpv = '03'  where coalesce(pven_con_clpv,'ru') = 'ru' and character_length(pven_ruc_rcuen) > 10 and character_length(pven_ruc_rcuen) < 13 and pven_ruc_rcuen <>'9999999999999' and pven_cod_clie <> '78506';

    -- CONSUMIDOR FINAL
    for clv_con_clpv_v, factura_v, baseimpgrav_v, montoiva_v, baseimponible_v, valoretiva_v, valoretrenta_v, basenograv_v	
        in select   p.pven_con_clpv, 
        count( p.pven_num_pven ) as factura ,round (( coalesce ((sum (   p.pven_val_civa ) ),0)),2) as  baseimpgrav,
        round (( coalesce ((sum (   p.pven_iva_pven ) ),0)),2) as  montoiva,
        round (( coalesce ((sum (   p.pven_val_siva ) ),0)),2) as  baseimponible,
        (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr AND                          
                                        ASTO_COD_EJER =   in_ejer AND
                                        ASTO_NUM_PRDO =  in_mes AND             
                                        RETE_RUCI_BENF =   p.pven_ruc_rcuen AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE
                                                                    tret_cod_empr = in_empr and                                            
                                                                    tret_ban_retf = 'RI' and
                                                                    tret_ban_crdb = 'DB' ) ) as valoretiva,
        (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr AND
                                        ASTO_COD_EJER =   in_ejer AND                                  
                                        ASTO_NUM_PRDO = in_mes  AND 
                                        RETE_RUCI_BENF  =   p.pven_ruc_rcuen AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE 
                                                            tret_cod_empr = in_empr and  
                                                            tret_ban_retf = 'IR' and
                                                            tret_ban_crdb = 'DB' )  ) as valoretrenta,
        round (( coalesce ((sum (  coalesce( pven_fle_pven,0)    +    coalesce( p.pven_otr_pven,0)  +   coalesce( p.pven_fin_pven,0)  ) ),0)),2) as  basenograv
                    from saepven p where
        p.pven_cod_empr =  in_empr and
        p.pven_est_pven <>  'A' and
        EXTRACT(YEAR FROM p.pven_fec_pven ) = in_anio  and
        EXTRACT(MONTH FROM p.pven_fec_pven ) = in_mes and
                    p.pven_tip_pven = 'F' and
        p.pven_con_clpv = '07'
        group by  p.pven_con_clpv , 6, 7  order by 1 loop

        insert into tmp_ats_sis_vent (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv ,tipo ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  '9999999999999' ,  baseimpgrav_v ,  montoiva_v ,
                    factura_v ,  valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v, 'P' );


    end loop;


    -- SQLINES DEMO *** RUC - PASAPORTE
    for clpv_ruc_clpv_v, clv_con_clpv_v, factura_v, baseimpgrav_v, montoiva_v, baseimponible_v, valoretiva_v, valoretrenta_v, basenograv_v	
        in select   p.pven_ruc_rcuen , p.pven_con_clpv, 
        count( p.pven_num_pven ) as factura ,
        round (( coalesce ((sum (   p.pven_val_civa ) ),0)),2) as  baseimpgrav,
        round (( coalesce ((sum (   p.pven_iva_pven ) ),0)),2) as montoiva,
        round (( coalesce ((sum (   p.pven_val_siva ) ),0)),2) as  baseimponible,
        (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND                
                                        ASTO_COD_EJER =   in_ejer  AND
                                        ASTO_NUM_PRDO =  in_mes  AND
                                        RETE_RUCI_BENF =   p.pven_ruc_rcuen AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE                                                            
                                        tret_cod_empr = in_empr and
                                                                    tret_ban_retf = 'RI' and         
                                                                    tret_ban_crdb = 'DB' )   ) as valoretiva,
                    (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr AND
                                        ASTO_COD_EJER =   in_ejer AND
                                        ASTO_NUM_PRDO = in_mes  AND
                                        RETE_RUCI_BENF  =   p.pven_ruc_rcuen AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE                                                 
                                                                    tret_cod_empr = in_empr and
                                                                    tret_ban_retf = 'IR' and
                                                                    tret_ban_crdb = 'DB' )) as valoretrenta,
        round (( coalesce ((sum (  coalesce( pven_fle_pven,0)    +    coalesce( p.pven_otr_pven,0)  +   coalesce( p.pven_fin_pven,0)  ) ),0)),2) as  basenograv
                    from saepven p where
        p.pven_cod_empr =  in_empr and
        p.pven_est_pven <>  'A' and
        EXTRACT(YEAR FROM p.pven_fec_pven ) =  in_anio and
        EXTRACT(MONTH FROM p.pven_fec_pven ) = in_mes  and     p.pven_tip_pven = 'F' and
        p.pven_con_clpv <> '07'
        group by  p.pven_ruc_rcuen , p.pven_con_clpv , 7, 8  order by 1 loop

        insert into tmp_ats_sis_vent (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                clv_con_clpv ,  clpv_ruc_clpv ,baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,  baseimponible ,  basenograv , tipo ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  
                baseimpgrav_v ,  montoiva_v ,factura_v , valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v , 'P' );


    end loop;


    -- VENTAS SAEFPAK
    for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, aprobsri_f, montoiva_v, baseimpgrav_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v	

        in select  f.fpak_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, f.fpak_aprob_sri,
            round (( coalesce ((sum (  f.fpak_iva_natu ) ),0)),2) as  montoiva,
            -- SQLINES DEMO *** (  ( f.fpak_iva_natu *100 ) / 12  ) , 0) ),2) as baseimpgrav,
            -- SQLINES DEMO *** ( f.fpak_sub_natu -  ( f.fpak_iva_natu *100 ) / 12  ) , 0)  ),2) as  baseimponible,
            sum(round( ( CASE
                    WHEN f.fpak_val_iva > 0 THEN ( f.fpak_sub_natu )
                            ELSE
                            0
                END ) ,2))AS baseimpgrav,
            sum(round( ( CASE
                    WHEN f.fpak_val_iva > 0 THEN 0
                            ELSE
                            f.fpak_sub_natu
                            END ) ,2)) AS baseimponible,
                            
            count ( f.fpak_num_fact ) as factura,
            ( SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                            ASTO_COD_EMPR =  in_empr AND
                                            ASTO_COD_EJER =  in_ejer AND
                                        ASTO_NUM_PRDO =  in_mes  AND
                                            RET_COD_CLPV =   f.fpak_cod_clpv  AND
                                            ret_cta_ret in ( select tret_cod from saetret WHERE   
                                                                        tret_cod_empr = in_empr  and     
                                                                        tret_ban_retf = 'RI' and
                                                                        tret_ban_crdb = 'DB' )   ) as valoretiva,  

            (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE                       	   
                        ASTO_COD_EMPR =  in_empr  AND
                    ASTO_COD_EJER =  in_ejer AND
                    ASTO_NUM_PRDO =  in_mes  AND
                    RET_COD_CLPV =   f.fpak_cod_clpv  AND
                    ret_cta_ret in ( select tret_cod from saetret WHERE

                                            tret_cod_empr =in_empr  and 
                                            tret_ban_retf = 'IR' and
                                            tret_ban_crdb = 'DB' )  ) as valoretrenta,
            0 as basenograv
            from saefpak f, saeclpv c where 
        c.clpv_cod_clpv = f.fpak_cod_clpv and
        c.clpv_cod_empr = in_empr and
        c.clpv_clopv_clpv = 'CL' and
        f.fpak_cod_empr = in_empr and
        f.fpak_cod_tven in ('B', 'D') and
        EXTRACT(YEAR FROM f.fpak_fec_pack )  = in_anio and
        EXTRACT(MONTH FROM f.fpak_fec_pack )  = in_mes and
        f.fpak_cod_efac <> 3
        group by f.fpak_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, f.fpak_aprob_sri order by c.clpv_ruc_clpv loop


        insert into tmp_ats_sis_flor (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                clv_con_clpv ,  clpv_ruc_clpv ,  baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user,baseimponible ,  basenograv, fpak_aprob_sri, fact_cod_clpv  ) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  baseimpgrav_v ,  montoiva_v ,
                    factura_v ,  valoretiva_v ,   valoretrenta_v , in_user, baseimponible_v,  basenograv_v,  aprobsri_f, fact_cod_clpv_v);


    end loop;

    -- ATS DEBITO
    --ATS SEMESTRAL

    IF in_tip_vent = 1 and in_vent='S' then

    for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, clpv_par_rela_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v
        

        in select  f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri,
                            round (( coalesce ((sum (  f.fact_con_miva ) ),0)),2) as  baseimpgrav,
                            round (( coalesce ((sum (  f.fact_iva )),0)),2) as  montoiva,
                round (( coalesce ((sum (  f.fact_sin_miva ) ),0)),2)as  baseimponible,
                            count( f.fact_num_preimp ) as factura ,
                            (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND
                                        ASTO_NUM_PRDO   in(1,2,3,4,5,6)  AND RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE
                                                                    tret_cod_empr = in_empr   and                               
                                                                    tret_ban_retf = 'RI' and
                                                                    tret_ban_crdb = 'DB' )   ) as valoretiva,
                            (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND                                 
                                        ASTO_NUM_PRDO in(1,2,3,4,5,6)  AND
                                        RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE                                   
                                                                tret_cod_empr = in_empr  and                            
                                                                tret_ban_retf = 'IR' and
                                                            tret_ban_crdb = 'DB' )  ) as valoretrenta,
            round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)    +    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
                            from saefact f, saeclpv c where
                            c.clpv_cod_clpv = f.fact_cod_clpv and
                            c.clpv_cod_empr = in_empr   and
                            c.clpv_clopv_clpv = 'CL' and
                            f.fact_cod_empr =  in_empr  and
                            f.fact_est_fact <> 'AN' and
                            f.fact_cod_ejer = in_ejer and
                            f.fact_num_prdo in(1,2,3,4,5,6)  and
                f.fact_fon_fact in(  select max(para_ndb_cxc) as para_ndb_cxc  from saepara where
                    para_cod_empr = in_empr)  and
                (  f.fact_tip_vent <> '41' )     
            group by f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri order by c.clpv_ruc_clpv loop 
        insert into tmp_ats_sis_deb (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv , clpv_par_rela,  baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user, baseimponible , basenograv, fact_aprob_sri) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  fact_cod_clpv_v ,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  clpv_par_rela_v,
                        baseimpgrav_v ,  montoiva_v , factura_v ,  valoretiva_v ,   valoretrenta_v , in_user , baseimponible_v, basenograv_v,
                        aprobsri_v);


    end loop;

    elsif in_tip_vent =2 and in_vent='S' then

    for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, clpv_par_rela_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v
        

        in select  f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri,
                            round (( coalesce ((sum (  f.fact_con_miva ) ),0)),2) as  baseimpgrav,
                            round (( coalesce ((sum (  f.fact_iva )),0)),2) as  montoiva,
                round (( coalesce ((sum (  f.fact_sin_miva ) ),0)),2)as  baseimponible,
                            count( f.fact_num_preimp ) as factura ,
                            (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND
                                        ASTO_NUM_PRDO in(7,8,9,10,11,12)  AND RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE
                                                                    tret_cod_empr = in_empr   and                               
                                                                    tret_ban_retf = 'RI' and
                                                                    tret_ban_crdb = 'DB' )   ) as valoretiva,
                            (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND                                 
                                        ASTO_NUM_PRDO in(7,8,9,10,11,12)  AND
                                        RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE                                   
                                                                tret_cod_empr = in_empr  and                            
                                                                tret_ban_retf = 'IR' and
                                                            tret_ban_crdb = 'DB' )  ) as valoretrenta,
            round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)    +    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
                            from saefact f, saeclpv c where
                            c.clpv_cod_clpv = f.fact_cod_clpv and
                            c.clpv_cod_empr = in_empr   and
                            c.clpv_clopv_clpv = 'CL' and
                            f.fact_cod_empr =  in_empr  and
                            f.fact_est_fact <> 'AN' and
                            f.fact_cod_ejer = in_ejer and
                            f.fact_num_prdo in(7,8,9,10,11,12)  and
                f.fact_fon_fact in(  select max(para_ndb_cxc) as para_ndb_cxc  from saepara where
                    para_cod_empr = in_empr)  and
                (  f.fact_tip_vent <> '41' )     
            group by f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri order by c.clpv_ruc_clpv loop 
        insert into tmp_ats_sis_deb (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv , clpv_par_rela,  baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user, baseimponible , basenograv, fact_aprob_sri) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  fact_cod_clpv_v ,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  clpv_par_rela_v,
                        baseimpgrav_v ,  montoiva_v , factura_v ,  valoretiva_v ,   valoretrenta_v , in_user , baseimponible_v, basenograv_v,
                        aprobsri_v);


    end loop;


    elsif in_tip_vent =0 then


    for fact_cod_clpv_v, clv_con_clpv_v, clpv_ruc_clpv_v, clpv_par_rela_v, aprobsri_v, baseimpgrav_v, montoiva_v, baseimponible_v, factura_v, valoretiva_v, valoretrenta_v, basenograv_v
        

        in select  f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri,
                            round (( coalesce ((sum (  f.fact_con_miva ) ),0)),2) as  baseimpgrav,
                            round (( coalesce ((sum (  f.fact_iva )),0)),2) as  montoiva,
                round (( coalesce ((sum (  f.fact_sin_miva ) ),0)),2)as  baseimponible,
                            count( f.fact_num_preimp ) as factura ,
                            (  SELECT  round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND
                                        ASTO_NUM_PRDO =  in_mes  AND RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE
                                                                    tret_cod_empr = in_empr   and                               
                                                                    tret_ban_retf = 'RI' and
                                                                    tret_ban_crdb = 'DB' )   ) as valoretiva,
                            (   SELECT round(( coalesce( ( sum( ret_valor )),0)),2)  as ret_valor  FROM SAERET WHERE
                                        ASTO_COD_EMPR =  in_empr  AND
                                        ASTO_COD_EJER =  in_ejer  AND                                 
                                        ASTO_NUM_PRDO =  in_mes  AND
                                        RET_COD_CLPV =   f.fact_cod_clpv  AND
                                        ret_cta_ret in ( select tret_cod from saetret WHERE                                   
                                                                tret_cod_empr = in_empr  and                            
                                                                tret_ban_retf = 'IR' and
                                                            tret_ban_crdb = 'DB' )  ) as valoretrenta,
            round (( coalesce ((sum (  coalesce(f.fact_fle_fact,0)    +    coalesce( f.fact_otr_fact,0)  +   coalesce(f.fact_fin_fact,0)  ) ),0)),2) as  basenograv
                            from saefact f, saeclpv c where
                            c.clpv_cod_clpv = f.fact_cod_clpv and
                            c.clpv_cod_empr = in_empr   and
                            c.clpv_clopv_clpv = 'CL' and
                            f.fact_cod_empr =  in_empr  and
                            f.fact_est_fact <> 'AN' and
                            f.fact_cod_ejer = in_ejer and
                            f.fact_num_prdo = in_mes  and
                f.fact_fon_fact in(  select max(para_ndb_cxc) as para_ndb_cxc  from saepara where
                    para_cod_empr = in_empr)  and
                (  f.fact_tip_vent <> '41' )     
            group by f.fact_cod_clpv, c.clv_con_clpv, c.clpv_ruc_clpv, c.clpv_par_rela, f.fact_aprob_sri order by c.clpv_ruc_clpv loop 
        insert into tmp_ats_sis_deb (  empr_cod_empr ,   ejer_cod_ejer, id_anio ,  prdo_num_prdo ,
                fact_cod_clpv , clv_con_clpv ,  clpv_ruc_clpv , clpv_par_rela,  baseimpgrav ,  montoiva ,
                factura ,	valoretiva ,  valoretrenta , user_cod_user, baseimponible , basenograv, fact_aprob_sri) 
                values( in_empr,     in_ejer,  in_anio,  in_mes,  fact_cod_clpv_v ,   clv_con_clpv_v ,  clpv_ruc_clpv_v ,  clpv_par_rela_v,
                        baseimpgrav_v ,  montoiva_v , factura_v ,  valoretiva_v ,   valoretrenta_v , in_user , baseimponible_v, basenograv_v,
                        aprobsri_v);


    end loop;


    end if; 
    

    end if;


    msn := 'OK';
    return msn;
    end 
    ;
    \$BODY\$
    LANGUAGE plpgsql VOLATILE
    COST 100";
    $oCon->QueryT($sqlalter);


    ////SP ATS COMPRAS WEB

    $sqlalter = "
    
    CREATE OR REPLACE FUNCTION \"public\".\"sp_ats_compras_web\"(\"in_empr\" int4, \"in_ejer\" int4, \"in_mes\" int4, \"in_anio\" int4, \"in_user\" int4, \"in_op\" int4, \"in_iva\" int4)
  RETURNS \"pg_catalog\".\"varchar\" AS \$BODY\$ -- ats saefprv
	
DECLARE
fprv_cod_sucu_o INTEGER;
fprv_cod_clpv_o INTEGER;
fprv_cre_fisc_o VARCHAR ( 20 );
clv_con_clpv_o VARCHAR ( 20 );
clpv_ruc_clpv_o VARCHAR ( 20 );
clpv_cod_tprov_o VARCHAR ( 20 );
clpv_par_rela_o VARCHAR ( 20 );
fprv_apl_conv_o VARCHAR ( 20 );
fprv_pag_exte_o VARCHAR ( 20 );
tran_tip_comp_o VARCHAR ( 20 );
fprv_fec_emis_o DATE;
fprv_fec_emis1_o DATE;
estab_o VARCHAR ( 20 );
ptoemi_o VARCHAR ( 20 );
fprv_num_fact_o VARCHAR ( 35 );
fprv_num_auto_o VARCHAR ( 100 );
basenogralva_o DECIMAL ( 16, 2 );
baseimponible_o DECIMAL ( 16, 2 );
baseimpgrav_o DECIMAL ( 16, 2 );
montoice_o DECIMAL ( 16, 2 );
fprv_val_viva_o DECIMAL ( 16, 2 );
porc1_o DECIMAL ( 16, 2 );
porc2_o DECIMAL ( 16, 2 );
val1_o DECIMAL ( 16, 2 );
val2_o DECIMAL ( 16, 2 );
fprv_cod_tpago_o VARCHAR ( 20 );
fprv_cod_fpagop_o VARCHAR ( 20 );
estab_ret_o VARCHAR ( 20 );
ptoemi_ret_o VARCHAR ( 20 );
fprv_aut_rete_o VARCHAR ( 100 );
fprv_fec_rete_o DATE;
fprv_num_rete_o VARCHAR ( 100 );
fprv_cod_tran_o VARCHAR ( 20 );
fprv_num_seri_o VARCHAR ( 20 );
fprv_mnt_noi_o VARCHAR ( 20 );
fprv_val_iva1_o DECIMAL ( 16, 2 );
valretservicios_o DECIMAL ( 16, 2 );
valretserv100_o DECIMAL ( 16, 2 );
fprv_cod_rtf1_o VARCHAR ( 20 );
fprv_val_bas1_o DECIMAL ( 16, 2 );
fprv_por_ret1_o DECIMAL ( 16, 2 );
fprv_val_ret1_o DECIMAL ( 16, 2 );
fprv_cod_rtf2_o VARCHAR ( 20 );
fprv_val_bas2_o DECIMAL ( 16, 2 );
fprv_por_ret2_o DECIMAL ( 16, 2 );
fprv_val_ret2_o DECIMAL ( 16, 2 );
clpv_cod_pais_o INTEGER;
-- rete fprv
ret_cta_ret_o VARCHAR ( 100 );
ret_porc_ret_o DECIMAL ( 16, 2 );
ret_bas_imp_o DECIMAL ( 16, 2 );
ret_valor_o DECIMAL ( 16, 2 );
msn VARCHAR ( 10 );
-- ats inventario
sucu_cod_sucu_in INTEGER;
minv_cod_clpv_in INTEGER;
clv_con_clpv_in VARCHAR ( 20 );
clpv_ruc_clpv_in VARCHAR ( 50 );
clpv_cod_tprov_in VARCHAR ( 50 );
clpv_par_rela_in VARCHAR ( 20 );
minv_apl_conv_in VARCHAR ( 20 );
minv_pag_exte_in VARCHAR ( 20 );
minv_cod_tran_in VARCHAR ( 20 );
tran_tip_comp_in VARCHAR ( 20 );
minv_fmov_in DATE;
estab_in VARCHAR ( 20 );
ptoemi_in VARCHAR ( 20 );
minv_fac_prov_in VARCHAR ( 100 );
fecha_emi_in DATE;
minv_aut_usua_in VARCHAR ( 100 );
valoretbienes10_in DECIMAL ( 16, 2 );
valoretservicios20_in DECIMAL ( 16, 2 );
valoretbienes30_in DECIMAL ( 16, 2 );
valoretservicios70_in DECIMAL ( 16, 2 );
valretserv100_in DECIMAL ( 16, 2 );
minv_iva_valo_in DECIMAL ( 16, 2 );
minv_tot_minv_in DECIMAL ( 16, 2 );
con_iva_in DECIMAL ( 16, 2 );
sin_iva_in DECIMAL ( 16, 2 );
minv_val_ice_in DECIMAL ( 16, 2 );
minv_cod_tpago_in VARCHAR ( 20 );
minv_cod_fpagop_in VARCHAR ( 20 );
minv_ser_docu_in VARCHAR ( 10 );
-- ats venta
fact_cod_clpv_v INTEGER;
clv_con_clpv_v VARCHAR ( 20 );
clpv_ruc_clpv_v VARCHAR ( 20 );
baseimpgrav_v DECIMAL ( 16, 2 );
montoiva_v DECIMAL ( 16, 2 );
montoice_v DECIMAL ( 16, 2 );
factura_v VARCHAR ( 100 );
valoretiva_v DECIMAL ( 16, 2 );
valoretrenta_v DECIMAL ( 16, 2 );
baseimponible_v DECIMAL ( 16, 2 );
basenograv_v DECIMAL ( 16, 2 );
clpv_par_rela_v VARCHAR ( 20 );
clpv_nom VARCHAR ( 255 );
asto_cod VARCHAR ( 255 );
ret_num_ret_inv VARCHAR ( 255 );
ret_ser VARCHAR ( 255 );
ret_aut VARCHAR ( 255 );
asto_fec DATE;
fprv_cod_asto VARCHAR ( 20 );

BEGIN-- borra tablas

-- DELETE FROM tmp_comp_cp;
-- DELETE FROM tmp_comp_inv;
-- DELETE FROM tmp_comp_ret;
-- DELETE FROM tmp_ats_sis_vent;
	
	IF
		in_op = 1 THEN
			FOR fprv_cod_sucu_o,
			fprv_cre_fisc_o,
			clv_con_clpv_o,
			fprv_cod_clpv_o,
			clpv_ruc_clpv_o,
			clpv_cod_tprov_o,
			clpv_par_rela_o,
			fprv_apl_conv_o,
			fprv_pag_exte_o,
			fprv_cod_tran_o,
			tran_tip_comp_o,
			fprv_fec_emis_o,
			estab_o,
			ptoemi_o,
			fprv_num_seri_o,
			fprv_num_fact_o,
			fprv_fec_emis1_o,
			fprv_num_auto_o,
			fprv_mnt_noi_o,
			basenogralva_o,
			baseimponible_o,
			baseimpgrav_o,
			montoice_o,
			fprv_val_viva_o,
			fprv_val_iva1_o,
			valretservicios_o,
			valretserv100_o,
			porc1_o,
			porc2_o,
			val1_o,
			val2_o,
			fprv_cod_tpago_o,
			fprv_cod_fpagop_o,
			fprv_cod_rtf1_o,
			fprv_val_bas1_o,
			fprv_por_ret1_o,
			fprv_val_ret1_o,
			fprv_cod_rtf2_o,
			fprv_val_bas2_o,
			fprv_por_ret2_o,
			fprv_val_ret2_o,
			estab_ret_o,
			ptoemi_ret_o,
			fprv_aut_rete_o,
			fprv_fec_rete_o,
			fprv_num_rete_o,
			clpv_cod_pais_o,
			clpv_nom,
			asto_cod -- compras ats saefprv
		IN SELECT
			F.FPRV_COD_SUCU,
			F.FPRV_CRE_FISC,
			C.CLV_CON_CLPV,
			F.FPRV_COD_CLPV,
			C.CLPV_RUC_CLPV,
			C.clpv_cod_tprov,
			C.clpv_par_rela,
			F.fprv_apl_conv,
			F.fprv_pag_exte,
			F.FPRV_COD_TRAN,
			( SELECT trans_tip_comp FROM saetran WHERE tran_cod_empr = in_empr AND tran_cod_modu = 4 
			AND tran_cod_tran = F.FPRV_COD_TRAN GROUP BY 1 ) AS TRAN_TIP_COMP,
			F.FPRV_FEC_EMIS,
			( SUBSTRING ( F.FPRV_NUM_SERI FROM 1 FOR 3 ) ) AS ESTAB,
			( SUBSTRING ( F.FPRV_NUM_SERI FROM 4 FOR 6 ) ) AS PTOEMI,
			F.FPRV_NUM_SERI,
			F.FPRV_NUM_FACT,
			F.FPRV_FEC_EMIS,
			F.FPRV_NUM_AUTO,
			F.FPRV_MNT_NOI,
			round((CASE	WHEN F.FPRV_MNT_NOI = 'S' THEN( COALESCE ( F.FPRV_VAL_GRA0, 0 ) + COALESCE ( F.FPRV_VAL_GR0S, 0 ) ) 
				ELSE COALESCE ( F.FPRV_VAL_NOI, 0 ) END),2 ) AS BASENOGRALVA,
			round((CASE	WHEN F.FPRV_MNT_NOI = 'S' THEN 0 ELSE ( COALESCE(F.FPRV_VAL_GRA0,0) + COALESCE( F.FPRV_VAL_GR0S, 0 ) ) 
						END 
						),
						2 
					) AS BASEIMPONIBLE,
					round(
						( COALESCE ( ( COALESCE ( F.fprv_val_grbs, 0 ) + COALESCE ( F.fprv_val_grab, 0 ) ), 0 ) ),
						2 
					) AS BASEIMPGRAV,
					round( ( COALESCE ( ( F.FPRV_VAL_VICE ), 0 ) ), 2 ) AS MONTOICE,
					round( ( COALESCE ( F.FPRV_VAL_VIVA, 0 ) ), 2 ) AS FPRV_VAL_VIVA,
					round( ( COALESCE ( ( COALESCE ( F.FPRV_VAL_IVA1, 0 ) ), 0 ) ), 2 ) AS FPRV_VAL_IVA1,
				round( ( CASE WHEN F.FPRV_POR_IVA2 = 70 THEN ( F.FPRV_VAL_IVA2 ) ELSE 0 END ), 2 ) AS VALRETSERVICIOS,
			round(
				(
				CASE
						
						WHEN ( ( COALESCE ( F.FPRV_POR_IVA2, 0 ) = 100 ) OR ( COALESCE ( F.fprv_por_iva1, 0 ) = 100 ) ) THEN
						( COALESCE ( F.FPRV_VAL_IVA2, 0 ) + COALESCE ( F.FPRV_VAL_IVA1, 0 ) ) ELSE 0 
					END 
					),
					2 
				) AS VALRETSERV100,
				round( ( COALESCE ( F.FPRV_POR_IVA1, 0 ) ), 2 ) ASporc1,
				round( ( COALESCE ( F.FPRV_POR_IVA2, 0 ) ), 2 ) AS porc2,
				round( ( COALESCE ( F.FPRV_VAL_IVA1, 0 ) ), 2 ) AS val1,
				round( ( COALESCE ( F.FPRV_VAL_IVA2, 0 ) ), 2 ) AS val2,
				F.FPRV_COD_TPAGO,
				F.FPRV_COD_FPAGOP,
				F.fprv_cod_rtf1,
				F.fprv_val_bas1,
				F.fprv_por_ret1,
				F.fprv_val_ret1,
				F.fprv_cod_rtf2,
				F.fprv_val_bas2,
				F.fprv_por_ret2,
				F.fprv_val_ret2,
				( SUBSTRING ( F.fprv_ser_rete FROM 1 FOR 3 ) ) AS ESTAB_RET,
				( SUBSTRING ( F.fprv_ser_rete FROM 4 FOR 6 ) ) AS PTOEMI_RET,
				fprv_aut_rete,
				fprv_fec_rete,
				fprv_num_rete,
				C.clpv_cod_paisp,
				C.clpv_nom_clpv,
				f.fprv_cod_asto 
			FROM
				SAEFPRV F,
				SAECLPV C 
			WHERE
				C.CLPV_COD_CLPV = F.FPRV_COD_CLPV 
				AND C.CLPV_COD_EMPR = in_empr 
				AND F.FPRV_COD_EMPR = in_empr 
				AND F.FPRV_COD_EJER = in_ejer 
				AND DATE_PART('MONTH' , F.FPRV_FEC_EMIS ) = in_mes
				loop
				INSERT INTO tmp_comp_cp (
					empr_cod_empr,
					sucu_cod_sucu,
					ejer_cod_ejer,
					anio_id,
					prdo_num_prdo,
					fprv_cod_clpv,
					fprv_cre_fisc,
					clv_con_clpv,
					clpv_ruc_clpv,
					tran_tip_comp,
					fprv_fec_emis,
					estab,
					ptoemi,
					fprv_num_fact,
					fprv_num_auto,
					basenogralva,
					baseimponible,
					baseimpgrav,
					montoice,
					fprv_val_viva,
					porc1,
					porc2,
					val1,
					val2,
					fprv_cod_tpago,
					fprv_cod_fpagop,
					estab_ret,
					ptoemi_ret,
					fprv_aut_rete,
					fprv_fec_rete,
					fprv_num_rete,
					user_cod_user,
					fprv_cod_rtf1,
					fprv_cod_rtf2,
					clpv_cod_pais,
					clpv_cod_tprov,
					clpv_par_rela,
					fprv_apl_conv,
					fprv_pag_exte,
					clpv_nom,
					asto_cod_asto,
					fprv_cod_tran 
				)
			VALUES
				(
					in_empr,
					fprv_cod_sucu_o,
					in_ejer,
					in_anio,
					in_mes,
					fprv_cod_clpv_o,
					fprv_cre_fisc_o,
					clv_con_clpv_o,
					clpv_ruc_clpv_o,
					tran_tip_comp_o,
					fprv_fec_emis_o,
					estab_o,
					ptoemi_o,
					fprv_num_fact_o,
					fprv_num_auto_o,
					basenogralva_o,
					baseimponible_o,
					baseimpgrav_o,
					montoice_o,
					fprv_val_viva_o,
					porc1_o,
					porc2_o,
					val1_o,
					val2_o,
					fprv_cod_tpago_o,
					fprv_cod_fpagop_o,
					estab_ret_o,
					ptoemi_ret_o,
					fprv_aut_rete_o,
					fprv_fec_rete_o,
					fprv_num_rete_o,
					in_user,
					fprv_cod_rtf1_o,
					fprv_cod_rtf2_o,
					clpv_cod_pais_o,
					clpv_cod_tprov_o,
					clpv_par_rela_o,
					fprv_apl_conv_o,
					fprv_pag_exte_o,
					clpv_nom,
					asto_cod,
					fprv_cod_tran_o 
				);
			
		END loop;
FOR fprv_cod_clpv_o,
fprv_cod_sucu_o,
fprv_num_fact_o,
clpv_ruc_clpv_o IN SELECT
fprv_cod_clpv,
sucu_cod_sucu,
fprv_num_fact,
clpv_ruc_clpv 
FROM
	tmp_comp_cp 
WHERE
	empr_cod_empr = in_empr 
	AND ejer_cod_ejer = in_ejer 
	AND prdo_num_prdo = in_mes 
	AND user_cod_user = in_user
	loop
	FOR ret_cta_ret_o,
	ret_porc_ret_o,
	ret_bas_imp_o,
	ret_valor_o,
	asto_cod IN SELECT
	R.RET_CTA_RET,
	round( R.RET_PORC_RET, 2 ) AS ret_porc_ret,
	round( R.RET_BAS_IMP, 2 ) AS ret_bas_imp,
	round( R.RET_VALOR, 2 ) AS ret_valor,
	R.RETE_COD_ASTO 
FROM
	SAERET R,
	SAEASTO A 
WHERE
	A.ASTO_COD_ASTO = R.RETE_COD_ASTO 
	AND A.ASTO_COD_EMPR = in_empr 
	AND A.ASTO_COD_EJER = in_ejer 
	AND A.ASTO_NUM_PRDO = in_mes 
	AND A.ASTO_COD_SUCU = fprv_cod_sucu_o 
	AND A.ASTO_EST_ASTO <> 'AN' 
	AND R.RET_CRE_ML >= 0 
	AND R.ASTO_COD_EMPR = in_empr 
	AND R.ASTO_COD_EJER = in_ejer 
	AND R.ASTO_NUM_PRDO = in_mes 
	AND R.RET_COD_CLPV = fprv_cod_clpv_o 
	AND R.RET_NUM_FACT = fprv_num_fact_o
	loop
	INSERT INTO tmp_comp_ret (
		empr_cod_empr,
		sucu_cod_sucu,
		ejer_cod_ejer,
		anio_id,
		prdo_num_prdo,
		ret_cod_clpv,
		ret_num_fact,
		ret_cta_ret,
		ret_porc_ret,
		ret_bas_imp,
		ret_valor,
		user_cod_user,
		tipo,
		asto_cod_asto 
	)
VALUES
	(
		in_empr,
		fprv_cod_sucu_o,
		in_ejer,
		in_anio,
		in_mes,
		fprv_cod_clpv_o,
		fprv_num_fact_o,
		ret_cta_ret_o,
		ret_porc_ret_o,
		ret_bas_imp_o,
		ret_valor_o,
		in_user,
		1,
		asto_cod 
	);

END loop;

END loop;

FOR sucu_cod_sucu_in,
minv_cod_clpv_in,
clv_con_clpv_in,
clpv_ruc_clpv_in,
clpv_cod_tprov_in,
clpv_par_rela_in,
minv_apl_conv_in,
minv_pag_exte_in,
minv_cod_tran_in,
tran_tip_comp_in,
minv_fmov_in,
estab_in,
ptoemi_in,
minv_fac_prov_in,
minv_ser_docu_in,
fecha_emi_in,
minv_aut_usua_in,
valoretbienes10_in,
valoretservicios20_in,
valoretbienes30_in,
valoretservicios70_in,
valretserv100_in,
minv_iva_valo_in,
minv_tot_minv_in,
con_iva_in,
sin_iva_in,
minv_val_ice_in,
minv_cod_tpago_in,
minv_cod_fpagop_in,
clpv_cod_pais_o,
clpv_nom,
asto_cod IN SELECT M
.minv_cod_sucu,
M.minv_cod_clpv,
C.clv_con_clpv,
C.clpv_ruc_clpv,
C.clpv_cod_tprov,
C.clpv_par_rela,
M.minv_apl_conv,
M.minv_pag_exte,
M.MINV_COD_TRAN,
( SELECT DEFI_TIP_COMP FROM saedefi WHERE defi_cod_empr = in_empr AND defi_cod_modu = 10 AND defi_cod_tran = M.MINV_COD_TRAN GROUP BY 1 ) AS TRAN_TIP_COMP,
M.minv_fmov,
( SUBSTRING ( M.minv_ser_docu FROM 1 FOR 3 ) ) AS ESTAB,
( SUBSTRING ( M.minv_ser_docu FROM 4 FOR 6 ) ) AS PTOEMI,
M.minv_fac_prov,
M.minv_ser_docu,
( M.minv_fmov ) AS fecha_emi,
M.minv_aut_usua,
(
	SELECT
		round( ( COALESCE ( SUM ( ret_valor ), 0 ) ), 2 ) AS ret_valor 
	FROM
		saeret 
	WHERE
		asto_cod_empr = in_empr 
		AND asto_cod_ejer = in_ejer 
		AND asto_num_prdo = in_mes 
		AND ret_porc_ret = '10' 
		AND ret_cod_clpv = M.MINV_COD_CLPV 
		AND ret_num_fact = M.minv_fac_prov 
	) AS VALORETBIENES10,
	(
	SELECT
		round( ( COALESCE ( SUM ( ret_valor ), 0 ) ), 2 ) AS ret_valor 
	FROM
		saeret 
	WHERE
		asto_cod_empr = in_empr 
		AND asto_cod_ejer = in_ejer 
		AND asto_num_prdo = in_mes 
		AND ret_porc_ret = '20' 
		AND ret_cod_clpv = M.MINV_COD_CLPV 
		AND ret_num_fact = M.minv_fac_prov 
	) AS VALORETSERVICIOS20,
	(
	SELECT
		round( ( COALESCE ( SUM ( ret_valor ), 0 ) ), 2 ) AS ret_valor 
	FROM
		saeret 
	WHERE
		asto_cod_empr = in_empr 
		AND asto_cod_ejer = in_ejer 
		AND asto_num_prdo = in_mes 
		AND ret_porc_ret = '30' 
		AND ret_cod_clpv = M.MINV_COD_CLPV 
		AND ret_num_fact = M.minv_fac_prov 
	) AS VALORETBIENES30,
	(
	SELECT
		round( ( COALESCE ( SUM ( ret_valor ), 0 ) ), 2 ) AS ret_valor 
	FROM
		saeret 
	WHERE
		asto_cod_empr = in_empr 
		AND asto_cod_ejer = in_ejer 
		AND asto_num_prdo = in_mes 
		AND ret_porc_ret = '70' 
		AND ret_cod_clpv = M.MINV_COD_CLPV 
		AND ret_num_fact = M.minv_fac_prov 
	) AS VALORETSERVICIOS70,
	(
	SELECT
		round( ( COALESCE ( SUM ( ret_valor ), 0 ) ), 2 ) AS ret_valor 
	FROM
		saeret 
	WHERE
		asto_cod_empr = in_empr 
		AND asto_cod_ejer = in_ejer 
		AND asto_num_prdo = in_mes 
		AND ret_porc_ret = '100' 
		AND ret_cod_clpv = M.MINV_COD_CLPV 
		AND ret_num_fact = M.minv_fac_prov 
	) AS VALRETSERV100,
	round( COALESCE ( M.minv_iva_valo, 0 ), 2 ) AS minv_iva_valo,
	round(
		(
			M.minv_tot_minv - COALESCE ( M.minv_dge_valo, 0 ) + COALESCE ( M.minv_otr_valo, 0 ) + COALESCE ( M.minv_fle_minv, 0 ) 
		),
		2 
	) AS minv_tot_minv,

    round(( select sum(dmov_cto_dmov) as base_grava 
									from  saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc > 0 and
									dmov_num_comp = M.minv_num_comp	 ),2) as con_iva ,
                        round(( select COALESCE(sum(dmov_cto_dmov), '0') as base_nograva from saedmov where 
									dmov_cod_empr = in_empr and
									dmov_cod_sucu = M.minv_cod_sucu and
									dmov_iva_porc = 0 and
									dmov_num_comp = M.minv_num_comp ) ,2 ) as sin_iva ,
	round( COALESCE ( M.minv_val_ice, 0 ), 2 ) AS minv_val_ice,
	M.minv_cod_tpago,
	M.minv_cod_fpagop,
	C.clpv_cod_paisp,
	C.clpv_nom_clpv,
	M.minv_comp_cont 
FROM
	SAEMINV M,
	SAECLPV C 
WHERE
	C.CLPV_COD_CLPV = M.MINV_COD_CLPV 
	AND M.MINV_COD_EMPR = in_empr 
	AND M.MINV_COD_EJER = in_ejer 
	AND M.MINV_NUM_PRDO = in_mes 
	AND M.MINV_COD_TRAN IN (
	SELECT
		D.DEFI_COD_TRAN 
	FROM
		SAEDEFI D,
		SAETRAN T 
	WHERE
		T.TRAN_COD_TRAN = D.DEFI_COD_TRAN 
		AND D.DEFI_COD_MODU = 10 
		AND D.DEFI_COD_EMPR = in_empr 
		AND D.DEFI_TIP_DEFI = '0'
		AND D.DEFI_TIP_COMP IN ( '01','02', '03' ) 
		AND T.TRAN_COD_EMPR = in_empr 
	) 
	AND M.MINV_COD_EJER = in_ejer 
	AND M.MINV_NUM_PRDO = in_mes 
	AND M.MINV_EST_MINV <> '0'
	loop
	INSERT INTO tmp_comp_inv (
		empr_cod_empr,
		sucu_cod_sucu,
		ejer_cod_ejer,
		id_anio,
		prdo_num_prdo,
		minv_cod_clpv,
		clv_con_clpv,
		clpv_ruc_clpv,
		minv_cod_tran,
		tran_tip_comp,
		minv_fmov,
		estab,
		ptoemi,
		minv_fac_prov,
		minv_ser_docu,
		fecha_emi,
		minv_aut_usua,
		valoretbienes10,
		valoretservicios20,
		valoretbienes30,
		valoretservicios70,
		valretserv100,
		minv_iva_valo,
		minv_tot_minv,
		con_iva,
		sin_iva,
		minv_val_ice,
		minv_cod_tpago,
		minv_cod_fpagop,
		user_cod_user,
		clpv_cod_pais,
		clpv_cod_tprov,
		clpv_par_rela,
		minv_apl_conv,
		minv_pag_exte,
		clpv_nom,
		asto_cod_asto,
		minv_cre_fisc 
	)
VALUES
	(
		in_empr,
		sucu_cod_sucu_in,
		in_ejer,
		in_anio,
		in_mes,
		minv_cod_clpv_in,
		clv_con_clpv_in,
		clpv_ruc_clpv_in,
		minv_cod_tran_in,
		tran_tip_comp_in,
		minv_fmov_in,
		estab_in,
		ptoemi_in,
		minv_fac_prov_in,
		minv_ser_docu_in,
		fecha_emi_in,
		minv_aut_usua_in,
		valoretbienes10_in,
		valoretservicios20_in,
		valoretbienes30_in,
		valoretservicios70_in,
		valretserv100_in,
		minv_iva_valo_in,
		minv_tot_minv_in,
		con_iva_in,
		sin_iva_in,
		minv_val_ice_in,
		minv_cod_tpago_in,
		minv_cod_fpagop_in,
		in_user,
		clpv_cod_pais_o,
		clpv_cod_tprov_in,
		clpv_par_rela_in,
		minv_apl_conv_in,
		minv_pag_exte_in,
		clpv_nom,
		asto_cod,
		'06' 
	);

END loop;

FOR fprv_cod_clpv_o,
fprv_cod_sucu_o,
fprv_num_fact_o,
clpv_ruc_clpv_o,
fprv_num_seri_o,
asto_cod IN SELECT
minv_cod_clpv,
sucu_cod_sucu,
minv_fac_prov,
clpv_ruc_clpv,
minv_ser_docu,
asto_cod_asto 
FROM
	tmp_comp_inv 
WHERE
	empr_cod_empr = in_empr 
	AND ejer_cod_ejer = in_ejer 
	AND prdo_num_prdo = in_mes 
	AND user_cod_user = in_user
	loop
	FOR ret_cta_ret_o,
	ret_porc_ret_o,
	ret_bas_imp_o,
	ret_valor_o,
	asto_fec,
	ret_num_ret_inv,
	ret_ser,
	ret_aut IN SELECT
	R.RET_CTA_RET,
	round( R.RET_PORC_RET, 2 ) AS ret_porc_ret,
	round( R.RET_BAS_IMP, 2 ) AS ret_bas_imp,
	round( R.RET_VALOR, 2 ) AS ret_valor,
	A.ASTO_FEC_ASTO,
	R.ret_num_ret,
	R.ret_ser_ret,
	R.ret_aut_ret 
FROM
	SAERET R,
	SAEASTO A,
	SAEMINV M 
WHERE
	A.ASTO_COD_ASTO = R.RETE_COD_ASTO 
	AND M.MINV_COMP_CONT = R.RETE_COD_ASTO 
	AND M.MINV_COD_EMPR = R.ASTO_COD_EMPR 
	AND M.MINV_COD_SUCU = R.ASTO_COD_SUCU 
	AND M.MINV_COD_EJER = R.ASTO_COD_EJER 
	AND M.MINV_FAC_PROV = R.RET_NUM_FACT 
	AND M.MINV_COD_CLPV = R.RET_COD_CLPV 
	AND A.ASTO_COD_EMPR = in_empr 
	AND A.ASTO_COD_EJER = in_ejer 
	AND A.ASTO_NUM_PRDO = in_mes 
	AND A.ASTO_COD_SUCU = fprv_cod_sucu_o 
	AND A.ASTO_EST_ASTO <> 'AN' 
	AND R.RET_CRE_ML >= 0 
	AND R.ASTO_COD_EMPR = in_empr 
	AND R.ASTO_COD_EJER = in_ejer 
	AND R.ASTO_NUM_PRDO = in_mes 
	AND R.RET_COD_CLPV = fprv_cod_clpv_o 
	AND R.RET_NUM_FACT = fprv_num_fact_o 
	AND M.MINV_SER_DOCU = fprv_num_seri_o
	loop
	INSERT INTO tmp_comp_ret (
		empr_cod_empr,
		sucu_cod_sucu,
		ejer_cod_ejer,
		anio_id,
		prdo_num_prdo,
		ret_cod_clpv,
		ret_num_fact,
		ret_cta_ret,
		ret_porc_ret,
		ret_bas_imp,
		ret_valor,
		user_cod_user,
		tipo,
		ret_num_ret,
		ret_ser_ret,
		ret_aut_ret,
		ret_ser_fact,
		asto_cod_asto 
	)
VALUES
	(
		in_empr,
		fprv_cod_sucu_o,
		in_ejer,
		in_anio,
		in_mes,
		fprv_cod_clpv_o,
		fprv_num_fact_o,
		ret_cta_ret_o,
		ret_porc_ret_o,
		ret_bas_imp_o,
		ret_valor_o,
		in_user,
		2,
		ret_num_ret_inv,
		ret_ser,
		ret_aut,
		fprv_num_seri_o,
		asto_cod 
	);

END loop;

END loop;
--ATS VENTAS SAEFACT
FOR clv_con_clpv_v,
clpv_ruc_clpv_v,
baseimpgrav_v,
montoiva_v,
baseimponible_v,
factura_v,
valoretiva_v,
valoretrenta_v,
basenograv_v IN SELECT CHARACTER_LENGTH
( f.fact_ruc_clie ) AS clpv_con_clpv,
f.fact_ruc_clie,
round( ( COALESCE ( ( SUM ( f.fact_con_miva ) ), 0 ) ), 2 ) AS baseimpgrav,
round( ( COALESCE ( ( SUM ( f.fact_iva ) ), 0 ) ), 2 ) AS montoiva,
round( ( COALESCE ( ( SUM ( f.fact_sin_miva ) ), 0 ) ), 2 ) AS baseimponible,
COUNT ( f.fact_num_preimp ) AS factura,
(
	SELECT
		round( ( COALESCE ( ( SUM ( ret_valor ) ), 0 ) ), 2 ) AS ret_valor 
	FROM
		SAERET 
	WHERE
		ASTO_COD_EMPR = in_empr 
		AND ASTO_COD_EJER = in_ejer 
		AND ASTO_NUM_PRDO = in_mes 
		AND RETE_RUCI_BENF = f.fact_ruc_clie 
		AND ret_cta_ret IN ( SELECT tret_cod FROM saetret WHERE tret_cod_empr = in_empr AND tret_ban_retf = 'RI' AND tret_ban_crdb = 'DB' ) 
	) AS valoretiva,
	(
	SELECT
		round( ( COALESCE ( ( SUM ( ret_valor ) ), 0 ) ), 2 ) asret_valor 
	FROM
		SAERET 
	WHERE
		ASTO_COD_EMPR = in_empr 
		AND ASTO_COD_EJER = in_ejer 
		AND ASTO_NUM_PRDO = in_mes 
		AND RETE_RUCI_BENF = f.fact_ruc_clie 
		AND ret_cta_ret IN ( SELECT tret_cod FROM saetret WHERE tret_cod_empr = in_empr AND tret_ban_retf = 'IR' AND tret_ban_crdb = 'DB' ) 
	) AS valoretrenta,
	round(
		(
			COALESCE (
				(
					SUM ( COALESCE ( f.fact_fle_fact, 0 ) + COALESCE ( f.fact_otr_fact, 0 ) + COALESCE ( f.fact_fin_fact, 0 ) ) 
				),
				0 
			) 
		),
		2 
	) AS basenograv 
FROM
	saefact f 
WHERE
	f.fact_cod_empr = in_empr 
	AND f.fact_est_fact <> 'AN' 
	AND DATE_PART('YEAR' , f.fact_fech_fact ) = in_anio 
	AND DATE_PART('MONTH' , f.fact_fech_fact ) = in_mes 
	AND f.fact_fon_fact IN ( SELECT para_fac_cxc FROM saepara WHERE para_cod_empr = in_empr ) 
	AND ( f.fact_tip_vent not in ( '99','41')) 
GROUP BY
	f.fact_ruc_clie 
ORDER BY
	f.fact_ruc_clie
	loop
	INSERT INTO tmp_ats_sis_vent_cp (
		empr_cod_empr,
		ejer_cod_ejer,
		id_anio,
		prdo_num_prdo,
		fact_cod_clpv,
		clv_con_clpv,
		clpv_ruc_clpv,
		baseimpgrav,
		montoiva,
		factura,
		valoretiva,
		valoretrenta,
		user_cod_user,
		baseimponible,
		basenograv,
		tipo 
	)
VALUES
	(
		in_empr,
		in_ejer,
		in_anio,
		in_mes,
		1,
		clv_con_clpv_v,
		clpv_ruc_clpv_v,
		baseimpgrav_v,
		montoiva_v,
		factura_v,
		valoretiva_v,
		valoretrenta_v,
		in_user,
		baseimponible_v,
		basenograv_v,
		'F' 
	);

END loop;

END IF;
msn := 'OK';
RETURN msn;

END;
\$BODY\$
  LANGUAGE plpgsql VOLATILE
  COST 100";

    $oCon->QueryT($sqlalter);
}


function sincronizar_base($aForm = '')
{

    //echo "DENTRO DE LA FUNCION SICNRONIZAR";exit;

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $oReturn = new xajaxResponse();

    //CREACION CARPETAS PARA FACTURACION 
    //RIDE POR PAIS
    $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];


    //PERU
    if ($S_PAIS_API_SRI == '51') {
        $ruta_dir = '../../modulos/envio_documentos/upload';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir, 0777, true);
        }
        $ruta_dir = '../../modulos/envio_documentos/upload/pdf';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir, 0777, true);
        }
        $ruta_dir = '../../modulos/envio_documentos/upload/xml';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir, 0777, true);
        }
        $ruta_dir = '../../modulos/envio_documentos/qr_facturas';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir, 0777, true);
        }
        $ruta_dir = '../../modulos/envio_documentos/qr_nota_credito';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir, 0777, true);
        }
    }
    //BOLIVIA
    if ($S_PAIS_API_SRI == '591') {
        $ruta_dir = '../../modulos/envio_documentos_bolivia/upload';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir);
        }
        $ruta_dir = '../../modulos/envio_documentos_bolivia/upload/pdf';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir);
        }
        $ruta_dir = '../../modulos/envio_documentos_bolivia/upload/xml';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir);
        }
        $ruta_dir = '../../modulos/envio_documentos_bolivia/qr_facturas';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir);
        }
        $ruta_dir = '../../modulos/envio_documentos_bolivia/qr_nota_credito';
        if (!file_exists($ruta_dir)) {
            mkdir($ruta_dir);
        }
    }


    try {

        $oCon->QueryT('BEGIN;');

        //ALTER PROFORMA
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpff_det_peso' AND TABLE_NAME = 'saedpff' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedpff add dpff_det_peso text";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS NUEVOS GUIAS DE REMISION LONGITUD EN BASE A LA FICHA TECNICA DEL SRI



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'para_inf_gfac' AND TABLE_NAME = 'saepara' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_inf_gfac varchar(1) default 'N'";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_cod_dest' AND TABLE_NAME = 'saeguia' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_cod_dest varchar(3)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_doc_adua' AND TABLE_NAME = 'saeguia' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_doc_adua varchar(20)";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_inf_adi' AND TABLE_NAME = 'saeguia' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_inf_adi text";
            $oCon->QueryT($sqlalter);
        }

        //TABLA NUEVA PUNTO DE VENTAS ADMINISTRACION DE TARJETAS DE CREDITO

        $sqlinf = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'puntos_venta' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {

            $sqltb = "CREATE TABLE comercial.puntos_venta (
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
                        INCREMENT 1
                        MINVALUE  1
                        MAXVALUE 2147483647
                                    START 1
                        CACHE 1
                        ),
            cod_empresa int2,
            cod_sucu int2,
            nombre varchar(255) COLLATE pg_catalog.default,
            descripcion varchar(255) COLLATE pg_catalog.default,
            cod_diners varchar(255) COLLATE pg_catalog.default,
            cod_pacificard varchar(255) COLLATE pg_catalog.default,
            cod_guayaquil varchar(255) COLLATE pg_catalog.default,
            cod_pichincha varchar(255) COLLATE pg_catalog.default,
            cod_solidario varchar(255) COLLATE pg_catalog.default,
            cod_produbanco varchar(255) COLLATE pg_catalog.default,
                centro_costo    varchar(1000)COLLATE pg_catalog.default,
            CONSTRAINT puntos_venta_pkey PRIMARY KEY (id)
            );
            ";
            $oCon->QueryT($sqltb);
        }
        //TABLA TIPO RETENCION
        $sqlinf = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'tipo_retencion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {

            $sqltb = "CREATE TABLE comercial.tipo_retencion (
                    id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
                                INCREMENT 1
                                MINVALUE  1
                                MAXVALUE 2147483647
                                            START 1
                                CACHE 1
                                ),
                    cod_empresa int2,
                        cod_sucursal int2,
                    cod_cliente varchar(255) COLLATE pg_catalog.default,
                    tipo varchar(2) COLLATE pg_catalog.default,
                        porc_comision numeric(18,6) ,
                    nro_cuenta varchar(255) COLLATE pg_catalog.default,
                    cod_cuenta varchar(255) COLLATE pg_catalog.default,
                    centro_costo varchar(1000) COLLATE pg_catalog.default,
                        local varchar(255) COLLATE pg_catalog.default,
                    nombre varchar(1000) COLLATE pg_catalog.default,
                    CONSTRAINT tipo_retencion_pkey PRIMARY KEY (id)
                    );
            ";
            $oCon->QueryT($sqltb);
        }

        //TABLA RESUMENES BOLETAS Y NOTAS DE CREDITO ASOCIADAS A BOLETAS PERU
        if ($S_PAIS_API_SRI == '51') {

            $sqlinf = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'resumenes_boletas' and table_schema='public'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE resumenes_boletas (
                    id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
                INCREMENT 1
                MINVALUE  1
                MAXVALUE 2147483647
                START 1
                CACHE 1
                ),
                    empresa int DEFAULT 1,
                    correlativo varchar(50),
                    fecha_generacion varchar(100),
                    fecha_emision varchar(100),
                    fecha_server  timestamp,
                    usuario_id    int,
                    numero_comprobantes int,
                    CONSTRAINT resumenes_boletas_pkey PRIMARY KEY (id)
                );";
                $oCon->QueryT($sqltb);
            }

            //CAMPO NUMERO DE CORRELATIVO RESUMENES
            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_cod_rc' AND TABLE_NAME = 'saefact' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saefact add fact_cod_rc int2";
                $oCon->QueryT($sqlalter);
            }
        }

        //CAMPOS INFO ADICIONAL FACTURACION DIGITAL SEDIMPRE VENEZUELA
        if ($S_PAIS_API_SRI == '58') {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'sucu_cod_site' AND TABLE_NAME = 'saesucu' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saesucu add sucu_cod_site varchar(50)";
                $oCon->QueryT($sqlalter);
            }




            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'sucu_val_site' AND TABLE_NAME = 'saesucu' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saesucu add sucu_val_site text";
                $oCon->QueryT($sqlalter);
            }
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'user_cod_comanda' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add user_cod_comanda varchar(4)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pass_admin_rest' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add pass_admin_rest varchar(4)";
            $oCon->QueryT($sqlalter);
        }

        $empr_cod_pais = $_SESSION['U_PAIS_COD'];


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prbo_cod_exist' AND TABLE_NAME = 'saeprbo' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeprbo add prbo_cod_exist varchar(4)";
            $oCon->QueryT($sqlalter);
        }


        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'tipo_existencia_pr' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.tipo_existencia_pr (
               id int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
               INCREMENT 1
               MINVALUE  1
               MAXVALUE 2147483647
               START 1
               CACHE 1),
               cod_tip_exist varchar(4) COLLATE pg_catalog.default,
               descripcion varchar(255) COLLATE pg_catalog.default,
               CONSTRAINT tipo_existencia_pr_pkey PRIMARY KEY (id)
               )";
            $oCon->QueryT($sqltb);
        }


        ///CAMPOS NUEVOS COMPRAS V2

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'inv_proforma_det' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'invpd_movil_clpv' AND TABLE_NAME = 'inv_proforma_det' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.inv_proforma_det add invpd_movil_clpv varchar(50)";
                $oCon->QueryT($sqlalter);
            }
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'clpv_pedi' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpe_cod_prod' AND TABLE_NAME = 'clpv_pedi' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.clpv_pedi add clpe_cod_prod varchar(255)";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpe_pre_ult' AND TABLE_NAME = 'clpv_pedi' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.clpv_pedi add clpe_pre_ult numeric(18,6)";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpe_pre_pac' AND TABLE_NAME = 'clpv_pedi' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.clpv_pedi add clpe_pre_pac numeric(18,6)";
                $oCon->QueryT($sqlalter);
            }
        }





        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'invp_det_prod' AND TABLE_NAME = 'inv_proforma' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.inv_proforma add invp_det_prod text";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_cod_anu' AND TABLE_NAME = 'saepedi' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_cod_anu varchar(35)";
            $oCon->QueryT($sqlalter);
        }

        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'aprobaciones_compras' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.aprobaciones_compras (
                id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 2147483647
              START 1
              CACHE 1
              ),
                empresa int DEFAULT 1,
                nombre varchar(255) COLLATE pg_catalog.default,
                descripcion varchar(255) COLLATE pg_catalog.default,
                estado varchar(1) COLLATE pg_catalog.default DEFAULT 'N'::character,
                orden int4 DEFAULT 1,
                envio_email_sn varchar(1) COLLATE pg_catalog.default DEFAULT 'N'::character varying,
                CONSTRAINT aprobaciones_compras_pkey PRIMARY KEY (id)
              );";
            $oCon->QueryT($sqltb);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'envio_whts_sn' AND TABLE_NAME = 'aprobaciones_compras' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.aprobaciones_compras add envio_whts_sn varchar(1) default 'N'";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tipo_aprobacion' AND TABLE_NAME = 'aprobaciones_compras' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.aprobaciones_compras add tipo_aprobacion varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'aprobaciones_solicitud_compra' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.aprobaciones_solicitud_compra (
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
            INCREMENT 1
            MINVALUE  1
            MAXVALUE 2147483647
            START 1
            CACHE 1
            ),
                empresa int,
                sucursal int,
            id_aprobacion int,
            id_solicitud varchar(100),
            observaciones varchar(1000) COLLATE pg_catalog.default,
            usuario varchar(255) COLLATE pg_catalog.default,
            fecha timestamp,
            CONSTRAINT aprobaciones_solicitud_compra_pkey PRIMARY KEY (id)
            );";
            $oCon->QueryT($sqltb);
        }



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_user_anu' AND TABLE_NAME = 'saepedi'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_user_anu int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_fec_anu' AND TABLE_NAME = 'saepedi'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_fec_anu timestamp";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_det_anu' AND TABLE_NAME = 'saepedi'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_det_anu text";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_dped_apro' AND TABLE_NAME = 'saepedi'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_dped_apro int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_dped_fecapr' AND TABLE_NAME = 'saepedi'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saepedi add pedi_dped_fecapr timestamp";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dped_can_apro' AND TABLE_NAME = 'saedped' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedped add dped_can_apro numeric(18,2)";
            $oCon->QueryT($sqlalter);
        }


        ///CAMPO NUEVO CLINICO



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_cod_uni' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_cod_uni varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO ADJUNTOS COMPRAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dped_adj_dped' AND TABLE_NAME = 'saedped' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedped add dped_adj_dped text";
            $oCon->QueryT($sqlalter);
        }


        ///CAMPOS APROBACIONES COMPRAS USUARIOS

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'aprobaciones_compras' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add aprobaciones_compras varchar(500) default '[]'";
            $oCon->QueryT($sqlalter);
        }

        ///BLQOUEO DE VENDEDORES

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_vendedor_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add bloqueo_vendedor_sn varchar(1) DEFAULT 'N'";
            $oCon->QueryT($sqlalter);
        }
        ///MUESTRA BLOQUEO PARA GENERAR SOLO COMPROBANTES FISCALES

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'habilita_fiscal_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add habilita_fiscal_sn varchar(1) DEFAULT 'N'";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO NUEVO PROFORMAS

        //TABLA DE ANULACION

        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'saeprff' and table_schema='public'";

        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'prff_ciud_clie' AND TABLE_NAME = 'saeprff'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeprff add prff_ciud_clie int4";
                $oCon->QueryT($sqlalter);
            }
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_det_rinf' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_det_rinf varchar(500)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_rinf_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_rinf_sn varchar(1)";
            $oCon->QueryT($sqlalter);
        }

        //HABILITACION PARA GENERAR SOLO COMPROBANTES FISCALES
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_mod_fiscal' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add empr_mod_fiscal varchar(1) DEFAULT 'N'";
            $oCon->QueryT($sqlalter);
        }

        /**INICIO - CAMPOS PARA EL WS DE VALIDACION DE INDENTIFICAION **/

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ws_iden_sn' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ws_iden_sn varchar(2)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ws_iden_url' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ws_iden_url text";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ws_iden_renueva' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ws_iden_renueva int4";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ws_iden_token' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ws_iden_token TEXT";
            $oCon->QueryT($sqlalter);
        }
        /**FIN - CAMPOS PARA EL WS DE VALIDACION DE INDENTIFICAION **/


        //CAMPOS COMPRADOR VENEZUELA

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpv_cod_ctrb' AND TABLE_NAME = 'saeclpv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeclpv add clpv_cod_ctrb varchar(1)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpv_ctrb_sn' AND TABLE_NAME = 'saeclpv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeclpv add clpv_ctrb_sn varchar(1) default 'N'";
            $oCon->QueryT($sqlalter);
        }


        //CAMPO TICKET GUIAS API LYCET PERU


        if ($S_PAIS_API_SRI == '51') {
            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'guia_num_tick' AND TABLE_NAME = 'saeguia' ";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeguia add guia_num_tick varchar(1000)";
                $oCon->QueryT($sqlalter);
            }
        }

        //CAMPOS EDICION SRI OFFLINE 

        //CAMPOS EDICION

        //FACTURAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fac_edi_sri' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fac_edi_sri int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_fedi_sri' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_fedi_sri timestamp";
            $oCon->QueryT($sqlalter);
        }

        //NOTAS DE CREDITO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ncre_edi_sri' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencre add ncre_edi_sri int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ncre_fedi_sri' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saencre add ncre_fedi_sri timestamp";
            $oCon->QueryT($sqlalter);
        }

        //GUIAS DE REMISION
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_edi_sri' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_edi_sri int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_fedi_sri' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_fedi_sri timestamp";
            $oCon->QueryT($sqlalter);
        }

        //RETENCIONES DE GASTO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fprv_edi_sri' AND TABLE_NAME = 'saefprv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_edi_sri int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fprv_fedi_sri' AND TABLE_NAME = 'saefprv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_fedi_sri timestamp";
            $oCon->QueryT($sqlalter);
        }

        //RETENCIONES DE INVENTARIO - LIQUIDACIONES DE COMPRA
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_edi_sri' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeminv add minv_edi_sri int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fedi_sri' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saeminv add minv_fedi_sri timestamp";
            $oCon->QueryT($sqlalter);
        }

        //TABLA DE ANULACION

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeminv_an' and table_schema='public'";

        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_edi_sri' AND TABLE_NAME = 'saeminv_an'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeminv_an add minv_edi_sri int2";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fedi_sri' AND TABLE_NAME = 'saeminv_an'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

            if ($ctralter == 0) {
                $sqlalter = "alter table saeminv_an add minv_fedi_sri timestamp";
                $oCon->QueryT($sqlalter);
            }
        }






        //CAMPO UBIGEO - PERU

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'sucu_ubi_geo' AND TABLE_NAME = 'saesucu' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saesucu add sucu_ubi_geo varchar(10)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_num_lic' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_num_lic varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_cod_prof' AND TABLE_NAME = 'saefact' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saefact add column fact_cod_prof INT";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_num_mtc' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_num_mtc varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_ch_iden' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_ch_iden int";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_cid_ch' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_cid_ch varchar(25)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_nom_ch' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_nom_ch varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_ape_ch' AND TABLE_NAME = 'saetrta' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetrta add trta_ape_ch varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_cod_hash' AND TABLE_NAME = 'saeguia' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_cod_hash text";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS FECHA RETENCION

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fprv_rete_fec' AND TABLE_NAME = 'saefprv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_rete_fec date";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fec_ret' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saeminv add minv_fec_ret date";
            $oCon->QueryT($sqlalter);
        }

        //VALIDACION TIPO DE DATOS FECHA RETENCION SRI
        $sql = "SELECT data_type from information_schema.columns 
        where table_name = 'saefprv' and column_name='fprv_rete_fec';";
        $dato_rete = consulta_string($sqlinf, 'data_type', $oIfx, 0);
        if ($dato_rete == 'date') {
            $sqlalter = "alter table saefprv alter column fprv_rete_fec set data type timestamp";
            $oCon->QueryT($sqlalter);
        }

        $sql = "SELECT data_type from information_schema.columns 
        where table_name = 'saeminv' and column_name='minv_fec_ret';";
        $dato_rete = consulta_string($sqlinf, 'data_type', $oIfx, 0);
        if ($dato_rete == 'date') {
            $sqlalter = "alter table saeminv alter column minv_fec_ret set data type timestamp";
            $oCon->QueryT($sqlalter);
        }

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeminv_an' and table_schema='public'";

        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fec_ret' AND TABLE_NAME = 'saeminv_an'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

            if ($ctralter == 0) {
                $sqlalter = "alter table saeminv_an add minv_fec_ret date";
                $oCon->QueryT($sqlalter);
            }
        }



        //CAMPOS SI/NO LEYENDA SRI OBLIGADO A LLEVAR CONTABILIDAD

        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'empr_sn_conta' AND TABLE_NAME = 'saeempr' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_sn_conta varchar(1) default 'S' ";
            $oCon->QueryT($sqlalter);
        }

        //PARAMETRO HABILITAR CONTROL CUPO FACTURAS

        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'para_fac_cup' AND TABLE_NAME = 'saepara' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_fac_cup varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }




        // PARAMETRO PARA CONTROLAR MENSAJE DE FACTURAS VENCIDAS
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'para_elim_item' AND TABLE_NAME = 'saepara' 
                        ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_elim_item varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }

        // PARAMETRO PARA CONTROLAR DESDE CUANTOS DIAS SE AVISA DE FACTURAS VENCIDAS
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'para_clave_elim' AND TABLE_NAME = 'saepara' 
                            ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_clave_elim varchar(255) default '' ";
            $oCon->QueryT($sqlalter);
        }


        //PERMISO ANULACION PROFORMAS


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'anula_proforma_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD anula_proforma_sn varchar(1) default 'N';";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'contrasena_actualizacion' AND TABLE_NAME = 'parametro_inv' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.parametro_inv ADD contrasena_actualizacion varchar(255) default '';";
            $oIfx->QueryT($sqlalter);
        }

        //CONTROL BLOQUEO DE SUCURSAL POR USUARIO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_sucu_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD bloqueo_sucu_sn varchar(1) default 'N';";
            $oIfx->QueryT($sqlalter);
        }


        // COLUMNA PARA SABER EL TIPO DE COLOR SI ES CLARO, MEDIO O OBSCURO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tipo_color' AND TABLE_NAME = 'color_inv' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.color_inv ADD tipo_color varchar(5) default 'N';";
            $oIfx->QueryT($sqlalter);
        }


        //VALIDACION DE NUEVOS CAMPOS TABLA SAEPRFF
        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeprff' and table_schema='public'";

        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE COLUMN_NAME = 'prff_cod_ref' AND TABLE_NAME = 'saeprff'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeprff add prff_cod_ref int";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE COLUMN_NAME = 'prff_cel_prff' AND TABLE_NAME = 'saeprff'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeprff add prff_cel_prff varchar(20)";
                $oCon->QueryT($sqlalter);
            }


            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prff_user_anu' AND TABLE_NAME = 'saeprff'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeprff add prff_user_anu int2";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prff_fec_anu' AND TABLE_NAME = 'saeprff'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

            if ($ctralter == 0) {
                $sqlalter = "alter table saeprff add prff_fec_anu timestamp";
                $oCon->QueryT($sqlalter);
            }
        }


        //PARAMETRO HABILITAR PEDIDOS AL ANULAR FACTURAS

        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'para_pedi_sn' AND TABLE_NAME = 'saepara' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_pedi_sn varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS CODIGO PRECIO - COSTO POR SUCURSAL
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'sucu_cod_prec' AND TABLE_NAME = 'saesucu' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saesucu add sucu_cod_prec varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'sucu_cod_cost' AND TABLE_NAME = 'saesucu' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saesucu add sucu_cod_cost varchar(255)";
            $oCon->QueryT($sqlalter);
        }


        //CAMPOS CODIGO HTML FORMATO ETIQUETAS - LUBAMAQUI

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ftrn_cod_html' AND TABLE_NAME = 'saeftrn' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeftrn add ftrn_cod_html text";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS NUEVOS CONFIGURACION LEYENDAD XML RIDE ECUADOR

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ixml_sn_xml' AND TABLE_NAME = 'saeixml' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeixml add ixml_sn_xml varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ixml_sn_pdf' AND TABLE_NAME = 'saeixml' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeixml add ixml_sn_pdf varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }


        //PARAMETRO CONVERSION DE UNIDADES

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'para_conv_sn' AND TABLE_NAME = 'saepara' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_conv_sn varchar(1) default 'N' ";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dfac_cant_conv' AND TABLE_NAME = 'saedfac' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedfac add dfac_cant_conv float4";
            $oCon->QueryT($sqlalter);
        }




        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'tmp_detalle_factura' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {
            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'cant_conv' AND TABLE_NAME = 'tmp_detalle_factura'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE tmp_detalle_factura ADD cant_conv float4;";
                $oIfx->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'descripcion_exp' AND TABLE_NAME = 'tmp_detalle_factura'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE tmp_detalle_factura ADD descripcion_exp varchar(300)";
                $oIfx->QueryT($sqlalter);
            }

            //CAMPO PERU - CONCEPTO IGV
            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'conc_igv' AND TABLE_NAME = 'tmp_detalle_factura'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE tmp_detalle_factura ADD conc_igv int4;";
                $oIfx->QueryT($sqlalter);
            }
        }

        //TABLA CONVERSION DE UNIDADES
        $sqlinf = "SELECT count(*) as conteo
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE  TABLE_NAME = 'conversion_unidad' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.conversion_unidad (
                        id serial PRIMARY KEY,
                        unid_cod_base int2,
                        unid_cod_conv int2,
                        unid_form_conv varchar(500) COLLATE \"pg_catalog\".\"default\"
                      )
                      ;";
            $oCon->QueryT($sqltb);
        }




        // TABLA PARA VERIFICAR EL ESTADO DE LAS FACTURAS 
        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'historial_estados_factura' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.historial_estados_factura (
                    id serial PRIMARY KEY,
                    cod_fact int4,
                    fecha_hora timestamp,
                    id_usuario int4,
                    comentario varchar(10000),
                    modulo_factura varchar(255),
                    estado_factura varchar(255)                    
                  )
                  ;";
            $oCon->QueryT($sqltb);
        }

        //TABLA TIPO DE RENTA - PERU


        //PERU
        //if ($S_PAIS_API_SRI == '51') {

        //TABLA VENTA MOVIL DEVOLUCIONES - MOTIVO
        $sqlinf = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE  TABLE_NAME = 'tipo_renta' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.tipo_renta (
                            id serial PRIMARY KEY,
                            numero varchar(4) COLLATE \"pg_catalog\".\"default\",
                            descripcion varchar(1000) COLLATE \"pg_catalog\".\"default\",
                            art_ley_imp varchar(255) COLLATE \"pg_catalog\".\"default\",
                            cod_ren_ocde varchar(10) COLLATE \"pg_catalog\".\"default\"
                        )
                        ;";
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }

        // TABLA LIBRO COMPRAS PARA PERU, VERIFICAR SI ESTA YA TOMADA ESA FACTURA
        $sqlinf = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = 'libro_compras_ajustes' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.libro_compras_ajustes (
                                        id serial PRIMARY KEY,
                                        cod_ejer int4,
                                        cod_empr int4,
                                        cod_sucu int4,
                                        cod_asto varchar(255),
                                        estado varchar(10),
                                        
                                        usua_crea int4,
                                        fec_crea timestamp,
                                        usua_act int4,
                                        fec_act timestamp
                                    )
                                    ;";
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }


        //TABLA Convenios para evitar la doble tributacion
        $sqlinf = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE  TABLE_NAME = 'convenios_doble_tributacion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.convenios_doble_tributacion (
                            id serial PRIMARY KEY,
                            numero varchar(4) COLLATE \"pg_catalog\".\"default\",
                            descripcion varchar(1000) COLLATE \"pg_catalog\".\"default\",
                            art_ley_imp varchar(255) COLLATE \"pg_catalog\".\"default\",
                            cod_ren_ocde varchar(10) COLLATE \"pg_catalog\".\"default\"
                        )
                        ;";
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }


        //TABLA Exoneraciones de operaciones de no domiciliados
        $sqlinf = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE  TABLE_NAME = 'exoneracion_oper_domici' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.exoneracion_oper_domici (
                            id serial PRIMARY KEY,
                            numero varchar(4) COLLATE \"pg_catalog\".\"default\",
                            descripcion varchar(1000) COLLATE \"pg_catalog\".\"default\",
                            art_ley_imp varchar(255) COLLATE \"pg_catalog\".\"default\",
                            cod_ren_ocde varchar(10) COLLATE \"pg_catalog\".\"default\"
                        )
                        ;";
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }

        // alter saefprv campo fprv_tip_rent tipo de renta factura de gasto
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fprv_tip_rent' AND TABLE_NAME = 'saefprv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_tip_rent int2";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // alter saefprv campo fprv_doble_tribu tipo de renta factura de gasto
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fprv_doble_tribu' AND TABLE_NAME = 'saefprv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_doble_tribu int2";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // alter saefprv campo fprv_exonera_opera tipo de renta factura de gasto
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fprv_exonera_opera' AND TABLE_NAME = 'saefprv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_exonera_opera int2";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // alter saefprv campo fprv_id_bien_serv tipo de renta factura de gasto
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fprv_id_bien_serv' AND TABLE_NAME = 'saefprv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefprv add fprv_id_bien_serv int2";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }
        //}
        //CAMPOS INFORMACION BANCARIA

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_cta_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_cta_sn varchar(50)";
            $oCon->QueryT($sqlalter);
        }


        // alter saefprv campo dmov_azoc_sn para saber si el azocado ya fue realizado
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'dmov_azoc_sn' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_azoc_sn varchar(10)";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // alter saefprv campo dmov_azoc_sn para saber si el azocado ya fue realizado
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'dmov_can_azoc' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_can_azoc float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // alter saefprv campo dmov_azoc_sn para saber si el azocado ya fue realizado
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'dmov_can_azoc' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_can_azoc float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // ---------------------------------------------------------------
        // Alters saedmov campos romaneo
        // ---------------------------------------------------------------

        // alter saedmov campo dmov_can_roma
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'dmov_can_roma' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_can_roma float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();

            $sqlalter = "alter table saedmov_an add dmov_can_roma float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // alter saedmov campo dmov_tara_roma
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'dmov_tara_roma' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_tara_roma float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();

            $sqlalter = "alter table saedmov_an add dmov_tara_roma float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // alter saedmov campo dmov_cant_merma
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dmov_cant_merma' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_cant_merma varchar(2555)";
            $oCon->QueryT($sqlalter);
            $oCon->Free();

            $sqlalter = "alter table saedmov_an add dmov_cant_merma varchar(2555)";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // alter saedmov campo dmov_prec_vent
        $sqlgein = "SELECT count(*) as conteo
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE COLUMN_NAME = 'dmov_prec_vent' AND TABLE_NAME = 'saedmov' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedmov add dmov_prec_vent float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();

            $sqlalter = "alter table saedmov_an add dmov_prec_vent float";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // alter saeminv campo minv_est_merma
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'minv_est_merma' AND TABLE_NAME = 'saeminv' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeminv add minv_est_merma VARCHAR(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();

            $sqlalter = "alter table saeminv_an add minv_est_merma VARCHAR(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        // alter saeminv campo minv_est_merma
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'defi_prec_vent' AND TABLE_NAME = 'saedefi' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedefi add defi_prec_vent VARCHAR(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // alter saeminv campo minv_est_merma
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'defi_mod_can' AND TABLE_NAME = 'saedefi' ";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saedefi add defi_mod_can VARCHAR(10) default 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // ---------------------------------------------------------------
        // Alters saedmov campos romaneo
        // ---------------------------------------------------------------


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_det_cta' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_det_cta varchar(500)";
            $oCon->QueryT($sqlalter);
        }



        //ACTULIZAICON- SP ATS - COMPRA WEB 
        //actualiza_sp_sri_web($oCon);


        //CAMPO TIPO ENTREGA - GUIAS DE REMISION
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_tip_entr' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_tip_entr varchar(50)";
            $oCon->QueryT($sqlalter);
        }

        //TABLA HISTORIAL FACTURACION ELECTRONICA
        $sqlinf = "SELECT count(*) as conteo
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE  TABLE_NAME = 'hist_fact_elec' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.hist_fact_elec (
                    id serial PRIMARY KEY,
                    id_empresa INT NOT NULL,
                    id_saefact TEXT NOT NULL,
                    comentario TEXT NOT NULL,
                    parametros JSONB NOT NULL,
                    estado VARCHAR(1) NOT NULL DEFAULT 'F',
                    considera_contratos VARCHAR(1) NOT NULL DEFAULT 'N',
                    id_usuario INT NOT NULL,
                    id_usuario_reversa INT NOT NULL DEFAULT 0,
                    fecha_server timestamp NOT NULL,
                    fecha_reversa timestamp
                    )
                    ;";
            $oCon->QueryT($sqltb);
        }

        //TABLA VENTA MOVIL DEVOLUCIONES - MOTIVO
        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'motivos_devolucion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.motivos_devolucion(
             id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 2147483647
              START 1
              ) ,
      mot_cod_empr int2,
      mot_cod_sucu int2,
      mot_des_mot varchar(255) COLLATE \"pg_catalog\".\"default\",
      mot_cod_bode int2,
      mot_adj_sn varchar(1) default 'N',
      mot_est_mot varchar(1) default 'S',
      mot_created_at timestamp,
      mot_user_created int,
      mot_updated_at timestamp,
      mot_user_updated int,
      CONSTRAINT \"pk_motivos_devolucion\" PRIMARY KEY (\"id\")
              );";
            $oCon->QueryT($sqltb);
        }

        //CAMPOS NUEVOS DEVOLUCIONES

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dncr_cod_dev' AND TABLE_NAME = 'saedncs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedncs add dncr_cod_dev int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
       FROM INFORMATION_SCHEMA.COLUMNS
       WHERE COLUMN_NAME = 'dncr_adj_dncr' AND TABLE_NAME = 'saedncs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedncs add dncr_adj_dncr varchar(1000)";
            $oCon->QueryT($sqlalter);
        }


        //CAMPOS NUEVOS FICHA CLIENTE

        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'clpv_scord_cre' AND TABLE_NAME = 'saeclpv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeclpv add clpv_scord_cre int2;";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS NUEVOS COMPARTIR FACTURA REDES SOCIALES - WHATSAPP
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_rsoc_empr' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_rsoc_empr varchar(1) default 'N';";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_frso_empr' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_frso_empr int4;";
            $oCon->QueryT($sqlalter);
        }


        //CAMPOS ASIENTO CONTABLE CHEQUES PROTESTADOS


        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'cfpg_asto_prot' AND TABLE_NAME = 'saergfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saergfp add cfpg_asto_prot varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
       FROM INFORMATION_SCHEMA.COLUMNS
       WHERE COLUMN_NAME = 'cfpg_prot_ejer' AND TABLE_NAME = 'saergfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saergfp add cfpg_prot_ejer int4;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
       FROM INFORMATION_SCHEMA.COLUMNS
       WHERE COLUMN_NAME = 'cfpg_prot_prdo' AND TABLE_NAME = 'saergfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saergfp add cfpg_prot_prdo int4;";
            $oCon->QueryT($sqlalter);
        }



        //CAMPOS TIPO TRAN CHEQUES PROTESTADOS

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tran_prot_tran' AND TABLE_NAME = 'saetran'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saetran add tran_prot_tran varchar(1) default 'N';";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO ESTADO CHEQUES PROTESTADOS SAERGFP

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'cfpg_est_chp' AND TABLE_NAME = 'saergfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saergfp add cfpg_est_chp varchar(1) default 'N';";
            $oCon->QueryT($sqlalter);
        }


        //CAMPOS FICHA PRODUCTO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'prod_cbarra_caja' AND TABLE_NAME = 'saeprod'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeprod ADD prod_cbarra_caja varchar(255);";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'prod_unid_caja' AND TABLE_NAME = 'saeprod'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeprod ADD prod_unid_caja int2;";
            $oIfx->QueryT($sqlalter);
        }


        //CAMPO CODIGO SAEDFAC NOTAS DE CREDITO

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dncr_cod_dfac' AND TABLE_NAME = 'saedncr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedncr ADD dncr_cod_dfac int4;";
            $oIfx->QueryT($sqlalter);
        }


        //TABLA OPERACIONES  GRAVADAS INAFECTAS EXONERADAS PERU

        if ($S_PAIS_API_SRI == '51') {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dfac_con_igv' AND TABLE_NAME = 'saedfac'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE saedfac ADD dfac_con_igv int4;";
                $oIfx->QueryT($sqlalter);
            }

            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'tmp_detalle_factura' and table_schema='comercial'";
            $ctralter_t = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter_t > 0) {
                $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'conc_igv' AND TABLE_NAME = 'tmp_detalle_factura' and table_schema='public'";
                $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
                if ($ctralter == 0) {
                    $sqlalter = "ALTER TABLE tmp_detalle_factura ADD conc_igv int4;";
                    $oIfx->QueryT($sqlalter);
                }
            }


            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'conceptos_igv_peru' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.conceptos_igv_peru(
                            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
                            INCREMENT 1
                            MINVALUE  1
                            MAXVALUE 2147483647
                            START 1
                            ) ,

                    igv_des_igv varchar(500) COLLATE \"pg_catalog\".\"default\",
                    igv_por_igv numeric DEFAULT 0,
                    igv_nobj_igv  varchar(1) DEFAULT 'N',
                    igv_exc_igv  varchar(1) DEFAULT 'N',

                    CONSTRAINT \"pk_conceptos_igv_peru\" PRIMARY KEY (\"id\")
                            );";
                $oCon->QueryT($sqltb);
            }
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'descripcion_exp' AND TABLE_NAME = 'tmp_detalle_factura'  and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        //echo $ctralter;exit;
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE tmp_detalle_factura ADD descripcion_exp varchar(300)";
            $oIfx->QueryT($sqlalter);
        }


        //TABLA  API BOLIVIA SINCRONIZACION DE CATALOGOS

        if ($S_PAIS_API_SRI == '591') {

            //CAMPOS TIPO DE EMISION FECHA NOTAS DE CREDITO

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_leye_ncre' AND TABLE_NAME = 'saencre' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saencre add ncre_leye_ncre varchar(500)";
                $oCon->QueryT($sqlalter);
            }



            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ncre_tip_emis' AND TABLE_NAME = 'saencre' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saencre add ncre_tip_emis varchar(50)";
                $oCon->QueryT($sqlalter);
            }


            //CAMPOS TIPO DE EMISION FECHA FACTURA
            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_leye_fact' AND TABLE_NAME = 'saefact' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saefact add fact_leye_fact varchar(500)";
                $oCon->QueryT($sqlalter);
            }






            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_tip_emis' AND TABLE_NAME = 'saefact' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saefact add fact_tip_emis varchar(50)";
                $oCon->QueryT($sqlalter);
            }



            //CAMPO CODIGO EXTERNO API FICHA PRODUCTO
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'prod_cod_api' AND TABLE_NAME = 'saeprod' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeprod add prod_cod_api int4";
                $oCon->QueryT($sqlalter);
            }

            //CODIGO ASIGNADO POR EL API AL MOMENTO DEL REGISTRO

            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_cod_api_fac' AND TABLE_NAME = 'saeempr' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeempr add empr_cod_api_fac varchar(255)";
                $oCon->QueryT($sqlalter);
            }
            //TOKEN DEL CLIENTE EN LA PLATAFORMA SIN (DATO PROPORCIONADO POR EL CLIENTE)
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_token_api_fac' AND TABLE_NAME = 'saeempr' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeempr add empr_token_api_fac text";
                $oCon->QueryT($sqlalter);
            }

            //CAMPO ALIAS PARA ASIGNAR CODIGO DE UNIDAD REGISTRADO EN EL API
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'unid_cod_alias' AND TABLE_NAME = 'saeunid' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saeunid add unid_cod_alias int4";
                $oCon->QueryT($sqlalter);
            }

            //CAMPO CODIGO FORMAS DE PAGO API
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fpag_cod_api' AND TABLE_NAME = 'saefpag' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saefpag add fpag_cod_api int4";
                $oCon->QueryT($sqlalter);
            }

            //CAMPO CODIGO MONEDA API
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'mone_cod_api' AND TABLE_NAME = 'saemone' and table_schema='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saemone add mone_cod_api int4";
                $oCon->QueryT($sqlalter);
            }
            //sincronizarActividades
            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'catalogo_actividades' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_actividades(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
             INCREMENT 1
             MINVALUE  1
             MAXVALUE 2147483647
             START 1
             ) ,
     caeb_cod_act  varchar(25) not null,
     cact_des_cact varchar(500) COLLATE \"pg_catalog\".\"default\",
     cact_tip_cact varchar(2) COLLATE \"pg_catalog\".\"default\",
     
     CONSTRAINT \"pk_catalogo_actividades\" PRIMARY KEY (\"id\")
             );";
                $oCon->QueryT($sqltb);
            }

            //sincronizarParametricaMotivoAnulacion

            $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'catalogo_motivos_anulacion' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_motivos_anulacion(
             id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 2147483647
              START 1
              ) ,
      manu_cod_clas int4 not null,
      manu_des_manu varchar(500) COLLATE \"pg_catalog\".\"default\",
      
      CONSTRAINT \"pk_motivos_anulacion\" PRIMARY KEY (\"id\")
              );";
                $oCon->QueryT($sqltb);
            }

            //-------------------------------------------------------//
            //              E  Q   U   I   F   A   X                //
            //------------------------------------------------------//


            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'equifax_credentials' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.equifax_credentials (
                id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 2147483647
              START 1
              CACHE 1
              ),
                empresa_id int4 NOT NULL,
                app_id varchar(255) COLLATE pg_catalog.default NOT NULL,
                app_secret varchar(255) COLLATE pg_catalog.default NOT NULL,
                token text COLLATE pg_catalog.default,
                duracion int4 NOT NULL,
                ultima_generacion timestamp(6) DEFAULT now(),
                url_ws varchar(255) COLLATE pg_catalog.default,
                url_estado_mensaje varchar(255) COLLATE pg_catalog.default,
                habilitado_sn varchar(1) COLLATE pg_catalog.default,
                rango_de_dias int4,
                CONSTRAINT equifax_key PRIMARY KEY (id)
              )
              ;";
                $oCon->QueryT($sqltb);
            }

            /*

            $sqlinf = "SELECT count(*) as conteo
           FROM INFORMATION_SCHEMA.COLUMNS
           WHERE  TABLE_NAME = 'consumo_equifax' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.consumo_equifax (
                id int2 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 32767
              START 1
              CACHE 1
              ),
                tipo_documento varchar(255) COLLATE pg_catalog.default,
                documento varchar(255) COLLATE pg_catalog.default,
                nombre_sujeto varchar(255) COLLATE pg_catalog.default,
                score_actual int4,
                score_6_meses int4,
                score_12_meses int4,
                actividad_sri char(3000) COLLATE pg_catalog.default,
                ruc_sri varchar(255) COLLATE pg_catalog.default,
                estado_contribuyente varchar(255) COLLATE pg_catalog.default,
                clase_contribuyente varchar(255) COLLATE pg_catalog.default,
                codigo_ciiu varchar(255) COLLATE pg_catalog.default,
                fecha_inicio_act varchar(255) COLLATE pg_catalog.default,
                fecha_fin_act varchar(255) COLLATE pg_catalog.default,
                numero_estab varchar(255) COLLATE pg_catalog.default,
                obligado_cont varchar(255) COLLATE pg_catalog.default,
                nombre_comercial varchar(255) COLLATE pg_catalog.default,
                usuario_reg int4,
                fecha_score date,
                fecha_server timestamp(6),
                id_empresa int2,
                CONSTRAINT consumo_equifax_pkey PRIMARY KEY (id)
              )
              ;)
             ;";
                $oCon->QueryT($sqltb);
            }
*/

            //-------------------------------------------------------//
            //         F    I   N        E  Q   U   I   F   A   X   //
            //------------------------------------------------------//



            //sincronizarParametricaTipoMoneda
            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'catalogo_monedas' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_monedas(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
             INCREMENT 1
             MINVALUE  1
             MAXVALUE 2147483647
             START 1
             ) ,
     cmone_cod_clas    int4 not null,
     cmone_des_cmone varchar(500) COLLATE \"pg_catalog\".\"default\",
     
     CONSTRAINT \"pk_catalogo_monedas\" PRIMARY KEY (\"id\")
             );";
                $oCon->QueryT($sqltb);
            }

            //sincronizarParametricaTipoMetodoPago

            $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'catalogo_metodos_pago' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_metodos_pago(
             id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
              INCREMENT 1
              MINVALUE  1
              MAXVALUE 2147483647
              START 1
              ) ,
      mpag_cod_clas int4 not null,
      mpag_des_mpag varchar(500) COLLATE \"pg_catalog\".\"default\",
      
      CONSTRAINT \"pk_metodos_pago\" PRIMARY KEY (\"id\")
              );";
                $oCon->QueryT($sqltb);
            }


            //sincronizarParametricaUnidadMedida

            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'catalogo_unidades' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_unidades(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
             INCREMENT 1
             MINVALUE  1
             MAXVALUE 2147483647
             START 1
             ) ,
     unid_cod_clas int4 not null,
     unid_des_unid varchar(500) COLLATE \"pg_catalog\".\"default\",
     
     CONSTRAINT \"pk_catalogo_unidades\" PRIMARY KEY (\"id\")
             );";
                $oCon->QueryT($sqltb);
            }



            //ListaLeyendasFactura

            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'catalogo_leyendas_factura' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_leyendas_factura(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
             INCREMENT 1
             MINVALUE  1
             MAXVALUE 2147483647
             START 1
             ) ,
     ley_cod_act varchar(50) COLLATE \"pg_catalog\".\"default\",
     ley_des_ley varchar(1000) COLLATE \"pg_catalog\".\"default\",
     
     CONSTRAINT \"pk_leyendas_factura\" PRIMARY KEY (\"id\")
             );";
                $oCon->QueryT($sqltb);
            }

            //ListaProductosServicios

            $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'catalogo_productos_servicios' and table_schema='comercial'";
            $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqltb = "CREATE TABLE comercial.catalogo_productos_servicios(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
             INCREMENT 1
             MINVALUE  1
             MAXVALUE 2147483647
             START 1
             ) ,
     lis_cod_act varchar(50) COLLATE \"pg_catalog\".\"default\",
     lis_cod_prod varchar(255) COLLATE \"pg_catalog\".\"default\",
     lis_des_prod varchar(1000) COLLATE \"pg_catalog\".\"default\",
     lis_nan_prod text COLLATE \"pg_catalog\".\"default\",
     
     
     CONSTRAINT \"pk_catalogo_productos_servicios\" PRIMARY KEY (\"id\")
             );";
                $oCon->QueryT($sqltb);
            }
        }



        //CAMPO FECHA DE CADUCIDAD

        //CAMPO CUENTA CLPV SOLICITUD CASH
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'dncr_lote_fcad' AND TABLE_NAME = 'saedncs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedncs add dncr_lote_fcad date";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO PARA GUARDAR EL ID DEL CONTRATO EN LA DMCC / PRINCIPALMENTE PARA ISP
        $sqlgein = "SELECT count(*) as conteo
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE COLUMN_NAME = 'dmcc_cod_contr' AND TABLE_NAME = 'saedmcc'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saedmcc add dmcc_cod_contr int4 default 0";
            $oCon->QueryT($sqlalter);
        }


        //TABLA VENTA MOVIL DEVOLUCIONES - SOLICITUDES
        $sqlinf = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE  TABLE_NAME = 'parametro_saencrs' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.parametro_saencrs(
         id_para_ncrs int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
          INCREMENT 1
          MINVALUE  1
          MAXVALUE 2147483647
          START 1
          ) ,
  empr_cod_empr int4,
  sucu_cod_sucu int4,
  secu_sn varchar(50) COLLATE \"pg_catalog\".\"default\",
  secuencial varchar(50) COLLATE \"pg_catalog\".\"default\",
  digitos int4,
  CONSTRAINT \"pk_parametro_saencrs\" PRIMARY KEY (\"id_para_ncrs\")
          );";
            $oCon->QueryT($sqltb);
        }

        ///TABLA TRANSACIONES PEDIDO DESPACHO MODELO CLINICO

        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'transaccion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.transaccion (
            id_bod_prod int8 NOT NULL DEFAULT '0'::bigint,
            empr_cod_empr int8,
            sucu_cod_sucu int8,
            bode_cod_bode int8,
            tran_cod_transf varchar(50) COLLATE \"pg_catalog\".\"default\",
            CONSTRAINT idx_14176668_primary PRIMARY KEY (id_bod_prod));";
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'usuario_deriva_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add usuario_deriva_sn varchar(1)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'reimpresion_sn' AND TABLE_NAME = 'usuario' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add reimpresion_sn varchar(1) default 'S'";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_cfe_contr' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add column empr_cfe_contr varchar(1) default 'S'";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_mone_fxfp' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add column empr_mone_fxfp varchar(1) default 'N'";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_asum_igtf' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add column empr_asum_igtf varchar(1) default 'N'";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO CUERPO MENSAJE - ASUNTO CORREOS

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'cuerpo_mensaje' AND TABLE_NAME = 'config_email' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.config_email add cuerpo_mensaje text";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'asunto' AND TABLE_NAME = 'config_email' and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.config_email add asunto text";
            $oCon->QueryT($sqlalter);
        }


        //TABLA BIOMETRICO 
        ///TABLA TURNOS -EMPELADO

        $sqlinf = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE  TABLE_NAME = 'turnos_empleado' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.turnos_empleado(
        id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
          INCREMENT 1
          MINVALUE  1
          MAXVALUE 2147483647
          START 1
          ),
            id_empleado varchar(25) COLLATE \"pg_catalog\".\"default\",
            id_turnos text COLLATE \"pg_catalog\".\"default\",
            user_web   int,
            empr_cod_empr int,
            created_at timestamp,
            user_created int,
            updated_at timestamp,
            user_updated int, 
            CONSTRAINT \"id_turno_empleado\" PRIMARY KEY (\"id\")
          );";
            $oCon->QueryT($sqltb);
        }
        //TABAL NUEVA CASH PROVVEDORES

        ///TABLA CUENTAS BANCARIAS PERU

        $sqlinf = "SELECT count(*) as conteo
           FROM INFORMATION_SCHEMA.COLUMNS
           WHERE  TABLE_NAME = 'cuentas_cash' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.cuentas_cash(
               id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
               INCREMENT 1
               MINVALUE  1
               MAXVALUE 2147483647
               START 1
               ),
               cash_cod_clpv int,
               cash_ruc_clpv varchar(25) COLLATE \"pg_catalog\".\"default\",
               cash_cod_empr int,
               cash_num_cuen varchar(50) COLLATE \"pg_catalog\".\"default\",
               cash_tip_cuen varchar(5)  COLLATE \"pg_catalog\".\"default\",
               cash_cod_ban int,
               cash_cod_int  varchar(50) COLLATE \"pg_catalog\".\"default\",
               cash_cod_iden int,
               cash_cod_mone int,
               cash_est_del  varchar(1)  COLLATE \"pg_catalog\".\"default\",
               cash_created_at timestamp,
               cash_user_created int,
               cash_updated_at timestamp,
               cash_user_updated int, 
               cash_deleted_at timestamp,
               cash_user_deleted int,  
               CONSTRAINT \"id_cuentas_cash\" PRIMARY KEY (\"id\")
               );";
            $oCon->QueryT($sqltb);
        }




        ///TABLA CONTEO FISICO RAPIDO
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'conteo_fisico_rapido' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.conteo_fisico_rapido(
             id serial, 
             cod_empr int4,
             cod_sucu int4,
             cod_bode int4,
             cod_prod varchar(255),
             unidad_medida int4,
             cantidad int4,
             stock_fecha_conteo int4,
             estado varchar(10),
             fecha_conteo timestamp,
             id_usuario int4,
             minv_num_comp int4,
             CONSTRAINT \"id_fisico_rapido\" PRIMARY KEY (\"id\")
             );";
            $oCon->QueryT($sqltb);
        }


        // TABLA CABECERA PACKING 
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'packing_pedido' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.packing_pedido(
             id serial, 
             cod_empr int4,
             cod_sucu int4,
             cod_clpv int4,
             nom_clpv varchar(255),
             comentario varchar(255),
             estado varchar(10),
             fecha timestamp,
             fecha_packing date,
             id_usuario int4,
             cod_fact int4,
             CONSTRAINT \"id_packing_pedido\" PRIMARY KEY (\"id\")
             );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA CONTEO FISICO PACKING
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'detalle_packing_pedido' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.detalle_packing_pedido(
              id serial, 
              cod_empr int4,
              cod_sucu int4,
              cod_bode int4,
              cod_prod varchar(255),
              id_packing_pedido int4,
              lote_serie varchar(255),
              fecha_elab date,
              fecha_cad date,
              unidad_medida int4,
              cantidad int4,
              stock_fecha_conteo int4,
              estado varchar(10),
              fecha_server timestamp,
              fecha_conteo timestamp,
              seleccionado_sn varchar(255),
              observacion varchar(255),
              id_usuario int4,
              cod_dfac int4,
              CONSTRAINT \"id_detalle_packing_pedido\" PRIMARY KEY (\"id\")
              );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA CONTEO FISICO RAPIDO HISTORIAL
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'conteo_fisico_rapido_historial' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.conteo_fisico_rapido_historial(
              id int4, 
              cod_empr int4,
              cod_sucu int4,
              cod_bode int4,
              cod_prod varchar(255),
              unidad_medida int4,
              cantidad int4,
              stock_fecha_conteo int4,
              estado varchar(10),
              fecha_conteo timestamp,
              id_usuario int4,
              minv_num_comp int4
              );";
            $oCon->QueryT($sqltb);
        }




        //TABLA HISTORIAL FACTURACION ELECTRONICA
        $sqlinf = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'historico_merma' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.historico_merma (
                        id serial PRIMARY KEY,
                        num_comp int4,
                        cod_dmov int4,
                        cod_prod varchar(255),
                        cantidad float,
                        id_usuario int4,
                        id_usuario_actualiza int4,
                        fecha_server timestamp,
                        fecha_server_actualiza timestamp
                        )
                        ;";
            $oCon->QueryT($sqltb);
        }




        //TABLA HISTORIAL FACTURACION ELECTRONICA
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'devolucion_merma_proveedor' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.devolucion_merma_proveedor (
                  id serial PRIMARY KEY,
                  num_comp int4,
                  cod_dmov int4,
                  cod_prod varchar(255),
                  cod_clpv int4,
                  cantidad float,
                  id_usuario int4,
                  id_usuario_actualiza int4,
                  fecha_server timestamp,
                  fecha_server_actualiza timestamp
                  )
                  ;";
            $oCon->QueryT($sqltb);
        }


        // CAMPO PARA GUARDAR NUM_COMP DEL MOVIMIENTO DE TRANSFERENCIA 
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'num_comp_transf' AND TABLE_NAME = 'historico_merma'  AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.historico_merma add num_comp_transf int4";
            $oCon->QueryT($sqlalter);
        }



        //CAMPO CUENTA CLPV SOLICITUD CASH
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'codigo_unico_registro' AND TABLE_NAME = 'conteo_fisico_rapido'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table conteo_fisico_rapido add codigo_unico_registro int4";
            $oCon->QueryT($sqlalter);

            $sqlalter = "ALTER table conteo_fisico_rapido_historial add codigo_unico_registro int4";
            $oCon->QueryT($sqlalter);
        }


        //CAMPO CUENTA CLPV SOLICITUD CASH
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fecha_server' AND TABLE_NAME = 'conteo_fisico_rapido'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table conteo_fisico_rapido add fecha_server timestamp";
            $oCon->QueryT($sqlalter);

            $sqlalter = "ALTER table conteo_fisico_rapido_historial add fecha_server timestamp";
            $oCon->QueryT($sqlalter);
        }


        ///TABLA PARA LOS COMBOS DE LA FACTURACION A PARTIR DE N UNIDADES TOMAR PVP2
        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'combos_productos' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.combos_productos(
                 id SERIAL, 
                 id_empresa int4,
                 id_sucursal int4,
                 id_bodega int4,
                 numero_pivote int4,
                 cod_nomp int4,
                 codigos_productos_array varchar(10000),
                 codigo_unico_grupo_prod varchar(255),
                 activo_sn varchar(10),
                 usuario int4,
                 fecha_server timestamp
                 );";
            $oCon->QueryT($sqltb);
        }

        // SUCURSALES DE SERVIENTREGA
        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'agencias_laarcourier' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.agencias_laarcourier(
                 id SERIAL, 
                 id_empresa int4,
                 id_sucursal int4,

                 codigo_agencia int4,
                 provincia varchar(100000),
                 ciudad varchar(100000),
                 nombre_punto_retail varchar(100000),
                 direccion varchar(100000),
                 representante varchar(1000),
                 codigo_postal varchar(255),
                 contacto1 varchar(50),
                 contacto2 varchar(50),
                 correo varchar(255),

                 usuario int4,
                 fecha_server timestamp,

                 primary key (id)

                 );";
            $oCon->QueryT($sqltb);
        }


        // TABLA PARA GUARDAR EL PORTAFOLIO DE PRODUCTOS DE LOS CLIENTES
        $sqlinf = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = 'saeclpo' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE saeclpo(
                                            clpo_cod_clpo serial,
	
                                            clpo_cod_empr int4,
                                            clpo_cod_sucu int4,
                                            clpo_cod_clpv int4,
                                            clpo_cod_bode int4,
                                            clpo_cod_prod varchar(255),
                                            clpo_nom_prod varchar(255),
                                            clpo_prod_clpv varchar(255),
                                            clpo_cod_unid int4,
                                            clpo_cod_nomp int4,
                                            clpo_lista_precios varchar(100000),

                                                
                                            clpo_usua_crea int4,
                                            clpo_fec_crea timestamp,
                                            clpo_usua_act int4,
                                            clpo_fec_act timestamp,

                                            primary key (clpo_cod_clpo)
                                    );";
            $oCon->QueryT($sqltb);
        }


        // TABLA PARA GUARDAR LAS AREAS DE FACTURACION
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'saeubar' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE saeubar(
                            ubar_cod_ubar serial,

                            ubar_cod_empr int4,
                            ubar_cod_sucu int4,
                            ubar_cod_bode int4,
                            ubar_nom_ubar varchar(255),
                            ubar_des_ubar varchar(2555),
                            ubar_est_ubar varchar(5),

                            ubar_usua_crea int4,
                            ubar_fec_crea timestamp,
                            ubar_usua_act int4,
                            ubar_fec_act timestamp,

                             primary key (ubar_cod_ubar)
                     );";
            $oCon->QueryT($sqltb);
        }

        // TABLA PARA GUARDAR LAS MESAS DE CADA AREA
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'saemesar' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE saemesar(
                            mesar_cod_mesar serial PRIMARY KEY,

                            mesar_cod_empr int4,
                            mesar_cod_sucu int4,
                            mesar_cod_bode int4,
                            mesar_cod_ubar  INTEGER REFERENCES saeubar(ubar_cod_ubar),
                            mesar_num_mesar varchar(255),
                            mesar_nom_mesar varchar(255),
                            mesar_des_mesar varchar(2555),
                            mesar_est_mesar varchar(5),
                            mesar_act_est varchar(5),
                            mesar_ubi_mesar varchar(255),
                            mesar_tip_mesar varchar(10),
                            mesar_num_pers float,

                            mesar_usua_crea int4,
                            mesar_fec_crea timestamp,
                            mesar_usua_act int4,
                            mesar_fec_act timestamp
                     );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA PARA LOS MENSAJES ENTRE EL VENDEDOR Y EL BORDADOR O ESTAMPADOR
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'chat_mensajes_factura' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.chat_mensajes_factura(
              id SERIAL, 
              id_empresa int4,
              id_sucursal int4,

              cod_factura int4,
              mensaje varchar(1000000),
              ajuntos varchar(1000000),

              usuario int4,
              fecha_server timestamp
              );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA PARA LOS MENSAJES ENTRE EL VENDEDOR Y EL BORDADOR O ESTAMPADOR
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'balance_ganacias_perdidas' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.balance_ganacias_perdidas(
              id SERIAL, 
              id_empresa int4,
              id_sucursal int4,

              otros_gastos varchar(1000000),
              resultados_explosicion varchar(1000000),
              participaciones varchar(1000000),

              usuario int4,
              fecha_server timestamp
              );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA PARA ALMACENAR LA INFORMACION DE ENVIO DE LOS PROVEEDORES DE ENVIO
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'datos_envio_serv_entrega' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.datos_envio_serv_entrega(
              id SERIAL, 
              id_empresa int4,
              id_sucursal int4,

              identificacion varchar(20),
              apellidos varchar(500),
              nombres varchar(500),
              calle_principal varchar(10000),
              calle_secundaria varchar(10000),
              referencia varchar(10000),
              numero_casa varchar(255),
              codigo_postal varchar(255),
              telefono varchar(255),
              celular varchar(255),
              ciudad_origen varchar(255),
              tipo varchar(255),
              observacion varchar(255),

              usuario int4,
              fecha_server timestamp
              );";
            $oCon->QueryT($sqltb);
        }


        ///TABLA PARA los desoacho de los productos de facturacion
        $sqlinf = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE  TABLE_NAME = 'despacho_prductos_fact' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.despacho_prductos_fact(
                  id SERIAL, 
                  id_empresa int4,
                  id_sucursal int4,

                  numero_factura int4,

                  cod_bode_fact int4,
                  cod_prod_fact varchar(255),
                  cant_prod_fact float,

                  cod_bode_desp int4,
                  cod_prod_desp varchar(255),
                  cant_prod_desp float,

                  estado varchar(10),

                  usuario int4,
                  fecha_server timestamp
                  );";
            $oCon->QueryT($sqltb);
        }

        ///TABLA PARA LA INFORMACION DEL PROVEEDOR DE ENVIO
        $sqlinf = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE  TABLE_NAME = 'informacion_envio_fact' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.informacion_envio_fact(
                  id SERIAL, 
                  id_empresa int4,
                  id_sucursal int4,

                  cod_factura int4,
                  nombres_completos varchar(500),
                  apellidos_completos varchar(500),
                  cedula int4,
                  ciudad varchar(255),
                  calle_principal varchar(1000),
                  calle_secundaria varchar(1000),
                  referencia varchar(1000),
                  telefono varchar(20),
                  celular varchar(20),
                  cooreo varchar(255),

                  estado varchar(10),
                  usuario int4,
                  fecha_server timestamp
                  );";
            $oCon->QueryT($sqltb);
        }

        ///TABLA PARA LOS COMBOS DE LA FACTURACION A PARTIR DE N UNIDADES TOMAR PVP2
        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'grupo_productos_combo' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.grupo_productos_combo(
                 id SERIAL, 
                 id_empresa int4,
                 id_sucursal int4,
                 id_bodega int4,
                 codigos_productos_array varchar(10000),
                 usuario int4,
                 fecha_server timestamp,
                 codigo_unico_ident varchar(255)
                 );";
            $oCon->QueryT($sqltb);
        }

        ///TABLA PARA LOS PROVEEDORES DE SERVICIO DE ENTREGA
        $sqlinf = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE  TABLE_NAME = 'proveedores_servicio_entrega' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.proveedores_servicio_entrega(
                 id SERIAL, 
                 id_empresa int4,
                 id_sucursal int4,
                 nombre varchar(255),
                 descripcion varchar(255),
                 peso_desde float,
                 peso_hasta float,
                 costo_base float,
                 costo_adicional float,
                 unidades_base int4,
                 usuario int4,
                 fecha_server timestamp
                 );";
            $oCon->QueryT($sqltb);
        }


        //CAMPO CUENTA CLPV SOLICITUD CASH
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'apag_cuen_clpv' AND TABLE_NAME = 'saeapag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeapag add apag_cuen_clpv int2";
            $oCon->QueryT($sqlalter);
        }


        //CAMPO ID PEDIDO JSON TIENDA VIRTUAL FOOTLOOSE

        //valida si existe el schema
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'json_cab_pedi' AND TABLE_NAME = 'apipedi' AND TABLE_SCHEMA='apicomercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter != 0) {

            //CAMPO PARA GUARDAR ID PEDIDO JSON
            $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pedi_id_json' AND TABLE_NAME = 'saepedf' AND TABLE_SCHEMA='public'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saepedf add pedi_id_json int2";
                $oCon->QueryT($sqlalter);
            }
        }


        //CAMPOS VENTA MOVIL MAPA
        //CAMPO HORA VENTA MOVIL
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'ncre_fec_hor' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_fec_hor timestamp";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO HORA VENTA MOVIL

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_fec_hor' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_fec_hor timestamp";
            $oCon->QueryT($sqlalter);
        }


        //CAMPOS ORDENES DE COMPRA DE SERVICIOS

        //CAMPOS FECHA CADUCIDAD ORDENES DE SERVICIO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fec_cad' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saeminv add minv_fec_cad date";
            $oCon->QueryT($sqlalter);
        }





        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'pedf_your_order' AND TABLE_NAME = 'saepedf'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepedf ADD COLUMN pedf_your_order VARCHAR(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'pedf_dir_envi' AND TABLE_NAME = 'saepedf'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepedf ADD COLUMN pedf_dir_envi varchar(1055);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'pedf_incoterms_pedf' AND TABLE_NAME = 'saepedf'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepedf ADD COLUMN pedf_incoterms_pedf varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'pedf_terms_pago' AND TABLE_NAME = 'saepedf'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepedf ADD COLUMN pedf_terms_pago varchar(1055);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        /*
        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'varpr_flujo_adi' AND TABLE_NAME = 'saevarpr'";
                        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saevarpr ADD COLUMN varpr_flujo_adi varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }
*/

        //CAMPOS minv_seg_minv 

        $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'minv_seg_minv' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeminv ADD COLUMN minv_seg_minv VARCHAR(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }
        /*
        $sql_minv = "ALTER TABLE saeminv ADD COLUMN IF NOT EXISTS minv_seg_minv VARCHAR(255) ;";
        $oCon->QueryT($sql_minv);
        */

        //CAMPOS CALCULO IMPUESTO PARA BOLIVIA
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'pais_imp_metodo' AND TABLE_NAME = 'saepais'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepais ADD COLUMN pais_imp_metodo INT NOT NULL DEFAULT 0;";
            $oCon->QueryT($sqlalter);

            $sqlalter = "COMMENT ON COLUMN saepais.pais_imp_metodo IS 'Campo para configurar el metodo del impuesto para Bolivia 0 es nominal y 1 es tasa efectiva';";
            $oCon->QueryT($sqlalter);
        }
        //TABLA ANULACION 
        /* $sqlgein="SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_fec_cad' AND TABLE_NAME = 'saeminv_an'";
        $ctralter=consulta_string($sqlgein,'conteo',$oCon,0);

        if($ctralter==0){
                $sqlalter="alter table saeminv_an add minv_fec_cad date";
                $oCon->QueryT($sqlalter);
            
        }*/

        //CAMPO IDENTIFICACION TECNICO INTERNO Y EXTERNO PARA CUADRILLAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empl_tec_ext' AND TABLE_NAME = 'saeempl'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempl add empl_tec_ext VARCHAR(1) DEFAULT 'S'";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO HABILITACION DE CUOTAS ISP
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'habilita_cuotas' AND TABLE_NAME = 'usuario'  AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add habilita_cuotas VARCHAR(1) DEFAULT 'N'";
            $oCon->QueryT($sqlalter);
        }





        //CAMPOS ELIMINACION COBROS VENTA MOVIL


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_eli_gein' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_eli_gein int2";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_fec_eli' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_fec_eli timestamp";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO ESTADO CHEQUES PROTESTADOS

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dpag_est_chp' AND TABLE_NAME = 'saedpag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedpag add dpag_est_chp varchar(1) default 'N';";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_est_chp' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_est_chp varchar(1) default 'N';";
            $oCon->QueryT($sqlalter);
        }




        //CAMPO LIQUIDACIONES EN COMPRAS BUSQUEDA ITEMS
        $sqlgein = "SELECT count(*) as conteo
	FROM INFORMATION_SCHEMA.COLUMNS
	WHERE COLUMN_NAME = 'pccp_par_liq' AND TABLE_NAME = 'saepccp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepccp add pccp_par_liq varchar(1) default 3;";
            $oCon->QueryT($sqlalter);
        }


        //pcon cuenta de utilidad
        $sqlgein = "SELECT count(*) as conteo
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE COLUMN_NAME = 'pcon_cta_utic' AND TABLE_NAME = 'saepcon'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepcon add pcon_cta_utic varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        //pcon cuenta de utilidad
        $sqlgein = "SELECT count(*) as conteo
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE COLUMN_NAME = 'pcon_cta_utic' AND TABLE_NAME = 'saepcon'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepcon add pcon_cta_utic varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        //pcon cuenta de perdida
        $sqlgein = "SELECT count(*) as conteo
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE COLUMN_NAME = 'pcon_cta_pedc' AND TABLE_NAME = 'saepcon'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepcon add pcon_cta_pedc varchar(255);";
            $oCon->QueryT($sqlalter);
        }






        //alter table saeguia para despachos agricola

        $sqlguia = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'guia_desp_minv' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlguia, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_desp_minv int";
            $oCon->QueryT($sqlalter);
        }

        //alter table saeguia 
        $sqlguia = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'guia_num_bult' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlguia, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_num_bult float";
            $oCon->QueryT($sqlalter);
        }

        //alter table saeguia 
        $sqlguia = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'guia_peso_neto' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlguia, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeguia add guia_peso_neto float";
            $oCon->QueryT($sqlalter);
        }




        //CAMPOS ASIENTO CONTABLE VENTA MOVIL COBROS

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_asto_ejer' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_asto_ejer int4;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'gein_asto_prdo' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_asto_prdo int4;";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS ASIENTO CONTABLE DEPOSITO


        $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'gein_asto_dep' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_asto_dep varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
       FROM INFORMATION_SCHEMA.COLUMNS
       WHERE COLUMN_NAME = 'gein_adep_ejer' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_adep_ejer int4;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
       FROM INFORMATION_SCHEMA.COLUMNS
       WHERE COLUMN_NAME = 'gein_adep_prdo' AND TABLE_NAME = 'saegein'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saegein add gein_adep_prdo int4;";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO REPORTE CONSOLIDADO

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bode_tip_bode' AND TABLE_NAME = 'saebode'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saebode add bode_tip_bode varchar(50);";
            $oCon->QueryT($sqlalter);
        }


        //CALCULAR PESO  PRODUCTO SN

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prod_peso_sn' AND TABLE_NAME = 'saeprod'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeprod add column prod_peso_sn varchar(1);";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS MODELO CLINICO
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'hoja008_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add hoja008_sn varchar(1);";
            $oCon->QueryT($sqlalter);
        }


        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'cheques_temp' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.cheques_temp (
                id_ch serial,
                cheque int4,
                banco varchar(100) COLLATE pg_catalog.default,
                fecha_vence date,
                girador varchar(100) COLLATE pg_catalog.default,
                cuenta varchar(50) COLLATE pg_catalog.default,
                valor numeric,
                factura varchar(50) COLLATE pg_catalog.default,
                detalle varchar(100) COLLATE pg_catalog.default,
                centro_act varchar(10) COLLATE pg_catalog.default,
                oculto varchar(1) COLLATE pg_catalog.default,
                clpv_cod_clpv varchar(10) COLLATE pg_catalog.default,
                num_fac varchar(20) COLLATE pg_catalog.default,
                valor_fac numeric
              );";
            $oCon->QueryT($sqltb);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'clpv_cod_clpv' AND TABLE_NAME = 'cheques_temp' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.cheques_temp add column clpv_cod_clpv int;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'oculto' AND TABLE_NAME = 'cheques_temp' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.cheques_temp add oculto varchar(10);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'codigo_iess' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.usuario add codigo_iess varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO REFERENCIA SOLICITUDES DE CASH
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'apag_num_refr' AND TABLE_NAME = 'saeapag' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "alter table saeapag alter column apag_num_refr SET DATA TYPE varchar(100);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }



        ///TABLA FORMATOS PDF PERU


        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeipdf' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.saeipdf(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
            INCREMENT 1
            MINVALUE  1
            MAXVALUE 2147483647
            START 1
            ),
            ipdf_cod_empr int,
            ipdf_tit_ipdf varchar(255) COLLATE \"pg_catalog\".\"default\",
            ipdf_det_ipdf text COLLATE \"pg_catalog\".\"default\",
            ipdf_tip_ipdf int,
            ipdf_est_ipdf varchar(1) COLLATE \"pg_catalog\".\"default\",
            ipdf_ord_ipdf int,
            ipdf_user_web   int,
            ipdf_created_at timestamp,
            ipdf_user_created int,
            ipdf_updated_at timestamp,
            ipdf_user_updated int, 
            ipdf_deleted_at timestamp,
            ipdf_user_deleted int, 
            ipdf_est_deleted varchar(1) default 'S', 
            CONSTRAINT \"id_ipdf\" PRIMARY KEY (\"id\")
            );";
            $oCon->QueryT($sqltb);
        }

        //TABLA NOTAS DESTADOS FINANCIEROS

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saenotasp' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = 'CREATE TABLE "public"."saenotasp" (
                "notasp_cod_notasp" serial,
                "notasp_cod_empr" int4,
                "notasp_sig_notasp" varchar(255) COLLATE "pg_catalog"."default",
                "notasp_nom_notasp" varchar(255) COLLATE "pg_catalog"."default",
                "notasp_det_notasp" varchar(255) COLLATE "pg_catalog"."default",
                "notasp_format_notasp" varchar(10) COLLATE "pg_catalog"."default",
                "notasp_cuen_array" varchar(10000000) COLLATE "pg_catalog"."default",
                "notasp_fec_crea" timestamp(6),
                "notasp_usu_crea" varchar(10) COLLATE "pg_catalog"."default",
                "notasp_fec_actu" timestamp(6),
                "notasp_usu_actu" varchar(10) COLLATE "pg_catalog"."default"
              )
              ;';
            $oCon->QueryT($sqltb);
        }

        // TABLA CONTROL CALIDAD PRODUCTOS
        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'recepcion_compra_eval' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE recepcion_compra_eval (
                id serial,
                id_empresa integer DEFAULT NULL,
                id_sucursal integer DEFAULT NULL,
                minv_num_comp integer DEFAULT NULL,
                cod_prod varchar(255) DEFAULT NULL,
                id_recepcion_parametro integer DEFAULT NULL,
                sn_estado varchar(1) DEFAULT NULL,
                observacion varchar(255) DEFAULT NULL,
                fecha_server timestamp DEFAULT NULL,
                lote_prod varchar(255) DEFAULT NULL,
                user_system varchar(255) DEFAULT NULL
              );";
            $oCon->QueryT($sqltb);
        }

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'recepcion_parametros' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE recepcion_parametros (
                id serial,
                id_empresa integer DEFAULT NULL,
                id_sucursal integer DEFAULT NULL,
                nombre_parametro varchar(255) DEFAULT NULL,
                fecha_server timestamp DEFAULT NULL
              );";
            $oCon->QueryT($sqltb);
        }


        //HASH FACTURACION NOTAS DE CREDITO

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_cod_hash' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_cod_hash varchar(1000);";
            $oCon->QueryT($sqlalter);
        }

        // FACTURA CANCELADA
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_val_can' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_val_can float;";
            $oCon->QueryT($sqlalter);
        }

        // FACTURA CANCELADA
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'fact_adj_credito' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_adj_credito varchar(1000000);";
            $oCon->QueryT($sqlalter);
        }

        // FACTURA CANCELADA
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'fact_retiro_ofi' AND TABLE_NAME = 'saefact'"; // 
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_retiro_ofi varchar(5);";
            $oCon->QueryT($sqlalter);
        }

        // FACTURA CANCELADA
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'fact_retiro_bode' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_retiro_bode int4;";
            $oCon->QueryT($sqlalter);
        }


        // CAMPO PARA GUARDAR LOS ADJUNTOS CARGADOS EN EL SERVICIO DE BORDADO
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fact_adj_serv' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_adj_serv varchar(100000);";
            $oCon->QueryT($sqlalter);
        }

        // CAMPO PARA GUARDAR LOS ADJUNTOS CARGADOS EN EL SERVICIO DE BORDADO
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fact_serv_sn' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_serv_sn varchar(5);";
            $oCon->QueryT($sqlalter);
        }

        // CAMPO PARA GUARDAR LOS SERVICIOS APROBADOS EN LA FACTURA
        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'fact_serv_aprb' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_serv_aprb varchar(10000);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'saencre'";

        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_cod_hash' AND TABLE_NAME = 'saencre'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table saencre add ncre_cod_hash varchar(1000);";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_fech_docu' AND TABLE_NAME = 'saencre'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER table saencre add column ncre_fech_docu timestamp;";
                $oCon->QueryT($sqlalter);
            }
        }


        //KEY GOOGLE MAPS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_key_maps' AND TABLE_NAME = 'saeempr' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add empr_key_maps TEXT default 'AIzaSyB8pAD65yn2Qtj_DTowH8xUUkUB6U_SRN0'";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS DETRACCIONES


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fact_cod_detra' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_cod_detra  varchar(10);";
            $oCon->QueryT($sqlalter);
        }


        ///TABLA INFORMACION ADICIONAL -XML

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeixml' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.saeixml(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
            INCREMENT 1
            MINVALUE  1
            MAXVALUE 2147483647
            START 1
            ),
            ixml_cod_empr int,
            ixml_tit_ixml varchar(255) COLLATE \"pg_catalog\".\"default\",
            ixml_det_ixml text COLLATE \"pg_catalog\".\"default\",
            ixml_est_ixml varchar(1) COLLATE \"pg_catalog\".\"default\",
            ixml_ord_ixml int,
            ixml_user_web   int,
            ixml_created_at timestamp,
            ixml_user_created int,
            ixml_updated_at timestamp,
            ixml_user_updated int, 
            ixml_deleted_at timestamp,
            ixml_user_deleted int, 
            ixml_est_deleted varchar(1) default 'S', 
            CONSTRAINT \"id_ixml\" PRIMARY KEY (\"id\")
            );";
            $oCon->QueryT($sqltb);
        }

        /**FACTURACION BOOSTRAP**/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'para_punt_emi' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_punt_emi varchar(1) DEFAULT 'N'::character varying;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_cod_ref' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_cod_ref int";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_tip_impr' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_tip_impr int4 DEFAULT 1;";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_nomcome_empr' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_nomcome_empr varchar(255);";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_tip_comp' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_tip_comp int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_tip_agri' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_tip_agri int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpv_ref_sn' AND TABLE_NAME = 'saeclpv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeclpv ADD clpv_ref_sn VARCHAR(1);";
            $oCon->QueryT($sqlalter);
        }

        /**NOTA DE CREDITO**/
        //CAMPOS APROBACION
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'ncre_est_ncre' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_est_ncre varchar(2) default 'P'";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS APROBACION
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'ncre_user_apro' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_user_apro int";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'ncre_fec_apro' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_fec_apro timestamp";
            $oCon->QueryT($sqlalter);
        }

        //CAMPOS ANULACION
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'ncre_user_anu' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_user_anu int";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'ncre_fec_anu' AND TABLE_NAME = 'saencrs'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saencrs add ncre_fec_anu timestamp";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'dfac_obj_iva' AND TABLE_NAME = 'saedfac'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedfac add dfac_obj_iva int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'dfac_exc_iva' AND TABLE_NAME = 'saedfac'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saedfac add dfac_exc_iva int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        //CREACION DE TABLAS PARA FORMULARIO COMERCIAL RECLAMOS

        //TIPO RECLAMO
        $sqlinf = "SELECT count(*) as conteo
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE  TABLE_NAME = 'tip_recl' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_recl(
				id serial,
				sigla varchar(50),
				tipo_recl varchar(100));";
            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_recl(sigla,tipo_recl) values('ABN','ABONADO');";
            $oCon->QueryT($in1);
            $in2 = "INSERT into comercial.tip_recl(sigla,tipo_recl) values('USU','USUARIO');";
            $oCon->QueryT($in2);
            $in3 = "INSERT into comercial.tip_recl(sigla,tipo_recl) values('REP','REPRESENTANTE');";
            $oCon->QueryT($in3);
        } // FIN TIPO RECLAMO

        //TIPO PROPUESTA
        $sqlinf = "SELECT count(*) as conteo
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE  TABLE_NAME = 'tip_prop' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_prop(
				id serial,
				sigla varchar(50),
				tipo_prop varchar(100))";
            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_prop(sigla,tipo_prop) values('DES','DESCUENTO')";
            $oCon->QueryT($in1);
            $in2 = "INSERT into comercial.tip_prop(sigla,tipo_prop) values('EXO','EXONERACION');";
            $oCon->QueryT($in2);
            $in3 = "INSERT into comercial.tip_prop(sigla,tipo_prop) values('REV','REVISION TECNICA')";
            $oCon->QueryT($in3);
            $in4 = "INSERT into comercial.tip_prop(sigla,tipo_prop) values('REP','REPARACION');";
            $oCon->QueryT($in4);
            $in5 = "INSERT into comercial.tip_prop(sigla,tipo_prop) values('OTR','OTRO');";
            $oCon->QueryT($in5);
        } // FIN PROPUESTA

        //TIPO SAP
        $sqlinf = "SELECT count(*) as conteo
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE  TABLE_NAME = 'tip_sap' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_sap(
				id serial,
				sigla varchar(50),
				tipo_sap varchar(100))";
            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_sap(sigla,tipo_sap) values('EMI','EMISION RESOLUCION')";
            $oCon->QueryT($in1);
            $in2 = "INSERT into comercial.tip_sap(sigla,tipo_sap) values('NOT','NOTIFICACION');";
            $oCon->QueryT($in2);
            $in3 = "INSERT into comercial.tip_sap(sigla,tipo_sap) values('ELE','ELEVACION RECURSO')";
            $oCon->QueryT($in3);
        } // FIN SAP

        //TIPO RECURSO
        $sqlinf = "SELECT count(*) as conteo
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE  TABLE_NAME = 'tip_recu' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_recu(
				id serial,
				sigla varchar(50),
				tipo_rec varchar(100))";

            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_recu(sigla,tipo_rec) values('APE','APELACION')";
            $oCon->QueryT($in1);
            $in2 = "INSERT into comercial.tip_recu(sigla,tipo_rec) values('QUJ','QUEJA');";
            $oCon->QueryT($in2);
            $in3 = "INSERT into comercial.tip_recu(sigla,tipo_rec) values('APQ','APELACION Y QUEJA')";
            $oCon->QueryT($in3);
        } // FIN RECURSO

        //TIPO RESOLUCION
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'tip_reso' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_reso(
				id serial,
				sigla varchar(50),
				tipo_reso varchar(100))";
            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_reso(sigla,tipo_reso) values('IND','INADMISIBLE')";
            $oCon->QueryT($in1);
            $in2 = "	INSERT into comercial.tip_reso(sigla,tipo_reso) values('IMP','IMPROCEDENTE');";
            $oCon->QueryT($in2);
            $in3 = "INSERT into comercial.tip_reso(sigla,tipo_reso) values('FUN','FUNDADO');";
            $oCon->QueryT($in3);
            $in4 = "INSERT into comercial.tip_reso(sigla,tipo_reso) values('PFN','PARCIALMENTE FUNDADO');";
            $oCon->QueryT($in4);
            $in5 = "INSERT into comercial.tip_reso(sigla,tipo_reso) values('INF','INFUNDADO');";
            $oCon->QueryT($in5);
            $in6 = "INSERT into comercial.tip_reso(sigla,tipo_reso) values('ARC','ARCHIVO');	";
            $oCon->QueryT($in6);
        } // FIN RESOLUCION


        //TIPO OBJETO QUEJA
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'tip_queja' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table comercial.tip_queja(
				id serial,
				sigla varchar(50),
				tipo_queja varchar(100))";
            $oCon->QueryT($sql_1);
            $in1 = "INSERT into comercial.tip_queja(sigla,tipo_queja) values('SAP','SAP')";
            $oCon->QueryT($in1);
            $in2 = "	INSERT into comercial.tip_queja(sigla,tipo_queja) values('CDR','CORTE DURANTE RECLAMO');";
            $oCon->QueryT($in2);
            $in3 = "	INSERT into comercial.tip_queja(sigla,tipo_queja) values('CBR','COBRO DURANTE RECLAMO');";
            $oCon->QueryT($in3);
            $in4 = "INSERT into comercial.tip_queja(sigla,tipo_queja) values('NRC','NEGATIVA A RECIBIR RECLAMO, RECURSO O QUEJA');";
            $oCon->QueryT($in4);
            $in5 = "INSERT into comercial.tip_queja(sigla,tipo_queja) values('OTR','OTRO');";
            $oCon->QueryT($in5);
        } // FIN OBJETO QUEJA


        //RECLAMOS
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'reclamos' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE comercial.reclamos(
                id serial,
                id_clpv int,
                id_propuesta int,
                otr_propuesta varchar(100),
                id_recurso int,
                id_sap int,
                id_sent_res int,
                id_obj_queja int,
                otr_queja varchar(100)
                );";
            $oCon->QueryT($sql_1);
        } // FIN RECLAMOS

        //*********TABLA SCORE CLIENTES */
        $table_name = '';
        $sql = "SELECT table_name FROM information_schema.columns 
                WHERE table_name='clientes_score' 
                AND table_schema = 'comercial'";
        $table_name = consulta_string_func($sql, 'table_name', $oCon, 0);

        if (strlen($table_name) <= 1) {
            $sqlCreateTable = 'CREATE TABLE "comercial"."clientes_score" (
                                        "id" SERIAL PRIMARY KEY,
                                        "id_contrato" INT,
                                        "id_clpv" INT,
                                        "nombre" TEXT,
                                        "ruc" VARCHAR(20),
                                        "codigo_ciiu" VARCHAR(20),
                                        "actividad_economica" TEXT,
                                        "fecha_inicio_act" VARCHAR(100),
                                        "fecha_suspen_defi" VARCHAR(100),
                                        "estado_contribuyente" VARCHAR(50),
                                        "clase_contribuyente" VARCHAR(50),
                                        "direccion" TEXT,
                                        "numero_establecimiento" VARCHAR(50),
                                        "obliga_contabilidad" VARCHAR(10),
                                        "nombre_fantasia" TEXT,
                                        "valor_score" INT,
                                        "fecha_score" timestamp(6),
                                        "fecha_server" timestamp(6),
                                        "user_id" INT
                                    );';
            $oCon->QueryT($sqlCreateTable);
            $oCon->Free();
        };
        //------------------------

        //RECIBO DIGITAL
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'fpago_digital' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table fpago_digital(
                id serial,
                id_empresa int,
                nom_fpago varchar(300),
                fpago_descripcion char(1000),
                img varchar(300),
                promocion varchar(300),
                tipo varchar(1)
                );";
            $oCon->QueryT($sql_1);
        } // FIN RECIBO DIGITAL


        //PARAMETRIZACION DE CUENTAS

        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'conf_cuentas' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE table conf_cuentas(
                id serial,
                nombre VARCHAR(100),
                cod_cuenta varchar(100),
                id_empresa int,
                id_sucursal int
                );";
            $oCon->QueryT($sql_1);
        } // FIN RECLAMOS


        //PARAMETRIZACION TALENTO HUMANO
        $sqlinf = "SELECT count(*) as conteo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE COLUMN_NAME = 'empr_dig_celu' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = 'ALTER TABLE "public"."saeempr"
  ADD COLUMN "empr_dig_celu" int2 DEFAULT 10';
            $oCon->QueryT($sql_1);

            $sql_1 = 'COMMENT ON COLUMN "public"."saeempr"."empr_dig_celu" IS \'PARAMETRO DIGITOS CELULAR FICHA EMPLEADO\';';
            $oCon->QueryT($sql_1);
        }

        $sqlinf = "SELECT count(*) as conteo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE COLUMN_NAME = 'empr_for_rdep' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = 'ALTER TABLE "public"."saeempr"
  ADD COLUMN "empr_for_rdep" varchar(1) DEFAULT \'S\'';
            $oCon->QueryT($sql_1);

            $sql_1 = 'COMMENT ON COLUMN "public"."saeempr"."empr_for_rdep" IS \'PARAMETRO SN MUESTRA CAMPOS PARA MODULO RUBROS REFERENTE A FORMULARIO 107\';';
            $oCon->QueryT($sql_1);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_tip_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_tip_oemp varchar(1);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_peri_frem' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_peri_frem numeric(6);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_dete_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_dete_oemp numeric(18)  DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_decu_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_decu_oemp numeric(18) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_fond_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_fond_oemp numeric(18) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_imre_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_imre_oemp numeric(18) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'frem_esta_oemp' AND TABLE_NAME = 'saefrem' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table public.saefrem add frem_esta_oemp numeric(1) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(table_name) conteo FROM information_schema.columns 
                    WHERE table_name='saeeps' 
                     AND table_schema = 'public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "CREATE TABLE public.saeeps (
                          eps_cod_eps SERIAL PRIMARY KEY,
                          eps_cod_empr varchar(1) COLLATE pg_catalog.default,
                          eps_nom_eps varchar(255) COLLATE pg_catalog.default,
                          eps_val_eps numeric(18,2),
                          eps_per_eps int4,
                          usuario int2,
                          eps_act_sn varchar(1) COLLATE pg_catalog.default DEFAULT 'S'::character varying,
                          created_at timestamp(6),
                          updated_at timestamp(6)
                        )";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        //FIN TALENTO HUMANO


        /**
         * ENVIO A LA DIAN
         */

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_dataico_id' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_dataico_id varchar(50);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_dataico_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_dataico_token varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        // INICIO CREACION DE COLUMNAS SIIGO

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_api_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_api_url varchar(200);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_username' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_username varchar(100);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_access_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_access_token varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_partnerid' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_partnerid varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_autoenvio' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_autoenvio varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_siigo_autoenvio_mail' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_siigo_autoenvio_mail varchar(2);";
            $oCon->QueryT($sqlalter);
        }


        // saevend
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'vend_cod_siigo' AND TABLE_NAME = 'saevend'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saevend ADD vend_cod_siigo varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        // saefact
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_id' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_name' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_name varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_prefix' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_prefix varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_number' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_number varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_date' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_date varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_envio' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_envio text;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_siigo_respuesta' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_siigo_respuesta text;";
            $oCon->QueryT($sqlalter);
        }
        // dfac

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dfac_siigo_id' AND TABLE_NAME = 'saedfac'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedfac ADD dfac_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        // saencre
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_id' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_name' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_name varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_number' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_number varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_date' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_date varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_envio' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_envio text;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'ncre_siigo_respuesta' AND TABLE_NAME = 'saencre'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saencre ADD ncre_siigo_respuesta text;";
            $oCon->QueryT($sqlalter);
        }
        // dncr

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dncr_siigo_id' AND TABLE_NAME = 'saedncr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedncr ADD dncr_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        // saeclpv
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'clpv_siigo_id' AND TABLE_NAME = 'saeclpv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeclpv ADD clpv_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        // saefxfp
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_siigo_id' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        //::: CAMBIOS PARA VENEZUELA :::
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_cod_mone' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_cod_mone INT DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_val_tcam' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_val_tcam DECIMAL(16,6) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_fec_tcam' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_fec_tcam timestamp(6)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_val_mone' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_val_mone DECIMAL(16,6) DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        //INGRESO DE NUEVA ETIQUETA PARA VENEZUELA DEL IMPUESTO DEL 3%
        $empr_cod_pais      = $_SESSION['U_PAIS_COD'];
        $pais_codigo_inter  = $_SESSION['S_PAIS_API_SRI'];
        $id_empresa         = $_SESSION['U_EMPRESA'];

        if ($pais_codigo_inter == '58') { // INGRESA IMPUESTO SOLO PARA VENEZUELA
            $id_etiq_imp = 0;
            $sql = "SELECT id_etiq_imp FROM comercial.pais_etiq_imp WHERE etiqueta = 'IGTF' AND impuesto = 'OTROS' AND pais_codigo_inter = '$pais_codigo_inter'";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $id_etiq_imp  = $oIfx->f('id_etiq_imp');
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            if ($id_etiq_imp == 0) {
                $sql = "INSERT INTO comercial.pais_etiq_imp ( pais_cod_pais, pais_codigo_inter, impuesto, etiqueta, porcentaje, porcentaje2) 
                                                            VALUES ( $empr_cod_pais, '$pais_codigo_inter', 'OTROS', 'IGTF', 3, 0);";
                $oCon->QueryT($sql);
            }
        }

        //::: FIN CAMBIOS PARA VENEZUELA :::


        // saetcmp 
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'tcmp_siigo_id' AND TABLE_NAME = 'saetcmp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saetcmp ADD tcmp_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'tcmp_siigo_name' AND TABLE_NAME = 'saetcmp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saetcmp ADD tcmp_siigo_name varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        // saefpag
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fpag_siigo_id' AND TABLE_NAME = 'saefpag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefpag ADD fpag_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fpag_siigo_name' AND TABLE_NAME = 'saefpag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefpag ADD fpag_siigo_name varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        // comercial.tipo_iden_clpv_pais
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'siigo_id' AND TABLE_NAME = 'tipo_iden_clpv_pais' AND TABLE_SCHEMA='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.tipo_iden_clpv_pais ADD siigo_id varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        //ID MONEDA EN LA DENOMINACION DE MONEDAS
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'id_moneda' AND TABLE_NAME = 'pais_moneda' AND TABLE_SCHEMA='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.pais_moneda ADD id_moneda INT DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'id_empresa' AND TABLE_NAME = 'pais_moneda' AND TABLE_SCHEMA='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.pais_moneda ADD id_empresa INT DEFAULT $id_empresa;";
            $oCon->QueryT($sqlalter);
        }

        // comercial.tipo_iden_clpv
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'siigo_id' AND TABLE_NAME = 'tipo_iden_clpv' AND TABLE_SCHEMA='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.tipo_iden_clpv ADD siigo_id varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        // saeret
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'tret_siigo_id' AND TABLE_NAME = 'saetret'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saetret ADD tret_siigo_id varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'tret_siigo_name' AND TABLE_NAME = 'saetret'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saetret ADD tret_siigo_name varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        //************** TIPO IMPUESTO SIIGO COLOMBIA************ */
        $table_name = '';
        $sql = "SELECT table_name FROM information_schema.columns 
                WHERE table_name='siigo_tax' 
                AND table_schema = 'comercial'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $table_name    = $oCon->f('table_name');
            }
        }
        $oCon->Free();
        if (empty($table_name)) {
            $sqlCreateTable = 'CREATE TABLE "comercial"."siigo_tax" (
                                    "tax_cod_tax" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
                                    INCREMENT 1
                                    MINVALUE  1
                                    MAXVALUE 2147483647
                                    START 1
                                    CACHE 1
                                    ),
                                        "pais_cod_pais" int4 NOT NULL,
                                        "empr_cod_empr" int4 NOT NULL,

                                        "id_siigo" int4 NOT NULL,
                                        "name_siigo" VARCHAR(100) NOT NULL,
                                        "type_siigo" VARCHAR(100) NOT NULL,
                                        "percentage_siigo" float4 NOT NULL,
                                        "active_siigo" bool NOT NULL,

                                        "tax_est_sn" VARCHAR(2) NOT NULL,
                                        "fecha_registro" timestamp(6) NOT NULL
                                    );';
            $oCon->QueryT($sqlCreateTable);
            $oCon->Free();

            $sqlClavePrimaria = 'ALTER TABLE "comercial"."siigo_tax" ADD CONSTRAINT "siigo_tax_pkey" PRIMARY KEY ("tax_cod_tax");';
            $oCon->QueryT($sqlClavePrimaria);
            $oCon->Free();
        }

        //************** REGISTRO DE LOS PAGOS SFT PICHICNHA************ */
        $table_name = '';
        $sql = "SELECT table_name FROM information_schema.columns 
                WHERE table_name='pagos_sftp_pichincha' 
                AND table_schema = 'public'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $table_name    = $oCon->f('table_name');
            }
        }
        $oCon->Free();
        if (empty($table_name)) {
            $sqlCreateTable = 'CREATE TABLE "public"."pagos_sftp_pichincha" (
                                    "pago_cod_pago" int8 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
                                    INCREMENT 1
                                    MINVALUE  1
                                    MAXVALUE 9223372036854775807
                                    START 1
                                    CACHE 1
                                    ),
                                        "id_sobre"  int8 ,
                                        "id_item"  int8 ,
                                        "referencia_sobre"  VARCHAR(200),
                                        "pais"  VARCHAR(200),
                                        "banco"  VARCHAR(200),
                                        "forma_pago"  VARCHAR(200),
                                        "pais_banco_cuenta"  VARCHAR(200),
                                        "referencia" VARCHAR(200),
                                        "valor_procc" float8,
                                        "valor" float8,
                                        "moneda" VARCHAR(200),
                                        "fecha_proceso" VARCHAR(200),
                                        "hora_proceso" VARCHAR(200),
                                        "mensaje" text,
                                        "referencia_adicional" VARCHAR(200),
                                        "numero_documento" VARCHAR(200),
                                        "tipo_pago" VARCHAR(200),
                                        "numero_cuenta" VARCHAR(200),
                                        "no_documento" VARCHAR(200),
                                        "estado_impresion" VARCHAR(200),
                                        "secuencial_cobro" VARCHAR(200),
                                        "numero_comprobante" VARCHAR(200),
                                        "fecha_insersion" timestamptz,
                                        "procesado_sn" VARCHAR(200),
                                        "estado_proceso" VARCHAR(200),
                                        "contrapartida" VARCHAR(200),
                                        "nombre_archivo_carga" TEXT,
                                        "nombre_archivo_descarga" TEXT,
                                        "id_factura" int8,
                                        "id_empresa"  int8,
                                        "valid_solo_contrato_sn"  VARCHAR(2),
                                        "no_contrato_sn"  VARCHAR(2)

                                    );';

            $oCon->QueryT($sqlCreateTable);
            $oCon->Free();

            $sqlClavePrimaria = 'ALTER TABLE "public"."pagos_sftp_pichincha" ADD CONSTRAINT "pagos_sftp_pichincha_pkey" PRIMARY KEY ("pago_cod_pago");';
            $oCon->QueryT($sqlClavePrimaria);
            $oCon->Free();
        } else {
            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'contrapartida' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD contrapartida varchar(100);";
                $oCon->QueryT($sqlalter);
            }



            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'nombre_archivo_descarga' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD nombre_archivo_descarga TEXT;";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'id_factura' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD id_factura INT8;";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'nombre_archivo_carga' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD nombre_archivo_carga TEXT;";
                $oCon->QueryT($sqlalter);
            }


            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'id_empresa' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD id_empresa INT8;";
                $oCon->QueryT($sqlalter);
            }

            // ---------------------------------------------------------------------------------------------
            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'mensaje' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD mensaje text;";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'valid_solo_contrato_sn' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD valid_solo_contrato_sn VARCHAR(2);";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE COLUMN_NAME = 'no_contrato_sn' AND TABLE_NAME = 'pagos_sftp_pichincha' and table_schema='public'";

            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);

            if ($ctralter == 0) {
                $sqlalter = "ALTER TABLE public.pagos_sftp_pichincha ADD no_contrato_sn VARCHAR(2);";
                $oCon->QueryT($sqlalter);
            }
        }



        // FIN CREACION DE COLUMNAS SIIGO

        // INICIO CREACION DE COLUMNAS OPENPAY
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_openpay_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_openpay_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_openpay_api_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_openpay_api_url varchar(500);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_openpay_idempresa' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_openpay_idempresa varchar(50);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_openpay_publick' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_openpay_publick varchar(1000);";
            $oCon->QueryT($sqlalter);
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_openpay_privatek' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_openpay_privatek varchar(1000);";
            $oCon->QueryT($sqlalter);
        }
        //FIN CREACION DE COLUMNAS OPENPAY

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'sucu_pref_num' AND TABLE_NAME = 'saesucu'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saesucu ADD sucu_pref_num varchar(50);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'sucu_resol_num' AND TABLE_NAME = 'saesucu'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saesucu ADD sucu_resol_num varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD sucu_alias_sucu*/
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'sucu_alias_sucu' AND TABLE_NAME = 'saesucu'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saesucu ADD sucu_alias_sucu varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_bi_link*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_bi_link' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_bi_link text;";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_ncue_scotia*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_ncue_scotia' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_ncue_scotia varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_ncue_abono*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_ncue_abono' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_ncue_abono varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_cod_unico_interbank*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_cod_unico_interbank' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_cod_unico_interbank varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_cod_empresa_interbank*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_cod_empresa_interbank' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_cod_empresa_interbank varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /*ADD empr_cod_rubro_interbank*/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_cod_rubro_interbank' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_cod_rubro_interbank varchar(50);";
            $oCon->QueryT($sqlalter);
        }


        /** FORMULARIO WHATSAPP **/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_whatsapp_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_whatsapp_sn int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_whatsapp_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_whatsapp_url varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        /** FORMULARIO SMS **/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_sn int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_token varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_url varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_key' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_key varchar(100);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_cant' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_cant int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_sms_tipo' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_sms_tipo int4 DEFAULT 1;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        /** FORMULARIO DATAFAST **/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_datafast_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_datafast_sn int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_datafast_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_datafast_url varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_datafast_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_datafast_token varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        /** FORMULARIO WS PORTAL **/


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_cod_aux_ws' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_cod_aux_ws varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fpag_cod_externo' AND TABLE_NAME = 'saefpag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefpag ADD fpag_cod_externo varchar(25);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // campo para ver si la forma de pago tiene habilitada la opcion para los adjuntos
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fpag_adj_sn' AND TABLE_NAME = 'saefpag'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefpag ADD fpag_adj_sn varchar(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_portal_link' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_portal_link varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        /** WS DATAFAST **/

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_datafast_caja' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_datafast_caja varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_datafast_auto' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_datafast_auto varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_datafast_ws' AND TABLE_NAME = 'saefact'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefact ADD fact_datafast_ws text;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'para_sec_stock' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saepara add para_sec_stock varchar(1) DEFAULT 'S'::character varying;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        /**
         * MINV
         */

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'minv_id_actv' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeminv ADD minv_id_actv int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'minv_id_proy' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeminv ADD minv_id_proy int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'minv_est_desp' AND TABLE_NAME = 'saeminv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeminv ADD minv_est_desp varchar(5);";
            $oCon->Query($sqlalter);
            $oCon->Free();
        }

        /** FXFP DATAFAST */

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_dws_send' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_dws_send text;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_dws_result' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_dws_result text;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_dws_caja' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_dws_caja varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_dws_tran' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_dws_tran int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prov_cod_char' AND TABLE_NAME = 'saeprov'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeprov ADD prov_cod_char VARCHAR(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dire_cod_prov' AND TABLE_NAME = 'saedire'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedire ADD dire_cod_prov int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dire_cod_cant' AND TABLE_NAME = 'saedire'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedire ADD dire_cod_cant int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dire_cod_ciud' AND TABLE_NAME = 'saedire'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedire ADD dire_cod_ciud int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dire_cod_parr' AND TABLE_NAME = 'saedire'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedire ADD dire_cod_parr int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // Alter para guardar los adjuntos de respaldo de la forma de pago
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_adj_fxfp' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_adj_fxfp varchar(100000);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // Alter para guardar la cuenta a la que se realizo el deposito
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fxfp_cod_ctab' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saefxfp ADD fxfp_cod_ctab int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        //CAMPO PARA LAS APROBACIONES DE LAS FACTURAS
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_est_aprob' AND TABLE_NAME = 'saefact' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_est_aprob varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO PARA EL TIPO: EXTERNO O INTERNO  Y PARA EL TRANSPORTISTA: SERVIENTREGA O LAARCOURIER
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_cod_trta' AND TABLE_NAME = 'saefact' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_cod_trta int4";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO PARA ALMACENAR SI LA FACTURA VA A LLEVAR ENVIO O NO
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'fact_envi_sn' AND TABLE_NAME = 'saefact' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_envi_sn varchar(10)";
            $oCon->QueryT($sqlalter);
        }

        //CAMPO PARA EL TIPO: EXTERNO O INTERNO  Y PARA EL TRANSPORTISTA: SERVIENTREGA O LAARCOURIER
        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'fact_cate_trans' AND TABLE_NAME = 'saefact' and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefact add fact_cate_trans int4";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'prod_tienda_sn' AND TABLE_NAME = 'saeprod'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeprod ADD prod_tienda_sn int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_token_tienda' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_token_tienda varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // -----------------------------------------------------------------------------------
        // TABLA DE BALANZAS
        // -----------------------------------------------------------------------------------

        $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'config_balanza' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = 'CREATE TABLE config_balanza (
            id serial,
            id_empresa int4,
            id_sucursal int4,
        
            nombre varchar(255),
            descripcion varchar(255),
            url_api varchar(255),
        
            usuario_ingr int4,
            fecha_ingr timestamp,
            usuario_actu int4,
            fecha_actu timestamp
        );
        ';
            $oCon->QueryT($sqltb);
        }


        $sqlinf = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE  TABLE_NAME = 'balanza_usuario' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = 'CREATE TABLE balanza_usuario (
            id serial,
            usuario_ingr int4,
            id_balanza int4,
            modulo varchar(255)
        );
        ';
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }


        // -----------------------------------------------------------------------------------
        // FIN TABLA DE BALANZAS
        // -----------------------------------------------------------------------------------


        /*PARAMETROS BALNZA TARAS*/

        $sqlgein = "SELECT count(*) as conteo
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE COLUMN_NAME = 'para_unid_tara' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepara ADD para_unid_tara int4 ;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE COLUMN_NAME = 'para_peso_tara' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepara ADD para_peso_tara numeric(18,6) ;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE COLUMN_NAME = 'para_prr_cli_sn' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepara ADD para_prr_cli_sn varchar(10) ;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE COLUMN_NAME = 'para_fpag_def' AND TABLE_NAME = 'saepara'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saepara ADD para_fpag_def int4 ;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'empr_url_kardex' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD empr_url_kardex varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'clpv_cod_externo' AND TABLE_NAME = 'saeclpv'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeclpv add column clpv_cod_externo varchar(300);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }




        //CONTROL DE SECUENCIALES POR USUARIO DESDE LA SAEEMIFA


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_emifa_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN  bloqueo_emifa_sn VARCHAR(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'secuenciales_emifa' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN secuenciales_emifa VARCHAR(155) DEFAULT '';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_usuarios_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN bloqueo_usuarios_sn VARCHAR(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'usuarios_rep_bloc' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN usuarios_rep_bloc VARCHAR(155) DEFAULT '';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'sucursales_usuario' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN sucursales_usuario TEXT DEFAULT '';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'asignacion_grupo_trabajo' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN asignacion_grupo_trabajo VARCHAR(10000) DEFAULT '';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tret_cod_banc' AND TABLE_NAME = 'saetret' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saetret ADD COLUMN tret_cod_banc VARCHAR(150) DEFAULT '';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_vlan_user_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN  bloqueo_vlan_user_sn VARCHAR(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_elimina_pppoe_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN  bloqueo_elimina_pppoe_sn VARCHAR(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bloqueo_contratos_sn' AND TABLE_NAME = 'usuario' AND table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE comercial.usuario ADD COLUMN  bloqueo_contratos_sn VARCHAR(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dncr_obj_iva' AND TABLE_NAME = 'saedncr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saedncr add column dncr_obj_iva INT DEFAULT 0;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'dncr_exc_iva' AND TABLE_NAME = 'saedncr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saedncr add column dncr_exc_iva INT DEFAULT 0;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        //CONTROL PARA EL FACTURADOR EN PERÚ

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_cod_ftdr' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD COLUMN empr_cod_ftdr INT DEFAULT 1";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'fxfp_num_refe' AND TABLE_NAME = 'saefxfp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saefxfp add column fxfp_num_refe varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        // ---------------------------------------------------------------------------------------
        // ALTERS PARAMETROS DE INVNETARIO
        // ---------------------------------------------------------------------------------------
        $sql_para_inv = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'digitos_kardex' AND TABLE_NAME = 'parametro_inv' AND table_schema='comercial'";
        $ctralter = consulta_string($sql_para_inv, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.parametro_inv add COLUMN digitos_kardex int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sql_para_inv = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tran_ingreso_conteo' AND TABLE_NAME = 'parametro_inv' AND table_schema='comercial'";
        $ctralter = consulta_string($sql_para_inv, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.parametro_inv add COLUMN tran_ingreso_conteo varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sql_para_inv = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'tran_egreso_conteo' AND TABLE_NAME = 'parametro_inv' AND table_schema='comercial'";
        $ctralter = consulta_string($sql_para_inv, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.parametro_inv add COLUMN tran_egreso_conteo varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // ---------------------------------------------------------------------------------------
        // ALTERS PARAMETROS DE INVNETARIO
        // ---------------------------------------------------------------------------------------



        // -----------------------------------------------------------------------------------
        // TABLA DE HISTORIAL MODIFICACION DASI
        // -----------------------------------------------------------------------------------

        $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'historial_modificacion_saedasi' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = 'CREATE TABLE historial_modificacion_saedasi (
            id serial,
            id_empresa int4,
            id_sucursal int4,
            id_ejercicio int4,
        
            asto_cod varchar(255),
            observacion varchar(255),
        
            id_usuario int4,
            fecha timestamp
        );
        ';
            $oCon->QueryT($sqltb);
        }

        //ALTER DENOMINACION MONETARIA
        $sqlalter = "ALTER TABLE comercial.pais_moneda 
        ALTER COLUMN denominacion TYPE numeric(10,2) USING denominacion::numeric(10,2);";
        $oCon->QueryT($sqlalter);


        // ALTER SECUENCIAL AUTOMATICO DE FACTURA EN COMPRAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'secuencial_factura' AND TABLE_NAME = 'parametro_inv'  and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table comercial.parametro_inv add secuencial_factura varchar(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // ALTER CODIGO EN IDENTIFICACIONES PARA EL LIBRO DE COMPRAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'codigo_libro' AND TABLE_NAME = 'tipo_iden_clpv_pais'  and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.tipo_iden_clpv_pais add codigo_libro VARCHAR(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        // -----------------------------------------------------------------------------------
        // TABLA ELEMENTOS PARA PERU
        // -----------------------------------------------------------------------------------

        $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'saeelem' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = 'CREATE TABLE saeelem (
                                                elem_cod_elem serial,
                                                elem_cod_empr int4,
                                                elem_cod_nom varchar(255),
                                                elem_nom_elem varchar(255)
                                            );
                                            ';
            $oCon->QueryT($sqltb);
            $oCon->Free();
        }

        // ALTER CODIGO EN IDENTIFICACIONES PARA EL LIBRO DE COMPRAS
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'cuen_cod_elem' AND TABLE_NAME = 'saecuen'  and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saecuen add cuen_cod_elem int4;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // -----------------------------------------------------------------------------------
        // FIN TABLA ELEMENTOS PARA PERU
        // -----------------------------------------------------------------------------------



        // ALTER CODIGO PARA IDENTIFICAR RETENCIONES DE DETRACCIONES
        $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'tret_ret_det' AND TABLE_NAME = 'saetret'  and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saetret add tret_ret_det varchar(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        // ALTER CODIGO PARA CONFIGURAR LA TRANSACCION DE DETRACCION
        $sqlgein = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE COLUMN_NAME = 'pccp_tran_det' AND TABLE_NAME = 'saepccp'  and table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saepccp add pccp_tran_det varchar(10);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // ALTER campo en usuario para mostrar la fecha en la transferencia
        $sqlgein = "SELECT count(*) as conteo
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE COLUMN_NAME = 'cambio_fecha_transf_sn' AND TABLE_NAME = 'usuario'  and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add cambio_fecha_transf_sn varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'usuario_sucursal_sn' AND TABLE_NAME = 'usuario'  and table_schema='comercial'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table comercial.usuario add usuario_sucursal_sn varchar(1) DEFAULT 'N';";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        // TABLA CONTROL CALIDAD PRODUCTOS
        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'pais_etiq_contr' and table_schema='isp'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter > 0) {
            $sqlalter = "ALTER TABLE isp.pais_etiq_contr
                        SET SCHEMA comercial;";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }



        // CAMPO REFERENCIA PARTIDAS PRESUPUESTARIAS VUELA Y OTROS
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'dmov_part_pres' AND TABLE_NAME = 'saedmov' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saedmov ADD COLUMN dmov_part_pres varchar(255);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }


        ///TABLA TARA POR CLIENTE
        $sqlinf = "SELECT count(*) as conteo
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE  TABLE_NAME = 'tara_cliente' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.tara_cliente(
                   id serial, 
                   empresa int4,
                   sucursal int4,
                   cod_clpv int4,
                   nombre_tara varchar(255),
                   observacion_tara varchar(255),
                   peso float,
                   id_usuario int4,
                   fecha_creacion timestamp,
                   id_usuario_act int4,
                   fecha_actualizacion timestamp,
                   primary key (id)
                   );";
            $oCon->QueryT($sqltb);
        }


        // TABLA CONFIGURACION GENERAL ROMANEO GUADA
        $sqlinf = "SELECT count(*) as conteo
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE  TABLE_NAME = 'config_roma' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.config_roma(
                   id serial, 
                   empresa int4,
                   sucursal int4,
                   json_tara_peso varchar(255),
                   bodega_merma int4,
                   transaccion_merma_transf varchar(255),
                   id_usuario int4,
                   fecha_creacion timestamp,
                   id_usuario_act int4,
                   fecha_actualizacion timestamp,
                   primary key (id)
                   );";
            $oCon->QueryT($sqltb);
        }

        // ------------------------------------------------------------------
        // ALTER SAEDMOV
        // ------------------------------------------------------------------
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_azoc_sn' AND TABLE_NAME = 'saedmov_an' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saedmov_an ADD COLUMN dmov_azoc_sn varchar(255);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        // ------------------------------------------------------------------
        // FIN ALTER SAEDMOV
        // ------------------------------------------------------------------
        // ------------------------------------------------------------------
        // ALTER SAEDMOV
        // ------------------------------------------------------------------
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_can_azoc' AND TABLE_NAME = 'saedmov_an' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saedmov_an ADD COLUMN dmov_can_azoc float;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        // ------------------------------------------------------------------
        // FIN ALTER SAEDMOV
        // ------------------------------------------------------------------

        // ------------------------------------------------------------------
        // ALTER SAEDIRE
        // ------------------------------------------------------------------
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dire_cod_ciud' AND TABLE_NAME = 'saedire' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saedire ADD COLUMN dire_cod_ciud int;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        // ------------------------------------------------------------------
        // FIN ALTER SAEDIRE
        // ------------------------------------------------------------------
        // ------------------------------------------------------------------
        // ALTER SAEREQU
        // ------------------------------------------------------------------
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_realiz_x' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_realiz_x int;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_revisa_x' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_revisa_x varchar(255);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_despac_x' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_despac_x varchar(255);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_num_comp' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_num_comp int;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_usua_acept' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_usua_acept int;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'requ_date_acept' AND TABLE_NAME = 'saerequ' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saerequ ADD COLUMN requ_date_acept TIMESTAMP;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }


        // ------------------------------------------------------------------
        // FIN ALTER SAEDIRE
        // ------------------------------------------------------------------






        $sqlgein = "SELECT count(*) as conteo
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'dmov_part_pres' AND TABLE_NAME = 'saedmov_an' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saedmov_an ADD COLUMN dmov_part_pres varchar(255);";
            $oCon->QueryT($sql);
            $oCon->Free();
        }

        // COSTO PRODUCTO PARA CORDEROM
        $sqlgein = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME = 'prbo_costo_prod' AND TABLE_NAME = 'saeprbo' AND table_schema='public'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql = "ALTER TABLE saeprbo ADD COLUMN prbo_costo_prod float;";
            $oCon->QueryT($sql);
            $oCon->Free();
        }


        //CAMPO ID_ADJUNTO Y ESTADO_MENSAJE HIBOT

        $sql_table = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE  table_schema = 'comercial'
            AND    table_name   = 'hibot_plantilla_envio_auto'
            ) as existe;";

        $existe = consulta_string($sql_table, 'existe', $oCon, 0);

        if ($existe) {

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'id_adjunto' AND TABLE_NAME = 'hibot_plantilla_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            //echo $ctralter;exit;
            if ($ctralter == 0) {
                $sqlalter = "ALTER table comercial.hibot_plantilla_envio_auto add id_adjunto int4";
                $oCon->QueryT($sqlalter);
                $oCon->Free();
            }

            /*
            $sql_hibot = "ALTER TABLE comercial.hibot_plantilla_envio_auto ADD COLUMN IF NOT EXISTS id_adjunto int4;";
            $oCon->QueryT($sql_hibot);
            $oCon->Free();
            */
        }

        if ($existe) {
            // estado_mensaje para obtener si el mensaje fue enviado, entregado o visto

            $sqlgein = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE COLUMN_NAME = 'estado_mensaje' AND TABLE_NAME = 'hibot_plantilla_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            //echo $ctralter;exit;
            if ($ctralter == 0) {
                $sqlalter = "ALTER table comercial.hibot_plantilla_envio_auto add estado_mensaje varchar(255);";
                $oCon->QueryT($sqlalter);
                $oCon->Free();
            }

            /*
            $sql_hibot = "ALTER TABLE comercial.hibot_plantilla_envio_auto ADD COLUMN IF NOT EXISTS estado_mensaje varchar(255);";
            $oCon->QueryT($sql_hibot);
            $oCon->Free();
            */
        }


        /**
         * CAMPOS PARA ENVIO A SERVIENTREGA
         */


        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_servi_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_servi_sn int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_servi_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_servi_url varchar(100);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_servi_user' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_servi_user varchar(100);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_servi_pass' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_servi_pass varchar(100);";
            $oCon->QueryT($sqlalter);
        }


        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'ciudades_servientrega' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE comercial.ciudades_servientrega(
                id serial,
                id_ciudad int,
                nombre varchar(100)
                );";
            $oCon->QueryT($sql_1);
        }


        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'productos_servientrega' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE comercial.productos_servientrega(
                id serial,
                id_producto int,
                nombre_producto varchar(100)
                );";
            $oCon->QueryT($sql_1);
        }


        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'estados_servientrega' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE comercial.estados_servientrega(
                id serial,
                id_estado int,
                nombre_estado varchar(100)
                );";
            $oCon->QueryT($sql_1);
        }


        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'estados_facturas' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE comercial.estados_facturas(
                    id serial,
                    siglas varchar(10),
                    descripcion varchar(100),
                    estado_final varchar(10),
                    primary key(id)
                    );";
            $oCon->QueryT($sql_1);
        }


        // tabla para guardar el analisi del cafe en sensum
        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'saeanpro' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sql_1 = "CREATE TABLE saeanpro (
                                            anpro_cod_anpro serial PRIMARY KEY,
                                        
                                            anpro_num_comp int4,
                                            anpro_cod_dmov int4,
                                        
                                            anpro_cod_prod varchar(255),
                                            anpro_nom_prod varchar(255),
                                            anpro_cod_lote varchar(255),
                                            anpro_can_tot float,
                                            anpro_can_muest float,
                                            anpro_can_qq float,
                                            anpro_val_h float,
                                        
                                            anpro_val_almendra float,
                                            anpro_val_pas1 float,
                                            anpro_val_pas2 float,
                                            anpro_val_br float,
                                            anpro_val_malla0 float,
                                            anpro_val_malla12 float,
                                            anpro_val_malla14 float,
                                            anpro_val_M15_18 float,
                                        
                                            anpro_val_F14 float,
                                            anpro_val_F15 float,
                                            anpro_val_puntaje float,
                                            anpro_val_grado varchar(255),
                                        
                                            anpro_val_oro float,
                                            anpro_val_broca float,
                                            anpro_val_caracol float,
                                            anpro_val_segundas float,

                                            anpro_est_anpro varchar(5),
                                            
                                            anpro_usua_crea int4,
                                            anpro_fec_crea timestamp,
                                            anpro_usua_act int4,
                                            anpro_fec_act timestamp
                                        );
                                        ";
            $oCon->QueryT($sql_1);
        }



        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'ciud_cod_servi' AND TABLE_NAME = 'saeciud'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeciud add column ciud_cod_servi int4;";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_servi_codigo' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_servi_codigo int4;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_servi_estado' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_servi_estado int4 DEFAULT 0;";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_servi_envio' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_servi_envio text;";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_servi_anulacion' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_servi_anulacion text;";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'guia_servi_tipo_prod' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_servi_tipo_prod int4;";
            $oCon->QueryT($sqlalter);
        }

        /**
         * FIN CAMPOS PARA ENVIO A SERVIENTREGA
         */

        // columnas en la guia para los datos de laar courier

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_laar_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_laar_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_laar_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_laar_url varchar(255);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_laar_user' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_laar_user varchar(100);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_laar_pass' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_laar_pass varchar(100);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_laar_cod' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_laar_cod varchar(100);";
            $oCon->QueryT($sqlalter);
        }




        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'guia_laar_guia' AND TABLE_NAME = 'saeguia'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeguia add column guia_laar_guia varchar(200);";
            $oCon->QueryT($sqlalter);
        } else {
            $sql_add_column = 'ALTER TABLE "saeguia" ALTER COLUMN guia_laar_guia TYPE varchar(200)';
            $oCon->QueryT($sql_add_column);
            $oCon->Free();
        }

        /**
         * INICIO CAMPOS PARA SFTP BANCO PICHINCHA
         */

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_user' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_user varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_ip' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_ip varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_port' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_port varchar(5);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_ppk_f_dir' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_ppk_f_dir varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_remote_dir' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_remote_dir varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_bpi_sftp_local_dir' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_bpi_sftp_local_dir varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        // OZMAP
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ozmap_sn' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ozmap_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ozmap_url' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ozmap_url varchar(500);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ozmap_user' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ozmap_user varchar(50);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ozmap_pass' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ozmap_pass varchar(100);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_ozmap_api_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_ozmap_api_token varchar(500);";
            $oCon->QueryT($sqlalter);
        }





        /**
         * FIN CAMPOS PARA SFTP BANCO PICHINCHA
         */



        // manejo de trasnportistas como courier

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_courier_sn' AND TABLE_NAME = 'saetrta'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saetrta add column trta_courier_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_courier_ws_sn' AND TABLE_NAME = 'saetrta'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saetrta add column trta_courier_ws_sn varchar(2);";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'trta_cod_courier' AND TABLE_NAME = 'saetrta'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saetrta add column trta_cod_courier varchar(255);";
            $oCon->QueryT($sqlalter);
        }



        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'empr_cod_bgy' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_cod_bgy varchar(5);";
            $oCon->QueryT($sqlalter);
        }


        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bapg_nom_banc' AND TABLE_NAME = 'saebafp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saebafp add column bapg_nom_banc varchar(100);";
            $oCon->QueryT($sqlalter);
        } else {
            $sql_add_column = 'ALTER TABLE "saebafp" ALTER COLUMN bapg_nom_banc TYPE text';
            $oCon->QueryT($sql_add_column);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'bapg_nom_gira' AND TABLE_NAME = 'saebafp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saebafp add column bapg_nom_gira varchar(100);";
            $oCon->QueryT($sqlalter);
        } else {
            $sql_add_column = 'ALTER TABLE "saebafp" ALTER COLUMN bapg_nom_gira TYPE text';
            $oCon->QueryT($sql_add_column);
            $oCon->Free();
        }



        // tabla registro eenvio automatico ftp/sftp

        $sqlinf = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE  TABLE_NAME = 'envios_ftp_cash' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.envios_ftp_cash(
                            envio_cod_envio int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
                            INCREMENT 1
                            MINVALUE  1
                            MAXVALUE 2147483647
                            START 1
                            ) ,

                            \"id_empresa\" int4,
                            \"sigla\" varchar(20) ,
                            \"ip_server\" varchar(100) ,
                            \"port_server\" int4,
                            \"user_server\" varchar(20) ,
                            \"ppk_dir_server\" varchar(255) ,
                            \"remote_dir_server\" varchar(255) ,
                            \"base_dir\" varchar(255) ,
                            \"result_process\" varchar(255) ,
                            \"dir_file\" varchar(255) ,
                            \"ftp_auto_sn\" varchar(255) ,
                            \"date_process\" timestamptz(6),
                            \"pass_server\" varchar(255) ,
                            \"protocolo_server\" varchar(20) ,
                            \"estado\" int2,
                            CONSTRAINT \"pk_envios_ftp_cash\" PRIMARY KEY (\"envio_cod_envio\")
                            
                            );";
            $oCon->QueryT($sqltb);
        }






        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_enti_code' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempr add column empr_enti_code varchar(100);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_whatsapp_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempr ADD COLUMN empr_whatsapp_token varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }


        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'mov_id_progra' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN mov_id_progra varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_progr_det' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN dmov_progr_det varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_progr_preimp' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN dmov_progr_preimp varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_cod_pedf' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN dmov_cod_pedf varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'dmov_ref_clpv' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN dmov_ref_clpv varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'mov_id_progra' AND TABLE_NAME = 'saedmov_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saedmov_an ADD COLUMN mov_id_progra varchar(255);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'minv_alb_sn' AND TABLE_NAME = 'saeminv_an'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeminv_an ADD COLUMN minv_alb_sn varchar(2);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empl_num_ctas' AND TABLE_NAME = 'saeempl'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempl ADD COLUMN empl_num_ctas varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sqlval = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empl_num_ctas1' AND TABLE_NAME = 'saeempl'";
        $ctralter = consulta_string($sqlval, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER TABLE saeempl ADD COLUMN empl_num_ctas1 varchar(50);";
            $oCon->QueryT($sqlalter);
            $oCon->Free();
        }

        $sql = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empl_num_ccic' AND TABLE_NAME = 'saeempl'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table saeempl add empl_num_ccic VARCHAR(255)";
            $oCon->QueryT($sqlalter);
        }

        $oCon->QueryT('COMMIT;');

        //CAMPOS WHATSAPP
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_whatsapp_token' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_whatsapp_token varchar(255)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_whatsapp_reintentos' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_whatsapp_reintentos int2 DEFAULT(0)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_whatsapp_cant' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_whatsapp_cant int2 DEFAULT(0)";
            $oCon->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'empr_sms_reintentos' AND TABLE_NAME = 'saeempr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saeempr add empr_sms_reintentos int2 DEFAULT(0)";
            $oCon->QueryT($sqlalter);
        }


        // TABLA DE SUSTENTO TRIBUTARIO CAMPO PARA DEFINIR EL TIPO DE COMPRA (GRAVADAS, NO GRAVADAS, EXCENTO, ETC)
        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'crtr_tip_docu' AND TABLE_NAME = 'saecrtr'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table saecrtr add crtr_tip_docu VARCHAR(25);";
            $oCon->QueryT($sqlalter);
        }

        //CREACION TABLA PLANTILLAS

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'plantillas_envio' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlinf = "CREATE TABLE comercial.plantillas_envio (
                        id serial2,
                        tipo_plantilla int4,
                        tipo int4,
                        nombre varchar(100),
                        titulo varchar(100),
                        texto text,
                        id_usuario_delete int4,
                        delete_at timestamp(6),
                        CONSTRAINT plantillas_envio_pkey PRIMARY KEY (id)
                        );";
            $oCon->QueryT($sqlinf);
        }

        //TABLA PLANTILLAS ENVIO CAMPANA
        $sqlinf = "SELECT count(*) as conteo
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE  TABLE_NAME = 'plantillas_envio_campana' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.plantillas_envio_campana (
                       id serial2,
                       empr_cod_empr int2 NOT NULL,
                       plantilla_id int2 NOT NULL,
                       registros int2 NOT NULL,
                       usuario_id int2 NOT NULL,
                       estado_envio varchar(2) NOT NULL,
                       created_at timestamp NOT NULL,
                       updated_at timestamp,
                       PRIMARY KEY (id)
                     )
                     ;";

            $oCon->QueryT($sqltb);
            //INSERTAR PRIMERA CAMPANA DEFAULT
            $sql = "INSERT INTO comercial.plantillas_envio_campana (empr_cod_empr, plantilla_id, registros, usuario_id, estado_envio, created_at) 
                                                                         VALUES (1, 1, 0, 1, 'F', '2024-01-01 00:00:00');";
            $oCon->QueryT($sql);

            //ACTUALIZAR TODOS LOS ENVIOS ANTERIORES YA QUE NO TENIAN CAMPANAS
            $sql = "UPDATE comercial.plantillas_envio_auto SET id_envio_campana = 1 WHERE fecha_registro <= '2024-07-15'";
            $oCon->QueryT($sql);
        }


        // TABLA CABECEREA PRODUCTOS PEDIDOS SUPERMAXI, SANTA MARIA, TIA, ETC
        $sqlinf = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = 'pedido_cliente_produccion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.pedido_cliente_produccion (
                                        id serial,

                                        id_empresa int4,
                                        id_sucursal int4,
                                        id_bodega int4,
                                        
                                        cod_clpv int4,
                                        fecha_entr date,
                                        fecha_canc date,
                                        num_pedido varchar(255),
                                        data_info TEXT,
                                        tipo_pedido varchar(255),
                                        estado varchar(255),
                                        
                                        usua_crea int4,
                                        fec_crea timestamp,
                                        usua_act int4,
                                        fec_act timestamp,
                                        
                                        primary key (id)
                                    )
                                    ;";

            $oCon->QueryT($sqltb);
        }


        // TABLA DETAALLE PRODUCTOS PEDIDOS SUPERMAXI, SANTA MARIA, TIA, ETC
        $sqlinf = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = 'detalle_pedido_cliente_produccion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.detalle_pedido_cliente_produccion (
                                        id serial,

                                        id_empresa int4,
                                        id_sucursal int4,
                                        id_bodega int4,
                                        
                                        id_pedido_cliente_produccion int4,
                                        codigo_producto varchar(255),
                                        nombre_producto varchar(255),
                                        cod_prod_cliente varchar(255),
                                        nom_prod_cliente varchar(255),
                                        cantidad_gabeta float,
                                        cantidad_por_gabeta float,
                                        cantidad_total float,
                                        precio float,
                                        numero_orden varchar(255),
                                        
                                        usua_crea int4,
                                        fec_crea timestamp,
                                        usua_act int4,
                                        fec_act timestamp,
                                        
                                        primary key (id)
                                )
                                ;";

            $oCon->QueryT($sqltb);
        }

        // TABLA ASIGNACION CLIENTES BODEGAS
        $sqlinf = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = 'gestion_cliente_bodega' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.gestion_cliente_bodega (
                                        id serial,

                                        id_empresa int4,
                                        id_sucursal int4,
                                        id_bodega int4,
                                        cod_clpv int4,
                                        formato varchar(255),
                                        
                                        usua_crea int4,
                                        fec_crea timestamp,
                                        usua_act int4,
                                        fec_act timestamp,
                                        
                                        primary key (id)
                                    )
                                    ;";
            $oCon->QueryT($sqltb);
        }


        //TABLA PLANTILLAS ENVIO AUTO
        $sqlinf = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE  TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.plantillas_envio_auto (
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
            INCREMENT 1
            MINVALUE  1
            MAXVALUE 2147483647
            START 1
            CACHE 1
            ),
            plantilla_id int4 NOT NULL,
            fecha_registro timestamp(6) NOT NULL DEFAULT now(),
            fecha_envio timestamp(6),
            receptor_envio varchar(255) COLLATE pg_catalog.default NOT NULL,
            titulo_envio varchar(255) COLLATE pg_catalog.default NOT NULL,
            texto text COLLATE pg_catalog.default NOT NULL,
            result_ws text COLLATE pg_catalog.default,
            usuario_id int4 NOT NULL,
            estado int4 NOT NULL DEFAULT 0,
            empresa_id int4 NOT NULL,
            intentos int4 NOT NULL DEFAULT 0,
            id_adjunto int2,
            estado_mensaje varchar(50) COLLATE pg_catalog.default,
            id_whastapp_mensaje varchar(255) COLLATE pg_catalog.default,
            id_envio_campana int2,
            CONSTRAINT plantillas_envio_historial_pkey PRIMARY KEY (id),
            CONSTRAINT plantillas_envio_campana FOREIGN KEY (id_envio_campana) REFERENCES comercial.plantillas_envio_campana (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT plantillas_envio_historial_plantilla_id_fkey FOREIGN KEY (plantilla_id) REFERENCES comercial.plantillas_envio (id) ON DELETE NO ACTION ON UPDATE NO ACTION
            );";
            $oCon->QueryT($sqltb);
        }



        //TABLA CABECERA RECEPCION OC DE OTRAS EMPRESAS
        $sqlinf = "SELECT count(*) as conteo
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE  TABLE_NAME = 'recepcion_oc_produccion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.recepcion_oc_produccion (
                 id serial primary key,
                 empresa int4,
                 sucursal int4,
                 fecha_desde date,
                 fecha_hasta date,
                 minv_num_comp_asociados varchar(2555),
                 requ_cod_requ_asociados varchar(2555),
                 id_usuario int4,
                 fecha_ingreso timestamp,
                 id_usuario_act int4,
                 fecha_ingreso_act timestamp
                 );";
            $oCon->QueryT($sqltb);
        }


        //TABLA DETALLE RECEPCION OC DE OTRAS EMPRESAS
        $sqlinf = "SELECT count(*) as conteo
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE  TABLE_NAME = 'detalle_recepcion_oc_produccion' and table_schema='comercial'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE comercial.detalle_recepcion_oc_produccion (
             id serial primary key,
             empresa int4,
             sucursal int4,
             bodega int4,
             id_recepcion_oc_produccion int4,
             codigo_producto varchar(255),
             nombre_producto varchar(255),
             cantidad_consolidado float,
             stock_fecha float,
             por_producir float,
             real_a_producir float,

             id_usuario int4,
             fecha_ingreso timestamp,
             id_usuario_act int4,
             fecha_ingreso_act timestamp,
             CONSTRAINT recepcion_oc_produccion_foreign FOREIGN KEY (id_recepcion_oc_produccion) REFERENCES comercial.recepcion_oc_produccion (id) ON DELETE NO ACTION ON UPDATE NO ACTION
             );";
            $oCon->QueryT($sqltb);
        }


        //VALIDACION TABLAS PLANTILLAS

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";

        $ctralter = consulta_string($sqlinf, 'conteo', $oIfx, 0);
        if ($ctralter != 0) {

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'id_adjunto' AND TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.plantillas_envio_auto add id_adjunto int2";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'estado_mensaje' AND TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.plantillas_envio_auto add estado_mensaje varchar(50)";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'id_whastapp_mensaje' AND TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.plantillas_envio_auto add id_whastapp_mensaje varchar(50)";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'id_envio_campana' AND TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.plantillas_envio_auto add id_envio_campana int2";
                $oCon->QueryT($sqlalter);
            }

            $sqlgein = "SELECT count(*) as conteo
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'url_deuda' AND TABLE_NAME = 'plantillas_envio_auto' and table_schema='comercial'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table comercial.plantillas_envio_auto add url_deuda text";
                $oCon->QueryT($sqlalter);
            }
        } //cierre if tabla plantillas_envio_auto

        $sqlalter = "ALTER TABLE public.saeempr ALTER COLUMN empr_whatsapp_cant TYPE int8";
        $oCon->QueryT($sqlalter);
    } catch (Exception $e) {
        // rollback

        $oCon->QueryT('ROLLBACK;');
        $oReturn->alert($e->getMessage());
        $oReturn->assign("ctrl", "value", 1);
    }


    return $oReturn;
}

function validar_firma($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $oReturn->assign("divValidarFirma", "innerHTML", "");

    $empresa = $aForm['empr_cod_empr'] ?? null;

    $sql = "select empr_nom_empr from saeempr where empr_cod_empr = $empresa";
    $empr_nom_empr = "";
    if ($oIfx->Query($sql)) {
        $empr_nom_empr = $oIfx->f("empr_nom_empr");
    }
    $oIfx->Free();

    $empr_nom_toke = $aForm['empr_nom_toke'];
    $empr_pass_toke = $aForm['empr_pass_toke'];
    $empr_token_api = $aForm['empr_token_api'];
    $empr_ws_sri_url = $aForm['empr_ws_sri_url'];
    $empr_ws_sri_sn = $aForm['empr_ws_sri_sn'];

    if ($empr_ws_sri_sn != 'S') {
        $oReturn->script("Swal.fire({title:'Advertencia', text:'Web service no activado para la empresa.', type:'warning'});");
        return $oReturn;
    }

    $respFirma = "";
    $ultimo_tiempo = 0;
    $ultimo_archivo = null;
    $patron = '/^.*\.p12$/';
    $directorio_firma = DIR_INCLUDE . "Clases/Formulario/Plugins/reloj";

    $archivos = glob($directorio_firma . '/*');
    foreach ($archivos as $archivo) {
        $nombre_archivo = basename($archivo);
        if (preg_match($patron, $nombre_archivo)) {
            $fecha_modificacion = filemtime($archivo);
            if ($fecha_modificacion > $ultimo_tiempo) {
                $ultimo_tiempo = $fecha_modificacion;
                $ultimo_archivo = $archivo;
            }
        }
    }

    $empr_ws_sri_url = rtrim($empr_ws_sri_url ?: (URL_JIREH_WS ?? ''), '/');

    if (!$empr_ws_sri_url) {
        $oReturn->script("Swal.fire({title:'Error', text:'No se ha definido la URL del servicio de firma electrónica.', type:'error'});");
        return $oReturn;
    }
    $url_validar_firma_api = $empr_ws_sri_url . '/api/facturacion/electronica/examinar/firma';
    $url_cargar_firma_api = $empr_ws_sri_url . '/api/facturacion/electronica/cargar/firma';
    $url_nueva_empresa_api = $empr_ws_sri_url . '/api/facturacion/electronica/nueva/empresa';

    if (empty($empr_token_api) && ($_SESSION['S_PAIS_API_SRI'] == 593)){
        $data = [
            'nombre' => $empr_nom_empr,
            'descripcion'  => 'Creado automaticamente',
            'archivo_p12' => '',
            'clave_p12' => '',
        ];
        $curl = curl_init($url_nueva_empresa_api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $respuesta = curl_exec($curl);
        $resultado = json_decode($respuesta, true);

        if ($resultado['success']){
            $empr_token_api = $resultado['empresa']['token_api'];
            if (!empty($empr_token_api)) {
                $sql_update = "UPDATE saeempr 
                            SET empr_token_api = '{$empr_token_api}' 
                            WHERE empr_cod_empr = {$empresa}";

                $oIfx->Query($sql_update);
                $oReturn->assign("empr_token_api", "value", $empr_token_api);
            }
        } else {
            $oReturn->script("Swal.fire({title: 'Error', html: '" . addslashes($resultado['msg']) . "<br>Ingrese el token manualmente', type: 'error'});");
            return $oReturn;
        }
    }

    if ($ultimo_tiempo) {
        $ultimo_archivo = basename($ultimo_archivo);
        $uri = "{$directorio_firma}/{$ultimo_archivo}";
        $archivo = file_get_contents($uri);
        if ($archivo) {
            $archivo_p12 = new CURLFILE($uri, null, basename($uri));
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url_validar_firma_api,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('token_api' => $empr_token_api, 'archivo_p12' => $archivo_p12, 'clave_p12' => $empr_pass_toke),
            ));
            $respuesta = curl_exec($curl);
            if ($respuesta == true) {
                $resultado = json_decode($respuesta, true);
                $respFirma = $resultado["msg"];
                if ($resultado["success"] && ($_SESSION['S_PAIS_API_SRI'] == 593)){
                    $data = [
                        'token_api' => $empr_token_api,
                        'archivo_p12'  => $archivo_p12,
                        'clave_p12'=> $empr_pass_toke
                    ];
                    $curl = curl_init($url_cargar_firma_api);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    $respuesta = curl_exec($curl);
                    $resultado = json_decode($respuesta, true);
                    $respFirma .= "<br>" . $resultado["msg"];

                    $sql_update = "UPDATE saeempr 
                        SET empr_token_api = '{$empr_token_api}',
                            empr_nom_toke = '{$empr_nom_toke}',
                            empr_pass_toke = '{$empr_pass_toke}',
                            empr_ws_sri_sn = '{$empr_ws_sri_sn}'
                        WHERE empr_cod_empr = {$empresa}";
                    $oIfx->Query($sql_update);
                    $oReturn->assign("empr_token_api", "value", $empr_token_api);
                    $oReturn->script("Swal.fire({title:'Éxito', html:'" . $respFirma . "', type:'success'});");
                } else if ($resultado["success"]) {
                    $sql_update = "UPDATE saeempr 
                        SET empr_token_api = '{$empr_token_api}',
                            empr_nom_toke = '{$empr_nom_toke}',
                            empr_pass_toke = '{$empr_pass_toke}',
                            empr_ws_sri_sn = '{$empr_ws_sri_sn}'
                        WHERE empr_cod_empr = {$empresa}";
                    $oIfx->Query($sql_update);
                    $oReturn->assign("empr_token_api", "value", $empr_token_api);
                    $oReturn->script("Swal.fire({title:'Éxito', html:'" . $respFirma . "', type:'success'});");
                } else {
                    $oReturn->script("Swal.fire({title:'Error', html:'" . $respFirma . "', type:'error'});");
                }
            } else {
                $msg = curl_error($curl);
                $respFirma = "Error: {$msg}";
                $oReturn->script("Swal.fire({title:'Error', html:'" . $respFirma . "', type:'error'});");
            }
            curl_close($curl);
        } else {
            $respFirma = "Error al leer el archivo de firma electronica en el servidor";
            $oReturn->script("Swal.fire({title:'Error', html:'" . $respFirma . "', type:'error'});");
        }
    } else {
        $respFirma = "No se pudo ubicar el certificado de firma electrónica mas reciente desde su importacion";
        $oReturn->script("Swal.fire({title:'Error', html:'" . $respFirma . "', type:'error'});");
    }

    //$oReturn->assign("divValidarFirma", "innerHTML", $respFirma);

    return $oReturn;
}

function acciones_integracion($accion, $aForm = [], $id_integracion_del = 0)
{
    global $DSN;

    session_start();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'] ? $_SESSION['U_EMPRESA'] : 0;

    $id_integracion = $aForm['id_integracion'];
    $tipo_guardado = !empty($id_integracion) ? " De Modificacion" : "De Creacion";

    $id_modal = 'miModal';
    $color_jireh_blue = '#337ab7';
    $color_jireh_red = '#ff3342';
    $color_jireh_green = '# #68ff33 ';

    $columnas_mostrar = ['id', 'estado_sn', 'nombre_integracion', 'descripcion', 'ambiente', 'url_api', 'tipo_api', 'token', 'fecha_creacion', 'fecha_modificacion'];
    $column_implode = implode(',', $columnas_mostrar);

    $integracion = new IntegracionComercial($oCon, $oConA, $idempresa, 0);
    $integracion_data = $integracion->obtener_integracion_by_id($idempresa, $id_integracion, $column_implode);

    $header = '<tr>
                    <th scope="col">#</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Ambiente</th>
                    <th scope="col">Url</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Token</th>
                    <th scope="col">Fecha creacion</th>
                    <th scope="col">Fecha modificacion</th>
                    <th scope="col">Accion</th>
                </tr>';

    $data_integracion = array();
    $count = 1;
    $body = (count($integracion_data) > 0) ? "" : '<tr><th scope="row" colspan="9">Sin integraciones</th></tr>';
    foreach ($integracion_data as $key => $integracion_indi) {

        $id_integracion     = $integracion_indi['id'];
        $estado_sn          = $integracion_indi['estado_sn'];
        $nombre_integracion = $integracion_indi['nombre_integracion'];
        $descripcion        = $integracion_indi['descripcion'];
        $ambiente           = $integracion_indi['ambiente'];
        $tipo_api           = $integracion_indi['tipo_api'];
        $url_api            = $integracion_indi['url_api'];
        $token              = $integracion_indi['token'];
        $fecha_creacion     = $integracion_indi['fecha_creacion'];
        $fecha_modificacion = $integracion_indi['fecha_modificacion'];


        $token_len = mb_strlen($token, 'UTF-8');
        $token = str_repeat("*", ($token_len / 2)) . substr($token, ($token_len / 2));

        $data_integracion = array(
            "id_integracion" => $id_integracion,
            "estado_sn" => ($estado_sn == 'S' ? 'On' : 'Off'),
            "nombre_integracion" => $nombre_integracion,
            "descripcion" => $descripcion,
            "ambiente" => $ambiente,
            "tipo_api" => $tipo_api,
            "url_api" => $url_api,
            "token" => $token,
            "fecha_creacion" => $fecha_creacion,
            "fecha_modificacion" => $fecha_modificacion
        );

        $body .= '<tr>
                    <th>' . $count . '</th>
                    <td>' . ($estado_sn == 'S' ? 'On' : 'Off') . '</td>
                    <td>' . $nombre_integracion . '</td>
                    <td>' . $descripcion . '</td>
                    <td>' . $ambiente . '</td>
                    <td>' . $url_api . '</td>
                    <td>' . $tipo_api . '</td>
                    <td>' . $token . '</td>
                    <td>' . $fecha_creacion . '</td>
                    <td>' . $fecha_modificacion . '</td>
                    <td>
                        <span class="btn btn-sm" style="border-color:' . $color_jireh_blue . '; background-color:' . $color_jireh_blue . ';color:white;" title="edit_integration" onClick="editar_integracion(' . $id_integracion . ');">
                            <i class="glyphicon glyphicon-edit"></i>
                        </span>
                        <span class="btn btn-sm" style="border-color:' . $color_jireh_red . '; background-color:' . $color_jireh_red . ';color:white;" title="del_integration" onClick="guardar_integracion(6,' . $id_integracion . ');">
                            <i class="glyphicon glyphicon-remove"></i>
                        </span>
                    </td>
                </tr>';

        $count++;
    }

    $data_integracion = $accion == 3 ? $data_integracion : [];
    $edit_token = $accion == 3 ? " readonly " : '';
    $edit_token_check = $accion == 3 ? '<label class="checkbox-inline"><input class="form-control" style="width: 15px; height: 12px;" type="checkbox" id="token_edit" name="token_edit" value="N" onclick="editar_token(this,`token`)">Editar Token</label>' : '';


    $accion_guardar = $accion == 3 ? 4 : 5;
    $estado_integracion = $data_integracion['estado_sn'] ? $data_integracion['estado_sn'] : "Off";

    $modal_content_registro = '  <div class="modal-dialog" role="document">
                                <div class="modal-content" style="display: table; margin: auto;">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="integracionModalLabel" style="text-align: center;">Formulario ' . $data_integracion['tipo_guardado'] . ' Integracion</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="form_modal" name="form_modal">
                                        <div class="row">
                                        <div class="col-md-12">
                                            <textarea id="descripcion" name="descripcion" style="width:100%;border: 2px solid white; border-radius: 5px;white-space: normal;text-align: justify;-moz-text-align-last: center;text-align-last: center;" value="' . $data_integracion['descripcion'] . '">' . $data_integracion['descripcion'] . '</textarea>
                                            <br>
                                        </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="estado_sn" class="col-form-label">Estado:
                                                    <div class="btn-group btn-toggle" id="estado_sn" name="estado_sn"> 
                                                        <button class="btn btn-sm btn-primary active">' . $estado_integracion . '</button>
                                                    </div>
                                                </label>

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nombre_integracion" class="col-form-label">Nombre:</label>
                                                    <input type="text" class="form-control" id="nombre_integracion" name="nombre_integracion" value="' . $data_integracion['nombre_integracion'] . '">
                                                    <input type="hidden" class="form-control" id="id_integracion" name="id_integracion" value="' . $data_integracion['id_integracion'] . '">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ambiente" class="col-form-label">Ambiente:</label>
                                                    <input type="text" class="form-control" id="ambiente" name="ambiente" value="' . $data_integracion['ambiente'] . '">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tipo_api" class="col-form-label">Tipo Api:</label>
                                                    <input type="text" class="form-control" id="tipo_api" name="tipo_api" value="' . $data_integracion['tipo_api'] . '">
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="url_api" class="col-form-label">Url:</label>
                                                    <input type="text" class="form-control" id="url_api" name="url_api" value="' . $data_integracion['url_api'] . '">
                                                </div>
                                            </div>                                            
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="token" class="col-form-label">Token:</label>
                                                    <input type="text" class="form-control" id="token" name="token" temp_value="' . $data_integracion['token'] . '" value="' . $data_integracion['token'] . '" ' . $edit_token . '>
                                                    ' . $edit_token_check . '
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" onClick="guardar_integracion(' . $accion_guardar . ')">Guardar</button>
                                </div>
                                </div>
                            </div>';

    $modal_content_lista = '  <div class="modal-dialog" role="document">
                                <div class="modal-content" style="display: inline-block;justify-content: center">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="integracionModalLabel" style="text-align: center;">Lista de Integraciones</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="form_modal" name="form_modal">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <table class="table table-responsive" style="width:auto;word-wrap:break-word;">
                                                    <thead>
                                                    ' . $header . '
                                                    </thead>
                                                    <tbody>
                                                    ' . $body . '
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                                </div>
                            </div>';

    // print_r($aForm);exit;


    switch ($accion) {
        case 1:
            //crear nueva integracion
            $modal_content = $modal_content_registro;
            break;
        case 2:
            //listar integraciones
            $modal_content = $modal_content_lista;
            break;
        case 3:
            //formulario modificar nueva integracion
            $modal_content = $modal_content_registro;
            break;
        case 4:
            //modificar nueva integracion
            try {
                $fecha_servidor_this = "'" . date("Y-m-d H:i:s") . "'";

                $integracion_data = $integracion->registrar_integracion(
                    $aForm['id_integracion'],
                    $idempresa,
                    'N',
                    $aForm['nombre_integracion'],
                    $aForm['descripcion'],
                    $aForm['ambiente'],
                    $aForm['url_api'],
                    $aForm['tipo_api'],
                    '',
                    '',
                    $usuario,
                    $clave,
                    $aForm['token'],
                    val_check_inv($aForm['token_edit'], 'S', 'N'),
                    '',
                    '',
                    ''
                );

                // ($integracion_data['estado']);

                if ($integracion_data['procesado']) {
                    $oReturn->script("hide_modal('$id_modal');"); //muestra el modal
                } else {
                    $message_to_user = ($integracion_data['data'][1]);
                    $message_array = explode('LINE', $message_to_user);
                    throw new Exception(($message_array[0]), 1);
                }
            } catch (Exception $e) {

                $oReturn->alert($e->getMessage());
            }


            break;

        case 6:
            //insertar nueva integracion
            try {
                if (!empty($id_integracion_del)) {
                    $integracion_data = $integracion->eliminar_integracion($id_integracion_del);
                } else {
                    throw new Exception("No se determino el componente a eliminar", 1);
                }
            } catch (Exception $e) {

                $oReturn->alert($e->getMessage());
            }
            $oReturn->script("hide_modal('$id_modal');"); //muestra el modal


            break;
        case 5:
            //insertar nueva integracion
            try {
                $fecha_servidor_this = "'" . date("Y-m-d H:i:s") . "'";

                if (
                    !empty($aForm['nombre_integracion']) &&
                    !empty($aForm['ambiente']) &&
                    !empty($aForm['url_api']) &&
                    !empty($aForm['tipo_api']) &&
                    !empty($idempresa)
                ) {

                    $integracion_data = $integracion->registrar_integracion(
                        $aForm['id_integracion'],
                        $idempresa,
                        'N',
                        $aForm['nombre_integracion'],
                        $aForm['descripcion'],
                        $aForm['ambiente'],
                        $aForm['url_api'],
                        $aForm['tipo_api'],
                        '',
                        '',
                        $usuario,
                        $clave,
                        $aForm['token'],
                        val_check_inv($aForm['token_edit'], 'S', 'N'),
                        '',
                        '',
                        ''
                    );
                } else {
                    throw new Exception("Compete el formulario", 1);
                }
            } catch (Exception $e) {

                $oCon->QueryT('ROLLBACK');
                $oReturn->alert($e->getMessage());
            }
            $oReturn->script("hide_modal('$id_modal');"); //muestra el modal


            break;
    }

    if ($accion < 4) {
        $oReturn->assign("$id_modal", "innerHTML", $modal_content); //setea el contendor del modal
        $oReturn->script("generate_modal('$id_modal')"); //muestra el modal
    }

    if (!empty($aForm)) {
        // $oReturn->script("console.log('aform: ".json_encode($aForm)."')");//muestra el modal
    }

    // $oReturn->script("console.log('$accion')");//muestra el modal


    return $oReturn;
}


/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */