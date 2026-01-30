<?php
	
	include_once('../../Include/config.inc.php');
	include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
	include_once(path(DIR_INCLUDE).'comun.lib.php');

	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    global $DSN_Ifx, $DSN;

	$oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
	$idbodega = $_SESSION['U_BODE_COD_BODE_'];
	
	//variables request
	if (isset($_REQUEST['prod']))
        $prod = $_REQUEST['prod'];
    else
        $prod = null;

	if (isset($_REQUEST['bode']))
        $bode = $_REQUEST['bode'];
    else
        $bode = null;	

    if (isset($_REQUEST['sucu']))
        $sucu = $_REQUEST['sucu'];
    else
        $sucu = null;

    //lectura sucia
    //////////////

    //query de unidades
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            unset($arrayUnidad);
            do {
                $arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_nom_unid');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $tabla = '';
	
    $sql = "select p.prod_cod_sucu, pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod,
			pr.prbo_cod_bode, pr.prbo_cod_unid, p.prod_cod_barra
			from saeprbo pr, saeprod p 
			where
			p.prod_cod_prod = pr.prbo_cod_prod and
			p.prod_cod_empr = pr.prbo_cod_empr and
			p.prod_cod_sucu = pr.prbo_cod_sucu and
			p.prod_cod_empr = $idempresa and
			p.prod_cod_sucu = $sucu and
			pr.prbo_cod_bode = $bode and
			pr.prbo_est_prod = 1 and
			(p.prod_cod_prod like ('%$prod%') OR p.prod_nom_prod like upper('%$prod%'))
			order by p.prod_nom_prod";
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		do{

    			$prbo_cod_prod = $oIfx->f('prbo_cod_prod');
                $prod_nom_prod = $oIfx->f('prod_nom_prod');
				$prod_cod_sucu = $oIfx->f('prod_cod_sucu');
				$prbo_cod_bode = $oIfx->f('prbo_cod_bode');
				
				
				$prod_nom_prod = str_replace('"', "'", $prod_nom_prod);
				//$prod_nom_prod = '';
				
                $prbo_cod_unid = $oIfx->f('prbo_cod_unid');
                $prbo_dis_prod = $oIfx->f('prbo_dis_prod');
				$prod_cod_barra = $oIfx->f('prod_cod_barra');
				$img = '<div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(\''.$prbo_cod_prod.'\', \'' . $prod_nom_prod . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div>';

    			$tabla.='{
				  "prbo_cod_prod":"'.$prbo_cod_prod.'",
				  "prod_nom_prod":"'.$prod_nom_prod.'",
				  "prod_cod_barra":"'.$prod_cod_barra.'",
				  "prbo_cod_unid":"'.$arrayUnidad[$prbo_cod_unid].'",
				  "prbo_dis_prod":"'.$prbo_dis_prod.'",
				  "selecciona":"'.$img.'"
				},';

			}while($oIfx->SiguienteRegistro());
    	}
	}
	$oIfx->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
	
?>