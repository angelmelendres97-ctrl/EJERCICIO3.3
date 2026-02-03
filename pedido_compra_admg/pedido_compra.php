<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" type="text/css"
        href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.buttons.min.css"
        media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css"
        href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" type="text/css"
        href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css"
        media="screen">
    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/style.css">

    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript"
        src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.print.min.js"></script>

    <style>
        .input-group-addon.primary {
            color: rgb(255, 255, 255);
            background-color: rgb(50, 118, 177);
            border-color: rgb(40, 94, 142);
        }
        table.dataTable {
            font-size: 12px; /* Cambia a cualquier tamaño que desees */
            table-layout: fixed;
            word-wrap: break-word;
        }

        table.dataTable thead th {
            font-size: 13px; /* Tamaño para encabezados */
        }

        table.dataTable tbody td {
            font-size: 12px; /* Tamaño para celdas */
        }

        .productos-agregados-card table {
            font-size: 13px;
            color: #2c3e50;
        }

        .productos-agregados-card th {
            font-size: 14px;
            font-weight: 600;
            color: #1f2d3d;
        }

        .productos-agregados-card td {
            font-size: 13px;
        }

        .productos-agregados-card .section-card__body {
            overflow-x: auto;
        }

        .productos-agregados-card table {
            min-width: 720px;
        }

        .productos-agregados-card .detalle-texto-grid {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .detalle-editor-inline {
            margin-top: 6px;
            resize: vertical;
        }

        @media (max-width: 768px) {
            .productos-agregados-card table {
                min-width: 600px;
            }
        }

        /* Fix para alineación de cabeceras DataTables con scroll horizontal */
        .dataTables_scrollHead,
        .dataTables_scrollBody {
            width: 100% !important;
        }

        .dataTables_scrollHeadInner {
            width: 100% !important;
        }

        .dataTables_scrollHeadInner table {
            width: 100% !important;
        }
    </style>



    <?php
    unset($_SESSION['CodigosRecetaReorden']);
    if (isset($_GET['codigos_receta_reorden'])) {
        $codigos_receta_reorden = $_GET['codigos_receta_reorden'];
        $_SESSION['CodigosRecetaReorden'] = $codigos_receta_reorden;
    }

    ///VALIDACION MODAL EDITAR
    if (isset($_GET['codsol'])) {
        $codigo_pedido = $_GET['codsol'];
        $empr_pedido = $_GET['codEmpr'];
        $sucu_pedido = $_GET['codSucu'];
    } else {
        $codigo_pedido = 0;
        $empr_pedido = 0;
        $sucu_pedido = 0;
    }

    ///SOLITUD DE MATERIALES
    if (isset($_GET['idReq'])) {

        $idReq = $_GET['idReq'];
        $filDreq = $_GET['filDreq'];
    } else {
        $idReq = 0;
        $filDreq = 0;
    }
    ?>


    <script>
        $(document).on('hidden.bs.modal', function(event) {
            if ($('.modal:visible').length) {
                $('body').addClass('modal-open');
            }
        });

        $(document).on('shown.bs.modal', '#ModalDetalle, #ModalDetalleOrd', function() {
            var table = $(this).find('table.dataTable').DataTable();
            if ($.fn.DataTable.isDataTable(table)) {
                table.columns.adjust();
            }
        });

        function validarCantidadCumplimiento(input, max) {
            var valor = parseFloat(input.value);
            if (isNaN(valor) || valor < 0) {
                input.value = 0;
            } else if (valor > max) {
                alert("La cantidad cumplida no puede ser mayor a la solicitada (" + max + ")");
                input.value = max;
            }
        }

        function guardar_cumplimiento(codpedi, idempresa, idsucursal) {
            // Usar API de DataTable para obtener todos los inputs, incluso los de otras paginas
            var table = $('#tbdetalle').DataTable();
            var data = table.$('input').serialize();
            
            if (!data) {
                alert("No hay datos para guardar o error en la tabla.");
                return;
            }
            xajax_guardar_cumplimiento(codpedi, idempresa, idsucursal, data);
        }

        function modal_ordenes_compra(id, empresa, sucursal) {
            $("#ModalOrdenes").modal("show");
            xajax_form_ordenes_proveedores(id, empresa, sucursal);
        }

        function imprime_cuadro(id, empresa, sucursal) {
            xajax_genera_pdf_cuadro(id, empresa, sucursal);
        }

        function elimina_prove(idempresa, idsucursal, codclpv, codpedi, codapro, codprod, tipo) {

            Swal.fire({
                title: 'Esta seguro de eliminar el proveedor...?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '40%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_eliminar_prove(idempresa, idsucursal, codclpv, codpedi, codapro, codprod, tipo);
                }
            })


        }

        function autoriza_proforma(id, idapro, empresa, sucursal) {
            Swal.fire({
                title: '¿Esta seguro de Autorizar la Proforma?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '25%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_autorizar_precios_proforma(id, idapro, empresa, sucursal, xajax.getFormValues("form1"));
                }
            })
        }

        function generar_orden_compra(id, idapro, empresa, sucursal) {
            Swal.fire({
                title: '¿Esta seguro de generar la Orden de Compra?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '25%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_orden_compra_proforma(id, idapro, empresa, sucursal, xajax.getFormValues("form1"));
                }
            })
        }

        function ingresa_proforma(id, idapro, empresa, sucursal) {
            Swal.fire({
                title: '¿Esta seguro de Ingresar los precios de la Proforma?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '25%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_ingresar_precios_proforma(id, idapro, empresa, sucursal, xajax.getFormValues("form1"));
                }
            })
        }

        function genera_proforma(id, idapro, empresa, sucursal) {
            Swal.fire({
                title: '¿Esta seguro de Ingresar la Proforma?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '25%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_generar_proforma(id, idapro, empresa, sucursal, xajax.getFormValues("form1"));
                }
            })
        }

        function autocompletar_prove(event, op) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                searchClientes(op);
            }
        }

        function searchClientes(op) {
            //var consumidor = document.getElementById('consumidor').checked;
            var consumidor = '';
            var search = '';

            if (op == 1) {
                search = document.getElementById('proveedor_nombre').value;
            } else {
                search = document.getElementById('ruc_prove').value;
            }

            var pagina_in = 'busca_cliente.php?search=' + search + '&consumidor=' + consumidor;
            $('#modal_load_body').load(encodeURI(pagina_in), function() {
                $("#miModalLoad").modal("show");
                $("#modal_load_titulo").html('<b>Buscar Cliente</b>');
            });
        }

        function cerrarModalProveedor() {
            $("#ModalProveedor").html("");
            $("#ModalProveedor").modal("hide");
        }

        function cerrarModalProfProv() {
            $("#ModalProfProv").html("");
            $("#ModalProfProv").modal("hide");
        }

        function agrega_proveedor(id, idapro, empresa, sucursal, tipo) {

            var prove = document.getElementById("proveedor").value;
            var correo = document.getElementById("proveedor_correo").value;

            if (prove == '') {
                alertSwal('Ingrese un Proveedor', 'warning');
                foco('proveedor_nombre');
            } else if (correo == '') {
                alertSwal('Ingrese el correo', 'warning');
                foco('proveedor_correo');
            } else {
                jsShowWindowLoad();
                xajax_agregar_proveedor(id, idapro, empresa, sucursal, tipo, xajax.getFormValues("form1"));
            }

        }

        function marcar(source, tip) {

            checkboxes = document.getElementsByTagName('input'); //obtenemos todos los controles del tipo Input
            for (i = 0; i < checkboxes.length; i++) //recoremos todos los controles
            {

                if (checkboxes[i].type == "checkbox") //solo si es un checkbox entramos
                {

                    var stg = checkboxes[i].id;
                    if (stg.includes(tip)) {
                        checkboxes[i].checked = source.checked; //si es un checkbox le damos el valor del checkbox que lo llamó (Marcar/Desmarcar Todos)
                    }

                }
            }
        }

        function modal_prove(id, idapro, empresa, sucursal, tipo) {
            $("#ModalProveedor").modal("show");
            xajax_form_proveedores(id, idapro, empresa, sucursal, tipo);

        }

        function modal_proformas(id, idapro, empresa, sucursal) {
            $("#ModalProfProv").modal("show");
            xajax_form_proforma_proveedores(id, idapro, empresa, sucursal);

        }

        function cerrarModalAnular() {
            $("#ModalAnular").html("");
            $("#ModalAnular").modal("hide");
        }

        function anula_solicitud(id, empresa, sucursal) {
            var motivo = document.getElementById('det_anula').value;

            if (motivo == '') {
                alertSwal('Ingrese un motivo', 'warning');
                document.getElementById('det_anula').focus();
            } else {
                Swal.fire({
                    title: '¿Esta seguro de anular la solicitud: ' + id + '?',
                    text: "",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                    width: '25%',
                }).then((result) => {
                    if (result.value) {
                        jsShowWindowLoad();
                        xajax_anular_solicitud(id, empresa, sucursal, motivo);
                    }
                })
            }
        }

        function anular_solicitud(id, empresa, sucursal) {
            $("#ModalAnular").modal("show");
            xajax_form_anular(id, empresa, sucursal);
        }

        function editar_solicitud(id, empresa, sucursal) {
            $("#ModalEditar").modal("show");
            xajax_form_editar(id, empresa, sucursal);
        }

        function cerrarModalAprobaciones() {
            $("#ModalAprobaciones").html("");
            $("#ModalAprobaciones").modal("hide");
        }

        function autorizar_solicitud(id, idapro, empresa, sucursal) {
            Swal.fire({
                title: '¿Desea autorizar las solicitud: ' + id + '?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '25%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_autorizar_solicitud(id, idapro, empresa, sucursal, xajax.getFormValues("form1"));
                }
            })
        }

        function valida_cant_ped(eti, cant) {
            var cant_old = parseFloat(cant);
            var cant_new = parseFloat(document.getElementById(eti).value);

            if (cant_new > cant_old) {
                alertSwal("La cantidad no puede ser mayor a la solicitada");
                document.getElementById(eti).value = cant;
            }
            if (cant_new <= 0) {
                alertSwal("La cantidad debe ser mayor a 0");
                document.getElementById(eti).value = cant;
            }
        }

        function aprobar_solicitud(id, idapro, empresa, sucursal) {
            $("#ModalAprobaciones").modal("show");
            xajax_form_aprobaciones(id, idapro, empresa, sucursal);
        }

        function detalle_pedido(id, empresa, sucursal, tipo) {
            if (tipo == 1) {
                $("#ModalDetalle").modal("show");
            } else {
                $("#ModalDetalleOrd").modal("show");
            }

            xajax_form_detalle(id, empresa, sucursal, tipo);
        }

        function adjuntos_solicitud(id, empresa, sucursal, tipo) {
            if (tipo == 1) {
                $("#ModalAdj").modal("show");
            } else {
                $("#ModalAdjOrd").modal("show");
            }

            xajax_adjuntos_solicitud(id, empresa, sucursal, tipo);
        }

        function carga_detalle_pedido(id, empr, sucu) {
            xajax_detalle_pedido(id, empr, sucu);
        }

        function editaPedido(id, empr, sucu) {
            $("#ModalPedidos").html("");
            $("#ModalPedidos").modal("hide");
            setEstadoPendiente('editando');
            xajax_carga_pedido(id, empr, sucu);
        }

        function navegarPedido(direccion) {
            var empresa = document.getElementById('empresa') ? document.getElementById('empresa').value : '';
            var sucursal = document.getElementById('sucursal') ? document.getElementById('sucursal').value : '';
            var actual = document.getElementById('nota_compra') ? document.getElementById('nota_compra').value : '';

            if (!empresa || !sucursal) {
                alertSwal('Seleccione empresa y sucursal para navegar entre pedidos');
                return;
            }

            setEstadoPendiente('creada');
            jsShowWindowLoad();
            xajax_navegar_pedido(direccion, actual || 0, empresa, sucursal);
        }

        function buscarPedidoPorNumero(event) {
            if (event && event.keyCode !== 13) {
                return;
            }

            const campoBusqueda = document.getElementById('busquedaPedido');
            const empresa = document.getElementById('empresa') ? document.getElementById('empresa').value : '';
            const sucursal = document.getElementById('sucursal') ? document.getElementById('sucursal').value : '';

            if (!campoBusqueda || !campoBusqueda.value) {
                alertSwal('Ingrese un número de pedido para buscar');
                return;
            }

            if (!empresa || !sucursal) {
                alertSwal('Seleccione empresa y sucursal antes de buscar un pedido');
                return;
            }

            setEstadoPendiente('creada');
            jsShowWindowLoad();
            xajax_buscar_pedido_por_numero(campoBusqueda.value, empresa, sucursal);
        }

        function abrirModalPedidos() {
            $("#ModalPedidos").modal("show");
            xajax_lista_pedidos();
        }

        function duplicarPedido() {
            const nota = document.getElementById('nota_compra');
            const ctrl = document.getElementById('ctrl');
            const codigoInterno = document.getElementById('pedi_cod_pedi');

            if (!nota || !nota.value) {
                alertSwal('Cargue un pedido antes de duplicarlo');
                return;
            }

            if (ctrl) {
                ctrl.value = 1;
            }

            nota.value = '';
            if (codigoInterno) {
                codigoInterno.value = '';
            }
            establecerEstadoFormulario('creando');
            alertSwal('Se preparó una copia del pedido. Actualice los datos necesarios y guarde para crear uno nuevo.', 'info');
        }

        function cargaAnulado(id, empr, sucu) {
            $("#ModalAnulados").html("");
            $("#ModalAnulados").modal("hide");
            xajax_carga_anulado(id, empr, sucu);
        }





        function normalizarValorCelda(valor) {
            var div = document.createElement('div');
            div.innerHTML = valor;
            return div.textContent || div.innerText || '';
        }

        function generarOpcionesFiltro(table) {
            var filtros = [{
                    id: 'filtroEstado',
                    columna: '.col-estado'
                },
                {
                    id: 'filtroBodega',
                    columna: '.col-bodega'
                },
                {
                    id: 'filtroSucursal',
                    columna: '.col-sucursal'
                }
            ];

            filtros.forEach(function(filtro) {
                var select = document.getElementById(filtro.id);
                if (!select) {
                    return;
                }

                var datos = table.column(filtro.columna).data().toArray()
                    .map(function(valor) {
                        return normalizarValorCelda(valor);
                    })
                    .filter(function(valor) {
                        return valor !== '';
                    });

                var valoresUnicos = Array.from(new Set(datos)).sort();
                select.innerHTML = '<option value="">Todos</option>' +
                    valoresUnicos.map(function(valor) {
                        return '<option value="' + valor + '">' + valor + '</option>';
                    }).join('');
            });
        }

        function reporte_solicitudes() {
            jsShowWindowLoad();
            xajax_reporte_solicitudes(xajax.getFormValues("form1"));
        }


        function configurarFiltrosReporte(table) {
            var filtroEstado = document.getElementById('filtroEstado');
            var filtroBodega = document.getElementById('filtroBodega');
            var filtroSucursal = document.getElementById('filtroSucursal');
            var buscador = document.getElementById('buscadorSolicitudes');
            var botonLimpiar = document.getElementById('btnLimpiarBusquedaSolicitudes');
            var botonGenerar = document.getElementById('btnGenerarReporteSolicitudes');
            var botonImprimir = document.getElementById('btnImprimirSeleccion');
            var filtroPedidoNumero = document.getElementById('filtroPedidoNumero');
            var filtroNombreResponsable = document.getElementById('filtroNombreResponsable');
            var filtroMotivo = document.getElementById('filtroMotivo');
            var filtroProductoDetalle = document.getElementById('filtroProductoDetalle');

            // Filtros avanzados (por columnas específicas)
            var aplicarFiltrosAvanzados = function() {
                if (filtroPedidoNumero) {
                    table.column('.col-numero').search(filtroPedidoNumero.value || '', false, false);
                }
                if (filtroNombreResponsable) {
                    table.column('.col-responsable').search(filtroNombreResponsable.value || '', false, false);
                }
                if (filtroMotivo) {
                    table.column('.col-motivo').search(filtroMotivo.value || '', false, false);
                }
                if (filtroProductoDetalle) {
                    table.column('.col-busqueda').search(filtroProductoDetalle.value || '', false, false);
                }
                table.draw();
            };

            if (filtroBodega) {
                filtroBodega.onchange = function() {
                    table.column('.col-bodega').search(this.value || '', false, false).draw();
                };
            }

            if (filtroSucursal) {
                filtroSucursal.onchange = function() {
                    table.column('.col-sucursal').search(this.value || '', false, false).draw();
                };
            }

            // Búsqueda global + estado -> SOLO al presionar "Generar reporte"
            var aplicarFiltrosBasicos = function() {
                var termino = (buscador && buscador.value) ? buscador.value : '';
                table.search(termino);
                table.column('.col-busqueda').search(termino, false, false);

                if (filtroEstado) {
                    table.column('.col-estado').search(filtroEstado.value || '', false, false);
                }
                table.draw();
            };

            if (botonGenerar) {
                botonGenerar.onclick = function() {
                    aplicarFiltrosBasicos();
                    aplicarFiltrosAvanzados(); // si estás usando también los filtros por número, motivo, etc.
                };
            }

            if (botonLimpiar) {
                botonLimpiar.onclick = function() {
                    if (buscador) buscador.value = '';
                    if (filtroEstado) {
                        filtroEstado.value = '';
                        table.column('.col-estado').search('', false, false);
                    }

                    if (filtroPedidoNumero) filtroPedidoNumero.value = '';
                    if (filtroNombreResponsable) filtroNombreResponsable.value = '';
                    if (filtroMotivo) filtroMotivo.value = '';
                    if (filtroProductoDetalle) filtroProductoDetalle.value = '';

                    table.search('');
                    table.column('.col-busqueda').search('', false, false);

                    aplicarFiltrosAvanzados();
                };
            }

            [filtroPedidoNumero, filtroNombreResponsable, filtroMotivo, filtroProductoDetalle].forEach(function(input) {
                if (input) {
                    input.oninput = aplicarFiltrosAvanzados;
                }
            });

            if (botonImprimir) {
                botonImprimir.onclick = imprimirSolicitudesSeleccionadas;
            }
        }

        function imprimirSolicitudesSeleccionadas() {
            var seleccionados = Array.from(solicitudesSeleccionadas);
            if (!seleccionados.length) {
                alertSwal('Seleccione al menos una solicitud para imprimir');
                return;
            }

            seleccionados.forEach(function(id, index) {
                setTimeout(function() {
                    vista_previa_reporte(id);
                }, index * 300);
            });
        }

        let aprobadoresDisponibles = [];
        let aprobadoresSeleccionados = [];
        let gruposAprobadores = [];
        let ordenCargosSeleccionados = [];
        let filtroAprobadores = '';
        let filtroCargoId = '';
        let estadoFormulario = 'creando';
        let estadoPendiente = '';
        let aprobacionesDesactivadas = false;
        let contextoAprobaciones = {
            empresaId: '',
            sucursalId: ''
        };
        let aprobadorEnEdicion = '';
        let grupoEnEdicion = '';
        let avisoCargoElaboradoPorMostrado = false;
        let cargoArrastrado = '';
        let solicitudesSeleccionadas = new Set();

        (function adaptarAlertasServidor() {
            const alertaNativa = window.alert;
            window.alert = function(mensaje) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: mensaje,
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                } else if (typeof alertSwal === 'function') {
                    alertSwal(mensaje, 'warning');
                } else if (typeof alertaNativa === 'function') {
                    alertaNativa(mensaje);
                }
            };
        })();

        function aplicarColorEstado() {
            const tarjeta = document.getElementById('tarjetaPrincipal');
            if (!tarjeta) {
                return;
            }

            tarjeta.classList.remove('estado-creando', 'estado-creada', 'estado-editando');
            tarjeta.classList.add('estado-' + estadoFormulario);
        }

        function imprimirPedido() {
            var id = document.getElementById('nota_compra') ?
                document.getElementById('nota_compra').value :
                '';

            if (id && id.trim() !== '') {
                vista_previa_reporte(id);
            } else {
                alertSwal('Por favor ingrese el Pedido para generar vista previa', 'warning');
            }
        }


        function aplicarHabilitacionCampos(habilitar) {
            const tarjeta = document.getElementById('tarjetaPrincipal');
            if (!tarjeta) {
                return;
            }

            const elementos = tarjeta.querySelectorAll(
                '.section-card__body input:not([type="hidden"]), ' +
                '.section-card__body select, ' +
                '.section-card__body textarea, ' +
                '.section-card__body button, ' +
                '.section-card__body .btn'
            );

            elementos.forEach(function(elemento) {
                // 1) No tocar nada que esté dentro de las acciones de la tarjeta
                if (elemento.closest('.section-card__actions')) {
                    return;
                }

                // 2) No bloquear el botón de imprimir
                if (
                    elemento.id === 'btnImprimirPedido' ||
                    elemento.classList.contains('btn-imprimir-pedido')
                ) {
                    // Aseguramos que esté habilitado siempre
                    elemento.disabled = false;
                    elemento.style.pointerEvents = '';
                    elemento.classList.remove('disabled');
                    return;
                }

                // 3) Resto de lógica de bloqueo
                if (elemento.tagName === 'BUTTON' || elemento.classList.contains('btn')) {
                    elemento.disabled = !habilitar;
                    elemento.style.pointerEvents = habilitar ? '' : 'none';
                    elemento.classList.toggle('disabled', !habilitar);
                } else if (
                    elemento.tagName === 'SELECT' ||
                    elemento.type === 'checkbox' ||
                    elemento.type === 'radio'
                ) {
                    elemento.disabled = !habilitar;
                } else {
                    elemento.readOnly = !habilitar;
                }
            });

            bloquearCamposDetalleProductos(habilitar);
        }

        function bloquearCamposDetalleProductos(habilitar) {
            const contenedores = ['divFormularioDetalle', 'divFormularioDetalle2'];

            contenedores.forEach(function(idContenedor) {
                const contenedor = document.getElementById(idContenedor);
                if (!contenedor) {
                    return;
                }

                const campos = contenedor.querySelectorAll('input, select, textarea, button, .btn');
                campos.forEach(function(campo) {
                    if (campo.tagName === 'BUTTON' || campo.classList.contains('btn')) {
                        campo.disabled = !habilitar;
                        campo.style.pointerEvents = habilitar ? '' : 'none';
                        campo.classList.toggle('disabled', !habilitar);
                    } else if (campo.type === 'checkbox' || campo.type === 'radio' || campo.tagName === 'SELECT') {
                        campo.disabled = !habilitar;
                    } else {
                        campo.readOnly = !habilitar;
                    }
                });
            });
        }

        function actualizarEstadoNavegacion(tieneAnterior, tieneSiguiente) {
            const botonPrimero = document.getElementById('btnPrimeroPedido');
            const botonAnterior = document.getElementById('btnAnteriorPedido');
            const botonSiguiente = document.getElementById('btnSiguientePedido');
            const botonUltimo = document.getElementById('btnUltimoPedido');

            const mostrarPrevios = tieneAnterior !== false;
            const mostrarSiguientes = tieneSiguiente !== false;

            if (botonPrimero) {
                botonPrimero.style.display = mostrarPrevios ? '' : 'none';
            }

            if (botonAnterior) {
                botonAnterior.style.display = mostrarPrevios ? '' : 'none';
            }

            if (botonSiguiente) {
                botonSiguiente.style.display = mostrarSiguientes ? '' : 'none';
            }

            if (botonUltimo) {
                botonUltimo.style.display = mostrarSiguientes ? '' : 'none';
            }
        }

        function actualizarBotonesPorEstado() {
            const botonGuardarSup = document.getElementById('btnGuardarPedidoSuperior');
            const botonActualizarSup = document.getElementById('btnActualizarPedidoSuperior');
            const botonGuardarInf = document.getElementById('btnGuardarPedidoInferior');
            const botonActualizarInf = document.getElementById('btnActualizarPedidoInferior');
            const botonDuplicar = document.getElementById('btnDuplicarPedido');
            const botonImprimir = document.getElementById('btnImprimirPedido');
            const botonEditar = document.getElementById('btnEditarPedido');

            const esCreacion = estadoFormulario === 'creando';
            const esEdicion = estadoFormulario === 'editando';
            const esVista = estadoFormulario === 'creada';

            if (botonGuardarSup) {
                botonGuardarSup.style.display = esCreacion ? '' : 'none';
            }

            if (botonGuardarInf) {
                botonGuardarInf.style.display = esCreacion ? '' : 'none';
            }

            if (botonActualizarSup) {
                botonActualizarSup.style.display = esEdicion ? '' : 'none';
            }

            if (botonActualizarInf) {
                botonActualizarInf.style.display = esEdicion ? '' : 'none';
            }

            if (botonDuplicar) {
                botonDuplicar.style.display = esVista ? '' : 'none';
            }

            if (botonImprimir) {
                botonImprimir.style.display = esCreacion ? 'none' : '';
            }

            if (botonEditar) {
                botonEditar.style.display = esVista ? '' : 'none';
            }
        }

        function setEstadoPendiente(estado) {
            estadoPendiente = estado;
        }

        function aplicarEstadoPendiente() {
            if (estadoPendiente) {
                establecerEstadoFormulario(estadoPendiente);
                estadoPendiente = '';
            }
        }

        function establecerEstadoFormulario(estado) {
            estadoFormulario = estado;
            aplicarColorEstado();
            aplicarHabilitacionCampos(estado !== 'creada');
            actualizarBotonesPorEstado();
            actualizarAccionesAprobacionesPorEstado();

            if (estado === 'creada') {
                // Pedido ya creado: el estado real de omitir aprobaciones
                // se termina de ajustar con restaurarAprobadoresGuardados()
                toggleOmitirAprobaciones(false, true);
            } else if (estado === 'creando') {
                // NUEVO COMPORTAMIENTO:
                // Al crear la nota de compra NO se omiten aprobadores
                toggleOmitirAprobaciones(false, true);

                // Opcional pero recomendable: asegurar la sección desplegada
                ajustarSeccionAprobacionesPorDatos(true);
            }
        }


        function refrescarBloqueoCampos() {
            if (estadoFormulario === 'creada') {
                aplicarHabilitacionCampos(false);
            }
        }

        function mostrarMensajeAprobaciones(mensaje, tipo = 'success') {
            Swal.fire({
                title: mensaje,
                type: tipo,
                confirmButtonText: 'Aceptar'
            });
        }

        function actualizarVisibilidadAprobaciones() {
            const cuerpo = document.getElementById('cuerpoSeccionAprobaciones');
            const aviso = document.getElementById('avisoAprobacionesDesactivadas');

            if (cuerpo) {
                cuerpo.style.display = aprobacionesDesactivadas ? 'none' : '';
            }

            if (aviso) {
                aviso.style.display = aprobacionesDesactivadas ? 'block' : 'none';
            }
        }

        function actualizarAccionesAprobacionesPorEstado() {
            const esVista = estadoFormulario === 'creada';
            const contenedor = document.getElementById('accionesAprobaciones');
            const opcionAprobaciones = document.getElementById('opcionAprobaciones');
            const botonAbrirGestion = document.getElementById('btnAbrirGestionAprobaciones');

            if (opcionAprobaciones) {
                opcionAprobaciones.style.display = esVista ? 'none' : '';
            }

            if (botonAbrirGestion) {
                botonAbrirGestion.style.display = esVista ? 'none' : '';
            }

            if (contenedor) {
                contenedor.classList.toggle('acciones-aprobaciones-solo-lectura', esVista);
            }
        }

        function ordenarCargosPorPreferencia(cargos) {
            if (!Array.isArray(cargos)) {
                return [];
            }

            const mapaOrden = {};
            ordenCargosSeleccionados.forEach(function(id, indice) {
                mapaOrden[id] = indice + 1;
            });

            return cargos.slice().sort(function(a, b) {
                const ordenA = mapaOrden[a.id] || Number.MAX_SAFE_INTEGER;
                const ordenB = mapaOrden[b.id] || Number.MAX_SAFE_INTEGER;

                if (ordenA === ordenB) {
                    return (a.nombre || '').localeCompare(b.nombre || '');
                }

                return ordenA - ordenB;
            });
        }

        function sincronizarOrdenCargos(contexto = 'buscar') {
            const cargosActuales = obtenerCargosPorContexto(contexto);
            if (!cargosActuales.length) {
                ordenCargosSeleccionados = [];
                return;
            }

            const idsActuales = cargosActuales.map(function(cargo) {
                return cargo.id;
            });

            const idsOrdenados = cargosActuales.slice().sort(function(a, b) {
                const ordenA = a.orden ? parseInt(a.orden, 10) : Number.MAX_SAFE_INTEGER;
                const ordenB = b.orden ? parseInt(b.orden, 10) : Number.MAX_SAFE_INTEGER;
                if (ordenA === ordenB) {
                    return (a.nombre || '').localeCompare(b.nombre || '');
                }
                return ordenA - ordenB;
            }).map(function(cargo) {
                return cargo.id;
            });

            if (!ordenCargosSeleccionados.length) {
                ordenCargosSeleccionados = idsOrdenados;
                return;
            }

            ordenCargosSeleccionados = ordenCargosSeleccionados.filter(function(id) {
                return idsActuales.indexOf(id) !== -1;
            });

            idsOrdenados.forEach(function(id) {
                if (ordenCargosSeleccionados.indexOf(id) === -1) {
                    ordenCargosSeleccionados.push(id);
                }
            });
        }

        function actualizarOrdenAprobadoresDesdeCargos() {
            sincronizarOrdenCargos('buscar');
            const mapaOrden = {};
            ordenCargosSeleccionados.forEach(function(id, indice) {
                mapaOrden[id] = indice + 1;
            });

            aprobadoresSeleccionados = aprobadoresSeleccionados.map(function(aprobador) {
                if (!aprobador.grupoId) {
                    return aprobador;
                }

                const orden = mapaOrden[aprobador.grupoId] || mapaOrden[aprobador.cargoId] || null;
                return Object.assign({}, aprobador, { orden: orden });
            }).sort(function(a, b) {
                const ordenA = a.orden || Number.MAX_SAFE_INTEGER;
                const ordenB = b.orden || Number.MAX_SAFE_INTEGER;

                if (ordenA === ordenB) {
                    return (a.grupoNombre || '').localeCompare(b.grupoNombre || '');
                }

                return ordenA - ordenB;
            });
        }

        function moverCargo(grupoId, paso) {
            const indice = ordenCargosSeleccionados.indexOf(grupoId);
            if (indice === -1) {
                return;
            }

            const nuevoIndice = indice + paso;
            if (nuevoIndice < 0 || nuevoIndice >= ordenCargosSeleccionados.length) {
                return;
            }

            const ordenActual = ordenCargosSeleccionados.slice();
            const [item] = ordenActual.splice(indice, 1);
            ordenActual.splice(nuevoIndice, 0, item);
            ordenCargosSeleccionados = ordenActual;
            actualizarOrdenAprobadoresDesdeCargos();
            renderFirmasSeleccionadas();
        }

        function cambiarPosicionCargo(grupoId, posicion) {
            const indiceActual = ordenCargosSeleccionados.indexOf(grupoId);
            if (indiceActual === -1) {
                return;
            }

            const posicionNumerica = isNaN(posicion) ? 0 : posicion;
            const indiceObjetivo = Math.max(0, Math.min(posicionNumerica, ordenCargosSeleccionados.length - 1));
            const ordenActual = ordenCargosSeleccionados.slice();
            const [item] = ordenActual.splice(indiceActual, 1);
            ordenActual.splice(indiceObjetivo, 0, item);
            ordenCargosSeleccionados = ordenActual;
            actualizarOrdenAprobadoresDesdeCargos();
            renderFirmasSeleccionadas();
        }

        function iniciarArrastreCargo(evento) {
            cargoArrastrado = evento.currentTarget.dataset.grupoId;
            evento.dataTransfer.effectAllowed = 'move';
        }

        function permitirArrastreCargo(evento) {
            evento.preventDefault();
            evento.dataTransfer.dropEffect = 'move';
        }

        function soltarCargo(evento) {
            evento.preventDefault();
            const grupoObjetivo = evento.currentTarget.dataset.grupoId;
            if (!cargoArrastrado || !grupoObjetivo || cargoArrastrado === grupoObjetivo) {
                cargoArrastrado = '';
                return;
            }

            const origen = ordenCargosSeleccionados.indexOf(cargoArrastrado);
            const destino = ordenCargosSeleccionados.indexOf(grupoObjetivo);

            if (origen === -1 || destino === -1) {
                cargoArrastrado = '';
                return;
            }

            const ordenActual = ordenCargosSeleccionados.slice();
            ordenActual.splice(origen, 1);
            ordenActual.splice(destino, 0, cargoArrastrado);
            ordenCargosSeleccionados = ordenActual;
            cargoArrastrado = '';
            actualizarOrdenAprobadoresDesdeCargos();
            renderFirmasSeleccionadas();
        }

        function limpiarArrastreCargo() {
            cargoArrastrado = '';
        }

        function formatearMayusculas(valor) {
            return (valor || '').toString().toUpperCase();
        }

        function toggleOmitirAprobaciones(omitir, forzarEstado) {
            aprobacionesDesactivadas = omitir;
            const check = document.getElementById('omitirAprobaciones');
            const campo = document.getElementById('omitirAprobacionesCampo');

            if (check) {
                check.checked = omitir;
            }
            if (campo) {
                campo.value = omitir ? '1' : '0';
            }

            actualizarVisibilidadAprobaciones();

            if (omitir) {
                aprobadoresSeleccionados = [];
                renderFirmasSeleccionadas();
                establecerAprobadoresEnvio();
                return;
            }

            if (!forzarEstado) {
                configurarSeccionAprobaciones();
            }
        }

        function aplicarEstadoAprobacionesGuardado(tieneAprobaciones) {
            if (tieneAprobaciones) {
                toggleOmitirAprobaciones(false, true);
                ajustarSeccionAprobacionesPorDatos(true);
                return;
            }

            toggleOmitirAprobaciones(true, true);
            ajustarSeccionAprobacionesPorDatos(false);
        }

        function restaurarAprobadoresGuardados(aprobadores, omitir) {
            const listado = Array.isArray(aprobadores) ? aprobadores : [];
            const omitirAprobaciones = !!omitir;
            toggleOmitirAprobaciones(omitirAprobaciones, true);

            if (omitirAprobaciones) {
                ajustarSeccionAprobacionesPorDatos(false);
                return;
            }

            const cargosGuardados = [];

            const rolesVistos = [];
            aprobadoresSeleccionados = listado.map(function(aprobador, indice) {
                const grupoId = aprobador.grupoId || aprobador.cargo_id || aprobador.cargoId || '';
                const grupoNombre = aprobador.grupoNombre || aprobador.cargo || aprobador.cargo_nombre || '';

                if (grupoId && cargosGuardados.indexOf(grupoId) === -1) {
                    cargosGuardados.push(grupoId);
                }

                return {
                    id: aprobador.id ? aprobador.id.toString() : '',
                    nombre: formatearMayusculas(aprobador.nombre),
                    grupoId: grupoId ? grupoId.toString() : '',
                    grupoNombre: formatearMayusculas(grupoNombre),
                    cargo: formatearMayusculas(grupoNombre),
                    sucursalId: aprobador.sucursalId ? aprobador.sucursalId.toString() : obtenerValorSucursalActual(),
                    empresaId: aprobador.empresaId ? aprobador.empresaId.toString() : obtenerValorEmpresaActual(),
                    enviar: aprobador.enviar === false || aprobador.enviar === 'N' ? false : true,
                    orden: aprobador.orden ? parseInt(aprobador.orden, 10) : indice + 1
                };
            }).filter(function(aprobador) {
                const claveRol = aprobador.grupoId || aprobador.grupoNombre || aprobador.cargo;
                if (!claveRol) {
                    return true;
                }

                if (rolesVistos.indexOf(claveRol) !== -1) {
                    return false;
                }

                rolesVistos.push(claveRol);
                return true;
            });

            if (cargosGuardados.length) {
                ordenCargosSeleccionados = cargosGuardados;
            } else {
                sincronizarOrdenCargos('buscar');
            }
            actualizarOrdenAprobadoresDesdeCargos();

            renderFirmasSeleccionadas();
            ajustarSeccionAprobacionesPorDatos(true);
        }

        function obtenerCargoElaboradoPor(empresaId, sucursalId) {
            const nombreObjetivo = normalizarTexto('Elaborado por');
            return gruposAprobadores.find(function(grupo) {
                return normalizarTexto(grupo.nombre) === nombreObjetivo && grupo.empresaId === empresaId && grupo.sucursalId === sucursalId;
            });
        }

        function formatearCargoServidor(cargo) {
            return {
                id: cargo && cargo.id ? cargo.id.toString() : '',
                nombre: formatearMayusculas(cargo ? cargo.nombre : ''),
                sucursalId: cargo && cargo.sucursalId ? cargo.sucursalId.toString() : '',
                sucursalNombre: formatearMayusculas(cargo ? (cargo.sucursalNombre || '') : ''),
                empresaId: cargo && cargo.empresaId ? cargo.empresaId.toString() : '',
                empresaNombre: formatearMayusculas(cargo ? (cargo.empresaNombre || '') : ''),
                estado: cargo && cargo.estado ? cargo.estado : 'S',
                orden: cargo && cargo.orden ? parseInt(cargo.orden, 10) : null
            };
        }

        function formatearAprobadorServidor(aprobador) {
            return {
                id: aprobador && aprobador.id ? aprobador.id.toString() : '',
                nombre: formatearMayusculas(aprobador ? aprobador.nombre : ''),
                grupoId: aprobador && aprobador.grupoId ? aprobador.grupoId.toString() : '',
                cargo: formatearMayusculas(aprobador ? (aprobador.cargo || aprobador.grupoNombre || '') : ''),
                grupoNombre: formatearMayusculas(aprobador ? (aprobador.grupoNombre || aprobador.cargo || '') : ''),
                sucursalId: aprobador && aprobador.sucursalId ? aprobador.sucursalId.toString() : '',
                sucursal: formatearMayusculas(aprobador ? (aprobador.sucursal || '') : ''),
                empresaId: aprobador && aprobador.empresaId ? aprobador.empresaId.toString() : '',
                empresa: formatearMayusculas(aprobador ? (aprobador.empresa || '') : '')
            };
        }

        function obtenerCargosPorContexto(contexto = 'buscar', incluirEliminados = false) {
            const sucursalId = obtenerValorSucursalActual(contexto);
            const empresaId = obtenerValorEmpresaActual(contexto);
            return gruposAprobadores.filter(function(grupo) {
                const coincideSucursal = grupo.sucursalId === sucursalId;
                const coincideEmpresa = grupo.empresaId === empresaId;
                const estaActivo = incluirEliminados ? true : grupo.estado !== 'N';
                return coincideSucursal && coincideEmpresa && estaActivo;
            });
        }

        function obtenerCargosSucursalActual() {
            return ordenarCargosPorPreferencia(obtenerCargosPorContexto('buscar'));
        }

        function obtenerAprobadoresPorCargo(grupoId, sucursalId, empresaId) {
            return aprobadoresDisponibles.filter(function(aprobador) {
                const coincideGrupo = aprobador.grupoId === grupoId;
                const coincideSucursal = !aprobador.sucursalId || aprobador.sucursalId === sucursalId;
                const coincideEmpresa = !aprobador.empresaId || aprobador.empresaId === empresaId;
                return coincideGrupo && coincideSucursal && coincideEmpresa;
            });
        }

        function sincronizarSeleccionConDatos() {
            const idsDisponibles = aprobadoresDisponibles.map(function(aprobador) {
                return aprobador.id;
            });

            aprobadoresSeleccionados = aprobadoresSeleccionados.filter(function(aprobador) {
                return aprobador.id === 'SOLICITANTE' || idsDisponibles.indexOf(aprobador.id) !== -1;
            });
        }

        function actualizarDatosAprobadores(datos) {
            jsRemoveWindowLoad();
            gruposAprobadores = Array.isArray(datos && datos.cargos) ? datos.cargos.map(formatearCargoServidor) : [];
            aprobadoresDisponibles = Array.isArray(datos && datos.aprobadores) ? datos.aprobadores.map(formatearAprobadorServidor) : [];
            aprobadorEnEdicion = '';
            restablecerFormularioAprobador();
            sincronizarSeleccionConDatos();
            sincronizarOrdenCargos('buscar');
            configurarSeccionAprobaciones(true);
        }

        const selectoresContexto = {
            buscar: {
                empresa: 'empresaBuscarAprobador',
                sucursal: 'sucursalBuscarAprobador'
            },
            agregar: {
                empresa: 'empresaAgregarAprobador',
                sucursal: 'sucursalAgregarAprobador'
            },
            crear: {
                empresa: 'empresaCrearGrupo',
                sucursal: 'sucursalCrearGrupo'
            },
            principal: {
                empresa: 'empresa',
                sucursal: 'sucursal'
            }
        };

        function normalizarTexto(texto) {
            return (texto || '').toString().trim().toLowerCase();
        }

        function existeCargoConNombre(nombre, empresaId, sucursalId, excluirId) {
            const nombreNormalizado = normalizarTexto(nombre);
            return gruposAprobadores.some(function(grupo) {
                const coincideNombre = normalizarTexto(grupo.nombre) === nombreNormalizado;
                const coincideEmpresa = grupo.empresaId === empresaId;
                const coincideSucursal = grupo.sucursalId === sucursalId;
                const esExcluido = excluirId && grupo.id === excluirId;
                return coincideNombre && coincideEmpresa && coincideSucursal && !esExcluido;
            });
        }

        function existeAprobadorConNombre(nombre, empresaId, sucursalId, excluirId) {
            const nombreNormalizado = normalizarTexto(nombre);
            return aprobadoresDisponibles.some(function(aprobador) {
                const coincideNombre = normalizarTexto(aprobador.nombre) === nombreNormalizado;
                const coincideEmpresa = aprobador.empresaId === empresaId;
                const coincideSucursal = aprobador.sucursalId === sucursalId;
                const esExcluido = excluirId && aprobador.id === excluirId;
                return coincideNombre && coincideEmpresa && coincideSucursal && !esExcluido;
            });
        }

        function obtenerSelectEmpresaReferencia(contexto = 'buscar') {
            const selectorId = selectoresContexto[contexto] ? selectoresContexto[contexto].empresa : null;
            const select = selectorId ? document.getElementById(selectorId) : null;
            if (select) {
                return select;
            }
            return document.getElementById(selectoresContexto.principal.empresa);
        }

        function obtenerSelectSucursalReferencia(contexto = 'buscar') {
            const selectorId = selectoresContexto[contexto] ? selectoresContexto[contexto].sucursal : null;
            const select = selectorId ? document.getElementById(selectorId) : null;
            if (select) {
                return select;
            }
            return document.getElementById(selectoresContexto.principal.sucursal);
        }

        function obtenerValorEmpresaActual(contexto = 'buscar') {
            const empresa = obtenerSelectEmpresaReferencia(contexto);
            if (empresa) {
                return empresa.value || '';
            }
            return '';
        }

        function obtenerEmpresaActual(contexto = 'buscar') {
            const empresa = obtenerSelectEmpresaReferencia(contexto);
            if (empresa && empresa.options && empresa.selectedIndex >= 0) {
                return empresa.options[empresa.selectedIndex].text || empresa.value;
            }
            return '';
        }

        function obtenerValorSucursalActual(contexto = 'buscar') {
            const sucursal = obtenerSelectSucursalReferencia(contexto);
            if (sucursal) {
                return sucursal.value || '';
            }
            return '';
        }

        function obtenerSucursalActual(contexto = 'buscar') {
            const sucursal = obtenerSelectSucursalReferencia(contexto);
            if (sucursal && sucursal.options && sucursal.selectedIndex >= 0) {
                return sucursal.options[sucursal.selectedIndex].text || sucursal.value;
            }
            return '';
        }

        function obtenerAprobadorSolicitante() {
            const solicitante = document.getElementById('solicitado');
            const sucursalActual = obtenerSucursalActual('buscar');
            const empresaActual = obtenerEmpresaActual('buscar');

            if (solicitante && solicitante.value) {
                return {
                    id: 'SOLICITANTE',
                    nombre: solicitante.value,
                    cargo: 'Solicitante',
                    sucursal: sucursalActual,
                    empresa: empresaActual
                };
            }

            return null;
        }

        function obtenerGrupoPorDefecto(contexto = 'buscar') {
            const sucursalId = obtenerValorSucursalActual(contexto);
            const sucursalNombre = obtenerSucursalActual(contexto);
            const empresaId = obtenerValorEmpresaActual(contexto);
            const empresaNombre = obtenerEmpresaActual(contexto);

            if (!sucursalId) {
                return '';
            }

            let grupo = gruposAprobadores.find(function(item) {
                return item.sucursalId === sucursalId && item.empresaId === empresaId && item.estado !== 'N';
            });
            if (!grupo) {
                grupo = {
                    id: 'GR-' + sucursalId,
                    nombre: 'Aprobadores ' + (sucursalNombre || sucursalId),
                    sucursalId: sucursalId,
                    sucursalNombre: sucursalNombre,
                    empresaId: empresaId,
                    empresaNombre: empresaNombre,
                    estado: 'S'
                };
                gruposAprobadores.push(grupo);
            }

            return grupo.id;
        }

        function obtenerNombreGrupo(grupoId) {
            const grupo = gruposAprobadores.find(function(item) {
                return item.id === grupoId;
            });
            return grupo ? grupo.nombre : '';
        }

        function sincronizarSelectoresModal() {
            const empresaPrincipal = document.getElementById('empresa');
            const sucursalPrincipal = document.getElementById('sucursal');
            const selectoresEmpresa = ['empresaBuscarAprobador', 'empresaAgregarAprobador', 'empresaCrearGrupo'];
            const selectoresSucursal = ['sucursalBuscarAprobador', 'sucursalAgregarAprobador', 'sucursalCrearGrupo'];

            selectoresEmpresa.forEach(function(selectorId) {
                const select = document.getElementById(selectorId);
                if (select && empresaPrincipal) {
                    select.innerHTML = empresaPrincipal.innerHTML;
                    select.value = empresaPrincipal.value;
                }
            });

            selectoresSucursal.forEach(function(selectorId) {
                const select = document.getElementById(selectorId);
                if (select && sucursalPrincipal) {
                    select.innerHTML = sucursalPrincipal.innerHTML;
                    select.value = sucursalPrincipal.value;
                }
            });
        }

        function renderSelectorGrupos(targetId = 'grupoAprobador', contexto = 'agregar') {
            const selector = document.getElementById(targetId);
            if (!selector) {
                return;
            }

            const sucursalId = obtenerValorSucursalActual(contexto);
            const empresaId = obtenerValorEmpresaActual(contexto);
            selector.innerHTML = '';

            if (!sucursalId || !empresaId) {
                selector.innerHTML = '<option value="">Seleccione empresa y sucursal para continuar</option>';
                return;
            }

            sincronizarOrdenCargos(contexto);

            const gruposSucursal = gruposAprobadores.filter(function(item) {
                return item.sucursalId === sucursalId && item.empresaId === empresaId && item.estado !== 'N';
            });
            if (!gruposSucursal.length) {
                obtenerGrupoPorDefecto(contexto);
            }

            ordenarCargosPorPreferencia(gruposAprobadores.filter(function(item) {
                    return item.sucursalId === sucursalId && item.empresaId === empresaId && item.estado !== 'N';
                }))
                .forEach(function(grupo) {
                    const option = document.createElement('option');
                    option.value = grupo.id;
                    option.textContent = grupo.nombre;
                    selector.appendChild(option);
                });
        }

        function registrarEventosAprobaciones() {
            const empresaPrincipal = document.getElementById('empresa');
            const sucursalPrincipal = document.getElementById('sucursal');
            const empresaBuscar = document.getElementById('empresaBuscarAprobador');
            const sucursalBuscar = document.getElementById('sucursalBuscarAprobador');
            const empresaAgregar = document.getElementById('empresaAgregarAprobador');
            const sucursalAgregar = document.getElementById('sucursalAgregarAprobador');
            const empresaCrear = document.getElementById('empresaCrearGrupo');
            const sucursalCrear = document.getElementById('sucursalCrearGrupo');

            if (empresaPrincipal && !empresaPrincipal.dataset.aprobacionListener) {
                empresaPrincipal.addEventListener('change', function() {
                    sincronizarSelectoresModal();
                    configurarSeccionAprobaciones();
                });
                empresaPrincipal.dataset.aprobacionListener = 'true';
            }

            if (sucursalPrincipal && !sucursalPrincipal.dataset.aprobacionListener) {
                sucursalPrincipal.addEventListener('change', function() {
                    sincronizarSelectoresModal();
                    configurarSeccionAprobaciones();
                });
                sucursalPrincipal.dataset.aprobacionListener = 'true';
            }

            if (empresaBuscar && !empresaBuscar.dataset.aprobacionListener) {
                empresaBuscar.addEventListener('change', configurarSeccionAprobaciones);
                empresaBuscar.dataset.aprobacionListener = 'true';
            }

            if (sucursalBuscar && !sucursalBuscar.dataset.aprobacionListener) {
                sucursalBuscar.addEventListener('change', configurarSeccionAprobaciones);
                sucursalBuscar.dataset.aprobacionListener = 'true';
            }

            if (empresaAgregar && !empresaAgregar.dataset.aprobacionListener) {
                empresaAgregar.addEventListener('change', function() {
                    renderSelectorGrupos('grupoAprobador', 'agregar');
                });
                empresaAgregar.dataset.aprobacionListener = 'true';
            }

            if (sucursalAgregar && !sucursalAgregar.dataset.aprobacionListener) {
                sucursalAgregar.addEventListener('change', function() {
                    renderSelectorGrupos('grupoAprobador', 'agregar');
                });
                sucursalAgregar.dataset.aprobacionListener = 'true';
            }

            if (empresaCrear && !empresaCrear.dataset.aprobacionListener) {
                empresaCrear.addEventListener('change', function() {
                    renderFiltroCargo();
                    renderListaCargos();
                    renderOrdenCargos();
                });
                empresaCrear.dataset.aprobacionListener = 'true';
            }

            if (sucursalCrear && !sucursalCrear.dataset.aprobacionListener) {
                sucursalCrear.addEventListener('change', function() {
                    renderFiltroCargo();
                    renderListaCargos();
                    renderOrdenCargos();
                });
                sucursalCrear.dataset.aprobacionListener = 'true';
            }
        }

        function renderFiltroCargo() {
            const selector = document.getElementById('filtroCargoAprobador');
            if (!selector) {
                return;
            }

            const sucursalIdActual = obtenerValorSucursalActual('buscar');
            const empresaIdActual = obtenerValorEmpresaActual('buscar');

            selector.innerHTML = '';
            const opcionTodos = document.createElement('option');
            opcionTodos.value = '';
            opcionTodos.textContent = 'Todos los cargos';
            selector.appendChild(opcionTodos);

            if (!sucursalIdActual || !empresaIdActual) {
                selector.value = '';
                return;
            }

            sincronizarOrdenCargos('buscar');
            ordenarCargosPorPreferencia(gruposAprobadores.filter(function(grupo) {
                return grupo.sucursalId === sucursalIdActual && grupo.empresaId === empresaIdActual && grupo.estado !== 'N';
            })).forEach(function(grupo) {
                const option = document.createElement('option');
                option.value = grupo.id;
                option.textContent = grupo.nombre;
                selector.appendChild(option);
            });

            selector.value = selector.querySelector('option[value="' + filtroCargoId + '"]') ? filtroCargoId : '';
        }

        function renderListaCargos() {
            const contenedor = document.getElementById('contenedorListaCargos');
            if (!contenedor) {
                return;
            }

            const sucursalIdActual = obtenerValorSucursalActual('crear');
            const empresaIdActual = obtenerValorEmpresaActual('crear');

            if (!sucursalIdActual || !empresaIdActual) {
                contenedor.innerHTML = '<div class="alert alert-info">Seleccione empresa y sucursal para ver los cargos disponibles.</div>';
                return;
            }

            sincronizarOrdenCargos('crear');
            const gruposSucursal = ordenarCargosPorPreferencia(gruposAprobadores.filter(function(grupo) {
                return grupo.sucursalId === sucursalIdActual && grupo.empresaId === empresaIdActual;
            }));

            if (!gruposSucursal.length) {
                contenedor.innerHTML = '<div class="alert alert-warning">No hay cargos registrados en esta sucursal. Cree uno para comenzar.</div>';
                return;
            }

            const filas = gruposSucursal.map(function(grupo, indice) {
                const esActivo = grupo.estado !== 'N';
                const badgeEstado = esActivo ? '' : ' <span class="label label-default">Eliminado</span>';
                let acciones = '';

                if (esActivo) {
                    acciones += '       <button type="button" class="btn btn-link btn-sm" onclick="editarCargo(\'' + grupo.id + '\');"><i class="fa fa-pencil"></i> Editar</button>';
                    acciones += '       <button type="button" class="btn btn-link btn-sm text-danger" onclick="eliminarCargo(\'' + grupo.id + '\');"><i class="fa fa-trash"></i> Eliminar</button>';
                } else {
                    acciones += '       <button type="button" class="btn btn-link btn-sm text-success" onclick="restaurarCargo(\'' + grupo.id + '\');"><i class="fa fa-undo"></i> Restaurar</button>';
                }

                return '' +
                    '<tr class="' + (esActivo ? '' : 'text-muted') + '">' +
                    '   <td class="text-center" style="width: 80px;">' + (indice + 1) + '</td>' +
                    '   <td>' + (grupo.nombre || '') + badgeEstado + '</td>' +
                    '   <td class="text-right">' + acciones + '</td>' +
                    '</tr>';
            }).join('');

            contenedor.innerHTML = '' +
                '<div class="table-responsive">' +
                '   <table class="table table-bordered table-hover tabla-aprobadores">' +
                '       <thead>' +
                '           <tr>' +
                '               <th class="text-center" style="width: 80px;">Orden</th>' +
                '               <th>Nombre del cargo</th>' +
                '               <th class="text-right" style="width: 180px;">Acciones</th>' +
                '           </tr>' +
                '       </thead>' +
                '       <tbody>' + filas + '</tbody>' +
                '   </table>' +
                '</div>';
        }

        function renderOrdenCargos() {
            const contenedor = document.getElementById('contenedorOrdenCargos');
            if (!contenedor) {
                return;
            }

            const sucursalIdActual = obtenerValorSucursalActual('crear');
            const empresaIdActual = obtenerValorEmpresaActual('crear');

            if (!sucursalIdActual || !empresaIdActual) {
                contenedor.innerHTML = '<div class="alert alert-info">Seleccione empresa y sucursal para ordenar cargos.</div>';
                return;
            }

            sincronizarOrdenCargos('crear');
            const cargos = obtenerCargosPorContexto('crear');

            if (!cargos.length) {
                contenedor.innerHTML = '<div class="alert alert-warning">No hay cargos activos para ordenar en esta sucursal.</div>';
                return;
            }

            const mapaCargos = {};
            cargos.forEach(function(cargo) {
                mapaCargos[cargo.id] = cargo;
            });

            const ordenados = ordenCargosSeleccionados.map(function(id) {
                return mapaCargos[id];
            }).filter(Boolean);

            const filas = ordenados.map(function(cargo, indice) {
                const puedeSubir = indice > 0;
                const puedeBajar = indice < ordenados.length - 1;
                return '' +
                    '<tr>' +
                    '   <td class="text-center" style="width: 80px;">' + (indice + 1) + '</td>' +
                    '   <td>' + (cargo.nombre || '') + '</td>' +
                    '   <td class="text-right" style="width: 140px;">' +
                    '       <button type="button" class="btn btn-default btn-xs" onclick="moverCargoEnModal(\'' + cargo.id + '\', -1);" ' + (puedeSubir ? '' : 'disabled') + '>' +
                    '           <i class="fa fa-arrow-up"></i>' +
                    '       </button> ' +
                    '       <button type="button" class="btn btn-default btn-xs" onclick="moverCargoEnModal(\'' + cargo.id + '\', 1);" ' + (puedeBajar ? '' : 'disabled') + '>' +
                    '           <i class="fa fa-arrow-down"></i>' +
                    '       </button>' +
                    '   </td>' +
                    '</tr>';
            }).join('');

            contenedor.innerHTML = '' +
                '<div class="table-responsive">' +
                '   <table class="table table-bordered table-hover tabla-aprobadores">' +
                '       <thead>' +
                '           <tr>' +
                '               <th class="text-center" style="width: 80px;">Orden</th>' +
                '               <th>Cargo</th>' +
                '               <th class="text-right" style="width: 140px;">Mover</th>' +
                '           </tr>' +
                '       </thead>' +
                '       <tbody>' + filas + '</tbody>' +
                '   </table>' +
                '</div>';
        }

        function moverCargoEnModal(grupoId, paso) {
            moverCargo(grupoId, paso);
            renderOrdenCargos();
        }

        function guardarOrdenCargos() {
            const sucursalIdActual = obtenerValorSucursalActual('crear');
            const empresaIdActual = obtenerValorEmpresaActual('crear');

            if (!sucursalIdActual || !empresaIdActual) {
                alertSwal('Seleccione empresa y sucursal antes de guardar el orden');
                return;
            }

            if (!ordenCargosSeleccionados.length) {
                alertSwal('No hay cargos disponibles para guardar el orden');
                return;
            }

            jsShowWindowLoad();
            xajax_guardar_orden_cargos(empresaIdActual, sucursalIdActual, JSON.stringify(ordenCargosSeleccionados));
        }

        function irACrearCargoDesdeAgregar() {
            const empresaAgregar = document.getElementById('empresaAgregarAprobador');
            const sucursalAgregar = document.getElementById('sucursalAgregarAprobador');
            const empresaCrear = document.getElementById('empresaCrearGrupo');
            const sucursalCrear = document.getElementById('sucursalCrearGrupo');

            if (empresaAgregar && empresaCrear) {
                empresaCrear.value = empresaAgregar.value;
            }

            if (sucursalAgregar && sucursalCrear) {
                sucursalCrear.value = sucursalAgregar.value;
            }

            renderListaCargos();
            renderOrdenCargos();
            $('.nav-tabs a[href="#tabCrearGrupo"]').tab('show');
        }

        function editarCargo(grupoId) {
            const grupo = gruposAprobadores.find(function(item) {
                return item.id === grupoId;
            });
            if (!grupo) {
                return;
            }

            grupoEnEdicion = grupo.id;

            const empresaCrear = document.getElementById('empresaCrearGrupo');
            const sucursalCrear = document.getElementById('sucursalCrearGrupo');
            if (empresaCrear) {
                empresaCrear.value = grupo.empresaId || '';
            }
            if (sucursalCrear) {
                sucursalCrear.value = grupo.sucursalId || '';
            }

            const campoNombre = document.getElementById('nuevoGrupoNombre');
            if (campoNombre) {
                campoNombre.value = grupo.nombre || '';
            }

            const botonGuardar = document.querySelector('.crear-grupo-btn');
            if (botonGuardar) {
                botonGuardar.innerHTML = '<i class="fa fa-save"></i> Actualizar cargo';
            }

            $('.nav-tabs a[href="#tabCrearGrupo"]').tab('show');
        }

        function eliminarCargo(grupoId) {
            const grupo = gruposAprobadores.find(function(item) {
                return item.id === grupoId;
            });
            if (!grupo) {
                return;
            }

            Swal.fire({
                title: '¿Eliminar cargo?',
                text: 'Se eliminará el cargo "' + (grupo.nombre || '') + '" y los aprobadores asociados.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_eliminar_cargo_aprobador(grupoId, grupo.empresaId || obtenerValorEmpresaActual('buscar'), grupo.sucursalId || obtenerValorSucursalActual('buscar'));
                }
            });
        }

        function restaurarCargo(grupoId) {
            const grupo = gruposAprobadores.find(function(item) {
                return item.id === grupoId;
            });
            if (!grupo) {
                return;
            }

            Swal.fire({
                title: '¿Restaurar cargo?',
                text: 'Se reactivará el cargo "' + (grupo.nombre || '') + '" y sus aprobadores asociados.',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Restaurar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_restaurar_cargo_aprobador(grupoId, grupo.empresaId || obtenerValorEmpresaActual('buscar'), grupo.sucursalId || obtenerValorSucursalActual('buscar'));
                }
            });
        }

        function actualizarFiltroCargo(grupoId) {
            filtroCargoId = grupoId || '';
            renderListaAprobadores();
        }

        function configurarSeccionAprobaciones(omitirCarga) {
            if (!Array.isArray(aprobadoresDisponibles)) {
                aprobadoresDisponibles = [];
            }

            registrarEventosAprobaciones();

            sincronizarSelectoresModal();

            const sucursalActual = obtenerSucursalActual('buscar');
            const sucursalIdActual = obtenerValorSucursalActual('buscar');
            const empresaIdActual = obtenerValorEmpresaActual('buscar');
            const empresaActual = obtenerEmpresaActual('buscar');

            const contextoCambio = contextoAprobaciones.empresaId !== empresaIdActual || contextoAprobaciones.sucursalId !== sucursalIdActual;
            if (contextoCambio) {
                aprobadoresSeleccionados = [];
                filtroAprobadores = '';
                filtroCargoId = '';
                restablecerFormularioCargo();
                const buscador = document.getElementById('busquedaAprobadores');
                if (buscador) {
                    buscador.value = '';
                }
                const filtroCargo = document.getElementById('filtroCargoAprobador');
                if (filtroCargo) {
                    filtroCargo.value = '';
                }
            }

            contextoAprobaciones = {
                empresaId: empresaIdActual,
                sucursalId: sucursalIdActual
            };

            if (!sucursalIdActual || !empresaIdActual) {
                const contenedor = document.getElementById('contenedorFirmasAprobadores');
                if (contenedor) {
                    contenedor.innerHTML = '<div class="alert alert-info">Seleccione la empresa y la sucursal para gestionar aprobaciones.</div>';
                }
                renderSelectorGrupos('grupoAprobador', 'agregar');
                return;
            }

            if (aprobacionesDesactivadas) {
                renderFirmasSeleccionadas();
                actualizarVisibilidadAprobaciones();
                return;
            }

            if (!omitirCarga) {
                jsShowWindowLoad();
                xajax_obtener_catalogo_aprobadores(empresaIdActual, sucursalIdActual);
                return;
            }

            aprobadoresSeleccionados = aprobadoresSeleccionados.filter(function(aprobador) {
                return aprobador.sucursalId === sucursalIdActual && aprobador.empresaId === empresaIdActual;
            });

            sincronizarSeleccionConDatos();

            const cargoElaboradoPor = obtenerCargoElaboradoPor(empresaIdActual, sucursalIdActual);
            const aprobadorSolicitante = cargoElaboradoPor ? obtenerAprobadorSolicitante() : null;
            if (aprobadorSolicitante && cargoElaboradoPor) {
                aprobadorSolicitante.sucursalId = sucursalIdActual;
                aprobadorSolicitante.grupoId = cargoElaboradoPor.id;
                aprobadorSolicitante.grupoNombre = cargoElaboradoPor.nombre;
                aprobadorSolicitante.cargo = cargoElaboradoPor.nombre;
                aprobadorSolicitante.empresaId = empresaIdActual;
                aprobadorSolicitante.empresa = empresaActual;
                const indiceSolicitante = aprobadoresDisponibles.findIndex(function(item) {
                    return item.id === 'SOLICITANTE';
                });
                if (indiceSolicitante === -1) {
                    aprobadoresDisponibles.unshift(aprobadorSolicitante);
                } else {
                    aprobadoresDisponibles[indiceSolicitante] = aprobadorSolicitante;
                }
            } else if (!cargoElaboradoPor && aprobadorSolicitante && !avisoCargoElaboradoPorMostrado) {
                alertSwal('Cree el cargo "Elaborado por" para asignar automáticamente al solicitante', 'info');
                aprobadoresDisponibles = aprobadoresDisponibles.filter(function(item) {
                    return item.id !== 'SOLICITANTE';
                });
                aprobadoresSeleccionados = aprobadoresSeleccionados.filter(function(item) {
                    return item.id !== 'SOLICITANTE';
                });
                avisoCargoElaboradoPorMostrado = true;
            }

            renderFiltroCargo();
            renderSelectorGrupos('grupoAprobador', 'agregar');
            renderListaAprobadores();
            renderFirmasSeleccionadas();
            renderListaCargos();
            renderOrdenCargos();
            actualizarVisibilidadAprobaciones();
        }

        function renderListaAprobadores() {
            const contenedor = document.getElementById('contenedorListaAprobadores');
            if (!contenedor) {
                return;
            }

            const sucursalIdActual = obtenerValorSucursalActual('buscar');
            const empresaIdActual = obtenerValorEmpresaActual('buscar');
            if (!sucursalIdActual || !empresaIdActual) {
                contenedor.innerHTML = '<div class="alert alert-info">Seleccione empresa y sucursal para mostrar aprobadores.</div>';
                return;
            }

            sincronizarOrdenCargos('buscar');
            const gruposSucursal = ordenarCargosPorPreferencia(gruposAprobadores.filter(function(item) {
                const coincideSucursal = item.sucursalId === sucursalIdActual;
                const coincideEmpresa = item.empresaId === empresaIdActual;
                const coincideFiltro = !filtroCargoId || filtroCargoId === item.id;
                const estaActivo = item.estado !== 'N';
                return coincideSucursal && coincideEmpresa && coincideFiltro && estaActivo;
            }));

            aprobadoresDisponibles = aprobadoresDisponibles.map(function(aprobador) {
                if (!aprobador.grupoNombre && aprobador.grupoId) {
                    aprobador.grupoNombre = obtenerNombreGrupo(aprobador.grupoId) || aprobador.cargo;
                }
                return aprobador;
            });

            if (!aprobadoresDisponibles.length || !gruposSucursal.length) {
                contenedor.innerHTML = '<div class="alert alert-info">No hay aprobadores registrados en esta sucursal. Cree un cargo y agregue usuarios.</div>';
                return;
            }

            const secciones = gruposSucursal.map(function(grupo) {
                const filas = aprobadoresDisponibles.filter(function(aprobador) {
                    if ((aprobador.sucursalId && aprobador.sucursalId !== sucursalIdActual) || (aprobador.empresaId && aprobador.empresaId !== empresaIdActual)) {
                        return false;
                    }
                    if (filtroAprobadores) {
                        const termino = filtroAprobadores.toLowerCase();
                        const coincide = (aprobador.nombre || '').toLowerCase().indexOf(termino) !== -1 ||
                            (aprobador.cargo || '').toLowerCase().indexOf(termino) !== -1 ||
                            (aprobador.grupoNombre || '').toLowerCase().indexOf(termino) !== -1;
                        if (!coincide) {
                            return false;
                        }
                    }
                    const grupoAprobador = aprobador.grupoId || grupo.id;
                    return grupoAprobador === grupo.id;
                }).map(function(aprobador) {
                    var checked = aprobadoresSeleccionados.some(function(item) {
                        return item.id === aprobador.id;
                    }) ? 'checked' : '';
                    var acciones = '<button type="button" class="btn btn-link btn-sm" onclick="iniciarEdicionAprobador(\'' + aprobador.id + '\');"><i class="fa fa-pencil"></i> Editar</button>';
                    if (aprobador.id !== 'SOLICITANTE') {
                        acciones += '<button type="button" class="btn btn-link btn-sm text-danger" onclick="eliminarAprobador(\'' + aprobador.id + '\');"><i class="fa fa-trash"></i> Eliminar</button>';
                    }
                    return '<tr>' +
                        '<td class="text-center"><input type="radio" name="aprobador-' + grupo.id + '" class="aprobador-check" data-id="' + aprobador.id + '" ' + checked + '></td>' +
                        '<td>' + (aprobador.nombre || '') + '</td>' +
                        '<td>' + (aprobador.grupoNombre || aprobador.cargo || '') + '</td>' +
                        '<td class="text-center" style="width:160px;">' + acciones + '</td>' +
                        '</tr>';
                }).join('');

                if (!filas) {
                    return '';
                }

                return '<div class="section-card section-card--secondary" style="margin-bottom:10px;">' +
                    '    <div class="section-card__header">' +
                    '        <h5 class="section-card__title"><i class="fa fa-users"></i> ' + grupo.nombre + '</h5>' +
                    '    </div>' +
                    '    <div class="section-card__body">' +
                    '        <table class="table table-bordered table-hover tabla-aprobadores">' +
                    '            <thead>' +
                    '                <tr>' +
                    '                    <th class="text-center" style="width: 60px;"><i class="fa fa-check-square-o"></i></th>' +
                    '                    <th>Nombre</th>' +
                    '                    <th>Cargo</th>' +
                    '                    <th class="text-center" style="width: 100px;">Acciones</th>' +
                    '                </tr>' +
                    '            </thead>' +
                    '            <tbody>' + filas + '</tbody>' +
                    '        </table>' +
                    '    </div>' +
                    '</div>';
            }).filter(function(contenido) {
                return contenido !== '';
            }).join('');

            contenedor.innerHTML = secciones || '<div class="alert alert-info">No hay aprobadores registrados en esta sucursal. Cree un cargo y agregue usuarios.</div>';
        }

        function actualizarFiltroAprobadores(valor) {
            filtroAprobadores = valor ? valor.toLowerCase() : '';
            renderListaAprobadores();
        }

        function restablecerFormularioAprobador() {
            aprobadorEnEdicion = '';
            const titulo = document.getElementById('tituloAgregarAprobador');
            const botonGuardar = document.getElementById('btnGuardarAprobador');
            if (titulo) {
                titulo.textContent = 'Nuevo aprobador';
            }
            if (botonGuardar) {
                botonGuardar.innerHTML = '<i class="fa fa-save"></i> Guardar aprobador';
            }
            const campoNombre = document.getElementById('nuevoAprobadorNombre');
            if (campoNombre) {
                campoNombre.value = '';
            }
        }

        function restablecerFormularioCargo() {
            grupoEnEdicion = '';
            const campoNombre = document.getElementById('nuevoGrupoNombre');
            const botonGuardar = document.querySelector('.crear-grupo-btn');
            if (campoNombre) {
                campoNombre.value = '';
            }
            if (botonGuardar) {
                botonGuardar.innerHTML = '<i class="fa fa-plus"></i> Guardar cargo';
            }
        }

        function toggleSeccionAprobaciones() {
            const cuerpo = document.getElementById('cuerpoSeccionAprobaciones');
            const icono = document.getElementById('iconoToggleAprobaciones');
            if (!cuerpo) {
                return;
            }

            const estaColapsado = cuerpo.classList.toggle('section-card__body--colapsado');
            cuerpo.style.display = estaColapsado ? 'none' : '';
            if (icono) {
                icono.className = estaColapsado ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
            }
        }

        function ajustarSeccionAprobacionesPorDatos(expandida) {
            const cuerpo = document.getElementById('cuerpoSeccionAprobaciones');
            const icono = document.getElementById('iconoToggleAprobaciones');
            if (!cuerpo) {
                return;
            }

            const debeColapsar = expandida === false;
            cuerpo.classList.toggle('section-card__body--colapsado', debeColapsar);
            cuerpo.style.display = debeColapsar ? 'none' : '';
            if (icono) {
                icono.className = debeColapsar ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
            }
        }

        function iniciarEdicionAprobador(id) {
            const aprobador = aprobadoresDisponibles.find(function(item) {
                return item.id === id;
            });
            if (!aprobador) {
                return;
            }

            aprobadorEnEdicion = id;

            const titulo = document.getElementById('tituloAgregarAprobador');
            const botonGuardar = document.getElementById('btnGuardarAprobador');
            if (titulo) {
                titulo.textContent = 'Editar aprobador';
            }
            if (botonGuardar) {
                botonGuardar.innerHTML = '<i class="fa fa-save"></i> Actualizar aprobador';
            }

            const empresaAgregar = document.getElementById('empresaAgregarAprobador');
            const sucursalAgregar = document.getElementById('sucursalAgregarAprobador');
            if (empresaAgregar) {
                empresaAgregar.value = aprobador.empresaId || '';
            }
            if (sucursalAgregar) {
                sucursalAgregar.value = aprobador.sucursalId || '';
            }
            renderSelectorGrupos('grupoAprobador', 'agregar');

            const selectorGrupo = document.getElementById('grupoAprobador');
            if (selectorGrupo) {
                selectorGrupo.value = aprobador.grupoId || '';
            }

            const campoNombre = document.getElementById('nuevoAprobadorNombre');
            if (campoNombre) {
                campoNombre.value = aprobador.nombre || '';
            }

            $('.nav-tabs a[href="#tabAgregarAprobador"]').tab('show');
        }

        function abrirModalAprobaciones() {
            const empresa = document.getElementById('empresa');
            const sucursal = document.getElementById('sucursal');
            if (!empresa || !empresa.value || !sucursal || !sucursal.value) {
                alertSwal('Seleccione empresa y sucursal antes de gestionar aprobaciones');
                return;
            }
            filtroAprobadores = '';
            restablecerFormularioAprobador();
            const campoBusqueda = document.getElementById('busquedaAprobadores');
            if (campoBusqueda) {
                campoBusqueda.value = '';
            }
            sincronizarSelectoresModal();
            configurarSeccionAprobaciones();
            $('.nav-tabs a[href="#tabBuscarAprobadores"]').tab('show');
            renderSelectorGrupos();
            renderListaAprobadores();
            $('#ModalGestionAprobaciones').modal('show');
        }

        $(function() {
            $('a[href="#tabCrearGrupo"]').on('shown.bs.tab', function() {
                renderListaCargos();
            });
        });

        function agregarAprobadorDesdeModal() {
            const campoNombre = document.getElementById('nuevoAprobadorNombre');
            const selectorGrupo = document.getElementById('grupoAprobador');

            const nombre = campoNombre ? campoNombre.value.trim() : '';
            const grupoId = selectorGrupo ? selectorGrupo.value : '';
            const sucursalId = obtenerValorSucursalActual('agregar');
            const empresaId = obtenerValorEmpresaActual('agregar');
            const sucursal = obtenerSucursalActual('agregar');
            const grupoNombre = obtenerNombreGrupo(grupoId);

            if (!nombre) {
                alertSwal('Ingrese el nombre del aprobador');
                return;
            }

            if (!grupoId) {
                alertSwal('Seleccione o cree un cargo para el aprobador');
                return;
            }

            if (existeCargoConNombre(nombre, empresaId, sucursalId, aprobadorEnEdicion)) {
                alertSwal('Ya existe un cargo con el mismo nombre en la sucursal seleccionada');
                return;
            }

            if (existeAprobadorConNombre(nombre, empresaId, sucursalId, aprobadorEnEdicion)) {
                alertSwal('Ya existe un aprobador con ese nombre en la sucursal seleccionada');
                return;
            }

            jsShowWindowLoad();
            xajax_guardar_aprobador_modal(empresaId, sucursalId, grupoId, nombre, aprobadorEnEdicion || 0);
        }

        function confirmarSeleccionAprobadores() {
            const seleccionados = Array.prototype.slice.call(document.querySelectorAll('.aprobador-check:checked'))
                .map(function(input) {
                    return input.getAttribute('data-id');
                });

            const sucursalIdActual = obtenerValorSucursalActual();
            const empresaIdActual = obtenerValorEmpresaActual();
            aprobadoresSeleccionados = seleccionados.map(function(idSeleccionado, indice) {
                const aprobador = aprobadoresDisponibles.find(function(item) {
                    const coincideSucursal = !item.sucursalId || item.sucursalId === sucursalIdActual;
                    const coincideEmpresa = !item.empresaId || item.empresaId === empresaIdActual;
                    return item.id === idSeleccionado && coincideSucursal && coincideEmpresa;
                });

                if (!aprobador) {
                    return null;
                }

                return Object.assign({}, aprobador, {
                    enviar: true,
                    orden: indice + 1
                });
            }).filter(Boolean);
            renderFirmasSeleccionadas();
            $('#ModalGestionAprobaciones').modal('hide');
        }

        function renderFirmasSeleccionadas() {
            const contenedor = document.getElementById('contenedorFirmasAprobadores');
            const campoOculto = document.getElementById('aprobadoresSeleccionadosCampo');
            const sucursalIdActual = obtenerValorSucursalActual();
            const empresaIdActual = obtenerValorEmpresaActual();
            const soloGuardados = estadoFormulario !== 'creando';
            if (!contenedor) {
                return;
            }

            if (aprobacionesDesactivadas) {
                contenedor.innerHTML = '<div class="alert alert-info">Aprobaciones desactivadas para esta solicitud.</div>';
                if (campoOculto) {
                    campoOculto.value = JSON.stringify([]);
                }
                return;
            }

            const aprobadoresOrdenados = (aprobadoresSeleccionados || []).slice().sort(function(a, b) {
                return (a.orden || Number.MAX_SAFE_INTEGER) - (b.orden || Number.MAX_SAFE_INTEGER);
            }).map(function(item, indice) {
                return Object.assign({}, item, {
                    nombre: formatearMayusculas(item.nombre),
                    grupoNombre: formatearMayusculas(item.grupoNombre || item.cargo),
                    cargo: formatearMayusculas(item.cargo || item.grupoNombre),
                    orden: item.orden || indice + 1
                });
            });

            if (soloGuardados) {
                if (!aprobadoresOrdenados.length) {
                    contenedor.innerHTML = '<div class="alert alert-info">No hay aprobadores registrados para este pedido.</div>';
                    if (campoOculto) {
                        campoOculto.value = JSON.stringify([]);
                    }
                    return;
                }

                const tarjetas = aprobadoresOrdenados.map(function(item) {
                    const rol = formatearMayusculas(item.grupoNombre || item.cargo || 'Aprobador');
                    const nombre = formatearMayusculas(item.nombre || '');
                    const envia = item.enviar === false ? 'No envía' : 'Envía';
                    return '<div class="firma-card firma-card--resumen">' +
                        '<h4><i class="fa fa-user"></i> ' + rol + '</h4>' +
                        '<p class="text-muted" style="margin:0;">' + nombre + '</p>' +
                        '<small class="text-muted">' + envia + '</small>' +
                        '</div>';
                }).join('');

                contenedor.innerHTML = '<div class="aprobadores-grid firmas-aprobadores">' + tarjetas + '</div>';
                if (campoOculto) {
                    campoOculto.value = JSON.stringify(aprobadoresOrdenados);
                }
                return;
            }

            sincronizarOrdenCargos('buscar');
            const cargos = obtenerCargosSucursalActual();
            const cargoElaboradoPor = obtenerCargoElaboradoPor(empresaIdActual, sucursalIdActual);

            actualizarOrdenAprobadoresDesdeCargos();

            aprobadoresSeleccionados = aprobadoresSeleccionados.filter(function(aprobador) {
                if (aprobador.id === 'SOLICITANTE') {
                    if (cargoElaboradoPor) {
                        aprobador.grupoId = cargoElaboradoPor.id;
                        aprobador.grupoNombre = cargoElaboradoPor.nombre;
                        aprobador.cargo = cargoElaboradoPor.nombre;
                        return true;
                    }
                    return false;
                }

                return cargos.some(function(cargo) {
                    return cargo.id === aprobador.grupoId;
                });
            });

            contenedor.innerHTML = '';

            if (!sucursalIdActual || !empresaIdActual) {
                contenedor.innerHTML = '<div class="alert alert-info">Seleccione la empresa y la sucursal antes de agregar aprobaciones.</div>';
                if (campoOculto) {
                    campoOculto.value = '';
                }
                return;
            }

            if (!cargos.length) {
                contenedor.innerHTML = '<div class="alert alert-info">No hay cargos de aprobadores registrados para la sucursal seleccionada.</div>';
                if (campoOculto) {
                    campoOculto.value = '';
                }
                return;
            }

            const contenedorTarjetas = document.createElement('div');
            contenedorTarjetas.className = 'aprobadores-grid firmas-aprobadores';

            cargos.forEach(function(cargo) {
                const aprobadoresCargo = obtenerAprobadoresPorCargo(cargo.id, sucursalIdActual, empresaIdActual);
                let seleccion = aprobadoresSeleccionados.find(function(item) {
                    return item.grupoId === cargo.id;
                });

                if (seleccion && typeof seleccion.enviar === 'undefined') {
                    seleccion.enviar = true;
                }

                const tarjeta = document.createElement('div');
                tarjeta.className = 'firma-card';
                tarjeta.draggable = true;
                tarjeta.dataset.grupoId = cargo.id;
                tarjeta.addEventListener('dragstart', iniciarArrastreCargo);
                tarjeta.addEventListener('dragover', permitirArrastreCargo);
                tarjeta.addEventListener('drop', soltarCargo);
                tarjeta.addEventListener('dragend', limpiarArrastreCargo);
                const opcionesPosicion = ordenCargosSeleccionados.map(function(id, indice) {
                    const seleccionado = id === cargo.id ? 'selected' : '';
                    return '<option value="' + indice + '" ' + seleccionado + '>Posición ' + (indice + 1) + '</option>';
                }).join('');
                const opcionPlaceholder = seleccion ? '' : 'selected';
                const opciones = '<option value="" ' + opcionPlaceholder + '>Seleccione aprobador</option>' +
                    aprobadoresCargo.map(function(aprobador) {
                        const seleccionado = seleccion && seleccion.id === aprobador.id ? 'selected' : '';
                        return '<option value="' + aprobador.id + '" ' + seleccionado + '>' + (aprobador.nombre || '') + '</option>';
                    }).join('');

                const descripcionGrupo = cargo.nombre || 'Aprobador';
                const checkEnvio = seleccion && seleccion.enviar === false ? '' : 'checked';
                const sinUsuarios = aprobadoresCargo.length === 0 ? '<p class="text-muted">Sin usuarios registrados para este cargo.</p>' : '';

                const selectorAprobadores = aprobadoresCargo.length ? '<select class="form-control" data-grupo="' + cargo.id + '" onchange="actualizarSeleccionAprobador(\'' + cargo.id + '\', this.value);">' + opciones + '</select>' : '';
                const selectorPosicion = '<select class="form-control input-sm" onchange="cambiarPosicionCargo(\'' + cargo.id + '\', parseInt(this.value, 10));">' + opcionesPosicion + '</select>';
                const estadoEnvio = checkEnvio;

                tarjeta.innerHTML = '' +
                    '<div class="firma-card__titulo">' +
                        '<h5><i class="fa fa-pencil"></i> ' + descripcionGrupo + '</h5>' +
                        '<label class="firma-card__envio">' +
                            '<input type="checkbox" data-grupo="' + cargo.id + '" onchange="actualizarEnvioAprobador(\'' + cargo.id + '\', this.checked);" ' + estadoEnvio + '> Incluir' +
                        '</label>' +
                    '</div>' +
                    (sinUsuarios ? sinUsuarios : '') +
                    (aprobadoresCargo.length ? '<div class="firma-card__fila-controles">' +
                        '<div class="form-group" style="margin-bottom:0; min-width: 220px;">' + selectorAprobadores + '</div>' +
                        '<div class="form-group" style="margin-bottom:0; min-width: 140px;">' + selectorPosicion + '</div>' +
                    '</div>' : '');

                contenedorTarjetas.appendChild(tarjeta);
            });

            contenedor.appendChild(contenedorTarjetas);
            establecerAprobadoresEnvio();
        }

        function establecerAprobadoresEnvio() {
            const campoOculto = document.getElementById('aprobadoresSeleccionadosCampo');
            if (!campoOculto) {
                return;
            }

            if (aprobacionesDesactivadas) {
                campoOculto.value = JSON.stringify([]);
                return;
            }

            const mapaOrden = {};
            ordenCargosSeleccionados.forEach(function(id, indice) {
                mapaOrden[id] = indice + 1;
            });

            const rolesRegistrados = [];
            const aprobadoresParaEnviar = aprobadoresSeleccionados.filter(function(item) {
                return item.enviar !== false;
            }).map(function(item, indice) {
                return Object.assign({}, item, {
                    nombre: formatearMayusculas(item.nombre),
                    grupoNombre: formatearMayusculas(item.grupoNombre || item.cargo),
                    cargo: formatearMayusculas(item.cargo || item.grupoNombre),
                    empresa: formatearMayusculas(item.empresa),
                    sucursal: formatearMayusculas(item.sucursal),
                    orden: mapaOrden[item.grupoId] || indice + 1
                });
            }).filter(function(item) {
                const claveRol = item.grupoId || item.cargo || item.grupoNombre;
                if (!claveRol) {
                    return true;
                }
                if (rolesRegistrados.indexOf(claveRol) !== -1) {
                    return false;
                }
                rolesRegistrados.push(claveRol);
                return true;
            }).sort(function(a, b) {
                return (a.orden || Number.MAX_SAFE_INTEGER) - (b.orden || Number.MAX_SAFE_INTEGER);
            });
            campoOculto.value = JSON.stringify(aprobadoresParaEnviar);
        }

        function actualizarSeleccionAprobador(grupoId, aprobadorId) {
            const sucursalIdActual = obtenerValorSucursalActual();
            const empresaIdActual = obtenerValorEmpresaActual();
            const aprobador = aprobadoresDisponibles.find(function(item) {
                return item.id === aprobadorId && item.grupoId === grupoId;
            });
            if (!aprobador) {
                return;
            }
            const existente = aprobadoresSeleccionados.findIndex(function(item) {
                return item.grupoId === grupoId;
            });
            const aprobadorConEnvio = Object.assign({}, aprobador, {
                enviar: existente >= 0 ? aprobadoresSeleccionados[existente].enviar !== false : true,
                sucursalId: sucursalIdActual,
                empresaId: empresaIdActual
            });
            if (existente >= 0) {
                aprobadoresSeleccionados[existente] = aprobadorConEnvio;
            } else {
                aprobadoresSeleccionados.push(aprobadorConEnvio);
            }
            renderFirmasSeleccionadas();
        }

        function actualizarEnvioAprobador(grupoId, enviar) {
            const indice = aprobadoresSeleccionados.findIndex(function(item) {
                return item.grupoId === grupoId;
            });
            if (indice >= 0) {
                aprobadoresSeleccionados[indice].enviar = enviar;
            }
            establecerAprobadoresEnvio();
        }

        function eliminarAprobador(id) {
            const aprobador = aprobadoresDisponibles.find(function(item) {
                return item.id === id;
            });

            if (!aprobador || id === 'SOLICITANTE') {
                return;
            }

            Swal.fire({
                title: '¿Eliminar aprobador?',
                text: 'Se eliminará el aprobador "' + (aprobador.nombre || '') + '" de la sucursal seleccionada.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    const empresaId = aprobador.empresaId || obtenerValorEmpresaActual('buscar');
                    const sucursalId = aprobador.sucursalId || obtenerValorSucursalActual('buscar');
                    jsShowWindowLoad();
                    xajax_eliminar_aprobador_modal(id, empresaId, sucursalId);
                }
            });
        }

        function crearGrupoAprobador() {
            const nombreGrupo = document.getElementById('nuevoGrupoNombre');
            const sucursalId = obtenerValorSucursalActual('crear');
            const sucursalNombre = obtenerSucursalActual('crear');
            const empresaId = obtenerValorEmpresaActual('crear');
            const empresaNombre = obtenerEmpresaActual('crear');

            if (!empresaId || !sucursalId) {
                alertSwal('Seleccione empresa y sucursal antes de crear cargos');
                return;
            }

            const nombre = nombreGrupo ? nombreGrupo.value.trim() : '';
            if (!nombre) {
                alertSwal('Ingrese el nombre del cargo');
                return;
            }

            if (existeCargoConNombre(nombre, empresaId, sucursalId, grupoEnEdicion)) {
                alertSwal('Ya existe un cargo con el mismo nombre en la sucursal seleccionada');
                return;
            }

            if (existeAprobadorConNombre(nombre, empresaId, sucursalId)) {
                alertSwal('No puede registrar un cargo con el mismo nombre de un aprobador en esta sucursal');
                return;
            }

            jsShowWindowLoad();
            xajax_guardar_cargo_aprobador(empresaId, sucursalId, nombre, grupoEnEdicion || 0);
            restablecerFormularioCargo();
        }


        function imprimirPedidoActual() {
            const nota = document.getElementById('nota_compra');
            if (!nota || !nota.value) {
                alertSwal('Por favor ingrese el Pedido para generar vista previa', 'warning');
                return;
            }

            // Usamos el mismo mecanismo que el reporte, que ya funciona bien
            vista_previa_reporte(nota.value);
        }


        function editar_pedido() {
            const nota = document.getElementById('nota_compra');
            const empresa = document.getElementById('empresa');
            const sucursal = document.getElementById('sucursal');

            // Caso 2: estoy parado sobre un pedido ya creado (tarjeta amarilla)
            if (
                typeof estadoFormulario !== 'undefined' &&
                estadoFormulario === 'creada' &&
                nota && nota.value &&
                empresa && empresa.value &&
                sucursal && sucursal.value
            ) {
                // Mismo flujo que el botón "Editar" del modal, pero sin modal
                setEstadoPendiente('editando');
                // IMPORTANTE: aquí NO llamamos jsShowWindowLoad()
                xajax_carga_pedido(nota.value, empresa.value, sucursal.value);
                return;
            }

            // Caso 1: estoy en "Nuevo" (azul) u otro estado sin pedido cargado → abrir listado
            abrirModalPedidos();
        }



        function buscar_anulado() {
            $("#ModalAnulados").modal("show");
            xajax_lista_pedidos_anulados();
        }



        function genera_formulario() {
            setEstadoPendiente('creando');
            xajax_genera_formulario_pedido('nuevo', '', <?= $codigo_pedido ?>, <?= $empr_pedido ?>, <?= $sucu_pedido ?>, <?= $idReq ?>);
        }

        function cargar_sucursal() {
            xajax_genera_formulario_pedido('sucursal', xajax.getFormValues("form1"), <?= $codigo_pedido ?>, <?= $empr_pedido ?>, <?= $sucu_pedido ?>, <?= $idReq ?>);
        }

        function cargar_bodega() {
            xajax_genera_formulario_pedido('bodega', xajax.getFormValues("form1"), <?= $codigo_pedido ?>, <?= $empr_pedido ?>, <?= $sucu_pedido ?>, <?= $idReq ?>);
        }

        function autocompletar(empresa, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var cliente_nom = document.getElementById('cliente_nombre').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../pedido_compra_v3/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }

        function autocompletar_atajo() {
            var cliente_nom = document.getElementById('cliente_nombre').value;
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../pedido_compra_v3/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa;
            window.open(pagina, "", opciones);
        }


        function guardar_pedido(id_op) {
            establecerAprobadoresEnvio();
            if (ProcesarFormulario() == true) {
                var prioridad = document.getElementById("pedi_pri_pedi");
                if (!prioridad || prioridad.value === '') {
                    alertSwal('Seleccione la prioridad del pedido.', 'warning');
                    return;
                }
                const payloadPedido = xajax.getFormValues("form1");
                console.log('Enviando datos del pedido', payloadPedido);
                var ctrl = document.getElementById("ctrl").value;
                if (ctrl == 1) {

                    var cod = document.getElementById("nota_compra").value;

                    if (cod == '') {
                        document.getElementById("ctrl").value = 2;

                        var area = document.getElementById("area").value;

                        if (area == '') {
                            alertSwal('Configure el Area', 'warning');
                        } else {
                            jsShowWindowLoad();
                            xajax_guarda_pedido(id_op, xajax.getFormValues("form1"), <?= $idReq ?>);
                        }

                    } else {
                        jsShowWindowLoad();
                        xajax_actualiza_pedido(cod, xajax.getFormValues("form1"));

                    }


                } else {
                    var codigo = document.getElementById("nota_compra").value;
                    var cont = codigo.length;
                    if (cont > 0) {
                        alertSwal('!!!!....Error el Pedido de Compra ya esta Ingresado....!!!!!...', 'warning');
                    } else {
                        alertSwal('Por favor espere Guardando Informacion...', 'info');
                    } // fin if
                }
            }
        }


        function cancelar_pedido() {
            confirmar = confirm("¿Deseas Guardar los cambios?");
            if (confirmar) {
                guardar_pedido();
            } else {
                genera_formulario();
            }
        }

        function cancelar_actualizacion() {
            confirmar = confirm("¿Deseas Salir del Pedido?");
            if (confirmar) {
                genera_formulario();
            } else {
                cargar_datos_pedido();
            }
        }

        function cerrar_ventana() {
            CloseAjaxWin();
        }

        function anadir_elemento_comun(x, i, elemento) {
            var lista = document.form1.placa;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function esProductoNoRegistrado() {
            const checkbox = document.getElementById('producto_no_registrado');
            return checkbox ? checkbox.checked : false;
        }

        function moverElemento(elemento, destino) {
            if (elemento && destino) {
                destino.appendChild(elemento);
            }
        }

        function actualizarInfoBodegaNoRegistrado() {
            const infoBodega = document.getElementById('infoBodegaNoRegistrado');
            const selectBodega = document.getElementById('bodega');

            if (infoBodega) {
                const textoBodega = selectBodega && selectBodega.options && selectBodega.selectedIndex >= 0
                    ? selectBodega.options[selectBodega.selectedIndex].text
                    : '';
                infoBodega.textContent = textoBodega ? 'Bodega seleccionada: ' + textoBodega : 'Seleccione una bodega para el producto';
            }
        }

        function actualizarVisibilidadSlotsArchivo() {
            const slotArchivoPrincipal = document.getElementById('slotArchivoPrincipal');
            const slotArchivoDetalle = document.getElementById('slotArchivoDetalle');

            if (slotArchivoPrincipal) {
                slotArchivoPrincipal.classList.toggle('slot-archivo-vacio', !slotArchivoPrincipal.children.length);
            }

            if (slotArchivoDetalle) {
                slotArchivoDetalle.classList.toggle('slot-archivo-vacio', !slotArchivoDetalle.children.length);
            }
        }

        function limpiarCamposProductoNoRegistrado() {
            const codigoAuxiliar = document.getElementById('codigo_auxiliar');
            const descripcionAuxiliar = document.getElementById('descripcion_auxiliar');
            if (codigoAuxiliar) {
                codigoAuxiliar.value = '';
            }
            if (descripcionAuxiliar) {
                descripcionAuxiliar.value = '';
            }
            actualizarVisibilidadSlotsArchivo();
        }

        function toggleProductoNoRegistrado(activado) {
            const producto = document.getElementById('producto');
            const codigoProducto = document.getElementById('codigo_producto');
            const botonBuscar = document.getElementById('botonBuscarProducto');
            const filaProductoRegistrado = document.getElementById('filaProductoRegistrado');
            const filaProductoNoRegistrado = document.getElementById('filaProductoNoRegistrado');
            const contenedorProducto = document.getElementById('camposProductoEstandar');
            const slotCantidadRegistrado = document.getElementById('slotCantidadRegistrado');
            const slotCantidadNoRegistrado = document.getElementById('slotCantidadNoRegistrado');
            const contenedorCantidad = document.getElementById('contenedorCantidad');
            const contenedorUnidad = document.getElementById('contenedorUnidad');
            const slotUnidadRegistrado = document.getElementById('slotUnidadRegistrado');
            const slotArchivoPrincipal = document.getElementById('slotArchivoPrincipal');
            const slotArchivoDetalle = document.getElementById('slotArchivoDetalle');
            const contenedorArchivo = document.getElementById('wrapperArchivo');
            const columnaBodega = document.getElementById('columnaBodega');
            const contenedorCamposNoRegistrado = document.getElementById('camposProductoNoRegistrado');
            const codigoAuxiliar = document.getElementById('codigo_auxiliar');
            const infoBodega = document.getElementById('infoBodegaNoRegistrado');
            actualizarInfoBodegaNoRegistrado();
            if (infoBodega) {
                infoBodega.style.display = activado ? '' : 'none';
            }

            if (activado) {
                if (producto) {
                    // Solo bloqueamos el campo, no ponemos código fijo
                    producto.value = '';
                    producto.readOnly = true;
                }
                if (codigoProducto) {
                    codigoProducto.value = '';
                    codigoProducto.readOnly = false;
                    codigoProducto.classList.remove('solo-lectura');
                }
                if (botonBuscar) {
                    botonBuscar.disabled = true;
                    botonBuscar.classList.add('disabled');
                }
                if (filaProductoRegistrado) {
                    filaProductoRegistrado.style.display = 'none';
                }
                if (filaProductoNoRegistrado) {
                    filaProductoNoRegistrado.style.display = '';
                }
                if (contenedorProducto) {
                    contenedorProducto.style.display = 'none';
                }
                if (slotCantidadNoRegistrado) {
                    moverElemento(contenedorCantidad, slotCantidadNoRegistrado);
                }
                if (slotArchivoDetalle) {
                    moverElemento(contenedorArchivo, slotArchivoDetalle);
                }
                if (columnaBodega) {
                    columnaBodega.style.display = '';
                }
                if (contenedorCamposNoRegistrado) {
                    contenedorCamposNoRegistrado.style.display = '';
                }
                if (codigoAuxiliar) {
                    codigoAuxiliar.readOnly = false;
                }
                if (slotUnidadRegistrado && contenedorUnidad) {
                    moverElemento(contenedorUnidad, slotUnidadRegistrado);
                }
                generarCodigoAuxiliarAuto();
            } else {
                if (producto) {
                    producto.value = '';
                    producto.readOnly = false;
                }
                if (codigoProducto) {
                    codigoProducto.value = '';
                    codigoProducto.readOnly = true;
                    codigoProducto.classList.add('solo-lectura');
                }
                if (botonBuscar) {
                    botonBuscar.disabled = false;
                    botonBuscar.classList.remove('disabled');
                }
                limpiarCamposProductoNoRegistrado();
                if (contenedorProducto) {
                    contenedorProducto.style.display = '';
                }
                if (filaProductoRegistrado) {
                    filaProductoRegistrado.style.display = '';
                }
                if (filaProductoNoRegistrado) {
                    filaProductoNoRegistrado.style.display = 'none';
                }
                if (slotCantidadRegistrado) {
                    moverElemento(contenedorCantidad, slotCantidadRegistrado);
                }
                if (slotArchivoPrincipal) {
                    moverElemento(contenedorArchivo, slotArchivoPrincipal);
                }
                if (columnaBodega) {
                    columnaBodega.style.display = '';
                }
                if (contenedorCamposNoRegistrado) {
                    contenedorCamposNoRegistrado.style.display = 'none';
                }
                if (codigoAuxiliar) {
                    codigoAuxiliar.readOnly = false;
                }
                if (slotUnidadRegistrado && contenedorUnidad) {
                    moverElemento(contenedorUnidad, slotUnidadRegistrado);
                }
            }

            actualizarVisibilidadSlotsArchivo();

        }

        function generarCodigoAuxiliarAuto() {
            if (!esProductoNoRegistrado()) {
                return;
            }

            const empresa = document.getElementById('empresa');
            const sucursal = document.getElementById('sucursal');
            if (empresa && sucursal && empresa.value && sucursal.value) {
                xajax_generar_codigo_auxiliar(xajax.getFormValues("form1"));
            }
        }

        function ajustarCampoTipo() {
            const tipo = document.getElementById('tipo');
            if (!tipo || !tipo.options || !tipo.options.length) {
                return;
            }
            for (let i = tipo.options.length - 1; i >= 0; i--) {
                const opcion = tipo.options[i];
                const valor = (opcion.value || '').toLowerCase();
                const texto = (opcion.text || '').toLowerCase();
                if (valor === '' || texto === 'seleccionar') {
                    tipo.remove(i);
                }
            }
            if (tipo.options.length > 0) {
                tipo.selectedIndex = 0;
            }
        }

        function autocompletar_producto(event, para) {
            if (esProductoNoRegistrado()) {
                return;
            }
            var bodega = document.getElementById("bodega").value;
            var sucursal = document.getElementById("sucursal").value;
            var precio = 1;
            var empresa = document.getElementById('empresa').value;
            var cod_nom = document.getElementById('codigo_producto').value;

            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                mostrarModalProductos();
            }
        }

        function autocompletar_producto_atajo() {
            if (esProductoNoRegistrado()) {
                return;
            }
            var bodega = document.getElementById("bodega").value;
            var sucursal = document.getElementById("sucursal").value;
            var precio = 1;
            if (bodega != '' && sucursal != '' && precio != '') {
                var producto = trim(document.getElementById('producto').value);
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1000, height=390, top=200, left=130";
                var pagina = '../pedido_compra_v3/view/_buscar_productos.php?sesionId=<?= session_id() ?>&sucursal=' + sucursal + '&bodega=' + bodega + '&producto=' + encodeURIComponent(producto) + '&precio=' + precio;
                // window.open(pagina, "", opciones);
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', pagina, 'DetalleShow', 'iframe', 'Listado Productos', '980', '500', '0', '0', '0', '0');
                document.getElementById('producto').value = '';
            } else {
                alertSwal('Ingrese Cliente - Bodega - Tipo de Precio', 'warning');
            }
        }

        /*function cargar_producto() {
            var precio = document.getElementById("precio").value;
            xajax_agrega_modifica_grid(0, precio, 0, xajax.getFormValues("form1"));
            //limpiar_prod();
        }*/

        function actualizar_presupuesto() {
            xajax_actualizar_presupuesto(xajax.getFormValues("form1"));
        }

        function limpiar_prod() {
            foco('producto');
            document.getElementById("producto").value = '';
            document.getElementById("cantidad").value = 1;
            document.getElementById("costo").value = 0;
            document.getElementById("codigo_producto").value = '';
            document.getElementById("detalle").value = '';
            document.getElementById("archivo").value = '';
            const unidad = document.getElementById('unidad');
            if (unidad) {
                unidad.selectedIndex = 0;
            }
            const productoNoRegistrado = document.getElementById('producto_no_registrado');
            if (productoNoRegistrado && productoNoRegistrado.checked) {
                productoNoRegistrado.checked = false;
            }
            limpiarCamposProductoNoRegistrado();
            toggleProductoNoRegistrado(false);
        }

        function foco(idElemento) {
            document.getElementById(idElemento).focus();
        }

        function cargar_producto() {
            var prod = document.getElementById("producto").value;
            var cant = parseFloat(document.getElementById("cantidad").value);
            var cod = document.getElementById("codigo_producto").value;

            var codigoAuxiliar = document.getElementById("codigo_auxiliar");
            var descripcionAuxiliar = document.getElementById("descripcion_auxiliar");
            var productoNoRegistrado = esProductoNoRegistrado();

            // FLUJO: PRODUCTO NO REGISTRADO
            if (productoNoRegistrado) {
                var codigoAux = codigoAuxiliar ? codigoAuxiliar.value.trim() : '';
                var descripcionAux = descripcionAuxiliar ? descripcionAuxiliar.value.trim() : '';

                if (!codigoAux || !descripcionAux) {
                    alertSwal('Ingrese código auxiliar y descripción para el producto no registrado',
                        'warning');
                    foco(!codigoAux ? 'codigo_auxiliar' : 'descripcion_auxiliar');
                    return;
                }

                // Si el detalle está vacío, usamos la descripción auxiliar
                var detalle = document.getElementById('detalle');
                if (detalle && !detalle.value.trim()) {
                    detalle.value = descripcionAux;
                }

                // Validar cantidad
                if (cant <= 0 || isNaN(cant)) {
                    alertSwal('La cantidad tiene que ser mayor a 0', 'warning');
                    foco('cantidad');
                    return;
                }

                // IMPORTANTE: aquí YA NO validamos prod/cod.
                // El backend tomará bodega y producto desde comercial.parametro_inv.
                jsShowWindowLoad();
                xajax_agrega_modifica_grid(0, 0, '', xajax.getFormValues("form1"));
                return;
            }

            // FLUJO NORMAL (producto registrado)
            if (prod == '') {
                alertSwal('Ingrese un producto', 'warning');
                foco('producto');
            } else if (cod == '') {
                alertSwal('Ingrese un producto', 'warning');
                foco('producto');
            } else if (cant <= 0 || isNaN(cant)) {
                alertSwal('La cantidad tiene que ser mayor a 0', 'warning');
                foco('cantidad');
            } else {
                jsShowWindowLoad();
                xajax_agrega_modifica_grid(0, 0, '', xajax.getFormValues("form1"));
            }
        }


        function cargar_update_cant(id) {
            var a = document.getElementById(id + "_cantidad").value;
            xajax_actualiza_grid(id, xajax.getFormValues("form1"));
        }

        function cargar_update_grid(id, producto, cantidad, bode, costo, ccos, detalle, unidad) {
            xajax_agrega_modifica_grid(1, 0, producto, xajax.getFormValues("form1"), id, cantidad, bode, costo, ccos, detalle, unidad);
        }

        function cargar_grid() {
            xajax_cargar_grid(0, xajax.getFormValues("form1"));
        }

        function totales_comp() {
            xajax_total_grid(xajax.getFormValues("form1"));
        }

        function cargar_prove() {
            var cliente_nom = document.getElementById('cliente_nombre').value;
            var empresa = document.getElementById('empresa').value;
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../pedido_compra_v3/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa;
            window.open(pagina, "", opciones);
        }

        function cargar_prod_nom(op) {
            if (esProductoNoRegistrado()) {
                return;
            }
            var prod_nom = document.getElementById('producto').value;
            var cod_nom = document.getElementById('codigo_producto').value;
            var sucu = document.getElementById('sucursal').value;
            var bode = document.getElementById('bodega').value;
            var empresa = document.getElementById('empresa').value;
            //var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            //var pagina = '../pedido_compra_v3/buscar_prod.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&producto=' + prod_nom + '&codigo=' + cod_nom + '&opcion=' + op + '&sucursal=' + sucu + '&bodega=' + bode + '&empresa=' + empresa;
            //window.open(pagina, "", opciones);
            if (op == 1) {
                document.getElementById('codigo_producto').value = '';
            } else {
                document.getElementById('producto').value = '';
            }
            mostrarModalProductos();
        }

        function centro_costo_22(id, event) {
            if (event.keyCode == 115) { // F4
                var cod = document.getElementById(id).value;
                var empresa = document.getElementById('empresa').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../pedido_compra_v3/centro_costo.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&id=' + id + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }

        function centro_costo_22_btn(id) {
            var cod = document.getElementById(id).value;
            var empresa = document.getElementById('empresa').value;
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../pedido_compra_v3/centro_costo.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&id=' + id + '&empresa=' + empresa;
            window.open(pagina, "", opciones);
        }

        function cargar_prod_grid(event) {
            if (event.keyCode == 13) { // F4
                cargar_producto();
            }
        }

        function intentarAgregarProductoConEnter(event) {
            if (event.key !== 'Enter') {
                return;
            }

            const productoNoRegistrado = esProductoNoRegistrado();
            const codigo = document.getElementById('codigo_producto');
            const nombre = document.getElementById('producto');

            if (productoNoRegistrado) {
                return;
            }

            if (codigo && nombre && codigo.value.trim() && nombre.value.trim()) {
                event.preventDefault();
                cargar_producto();
            }
        }

        function inicializarAccesosRapidosProducto(intentos) {
            const reintento = typeof intentos === 'number' ? intentos : 0;
            const campos = [document.getElementById('codigo_producto'), document.getElementById('producto')];
            const disponibles = campos.filter(function(campo) { return campo; });

            if (!disponibles.length && reintento < 5) {
                setTimeout(function() { inicializarAccesosRapidosProducto(reintento + 1); }, 300);
                return;
            }

            disponibles.forEach(function(campo) {
                campo.removeEventListener('keydown', intentarAgregarProductoConEnter);
                campo.addEventListener('keydown', intentarAgregarProductoConEnter);
            });

            window.removeEventListener('keydown', atajosModalProductos);
            window.addEventListener('keydown', atajosModalProductos);
        }

        function atajosModalProductos(event) {
            if (estadoFormulario !== 'creando') {
                return;
            }

            const modalProductos = $('#ModalProd');

            if (event.key === 'F4' || event.keyCode === 115) {
                event.preventDefault();
                mostrarModalProductos();
            }

            if (event.key === 'Escape' && modalProductos.length && modalProductos.hasClass('in')) {
                event.preventDefault();
                modalProductos.modal('hide');
            }
        }

        function mostrarModalProductos() {
            if (esProductoNoRegistrado()) {
                return;
            }

            var bodega = document.getElementById("bodega");
            var sucursal = document.getElementById("sucursal");
            var precio = 1;

            if (bodega && sucursal && bodega.value !== '' && sucursal.value !== '' && precio !== '') {
                $("#ModalProd").modal("show");
                xajax_buscar_productos(xajax.getFormValues("form1"));
            } else {
                alertSwal('Ingrese Cliente - Bodega - Tipo de Precio', 'warning');
            }
        }

        function convertirCampoATextarea(id, filas) {
            const campo = document.getElementById(id);
            if (!campo) {
                return;
            }

            if (campo.tagName.toLowerCase() === 'textarea') {
                campo.rows = filas;
                return;
            }

            const area = document.createElement('textarea');
            area.id = campo.id;
            area.name = campo.name || campo.id;
            area.className = campo.className || 'form-control';
            area.rows = filas;
            area.value = campo.value || '';

            campo.parentNode.replaceChild(area, campo);
        }

        function manejarTeclasFormulario(event) {
            if (event.key === 'Enter' || event.keyCode === 13) {
                const target = event.target || {};
                const tagName = (target.tagName || '').toLowerCase();
                const permiteEnter = tagName === 'textarea' || target.dataset && target.dataset.allowEnter === 'true';

                if (!permiteEnter) {
                    event.preventDefault();
                    return false;
                }
            }

            return true;
        }

        function configurarCamposMultilinea() {
            convertirCampoATextarea('motivo', 4);
            convertirCampoATextarea('observaciones', 4);
            convertirCampoATextarea('detalle', 3);
        }

        function configurarCamposProducto() {
            const codigoProducto = document.getElementById('codigo_producto');
            const nombreProducto = document.getElementById('producto');
            if (codigoProducto) {
                codigoProducto.readOnly = true;
                codigoProducto.classList.add('solo-lectura');
                codigoProducto.addEventListener('blur', solicitarUnidadProductoPorDefecto);
            }

            if (nombreProducto) {
                nombreProducto.addEventListener('input', function() {
                    if (!esProductoNoRegistrado() && codigoProducto) {
                        codigoProducto.value = '';
                    }
                });
            }

            const bodegaSelect = document.getElementById('bodega');
            if (bodegaSelect) {
                bodegaSelect.addEventListener('change', actualizarInfoBodegaNoRegistrado);
                bodegaSelect.addEventListener('change', solicitarUnidadProductoPorDefecto);
            }

            actualizarInfoBodegaNoRegistrado();
        }

        function solicitarUnidadProductoPorDefecto() {
            if (esProductoNoRegistrado()) {
                return;
            }

            const codigoProducto = document.getElementById('codigo_producto');
            const empresa = document.getElementById('empresa');
            const sucursal = document.getElementById('sucursal');
            const bodega = document.getElementById('bodega');

            if (!codigoProducto || !empresa || !sucursal || !bodega) {
                return;
            }

            if (!codigoProducto.value || !empresa.value || !sucursal.value || !bodega.value) {
                return;
            }

            xajax_obtener_unidad_producto(codigoProducto.value, empresa.value, sucursal.value, bodega.value);
        }

        function seleccionarUnidadProducto(unidadId) {
            const unidadSelect = document.getElementById('unidad');
            if (unidadSelect && unidadId) {
                unidadSelect.value = unidadId;
            }
        }

        function inicializarEditorDetalleProductos() {
            document.addEventListener('dblclick', function(event) {
                const contenedorDetalle = buscarContenedorDetalle(event.target);
                if (contenedorDetalle) {
                    mostrarEditorDetalle(contenedorDetalle);
                }
            });
        }

        function buscarContenedorDetalle(elemento) {
            if (!elemento) {
                return null;
            }

            const contenedorDirecto = elemento.closest('.detalle-texto-grid');
            if (contenedorDirecto && contenedorDirecto.closest('#divFormularioDetalle')) {
                return contenedorDirecto;
            }

            const celdaDetalle = elemento.closest('#divFormularioDetalle td');
            if (celdaDetalle) {
                const contenedorCelda = celdaDetalle.querySelector('.detalle-texto-grid');
                if (contenedorCelda) {
                    return contenedorCelda;
                }
            }

            return null;
        }

        function mostrarEditorDetalle(contenedor) {
            const celda = contenedor.closest('td');
            if (!celda || celda.querySelector('.detalle-editor-inline')) {
                return;
            }

            const campoOculto = celda.querySelector('input[type="hidden"][id$="_det"]');
            const detallePlano = campoOculto ? campoOculto.value : contenedor.textContent;

            const editor = document.createElement('textarea');
            editor.className = 'form-control detalle-editor-inline';
            editor.value = detallePlano.replace(/\r?\n/g, '\n');

            contenedor.style.display = 'none';
            celda.appendChild(editor);
            editor.focus();

            editor.addEventListener('blur', function() {
                guardarDetalleEditado(editor, contenedor, campoOculto);
            });

            editor.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    editor.blur();
                }
            });
        }

        function guardarDetalleEditado(editor, contenedor, campoOculto) {
            const nuevoDetalle = editor.value || '';

            if (campoOculto) {
                campoOculto.value = nuevoDetalle;
            }

            contenedor.innerHTML = convertirTextoADisplaySeguro(nuevoDetalle);
            contenedor.style.display = '';
            editor.remove();
        }

        function convertirTextoADisplaySeguro(texto) {
            if (texto === undefined || texto === null) {
                return '';
            }

            const escapado = texto
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            return escapado.replace(/\n/g, '<br>');
        }

        function cargar_portafolio(empresa, sucursal) {
            var cliente = document.getElementById("cliente").value;
            if (cliente != '') {
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../recepcion_compra/portafolio.php?sesionId=<?= session_id() ?>&mOp=false&mVer=false&cliente=' + cliente + '&empresa=' + empresa + '&sucursal=' + sucursal, 'DetalleShow', 'iframe', 'Portafolio-Productos', '980', '500', '0', '0', '0', '0');
            } else {
                alertSwal('Ingrese Cliente para generar Portafolio', 'warning');
            }
        }


        function vista_previa() {
            var secu = document.getElementById('nota_compra') ?
                document.getElementById('nota_compra').value :
                '';

            if (!secu) {
                alertSwal('Por favor ingrese el Pedido para generar vista previa', 'warning');
                return;
            }

            // Obtenemos los valores del form
            var form = xajax.getFormValues("form1") || {};

            // Si empresa/sucursal NO vienen porque están disabled, los rellenamos a mano
            var empresaEl = document.getElementById('empresa');
            var sucursalEl = document.getElementById('sucursal');

            if (empresaEl && !form.empresa) {
                form.empresa = empresaEl.value;
            }
            if (sucursalEl && !form.sucursal) {
                form.sucursal = sucursalEl.value;
            }

            // Llamamos igual que antes, pero con empresa/sucursal garantizados
            xajax_genera_pdf_doc(form);
        }

        function vista_previa_reporte(id) {
            // Obtenemos los valores del form
            var form = xajax.getFormValues("form1") || {};

            var empresaEl = document.getElementById('empresa');
            var sucursalEl = document.getElementById('sucursal');

            if (empresaEl && !form.empresa) {
                form.empresa = empresaEl.value;
            }
            if (sucursalEl && !form.sucursal) {
                form.sucursal = sucursalEl.value;
            }

            xajax_genera_pdf_doc_reporte(id, form);
        }


        function vista_previa_reporte(id) {
            xajax_genera_pdf_doc_reporte(id, xajax.getFormValues("form1"));
        }


        function generar_pdf() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
            var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }

        function generar_pdf_compras() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
            var pagina = '../../Include/documento_pdf_compras.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }


        function generar_pdf_inv(secu) {
            if (ProcesarFormulario() == true) {
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
                var pagina = '../../Include/documento_pdf_inv.php?sesionId=<?= session_id() ?>&secu=' + secu;
                window.open(pagina, "", opciones);

            }
        }

function init(tableId) {
    // Si ya existe el DataTable, lo destruimos primero
    if ($.fn.DataTable.isDataTable('#' + tableId)) {
        $('#' + tableId).DataTable().destroy();
    }

    var table = $('#' + tableId).DataTable({
        scrollY: '60vh',
        scrollX: true,
        scrollCollapse: true,

        //Paginación
        paging: true,

        //Cantidad inicial
        pageLength: 10,

        //Opciones de cantidad a mostrar
        lengthChange: true, // (por si acaso)
        lengthMenu: [
            [10, 50, 200, -1],       // valores
            [10, 50, 200, 'Todos']   // etiquetas visibles
        ],

        //IMPORTANTE: aquí se muestra el selector "Mostrar _MENU_"
        dom:
            "<'row'<'col-sm-4'l><'col-sm-4'B><'col-sm-4'f>>" +  // l = length, B = botones, f = filtro
            "<'row'<'col-sm-12'tr>>" +                         // tabla
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",               // info + paginación

        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                orientation: 'landscape',
                pageSize: 'A4'
            }
        ],

        processing: true,
        language: {
            processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color:#34495e;'></i>",
            search: "<i class='fa fa-search'></i>",
            searchPlaceholder: "Buscar",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron datos",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                previous: "Anterior",
                next: "Siguiente"
            }
        },

        ordering: true,
        info: true
    });

    return table;
}

function initTabla(tableId) {
    var table = init(tableId);

    if (tableId === 'listprod') {
        configurarBuscadorProductosModal(table);
        prepararSeleccionFilasProductos();
    }

    return table;
}

function configurarBuscadorProductosModal(tableInstance) {
    var buscador = document.getElementById('buscadorProductosModal');
    if (!buscador || !tableInstance) {
        return;
    }

    if (buscador.dataset.listenerAttached === 'true') {
        return;
    }

    buscador.addEventListener('input', function() {
        tableInstance.search(this.value).draw();
    });
    buscador.dataset.listenerAttached = 'true';
}

function prepararSeleccionFilasProductos() {
    var cuerpoTabla = document.querySelector('#listprod tbody');
    if (!cuerpoTabla || cuerpoTabla.dataset.listenerAttached === 'true') {
        return;
    }

    cuerpoTabla.addEventListener('click', function(event) {
        var fila = event.target.closest('tr');
        if (!fila) {
            return;
        }

        var checkbox = fila.querySelector('.producto-seleccionado');
        if (!checkbox) {
            return;
        }

        if (event.target.classList.contains('producto-nombre-click')) {
            event.preventDefault();
            agregarProductoDesdeFila(checkbox);
            return;
        }

        if (!event.target.classList.contains('producto-seleccionado')) {
            checkbox.checked = !checkbox.checked;
        }

        actualizarSeleccionProductoVisual(checkbox);
    });

    cuerpoTabla.dataset.listenerAttached = 'true';
}

function agregarProductoDesdeFila(checkboxElemento) {
    if (!checkboxElemento) {
        return;
    }

    var codigo = checkboxElemento.dataset.codigo || '';
    var nombre = checkboxElemento.dataset.nombre || '';
    var costo = checkboxElemento.dataset.costo || 0;
    var unidad = checkboxElemento.dataset.unidad || '';

    if (!codigo || !nombre) {
        return;
    }

    var codigoProducto = document.getElementById('codigo_producto');
    var nombreProducto = document.getElementById('producto');
    var costoProducto = document.getElementById('costo');
    var cantidadInput = document.getElementById('cantidad');

    if (codigoProducto) {
        codigoProducto.value = codigo;
    }
    if (nombreProducto) {
        nombreProducto.value = nombre;
    }
    if (costoProducto) {
        costoProducto.value = costo;
    }
    if (cantidadInput && (!cantidadInput.value || parseFloat(cantidadInput.value) <= 0)) {
        cantidadInput.value = 1;
    }

    aplicarUnidadSeleccionada(unidad);

    cerrarModal();
}

function actualizarSeleccionProductoVisual(checkbox) {
    if (!checkbox) {
        return;
    }

    var fila = checkbox.closest('tr');
    if (fila) {
        if (checkbox.checked) {
            fila.classList.add('info');
        } else {
            fila.classList.remove('info');
        }
    }
}

function aplicarUnidadSeleccionada(unidad) {
    if (unidad) {
        seleccionarUnidadProducto(unidad);
    } else {
        solicitarUnidadProductoPorDefecto();
    }
}

function seleccionarTodosProductos(checked) {
    var checkboxes = document.querySelectorAll('#listprod .producto-seleccionado');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = checked;
        actualizarSeleccionProductoVisual(checkbox);
    });
}

function agregarProductosSeleccionados() {
    var seleccionados = document.querySelectorAll('#listprod .producto-seleccionado:checked');
    if (!seleccionados.length) {
        alertSwal('Seleccione al menos un producto', 'warning');
        return;
    }

    var cantidadInput = document.getElementById('cantidad');
    var cantidadBase = cantidadInput && parseFloat(cantidadInput.value) > 0 ? cantidadInput.value : 1;

    seleccionados.forEach(function(checkbox) {
        var codigo = checkbox.dataset.codigo || '';
        var nombre = checkbox.dataset.nombre || '';
        var costo = checkbox.dataset.costo || 0;
        var unidad = checkbox.dataset.unidad || '';

        var codigoProducto = document.getElementById('codigo_producto');
        var nombreProducto = document.getElementById('producto');
        var costoProducto = document.getElementById('costo');

        if (codigoProducto) {
            codigoProducto.value = codigo;
        }
        if (nombreProducto) {
            nombreProducto.value = nombre;
        }
        if (costoProducto) {
            costoProducto.value = costo;
        }
        if (cantidadInput) {
            cantidadInput.value = cantidadBase;
        }

        aplicarUnidadSeleccionada(unidad);

        cargar_producto();
    });

    cerrarModal();
}


        function datos_prod(a, b, c) {
            document.getElementById('codigo_producto').value = a;
            document.getElementById('producto').value = b;
            document.getElementById('costo').value = c;
            cerrarModal();
        }

        function cerrarModal() {
            $("#ModalProd").html("");
            $("#ModalProd").modal("hide");
        }


        function actualizar_estado_punto_reorden(secu_minv, tran, fecha_pedido, fecha_entrega) {
            window.opener.actualizar_estado_punto_reorden_seleccionados(secu_minv, tran, fecha_pedido, fecha_entrega, 'PEDIDO COMPRA');
            window.close();
        }




        // --------------------------------------------------------------
        // FUNCIONES PARA CARGAR POR ARCHIVO
        // --------------------------------------------------------------

        function modal_cargar_archivo() {
            xajax_modal_cargar_archivo(xajax.getFormValues("form1"));
        }

        function abre_modal() {
            $("#mostrarmodal").modal("show");
        }

        function cerrar_modal() {
            $("#mostrarmodal").modal("hide");
        }

        // carga imagen a servidor
        function upload_image(id) { //Funcion encargada de enviar el archivo via AJAX

            $(".upload-msg").text('Cargando...');
            var inputFileImage = document.getElementById(id);

            console.log(inputFileImage);

            var file = inputFileImage.files[0];
            var data = new FormData();
            data.append(id, file);

            $.ajax({
                url: "upload.php?id=" + id, // Url to which the request is send
                type: "POST", // Type of request to be send, called as method
                data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false, // The content type used when sending data to the server.
                cache: false, // To unable request pages to be cached
                processData: false, // To send DOMDocument or non processed data file it is set to false
                success: function(data) // A function to be called if request succeeds
                {
                    $(".upload-msg").html(data);
                    window.setTimeout(function() {
                        $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        }

        function consultar() {
            var sucursal = document.getElementById('sucursal').value;
            if (sucursal != '') {
                xajax_cargar_ord_compra_respaldo(xajax.getFormValues("form1"));
            } else {
                alertSwal('Seleccione la sucursal', 'warning');
            }
        }

        function ocultar_procesar() {
            document.getElementById('div_procesar').style.display = "none";
        }

        function mostrar_procesar() {
            document.getElementById('div_procesar').style.display = "block";
        }

        function processar_archivo() {
            xajax_cargar_ord_compra(xajax.getFormValues("form1"));
        }

        // --------------------------------------------------------------
        // FIN FUNCIONES PARA CARGAR POR ARCHIVO
        // --------------------------------------------------------------


        function configurarFiltrosReporte(table) {
            var filtroEstado = document.getElementById('filtroEstado');
            var buscador = document.getElementById('buscadorSolicitudes');

            var botonGenerar = document.getElementById('btnGenerarReporteSolicitudes');
            var botonLimpiar = document.getElementById('btnLimpiarBusquedaSolicitudes');
            var botonImprimir = document.getElementById('btnImprimirSeleccion');

            // GENERAR REPORTE -> consulta a BD con los filtros actuales
            if (botonGenerar) {
                botonGenerar.onclick = function() {
                    reporte_solicitudes(); // ya hace jsShowWindowLoad + xajax_reporte_solicitudes
                };
            }

            // LIMPIAR -> resetea filtros y vuelve a pedir el reporte completo
            if (botonLimpiar) {
                botonLimpiar.onclick = function() {
                    if (buscador) buscador.value = '';
                    if (filtroEstado) filtroEstado.value = '';

                    reporte_solicitudes();
                };
            }

            // Imprimir (se mantiene igual)
            if (botonImprimir) {
                botonImprimir.onclick = imprimirSolicitudesSeleccionadas;
            }
        }


        function solicitudes_orden_compra() {
            jsShowWindowLoad();
            xajax_reporte_solicitudes_orden_compra(xajax.getFormValues("form1"));
        }

        function totales(cod, j, pedi, clpv, sucu) {

            var serial = cod + '_vu';

            var c = cod + '_cantd';

            //var c = j + '_cant';

            var t = document.getElementById(serial).value;
            if (t == '') {
                t = 0;
            }
            var cant = document.getElementById(c).value;
            if (cant == '') {
                cant = 0;
            }

            var pt = cant * parseFloat(t);


            var st = cod + '_pt'

            document.getElementById(st).value = pt;

            xajax_subtotal_prod(xajax.getFormValues("form1"), pedi, clpv, sucu);

        }


        function stotal(ser, j, tot) {
            var serial = j + '-' + ser + '_st';
            var sub = 0;
            var val = 0;



            for (var i = j - tot; i < j; i++) {

                var id = i + '-' + ser + '_pt';

                val = document.getElementById(id).value;
                if (val == '') {
                    val = 0;
                }
                sub += parseFloat(val);

            }

            //IVA
            var i = parseInt(j + 2);
            var iva = i + '-' + ser + '_iv';
            var viva = document.getElementById(iva).value;
            if (viva == '') {
                viva = 0;
            }



            //CARGOS
            var c = parseInt(j + 3);
            var car = c + '-' + ser + '_ocar';
            var vcar = document.getElementById(car).value;
            if (vcar == '') {
                vcar = 0;
            }



            //ID DEL TOTAL
            var k = parseInt(j + 4);
            var tot = k + '-' + ser + '_tot';

            //ID DEL DESCUENTO
            var d = parseInt(j + 1);
            var des = d + '-' + ser + '_des';
            var vdes = document.getElementById(des).value;
            if (vdes == '') {
                vdes = 0;
            }


            var subtotal = parseFloat(sub) - parseFloat(vdes);

            var total = parseFloat(viva) + parseFloat(vcar) + subtotal;


            document.getElementById(serial).value = sub;
            document.getElementById(tot).value = total;

        }

        function siva(ser) {


            //iva
            var serial = ser + '_iv';

            var iva = document.getElementById(serial).value;

            if (iva == '') {
                iva = 0;
            }

            var civa = parseFloat(iva) / 100;

            //subtotal
            var st = ser + '_st';
            var sub = document.getElementById(st).value;

            //descuento
            var sd = ser + '_des';
            var des = document.getElementById(sd).value;
            if (des == '') {
                des = 0;
            }
            //otros cargos
            var scar = ser + '_ocar';
            var car = document.getElementById(scar).value;
            if (car == '') {
                car = 0;
            }

            //TOTAL

            var subtotal = parseFloat(sub) - parseFloat(des);
            var total = parseFloat(iva) + parseFloat(car) + subtotal;



            //ID DEL TOTAL
            var tot = ser + '_tot';

            document.getElementById(tot).value = total;

        }

        function focoCampo(id) {
            document.getElementById(id).focus();
        }

        function sdes(ser) {


            //descuento
            var serial = ser + '_des';

            var des = document.getElementById(serial).value;

            if (des == '') {
                des = 0;
            }


            //subtotal
            var st = ser + '_st';
            var sub = document.getElementById(st).value;

            //iva
            var siva = ser + '_iv';
            var iva = document.getElementById(siva).value;
            if (iva == '') {
                iva = 0;
            }

            //cargos
            var scar = ser + '_ocar';
            var car = document.getElementById(scar).value;
            if (car == '') {
                car = 0;
            }

            var total = parseFloat(iva) + parseFloat(car) + (parseFloat(sub) - parseFloat(des));

            //TOTAL

            var tot = ser + '_tot';


            document.getElementById(tot).value = total;

        }

        function mayus(e) {
            e.value = e.value.toUpperCase();
        }

        function salvar(pedi, empr, sucu, prof, codapro) {

            Swal.fire({
                title: 'Desea Salvar los Precios...?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '40%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_salvar(pedi, empr, sucu, prof, codapro, xajax.getFormValues("form1"));
                }
            })


        }

        function guardar_proforma(pedi, empr, sucu, prof, codapro) {

            Swal.fire({
                title: '¿Esta seguro de Ingresar los precios de la Proforma?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                width: '40%',
            }).then((result) => {
                if (result.value) {
                    jsShowWindowLoad();
                    xajax_guardar_proforma(pedi, empr, sucu, prof, codapro, xajax.getFormValues("form1"));
                }
            })


        }

        //FUNCIONES SOLICITUD DE MATERIALES CCDC
        function carga_detalle_pedido_req() {
            xajax_detalle_pedido_req(<?= $idReq ?>, <?= $filDreq ?>);
        }

        function cerrarModalCompra(id, idapro, empresa, sucursal) {
            parent.cerrar_modales(id, idapro, empresa, sucursal, 2);
        }

        function cerrarModales() {
            parent.cerrar_modales_val();
        }
        //FIN FUNCIONES SOLICITUD DE MATERIALES

        function modal_adjuntos_pedi(pedi, empr, sucu){
            
            $("#ModalAdjPedi").modal("show");    
            xajax_form_adjuntos_pedi( pedi, empr, sucu);

        }
        function agregar_adjuntos_pedi(id, empr, sucu){
            var adj = document.getElementById("archivo_pedi").value;

            if(adj==''){
                alertSwal('Seleccione un Archivo');
            }
            else{
                jsShowWindowLoad();
                xajax_agregar_adjuntos_pedi( id, empr, sucu, adj);
            }
 
         }

         function elimina_adjpedi(id, comp, empr, sucu){
            Swal.fire({
                   title: '¿Esta seguro de eliminar el Archivo?',
                   text: "",
                   type: 'warning',
                   showCancelButton: true,
                   confirmButtonColor: '#3085d6',
                   cancelButtonColor: '#d33',
                   confirmButtonText: 'Aceptar',
                   allowOutsideClick: false,
                   width: '40%',              
                   }).then((result) => {
                       if (result.value) {	
                           jsShowWindowLoad();
                                xajax_eliminar_adjunto_pedi(id, comp, empr, sucu);  
                       }
            })
         }
    </script>


    <!--DIBUJA FORMULARIO FILTRO-->

    <body onload="removeCSS(3);">
        <style>
            .section-card {
                background: #fff;
                border: 1px solid #d2d6de;
                border-radius: 4px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
            }

            .section-card__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 15px;
                border-bottom: 1px solid #e5e5e5;
                background-color: #f4f8fb;
            }

            .section-card__header--with-actions .section-card__actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .section-card__title {
                margin: 0;
                font-weight: 600;
                color: #3c8dbc;
            }

            .section-card__body {
                padding: 15px;
            }

            .section-card__body--colapsado {
                display: none;
            }

            .section-card__body--stacked>.section-card,
            .section-card__body--stacked>.section-card+.section-card,
            .section-card__body--spaced>* {
                margin-bottom: 15px;
            }

            .section-card__body--stacked>.section-card:last-child,
            .section-card__body--spaced>*:last-child {
                margin-bottom: 0;
            }

            .section-card.section-card--main {
                border-color: #3c8dbc;
            }

            .section-card.section-card--secondary .section-card__header {
                background-color: #f9f9f9;
            }

            .box.section-panel {
                border-top: 3px solid #3c8dbc;
            }

            .box.section-panel .box-header {
                background-color: #f4f8fb;
                border-bottom: 1px solid #3c8dbc;
            }

            .firmas-aprobadores .firma-card {
                border: 1px solid #d2d6de;
                padding: 8px 10px;
                border-radius: 4px;
                background: #fff;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
                min-height: 100px;
            }

            .reorden-controles {
                display: flex;
                align-items: center;
                gap: 8px;
                justify-content: space-between;
                margin: 5px 0 10px;
                flex-wrap: wrap;
            }

            .reorden-dropdown {
                min-width: 140px;
            }

            .aprobadores-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(200px, 1fr));
                gap: 15px;
            }

            @media (max-width: 1199px) {
                .aprobadores-grid {
                    grid-template-columns: repeat(3, minmax(200px, 1fr));
                }
            }

            @media (max-width: 991px) {
                .aprobadores-grid {
                    grid-template-columns: repeat(2, minmax(200px, 1fr));
                }
            }

            @media (max-width: 600px) {
                .aprobadores-grid {
                    grid-template-columns: 1fr;
                }
            }

            .firmas-aprobadores .firma-card h4 {
                margin-top: 0;
                color: #3c8dbc;
                font-weight: 600;
            }

            .firma-card__titulo {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                margin-bottom: 6px;
            }

            .firma-card__envio {
                font-size: 12px;
                color: #555;
                margin: 0;
            }

            .firma-card__fila-controles {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }

            .firma-card__fila-controles .form-control {
                height: 30px;
                padding: 4px 8px;
            }

            .firmas-aprobadores .firma-card small {
                color: #888;
            }

            .tabla-aprobadores thead {
                background: #3c8dbc;
                color: #fff;
            }

            .firma-card__actions {
                margin-top: 10px;
                display: flex;
                justify-content: flex-end;
            }

            .firma-card__actions .btn-link {
                padding: 0;
            }

            .busqueda-aprobadores {
                margin-bottom: 10px;
            }

            .crear-grupo-btn {
                margin-top: 8px;
                width: 100%;
            }

            #tarjetaPrincipal {
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }

            #tarjetaPrincipal.estado-creando {
                border-color: #3498db;
                box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.15);
            }

            #tarjetaPrincipal.estado-creando .section-card__header {
                background-color: #e8f2ff;
            }

            #tarjetaPrincipal.estado-creada {
                border-color: #f1c40f;
                box-shadow: 0 0 0 2px rgba(241, 196, 15, 0.2);
            }

            #tarjetaPrincipal.estado-creada .section-card__header {
                background-color: #fff8e1;
            }

            #tarjetaPrincipal.estado-editando {
                border-color: #2ecc71;
                box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
            }

            #tarjetaPrincipal.estado-editando .section-card__header {
                background-color: #e7f9f1;
            }

            .producto-no-registrado-label {
                color: #8a6d3b;
                font-weight: 700;
                font-size: 14px;
                display: flex;
                align-items: center;
            }

            .producto-no-registrado-alerta {
                background: #fff9c4;
                border: 1px solid #f0e68c;
                border-radius: 4px;
                padding: 10px 12px;
                margin-bottom: 10px;
            }

            .producto-no-registrado-alerta strong {
                margin-right: 8px;
                font-size: 15px;
            }

            .btn-accion-superior {
                padding: 8px 14px;
                font-size: 14px;
                border-radius: 4px;
            }

            .btn-agregar-producto {
                font-weight: 700;
                min-width: 200px;
            }

            .productos-agregados-card {
                margin-top: 15px;
            }

            .producto-layout .fila-producto {
                margin-bottom: 10px;
            }

            .producto-layout .slot-archivo-vacio {
                display: none;
            }

            .producto-layout .detalle-acciones {
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
                height: 100%;
            }

            .firma-card--resumen {
                box-shadow: none;
                border: 1px solid #eaeaea;
            }

 .firma-card__fila-controles {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto; /* persona | posición */
    column-gap: 8px;
    align-items: center;
}

/* Primer select (persona) ocupa toda la columna */
.firma-card__fila-controles .form-control:first-child {
    width: 100%;
}

/* Segundo select (posición) con un ancho mínimo consistente */
.firma-card__fila-controles .form-control:last-child {
    min-width: 120px; /* ajusta según te guste */
}

/* En móviles se apilan */
@media (max-width: 600px) {
    .firma-card__fila-controles {
        grid-template-columns: 1fr;
        row-gap: 6px;
    }
}


        </style>
        <div class="row">
            <!-- Main content -->
            <section class="content">
                <form id="form1" name="form1" class="" role="form" action="javascript:void(null);" onkeypress="return manejarTeclasFormulario(event);">
                    <div class="col-md-12">
                        <div id="divMainInfo" class="direct-chat direct-chat-primary">
                            <div class="box-body">
                                <ul class="nav nav-tabs" id="myTab">
                                    <li class="active"><a data-target="#home" data-toggle="tab">PEDIDOS </a></li>
                                    <? if ($codigo_pedido == '0' && $idReq == '0') { ?>
                                        <li><a data-target="#reporte" data-toggle="tab" onclick="reporte_solicitudes();">REPORTE PEDIDOS</a></li>
                                    <? } ?>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="home">
                                        <div class="section-card section-card--main" style="margin-top:25px;">
                                            <div class="section-card__body section-card__body--stacked">
                                                <div id="divFormularioCabecera" class="section-card__block"></div>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="ModalProd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                        <div class="modal fade" id="ModalPedidos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                        <div class="modal fade" id="ModalAnulados" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                        <div class="modal fade" id="ModalGestionAprobaciones" tabindex="-1" role="dialog" aria-labelledby="modalAprobacionesLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <h4 class="modal-title" id="modalAprobacionesLabel"><i class="fa fa-users"></i> Gestión de aprobadores</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
                                                            <li role="presentation" class="active"><a href="#tabBuscarAprobadores" aria-controls="tabBuscarAprobadores" role="tab" data-toggle="tab">Buscar aprobadores</a></li>
                                                            <li role="presentation"><a href="#tabAgregarAprobador" aria-controls="tabAgregarAprobador" role="tab" data-toggle="tab">Agregar aprobador</a></li>
                                                            <li role="presentation"><a href="#tabCrearGrupo" aria-controls="tabCrearGrupo" role="tab" data-toggle="tab">Crear cargo</a></li>
                                                        </ul>

                                                        <div class="tab-content">
                                                            <div role="tabpanel" class="tab-pane active" id="tabBuscarAprobadores">
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="empresaBuscarAprobador">Empresa</label>
                                                                            <select class="form-control" id="empresaBuscarAprobador"></select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="sucursalBuscarAprobador">Sucursal</label>
                                                                            <select class="form-control" id="sucursalBuscarAprobador"></select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="text-muted">Seleccione una empresa y sucursal para gestionar aprobadores.</p>
                                                                <div class="form-group">
                                                                    <label for="filtroCargoAprobador">Cargo</label>
                                                                    <select class="form-control" id="filtroCargoAprobador" onchange="actualizarFiltroCargo(this.value);"></select>
                                                                    <small class="help-block">Filtre los aprobadores por el cargo disponible en la sucursal.</small>
                                                                </div>
                                                                <div class="busqueda-aprobadores">
                                                                    <label for="busquedaAprobadores">Buscar aprobadores</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text" class="form-control" id="busquedaAprobadores" placeholder="Nombre o cargo" oninput="actualizarFiltroAprobadores(this.value);">
                                                                        <span class="input-group-btn">
                                                                            <button type="button" class="btn btn-default" onclick="document.getElementById('busquedaAprobadores').value='';actualizarFiltroAprobadores('');" title="Limpiar búsqueda">
                                                                                <i class="fa fa-eraser"></i>
                                                                            </button>
                                                                        </span>
                                                                    </div>
                                                                    <small class="help-block">La búsqueda se actualiza automáticamente mientras escribe.</small>
                                                                </div>
                                                                <div class="table-responsive" id="contenedorListaAprobadores"></div>
                                                            </div>
                                                            <div role="tabpanel" class="tab-pane" id="tabAgregarAprobador">
                                                                <h3 class="box-title" style="margin-top:0;" id="tituloAgregarAprobador"><i class="fa fa-user-plus"></i> Nuevo aprobador</h3>
                                                                <div class="box-header with-border">
                                                                    <h3 class="box-title"><i class="fa fa-id-card"></i> Datos del aprobador</h3>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="empresaAgregarAprobador">Empresa</label>
                                                                            <select class="form-control" id="empresaAgregarAprobador"></select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="sucursalAgregarAprobador">Sucursal</label>
                                                                            <select class="form-control" id="sucursalAgregarAprobador"></select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="text-muted">Los aprobadores se guardarán para la empresa y sucursal seleccionadas.</p>
                                                                <div class="box box-default section-panel" style="margin-bottom:0;">

                                                                    <div class="box-body">
                                                                        <div class="form-group">
                                                                            <label for="grupoAprobador">Cargo</label>
                                                                            <div class="input-group">
                                                                                <select class="form-control" id="grupoAprobador"></select>
                                                                                <span class="input-group-btn">
                                                                                    <button type="button" class="btn btn-default" onclick="renderSelectorGrupos();" title="Refrescar cargos">
                                                                                        <i class="fa fa-refresh"></i>
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-primary" onclick="irACrearCargoDesdeAgregar();" title="Crear cargo">
                                                                                        <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </span>
                                                                            </div>
                                                                            <small class="help-block">Seleccione el cargo al que pertenecerá el aprobador.</small>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="nuevoAprobadorNombre">Nombre del aprobador</label>
                                                                            <input type="text" class="form-control" id="nuevoAprobadorNombre" placeholder="Ingrese el nombre completo">
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <button type="button" class="btn btn-success" onclick="agregarAprobadorDesdeModal();" id="btnGuardarAprobador">
                                                                                <i class="fa fa-save"></i> Guardar aprobador
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div role="tabpanel" class="tab-pane" id="tabCrearGrupo">

                                                                <h3 class="box-title" style="margin-top:0;"><i class="fa fa-id-badge"></i> Crear cargo</h3>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="empresaCrearGrupo">Empresa</label>
                                                                            <select class="form-control" id="empresaCrearGrupo"></select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label for="sucursalCrearGrupo">Sucursal</label>
                                                                            <select class="form-control" id="sucursalCrearGrupo"></select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="box box-default section-panel" style="margin-bottom:0;">

                                                                    <div class="box-body">

                                                                        <p class="text-muted">Los cargos ayudan a organizar aprobadores por sucursal.</p>
                                                                        <div class="form-group">
                                                                            <label for="nuevoGrupoNombre">Nombre del cargo</label>
                                                                            <input type="text" class="form-control" id="nuevoGrupoNombre" placeholder="Ejemplo: Compras, Finanzas">
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <button type="button" class="btn btn-success crear-grupo-btn" onclick="crearGrupoAprobador();">
                                                                                <i class="fa fa-plus"></i> Guardar cargo
                                                                            </button>
                                                                        </div>
                                                                        <p class="text-muted">Gestionar cargos disponibles.</p>
                                                                        <div id="contenedorListaCargos" style="margin-bottom:15px;"></div>
                                                                        <p class="text-muted">Defina el orden en el que aparecerán los cargos al crear pedidos.</p>
                                                                        <div id="contenedorOrdenCargos" style="margin-bottom:15px;"></div>
                                                                        <div class="text-right">
                                                                            <button type="button" class="btn btn-primary" onclick="guardarOrdenCargos();">
                                                                                <i class="fa fa-save"></i> Guardar orden
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                                        <button type="button" class="btn btn-primary" onclick="confirmarSeleccionAprobadores();">Agregar aprobaciones</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <? if ($codigo_pedido == '0') { ?>
                                        <div class="tab-pane" id="reporte">

                                            <div style="margin-top:10px;text-align:right;"><span class="btn btn-warning btn-md" onclick="reporte_solicitudes();" title="Refrescar Solicitudes" style="cursor: pointer;"><i class="glyphicon glyphicon-refresh"></i></span></div>

                                            <div id="divReporte" style="margin-top:25px;"></div>
                                            <div class="modal fade" id="ModalDetalle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalAdj" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalAprobaciones" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalEditar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalAnular" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalProfProv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalProveedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            <div class="modal fade" id="ModalAdjPedi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                                            
                                        </div>

                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="width: 100%; margin: 0px;">
                        <div class="modal fade" id="miModalLoad" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="modal_load_titulo"> TITULO </h4>
                                    </div>
                                    <div class="modal-body" id="modal_load_body"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </body>

    <script>
        window.addEventListener('load', function() {
            inicializarAccesosRapidosProducto();
            configurarCamposMultilinea();
            configurarCamposProducto();
            inicializarEditorDetalleProductos();
        });
        genera_formulario(); /*genera_detalle();genera_form_detalle();*/
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>
