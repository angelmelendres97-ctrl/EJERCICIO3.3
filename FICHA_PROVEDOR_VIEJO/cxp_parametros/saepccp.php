<?php
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
global $DSN_Ifx, $DSN;

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

//varibales de sesion
$idempresa  = $_SESSION['U_EMPRESA'];

//lectura sucia
//////////////

$tabla = '';
$sql = "SELECT * FROM saepccp where pccp_cod_empr = $idempresa ";
//echo $sql;
$i = 1;
if ($oIfx->Query($sql)) {
	if ($oIfx->NumFilas() > 0) {
		$sHtmlEstado = '';
		do {
			$codigo         =  $oIfx->f('pccp_cod_pccp');
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
			$parliq          = $oIfx->f('pccp_par_liq');
			$sec_fgasto          = $oIfx->f('pccp_nur_orpa');
			$cuenta_no_domiciliado = $oIfx->f('pccp_pat_email');
			$tran_det          = $oIfx->f('pccp_tran_det');



			// SOLO SIRVE PARA LAS OPCIONES DE SELECCION LAS OPCIONES DE INGRESO DE DATOS NO.

			$sql = "select empr_nom_empr from saeempr where empr_cod_empr = $idempresa ";
			$empr_nom = consulta_string_func($sql, 'empr_nom_empr', $oIfxA, '');

			$sql = "select tran_des_tran from saetran where tran_cod_empr = $idempresa and tran_cod_tran = '$fac' ";
			$tran_nom = consulta_string_func($sql, 'tran_des_tran', $oIfxA, '');

			$sql = "select tran_des_tran from saetran where tran_cod_empr = $idempresa and tran_cod_tran = '$aut' ";
			$tran_auto = consulta_string_func($sql, 'tran_des_tran', $oIfxA, '');

			$sql = "select bode_nom_bode from saebode where bode_cod_empr = $idempresa and bode_cod_bode = '$bodegaser' ";
			$bode_nom = consulta_string_func($sql, 'bode_nom_bode', $oIfxA, '');

			$sql = "select tidu_des_tidu from saetidu where tidu_cod_empr = $idempresa and tidu_cod_tidu = '$docierreant' ";
			$tidu_nom = consulta_string_func($sql, 'tidu_des_tidu', $oIfxA, '');


			$img = '<div align=\"center\"> <div class=\"btn btn-warning btn-sm\" onclick=\" seleccionaItem(\'' . $factu . '\', \'' . $fac . '\', \'' . $aut . '\', \'' . $bodegaser . '\', \'' . $lotes . '\', \'' . $orden . '\', \'' . $credfis . '\', \'' . $numdig . '\', \'' . $docierreant . '\', \'' . $ctaret . '\', \'' . $codigo . '\', \'' . $parliq . '\', \'' . $sec_fgasto . '\', \'' . $cuenta_no_domiciliado . '\', \'' . $tran_det . '\'  )\"><span class=\"glyphicon glyphicon-pencil\"><span></div> </div>';
			$tabla .= '{
    				      "pccp_cod_pccp":"' . $codigo . '",
						  "pccp_cod_empr":"' . $empr_nom . '",
						  "pccp_cod_facp":"' . $tran_nom . '",
						  "pccp_aut_pago":"' . $tran_auto . '",	
						  "pccp_bod_serv":"' . $bode_nom . '",							  
						  "pccp_num_orpa":"' . $orden . '",	
						  "pccp_cre_fis":"' . $credfis . '",	
						  "pccp_num_digi":"' . $numdig . '",	
						  "pccp_tidu_anti":"' . $tidu_nom . '",	
						  "pccp_cret_asumi":"' . $ctaret . '",	
						  "selecciona":"' . $img . '"
				},';

			$i++;
		} while ($oIfx->SiguienteRegistro());
	}
}
$oIfx->Free();

//eliminamos la coma que sobra
$tabla = substr($tabla, 0, strlen($tabla) - 1);

echo '{"data":[' . $tabla . ']}';
