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
    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    
    if (isset($_REQUEST['nomClpv']))
        $nomClpv = $_REQUEST['nomClpv'];
    else
        $nomClpv = null;

    //lectura sucia
    //////////////

	$tabla = '';

    $sql = "select empr_cod_empr, empr_nom_empr, empr_dir_empr,
				empr_ruc_empr, empr_nom_cont  from saeempr ";
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{
    			$empr_cod_empr  = $oIfx->f('empr_cod_empr');
    			$empr_nom_empr  = $oIfx->f('empr_nom_empr');
				$empr_dir_empr  = $oIfx->f('empr_dir_empr');
				$empr_ruc_empr  = $oIfx->f('empr_ruc_empr');
                $empr_nom_cont  = $oIfx->f('empr_nom_cont');

				$img = '<div align=\"center\"> <div class=\"btn btn-warning btn-sm\" onclick=\"seleccionaItem( \'' . $empr_cod_empr . '\')\"><span class=\"glyphicon glyphicon-pencil\"><span></div> </div>';

    			$tabla.='{
						  "empr_cod_empr":"'.$empr_cod_empr.'",
						  "empr_nom_empr":"'.$empr_nom_empr.'",						  
						  "empr_ruc_empr":"'.$empr_ruc_empr.'",
						  "empr_dir_empr":"'.$empr_dir_empr.'",
						  "empr_nom_cont":"'.$empr_nom_cont.'",
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