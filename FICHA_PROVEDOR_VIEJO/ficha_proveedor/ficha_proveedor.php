<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<?
if (isset($_REQUEST['codclpv'])) {
    $codclpv = $_REQUEST['codclpv'];
} else {
    $codclpv = 0;
}



if (isset($_REQUEST['codpedi'])) {
    $codpedi = $_REQUEST['codpedi'];
} else {
    $codpedi = 0;
}

$usaUafeModal = 'f';
if (isset($_SESSION['U_EMPRESA'])) {
    $idEmpresaUafe = intval($_SESSION['U_EMPRESA']);
    if ($idEmpresaUafe > 0) {
        $oIfxUafe = new Dbo;
        $oIfxUafe->DSN = $DSN_Ifx;
        $oIfxUafe->Conectar();
        $sqlUafeModal = "SELECT emmpr_uafe_cprov FROM saeempr WHERE empr_cod_empr = $idEmpresaUafe";
        $usaUafeModal = consulta_string($sqlUafeModal, 'emmpr_uafe_cprov', $oIfxUafe, 'f');
        $oIfxUafe->Free();
    }
}
?>

<? if ($ejecuta) { ?>

    <!--CSS-->
    <!-- <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="datetime/jquery.datetimepicker.css" rel="stylesheet"/>
    <!-- <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" /> -->
    <!-- <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/css/select2.min.css">
	<script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/js/select2.full.min.js"></script>
    <!--Javascript-->

    <!-- 
    <script type="text/javascript" src="datetime/jquery.datetimepicker.js"></script>    
    
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>          
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	-->

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link type="text/css" href="css/style.css" rel="stylesheet" />
    <link type="text/css" href="datetime/jquery.datetimepicker.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" /> -->
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/openlayers/4.6.5/ol.css" type="text/css">

    <!--Javascript-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="datetime/jquery.js"></script>
    <script type="text/javascript" src="datetime/jquery.datetimepicker.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/openlayers/4.6.5/ol.js"></script>


    <!-- Georeferencia -->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/maps/georeferencia.js"></script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8pAD65yn2Qtj_DTowH8xUUkUB6U_SRN0&libraries=places&callback=initMap&force=lite"></script>


    <style>
        #map {
            width: 100%;
            height: 60%;
        }

        #map_drop {
            width: 100%;
            height: 80%;
        }

        #coords {
            width: 500px;
        }

        .uafe-modal-header {
            background-color: #337ab7;
            color: #fff;
        }

        .uafe-metric {
            margin-bottom: 0;
        }

        .d-none {
            display: none;
        }
    </style>



    <script src="media/js/lenguajeusuario_.js"></script>

    <!-- FUNCIONES PARA MANEJO DE PESTA�AS  -->
    <script type="text/javascript">
        function cambiarPestanna(pestannas, pestanna) {
            // Obtiene los elementos con los identificadores pasados.
            pestanna = document.getElementById(pestanna.id);
            //alert(pestanna);
            listaPestannas = document.getElementById(pestannas.id);

            // Obtiene las divisiones que tienen el contenido de las pesta�as.
            cpestanna = document.getElementById('c' + pestanna.id);
            tpestanna = document.getElementById('t' + pestanna.id);
            listacPestannas = document.getElementById('contenido' + pestannas.id);
            i = 0;
            // Recorre la lista ocultando todas las pesta�as y restaurando el fondo
            // y el padding de las pesta�as.
            while (typeof listacPestannas.getElementsByTagName('div')[i] != 'undefined') {
                $(document).ready(function() {
                    if (listacPestannas.getElementsByTagName('div')[i].id == "cpestana1" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana2" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana1" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana2" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana3" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana3" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana4" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana4" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana5" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana5" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana6" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana6" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana7" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana7" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana8" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana8"
                    ) {
                        $(listacPestannas.getElementsByTagName('div')[i]).css('display', 'none');
                    }
                });
                i += 1;
            }
            i = 0;
            while (typeof listaPestannas.getElementsByTagName('li')[i] != 'undefined') {
                $(document).ready(function() {
                    $(listaPestannas.getElementsByTagName('li')[i]).css('background', '');
                    $(listaPestannas.getElementsByTagName('li')[i]).css('padding-bottom', '');
                });
                i += 1;
            }
            $(document).ready(function() {
                // Muestra el contenido de la pesta�a pasada como parametro a la funcion,
                // cambia el color de la pesta�a y aumenta el padding para que tape el
                // borde superior del contenido que esta justo debajo y se vea de este
                // modo que esta seleccionada.
                //alert("recupera");
                $(cpestanna).css('display', '');
                $(tpestanna).css('display', '');
                $(pestanna).css('background', '#3783FE');
                $(pestanna).css('padding-bottom', '2px');
            });
        }
    </script>

    <script language="javascript">
        window.onload = function() {
            cambiarPestanna('pestanas', 'pestana3');
        }
    </script>

    <script>
        function recargar_formulario() {
            $("#form1")[0].reset();
            location.reload();
        }

        function foco(idElemento) {
            document.getElementById(idElemento).focus();
        }

        function consulta_cash(clpv) {
            xajax_consultar_cash(clpv);
        }

        function cargarModalRecalculoUafe() {
            xajax_obtenerConteoProveedoresUafeVencida();
        }

        function mostrarModalRecalculoUafe(conteo) {
            var $boton = $("#btnRecalcularUafe");
            var $resultado = $("#uafeRecalculoResultado");

            $("#uafeTotalVencidos").text(conteo);

            $resultado.addClass('d-none').removeClass('alert-success alert-info alert-danger').text('');
            $boton.prop('disabled', false).html('<i class="fa fa-sync"></i> Recalcular estados UAFE');

            if (parseInt(conteo, 10) > 0) {
                $boton.show();
            } else {
                $boton.hide();
            }

            if (parseInt(conteo, 10) > 0) {
                $("#modalUafeRecalculo").modal("show");
            }
        }

        function ejecutarRecalculoUafe() {
            $("#btnRecalcularUafe")
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Recalculando...');
            xajax_recalcularEstadosUafeProveedores();
        }

        function mostrarResultadoRecalculoUafe(afectados) {
            var $resultado = $("#uafeRecalculoResultado");

            if (parseInt(afectados, 10) > 0) {
                $resultado
                    .removeClass('d-none alert-info alert-danger')
                    .addClass('alert-success')
                    .html('<strong>Proceso completado.</strong><br>Se actualizaron <b>' + afectados + '</b> proveedores a estado <b>PENDIENTE</b>.');
            } else {
                $resultado
                    .removeClass('d-none alert-success alert-danger')
                    .addClass('alert-info')
                    .text('No se encontraron proveedores que requieran actualización.');
            }

            $("#btnRecalcularUafe").hide();
        }

        function edit_del_cash(id, exe, clpv) {

            var ruc = document.getElementById('ruc_' + id).value;
            var cta = document.getElementById('cta_' + id).value;
            var banco = document.getElementById('ban_' + id).value;
            var tip = document.getElementById('tip_' + id).value;
            var int = document.getElementById('int_' + id).value;
            var iden = document.getElementById('iden_' + id).value;
            var mone = document.getElementById('mone_' + id).value;


            //VALIDA VARIBOLE PARA ACTAULZIAR O ELIMINAR 
            if (exe == 1) {
                if (ruc == '') {
                    alert('Ingrese EL DOI tipo');
                    foco('ruc_' + id);
                } else if (cta == '') {
                    alert('Ingrese el numero de cuenta');
                    foco('cta_' + id);
                } else if (banco == '') {
                    alert('Seleccione el banco');

                } else if (tip == '') {
                    alert('Seleccione el tipo de cuenta');
                } else if (iden == '') {
                    alert('Seleccione el DOI tipo');
                } else if (mone == '') {
                    alert('Seleccione la moneda');
                } else {

                    Swal.fire({
                        title: 'Desea Guardar los cambios...??',
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
                            xajax_actualiza_cash(id, ruc, cta, banco, tip, int, iden, mone, exe, clpv);
                        }
                    })

                }
            } else {
                Swal.fire({
                    title: 'Esta seguro de eliminar el registro...??',
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
                        xajax_actualiza_cash(id, ruc, cta, banco, tip, int, iden, mone, exe, clpv);
                    }
                })



            }



        }

        function genera_formulario() {
            document.getElementById('divReporteProdServClpv').innerHTML = '';
            document.getElementById('divReporteDsctLinp').innerHTML = '';


            xajax_genera_formulario_cliente('nuevo', xajax.getFormValues("form1"), <?php echo $codclpv ?>, <?php echo $codpedi ?>);
        }

        function editar(estado) {
            let AC = document.getElementById("AC");
            let SU = document.getElementById("SU");
            let PE = document.getElementById("PE");

            if (estado === undefined || estado === null || estado === "") {
                return;
            }

            const estadoNormalizado = estado;

            AC.checked = (estadoNormalizado === "AC" || estadoNormalizado === "A");
            SU.checked = (estadoNormalizado === "SU" || estadoNormalizado === "S");
            PE.checked = (estadoNormalizado === "PE" || estadoNormalizado === "P");
        }

        function ajustarComboIdentificacion(valor, valorPadded) {
            var select = document.getElementById('identificacion');
            if (!select) {
                return;
            }

            var candidatos = [];
            if (valor !== undefined && valor !== null) {
                candidatos.push(valor.toString());
            }
            if (valorPadded !== undefined && valorPadded !== null) {
                candidatos.push(valorPadded.toString());
            }

            var valorNumerico = parseInt(valorPadded || valor, 10);
            if (!isNaN(valorNumerico)) {
                candidatos.push(valorNumerico.toString());
            }

            for (var i = 0; i < candidatos.length; i++) {
                var candidato = candidatos[i];
                if (!candidato) continue;
                for (var j = 0; j < select.options.length; j++) {
                    if (select.options[j].value == candidato) {
                        select.value = candidato;
                        $(select).trigger('change');
                        if (typeof $(select).trigger === 'function') {
                            $(select).trigger('chosen:updated');
                        }
                        return;
                    }
                }
            }
        }

        function cerrar() {
            parent.CloseAjaxWin();
        }

        function guardar() {
            if (ProcesarFormulario() == true) {
                var codigo = document.getElementById('codigoCliente').value;
                var zona = document.getElementById('zona').value;
                var clpv_cod_sucu = document.getElementById('clpv_cod_sucu').value;

                if (zona == '' || clpv_cod_sucu == '') {
                    alert('Seleccione: Sucursal, Zona');
                } else {
                    if (codigo == '') {
                        xajax_guardar_cliente(<?php echo $codpedi ?>, xajax.getFormValues("form1"));
                    } else {
                        xajax_update_cliente_frame(xajax.getFormValues("form1"));
                    }
                }

            }
        }

        function copiar_nombre() {
            var val = document.getElementById('nombre').value;
            document.getElementById('nombre_comercial').value = val;
        }

        function editarCliente(tip, ruc, nom, com, grpv, dir, tlf, ema, suc, zon, pre, vend, est, lim, dia, pag, gen, det, cod,
            tidu, tclp, trta, pago, tprov, tpago, pais, etu) {
            document.getElementById('identificacion').value = tip;
            document.getElementById('ruc_cli').value = ruc;
            document.getElementById('nombre').value = nom;
            document.getElementById('nombre_comercial').value = com;
            document.getElementById('grupo').value = grpv;
            document.getElementById('direccion_cli').value = dir;
            document.getElementById('telefono_cli').value = tlf;
            document.getElementById('emai_ema_emai').value = ema;
            document.getElementById('dire_op').value = dir;
            document.getElementById('telf_op').value = tlf;
            document.getElementById('mail_op').value = ema;
            document.getElementById('clpv_cod_sucu').value = suc;
            document.getElementById('zona').value = zon;
            if (est != '') {
                document.getElementById(est).checked = true;
            } else {
                document.getElementById('A').checked = true;
            }
            document.getElementById('limite').value = lim;
            document.getElementById('dias_pago').value = dia;
            document.getElementById('pago').value = pag;
            document.getElementById('dsctGeneral').value = gen;
            document.getElementById('dsctDetalle').value = det;
            document.getElementById('codigoCliente').value = cod;

            //datos cliente
            document.getElementById('tipo_cliente').value = tidu;
            document.getElementById('tipo_prove').value = tprov;
            document.getElementById('pago').value = pago;
            document.getElementById('tipo_pago').value = tpago;
            document.getElementById('pais').value = pais;

            if (etu == 1) {
                document.getElementById('contriEspecial').checked = true;
            } else {
                document.getElementById('contriEspecial').checked = false;
            }

            document.getElementById('lgTitulo_frame').innerHTML = 'EDITAR FICHA PROVEEDOR';
            xajax_listaCcli(xajax.getFormValues("form1"));
            xajax_listaProdServCliente(xajax.getFormValues("form1"));
            xajax_listaDsctoLinpCliente(xajax.getFormValues("form1"));
        }

        function copiar_nombre_() {
            var val = document.getElementById('nombre_').value;
            document.getElementById('nombre_comercial').value = val;
        }

        function cargar_zona_lista(cod) {
            xajax_cargar_lista_zona(xajax.getFormValues("form1"), cod);
        }

        function eliminar_lista_zona() {
            var sel = document.getElementById("zona");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_zona(x, i, elemento) {
            var lista = document.form1.zona;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.zona.value = i;
        }

        function consultarReporteCliente() {
            xajax_consultarReporteCliente(xajax.getFormValues("form1"));
        }

        function consultaExistenciaIden() {
            validarDocumento();
            xajax_consultaExistenciaIden(<?php echo $codclpv ?>, <?php echo $codpedi ?>, xajax.getFormValues("form1"));

        }

        function consultaExistenciaIdenGeneral() {
            xajax_consultaExistenciaIden(<?php echo $codclpv ?>, <?php echo $codpedi ?>, xajax.getFormValues("form1"));
        }

        function ingresar_prove_compras(codclpv, pedi, ruc) {
            Swal.fire({
                title: 'Proveedor ya ingresado, Desea actualizar la Orden de Compra...?',
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
                    xajax_ingresar_proveedor_compras(codclpv, ruc, pedi, xajax.getFormValues("form1"));
                }
            })
        }

        function validaTipoProve() {
            xajax_validaTipoProve(xajax.getFormValues("form1"));
        }

        function focoCampo() {
            document.getElementById('ruc_cli').focus();
        }

        function focoCampoCcli() {
            document.getElementById('ruc_ccli').focus();
        }

        function focoBodega() {
            document.getElementById('prodProdServ').focus();
        }

        function focoLinp() {
            document.getElementById('dsctoLinp').focus();
        }

        function autocompletarProdServ(event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var bode = document.getElementById('idBodegaProdServ').value;
                var prod = document.getElementById('prodProdServ').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../ficha_proveedor/buscar_prod.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&producto=' + prod + '&id_bodega=' + bode;
                window.open(pagina, "", opciones);
            }
        }

        function listaProdServCliente() {
            xajax_listaProdServCliente(xajax.getFormValues("form1"));
        }

        function guardarProdServ(tipo, prod) {
            var codProdProdServ = document.getElementById('codProdProdServ').value;
            var cliente = document.getElementById('codigoCliente').value;
            if (cliente == '' && tipo == 1) {
                alert('Seleccione un Cliente...');
            } else if (codProdProdServ == '' && tipo == 1) {
                alert('Seleccione Producto...');
            } else {

                Swal.fire({
                    title: 'Esta seguro de guardar?',
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
                        xajax_guardarProdServ(tipo, prod, xajax.getFormValues("form1"));
                    }
                })



            }
        }

        function eliminarProdServ(clpv, clse) {
            xajax_eliminarProdServ(clpv, clse);
        }

        function modificarProdServ() {
            xajax_modificarProdServ(xajax.getFormValues("form1"));
        }

        function guardarDsctoLinpCliente() {
            var linp = document.getElementById('linp').value;
            var dscto = document.getElementById('dsctoLinp').value;

            if (linp != '' && dscto > 0) {
                xajax_guardarDsctoLinpCliente(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione Linea Inventario y Descuento Mayor a Cero...');
            }

        }

        function eliminarDsctoLinpCliente(clpv, clnp) {
            xajax_eliminarDsctoLinpCliente(clpv, clnp);
        }

        function modificarDsctoLinpCliente() {
            xajax_modificarDsctoLinpCliente(xajax.getFormValues("form1"));
        }

        function listaDsctoLinpCliente() {
            xajax_listaDsctoLinpCliente(xajax.getFormValues("form1"));
        }

        function nuevoFormCcli() {
            document.getElementById('identificacionCcli').value = '';
            document.getElementById('codigoSubCliente').value = '';
            document.getElementById('ruc_ccli').value = '';
            document.getElementById('nombreCcli').value = '';
            document.getElementById('emaiCcli').value = '';
            document.getElementById('telefonoCcli').value = '';
            document.getElementById('direccionCcli').value = '';
            document.getElementById('vendCcli').value = '';
            xajax_listaCcli(xajax.getFormValues("form1"));
        }

        function editarCcli(cod, tip, ruc, nom, dir, tlf, ema, ven) {
            document.getElementById('identificacionCcli').value = tip;
            document.getElementById('codigoSubCliente').value = cod;
            document.getElementById('ruc_ccli').value = ruc;
            document.getElementById('nombreCcli').value = nom;
            document.getElementById('emaiCcli').value = ema;
            document.getElementById('telefonoCcli').value = tlf;
            document.getElementById('direccionCcli').value = dir;
            document.getElementById('vendCcli').value = ven;
        }

        function guardarCcli() {
            var cod = document.getElementById('codigoCliente').value;
            if (cod != '') {
                xajax_guardarCcli(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione Cliente para continuar...!');
            }
        }

        function listaCcliNombre() {
            xajax_listaCcliNombre(xajax.getFormValues("form1"));
        }

        function limpiarCampoCcli() {
            document.getElementById('ccliNombreSearch').value = '';
            xajax_listaCcliNombre(xajax.getFormValues("form1"));
        }

        function cargar_ciudad() {
            var op = document.getElementById('provincia').value;
            if (op == 0) {
                document.getElementById("ciudad").options.length = 0;
            } else {
                xajax_cargar_ciudad(xajax.getFormValues("form1"));
            }
        }

        function limpiar_lista() {
            document.getElementById("ciudad").options.length = 0;
        }

        function anadir_elemento_comun(x, i, elemento) {
            var lista = document.form1.ciudad;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargar_canton() {
            var op = document.getElementById('canton').value;
            if (op == 0) {
                document.getElementById("parroquia").options.length = 0;
            } else {
                xajax_cargar_canton(xajax.getFormValues("form1"));
            }
        }

        function limpiar_lista_canton() {
            document.getElementById("parroquia").options.length = 0;
        }

        function anadir_elemento_comun_canton(x, i, elemento) {
            var lista = document.form1.parroquia;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function completa_ceros(op) {
            xajax_completa_ceros(xajax.getFormValues("form1"), op);
        }

        function seleccionaItem(id) {
            const usaUafe = typeof window.usaUafeEmpresa !== 'undefined' ? window.usaUafeEmpresa : false;

            xajax_seleccionaItem(xajax.getFormValues("form1"), id);

            if (usaUafe) {
                xajax_validarEstadoUAFEProveedor(id);
            }
        }

        function cargarDatosProd(a, b) {
            document.getElementById('codProdProdServ').value = a;
            document.getElementById('prodProdServ').value = b;
        }

        function editarCoa(id) {
            xajax_editarCoa(xajax.getFormValues("form1"), id);
        }

        function nuevoCoa() {
            document.getElementById('codigoCoa').value = '';
            document.getElementById('autUsuario').value = '';
            document.getElementById('autImprenta').value = '';
            document.getElementById('facturaInicio').value = '';
            document.getElementById('facturaFin').value = '';
            document.getElementById('facturaSerie').value = '';
            document.getElementById('estadoATS').value = '';
        }

        function editarCash() {
            var cod = document.getElementById('codigoCliente').value;
            if (cod != '') {
                xajax_editarCash(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione Proveedor para continuar..');
            }
        }

        function ventanaCuentasContables(event, op) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                if (op == 1) {
                    var cuenta = document.getElementById('cuentaAplicada').value;
                } else if (op == 2) {
                    var cuenta = document.getElementById('creditoBienes').value;
                } else if (op == 3) {
                    var cuenta = document.getElementById('creditoServicios').value;
                } else if (op == 4) {
                    var cuenta = document.getElementById('cuentaContable').value;
                }

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1100, height=500, top=300, left=100";
                var pagina = '../ficha_proveedor/cuentas_contables.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cuenta + '&op=' + op;
                window.open(pagina, "", opciones);
            }
        }

        function ventanaRetenciones(event, op) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                if (op == 1) {
                    var rete = document.getElementById('retencionBienes').value;
                } else if (op == 2) {
                    var rete = document.getElementById('retencionServicios').value;
                } else if (op == 3) {
                    var rete = document.getElementById('retencionIvaBienes').value;
                } else if (op == 4) {
                    var rete = document.getElementById('retencionIvaServicios').value;
                }

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1100, height=500, top=300, left=100";
                var pagina = '../ficha_proveedor/retenciones.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&rete=' + rete + '&op=' + op;
                window.open(pagina, "", opciones);
            }
        }

        function guardarPlantilla() {
            var cod = document.getElementById('codigoCliente').value;
            if (cod != '') {
                xajax_guardarPlantilla(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione Proveedor para continuar..');
            }
        }

        function editarPlantilla(a, b, c, d, e, f, g, h, i, j, k, l) {
            document.getElementById('idPlantilla').value = a;
            document.getElementById('codigoPlantilla').value = b;
            document.getElementById('nombrePlantilla').value = c;
            document.getElementById('detallePlantilla').value = d;
            document.getElementById('cuentaAplicada').value = e;
            document.getElementById('creditoBienes').value = f;
            document.getElementById('creditoServicios').value = g
            document.getElementById('retencionBienes').value = h;
            document.getElementById('retencionServicios').value = i;
            document.getElementById('retencionIvaBienes').value = j;
            document.getElementById('retencionIvaServicios').value = k;
        }

        function cuentaAplicada() {
            $("#miModal").modal("show");
            xajax_cuentaAplicada(xajax.getFormValues("form1"));
        }

        function agregarCentroCostos() {
            var cant = document.getElementById('porcentaje').value;

            if (cant > 0 || cant != '') {
                xajax_agrega_modifica_grid(0, '', xajax.getFormValues("form1"), 1, '', 0);
            } else {
                alert('Ingrese cuenta y porcentaje para continuar...!');
            }
        }

        function elimina_detalle(id) {
            xajax_elimina_detalle(id, xajax.getFormValues("form1"));
        }

        function total_grid() {
            xajax_total_grid(xajax.getFormValues("form1"));
        }

        function validarPorcentaje(val) {
            if (val.value > 100) {
                val.value = '';
            }
        }

        function detalleCentroCostos(id) {
            $("#miModal").modal("show");
            xajax_cuentaAplicada(xajax.getFormValues("form1"), id);
        }

        function agregarEntidad(op) {
            var codigoCliente = document.getElementById('codigoCliente').value;
            if (codigoCliente != '') {

                if (op == 4) {
                    var doitip = document.getElementById('tipo_iden').value;
                    var doi = document.getElementById('identificacion_sf').value;
                    var cta = document.getElementById('cuenta').value;
                    var tipcta = document.getElementById('tipoCuenta').value;
                    var banco = document.getElementById('banco').value;
                    var cci = document.getElementById('cod_inter').value;
                    var mone = document.getElementById('mone_cash').value;

                    if (doitip == '') {
                        alert('Seleccione el DOI tipo');

                    } else if (doi == '') {
                        alert('Ingrese el DOI numero');
                        document.getElementById('identificacion_sf').focus();
                    } else if (cta == '') {
                        alert('Ingrese el numero de cuenta');
                        document.getElementById('cuenta').focus();
                    } else if (tipcta == '') {
                        alert('Seleccione el tipo de cuenta');

                    } else if (banco == '') {
                        alert('Seleccione el banco');

                    } else if (mone == '') {
                        alert('Seleccione la moneda');
                    } else {
                        xajax_agregarEntidad(xajax.getFormValues("form1"), op);
                    }
                } else {
                    xajax_agregarEntidad(xajax.getFormValues("form1"), op);
                }

            } else {
                alert('Seleccione Cliente para continuar..!');
            }

        }

        function reporteTelefonoCliente() {
            xajax_reporteTelefonoCliente(xajax.getFormValues("form1"));
        }

        function reporteEmailCliente() {
            xajax_reporteEmailCliente(xajax.getFormValues("form1"));
        }

        function reporteDireCliente() {
            xajax_reporteDireCliente(xajax.getFormValues("form1"));
        }

        function updateEntidad(op) {
            xajax_updateEntidad(xajax.getFormValues("form1"), op);
        }

        function eliminarEntidad(id, op) {
            xajax_eliminarEntidad(xajax.getFormValues("form1"), id, op);
        }

        function editarDireccion(id) {
            xajax_editarDireccion(xajax.getFormValues("form1"), id);
        }


        function validarDocumento_republica() {
            var ruc_cli = document.getElementById("ruc_cli").value;
            var iden = document.getElementById("identificacion").value;

            if (iden == '02') {
                //CEDULA
                var resp = valida_cedula_republica(ruc_cli);
                if (resp == 0) {
                    // CEDULA INVALIDA
                    alert('CEDULA INVALIDA...');
                    document.getElementById("ruc_cli").value = '';
                }
            }

        }


        function valida_cedula_republica(ced) {
            var c = ced.replace(/-/g, '');
            var cedula = c.substr(0, c.length - 1);
            var verificador = c.substr(c.length - 1, 1);
            var suma = 0;
            var cedulaValida = 0;
            if (ced.length < 11) {
                return false;
            }
            for (i = 0; i < cedula.length; i++) {
                mod = "";
                if ((i % 2) == 0) {
                    mod = 1
                } else {
                    mod = 2
                }
                res = cedula.substr(i, 1) * mod;
                if (res > 9) {
                    res = res.toString();
                    uno = res.substr(0, 1);
                    dos = res.substr(1, 1);
                    res = eval(uno) + eval(dos);
                }
                suma += eval(res);
            }
            el_numero = (10 - (suma % 10)) % 10;
            if (el_numero == verificador && cedula.substr(0, 3) != "000") {
                cedulaValida = 1;
            } else {
                cedulaValida = 0;
            }
            return cedulaValida;
        }

        function verMapaContr(id) {
            xajax_FooterMap(id, 1);
            $("#ModalMapa").modal("show");
            var lat = document.getElementById("latitud_tmp").value
            var lont = document.getElementById("longitud_tmp").value
            xajax_verifica_tipo_mapa(lat, lont, xajax.getFormValues("form1"));
        }

		/*function agregarArchivo() {
			//alert("agregarArchivo funciona");
            var titulo = $("#titulo").val();
            var archivo = $("#archivo").val();
            if (titulo != '' && archivo != '') {
                xajax_agrega_modifica_gridAdj(0, xajax.getFormValues("form1"), '', '');
            } else {
                alert("Ingrese Titulo, Adjunto para continuar...!");
            }

        }*/
        //----------------------------------------------------------------
        //INICIO FUNCION AGREGAR ARCHIVO AL DAR CLCIK EN AGREGAR 
        //----------------------------------------------------------------
        function agregarArchivo() {

            var tipo = $("#tipo_adj").val();// 0=NORMAL, 1=UAFE
            var titulo = $("#titulo").val();
            var archivo = $("#archivo").val();
            var docUafe = $("#id_archivo_uafe").val();

            // Validación por tipo
            if (tipo == "0") { // NORMAL
                if (titulo.trim() == "") {
                    alert("Debe ingresar un Título para el documento.");
                    return;
                }
            } else if (tipo == "1") { // UAFE
                if (docUafe == "" || docUafe == null) {
                    alert("Debe seleccionar el Documento UAFE.");
                    return;
                }
            }

            // Validación de archivo
            if (archivo == "") {
                alert("Debe seleccionar un archivo.");
                return;
            }

            // Llamar XAJAX normalmente
            xajax_agrega_modifica_gridAdj(0, xajax.getFormValues("form1"), '', '');
        }
        //----------------------------------------------------------------
        //FIN FUNCION AGREGAR ARCHIVO AL DAR CLCIK EN AGREGAR 
        //----------------------------------------------------------------

        function eliminarArchivoUAFE(id_uafe, id_clpv, id_adj) {
              Swal.fire({
                title: 'Estas seguro que deseas eliminar este archivo',
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
                    xajax_eliminarArchivoUAFE(id_uafe, id_clpv, id_adj);
                }
            });
        }

        function guardarAdjuntos() {
            var cliente = $("#codigoCliente").val();
            if (cliente != '') {
                xajax_guardarAdjuntos(xajax.getFormValues("form1"));
            } else {
                alert("Seleccione Cliente para continuar...!");
            }
        }

        function consultarAdjuntos() {
            var cliente = $("#codigoCliente").val();
            if (cliente != '') {
                xajax_consultarAdjuntos(xajax.getFormValues("form1"));
            } else {
                alert("Seleccione Cliente para continuar...!");
            }
        }

        // Consultar documentos UAFE del proveedor seleccionado
        function consultarAdjuntosUafe() {
            console.log("CLICK: ejecutar UAFE");
            console.log("Cliente =", $("#codigoCliente").val());

            xajax_consultarAdjuntosUafe(xajax.getFormValues("form1"));
        }

        function dowloand(ruta) {
            document.location = "../oportunidades/dowloand.php?ruta=" + ruta;
        }

        function eliminar_adj(id) {

            Swal.fire({
                title: 'Estas seguro que deseas borrar este Registro',
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
                    xajax_eliminar_adj(id, xajax.getFormValues("form1"));
                }
            });
        }

        function guardarAdjuntosUAFE() {
            let id_clpv = document.getElementById("codigoCliente").value;
            xajax_guardarAdjuntosUAFE(id_clpv);
        }

        function notificarDocumentosUAFE() {
            let id_clpv = document.getElementById("codigoCliente").value;
            if (id_clpv !== '' && id_clpv !== '0') {
                enviar_mail();
            } else {
                alert("Seleccione Cliente para continuar...!");
            }
        }

        function toggleNotificarUafe(mostrar) {
            var fila = document.getElementById('filaNotificarUafe');
            if (!fila) {
                return;
            }
            fila.style.display = mostrar ? '' : 'none';
        }

    </script>

    <script>
        function cambiarTipoAdjunto() {

            var tipo = $("#tipo_adj").val();

            if (tipo == "1") {   // UAFE
                $("#titulo").prop("disabled", true);
                $("#fila_uafe").show();
            } else {             // NORMAL
                $("#titulo").prop("disabled", false);
                $("#fila_uafe").hide();
            }
        }

        $(document).ready(function(){
            cambiarTipoAdjunto();
            $("#tipo_adj").on('change', cambiarTipoAdjunto);
        });

        function cambiarEstadoUafe(id_uafe, id_clpv, checked) {
            var valor = checked ? 1 : 0;
            xajax_cambiarEstadoUafe(id_uafe, id_clpv, valor);
        }
    </script>


    <script>
        var marker; //variable del marcador
        var coords = {}; //coordenadas obtenidas con la geolocalización

        //Funcion principal
        initMap = function() {
            //usamos la API para geolocalizar el usuario
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    coords = {
                        lng: position.coords.longitude,
                        lat: position.coords.latitude
                    };
                    console.log('las coordenadas 1522', coords);


                    //setMapa(coords); //pasamos las coordenadas al metodo para crear el mapa    ç
                    //console.log(coords,'las coordenadas -array');
                    //alert(coords.lat);

                    //coords.lat = document.getElementById("latitud_tmp").value;
                    //coords.lat = document.getElementById("la").value;
                    document.getElementById("latitud_tmp").value = coords.lat;
                    document.getElementById("longitud_tmp").value = coords.lng;
                    document.getElementById("la").value = coords.lat;
                    document.getElementById("lon").value = coords.lng;
                },
                function(error) {
                    console.log(error, 'el error');
                });
        }

        function setMapa(coords) {
            console.log(coords, 'dlll');
            //Se crea una nueva instancia del objeto mapa
            var map = new window.google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: new window.google.maps.LatLng(coords.lat, coords.lng),

            });

            document.getElementById("latitud_tmp").value = coords.lat;
            document.getElementById("longitud_tmp").value = coords.lng;

            console.log('latitud', coords.lat);
            //coords.lat = document.getElementById("latitud_tmp").value;


            //Creamos el marcador en el mapa con sus propiedades
            //para nuestro obetivo tenemos que poner el atributo draggable en true
            //position pondremos las mismas coordenas que obtuvimos en la geolocalización
            marker = new google.maps.Marker({
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                position: new google.maps.LatLng(coords.lat, coords.lng),

            });
            //agregamos un evento al marcador junto con la funcion callback al igual que el evento dragend que indica 
            //cuando el usuario a soltado el marcador
            marker.addListener('click', toggleBounce);

            marker.addListener('dragend', function(event) {
                //escribimos las coordenadas de la posicion actual del marcador dentro del input #coords
                //document.getElementById("coords").value = this.getPosition().lat()+","+ this.getPosition().lng();
                document.getElementById("latitud_tmp").value = this.getPosition().lat();
                document.getElementById("longitud_tmp").value = this.getPosition().lng();
            });
        }

        //callback al hacer clic en el marcador lo que hace es quitar y poner la animacion BOUNCE
        function toggleBounce() {
            if (marker.getAnimation() !== null) {
                marker.setAnimation(null);
            } else {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }
        }

        // Carga de la libreria de google maps 

        // PONER POSICION DE DROP
        function downloadUrl(url, callback) {
            var request = window.ActiveXObject ?
                new ActiveXObject('Microsoft.XMLHTTP') :
                new XMLHttpRequest;

            request.onreadystatechange = function() {
                if (request.readyState == 4) {
                    request.onreadystatechange = doNothing;
                    callback(request, request.status);
                }
            };

            request.open('GET', url, true);
            request.send(null);
        }

        function initMapDrop(lat, long) {
            map = new google.maps.Map(document.getElementById('map_drop'), {
                center: new google.maps.LatLng(lat, long),
                zoom: 13
            });

            var infoWindow = new google.maps.InfoWindow;

            // Change this depending on the name of your PHP or XML file
            downloadUrl('xml/data.xml', function(data) {
                var xml = data.responseXML;
                var markers = xml.documentElement.getElementsByTagName('marker');
                Array.prototype.forEach.call(markers, function(markerElem) {
                    var name = markerElem.getAttribute('name');
                    var drop = markerElem.getAttribute('drop');
                    var type = markerElem.getAttribute('type');
                    var capa = markerElem.getAttribute('capacidad');
                    var libre = markerElem.getAttribute('libre');
                    var id = markerElem.getAttribute('id');
                    var point = new google.maps.LatLng(
                        parseFloat(markerElem.getAttribute('lat')),
                        parseFloat(markerElem.getAttribute('lng')));


                    var infowincontent = "<div class='col-md-12'>" +
                        "   <div class='text-primary'>DROP:<strong>" + drop + "</strong></div>" +
                        "   <div>CAPACIDAD: " + capa + "</div>" +
                        "   <div>LIBRE: " + libre + " </div>" +
                        "   <div onclick='drop_choice(" + id + ");' title='Haga Click para Seleccionar'>" +
                        "       <button type='button' class='btn btn-sm btn-primary' onclick='drop_choice(" + id + ");'  style='width:100%'>" +
                        "       <span class='glyphicon glyphicon-ok'></span>" +
                        "       </button>" +
                        "   </div>" +
                        "   <div>.</div>" +
                        "</div>";

                    var image = "drop.jpeg";

                    var marker = new google.maps.Marker({
                        map: map,
                        position: point,
                        icon: image
                    });

                    marker.addListener('click', function() {
                        infoWindow.setContent(infowincontent);
                        infoWindow.open(map, marker);
                    });
                });
            });
        }

        function centrarMapa(latitude, longitude) {
            map.setCenter({
                lat: latitude,
                lng: longitude
            });
            map.setZoom(18);
        }

        function doNothing() {}


        function sendcoord() {

            var lat = document.getElementById("latitud_tmp").value
            var lont = document.getElementById("longitud_tmp").value
            lat = parseFloat(lat);
            lont = parseFloat(lont);

            //Creamos el punto a partir de la latitud y longitud de una dirección:
            var point = new google.maps.LatLng(lat, lont);

            //Configuramos las opciones indicando zoom, punto y tipo de mapa
            var myOptions = {
                zoom: 15,
                center: point,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            //Creamos el mapa y lo asociamos a nuestro contenedor
            var map = new google.maps.Map(document.getElementById("map"), myOptions);

            //Mostramos el marcador en el punto que hemos creado
            var marker = new google.maps.Marker({
                position: point,
                map: map,
                title: "Nombre empresa - Calle Balmes 192, Barcelona"
            });
        }

        function eliminarCoa(coa_cod_coa) {
            Swal.fire({
                title: 'Estas seguro que deseas borrar este reporte de autorizacion ?',
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
                    xajax_eliminarCoa(coa_cod_coa, xajax.getFormValues("form1"));
                }
            });
        }

        function listaCcli() {
            xajax_listaCcli(xajax.getFormValues("form1"));
        }

        function autocompletar_infomacion() {

            var numero = document.getElementById('ruc_cli').value;
            var tipo_identificacion = $("#identificacion").val();

            if (numero != '' && tipo_identificacion != '') {
                jsShowWindowLoad();
                xajax_autocompletar_infomacion_cliente(tipo_identificacion, numero);
            }
        }

        // --------------------------------------------------------------------------------------
        // MAPA DE STREET MAPS
        // --------------------------------------------------------------------------------------

        var longitud_ad = 0;
        var latitud_ad = 0;
        var contador_ingreso = 1;

        function open_modal_open_street_maps(longitud, latitud) {
            $("#mostrarModalDireccion").modal("show");
            initMapStreet(longitud, latitud);
            this.longitud_ad = longitud;
            this.latitud_ad = latitud;
            this.contador_ingreso = 1;
            reloadOpenStreet(longitud, latitud)
        }

        function initMapStreet(longitud, latitud) {

            document.getElementById('map').innerHTML = "";

            var lat = latitud;
            var lng = longitud;

            if (!lat || !lng) {
                lat = -0.1357444;
                lng = -78.4780243;
            } else {
                lat = parseFloat(lat);
                lng = parseFloat(lng);
            }


            // Crear una capa de mapa base usando OpenStreetMap
            var baseLayer = new ol.layer.Tile({
                source: new ol.source.OSM()
            });

            // Crear un marcador
            var marker = new ol.Feature({
                geometry: new ol.geom.Point(ol.proj.fromLonLat([lng, lat])) // Coordenadas de Madrid, España
            });

            // Estilo del marcador
            marker.setStyle(new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 1],
                    src: 'https://img.icons8.com/ultraviolet/40/000000/marker.png' // URL del icono del marcador
                })
            }));

            // Crear una capa vectorial para el marcador
            var vectorSource = new ol.source.Vector({
                features: [marker]
            });
            var markerVectorLayer = new ol.layer.Vector({
                source: vectorSource
            });



            var map = new ol.Map({
                target: 'map',
                layers: [baseLayer, markerVectorLayer],
                view: new ol.View({
                    center: ol.proj.fromLonLat([lng, lat]),
                    zoom: 18
                })
            });

            // Evento de clic en el mapa para obtener las coordenadas
            map.on('click', function(evt) {

                vectorSource.clear();

                var coordenadas = evt.coordinate; // Coordenadas del clic
                console.log('Coordenadas:', ol.proj.toLonLat(coordenadas)); // Coordenadas en lon/lat
                var longitud_temp = ol.proj.toLonLat(coordenadas)[0];
                var latitud_temp = ol.proj.toLonLat(coordenadas)[1];
                document.getElementById('longitud_tmp').value = longitud_temp;
                document.getElementById('latitud_tmp').value = latitud_temp;


                var marker = new ol.Feature({
                    geometry: new ol.geom.Point(coordenadas)
                });

                // Estilo del marcador
                marker.setStyle(new ol.style.Style({
                    image: new ol.style.Icon({
                        anchor: [0.5, 1],
                        src: 'https://img.icons8.com/ultraviolet/40/000000/marker.png' // URL del icono del marcador
                    })
                }));

                vectorSource.addFeature(marker);

            });


        }

        function reloadOpenStreet(longitud, latitud) {
            const intervalo = setInterval(miFuncion, 1000);
        }

        function miFuncion() {
            if (contador_ingreso < 2) {
                console.log('dentro de intervalo ' + this.longitud_ad + ' - ' + this.latitud_ad);
                initMapStreet(this.longitud_ad, this.latitud_ad);
                this.contador_ingreso++;
            }
        }

        // Abre el modal
        
        function enviar_mail(){
			document.getElementById('miModal').innerHTML = '';
			$("#miModal").modal("show");
			xajax_enviar_mail(xajax.getFormValues("form1"));
		}
		
		function enviaEmail(correo_destino){
			xajax_enviaEmail(xajax.getFormValues("form1"), correo_destino);
		}

        // --------------------------------------------------------------------------------------
        // FIN MAPA DE STREET MAPS
        // --------------------------------------------------------------------------------------


        function guardar_ubicacion_clpv() {

            var id_clpv = document.getElementById('codigoCliente').value;
            if (id_clpv) {
                Swal.fire({
                    title: 'Estas seguro que deseas guardar la ubicacion de este proveedor ?',
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
                        xajax_guardar_ubicacion_clpv(xajax.getFormValues("form1"));
                    }
                });
            } else {
                alert('Deben seleccionar Proveedor luego de guardarlo para ubicarlo en el mapa');
            }

        }

    </script>


    <!--DIBUJA FORMULARIO FILTRO-->

    <body onload='javascript:cambiarPestanna(pestanas, pestana1);'>
        <div class="row">
            <form id="form1" name="form1" action="javascript:void(null);">
                <div class="row">
                </div>

                <div class="col-md-5">
                    <input class="form-control" type="hidden" id="lon" name="lon">
                    <input class="form-control" type="hidden" id="la" name="la">
                </div>

                <div class="row">
                </div>

                <div id="pestanas">
                    <ul id="lista">
                        <li id="pestana1"><a href='javascript:cambiarPestanna(pestanas,pestana1);'>INFORMACION</a></li>
                        <li id="pestana2"><a href='javascript:cambiarPestanna(pestanas,pestana2);'>CONTACTOS</a></li>
                        <li id="pestana3"><a href='javascript:cambiarPestanna(pestanas,pestana3);'>DATO FISCAL</a></li>
                        <li id="pestana4"><a href='javascript:cambiarPestanna(pestanas,pestana4);'>CASH MANAGEMENT</a></li>
                        <li id="pestana5"><a href='javascript:cambiarPestanna(pestanas,pestana5);'>PLANILLA</a></li>
                        <li id="pestana6"><a href='javascript:cambiarPestanna(pestanas,pestana6);'>PRODUCTOS</a></li>
                        <li id="pestana7"><a href='javascript:cambiarPestanna(pestanas,pestana7);'>LINEA DE NEGOCIO</a></li>
                        <li id="pestana8"><a href='javascript:cambiarPestanna(pestanas,pestana8);'>ADJUNTOS</a></li>
                    </ul>
                </div>

                <div id="contenidopestanas">
                    <div id="cpestana1"></div>
                    <div id="tpestana1" class="main-row col-md-12">
                        <div class="col-md-5">
                            <div class="table responsive" style="width: 100%;">
                                <table id="example" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                                    <thead>
                                        <tr>
                                            <td colspan="5" class="bg-primary">REPORTE DE PROVEEDORES</td>
                                        </tr>
                                        <tr class="info">
                                            <td>Codigo</td>
                                            <td>Identificacion</td>
                                            <td>Nombre</td>
                                            <td>Editar</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7" id="divFormularioCli" align="center"></div>
                    </div>
                    <div id="cpestana2"></div>
                    <div id="tpestana2" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioContactoTelf" class="col-md-7"></div>
                        <div id="divFormularioContactoEmai" class="col-md-5"></div>
                        <div id="divFormularioContactoDire" class="col-md-12"></div>
                        <div class="row" align="center" style="display:  none;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d15959.224792031337!2d-78.4805025!3d-0.1412389!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2sec!4v1515358554001" width="800" height="450" frameborder="0" style="width: 90%; border:0;" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div id="cpestana3"></div>
                    <div id="tpestana3" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioDatos" align="center" width="100%"></div>
                        <div id="divReporteDatos" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana4"></div>
                    <div id="tpestana4" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioCash" align="center" width="100%"></div>
                        <div id="divReporteCash" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana5"></div>
                    <div id="tpestana5" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioPlantilla" align="center" width="100%"></div>
                        <div id="divReportePlantilla" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana6"></div>
                    <div id="tpestana6" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioProdServClpv" align="center" width="100%"></div>
                        <div id="divReporteProdServClpv" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana7"></div>
                    <div id="tpestana7" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioDsctLinp" align="center" width="100%"></div>
                        <div id="divReporteDsctLinp" align="center" width="100%"></div>
                    </div>

                    <div id="cpestana8"></div>
                    <div id="tpestana8" style="width:99%; height:98%; overflow: scroll;">
                        <div class="col-md-6">
                            <div id="divFormularioAdjuntos" align="center" width="100%"></div>
                            
                            <div id="gridArchivos" align="center" width="100%"></div>
                        </div>
                        <div class="col-md-6">
                            <div id="divReporteAdjuntos" align="center" width="100%"></div>
                        </div>

                        <div class="col-md-6">
                            <!-- AQUI SE CARGARÁ LA TABLA UAFE -->
                            <div id="divReporteAdjuntosUafe" align="center" width="100%"></div>
                        </div>

                    </div>

                </div>

                <div style="width: 100%;">
                    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                </div>

                <div class="modal fade" id="modalUafeRecalculo" tabindex="-1" role="dialog" aria-labelledby="modalUafeRecalculoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header uafe-modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true" style="color: #fff;">&times;</span>
                                </button>
                                <h4 class="modal-title" id="modalUafeRecalculoLabel">
                                    <i class="fa fa-shield"></i>
                                    Proceso GLOBAL – Validación normativa UAFE
                                </h4>
                            </div>
                            <div class="modal-body">
                                <div class="row text-center" style="margin-bottom: 15px;">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="panel panel-danger uafe-metric">
                                            <div class="panel-body">
                                                <h3 class="text-danger" id="uafeTotalVencidos">0</h3>
                                                <small>Proveedores activos con documentos UAFE vencidos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <strong>El recálculo cambiará el estado de estos proveedores a Pendiente hasta que regularicen su documentación.</strong>
                                </div>

                                <div id="uafeRecalculoResultado" class="alert d-none" style="margin-bottom: 0;"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button type="button" class="btn btn-primary" id="btnRecalcularUafe" onclick="ejecutarRecalculoUafe();">
                                    <i class="fa fa-sync"></i> Recalcular estados UAFE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ModalMapa" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" style="width: 90%">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">MAPA</h4>
                            </div>
                            <div class="modal-body">
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <div class="col-md-12">
                                            <div id="map" name="map"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label for="longitud_tmp">* Longitud:</label>
                                            <input type="text" class="form-control input-sm" id="longitud_tmp" name="longitud_tmp" style="text-align:right" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="latitud_tmp">* Latitud:</label>
                                            <input type="text" class="form-control input-sm" id="latitud_tmp" name="latitud_tmp" style="text-align:right" />
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer" style="text-align: center">
                                <button type="button" class="btn btn-success" onclick="guardar_ubicacion_clpv();">Guardar Ubicacion</button>
                                <button type="button" class="btn btn-primary" onclick="verMapaContr(id);">Ubicar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL PARA ENVIAR CORREO -->
                    <div class="col-md-12">
                        <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="myModalLabel">Listado de Mensajes SRI</h4>
                                    </div>
                                    <div class="modal-body">
                                        <table id="example" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                                            <thead>
                                                <tr class="primary">
                                                    <th style="width: 10%;">Codigo</th>
                                                    <th style="width: 45%;">Mensaje</th>
                                                    <th style="width: 45%;">Detalle</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>  
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                                     


            </form>

      
        </div>
    </body>

    <script src="js/uafe_bloqueo.js"></script>

    <?php if ($usaUafeModal == 't' || $usaUafeModal == 1) { ?>
        <script>
            window.addEventListener('load', function() {
                cargarModalRecalculoUafe();
            });
        </script>
    <?php } ?>


    <script>
        genera_formulario(); /*genera_detalle();genera_form_detalle();*/
    </script>

    <?php
        if ($usaUAFE == 't') {
            echo "<script> habilitarEstadoProveedor(true); </script>";
        } else {
            echo "<script> habilitarEstadoProveedor(false); </script>";
        }
    ?>
    <script src="js/google_maps.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8pAD65yn2Qtj_DTowH8xUUkUB6U_SRN0&callback=initMap"></script>

    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>
