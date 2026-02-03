<?php
if (isset($_REQUEST['search'])) {
    $campo_like = $_REQUEST['search'];
} else {
    $campo_like = '';
}

if (isset($_REQUEST['consumidor'])) {
    $consumidor = $_REQUEST['consumidor'];
} else {
    $consumidor = '';
}

?>
<div class="row">
    <div class="col-sm-12">
        <br>
        <div class="panel panel-info">
            <div class="panel-heading text-left">
                Listado Clientes
            </div>
            <div class="panel-body table-responsive">

                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group input-group">
                        <input class="form-control form-control-sm" type="text" id="search_cliente"
                               name="search_cliente" placeholder="Buscar Cliente" value="<?=$campo_like?>" style="text-transform: none;"/>
                        <span class="input-group-addon primary" style="cursor: pointer;" onclick="consultarClientes();"><i
                                    class="fa fa-search"></i></span>
                    </div>
                </div>

                <div id="divBusquedaSeg"></div>
            </div>
        </div>
    </div>
</div>
<script>
    // FUNCION DE INICIO
    function genera_busqueda() {
        jsShowWindowLoad();

        var consumidor = '<?=$consumidor?>';
        if (consumidor == 'true') {
            xajax_genera_busqueda_cliente_consumidor('<?= $campo_like ?>');
        } else {
            xajax_genera_busqueda_cliente('<?= $campo_like ?>');
        }

    }


    var input = document.getElementById("search_cliente");
    input.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            jsShowWindowLoad();
            event.preventDefault();
            consultarClientes();
        }
    });

    function consultarClientes() {
        var consumidor = '<?=$consumidor?>';
        var cliente__sear = $('#search_cliente').val();

        if (consumidor == 'true') {
            xajax_genera_busqueda_cliente_consumidor(cliente__sear);
        } else {
            xajax_genera_busqueda_cliente(cliente__sear);
        }
    }

    function asignar_seg(cod, cli, ruc, dir, tel, cel, vend, cont, pre, est, tip, desc, desc1, limi, ciud, email = '',dia) {
        var consumidor = '<?=$consumidor?>';
        if (est != 'S') {
                document.form1.proveedor.value = cod;
                document.form1.proveedor_nombre.value = cli;
                document.form1.ruc_prove.value = ruc;
                document.form1.proveedor_correo.value = email;
                document.form1.proveedor_movil.value = tel;
                
            $("#miModalLoad").modal("hide");

        } else {
            alertSwal('Cliente: ' + cli + ' Suspendido...');
        }
    }



    genera_busqueda();

    function init() {
        $('#tbclientes').DataTable().destroy();
        var table = $('#tbclientes').DataTable({
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
            "paging": false,
            "ordering": true,
            "info": true,
            "searching": false
        });
    }


</script>
