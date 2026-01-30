<?php

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
global $DSN_Ifx, $DSN;

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

//varibales de sesion
$idempresa = $_SESSION['U_EMPRESA'];
$idsucursal = $_SESSION['U_SUCURSAL'];

if (isset($_REQUEST['cuenta']))
	$cuenta = $_REQUEST['cuenta'];
else
	$cuenta = null;

if (isset($_REQUEST['op']))
	$op = $_REQUEST['op'];
else
	$op = null;

//lectura sucia
//////////////

$tabla = '';

$sql = "select cuen_cod_cuen, cuen_nom_cuen, cuen_mov_cuen, cuen_ccos_cuen 
			from saecuen
			where cuen_cod_empr = $idempresa and
			(cuen_cod_cuen like('%$cuenta%') OR cuen_nom_cuen like upper('%$cuenta%'))";

if ($oIfx->Query($sql)) {
	//echo ($oIfx->NumFilas());
	//exit;

	if ($oIfx->NumFilas() > 0) {
		do {

			$cuen_cod_cuen = $oIfx->f('cuen_cod_cuen');
			$cuen_nom_cuen = limpiar_string($oIfx->f('cuen_nom_cuen'));
			$cuen_mov_cuen = $oIfx->f('cuen_mov_cuen');
			$cuen_ccos_cuen = $oIfx->f('cuen_ccos_cuen');

			$img = '';
			if ($cuen_mov_cuen == 1) {
				$img = '<div align=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(\'' . $cuen_cod_cuen . '\', ' . $op . ', \'' . $cuen_ccos_cuen . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div></div>';
			}

			$tabla .= '{
				  "cuen_cod_cuen":"' . $cuen_cod_cuen . '",
				  "cuen_nom_cuen":"' . $cuen_nom_cuen . '",
				  "cuen_ccos_cuen":"' . $cuen_ccos_cuen . '",
				  "selecciona":"' . $img . '"
				},';
		} while ($oIfx->SiguienteRegistro());
	}
}
$oIfx->Free();

//eliminamos la coma que sobra
$tabla = substr($tabla, 0, strlen($tabla) - 1);
$tabla = preg_replace('/[\x00-\x1F\x7F]/', '', $tabla);

echo '{"data":[' . $tabla . ']}';
