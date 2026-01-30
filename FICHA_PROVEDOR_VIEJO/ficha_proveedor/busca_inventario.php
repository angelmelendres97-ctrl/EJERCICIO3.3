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
	
	if (isset($_REQUEST['producto']))
        $producto = $_REQUEST['producto'];
    else
        $producto = null;
		
	if (isset($_REQUEST['id_bodega']))
        $id_bodega = $_REQUEST['id_bodega'];
    else
        $id_bodega = $_SESSION['U_BODE_COD_BODE_'];

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

    $sql = "select pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod,
    		p.prod_alt_clie, p.prod_alt_prov,
			p.prod_cod_barra, p.prod_stock_neg, p.prod_cod_tpro, 
			pr.prbo_cod_bode, p.prod_des_prod, pr.prbo_cco_prbo,
			pr.prbo_cod_unid, pr.prbo_iva_porc, pr.prbo_tot_prov
			from saeprbo pr, saeprod p 
			where
			p.prod_cod_prod = pr.prbo_cod_prod and
			p.prod_cod_empr = pr.prbo_cod_empr and
			p.prod_cod_sucu = pr.prbo_cod_sucu and
			p.prod_cod_empr = $idempresa and
			p.prod_cod_sucu = $idsucursal and
			pr.prbo_cod_bode = $id_bodega and
			pr.prbo_est_prod = '1' and
			(p.prod_cod_prod like upper('%$producto%') OR p.prod_nom_prod like upper('%$producto%'))
			order by p.prod_nom_prod";
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		do{

    			$prbo_cod_prod = $oIfx->f('prbo_cod_prod');
    			$prod_alt_clie = $oIfx->f('prod_alt_clie');
                $prod_alt_prov = $oIfx->f('prod_alt_prov');
                $prod_nom_prod = $oIfx->f('prod_nom_prod');
                $prbo_cod_unid = $oIfx->f('prbo_cod_unid');
                $prbo_dis_prod = $oIfx->f('prbo_dis_prod');
				$prbo_cod_bode = $oIfx->f('prbo_cod_bode');
				
				$img = '<div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(\'' . $prbo_cod_prod . '\', \'' . $prbo_cod_prod . '\', '.$prbo_cod_bode.')\"><span class=\"glyphicon glyphicon-ok\"><span></div>';

    			$tabla.='{
				  "prbo_cod_prod":"'.$prbo_cod_prod.'",
				  "prod_alt_clie":"'.$prod_alt_clie.'",
				  "prod_alt_prov":"'.$prod_alt_prov.'",
				  "prod_nom_prod":"'.$prod_nom_prod.'",
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

	$tabla = preg_replace('/[\x00-\x1F\x7F]/', '', $tabla);

	echo '{"data":['.$tabla.']}';
	
?>