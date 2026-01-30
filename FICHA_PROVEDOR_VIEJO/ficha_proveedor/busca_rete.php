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
    
    if (isset($_REQUEST['rete']))
        $rete = $_REQUEST['rete'];
    else
        $rete = null;
		
	if (isset($_REQUEST['op']))
        $op = $_REQUEST['op'];
    else
        $op = null;

    //lectura sucia
    //////////////

    $tabla = '';
	
	$tmpSql = '';
	
	if($op == 1 || $op == 2){
		$tmpSql = " and tret_ban_retf = 'IR' and tret_ban_crdb = 'CR'";
	}elseif($op == 3 || $op == 4){
		$tmpSql = " and tret_ban_retf = 'RI' and tret_ban_crdb = 'CR'";
	}

    $sql = "select tret_cod, tret_det_ret, tret_porct,
			tret_cta_deb, tret_cta_cre
			from saetret
			where tret_cod_empr = $idempresa and
			tret_cod like ('%$rete%')
			$tmpSql";
	//echo ($oIfx->Query($sql));
	//exit;		
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		do{
    			$tret_cod = $oIfx->f('tret_cod');
    			$tret_det_ret = $oIfx->f('tret_det_ret');
				$tret_porct = $oIfx->f('tret_porct');
				$tret_cta_deb = $oIfx->f('tret_cta_deb');
				$tret_cta_cre = $oIfx->f('tret_cta_cre');
				
				$img = '<div align=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(\'' . $tret_cod . '\', '.$op.', \'' . $tret_cta_cre . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div></div>';
				
    			$tabla.='{
				  "tret_cod":"'.$tret_cod.'",
				  "tret_det_ret":"'.$tret_det_ret.'",
				  "tret_porct":"<div align=\"right\">'.$tret_porct.'</div>",
				  "tret_cta_deb":"'.$tret_cta_deb.'",
				  "tret_cta_cre":"'.$tret_cta_cre.'",
				  "selecciona":"'.$img.'"
				},';

			}while($oIfx->SiguienteRegistro());
    	}
	}
	$oIfx->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
