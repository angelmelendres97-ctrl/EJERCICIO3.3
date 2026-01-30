<?
header("Content-Type: text/html; charset=ISO-8859-1");
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
			<script src="js/teclaEvent.js" type="text/javascript"></script>
			<link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css">
            <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>LISTA DE DOMINIOS</title>
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
				shortcut.add("Esc", function() {
					close();
				});
		
                function datos(a) {
                    window.opener.asignarTipoCorreo(a);
                    close();
                }
            </script>
    </head>

    <BODY>

        <?
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        global $DSN, $DSN_Ifx;

        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $oCon = new Dbo;
        $oCon->DSN = $DSN;
        $oCon->Conectar();

        $sql = "select id, tipo, siglas from tipo_correo";

        ?>
    </body>
    <div id="contenido">
        <?
        $cont = 1;
		
        echo '<fieldset align="center" style="border:#999999 1px solid; padding:2px; width:100%;">
              <legend class="Titulo">LISTA DE DOMINIOS</legend>';

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
				

				echo '<table align="center" cellpadding="2" cellspacing="1" width="98%">';
				echo '<tr>
						<th align="left" bgcolor="#EBF0FA" class="titulopedido">ID</th>
						<th align="left" bgcolor="#EBF0FA" class="titulopedido">DOMINIO</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">ELEGIR</th>
					</tr>';
				
                do {
                    $id = $oCon->f('id');
                    $tipo = $oCon->f('tipo');
                    $siglas = $oCon->f('siglas');

                    if ($sClass == 'off')
                        $sClass = 'on';
                    else
                        $sClass = 'off';
                    echo '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                    echo '<td>' . $cont . '</td>';
                    echo '<td>';
                    ?>
                    <span onclick="datos('<? echo $siglas; ?>')">
                        <? echo $tipo; ?> </span>
                    <?
                    echo '</td>';
                    echo '<td align="center">';
                    ?>                                 
                     <a href="#" onclick="datos('<? echo $siglas;?>')">
                    <?  echo '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/accept_1.png"/>';?></a>
                    <?
                    echo '</td>'; 
                    echo '</tr>';
                    echo '<tr>';
                    echo '</tr>';
                    $cont++;
                }while ($oCon->SiguienteRegistro());
            }else {
                echo '<span class="fecha_letra">Sin Datos....</span>';
            }
        }
        $oCon->Free();
        echo '<tr><td colspan="3">Se mostraron ' . ($cont - 1) . ' Registros</td></tr>';
        echo '</table>';
        echo '</fieldset>';
        //echo $cod_producto;
        ?>
    </div>
</html>

