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
    $id = $_REQUEST['id'];
    if (isset($_REQUEST['nomProveedor'])){
        $nomProveedor = $_REQUEST['nomProveedor'];
		if(is_numeric($nomProveedor)) {
			$con_nom=" and (clpv_ruc_clpv like '%$nomProveedor%')";
		} else {
			if(($nomProveedor!='0')&&($nomProveedor!='')){
				$con_nom=" and (clpv_nom_clpv like Upper('%$nomProveedor%'))";
			}else{
				$con_nom=null;
			}
		}	
    }
    else{
        $nomProveedor = null;
    }
	//ECHO HOLA; EXIT;

//lectura sucia
    //////////////

    $tabla = '';
		$sql = "SELECT saeclpv.clpv_cod_clpv,   
                       saeclpv.clpv_nom_clpv,   
                       saeclpv.clpv_ruc_clpv
                FROM saeclpv                         
                WHERE saeclpv.clpv_clopv_clpv = 'PV' 
				and saeclpv.clpv_cod_empr =  $idempresa
				$con_nom         
                order by 2";
				//echo $sql;exit;
	$i=1;
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		do{
                $clpv_cod_clpv   = $oIfx->f('clpv_cod_clpv');
                $ruc           = $oIfx->f('clpv_ruc_clpv');
                $clpv_nom_clpv   = htmlentities($oIfx->f('clpv_nom_clpv'));
				$clpv_nom_clpv   = str_replace("'", " ", $clpv_nom_clpv);
				$clpv_nom_clpv   = str_replace('"', ' ', $clpv_nom_clpv);
				
				$img = '<div align=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"bajar_proveedores(\'' . $clpv_cod_clpv . '\', \'' . $clpv_nom_clpv . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div> </div>';
    			//echo $nomPais;exit;
				$tabla.='{
				  "ruc":"'.$ruc.'",
				  "nombre":"'.$clpv_nom_clpv.'",
				  "selecciona":"'.$img.'"
				},';
				$i++;
			}while($oIfx->SiguienteRegistro());
    	}
	}

	
	$oIfx->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
	
?>