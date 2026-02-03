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
<title>Centro de Costo</title>
<style type="text/css">
<!--
.Estilo1 {
	font-size: 12px;
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #000000;
}
-->
</style>

<script>
	function datos( a, txt, id ){
                window.opener.document.getElementById(txt).value = a;
                window.opener.cargar_update_cant(id);
		close();
	}
</script>
</head>

<body>

<?
	$oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();
	
	$empresa = $_GET['empresa'];
        $txt     = $_GET['id'];
        list($id_grid, $n) = explode('_', $txt);
        $cod     = $_GET['cuenta'];
	$fecha   = date("m-d-Y");

        if(empty($cod)){
            $sql_tmp = '';
        }elseif(!empty($cod)){
            $numero = str_replace('.', '', $cod);
            $is_numeric = is_numeric($numero);
            if($is_numeric==true){
                $sql_tmp = " and ccosn_cod_ccosn like '$cod%' ";
            }else{
                $sql_tmp = " and ccosn_nom_ccosn like UPPER('$cod%') ";
            }  
        }

        // cuenta inicial
        $sql = "select ccosn_cod_ccosn, ccosn_nom_ccosn from saeccosn where
                    ccosn_cod_empr  = $empresa and
                    ccosn_mov_ccosn = 1
                    $sql_tmp
                    order by ccosn_nom_ccosn ";
	
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid">';
	echo '<tr><th colspan="7" align="center" class="diagrama">Centro de Costo</th></tr>';
	echo '<tr>
			<th align="center" class="diagrama">Cuenta</th>
			<th align="center" class="diagrama">Nombre - Espa&ntilde;ol</th>
              </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$cod_cuenta=htmlentities($oIfx->f('ccosn_cod_ccosn'));
			$nom_cuenta=htmlentities($oIfx->f('ccosn_nom_ccosn'));

                        if ($sClass=='off') $sClass='on'; else $sClass='off';
                        echo '<tr height="20" class="'.$sClass.'"
                                    onMouseOver="javascript:this.className=\'link\';"
                                    onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td width="100">';		
	?>
    				<a href="#" onclick="datos( '<? echo $cod_cuenta;?>', '<? echo $txt;?>', '<? echo $id_grid;?>' ) ">
					             <? echo $cod_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos( '<? echo $cod_cuenta;?>', '<? echo $txt;?>', '<? echo $id_grid;?>'  ) ">
						     <? echo $nom_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>';	
	?>
    <?
			echo '</td>';
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

