<?
if (isset($_REQUEST['cuenta'])) {
    $cuenta = $_REQUEST['cuenta'];
} else {
    $cuenta = '';
}

if (isset($_REQUEST['op']))
    $op = $_REQUEST['op'];
else
    $op = '';
?>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuentas Contables</title>
    <!--CSS-->
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
    <!--Javascript-->
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <script src="media/js/lenguajeusuario_1.js"></script>
    <script src="js/teclaEvent.js" type="text/javascript"></script>
    <script>
        shortcut.add("Esc", function() {
            close();
        });

        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });


        function seleccionaItem(cuenta, op, c) {
            if (op == 1) {
                window.opener.document.form1.cuentaAplicada.value = cuenta;
                if (c == 'S') {
                    window.opener.cuentaAplicada();
                }
                window.opener.document.form1.creditoBienes.focus();
            } else if (op == 2) {
                window.opener.document.form1.creditoBienes.value = cuenta;
                window.opener.document.form1.creditoServicios.focus();
            } else if (op == 3) {
                window.opener.document.form1.creditoServicios.value = cuenta;
                window.opener.document.form1.retencionBienes.focus();
            } else if (op == 4) {
                window.opener.document.form1.cuentaContable.value = cuenta;
            }
            window.close();
        }
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="col-md-12 table-responsive">
            <input type="hidden" name="op" id="op" value="<?= $op ?>">
            <input type="hidden" name="cuenta" id="cuenta" value="<?= $cuenta ?>">
            <table id="divCuentasContables" class="table table-striped table-bordered table-hover table-condensed" cellspacing="0" width="100%">
                <thead>
                    <tr class="info">
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>C. Costos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr class="info">
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>C. Costos</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>