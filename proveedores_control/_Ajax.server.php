<?php

require("_Ajax.comun.php"); // No modificar esta linea
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */

function genera_cabecera_formulario($sAccion = 'nuevo', $aForm = '')
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfx1 = new Dbo();
	$oIfx1->DSN = $DSN_Ifx;
	$oIfx1->Conectar();

	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$fu = new Formulario;
	$fu->DSN = $DSN;

	$ifu = new Formulario;
	$ifu->DSN = $DSN_Ifx;

	$oReturn = new xajaxResponse();

	//variables de sesion
	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];

	//variables del formulario
	$empresa = $aForm['empresa'];
	$sucursal = $aForm['sucursal'];

	if (empty($empresa)) {
		$empresa = $idempresa;
	}
	if (empty($sucursal)) {
		$sucursal = $idsucursal;
	}

	$sql_moneda = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa";
	$moneda_base = consulta_string($sql_moneda, 'pcon_mon_base', $oIfx, '0');


	switch ($sAccion) {
		case 'nuevo':
			$ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr, empr_nom_empr
															from saeempr
															WHERE empr_cod_empr = $idempresa
															order by empr_cod_empr", true, 170, 150, true);

			$ifu->AgregarComandoAlCambiarValor('empresa', 'f_filtro_sucursal();');
			$ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', "select sucu_cod_sucu, sucu_nom_sucu
																	from saesucu
																	where sucu_cod_empr = $empresa", false, 170, 150, true);
			// $ifu->AgregarCampoFecha('fecha_corte', 'Fin|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
			$ifu->AgregarCampoListaSQL('moneda', 'Moneda|left', "select mone_cod_mone, upper(mone_des_mone) as mone_des_mone
															from saemone
															where mone_cod_empr = $empresa
															order by mone_des_mone", true, 170, 150, true);

			$ifu->AgregarCampoTexto('proveedor', 'Proveedor|left', false, '', 290, 150, true);
			$ifu->AgregarComandoAlEscribir('proveedor', 'buca_proveedor(event, id); form1.proveedor.value=form1.proveedor.value.toUpperCase();');
			$ifu->AgregarCampoOculto('clpv_cod_clpv', '');
			$ifu->AgregarCampoTexto('factura', 'No. Factura|left', false, '', 170, 150, true);
			$ifu->AgregarCampoListaSQL('grupo', 'Grupo|left', "select grpv_cod_grpv, grpv_nom_grpv
															  from saegrpv
															  where grpv_cod_empr = $empresa
															  and grpv_cod_modu = 4", false, 150, 150, true);
			$ifu->AgregarCampoListaSQL('zona', 'Zona|left', "select provc_cod_provc, provc_nom_provc
															  from saeprovc", false, 170, 150, true);
			$ifu->AgregarComandoAlCambiarValor('zona', 'f_filtro_ciudad();');
			$ifu->AgregarCampoListaSQL('ciudad', 'Ciudad|left', '', false, 170, 150, true);

			$ifu->AgregarCampoListaSQL('vendedor', 'Vendedor|left', "select vend_cod_vend, vend_nom_vend
															  from saevend
															  where vend_cod_empr = $empresa
															  and vend_cod_sucu = $sucursal", false, 170, 150, true);
			$ifu->cCampos["empresa"]->xValor = $empresa;
			$ifu->cCampos["moneda"]->xValor = $moneda_base;
			//echo $idsucursal; exit;
	}

	$table_op .= '<table class="table table-bordered table-condensed" style="margin-bottom: 0px; width: 90%;">
					<tr> 
						<td colspan="10" align="center" class="bg-primary">CONTROL DE PROVEEDORES</td>
					</tr>
					<tr>
						<td colspan = "10">    
							<div class="btn-group">
								<div class="btn btn-primary btn-sm" onclick="document.location=\'excel.php?\'" >
									<span class="glyphicon glyphicon-print"></span>
									Excel
								</div>							
								<div class="btn btn-primary btn-sm" onclick="f_pdf();" id = "exportar">
										<span class="glyphicon glyphicon-print"></span>
										Imprimir
								</div>								
							</div>
						</td>                   
					</tr>
					<tr class="info">
						<td colspan="10" align="center">Los campos con * son de ingreso obligatorio</td>
					</tr>
					<tr class="info">
						<td style="width: 75%;" colspan="6" align="center">Filtros</td>
						<td style="width: 75%;" colspan="2" align="center">Corte Factura</td>
						<td style="width: 10%;" align="center">Fecha de Corte</td>
						<td style="width: 15%;" align="center">Ordena Por</td>
					</tr>
					<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empresa') . '</td>
						<td>' . $ifu->ObjetoHtml('empresa') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('grupo') . ' </td>						
						<td>' . $ifu->ObjetoHtml('grupo') . ' </td>
						<td>' . $ifu->ObjetoHtmlLBL('zona') . ' </td>						
						<td>' . $ifu->ObjetoHtml('zona') . ' </td>
						<td>	<label for="">Fec. Emis</label><br>
						<input type="radio" name="tipo" id="dcmp_fec_emis" value="dcmp_fec_emis" checked/></td>						
						<td><label for="">Fec. Vence</label>
						<input type="radio" name="tipo" id="dmcp_fec_ven" value="dmcp_fec_ven" onclick=""/></td>
						<td style="text-align:center"><input type="date" name="fecha_corte" id="fecha_corte" value="' . date("Y-m-d") . '"></td>
						<td>
							<table style="width: 100%;">
								<tr>
									<td>
										<label for="orden">Proveedor</label>
										<input type="radio" name="orden" value="proveedor" checked="true" />
									</td>
									<td>
										<label for="orden">Ciudad</label>
										<input type="radio" name="orden" value="ciudad" />						
									</td>
								</tr>	
							</table>
						</td>												
					</tr>	
					<tr>
						<td>' . $ifu->ObjetoHtmlLBL('ciudad') . ' </td>						
						<td>' . $ifu->ObjetoHtml('ciudad') . ' </td>						
						<td>' . $ifu->ObjetoHtmlLBL('moneda') . ' </td>
						<td>' . $ifu->ObjetoHtml('moneda') . ' </td>
						<td></td>
						<td></td>	<td></td>
						<td></td>						
						<td><label for="anticipos">Sin Anticipos</label>
							<input type="checkbox" name="anticipos" id="anticipos" value="S" onclick="cambioRemesa(id)"/>
						</td>						
						<td><label for="solo_ant">Solo Anticipos</label>
							<input type="checkbox" name="solo_ant" id="solo_ant" value="S" onclick="cambioRemesa(id)"/>
						</td>						

					 </tr>
					 <tr>
						<td>' . $ifu->ObjetoHtmlLBL('factura') . ' </td>
						<td>' . $ifu->ObjetoHtml('factura') . ' </td>	
						<td>' . $ifu->ObjetoHtml('clpv_cod_clpv') . '' . $ifu->ObjetoHtmlLBL('proveedor') . ' </td>
						<td colspan="3">' . $ifu->ObjetoHtml('proveedor') . '
							<div id ="proveedor" class="btn btn-primary btn-sm" onclick="buca_proveedor_id()">
						         <span class="glyphicon glyphicon-list-alt"><span>
						    </div></td>
						</td>

						
					</tr>
					<tr>
						<td colspan = "8" align="center">    
							<div class="btn-group">
								<div class="btn btn-primary btn-sm" onclick="generar();" id = "generar">
										<span class="glyphicon glyphicon-search"></span>
										Consultar
								</div>
							</div>
						</td>                   
					</tr>

                </table>				
				<br>
				<div id = "DivReporte"> </div>';
	$table_op .= '</fieldset>';
	$oReturn->assign("divFormularioReportesGrupos", "innerHTML", $table_op);

	return $oReturn;
}

function f_filtro($aForm = '', $data = '')
{
	//Definiciones
	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();


	//variables formulario
	$empresa = $aForm['empresa'];
	$modulo = $aForm['modulo'];

	// DATOS EMPRESA
	$sql1 = "select tidu_cod_tidu,  tidu_des_tidu
			from saetidu
			where tidu_cod_empr = '$empresa' and 
			tidu_cod_modu = '$modulo'				
			order by 2";
	$i = 1;
	if ($oIfx->Query($sql1)) {
		$oReturn->script('eliminar_lista_documentos();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$tipo_documento = $oIfx->f('tidu_cod_tidu') . ' ' . $oIfx->f('tidu_des_tidu');
				$oReturn->script(('anadir_elemento_documentos(' . $i++ . ',\'' . $oIfx->f('tidu_cod_tidu') . '\', \'' . $tipo_documento . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}

	return $oReturn;
}

function f_filtro_sucursal($aForm, $data)
{
	//Definiciones
	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();
	//echo 'hola';
	//variables formulario
	$empresa = $aForm['empresa'];

	// DATOS EMPRESA
	$sql = "select sucu_cod_sucu, sucu_nom_sucu
			from saesucu
			where sucu_cod_empr = '$empresa'			
			order by sucu_nom_sucu";
	//echo $sql; exit;
	$i = 1;
	if ($oIfx->Query($sql)) {
		$oReturn->script('eliminar_lista_sucursal();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$oReturn->script(('anadir_elemento_sucursal(' . $i++ . ',\'' . $oIfx->f('sucu_cod_sucu') . '\', \'' . $oIfx->f('sucu_nom_sucu') . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oReturn->assign('sucursal', 'value', $data);
	return $oReturn;
}

function f_filtro_ciudad($aForm, $data)
{
	//Definiciones
	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables formulario
	$provincia = $aForm['zona'];

	// DATOS EMPRESA
	$sql = "select ciud_cod_ciud, ciud_nom_ciud
			from saeciud
			where ciud_cod_provc = '$provincia'			
			order by ciud_nom_ciud";
	//echo $sql; exit;
	$i = 1;
	if ($oIfx->Query($sql)) {
		$oReturn->script('eliminar_lista_ciudad();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$oReturn->script(('anadir_elementos_ciudad(' . $i++ . ',\'' . $oIfx->f('ciud_cod_ciud') . '\', \'' . $oIfx->f('ciud_nom_ciud') . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oReturn->assign('ciudad', 'value', $data);
	return $oReturn;
}

function verDiarioContable($aForm = '', $empr = 0, $sucu = 0, $ejer = 0, $mes = 0, $asto = '')
{

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx, $DSN;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oReturn = new xajaxResponse();

	//variables del formulario
	$empresa = $aForm['empresa'];
	$anio = $aForm['anio'];
	$mes_1 = $aForm['mes_1'];
	$mes_2 = $aForm['mes_2'];
	$nivel = $aForm['nivel'];
	$campo = 0;

	$class = new GeneraDetalleAsientoContable();

	$arrayAsto = $class->informacionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);
	//var_dump($arrayAsto);exit;
	$arrayDiario = $class->diarioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

	$arrayDirectorio = $class->directorioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

	$arrayRetencion = $class->retencionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

	$arrayAdjuntos = $class->adjuntosAsientoContable($oCon, $empr, $sucu, $ejer, $mes, $asto);

	try {

		//LECTURA SUCIA1
		//////////////


		//sucursal
		$sql = "select sucu_nom_sucu from saesucu where sucu_cod_sucu = $sucu";
		$sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');


		$oReturn->assign("divTituloAsto", "innerHTML", $asto . ' - ' . $sucu_nom_sucu);

		if (count($arrayAsto) > 0) {

			$table .= '<table class="table table-striped table-condensed" align="center" width="98%">';
			$table .= '<tr>';
			$table .= '<td colspan="4" class="bg-primary">DIARIO CONTABLE</td>';
			$table .= '</tr>';

			foreach ($arrayAsto as $val) {
				$asto_cod_asto = $val[0];
				$asto_vat_asto = $val[1];
				$asto_ben_asto = $val[2];
				$asto_fec_asto = $val[3];
				$asto_det_asto = $val[4];
				$asto_cod_modu = $val[5];
				$asto_usu_asto = $val[6];
				$asto_user_web = $val[7];
				$asto_fec_serv = $val[8];
				$asto_cod_tidu = $val[9];

				//modulo
				$sql = "select modu_des_modu from saemodu where modu_cod_modu = $asto_cod_modu";
				$modu_des_modu = consulta_string_func($sql, 'modu_des_modu', $oIfx, '');

				//tipo documento
				$sql = "select tidu_des_tidu from saetidu where tidu_cod_tidu = $asto_cod_tidu";
				$tidu_des_tidu = consulta_string_func($sql, 'tidu_des_tidu', $oIfx, '');

				$table .= '<tr>';
				$table .= '<td>Diario:</td>';
				$table .= '<td>' . $asto_cod_asto . '</td>';
				$table .= '<td>Fecha:</td>';
				$table .= '<td>' . $asto_fec_asto . '</td>';
				$table .= '</tr>';

				$table .= '<tr>';
				$table .= '<td>Beneficiario:</td>';
				$table .= '<td colspan="3">' . $asto_ben_asto . '</td>';
				$table .= '</tr>';

				$table .= '<tr>';
				$table .= '<td>Modulo:</td>';
				$table .= '<td>' . $modu_des_modu . '</td>';
				$table .= '<td>Documento:</td>';
				$table .= '<td>' . $asto_cod_tidu . ' - ' . $tidu_des_tidu . '</td>';
				$table .= '</tr>';

				$table .= '<tr>';
				$table .= '<td>Detalle:</td>';
				$table .= '<td colspan="3">' . $asto_det_asto . '</td>';
				$table .= '</tr>';
				//sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod
				$table .= '<tr>';
				$table .= '<td>Formato:</td>';
				$table .= '<td align="left">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
				$table .= '<td>Valor:</td>';
				$table .= '<td class="bg-danger fecha_letra" align="left">' . number_format($asto_vat_asto, 2, '.', ',') . '</td>';
				$table .= '</tr>';
			} //fin foreach

			$table .= '</table>';

			$oReturn->assign("divInfo", "innerHTML", $table);
		}

		//directorio
		if (count($arrayDiario) > 0) {

			$tableDia .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
			$tableDia .= '<tr>';
			$tableDia .= '<td colspan="5" class="bg-primary">DIARIO</td>
						<td align="center">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
			$tableDia .= '</tr>';
			$tableDia .= '<tr>';
			$tableDia .= '<td>Cuenta Contable</td>';
			$tableDia .= '<td>Centro Costos</td>';
			$tableDia .= '<td>Centro Actividad</td>';
			$tableDia .= '<td>Documento</td>';
			$tableDia .= '<td>Debito</td>';
			$tableDia .= '<td>Credito</td>';
			$tableDia .= '</tr>';
			$totalDeb = 0;
			$totalCre = 0;
			foreach ($arrayDiario as $val) {
				$dasi_cod_cuen = $val[0];
				$dasi_cod_cact = $val[1];
				$ccos_cod_ccos = $val[2];
				$dasi_dml_dasi = $val[3];
				$dasi_cml_dasi = $val[4];
				$dasi_det_asi = $val[5];
				$dasi_num_depo = $val[6];

				//clpv
				$cuen_nom_cuen = '';
				if (!empty($dasi_cod_cuen)) {
					$sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$dasi_cod_cuen' and cuen_cod_empr = $empr";
					$cuen_nom_cuen = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
				}

				$ccosn_nom_ccosn = '';
				if (!empty($ccos_cod_ccos)) {
					$sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn = '$ccos_cod_ccos' and ccosn_cod_empr = $empr";
					$ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
				}

				$cact_nom_cact = '';
				if (!empty($dasi_cod_cact)) {
					$sql = "select cact_nom_cact from saecact where cact_cod_cact = '$dasi_cod_cact' and cact_cod_empr = $empr";
					$cact_nom_cact = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
				}

				$tableDia .= '<tr>';
				$tableDia .= '<td>' . $dasi_cod_cuen . ' - ' . $cuen_nom_cuen . '</td>';
				$tableDia .= '<td>' . $ccos_cod_ccos . ' - ' . $ccosn_nom_ccosn . '</td>';
				$tableDia .= '<td>' . $dasi_cod_cact . ' - ' . $cact_nom_cact . '</td>';
				$tableDia .= '<td>' . $dasi_num_depo . '</td>';
				$tableDia .= '<td align="right">' . number_format($dasi_dml_dasi, 2, '.', ',') . '</td>';
				$tableDia .= '<td align="right">' . number_format($dasi_cml_dasi, 2, '.', ',') . '</td>';
				$tableDia .= '</tr>';

				$totalDeb += $dasi_dml_dasi;
				$totalCre += $dasi_cml_dasi;
			} //fin foreach
			$tableDia .= '<tr>';
			$tableDia .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
			$tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
			$tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
			$tableDia .= '</tr>';
			$tableDia .= '</table>';

			$oReturn->assign("divDiario", "innerHTML", $tableDia);
		}

		//directorio
		if (count($arrayDirectorio) > 0) {

			$tableDir .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
			$tableDir .= '<tr>';
			$tableDir .= '<td colspan="6" class="bg-primary">DIRECTORIO</td>';
			$tableDir .= '</tr>';
			$tableDir .= '<tr>';
			$tableDir .= '<td>No.</td>';
			$tableDir .= '<td>Cliente/Proveedor</td>';
			$tableDir .= '<td>Transaccion</td>';
			$tableDir .= '<td>Factura</td>';
			$tableDir .= '<td>Credito</td>';
			$tableDir .= '<td>Debito</td>';
			$tableDir .= '</tr>';
			$totalDeb = 0;
			$totalCre = 0;
			foreach ($arrayDirectorio as $val) {
				$dir_cod_dir = $val[0];
				$dir_cod_cli = $val[1];
				$tran_cod_modu = $val[2];
				$dir_cod_tran = $val[3];
				$dir_num_fact = $val[4];
				$dir_detalle = $val[5];
				$dir_fec_venc = $val[6];
				$dir_deb_ml = $val[7];
				$dir_cre_ml = $val[8];

				//clpv
				$clpv_nom_clpv = '';
				if (!empty($dir_cod_cli)) {
					$sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $dir_cod_cli";
					$clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
				}

				$tableDir .= '<tr>';
				$tableDir .= '<td>' . $dir_cod_dir . '</td>';
				$tableDir .= '<td>' . $clpv_nom_clpv . '</td>';
				$tableDir .= '<td>' . $dir_cod_tran . '</td>';
				$tableDir .= '<td>' . $dir_num_fact . '</td>';
				$tableDir .= '<td align="right">' . number_format($dir_cre_ml, 2, '.', ',') . '</td>';
				$tableDir .= '<td align="right">' . number_format($dir_deb_ml, 2, '.', ',') . '</td>';
				$tableDir .= '</tr>';

				$totalCre += $dir_cre_ml;
				$totalDeb += $dir_deb_ml;
			} //fin foreach
			$tableDir .= '<tr>';
			$tableDir .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
			$tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
			$tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
			$tableDir .= '</tr>';
			$tableDir .= '</table>';

			$oReturn->assign("divDirectorio", "innerHTML", $tableDir);
		}

		//retencion
		if (count($arrayRetencion) > 0) {

			$tableRet .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
			$tableRet .= '<tr>';
			$tableRet .= '<td colspan="8" class="bg-primary">RETENCION</td>';
			$tableRet .= '</tr>';
			$tableRet .= '<tr>';
			$tableRet .= '<td>Cliente/Proveedor</td>';
			$tableRet .= '<td>Factura</td>';
			$tableRet .= '<td>Retencion</td>';
			$tableRet .= '<td>Codigo</td>';
			$tableRet .= '<td>Porcentaje</td>';
			$tableRet .= '<td>Base Imp.</td>';
			$tableRet .= '<td>Valor</td>';
			$tableRet .= '<td>Print</td>';
			$tableRet .= '</tr>';
			foreach ($arrayRetencion as $val) {
				$ret_cta_ret = $val[0];
				$ret_porc_ret = $val[1];
				$ret_bas_imp = $val[2];
				$ret_valor = $val[3];
				$ret_num_ret = $val[4];
				$ret_detalle = $val[5];
				$ret_num_fact = $val[6];
				$ret_ser_ret = $val[7];
				$ret_cod_clpv = $val[8];
				$ret_fec_ret = $val[9];

				//clpv
				$clpv_nom_clpv = '';
				if (!empty($ret_cod_clpv)) {
					$sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $ret_cod_clpv";
					$clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
				}

				//fprv
				$printRet = '';
				if ($asto_cod_modu == 4 || $asto_cod_modu == 6) {

					//fecha fprv o minv
					if ($asto_cod_modu == 4) {
						$sql = "select fprv_fec_emis 
								from saefprv
								where fprv_cod_clpv = $ret_cod_clpv and
								fprv_num_fact = '$ret_num_fact' and
								fprv_cod_asto = '$asto' and
								fprv_cod_ejer = $ejer and
								fprv_cod_empr = $empr and
								fprv_cod_sucu = $sucu";
						$fechaEmis = consulta_string_func($sql, 'fprv_fec_emis', $oIfx, '');
					} elseif ($asto_cod_modu == 6) {
						$sql = "select minv_fmov 
								from saeminv
								where minv_cod_clpv = $ret_cod_clpv and
								minv_fac_prov = '$ret_num_fact' and
								minv_comp_cont = '$asto' and
								minv_cod_ejer = $ejer and
								minv_cod_empr = $empr and
								minv_cod_sucu = $sucu";
						$fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
					}

					$printRet = '<div class="btn btn-primary btn-sm" onclick="genera_documento(5, \'' . $campo . '\',\'' . $fprv_clav_sri . '\' ,
																				 \'' . $ret_cod_clpv . '\'  , \'' . $ret_num_fact . '\', \'' . $ejer . '\',
																				 \'' . $asto . '\',  \'' . $fechaEmis . '\', ' . $sucu . ');">
									<span class="glyphicon glyphicon-print"></span>
								</div>';
				}

				$tableRet .= '<tr>';
				$tableRet .= '<td>' . $clpv_nom_clpv . '</td>';
				$tableRet .= '<td>' . $ret_num_fact . '</td>';
				$tableRet .= '<td>' . $ret_ser_ret . ' - ' . $ret_num_ret . '</td>';
				$tableRet .= '<td>' . $ret_cta_ret . '</td>';
				$tableRet .= '<td align="right">' . $ret_porc_ret . '</td>';
				$tableRet .= '<td align="right">' . number_format($ret_bas_imp, 2, '.', ',') . '</td>';
				$tableRet .= '<td align="right">' . number_format($ret_valor, 2, '.', ',') . '</td>';
				$tableRet .= '<td align="center">' . $printRet . '</td>';
				$tableRet .= '</tr>';
			} //fin foreach

			$tableRet .= '</table>';

			$oReturn->assign("divRetencion", "innerHTML", $tableRet);
		}

		//adjuntos
		if (count($arrayAdjuntos) > 0) {

			$tableAdj .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
			$tableAdj .= '<tr>';
			$tableAdj .= '<td colspan="2" class="bg-primary">ARCHIVOS ADJUNTOS</td>';
			$tableAdj .= '</tr>';
			$tableAdj .= '<tr>';
			$tableAdj .= '<td>Titulo</td>';
			$tableAdj .= '<td>Ruta</td>';
			$tableAdj .= '</tr>';
			foreach ($arrayAdjuntos as $val) {
				$titulo = $val[0];
				$ruta = $val[1];

				$tableAdj .= '<tr>';
				$tableAdj .= '<td>' . $titulo . '</td>';
				$tableAdj .= '<td><a href="#" onclick="dowloand(\'' . $ruta . '\')">' . $ruta . '</a></td>';
				$tableAdj .= '</tr>';
			} //fin foreach

			$tableAdj .= '</table>';

			$oReturn->assign("divAdjuntos", "innerHTML", $tableAdj);
		}
	} catch (Exception $e) {
		$oReturn->alert($e->getMessage());
	}

	return $oReturn;
}


function generar($aForm = '')
{
	
	global $DSN_Ifx, $DSN;
	
	session_start();
	
	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo();
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$oIfxB = new Dbo();
	$oIfxB->DSN = $DSN_Ifx;
	$oIfxB->Conectar();
	$oReturn = new xajaxResponse();
	//$oReturn->alert('Buscando....');

	//variables de sesion
	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];
	// VARIABLES
	$id_empresa = $aForm['empresa'];
	$fecha_corte = $aForm['fecha_corte'];
	$fecha_corte = date("Y-m-d", strtotime($fecha_corte));
	$id_cliente = $aForm['clpv_cod_clpv'];
	$proveedor = $aForm['proveedor'];
	$id_grupo = $aForm['grupo'];
	$ciudad_cod = $aForm['ciudad'];
	$vend_ord = $op;
	//$ciud_ord = $aForm['ciud_ord'];
	$clpv_ord = $aForm['orden'];
	$proyec_cod = $aForm['proyecto'];
	$sinAnticipos = $aForm['anticipos'];
	$solo_anticipos = $aForm['solo_ant'];
	$tipo = $aForm['tipo'];
	if (empty($sinAnticipos))
	{
		$sinAnticipos = 'N';
	}
	if (empty($solo_anticipos))
	{
		$solo_anticipos = 'N';
	}

	if ($sinAnticipos == 'S') {
		$filtroSinAnticipos = "AND d.dmcp_cod_tran not in (select  tran_cod_tran	
													from saetran
													where  tran_cod_modu  = 4
													and tran_ant_tran = 1
													and tran_cod_empr = $idempresa
													and tran_cod_sucu ||''like '$idsucursal')";
	}
	if ($solo_anticipos == 'S') {
		$filtroSoloAnticipos = "AND d.dmcp_cod_tran in (select  tran_cod_tran	
													from saetran
													where  tran_cod_modu  = 4
													and tran_ant_tran = 1
													and tran_cod_empr = $idempresa
													and tran_cod_sucu ||''like '$idsucursal')";
	}

	//echo $filtroSinAnticipos; exit;
	$sql_ord = '';
	/* if (!empty($ciud_ord)) {
        $sql_ord = " c.clpv_cod_ciud, ";
    }*/

	if (!empty($clpv_ord)) {
		if ($clpv_ord == "ciudad") $sql_ord = " c.clpv_cod_ciud, ";
		if ($clpv_ord == "proveedor") $sql_ord = " c.clpv_nom_clpv, ";
	}

	if (!empty($proyec_cod)) {
		$sql_ord = " c.clpv_nom_clpv, dmcp_cod_sucu, ";
	}
	//echo $sql_ord; exit;

	if (empty($proveedor)) $id_cliente = null;
	$sql_tmp = '';
	if (!empty($id_cliente)) {
		$sql_tmp = " and d.clpv_cod_clpv = $id_cliente ";
	}

	$sql_tmp2 = '';
	if (!empty($ciudad_cod)) {
		$sql_tmp2 = " and c.clpv_cod_ciud = $ciudad_cod  ";
	}

	$sql_tmp3 = '';
	if (!empty($id_grupo)) {
		$sql_tmp3 = " and c.grpv_cod_grpv = '$id_grupo'  ";
	}


	//  LECTURA SUCIA
	//////////////$oIfx->QueryT('set isolation to dirty read;');

	// CIUDAD
	$sql = "select ciud_cod_ciud, ciud_nom_ciud from saeciud";
	unset($array_ciud);
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$array_ciud[$oIfx->f('ciud_cod_ciud')] = $oIfx->f('ciud_nom_ciud');
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();
	//var_dump($array_ciud);  exit;

	// SUCURSAL
	$sql = "select sucu_cod_sucu , sucu_nom_sucu from saesucu where sucu_cod_empr = $id_empresa  ";
	unset($array_sucu);
	$array_sucu = array_dato($oIfx, $sql, 'sucu_cod_sucu', 'sucu_nom_sucu');

// border="1"
//class="bg-primary" 
	$tabla_reporte = '<table class="table table-striped table-condensed table-bordered table-hover" style="margin-top: 10px;" width="100%" align="center">';
	$tabla_reporte .= '<thead><tr>
                                <td class="bg-primary" style="font-size: 9px;text-align:center;" colspan="11"><b>REPORTE SALDOS VENCIDOS</b><br></td>
                     </tr>';
	$tabla_reporte .= '<tr>
                                <!-- <td class="bg-primary" style="font-size: 10px; text-align:center;width:3%;"><b>No.</b></td>
                                <td class="bg-primary" style="font-size: 10px; text-align:center;width:9%;"><b>Sucursal</b></td> -->
                                <td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:22%;" ><b>Proveedor</b></td>
                                <td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:15%;"><b>No. Factura</b></td>
								<td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:9%;"><b>Fecha Emision</b></td>
                                <td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:9%;"><b>Fecha Vence</b></td>
                                <td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:35%;"><b>Detalle</b></td>
                                <!-- <td class="bg-primary" style="font-size: 10px; text-align:center;width:8%;"><b>Valor Factura</b></td>
                                <td class="bg-primary" style="font-size: 10px; text-align:center;width:8%;"><b>Valor Abonos</b></td> -->
                                <td class="bg-primary" style="font-size: 10px; border_bottom:1px; text-align:center;width:10%;"><b>Saldo</b></td>
                                <!-- <td class="bg-primary" style="font-size: 10px; text-align:center;width:4%;"><b>Dia</b></td> -->
                     </tr></thead><tbody>';
	// SQL

	if (empty($fecha_fin)) {
		$fecha_fin = 'null';
	}

	$sql = "SELECT
                    c.clpv_cod_clpv,
                    c.clpv_cod_ciud,
                    c.clpv_nom_clpv,
                    d.dmcp_num_fac,
					min(d.dmcp_cod_sucu)  as sucu_cod, 
                    min(d.dmcp_cod_fact ) as cod_fact,
                    min(d.dcmp_fec_emis)  as fec_emi,
                    max(d.dmcp_fec_ven)   as fec_venc,
                    sum(d.dcmp_deb_ml)    as abono,
                    sum(d.dcmp_cre_ml)    as tot_fac,
                    sum(d.dcmp_deb_ml - d.dcmp_cre_ml) saldo,
					( select  min(dmcp_cod_sucu) 
								from saedmcp where
								dmcp_cod_empr  = $id_empresa and
								dmcp_est_dcmp  <> 'AN' and
								dcmp_fec_emis  <= $fecha_fin and
								dcmp_cre_ml    > 0 and
								clpv_cod_clpv  = c.clpv_cod_clpv  and
								dmcp_num_fac   = d.dmcp_num_fac	) as dmcp_cod_sucu,
					( select min(dmcp_cod_asto) from saedmcp where 
								dmcp_cod_empr   = $id_empresa and
								clpv_cod_clpv   = c.clpv_cod_clpv   and
								dmcp_num_fac    = d.dmcp_num_fac and
								dmcp_est_dcmp  <> 'AN' and
								dcmp_fec_emis  <= $fecha_fin and
								dcmp_cre_ml     > 0  ) as dmcp_cod_asto,
                    ( select min(dmcp_cod_ejer ) 	from saedmcp where 
								dmcp_cod_empr   = $id_empresa and
								clpv_cod_clpv   = c.clpv_cod_clpv   and
								dmcp_num_fac    = d.dmcp_num_fac and
								dmcp_est_dcmp  <> 'AN' and
								dcmp_fec_emis  <= $fecha_fin and
								dcmp_cre_ml    > 0  ) as dmcp_cod_ejer
                    from saedmcp d, saeclpv c  where
                    c.clpv_cod_clpv = d.clpv_cod_clpv and
                    c.clpv_cod_empr   = $id_empresa  and
                    c.clpv_clopv_clpv = 'PV' and
                    d.dmcp_cod_empr   = $id_empresa and
                    d.$tipo  <= '$fecha_corte' and
					--d.dcmp_fec_emis  between '1969-01-01' and '$fecha_corte' and
                    d.dmcp_est_dcmp <> 'AN'
                    $sql_tmp
                    $sql_tmp2
					$sql_tmp3
					$filtroSinAnticipos
					$filtroSoloAnticipos
                    group by c.clpv_cod_clpv,
                    c.clpv_cod_ciud,
                    c.clpv_nom_clpv,
                    d.dmcp_num_fac                    
                    order by $sql_ord  fec_emi, d.dmcp_num_fac ";


	$j = 1;
	$tot_fact = 0;
	$tot_abon = 0;
	$tot_sald = 0;
	//echo $sql;
	//exit;
	//$oReturn->alert($sql);
	unset($array);
	$subtotal = 0;
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$codigo_ciudad = $oIfx->f('clpv_cod_ciud');
				//echo $codigo_ciudad; exit;
				$ciud_nom = $array_ciud[$codigo_ciudad];
				$clpv_nom = $oIfx->f('clpv_nom_clpv');
				$factura = $oIfx->f('dmcp_num_fac');
				$fact_cod = $oIfx->f('cod_fact');
				$fec_emis = ($oIfx->f('fec_emi'));
				$fec_ven = ($oIfx->f('fec_venc'));
				$fact_tot = $oIfx->f('tot_fac');
				$fact_abon = $oIfx->f('abono');
				$fact_sald = $fact_tot - $fact_abon;
				$sucu_cod = $oIfx->f('sucu_cod');
				$sucu_nom = $array_sucu[$oIfx->f('dmcp_cod_sucu')];
				//$sucu_codigo = $array_sucu[$oIfx->f('dmcp_cod_sucu')];
				$ejer_cod = $oIfx->f('dmcp_cod_ejer');
				$asto_cod = $oIfx->f('dmcp_cod_asto');

				if ($fact_sald <> 0) {
					if ($fact_sald < 0) {
						//$fact_sald = 0;
					}

					if (empty($sucu_nom)) {
						$sucu_nom = $array_sucu[$oIfx->f('sucu_cod')];
					}

					$fec_tmp = fecha_mysql($oIfx->f('fec_venc'));
					// $fec_tmp  = $fec_tmp . ' ' . date("H:i:s");
					// $segundos = strtotime($fec_tmp) - strtotime(date("Y-m-d H:i:s"));

					// $dia            = restaFechas(fecha_d_m_y($fecha), fecha_mysql($fec_vence));
					list($a, $b, $c) = explode('-', $fecha_corte);     //2020-06-30
					$fec_rd = $c . '-' . $b . '-' . $a;

					$date_format_vence = date("d-m-Y", strtotime(str_replace('/', '', $fec_tmp)));
					// echo ($date_format_vence);
					// exit;

					if ($fec_tmp) {
						$dia = restaFechas($fec_rd, $date_format_vence); //  dd--mm-YY
					} else {
						$dia = 0;
					}


					$array[$j] = $oIfx->f('clpv_cod_clpv');
					$clpv_cod = $oIfx->f('clpv_cod_clpv');
					$asto_prdo = substr($fec_emis, 3, 2);


					if (!empty($asto_cod)) {
						$slq_asto = "and dmcp_cod_asto='$asto_cod'";
					}
					$sql = "SELECT dmcp_det_dcmp,dmcp_cod_asto,dmcp_cod_ejer from saedmcp where  
					dmcp_num_fac='$factura' 
					$slq_asto 
					and clpv_cod_clpv='$clpv_cod' 
					and dmcp_cod_empr='$id_empresa'
					and dmcp_cod_sucu='$sucu_cod'
					and dcmp_fec_emis='$fec_emis'
					and dmcp_est_dcmp <> 'AN'
					--and dmcp_fec_ven='$fec_ven'
					";
				
					$detalle_fact = consulta_string($sql, 'dmcp_det_dcmp', $oIfxB, '');
					/*$dmcp_cod_asto=consulta_string($sql,'dmcp_cod_asto',$oIfxB,'');
					$dmcp_cod_ejer=consulta_string($sql,'dmcp_cod_ejer',$oIfxB,'');


					if (empty($detalle_fact)) {

					$sql_detalle = "SELECT fprv_det_fprv from saefprv where fprv_cod_asto='$asto_cod' and fprv_cod_clpv='$clpv_cod'";

					if ($oIfxA->Query($sql_detalle)) {
						if ($oIfxA->NumFilas() > 0) {
							do {
								//$fprv_det_fprv1 = $oIfxA->f('dasi_det_asi');
								$detalle_fact = $oIfxA->f('fprv_det_fprv');
							} while ($oIfxA->SiguienteRegistro());
						}
					}
					$oIfxA->Free();
					}

					//TOMAMOS EL DETALLE PARA LIQUIDACIONES
					if (empty($detalle_fact)) {
						$sql_liq = "SELECT
										 asto_det_asto
									FROM
											saeasto
									WHERE
										asto_cod_asto = '$dmcp_cod_asto' and
										asto_cod_ejer = '$dmcp_cod_ejer'and
										asto_cod_sucu = '$sucu_cod' and
										asto_ben_asto ='$clpv_nom'
										";

						if ($oIfxA->Query($sql_liq)) {
							if ($oIfxA->NumFilas() > 0) {
								do {
									//$fprv_det_fprv1 = $oIfxA->f('dasi_det_asi');
									$detalle_fact = $oIfxA->f('asto_det_asto');
								} while ($oIfxA->SiguienteRegistro());
							}
						}
					}
					*/
					// VENDEDOR
					$sql = "select  f.fact_cod_vend , v.vend_nom_vend from saefact f, saevend v where 
                                    v.vend_cod_vend = f.fact_cod_vend and
                                    v.vend_cod_empr = $id_empresa and
                                    f.fact_cod_empr = $id_empresa and
                                    f.fact_cod_fact = '$fact_cod' ";
					$fact_vend = '';
					$vend_nom = '';

					if ($j > 1) {
						if ($array[$j] == $array_cli[$j - 1]) {
							//      $nom_clpv = '';
						} elseif ($array[$j] != $array[$j - 1]) {
							$tabla_reporte .= '<tr class="bg-info">';
							//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"><strong> SALDO: </strong></td>';
							//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"><b>TOTAL:</b></td>';
							$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"><strong>' . number_format($subtotal, 2, '.', ',') . '</strong><br></td>';
							//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
							$tabla_reporte .= '</tr>';
							$subtotal = 0;
						}
					}

					$tabla_reporte .= '<tr>';
					//$tabla_reporte .= '<td style="font-size: 10px; text-align:center; width:3%;">' . $j . '</td>';
					//$tabla_reporte .= '<td style="font-size: 10px; text-align:left;  width:9%;">' . $sucu_nom . '</td>';
					$tabla_reporte .= '<td style="font-size: 8px; text-align:left;  width:22%;" >' . $clpv_nom . '</td>';
					$tabla_reporte .= '<td style="font-size: 10px; text-align:center;  width:15%;">' . $factura . '</td>';
					$tabla_reporte .= '<td style="font-size: 10px; text-align:center;  width:9%;">' . $fec_emis . '</td>';
					$tabla_reporte .= '<td style="font-size: 10px; text-align:center;  width:9%;">' . $fec_ven . '</td>';
					$tabla_reporte .= '<td style="font-size: 10px; text-align:left;  width:35%;">' . $detalle_fact . '</td>';
					//$tabla_reporte .= '<td style="font-size: 10px; text-align:right; width:8%;">' . number_format($fact_tot, 2, '.', ',') . '</td>';
					//$tabla_reporte .= '<td style="font-size: 10px; text-align:right; width:8%;">' . number_format($fact_abon, 2, '.', ',') . '</td>';
					$tabla_reporte .= '<td style="font-size: 10px; text-align:right; width:10%;">' . number_format($fact_sald, 2, '.', ',') . '</td>';
					//$tabla_reporte .= '<td style="font-size: 10px; text-align:center; width:4%;">' . $dia . '</td>';
					$tabla_reporte .= '</tr>';

					$tot_fact += $fact_tot;
					$tot_abon += $fact_abon;
					$tot_sald += $fact_sald;
					$subtotal += $fact_sald;
					$j++;
				}
			} while ($oIfx->SiguienteRegistro());
			// ULTIMA LINEA
			$tabla_reporte .= '<tr>';
			//$tabla_reporte .= '<td class="bg-info" style="font-size: 10px;  text-align:right;"></td>';
			//$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"><strong>SALDO:</strong></td>';
			//$tabla_reporte .= '<td class="bg-info" style="font-size: 10px;  text-align:right;"></td>';
			//$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"><b>TOTAL:</b></td>';
			$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"><strong>' . number_format($subtotal, 2, '.', ',') . '</strong></td>';
			//$tabla_reporte .= '<td class="bg-info" style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '</tr>';

			$tot_sald = $tot_fact - $tot_abon;
			// TOTALES GENERALES
			$tabla_reporte .= '<tr class="bg-info">';
			//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"><strong>TOTAL:</strong></td>';
			//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;">' . number_format($tot_fact, 2, '.', ',') . '</td>';
			//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;">' . number_format($tot_abon, 2, '.', ',') . '</td>';
			$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"><strong>' . number_format($tot_sald, 2, '.', ',') . '</strong></td>';
			//$tabla_reporte .= '<td style="font-size: 10px; text-align:right;"></td>';
			$tabla_reporte .= '</tr>';
		}
	}
	$tabla_reporte .= '</tbody></table>';
	$oIfx->Free();
	$oReturn->alert('Buscando....');
	//var_dump($tabla_reporte); exit;
	$sql = "select trim(empr_nom_empr) as empr_nom_empr, 
					trim(empr_dir_empr) as empr_dir_empr,
					trim(empr_ruc_empr) as empr_ruc_empr
			from saeempr
			where empr_cod_empr = $id_empresa";
	$nombreEmpresa = consulta_string($sql, 'empr_nom_empr', $oIfx, '');
	$direccionEmpresa = consulta_string($sql, 'empr_dir_empr', $oIfx, '');
	$rucEmpresa = consulta_string($sql, 'empr_ruc_empr', $oIfx, '');


	//Armado Cabecera Excel
	unset($_SESSION['sHtml_cab']);
	unset($_SESSION['sHtml_det']);

	$sHtml_exe_p = '<table align="center" border="0" cellpadding="2" cellspacing="1" width="100%">
                            <tr>
                                    <td style="font-size: 10px;" align="center" ><b>' . $nombreEmpresa . '</b></td>
							</tr>
							<tr>
                                    <td style="font-size: 10px;" align="center" ><b>RUC:</b> ' . $rucEmpresa . '</td>
							</tr>	
							<tr>
                                    <td style="font-size: 10px;" align="center" ><b>DIR:</b> ' . $direccionEmpresa . '</td>
							</tr>							
                           
							<tr>
                                    <td style="font-size: 10px;" align="center" ><b>Fecha Reporte:</b> ' . date("d-m-Y") . '</td>
                            </tr>                            
                        </table><br><br>';


	$table = '';
	//arma pdf
	//$table .= '<page footer="date;heure;page" style="font-size: 9px width: 95%">';

	//$table .= '</page>';


	$_SESSION['sHtml_cab'] = $sHtml_exe_p;
	$_SESSION['sHtml_det'] = $tabla_reporte;
	$oReturn->assign("DivReporte", "innerHTML", $tabla_reporte);

	$tabla_reporte = str_replace("font-size: 10px;", "font-size: 8px;", $tabla_reporte);
	$table .= $sHtml_exe_p . $tabla_reporte;
	$_SESSION['pdf'] = $table;
	//var_dump($table); exit;

	return $oReturn;
}

function Mes($mes)
{

	switch ($mes) {
		case 1:
			$nombre_mes = "Enero";
			break;
		case 2:
			$nombre_mes = "Febrero";
			break;
		case 3:
			$nombre_mes = "Marzo";
			break;
		case 4:
			$nombre_mes = "Abril";
			break;
		case 5:
			$nombre_mes = "Mayo";
			break;
		case 6:
			$nombre_mes = "Junio";
			break;
		case 7:
			$nombre_mes = "Julio";
			break;
		case 8:
			$nombre_mes = "Agosto";
			break;
		case 9:
			$nombre_mes = "Septiembre";
			break;
		case 10:
			$nombre_mes = "Octubre";
			break;
		case 11:
			$nombre_mes = "Noviembre";
			break;
		case 12:
			$nombre_mes = "Diciembre";
			break;
	}

	return $nombre_mes;
}

function fecha_informix($fecha)
{
	$m = substr($fecha, 5, 2);
	$y = substr($fecha, 0, 4);
	$d = substr($fecha, 8, 2);

	return ($m . '/' . $d . '/' . $y);
}

function fecha_mysql($fecha)
{
	$fecha_array = explode('/', $fecha);
	$m = $fecha_array[0];
	$y = $fecha_array[2];
	$d = $fecha_array[1];

	return ($d . '/' . $m . '/' . $y);
}

function fecha_mysql_Ymd2($fecha)
{
	$fecha_array = explode('/', $fecha);
	$m = $fecha_array[0];
	$y = $fecha_array[2];
	$d = $fecha_array[1];

	return ($y . '-' . $m . '-' . $d);
}

function fecha_d_m_y($fecha)
{
	$fecha_array = explode('/', $fecha);
	$y = $fecha_array[0];
	$m = $fecha_array[1];
	$d = $fecha_array[2];

	return ($d . '/' . $m . '/' . $y);
}


function restaFechas($dFecIni, $dFecFin)
{
	$dFecIni = str_replace("-", "", $dFecIni);
	$dFecIni = str_replace("/", "", $dFecIni);
	$dFecFin = str_replace("-", "", $dFecFin);
	$dFecFin = str_replace("/", "", $dFecFin);

	preg_match("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecIni, $aFecIni);

	preg_match("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecFin, $aFecFin);

	$date1 = mktime(0, 0, 0, $aFecIni[2], $aFecIni[1], $aFecIni[3]);
	$date2 = mktime(0, 0, 0, $aFecFin[2], $aFecFin[1], $aFecFin[3]);

	return round(($date2 - $date1) / (60 * 60 * 24));
}


/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
