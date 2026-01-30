<?php
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>BUSCAR PROVEEDOR</title>

    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/general.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>Clases/Formulario/Css/Formulario.css">

    <style>
        .titulopedido {
            font-size: 12px;
            font-weight: bold;
        }
    </style>

    <script>
      function datos(id, nombre) {
            window.opener.document.getElementById("proveedor_codigo").value = id;
            window.opener.document.getElementById("proveedor_nombre").value = nombre;
            window.close();
        }

    </script>
</head>

<body>

<?php
$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$idempresa = isset($_GET['empresa']) ? intval($_GET['empresa']) : 0;
//$sucursal  = $_GET['sucursal'];
//$bodega   = $_GET['bodega'];


$nombre    = isset($_GET['nombre']) ? strtoupper(trim($_GET['nombre'])) : "";

if ($idempresa <= 0) {
    echo '<div class="alert alert-danger">No se pudo identificar la empresa. Cierre esta ventana y vuelva a intentar.</div>';
    exit;
}


// SQL PRINCIPAL
// $sql = "
//     SELECT
//         clpv_cod_clpv,
//         clpv_ruc_clpv,
//         clpv_nom_clpv,
//         clpv_est_clpv
//     FROM saeclpv
//     WHERE clpv_cod_empr = $idempresa
//       AND clpv_cod_sucu = $sucursal
//       AND clpv_clopv_clpv = 'PV'
//       AND clpv_est_clpv <> 'A'
//       AND clpv_nom_clpv LIKE '%$nombre%'
//     ORDER BY clpv_nom_clpv
//     LIMIT 200
// ";

$sql = "
    SELECT
        clpv_cod_clpv,
        clpv_ruc_clpv,
        clpv_nom_clpv,
        clpv_est_clpv
    FROM saeclpv
    WHERE clpv_cod_empr = $idempresa
      AND clpv_clopv_clpv = 'PV'
      AND clpv_est_clpv <> 'A'
      AND clpv_nom_clpv LIKE '%".addslashes($nombre)."%'
    ORDER BY clpv_nom_clpv
    LIMIT 200
";

// echo $sql;
// exit;


?>

<div id="contenido">

<?php
$cont = 1;
$sClass = 'off';

echo '
<table class="table table-bordered table-hover table-striped table-condensed"
       style="margin-top: 30px; width: 100%">
<tr><th colspan="4" align="center" class="titulopedido">LISTA PROVEEDORES</th></tr>

<tr>
    <th bgcolor="#EBF0FA" class="titulopedido">#</th>
    <th bgcolor="#EBF0FA" class="titulopedido">ID</th>
    <th bgcolor="#EBF0FA" class="titulopedido">RUC</th>
    <th bgcolor="#EBF0FA" class="titulopedido">NOMBRE</th>
</tr>
';

if ($oIfx->Query($sql) && $oIfx->NumFilas() > 0) {

    do {
        $id  = trim($oIfx->f('clpv_cod_clpv'));
        $ruc = trim($oIfx->f('clpv_ruc_clpv'));
        $nom = trim($oIfx->f('clpv_nom_clpv'));

        $idSafe  = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $rucSafe = htmlspecialchars($ruc, ENT_QUOTES, 'UTF-8');
        $nomSafe = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');

        if ($sClass == 'off') $sClass = 'on'; else $sClass = 'off';

        echo '
        <tr height="20" class="'.$sClass.'"
            onMouseOver="this.className=\'link\';"
            onMouseOut="this.className=\''.$sClass.'\';">

            <td>'.$cont.'</td>

            <td width="100">
                <a href="#" onclick="datos(\''.$idSafe.'\', \''.$nomSafe.'\')">
                '.$idSafe.'
                </a>
            </td>

            <td>
                <a href="#" onclick="datos(\''.$idSafe.'\', \''.$nomSafe.'\')">
                '.$rucSafe.'
                </a>
            </td>

            <td>
                <a href="#" onclick="datos(\''.$idSafe.'\', \''.$nomSafe.'\')">
                '.$nomSafe.'
                </a>
            </td>

        </tr>';

        $cont++;

    } while ($oIfx->SiguienteRegistro());

} else {
    echo '<tr><td colspan="4">Sin datos…</td></tr>';
}

echo '<tr><td colspan="4">Se mostraron '.($cont-1).' registros</td></tr>';
echo '</table>';

?>

</div>

</body>
</html>
