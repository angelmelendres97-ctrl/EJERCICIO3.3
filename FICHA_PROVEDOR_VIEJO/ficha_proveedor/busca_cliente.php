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

//varibales de sesion
$idempresa = $_SESSION['U_EMPRESA'];
$idsucursal = $_SESSION['U_SUCURSAL'];

if (isset($_REQUEST['nomClpv']))
	$nomClpv = $_REQUEST['nomClpv'];
else
	$nomClpv = null;

//lectura sucia
//////////////

$tabla = '';


// ---------------------------------------------------------------------------------------------------------
// CONTROL CLPV POR USUARIO, SUCURSALES
// ---------------------------------------------------------------------------------------------------------
$id_usuario_comercial = $_SESSION['U_ID'];
$bloqueo_sucu_sn = 'N';
$sucursales_usuario = '';
$sql_data_usuario_sucu = "SELECT bloqueo_sucu_sn, sucursales_usuario from comercial.usuario where usuario_id = $id_usuario_comercial";
if ($oIfx->Query($sql_data_usuario_sucu)) {
	if ($oIfx->NumFilas() > 0) {
		do {
			$bloqueo_sucu_sn = $oIfx->f('bloqueo_sucu_sn');
			$sucursales_usuario = $oIfx->f('sucursales_usuario');
		} while ($oIfx->SiguienteRegistro());
	}
}
$sql_adicional_sucu = "";
$oIfx->Free();
if ($bloqueo_sucu_sn == 'S') {
	if (!empty($sucursales_usuario)) {
		$sql_adicional_sucu = ' and clpv_cod_sucu in (' . $sucursales_usuario . ')';
	}
}
// ---------------------------------------------------------------------------------------------------------
// FIN CONTROL CLPV POR USUARIO, SUCURSALES
// ---------------------------------------------------------------------------------------------------------


$sql = "select clpv_nom_clpv, clpv_cod_clpv, clpv_ruc_clpv,
    		clpv_cod_sucu
    		from saeclpv
    		where clpv_cod_empr = $idempresa and
    		clpv_clopv_clpv = 'PV' and
    		clpv_nom_clpv like upper ('%$nomClpv%')
			$sql_adicional_sucu 
    		order by 3
			";
if ($oIfx->Query($sql)) {
	if ($oIfx->NumFilas() > 0) {
		$sHtmlEstado = '';
		do {

			$clpv_cod_clpv = $oIfx->f('clpv_cod_clpv');
			$clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
			$clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');

			$clpv_nom_clpv = str_replace("'", " ", $clpv_nom_clpv);
			$clpv_nom_clpv = str_replace('"', " ", $clpv_nom_clpv);


			$img = '<div align=\"center\"> <div class=\"btn btn-warning btn-sm\" onclick=\"seleccionaItem(\'' . $clpv_cod_clpv . '\')\"><span class=\"glyphicon glyphicon-pencil\"><span></div> </div>';

			$tabla .= '{
				  "clpv_cod_clpv":"' . $clpv_cod_clpv . '",
				  "clpv_ruc_clpv":"' . $clpv_ruc_clpv . '",
				  "clpv_nom_clpv":"' . $clpv_nom_clpv . '",
				  "selecciona":"' . $img . '"
				},';
		} while ($oIfx->SiguienteRegistro());
	}
}
$oIfx->Free();

//eliminamos la coma que sobra
$tabla = substr($tabla, 0, strlen($tabla) - 1);

echo '{"data":[' . $tabla . ']}';
