<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.buttons.min.css" media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/css/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css" media="screen">


    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/js/select2.full.min.js"></script>

    <!-- AdminLTE App -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/js/adminlte.min.js"></script>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/css/bootstrap-treeview.css" media="screen">
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css">

    <style>
        .input-group-addon.primary {
            color: rgb(255, 255, 255);
            background-color: rgb(50, 118, 177);
            border-color: rgb(40, 94, 142);
        }

        input {
            font-size: 11px !important;
        }

        /* Estilo de datatable */
        .copiar {
            font-size: 10px;
            color: white;
            margin: 7px;
        }

        .contenedor_copiar {
            border-radius: 50%;
            background-color: #337ab7 !important;
            text-align: center;
        }

        .pdf {
            font-size: 10px;
            color: white;
            margin: 7px;
        }

        .contenedor_pdf {
            border-radius: 50%;
            background-color: #dc2f2f !important;
            text-align: center;
        }

        .excel {
            font-size: 10px;
            color: white;
            margin: 7px;
        }

        .contenedor_excel {
            border-radius: 50%;
            background-color: #3ca23c !important;
            text-align: center;
        }

        .csv {
            font-size: 10px;
            color: white;
            margin: 7px;
        }

        .contenedor_csv {
            border-radius: 50%;
            background-color: #007c7c !important;
            text-align: center;
        }

        .imprimir {
            font-size: 10px;
            color: white;
            margin: 7px;
        }

        .contenedor_imprimir {
            border-radius: 50%;
            background-color: #8766b1 !important;
            text-align: center;
        }

        /* FIN Estilo de datatable */
    </style>

    <?php
    $id_parcial = 0;
    if (isset($_GET['codigo_solicitud'])) {
        $id_parcial = $_GET['codigo_solicitud'];
    }
    ?>


    <script>
        var id_parcial_ = '<?= $id_parcial ?>';
        if (id_parcial_ > 0) {
            padre = $(window.parent.document);
            idModulo = 197;
        } else {
            //id de modulo
            padre = $(window.parent.document);
            idModulo = $(padre).find("#idModuloMenu").val();
        }

        function genera_formulario() {
            xajax_genera_formulario_pedido('nuevo', xajax.getFormValues("form1"), idModulo);
        }

        //alertas
        function alerts(mensaje, tipo) {
            if (tipo == 'success') {
                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000,
                    width: '600',
                })
            } else {

                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: true,
                    width: '600',

                })
            }

        }


        // carga imagen a servidor
        function upload_image(id) { //Funcion encargada de enviar el archivo via AJAX
            $(".upload-msg").text('Cargando...');
            var inputFileImage = document.getElementById(id);
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
            var empresa = document.getElementById("empresa");
            if (!empresa || empresa.value === "") {
                alerts("Seleccione la empresa para realizar la búsqueda.", "error");
                return;
            }
            xajax_consultar(xajax.getFormValues('form1'));
        }


        function proveedorKeyHandler(event) {
            var key = event.key || event.keyCode;

            if (key === 'Enter' || key === 13 || key === 'F4' || key === 115) {
                event.preventDefault();
                autocompletar_proveedor_btn();
            }
        }

        function autocompletar_proveedor_btn() {
            var empresa  = document.getElementById("empresa").value;
            var nombre   = document.getElementById("proveedor_nombre").value;

            if (empresa === '') {
                alerts("Seleccione la empresa para continuar.", "error");
                return;
            }

            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=700, height=390, top=200, left=130";

            var pagina = '../reporte_aprobacion_proveedores/buscar_prov.php?empresa=' 
                            + empresa + 
                            //'&sucursal=' + sucursal + 
                            '&nombre=' + nombre;

            window.open(pagina, "", opciones);
        }

        //marcar
        function marcar(source) {
            var checkboxes = document.getElementsByTagName('input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == "checkbox") {
                    checkboxes[i].checked = source.checked;
                }
            }
        }

       function limpiarConsulta() {
            try { $('#tbclientes').DataTable().clear().destroy(); } catch(e){}
            document.getElementById("divFormularioDetalle").innerHTML = "";
        }

        function guardar() {
            var empresa = document.getElementById("empresa");
            if (!empresa || empresa.value === "") {
                alerts("Seleccione la empresa antes de aprobar proveedores.", "error");
                return;
            }

            var seleccionados = document.querySelectorAll("#tbclientes tbody input[type='checkbox']:checked");
            if (seleccionados.length === 0) {
                alerts("Seleccione al menos un proveedor para aprobar.", "error");
                return;
            }

            xajax_guardar_proveedores(xajax.getFormValues("form1"));
        }

    
    </script>



    <!--DIBUJA FORMULARIO FILTRO-->

    <body>
        <div class="container-fluid">
            <form id="form1" name="form1" action="javascript:void(null);" novalidate="novalidate">


                <div class="main row">
                    <div class="col-md-12">
                        <div id="divFormularioTotal" class="table-responsive"></div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <!-- Nav tabs -->
                    <!-- <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#divCompraMenu" aria-controls="divCompraMenu" role="tab" data-toggle="tab">Activacion de Proveedores</a></li>
                    </ul> -->

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="divCompraMenu" style="margin-top: 5px !important;">
                            <div id="divFormularioCabecera"></div>
                            <div id="divFormularioDetalle" class="table-responsive"></div>
                            <div id="divTotal"></div>
                            <div id="divFormularioDetalle2" class="table-responsive"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="divPagoMenu">
                            <div id="divFormularioFp" class="table-responsive"></div>
                            <div id="divFormularioDetalleFP_DET" class="table-responsive"></div>
                            <div id="divFormularioDetalle_FP" class="table-responsive"></div>
                            <div id="divTotalFP" class="table-responsive"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="divRetencionMenu">
                            <div id="divFormularioRET" class="table-responsive"></div>
                            <div id="divFormularioCabeceraRET" class="table-responsive"></div>
                            <div id="divFormularioDetalleRET" class="table-responsive"></div>
                        </div>
                    </div>
                </div>



                <div style="width: 100%;">
                    <div id="extra"></div>
                    <div id="extra2"></div>
                    <div id="extra3"></div>
                    <div id="precio_modal"></div>
                    <div id="miAdjunto"></div>
                    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

                    <div class="modal fade" id="ModalClpv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                    <div class="modal fade" id="ModalProd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>
                    <div class="modal fade" id="ModalGrid" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>
                    <div class="modal fade" id="ModalRECO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>
                    <div class="modal fade" id="ModalRECOD" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>
                </div>


                <div class="modal fade" id="miModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">DIARIO CONTABLE <span id="divTituloAsto"></span></h4>
                            </div>
                            <div class="modal-body">
                                <div>
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation" class="active"><a href="#divInfo" aria-controls="divInfo" role="tab" data-toggle="tab">Informacion</a></li>
                                        <li role="presentation"><a href="#divDirectorio" aria-controls="divDirectorio" role="tab" data-toggle="tab">Directorio</a></li>
                                        <li role="presentation"><a href="#divRetencion" aria-controls="divRetencion" role="tab" data-toggle="tab">Retencion</a></li>
                                        <li role="presentation"><a href="#divDiario" aria-controls="divDiario" role="tab" data-toggle="tab">Diario</a></li>
                                        <li role="presentation"><a href="#divAdjuntos" aria-controls="divAdjuntos" role="tab" data-toggle="tab">Adjuntos</a></li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="divInfo">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDirectorio">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divRetencion">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDiario">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divAdjuntos">...</div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div id="divGrid"></div>
        <br><br><br><br><br><br><br>
    </body>
    <script>
        genera_formulario();
    
        //DATATABLE
        function init() {
            // Destruir instancia anterior si existe
            if ($.fn.DataTable.isDataTable('#tbclientes')) {
                $('#tbclientes').DataTable().destroy();
            }

            // Verificar que la tabla existe
            if ($('#tbclientes').length === 0) {
                console.error('Tabla #tbclientes no encontrada en el DOM');
                return;
            }

            try {
                var table = $('#tbclientes').DataTable({
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'copy',
                            title: 'Lista de Proveedores',
                            titleAttr: 'Click para Copiar',
                            text: '<div class="contenedor_copiar"><i class="fa fa-clipboard copiar"></i><label class="labe"></label></div>',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var retorno = "",
                                            tag, respuesta = "",
                                            reponer = [];

                                        tag = $(node).find('input');
                                        if (tag.length > 0) {
                                            retorno = retorno + ($(tag).map(function() {
                                                return $(this).val();
                                            }).get().join(','));
                                        }

                                        respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                        for (i = 0; i < reponer.length; i++) {
                                            $(node).append(reponer[i]);
                                        }

                                        return respuesta;
                                    }
                                },
                            }
                        }, {
                            extend: 'excelHtml5',
                            title: 'Lista de Proveedores',
                            titleAttr: 'Click para descargar como Excel',
                            text: '<div class="contenedor_excel"><i class="fa fa-file-excel-o excel"></i><label class="labe"></label></div>',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var retorno = "",
                                            tag, respuesta = "",
                                            reponer = [];

                                        tag = $(node).find('input');
                                        if (tag.length > 0) {
                                            retorno = retorno + ($(tag).map(function() {
                                                return $(this).val();
                                            }).get().join(','));
                                        }

                                        respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                        for (i = 0; i < reponer.length; i++) {
                                            $(node).append(reponer[i]);
                                        }

                                        return respuesta;
                                    }
                                },
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: 'Lista de Proveedores',
                            titleAttr: 'Click para descargar como CSV',
                            text: '<div class="contenedor_csv"><i class="fa fa-file-text-o csv"></i><label class="labe"></label></div>',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var retorno = "",
                                            tag, respuesta = "",
                                            reponer = [];

                                        tag = $(node).find('input');
                                        if (tag.length > 0) {
                                            retorno = retorno + ($(tag).map(function() {
                                                return $(this).val();
                                            }).get().join(','));
                                        }

                                        respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                        for (i = 0; i < reponer.length; i++) {
                                            $(node).append(reponer[i]);
                                        }

                                        return respuesta;
                                    }
                                },
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                return "Lista de Proveedores";
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            text: '<div class="contenedor_pdf"><i class="fa fa-file-pdf-o pdf"></i><label class="labe"></label></div>',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var retorno = "",
                                            tag, respuesta = "",
                                            reponer = [];

                                        tag = $(node).find('input');
                                        if (tag.length > 0) {
                                            retorno = retorno + ($(tag).map(function() {
                                                return $(this).val();
                                            }).get().join(','));
                                        }

                                        respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                        for (i = 0; i < reponer.length; i++) {
                                            $(node).append(reponer[i]);
                                        }

                                        return respuesta;
                                    }
                                },
                            }
                        }, {
                            extend: 'print',
                            title: 'Lista de Proveedores',
                            titleAttr: 'Click para Imprimir',
                            text: '<div class="contenedor_imprimir"><i class="fa fa-print imprimir"></i><label class="labe"></label></div>',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var retorno = "",
                                            tag, respuesta = "",
                                            reponer = [];

                                        tag = $(node).find('input');
                                        if (tag.length > 0) {
                                            retorno = retorno + ($(tag).map(function() {
                                                return $(this).val();
                                            }).get().join(','));
                                        }

                                        respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                        for (i = 0; i < reponer.length; i++) {
                                            $(node).append(reponer[i]);
                                        }

                                        return respuesta;
                                    }
                                },
                            }
                        },
                    ],

                    processing: true,
                    "language": {
                        "processing": "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
                        "search": "<i class='fa fa-search'></i>",
                        "searchPlaceholder": "Buscar",
                        'paginate': {
                            'previous': 'Anterior',
                            'next': 'Siguiente'
                        },
                        "zeroRecords": "No se encontraron proveedores con los filtros ingresados.",
                        "emptyTable": "No existen proveedores para los filtros seleccionados.",
                        "info": "Mostrando _START_ a _END_ de  _TOTAL_ registros",
                        "infoEmpty": "No hay registros disponibles",
                        "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                    },
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "pageLength": 10,
                    "lengthMenu": [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    "responsive": true,
                    "autoWidth": false,
                    "deferRender": true,
                    "order": [[3, 'asc']],
                    "columnDefs": [
                        {
                            "targets": -1,
                            "orderable": false,
                            "searchable": false
                        }
                    ]
                });
                
                table.search().draw();
                console.log('DataTable inicializado correctamente');
                
            } catch(error) {
                console.error('Error al inicializar DataTable:', error);
            }
        }
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>
