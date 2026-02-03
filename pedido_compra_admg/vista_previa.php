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
<title>REPORTE PEDIDO</title>
<style type="text/css">
<!--
.Estilo1 {
	font-size: 12px;
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #000000;
	font-weight: bold;
}
.Estilo2 {font-size: 10px; font-family: Georgia, "Times New Roman", Times, serif; color: #000000; font-weight: bold; }
.Estilo3 {font-family: Verdana, Arial, Helvetica, sans-serif}
.Estilo4 {
	font-size: 16px;
	font-weight: bold;
	color:#000000;
}
.fecha {
	font-family: Tahoma, Arial, sans-serif;
	font-size: 34px;
	font-weight: bold;
	color:#000000;
}
-->
</style>

<script>
	function formato(){
		document.getElementById('dos').style.display= "none"; 
		window.print();	
	}
</script>
</head>

<body>

<?
	$oCnx = new Dbo ( );
	$oCnx->DSN = $DSN;
	$oCnx->Conectar ();

        $oCnxA = new Dbo ( );
	$oCnxA->DSN = $DSN;
	$oCnxA->Conectar ();

	$oCnxB = new Dbo ( );
	$oCnxB->DSN = $DSN;
	$oCnxB->Conectar ();
	
	$oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();
	
	$oIfxA = new Dbo;
	$oIfxA -> DSN = $DSN_Ifx;
	$oIfxA -> Conectar();
	
	$empresa =  $_SESSION['U_EMPRESA'];
	//Cambia de minusculas a mayusculas
	$nombre_empresa = strtr(strtoupper($empresa), "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ");
	//Obtener el id de la empresa desde Informix 
	$sql='SELECT * FROM SAEEMPR WHERE EMPR_COD_EMPR = ?';
	$data=array($empresa);
	if ($oIfxA->Query($sql,$data)){
		do {
			$idempresa=$oIfxA->f('empr_cod_empr');
		}while($oIfxA->SiguienteRegistro());
	}
	$oIfxA->Free();
	
	$codigo_secuencial = $_GET['codigo'];
	
	$sql_control = "select count(*) as contador from pedido where pedf_num_preimp = '$codigo_secuencial' ";
	
	if ($oCnxB->Query($sql_control)){
		do {
			$contador = $oCnxB->f('contador');
		}while($oCnxB->SiguienteRegistro());
	}
	
	if($contador>0){
	
	
	$sql_reporte = "SELECT *FROM PEDIDO WHERE PEDF_NUM_PREIMP = '$codigo_secuencial' and empr_cod_empr = $idempresa";
	
    if ($oCnx->Query($sql_reporte)){
		do {
			$id_pedido = $oCnx->f('id_pedido');
			$codigo_op = $oCnx->f('pedf_num_preimp');
			$id_cliente = $oCnx->f('clpv_cod_clpv');
			$id_abono = $oCnx->f('id_abono');
			$id_user = $oCnx->f('usuario_id');
			$fecha_user = $oCnx->f('fecha_modificar');
			$prioridad = $oCnx->f('prioridad');
			
			// N O M B R E     C L I E N T E
			$sql_op = "SELECT CLPV_COD_CLPV, CLPV_NOM_CLPV FROM SAECLPV WHERE
							CLPV_CLOPV_CLPV='CL' AND
							CLPV_COD_EMPR = $idempresa AND
							CLPV_COD_CLPV = $id_cliente";
			if ($oIfx->Query($sql_op)){
                            if($oIfx->NumFilas()>0){
				$nombre_cliente = htmlentities($oIfx->f('clpv_nom_clpv'));
                            }else{
                                $nombre_cliente = '';
                            }
			}
			$oIfx->Free();

			// D I R E C C  I O N     C L I E N T E
			$sql_op2 = "SELECT DIRE_DIR_DIRE FROM SAEDIRE WHERE DIRE_COD_CLPV = $id_cliente";
			if ($oIfx->Query($sql_op2)){
                            if($oIfx->NumFilas()>0){
				$direccion = htmlentities($oIfx->f('dire_dir_dire'));
                            }else{
                                $direccion='';
                            }
			}
			$oIfx->Free();

			// T E L E F O N O     C L I E N T E
			$sql_op3 = 'SELECT TLCP_TLF_TLCP FROM SAETLCP WHERE TLCP_COD_CLPV=? AND TLCP_TIP_TICP=?';
			$data = array($id_cliente,'T');
			if ($oIfx->Query($sql_op3,$data)){
                            if($oIfx->NumFilas()>0){
				$telefono = $oIfx->f('tlcp_tlf_tlcp');
                            }else{
                                $telefono=0;
                            }
			}
			$oIfx->Free();

			// C O N T A C T O      C L I E N T E
			$sql_op4 = "select clpv_cot_clpv from saeclpv where clpv_cod_clpv = $id_cliente";
			if ($oIfx->Query($sql_op4)){
                            if($oIfx->NumFilas()>0){
				$nombre_contacto = htmlentities($oIfx->f('clpv_cot_clpv'));
                            }else{
                                $nombre_contacto='';
                            }
			}
			$oIfx->Free();
			
			$fecha_cotizacion = $oCnx->f('fecha_cotizacion');
			$fecha_vencimiento = $oCnx->f('fecha_vencimiento');
			$observaciones = $oCnx->f('observaciones');
			
		}while($oCnx->SiguienteRegistro());
	}
	$oCnx->Free();  
	
	
	
	/********************************************/
	/*	A B O N O     C L I E N T E              */
	/********************************************/
	if($id_abono!=""){
		// T I P O     D E    A B O N O
		$sql_tipo_abono_op = "select * from abono where id_abono = $id_abono ";
					
		if($oCnx->Query($sql_tipo_abono_op)){
			if ($oCnx->NumFilas() > 0){
				do{ 
					$abono_tipo = $oCnx->f('id_tipo_abono');
					$valor_efectivo = $oCnx->f('valor_efectivo');
					$valor_tarjeta = $oCnx->f('valor_tarjeta');
					$valor_cheque = $oCnx->f('valor_cheque');		
				}while($oCnx->SiguienteRegistro());
				
			}else{
				echo "No hay registros...";
			}
		} 
		$oCnx->Free();  
				
		switch($abono_tipo){
			case 1:  //E F E C T I V O
				//E F E C T I V O				
				$sHtmlAbono .='<fieldset style="border:#009999 1px solid; padding:2px; text-align:center; width:300px;">
									<legend class="Titulo">A b o n o</legend>';
				$sHtmlAbono .= '<table align="left" cellpadding="0" cellspacing="2" width="70%" border="0">
								   <tr>
										<td class="Estilo2" align="left" >VALOR:</td>
										<td class="fecha_letra" height="20" align="left">$ '.$valor_efectivo.'</td>
								   </tr>
							   </table></fieldset>';
			break;  
			
			case 2:  // T A R J E T A     D E    C R E D I T O				
				$sHtmlAbono .='<fieldset style="border:#009999 1px solid; padding:2px; text-align:center; width:300px;">
									<legend class="Titulo">A b o n o</legend>';
				$sHtmlAbono .= '<table align="left" cellpadding="0" cellspacing="2" width="70%" border="0">
									<tr>
										<td class="Estilo2" align="left" >VALOR:</td>
										<td class="fecha_letra" height="20" align="left">$ '.$valor_tarjeta.'</td>
								   </tr>
							  </table></fieldset>';
			break;  
			
			case 3:  // C H E Q U E			
				$sHtmlAbono .='<fieldset style="border:#009999 1px solid; padding:2px; text-align:center; width:300px;">
									<legend class="Titulo">A b o n o</legend>';
									
				$sHtmlAbono .= '<table align="left" cellpadding="0" cellspacing="2" width="70%" border="0">
										<tr>
											<td class="Estilo2" align="left">VALOR:</td>
											<td class="fecha_letra" height="20" align="left">$ '.$valor_cheque.'</td>
									   </tr>
								</table>';
										
			break; 
			
			
		
		}
	
	}else{
		$sHtmlAbono .='<fieldset style="border:#009999 1px solid; padding:2px; text-align:center; width:300px;">
									<legend class="Titulo">A b o n o</legend>';
									
		$sHtmlAbono .= '<table align="left" cellpadding="0" cellspacing="2" width="100%" border="0">
								<tr>
									<td class="Estilo2" width="150" align="left" >V A L O R</td>
									<td class="fecha_grande" height="20" align="left">SIN ABONO</td>
							   </tr>
						</table>';				
	}
?> 

<div id="uno">

<table width="678" height="347" border="0" align="center">
  <tr>
    <td height="32">&nbsp;</td>
    <td height="32" colspan="2"><div align="center" class="Estilo1"> CINTEXSA CIA. LTDA </div></td>
    <td height="32"><div align="center"><img src="../../imagenes/logos/logobadent3.jpg" width="157" height="40" /></div></td>
  </tr>
  <tr>
    <td width="142">&nbsp;</td>
    <td width="201" class="Estilo1"><div align="right">NOTA PEDIDO N0:  </div></td>
    <td width="131" class="Estilo4" align="center"><? echo $codigo_op; ?></td>
    <td width="186"><img src="barcode_img.php?num=<? echo $codigo_secuencial; ?>&type='code128'"></td>
  </tr>
  <tr>
    <td class="Estilo2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="Estilo2">PRIORIDAD:</td>
    <td colspan="2" class="Estilo2"><? echo $prioridad; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="Estilo2">CLIENTE:</td>
    <td colspan="2"> <? echo $nombre_cliente; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="Estilo2">DIRECCION:</td>
    <td colspan="2"><? echo $direccion; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="Estilo2">FECHA INICIO: </td>
    <td><? echo $fecha_cotizacion; ?></td>
    <td class="Estilo2">FECHA VENCIMIENTO: </td>
    <td><? echo $fecha_vencimiento; ?></td>
  </tr> 
  <tr>
    <td class="Estilo2">TELEFONO:</td>
    <td> <? echo $telefono; ?></td>
    <td class="Estilo2">CONTACTO:</td>
    <td><? echo $nombre_contacto; ?></td>
  </tr>
  <tr>
    <td class="Estilo2">OBSERVACIONES:</td>
    <td colspan="2"><? echo $observaciones; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="Estilo2">RESPONSABLE PEDIDO :</td>
    <td colspan="2">
	
	<?
		/*****************************************************************************/
		/* U S U A R I O     Q U I E N     M O D I F I C O     E L    P E D I D O    */
		/*****************************************************************************/
		if($id_user!=''){
			$sql_user = "SELECT *FROM USUARIO WHERE USUARIO_ID = $id_user";
			if ($oCnxA->Query($sql_user)){
				do {
					$nombre_user = htmlentities($oCnxA->f('usuario_nombre'));
					$apellido_user = htmlentities($oCnxA->f('usuario_apellido'));
				}while($oCnxA->SiguienteRegistro());
			}
			$oCnxA->Free();
			
			$usuario = $nombre_user.' '.$apellido_user.'&nbsp;&nbsp;&nbsp;&nbsp;'.$fecha_user;
		}else{
			$usuario = "";
		}
		
		echo $usuario;
	
	?>	</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="4" class="Estilo2" align="left"><? echo $sHtmlAbono; ?></td>
  </tr>
  <tr>
    <td colspan="4" class="Estilo2" align="center">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="4" class="Estilo2" align="left">
	
		<?
			$sql_des = "SELECT PD.ID_PEDIDO_DESCRIPCION, P.ID_PEDIDO, PD.PROD_COD_PROD,
                                            PD.PROD_NOM_PROD, PD.CANTIDAD
                                            FROM PEDIDO P, PEDIDO_DESCRIPCION PD WHERE
                                            P.ID_PEDIDO = PD.ID_PEDIDO AND
                                            P.ID_PEDIDO = $id_pedido ORDER BY PD.ID_PEDIDO_DESCRIPCION";

			echo '<fieldset style="border:#009999 1px solid; padding:2px; text-align:center; width:630px;">';
			echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%">';
			echo '<tr>
                                    <th class="diagrama">CODIGO ITEM</th>
                                    <th class="diagrama">DESCRIPCION</th>
                                    <th class="diagrama">CANTIDAD</th>
                              </tr>';
			if($oCnx->Query($sql_des)){
                            if($oCnx->NumFilas()>0){
				do{
					echo '<tr>';
						echo '<td align="left">'.$oCnx->f('prod_cod_prod').'</td>';
						echo '<td align="left">'.htmlentities($oCnx->f('prod_nom_prod')).'</td>';
						echo '<td>'.$oCnx->f('cantidad').'</td>';
					echo '</tr>';

				}while($oCnx->SiguienteRegistro());
                            }else{
                                echo 'Sin Productos...';
                            }
			}
			$oCnx->Free();
			echo '</table>';
		?>	</td>
    </tr>
  <tr>
    <td colspan="4" class="Estilo2" align="left">&nbsp;</td>
  </tr>
 
  <tr>
    <td colspan="4" class="Estilo2" align="left">&nbsp;</td>
  </tr>
  
</table>

</div>



<div id="dos">

<table width="464" border="0" align="center">
  <tr>
    <td align="center"><label>
      <input name="Submit2" type="submit" class="Estilo2" value="Imprimir" onclick="formato();" />
    </label></td>
  </tr>
</table>
<?

	}else{
	
		echo '<div align="center" class="Estilo1">ERROR!!!! AUN NO INGRESA UN PEDIDO.... </div>';
	}

?>

</div>
</body>
</html>