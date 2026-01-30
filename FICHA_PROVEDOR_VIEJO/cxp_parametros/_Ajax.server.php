<?php

require("_Ajax.comun.php"); // No modificar esta linea

//MOSTRRAR ERRORES
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * **************************************************************** */
/*                               MODULO DE PARAMETROS        */
/* * **************************************************************** */


function dibuja_adjuntos($aForm = '')
{
    // $variables = $_SESSION['aDataGirdAdj'];

    // echo '<pre>';
    // print_r($variables);
    // echo '</pre>';

    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    // LIMPIA EL ARRAY TEMPORAL AL ENTRAR A ESTE MODULO
    unset($_SESSION['aDataGirdAdj']);

    $ifu = new Formulario;
    $ifu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $tableAdjuntos = "";

    // $oReturn->alert("Entró a dibuja_adjuntos");


    //adjuntos
    $ifu->AgregarCampoTexto('titulo', 'Titulo|left', false, '', 200, 200, true);
    $ifu->AgregarComandoAlEscribir('titulo', 'form1.titulo.value=form1.titulo.value.toUpperCase();');

    $ifu->AgregarCampoArchivo('archivo', 'Archivo|left', false, '', 100, 100, '', true);

    $tableAdjuntos .= '<table class="table table-striped table-condensed" align="center" style="width: 99%;">';
    $tableAdjuntos .= '<tr>';
    $tableAdjuntos .= '<td colspan="6"><h5>AJUNTOS <small>Ingreso Informacion</small></h5></td>';
    $tableAdjuntos .= '</tr>';
    $tableAdjuntos .= '<tr>';
    $tableAdjuntos .= '<td colspan="6">
									 <div class="btn btn-primary btn-sm" onclick="guardarAdjuntos();">
                                        <span class="glyphicon glyphicon-floppy-disk"></span>
                                        Guardar
                                    </div>
								</td>';
    $tableAdjuntos .= '</tr>';
    $tableAdjuntos .= '<tr>';
    $tableAdjuntos .= '<td>' . $ifu->ObjetoHtmlLBL('titulo') . '</td>';
    $tableAdjuntos .= '<td>' . $ifu->ObjetoHtml('titulo') . '</td>';
    $tableAdjuntos .= '<td>' . $ifu->ObjetoHtmlLBL('archivo') . '</td>';
    $tableAdjuntos .= '<td>' . $ifu->ObjetoHtml('archivo') . '</td>';
    $tableAdjuntos .= '<td align="center">
										<div class="btn btn-success btn-sm" onclick="agregarArchivo();">
											<span class="glyphicon glyphicon-plus-sign"></span>
											Agregar
										</div>
									<td>';
    $tableAdjuntos .= '</tr>';
    $tableAdjuntos .= '</tr>';


    // $tableAdjuntos .= '<tr>';

    // $tableAdjuntos .= '<td colspan="6">
    // 					    <div class="btn btn-danger btn-sm" onclick="enviar_mail();">
    //                         Enviar notificacion por Correo
    //                             <span class=" glyphicon glyphicon-envelope"></span>
    //                         </div>
    // 				    </td>';
    // $tableAdjuntos .= '</tr>';

    $tableAdjuntos .= '</table>';

    // enviamos la tabla al index
    $oReturn->assign("divFormularioAdjuntos", "innerHTML", $tableAdjuntos);
    return $oReturn;
}


function agrega_modifica_gridAdj($nTipo = 0,  $aForm = '', $id = '', $total_fact = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    if (!isset($_SESSION['aDataGirdAdj'])) {
        $_SESSION['aDataGirdAdj'] = [];
    }

    $aDataGrid = $_SESSION['aDataGirdAdj'];

    // $archivo = substr($aForm['archivo'], 3);
    $archivo = $aForm['archivo'];   // SIN substr

    $titulo  = $aForm['titulo'];

    $cont = count($aDataGrid);

    $aDataGrid[$cont] = [
        'Id'      => $cont,
        'Titulo'  => $titulo,
        'Archivo' => $archivo
    ];

    $_SESSION['aDataGirdAdj'] = $aDataGrid;

    $sHtml = mostrar_gridAdj();
    $oReturn->assign("gridArchivos", "innerHTML", $sHtml);

    return $oReturn;
}


function mostrar_gridAdj()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $aDataGrid = $_SESSION['aDataGirdAdj'];
    $aLabelGrid = ['Id', 'Titulo', 'Archivo', 'Eliminar'];

    $aDatos = [];
    $cont = 0;

    foreach ($aDataGrid as $row) {

        //$aDatos[$cont]['Id'] = $cont;
        $aDatos[$cont]['Id'] = $cont + 1;

        $aDatos[$cont]['Titulo']  = '<div align="left">' . $row['Titulo'] . '</div>';
        $aDatos[$cont]['Archivo'] = '<div align="left">' . $row['Archivo'] . '</div>';

        // botón regenerado correctamente
        $aDatos[$cont]['Eliminar'] =
            '<div align="center">
                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                     style="cursor:pointer;"
                     onclick="xajax_elimina_detalleAdj(' . $cont . ')"
                     alt="Eliminar">
             </div>';

        $cont++;
    }

    return genera_grid($aDatos, $aLabelGrid, 'Adjuntos', 98);
}



function genera_grid($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $ccos = null, $color = null, $aAccion = null, $Totales = null, $aOrden = null)
{

    // echo "Entró a la funcion";
    // exit;

    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    unset($arrayaDataGridVisible);
    $arrayaDataGridVisible[0] = 'S';
    $arrayaDataGridVisible[1] = 'S';
    $arrayaDataGridVisible[2] = 'S';
    $arrayaDataGridVisible[3] = 'S';
    $arrayaDataGridVisible[4] = 'S';

    if (is_array($aData) && is_array($aLabel)) {
        $iLabel = count($aLabel);
        $iData = count($aData);
        $sClass = 'on';
        $sHtml = '';
        $sHtml .= '<form id="DataGrid">';
        $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin:0px;">';
        $sHtml .= '<tr class="info">';

        for ($i = 0; $i < $iLabel; $i++) {
            $sLabel = explode('|', $aLabel[$i]);
            if ($sLabel[1] == '') {

                $aDataVisible = $arrayaDataGridVisible[$i];
                if ($aDataVisible == 'S') {
                    $aDataVisible = '';
                } else {
                    $aDataVisible = 'none;';
                }

                $sHtml .= '<td align="center" style="display: ' . $aDataVisible . '">' . $sLabel[0] . '</th>';
            } else {
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
                $sHtml .= '</td>';
            }
        }
        $sHtml .= '</tr>';

        // Genera Filas de Grid
        //rsort($aData, 0);
        for ($i = 0; $i < $iData; $i++) {
            $sHtml .= '<tr class="warning">';
            for ($j = 0; $j < $iLabel; $j++) {
                $campo = $aData[$i][$aLabel[$j]];

                $aDataVisible = $arrayaDataGridVisible[$j];
                if ($aDataVisible == 'S') {
                    $aDataVisible = '';
                } else {
                    $aDataVisible = 'none;';
                }

                if ($j != 0 && is_numeric($campo)) {
                    $campo = number_format($campo, 2, '.', '');
                    $sHtml .= '<td align="right" style="display: ' . $aDataVisible . '">' . $campo . '</td>';
                } else {
                    $sHtml .= '<td align="left" style="display: ' . $aDataVisible . '">' . $campo . '</td>';
                }
            } //fin for

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

// function consultarAdjuntos($aForm = '')
// {
//     if (session_status() !== PHP_SESSION_ACTIVE) {
//         session_start();
//     }
//     global $DSN;

//     $oCon = new Dbo();
//     $oCon->DSN = $DSN;
//     $oCon->Conectar();

//     $oReturn = new xajaxResponse();

//     $idempresa = $_SESSION['U_EMPRESA'];
//     $sHtml = '';

//     $sHtml .= '<br/>';
//     $sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 98%;">';
//     $sHtml .= '<tr>';
//     $sHtml .= '<td colspan="4"><h5>AJUNTOS <small>Reporte Informacion</small></h5></td>';
//     $sHtml .= '</tr>';
//     $sHtml .= '<tr>';
//     $sHtml .= '<td>No.</td>';
//     $sHtml .= '<td>Titulo</td>';
//     $sHtml .= '<td>Adjunto</td>';
//     $sHtml .= '<td></td>';
//     $sHtml .= '</tr>';

//     // Filtrar solo registros con estado 'AC'
//     $sql = "SELECT id, titulo, ruta
//             FROM comercial.archivos_uafe
//             WHERE empr_cod_empr = $idempresa
//             AND estado = 'AC'  
//             ORDER BY id DESC";
// 	// echo $sql;
// 	// exit;


//     if ($oCon->Query($sql)) {
//         if ($oCon->NumFilas() > 0) {
//             $i = 1;
//             do {
//                 $id = $oCon->f('id');
//                 $titulo = $oCon->f('titulo');
//                 $ruta = $oCon->f('ruta');

//                 $sHtml .= '<tr>';
//                 $sHtml .= '<td>' . $i++ . '</td>';
//                 $sHtml .= '<td>' . $titulo . '</td>';
//                 $sHtml .= '<td><a href="#" onclick="download(\'' . $ruta . '\')">' . $ruta . '</a></td>';
//                 $sHtml .= '<td><div class="btn btn-danger btn-sm" onclick="eliminar_adj(' . $id . ')">
//                                 <span class="glyphicon glyphicon-remove"></span>
//                             </div></td>';
//                 $sHtml .= '</tr>';
//             } while ($oCon->SiguienteRegistro());
//         } else {
//             $sHtml .= '<tr><td colspan="4" align="center">No hay archivos adjuntos</td></tr>';
//         }
//     }

//     $oCon->Free();

//     $oReturn->assign('divReporteAdjuntos', 'innerHTML', $sHtml);
//     return $oReturn;
// }

function consultarAdjuntos($aForm = '', $empr_cod_empr = 0)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    // SI NO SE PASA EMPRESA ESPECÍFICA, USA LA DE SESIÓN
    if ($empr_cod_empr == 0) {
        $idempresa = $_SESSION['U_EMPRESA'];
    } else {
        $idempresa = $empr_cod_empr;
    }

    $sHtml = '';

    $sHtml .= '<br/>';
    $sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 98%;">';
    $sHtml .= '<tr>';
    $sHtml .= '<td colspan="4"><h5>ADJUNTOS <small>Reporte Información</small></h5></td>';
    $sHtml .= '</tr>';
    $sHtml .= '<tr>';
    $sHtml .= '<td>No.</td>';
    $sHtml .= '<td>Título</td>';
    $sHtml .= '<td>Archivo</td>';
    $sHtml .= '<td>Acción</td>';
    $sHtml .= '</tr>';

    // CONSULTA (YA FILTRA POR EMPRESA)
    $sql = "SELECT id, titulo, ruta
            FROM comercial.archivos_uafe
            WHERE empr_cod_empr = $idempresa
            AND estado = 'AC'
            ORDER BY id ASC";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $i = 1;
            do {
                $id = $oCon->f('id');
                $titulo = $oCon->f('titulo');
                $ruta = $oCon->f('ruta');

                $sHtml .= '<tr>';
                $sHtml .= '<td>' . $i++ . '</td>';
                $sHtml .= '<td>' . $titulo . '</td>';
                // $sHtml .= '<td><a href="#" onclick="download(\'' . $ruta . '\')">' . basename($ruta) . '</a></td>';

                $ruta = str_replace('../', '', $ruta);
                $ruta_file = "../../Include/Clases/Formulario/Plugins/reloj/$ruta";

                $sHtml .= '<td>
                    <a href="' . $ruta_file . '" target="_blank">
                        Ver archivo
                    </a>
                </td>';


                $sHtml .= ' <td style="text-align:center; vertical-align:middle;">
                                    <div class="btn btn-danger btn-sm" onclick="javascript:eliminar_adj(' . $id . ');">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </div>
                            </td>';

                $sHtml .= '</tr>';
            } while ($oCon->SiguienteRegistro());
        } else {
            $sHtml .= '<tr><td colspan="4" align="center">No hay archivos adjuntos para esta empresa</td></tr>';
        }
    } else {
        $sHtml .= '<tr><td colspan="4" align="center">Error en la consulta</td></tr>';
    }

    $oCon->Free();

    $oReturn->assign('divReporteAdjuntos', 'innerHTML', $sHtml);
    return $oReturn;
}

function eliminar_adj($id = 0, $aForm = '')
{

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();
    //variables
    //usuario
    $usuario_web = $_SESSION['U_ID'];
    //empresa
    $idempresa = $_SESSION['U_EMPRESA'];


    try {

        $oIfx->QueryT('BEGIN WORK;');
        $sql_update = "UPDATE comercial.archivos_uafe 
                      SET estado = 'AN', 
                          usuario_actualiza = " . $usuario_web . ",
                          fecha_actualiza = NOW()
                      WHERE id = $id 
                      AND empr_cod_empr = " . $idempresa;
        // echo $sql_update;
        // exit;



        $oIfx->QueryT($sql_update);
        $oIfx->QueryT('COMMIT WORK');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Registro Eliminado...!',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            timer: 2000
        })");
        // $oReturn->script("consultar()");
        $oReturn->script('consultarAdjuntos();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        $oReturn->assign("ctrl", "value", 1);
    }


    return $oReturn;
}


function guardarAdjuntos($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    //mensaje de entrada
    //$oReturn->alert("Entró a guardar Adjuntos");


    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];
    $aDataGirdAdj = $_SESSION['aDataGirdAdj'];

    //variables del formulario
    $cliente = $aForm['codigoCliente'];
    $fechaServer = date("Y-m-d H:i:s");

    if (count($aDataGirdAdj) > 0) {
        try {

            $oCon->QueryT('BEGIN;');

            foreach ($aDataGirdAdj as $aValues) {
                $aux = 0;
                foreach ($aValues as $aVal) {
                    if ($aux == 0) {

                        $idAdj = $aVal;
                    } elseif ($aux == 1) {
                        $titulo = $aVal;
                    } elseif ($aux == 2) {
                        $adjunto = $aVal;



                        /*$sql = "insert into comercial.adjuntos_clpv (id_empresa, id_sucursal, id_clpv, titulo, ruta, estado, fecha_server, user_web)
											values($idempresa, $idsucursal, $cliente, '$titulo', '$adjunto', 'A', '$fechaServer', $usuario_web)";*/

                        $sql = "insert into comercial.archivos_uafe 
						(empr_cod_empr, titulo, ruta, estado, usuario_ingresa, fecha_ingresa)
						values ($idempresa, '$titulo', '$adjunto', 'AC', $usuario_web, '$fechaServer')";



                        //echo $sql;
                        //exit;

                        $oCon->QueryT($sql);
                        //$oReturn->alert($sql);
                    }
                    $aux++;
                } //fin foreach
            } //fin foreach


            $oCon->QueryT('COMMIT;');

            $oReturn->alert('Procesado Correctamente...!');
            $oReturn->script('consultarAdjuntos();');
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert('No existen Registros para procesar..!');
    }

    // echo "<pre>";
    // print_r($aDataGirdAdj);
    // echo "</pre>";

    return $oReturn;
}


function elimina_detalleAdj($id)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    // Recuperar grid
    $aDataGrid = $_SESSION['aDataGirdAdj'];

    // eliminar la fila
    unset($aDataGrid[$id]);

    // Reindexar
    $aDataGrid = array_values($aDataGrid);

    // Guardar nuevamente
    $_SESSION['aDataGirdAdj'] = $aDataGrid;

    // Volver a dibujar
    $sHtml = mostrar_gridAdj();
    $oReturn->assign("gridArchivos", "innerHTML", $sHtml);

    return $oReturn;
}













///GUARDAR
function guardar($aForm = '')
{
    // var_dump($aForm);exit;
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

    //  LECTURA SUCIA
    //////////////
    // en AFORM VA SIEMPRE EL NAME
    // $idempresa =  	$aForm['empresa'];
    $factu = $aForm['factu'];
    $fac = $aForm['fac'];
    $aut = $aForm['aut'];
    $lotes = $aForm['lotes'];
    $bodegaser = $aForm['bodegaser'];
    $orden = $aForm['orden'];
    $credfis = $aForm['credfis'];
    $numdig = $aForm['numdig'];
    $docierreant = $aForm['docierreant'];
    $ctaret = $aForm['ctaret'];
    $codigo = $aForm['id_pccp'];
    $par_liq = $aForm['parliq'];
    $sec_fgasto = $aForm['sec_fgasto'];
    $cuenta_no_domiciliado = $aForm['cuenta_no_domiciliado'];
    $tran_det = $aForm['tran_det'];

    //echo $tipo; exit;
    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    if (empty(trim($codigo))) {
        $codigo = 0;
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




    // PARA CADA UNO DE LOS CHEECK SE DEBE INGRESAR LA CONDICION CHECK FACTURACION ELECTRONICA

    if (empty($factu)) {
        $factu = "N";
    }

    // PARA CADA UNO DE LOS CHEECK SE DEBE INGRESAR LA CONDICION CHECK LOTES

    if (empty($lotes)) {
        $lotes = "N";
    }

    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');

        // LINEA PARA CONTROLAR EL CONSTRAINT

        if (!empty($codigo) || $codigo == 0) {
            $sql_control = "select count(*) as control from saepccp where pccp_cod_pccp = $codigo";
            $control = consulta_string($sql_control, 'control', $oIfx, 0);
        } else {
            $control = 0;
        }


        /// INSERTAR EN LA BASE INFORMIX
        if ($control == 0) {
            if (empty($numdig)) {
                $numdig = 0;
            }
            $sql = "INSERT INTO saepccp (pccp_cod_empr, pccp_fac_elec, pccp_cod_facp, pccp_aut_pago, pccp_lot_snp, pccp_bod_serv,pccp_num_orpa, pccp_cre_fis, pccp_num_digi, pccp_tidu_anti, pccp_cret_asumi, pccp_par_liq, pccp_nur_orpa, pccp_pat_email, pccp_tran_det) 
					VALUES ('$idempresa','$factu', '$fac', '$aut', '$lotes', $bodegaser ,'$orden', '$credfis', '$numdig', '$docierreant', '$ctaret','$par_liq', '$sec_fgasto', '$cuenta_no_domiciliado', '$tran_det')";
            $mensaje = 'Ingresado Correctamente...';
        } else {
            //  ACTUALIZAR LA BASE
            $sql = "UPDATE saepccp SET
						pccp_fac_elec=   '$factu', 
						pccp_cod_facp=   '$fac', 
						pccp_aut_pago=   '$aut', 
						pccp_bod_serv=   '$bodegaser', 
						pccp_lot_snp=    '$lotes', 
						pccp_num_orpa=   '$orden', 
						pccp_cre_fis=    '$credfis', 
						pccp_num_digi=   '$numdig', 
						pccp_tidu_anti=  '$docierreant', 
						pccp_cret_asumi= '$ctaret',
						pccp_par_liq   =  '$par_liq',
						pccp_nur_orpa   =  '$sec_fgasto',
						pccp_pat_email   =  '$cuenta_no_domiciliado',
						pccp_tran_det = '$tran_det'

				    WHERE pccp_cod_empr= '$idempresa'";
            //echo $sql;exit;
            $mensaje = 'Modificado Correctamente...';
        }
        $tipo_mesaje = 'success';
        $oIfx->QueryT($sql);
        $oIfx->QueryT('COMMIT WORK;');
        $oReturn->script('alerts("' . $mensaje . '", "' . $tipo_mesaje . '");');
        $oReturn->script('recargar()');
        $oReturn->script('consultarPara()');
        //  consultarPara
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }


    return $oReturn;
}

///CONSULTAR
function consultar($aForm = '')
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




    $table_op .= '<table id="example" class="table table-striped table-bordered table-hover table-condensed" >
						<thead>
							<tr>
							  <td class="bg-primary">ID</td>
								
								<td class="bg-primary">Empresa</td>
								<td class="bg-primary">Fac. Electronica</td>
								<td class="bg-primary">Codigo Factura</td>
								<td  class="bg-primary">Aut. Pagos</td>
								<td  class="bg-primary">Por Lotes</td>
								<td  class="bg-primary">Bodega Servicios</td>
								<td  class="bg-primary">Orden de Pago</td>
								<td  class="bg-primary">Credito Fiscal</td>
								<td  class="bg-primary">No. Digitos</td>
								<td  class="bg-primary">Doc. Cierre Anticipos</td>
								<td  class="bg-primary">Cta Ret Asu. Empresa1</td>
							</tr>
						</thead>
					';
    $sql = "SELECT * FROM saepccp where pccp_cod_empr = $idempresa";

    // echo '<pre>' . $sql;
    // exit;

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codigo          = $oIfx->f('pccp_cod_pccp');
                $factu          = $oIfx->f('pccp_fac_elec');
                $fac            = $oIfx->f('pccp_cod_facp');
                $aut            = $oIfx->f('pccp_aut_pago');
                $bodegaser      = $oIfx->f('pccp_bod_serv');
                $lotes          = $oIfx->f('pccp_lot_snp');
                $orden          =  $oIfx->f('pccp_num_orpa');
                $credfis        =  $oIfx->f('pccp_cre_fis');
                $numdig         =  $oIfx->f('pccp_num_digi');
                $docierreant    =  $oIfx->f('pccp_tidu_anti');
                $ctaret         =  $oIfx->f('pccp_cret_asumi');

                if ($sClass == 'off') $sClass = 'on';
                else $sClass = 'off';
                $table_op .= '<tr height="20" class="' . $sClass . '"
												onMouseOver="javascript:this.className=\'link\';"
												onMouseOut="javascript:this.className=\'' . $sClass . '\';">';

                $table_op .= '<td align="right">' . $i . '</td>';
                $table_op .= '<td>' . $codigo . '</td>';

                $table_op .= '<td>' . $factu . '</td>';
                $table_op .= '<td>' . $fac . '</td>';
                $table_op .= '<td>' . $aut . '</td>';
                $table_op .= '<td>' . $lotes . '</td>';
                $table_op .= '<td>' . $bodegaser . '</td>';
                $table_op .= '<td>' . $orden . '</td>';
                $table_op .= '<td>' . $credfis . '</td>';
                $table_op .= '<td>' . $numdig . '</td>';
                $table_op .= '<td>' . $docierreant . '</td>';
                $table_op .= '<td>' . $ctaret . '</td>';
                $table_op .= '</tr>';
                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    // MOSTRAR LO QUE ESTA PREVIAMENTE SELECCIONADO EN LAS OPCIONES

    // OPCION DE CHECK
    if ($factu == 'S') {
        $oReturn->assign("factu", "checked", "true");
    }

    if ($lotes == 'S') {
        $oReturn->assign("lotes", "checked", "true");
    }

    // OPCION CON SELECT2

    $oReturn->script("$('#fac').val('$fac').trigger('change.select2')");
    $oReturn->script("$('#aut').val('$aut').trigger('change.select2')");
    $oReturn->script("$('#bodegaser').val('$bodegaser').trigger('change.select2')");
    $oReturn->script("$('#docierreant').val('$docierreant').trigger('change.select2')");

    // OPCION CON SELECT NORMAL
    $oReturn->assign("orden", "value", $orden);
    $oReturn->assign("credfis", "value", $credfis);
    $oReturn->assign("numdig", "value", $numdig);
    $oReturn->assign("ctaret", "value", $ctaret);

    // MOSTRAR LA TABLA.
    $oReturn->assign("ejemplo", "innerHTML", $table_op);
    return $oReturn;
}



// CUENTA RETENCION FUENTE BIENES
function cod_ret_b($op, $ret_nom, $aForm = '', $tipo = 0)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa      = $_SESSION['U_EMPRESA'];
    $idsucursal     = $aForm['sucursal'];

    $ret_nom = $aForm['cuenta_no_domiciliado'];

    $sql = "select tret_cod, tret_det_ret, tret_porct,tret_cta_cre
                from saetret where
                tret_cod_empr = $idempresa and
                tret_ban_retf = 'IR' and
                tret_ban_crdb = 'CR' and
                tret_cod      = '$ret_nom'
                order by 1 ";
    $oIfx->Query($sql);
    $cont = $oIfx->NumFilas();

    if ($cont == 1) {
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $codigo  = $oIfx->f('tret_cod');
                $porc    = $oIfx->f('tret_porct');
                $cuen_cod = $oIfx->f('tret_cta_cre');
            }
        }



        // -----------------------------------------------------------------------------------------------------------
        // Traemos la configuracion de los decimales
        // -----------------------------------------------------------------------------------------------------------
        $empr_cod_pais = $_SESSION['U_PAIS_COD'];

        // TIPO RETENCION => RETENCION O DETRACCION
        //$sql_tipo_rete = "select * from comercial.pais_etiq_imp p where p.pais_cod_pais = $empr_cod_pais and impuesto = 'TIPO_RETENCION' ";
        //$tipo_rete = consulta_string_func($sql_tipo_rete, 'etiqueta', $oIfx, '');

        // DECIMALES DETRACCION
        $sql_decimales_rete = "select * from comercial.pais_etiq_imp p where p.pais_cod_pais = $empr_cod_pais and impuesto = 'DECIMALES_RETENCION' ";
        $decimales_rete = consulta_string_func($sql_decimales_rete, 'etiqueta', $oIfx, 0);
        if (empty($decimales_rete)) {
            $decimales_rete = 2;
        }


        // -----------------------------------------------------------------------------------------------------------
        // FIN Traemos la configuracion de los decimales
        // -----------------------------------------------------------------------------------------------------------


        if ($ret_nom == 327) {
            $oReturn->script('muestra_divi();');
        }
    } else {
        $oReturn->script('ventana_cod_ret_b(' . $op . ', ' . $tipo . ');');
    }


    return $oReturn;
}




/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
