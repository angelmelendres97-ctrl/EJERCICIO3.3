<?
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css">
    <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PROVEEDORES</title>

<!--CSS-->
			<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
			<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
			<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/css/bootstrap-treeview.css" media="screen">
			<link rel="stylesheet" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/dataTables/dataTables.bootstrap.min.css">
			<!--JavaScript-->
			<script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/js/bootstrap-treeview.js"></script>
			<script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/Webjs.js"></script>
			<script type="text/javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>

			
			<script src="js/jquery.min.js" type="text/javascript"></script>
			
			 <!--Javascript-->  
			<script type="text/javascript" src="js/jquery.min.js"></script>
			<script type="text/javascript" src="js/jquery.js"></script>  
			<script src="media/js/jquery-1.10.2.js"></script>
			<script src="media/js/jquery.dataTables.min.js"></script>
			<script src="media/js/dataTables.bootstrap.min.js"></script>          
			<script src="media/js/bootstrap.js"></script>
			<script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>

<script>
	function datos( cod, cli, ruc, dir, tel, cel, vend ,cont, pre ){
		window.opener.document.form1.cliente.value = cod;
		window.opener.document.form1.cliente_nombre.value = cli;
                window.opener.document.form1.ruc.value = ruc;
                window.opener.document.form1.producto.focus();
		close();
	}
</script>
</head>

<body>

<?
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        
        $oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();

        $oIfxA = new Dbo;
	$oIfxA -> DSN = $DSN_Ifx;
	$oIfxA -> Conectar();
	
	$idempresa   = $_GET['empresa'];
	$cliente_nom = $_GET['cliente'];


//	$codigo_busca = strtr(strtoupper($codigo), "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ");
	$sql = "select clpv_cod_clpv, clpv_nom_clpv,  clpv_ruc_clpv,
                        ( SELECT min(DIRE_DIR_DIRE) FROM SAEDIRE WHERE
                           DIRE_COD_CLPV  = clpv_cod_clpv ) as direccion,
                        ( SELECT min(TLCP_TLF_TLCP)  FROM SAETLCP WHERE
                         TLCP_COD_CLPV = clpv_cod_clpv AND
                         TLCP_TIP_TICP = 'T' ) as telefono,
                        ( SELECT min(TLCP_TLF_TLCP) FROM SAETLCP WHERE
                         TLCP_COD_CLPV = clpv_cod_clpv AND
                         TLCP_TIP_TICP = 'C' ) as celular,
                        clpv_cod_vend, clpv_cot_clpv, clpv_pre_ven from saeclpv where
                        clpv_cod_empr = $idempresa and
                        clpv_clopv_clpv = 'PV' and
                        clpv_nom_clpv like upper('%$cliente_nom%')  order by 2";
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<div class="table-responsive">';
	echo '<table class="table table-bordered table-hover" align="center" style="width: 98%;">';
	echo '<tr><td colspan="7" align="center" class="bg-primary">LISTA PROVEEDORES</td></tr>';
	echo '<tr>
			<td align="center" class="bg-primary" style="width: 10%;">ID</td>
			<td align="center" class="bg-primary" style="width: 30%;">CODIGO ITEM</td>
			<td align="center" class="bg-primary" style="width: 15%;">PROVEEDOR</td>
			<td align="center" class="bg-primary" style="width: 10%;">IDENTIFICACION</td>
		 </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$codigo = ($oIfx->f('clpv_cod_clpv'));
			$nom_cliente = htmlentities($oIfx->f('clpv_nom_clpv'));
                        $ruc = ($oIfx->f('clpv_ruc_clpv'));
			$dire = htmlentities($oIfx->f('direccion'));
                        $telefono = $oIfx->f('telefono');
                        $celular = $oIfx->f('celular');
                        $vendedor = $oIfx->f('clpv_cod_vend');
                        $contacto = $oIfx->f('clpv_cot_clpv');
                        $precio = round($oIfx->f('clpv_pre_ven'),0);
                        
                        if ($sClass=='off') $sClass='on'; else $sClass='off';
			echo '<tr height="20" class="'.$sClass.'"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td>'.$cont.'</td>';
				echo '<td width="20px">';		
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_cliente;?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>', '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>' )">
					             <? echo $codigo;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_cliente;?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>', '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>' )">
						     <? echo $nom_cliente;?></a>
    <?
				echo '</td>';
                                echo '<td>';
    ?>
                                <a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_cliente;?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>', '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>' )">
						     <? echo $ruc;?></a>
    <?
                                echo '</td>';                               
    ?>                               
    <?			
			echo '</tr>';
			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>'; 	
			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>'; 	
		$cont++;
		}while($oIfx->SiguienteRegistro());
            }else{
                echo '<span class="fecha_letra">Sin Datos....</span>';
            }
	}
	$oIfx->Free();
	echo '<tr><td colspan="3">Se mostraron '.($cont-1).' Registros</td></tr>';
	echo '</table>';
	//echo $cod_producto;
?>    
</div>
</html>

