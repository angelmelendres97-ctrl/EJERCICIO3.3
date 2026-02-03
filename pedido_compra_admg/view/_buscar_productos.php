<?php
include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>LISTA DE PRODUCTOS</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.css"
          media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css"
          media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/css/bootstrap-treeview.css"
          media="screen">
    <link rel="stylesheet" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/dataTables/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/ventanas/dhtmlwindow.css" rel="stylesheet"
          media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css"
          media="screen"/>
    <link rel="stylesheet" type="text/css" href='<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Calendario/calendario.css'
          media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/arbol/simpletree.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/lytebox/css/lytebox.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/lightbox/css/lightbox.css" media="screen"/>
    <!-- Select2 -->
    <link rel="stylesheet" type="text/css"
          href="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/select2/dist/css/select2.min.css">
    <!--Sweetalert2-->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/sweetalert2/sweetalert2.min.css">
    <!-- Valid -->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/jqueryValidate/jquery.validate.css">

    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/jquery/jquery-3.3.1.min.js.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/comun.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/process.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Js/Formulario.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/ventanas/dhtmlwindow.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/arbol/simpletreemenu.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/fc/js/FusionCharts.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/HTML_TreeMenuXL-2.0.2/TreeMenu.js"></script>
    <!-- Select2 -->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/select2/dist/js/select2.full.min.js"></script>
    <!--Sweetalert2-->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/sweetalert2/sweetalert2.min.js"></script>
    <!--Valid-->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/jqueryValidate/jquery.validates.min.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/jqueryValidate/localization/messages_es.min.js"></script>


    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" type="text/css"
          href="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/dataTables/dataTables.buttons.min.css"
          media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css"
          href="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" type="text/css"
          href="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_COMPONENTES"]?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?=$_COOKIE["JIREH_COMPONENTES"]?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/dataTables/dataTables.bootstrap.min.css"
          media="screen">
    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/style.css">

    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script type="text/javascript" language="JavaScript"
            src="<?=$_COOKIE["JIREH_COMPONENTES"]?>dist/js/adminlte.min.js"></script>

</head>
<body>
<?php
global $DSN_Ifx, $DSN;


$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$idempresa = $_SESSION['U_EMPRESA'];
$sucursal = $_GET['sucursal'];
$bodega = $_GET['bodega'];
$producto = $_GET['producto'];
$precio = $_GET['precio'];

/**
 * CONSULTAR NUMERO DE DECIMALES
 */
$para_num_des = obtener_numero_decimales_jire($idempresa,$sucursal);


$sql = "select *from sp_obtener_todos_productos($idempresa , $sucursal,$bodega,500,'$producto','$precio');";

?>
<div class="col-sm-12">
    <div class="col-sm-12">
        <br>
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                Listado Productos
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="table_productos" class="table table-condensed table-striped table-hover">
                        <thead>
                        <tr>
                            <th align="left" bgcolor="#EBF0FA">Código</th>
                            <th align="left" bgcolor="#EBF0FA">Descripción</th>
                            <th align="left" bgcolor="#EBF0FA">Código de barras</th>
                            <th align="left" bgcolor="#EBF0FA">Aplicación</th>
                            <th align="left" bgcolor="#EBF0FA">Marca</th>
                            <th align="left" bgcolor="#EBF0FA">Observación</th>
                            <th align="left" bgcolor="#EBF0FA">Stock</th>
                            <th align="left" bgcolor="#EBF0FA">Reserva</th>
                            <th align="left" bgcolor="#EBF0FA">Disponible</th>
                            <th align="left" bgcolor="#EBF0FA">Lote</th>
                            <th align="left" bgcolor="#EBF0FA">Serie</th>
                            <th align="left" bgcolor="#EBF0FA">PVP</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $cont = 1;
                        if ($oIfx->Query($sql)) {
                            if ($oIfx->NumFilas() > 0) {
                                do {
                                    $codigo = $oIfx->f('prbo_cod_prod');
                                    $prbo_cod_bode = $oIfx->f('prbo_cod_bode');
                                    $nom_bodega = $oIfx->f('bode_nom_bode');
                                    $marca = $oIfx->f('maut_nom_maut');
                                    $modelo = $oIfx->f('mode_nom_mode');
                                    $nombre = limpiar_string($oIfx->f('prod_nom_prod'));
                                    $descipcion = ($oIfx->f('prod_des_prod') ? limpiar_string($oIfx->f('prod_des_prod')): '');
//                                    $stock = $oIfx->f('prbo_dis_prod');
                                    $precio = ($oIfx->f('ppr_pre_raun') ? $oIfx->f('ppr_pre_raun') : 0);
                                    $cuenta = $oIfx->f('prbo_cta_inv');
                                    $cuenta_iva = $oIfx->f('prbo_cta_ideb');
                                    $lote = ($oIfx->f('prod_lot_sino') ? $oIfx->f('prod_lot_sino') : 'N');
                                    $serie = ($oIfx->f('prod_ser_prod') ? $oIfx->f('prod_ser_prod') : 'N');
                                    $cod_barra = $oIfx->f('prod_cod_barra');
                                    $cod_tpro = $oIfx->f('prod_cod_tpro');
                                    $prod_stock_neg = $oIfx->f('prod_stock_neg');
                                    $prbo_cco_prbo = $oIfx->f('prbo_cco_prbo');
                                    $aplicacion = $oIfx->f('aplicacion');

                                    /**
                                     * CONSULTAR STOCK
                                     */

                                    $sql_stock = "select  COALESCE( pr.prbo_dis_prod,'0' ) as stock
                                    from saeprod p, saeprbo pr where
                                    p.prod_cod_prod = pr.prbo_cod_prod and
                                    p.prod_cod_empr = $idempresa and
                                    p.prod_cod_sucu = $sucursal and
                                    pr.prbo_cod_empr = $idempresa and
                                    pr.prbo_cod_bode = $prbo_cod_bode and
                                    p.prod_cod_prod = '$codigo'";
                                    $stock = consulta_string_func($sql_stock, 'stock', $oIfxA, 0);

                                    if ($lote == 1) {
                                        $lote = 'S';
                                    }

                                    if ($serie == 1) {
                                        $serie = 'S';
                                    }

                                    $stock = convert_number_format_jire($stock, 0);
                                    $precio = convert_number_format_jire($precio, $para_num_des);


                                    // stock pedido
                                    $sqlStockPedido = "select COALESCE(sum(dpef_cant_dfac),'0') as dpef_cant_dfac 
									from saepedf p, saedpef d 
									where
									p.pedf_cod_pedf = d.dpef_cod_pedf and
									p.pedf_cod_empr = $idempresa and
									p.pedf_cod_sucu = $sucursal and
									p.pedf_est_fact = 'PE' and
									d.dpef_cod_bode = $bodega and
									d.dpef_cod_prod = '$codigo'";
                                    $reserva = consulta_string($sqlStockPedido, "dpef_cant_dfac", $oIfxA, 0);
                                    $reserva = number_format($reserva, 0);

                                    if ($cod_tpro == 1) {
                                        $disponible = $stock;
                                        $tipoProd = 'S';
                                    } else {
                                        $disponible = $stock - $reserva;
                                        $tipoProd = 'P';
                                    }

                                    $onclik = "datos('$codigo','$nom_bodega','$precio','$disponible','$nombre','$cuenta','$cuenta_iva','$lote','$serie','$cod_tpro','$prod_stock_neg','$prbo_cco_prbo');";

                                    ?>
                                    <tr onclick="<?= $onclik ?>">
                                        <td><?= $codigo ?></td>
                                        <td><?= $nombre ?></td>
                                        <td><?= $cod_barra ?></td>
                                        <td><?= $aplicacion ?></td>
                                        <td><?= $marca ?></td>
                                        <td><?= $descipcion ?></td>
                                        <td><?= $stock ?></td>
                                        <td><?= $reserva ?></td>
                                        <td><?= $disponible ?></td>
                                        <td><?= $lote ?></td>
                                        <td><?= $serie ?></td>
                                        <td><?= $precio ?></td>
                                    </tr>
                                    <?php
                                    $cont++;
                                } while ($oIfx->SiguienteRegistro());
                            } else {
                                ?>
                                <tr>
                                    <th colspan="7" align="center">sin datos</th>
                                </tr>
                                <?php
                            }
                        }
                        $oIfx->Free();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script>

    function datos(codigo, bodega, precio, stock, nombre, cuenta, cuenta_iva, lote, serie, tipo_prod, stock_neg, ccosn) {
        parent.document.form1.codigo_producto.value = codigo;
        parent.document.form1.producto.value = nombre;
        parent.document.form1.cantidad.value = '';
        parent.document.form1.cantidad.focus();
        parent.cerrar_ventana();
    }


    function initTable() {
        var search = '<?=$producto?>';
        var table = $('#table_productos').DataTable({
            dom: 'Bfrtip',
            processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
            "language": {
                "search": "<i class='fa fa-search'></i>",
                "searchPlaceholder": "Buscar",
                'paginate': {
                    'previous': 'Anterior',
                    'next': 'Siguiente'
                },
                "zeroRecords": "No se encontro datos",
                "info": "Mostrando _START_ a _END_ de  _TOTAL_ Total",
                "infoEmpty": "",
                "infoFiltered": "(Mostrando _MAX_ Registros Totales)",
            },
            "paging": true,
            "ordering": true,
            "info": true,
        });

        table.search(search).draw();
    }

    initTable();
</script>

