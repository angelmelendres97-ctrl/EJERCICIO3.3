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
<title>LISTA DE PRODUCTOS</title>

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
	function datos( a,b, c ){
		window.opener.document.form1.codigo_producto.value = a;
		window.opener.document.form1.producto.value = b;
                window.opener.document.form1.costo.value = c;
                window.opener.document.form1.cantidad.focus();
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

	$idempresa  =  $_GET['empresa'];
        $sucursal   =  $_GET['sucursal'];
	$prod_nom   =  $_GET['producto'];
        $codigo_nom =  $_GET['codigo'];
        $opcion     =  $_GET['opcion'];
        $bodega     =  $_GET['bodega'];
        $fecha      =  fecha_informix_func($_GET['fecha']);


        if($opcion==1){
            // producto
            $sql_tmp = " and p.prod_nom_prod like upper('%$prod_nom%') ";
        }elseif($opcion==2){
            // codigo
            $sql_tmp = " and p.prod_cod_prod like upper('%$codigo_nom%') ";
        }

	$sql = "select pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod , pr.prbo_pco_prod, pr.prbo_cod_unid
                    from saeprbo pr, saeprod p where
                    p.prod_cod_prod = pr.prbo_cod_prod and
                    p.prod_cod_empr = $idempresa and
                    p.prod_cod_sucu = $sucursal and
                    pr.prbo_cod_empr = $idempresa and
                    pr.prbo_cod_bode = '$bodega'
                    $sql_tmp  order by  2 limit 500 ";
echo $sql;exit;
?>
</body>
<div id="contenido">
<?
	$cont=1;
	echo '<div class="table-responsive">';
	echo '<table class="table table-bordered table-hover" align="center" style="width: 98%;">';
	echo '<tr><td colspan="7" align="center" class="bg-primary">LISTA PRODUCTOS</td></tr>';
	echo '<tr>
			<td align="center" class="bg-primary" style="width: 10%;">ID</td>
			<td align="center" class="bg-primary" style="width: 10%;">CODIGO ITEM</td>
			<td align="center" class="bg-primary" style="width: 30%;">PRODUCTO</td>
			<td align="center" class="bg-primary" style="width: 15%;">UNIDAD</td>
			<td align="center" class="bg-primary" style="width: 10%;">STOCK</td>
		 </tr>';
		 
    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$codigo 	= ($oIfx->f('prbo_cod_prod'));
			$nom_prod 	= htmlentities($oIfx->f('prod_nom_prod'));
			$stock 		= $oIfx->f('prbo_dis_prod');
			$costo 		= round($oIfx->f('prbo_pco_prod'),3);
			$unid_cod	= $oIfx->f('prbo_cod_unid');
			
			$sql = "select  unid_nom_unid  from saeunid where
							unid_cod_empr = $idempresa and
							unid_cod_unid = '$unid_cod' ";
			$unid_nom = consulta_string_func($sql, 'unid_nom_unid', $oIfxA, '');
			
			if ($sClass=='off') $sClass='on'; else $sClass='off';
				echo '<tr height="20" class="'.$sClass.'"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td>'.$cont.'</td>';
				echo '<td width="100">';
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_prod;?>', '<? echo $costo;?>' )">
					             <? echo $codigo;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_prod;?>', '<? echo $costo;?>' )">
						     <? echo $nom_prod;?></a>
    <?
				echo '</td>';
				echo '<td>';
?>
				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_prod;?>', '<? echo $costo;?>' )">
<? 				echo $unid_nom;?></a>
    <?
                echo '</td>';                                
    ?>
    <?			echo '<td>';

	?>
				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $nom_prod;?>', '<? echo $costo;?>' )">
	<? 			echo $stock;?></a>
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

