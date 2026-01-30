<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>
    
    <!--CSS--> 
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" />
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">

    <!--Javascript--> 
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>          
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>    
    <script src="media/js/lenguajeusuario_empr.js"></script>   

    <!-- FUNCIONES PARA MANEJO DE PESTAÑAS  -->
    <script type="text/javascript">



        function cambiarPestanna(pestannas, pestanna) {

            for(i = 1; i<= 7; i++){
                document.getElementById('pestana'+i).className = "";
            }

            // Obtiene los elementos con los identificadores pasados.
            pestanna = document.getElementById(pestanna.id);
            
            //alert(pestanna);
            listaPestannas = document.getElementById(pestannas.id);

            // Obtiene las divisiones que tienen el contenido de las pestañas.
            cpestanna = document.getElementById('c' + pestanna.id);
            tpestanna = document.getElementById('t' + pestanna.id);
            listacPestannas = document.getElementById('contenido' + pestannas.id);
            document.getElementById(pestanna.id).className = "active";
            i = 0;
            // Recorre la lista ocultando todas las pestañas y restaurando el fondo
            // y el padding de las pestañas.
            while (typeof listacPestannas.getElementsByTagName('div')[i] != 'undefined') {
                $(document).ready(function() {
                    if (listacPestannas.getElementsByTagName('div')[i].id == "cpestana1"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana1"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana2"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana2"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana3"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana3"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana4"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana4"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana5"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana5"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana6"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana6"
                            || listacPestannas.getElementsByTagName('div')[i].id == "cpestana7"
                            || listacPestannas.getElementsByTagName('div')[i].id == "tpestana7")
                    {
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
                // Muestra el contenido de la pestaña pasada como parametro a la funcion,
                // cambia el color de la pestaña y aumenta el padding para que tape el
                // borde superior del contenido que esta justo debajo y se vea de este
                // modo que esta seleccionada.
                //alert("recupera");
                $(cpestanna).css('display', '');
                $(tpestanna).css('display', '');
                $(pestanna).css('padding-bottom', '2px');
            });
        }
    </script>

    
    <script>

    function habilita_cta(){
            var jInput = document.getElementById('empr_det_cta');
                if(document.getElementById("empr_cta_sn").checked){
                    jInput.disabled = false;
                }
                else{
                    document.getElementById("empr_det_cta").value='';
                    jInput.disabled = true;
                }
        }

        function habilita_cta_edit(est){
            var jInput = document.getElementById('empr_det_cta');
                if(est=='S'){
                    jInput.disabled = false;
                }
                else{
                    document.getElementById("empr_det_cta").value='';
                    jInput.disabled = true;
                }
        }

        function habilita_rinf(){
            var jInput = document.getElementById('empr_det_rinf');
                if(document.getElementById("empr_rinf_sn").checked){
                    jInput.disabled = false;
                }
                else{
                    document.getElementById("empr_det_rinf").value='';
                    jInput.disabled = true;
                }
        }

        function habilita_rinf_edit(est){
            var jInput = document.getElementById('empr_det_rinf');
                if(est=='S'){
                    jInput.disabled = false;
                }
                else{
                    document.getElementById("empr_det_rinf").value='';
                    jInput.disabled = true;
                }
        }

function carga_correo(){

var jInput = document.getElementById('empr_ema_test');
        if(document.getElementById("empr_ema_sn").checked){
            jInput.disabled = false;
        }
        else{
            document.getElementById("empr_ema_test").value='';
            jInput.disabled = true;
        }

}

        function genera_formulario() {
            document.getElementById('divReporteProdServClpv').innerHTML = '';
            document.getElementById('divReporteDsctLinp').innerHTML = '';
            xajax_genera_formulario('nuevo', xajax.getFormValues("form1"));
        }

        function cerrar() {
            parent.CloseAjaxWin();
        }

        function guardar() {
            if (ProcesarFormulario() == true) {
                var codigo = document.getElementById('empr_cod_empr').value;
                if(codigo == ''){
                    xajax_guardar_tran(xajax.getFormValues("form1"));
                }else{
                    xajax_update_tran_frame(xajax.getFormValues("form1"));
                }
            }
        }
        

        function copiar_nombre() {
            var val = document.getElementById('nombre').value;
            document.getElementById('nombre_comercial').value = val;
        }

     
        function copiar_nombre_() {
            var val = document.getElementById('nombre_').value;
            document.getElementById('nombre_comercial').value = val;
        }
		
		
        function seleccionaItem( id){
            xajax_seleccionarTran(xajax.getFormValues("form1"),  id);
        }
        
        function limpiar_lista_by_id(id_lista) {
            var element = document.getElementById(id_lista);
            removeOptions(element)
            element.options.length = 0;
            
        }
        function removeOptions(selectElement) {
            var i, L = selectElement.options.length - 1;
            for(i = L; i >= 0; i--) {
                selectElement.remove(i);
            }
        }

        function anadir_elemento_comun_canton(x, i, elemento) {
            var lista = document.form1.empr_cod_cant;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
        function anadir_elemento_comun(x, i, elemento) {
            var lista = document.form1.empr_cod_ciud;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function anadir_elemento_comun_parroquia(x, i, elemento) {
            var lista = document.form1.empr_cod_parr;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		
		// function cargar_prov(){
		// 	xajax_cargar_prov( xajax.getFormValues("form1") );
		// }
        function cargar_cant(){
			xajax_cargar_cant( xajax.getFormValues("form1") );
			xajax_cargar_ciud( xajax.getFormValues("form1") );
		}
        function cargar_ciud(){//cuando carga la cant
			xajax_cargar_ciud( xajax.getFormValues("form1") );
			xajax_cargar_parr( xajax.getFormValues("form1") );
		}
        function cargar_parr(){
			xajax_cargar_parr( xajax.getFormValues("form1") );
		}

        function sincronizar_base_script(){
			xajax_sincronizar_base( xajax.getFormValues("form1") );
		}

        function consulta_infoxml(){
            var empr = document.getElementById('empr_cod_empr').value;
            
            if(empr == ''){
                alert("Seleccione la Empresa");
            }
            else{
                xajax_consultar_infoxml(empr);
            }
        }

        function consulta_infopdf(){
            var empr = document.getElementById('empr_cod_empr').value;
            
            if(empr == ''){
                alert("Seleccione la Empresa");
            }
            else{
                xajax_consultar_infopdf( empr); 
            }
        }

        function ingresa_detxml(){
            var empr = document.getElementById('empr_cod_empr').value;
            var titulo = document.getElementById('empr_tit_xml').value;
            var detalle = document.getElementById('empr_det_xml').value;

            if(empr == ''){
                alert("Se debe seleccionar o crear la Empresa");
            }

            else{

                if(titulo==''){
                    alert('Ingrese el titulo');
                    foco('empr_tit_xml');
                }
                else if(detalle==''){
                    alert('Ingrese el detalle');
                    foco('empr_det_xml');
                }
                else{
                    xajax_ingresar_detxml( empr,xajax.getFormValues("form1")); 
                }
                
            }
        }



        function ingresa_detpdf(){
            var empr = document.getElementById('empr_cod_empr').value;
            var titulo = document.getElementById('empr_tit_pdf').value;
            var detalle = document.getElementById('empr_det_pdf').value;
            var formato = document.getElementById('empr_tip_pdf').value;

            if(empr == ''){
                alert("Se debe seleccionar o crear la Empresa");
            }

            else{

                if(titulo==''){
                    alert('Ingrese el titulo');
                    foco('empr_tit_pdf');
                }
                else if(detalle==''){
                    alert('Ingrese el detalle');
                    foco('empr_det_pdf');
                }
                else if(formato==''){
                    alert('Ingrese el tipo de formato');
                   
                }
                else{
                    xajax_ingresar_detpdf( empr,xajax.getFormValues("form1")); 
                }
                
            }
        }

        function edita_eli_detxml(id,exe){

            
            var titulo = document.getElementById('tit_'+id).value;
            var detalle = document.getElementById('det_'+id).value;
            var orden = document.getElementById('ord_'+id).value;
            var estado = document.getElementById('est_'+id).checked;

            var xml = document.getElementById('xml_'+id).checked;
            if(xml){
                xml='S';
            }
            else{
                xml='N';
            }
                
            var pdf = document.getElementById('pdf_'+id).checked;
            if(pdf){
                pdf='S';
            }
            else{
                pdf='N';
            }

            if(exe==1){
                if(estado){
                    estado='S';
                }
                else{
                    estado='N';
                }

            
            if(titulo==''){
                alert('Ingrese el titulo');
                foco('tit_'+id);
            }
            else if(detalle==''){
                alert('Ingrese el detalle');
                foco('det_'+id);
            }
            else if(orden<=0){
                alert('Valor no permitido');
                foco('ord_'+id);
            }
            else{

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
                                xajax_guardar_detxml(id,titulo, detalle, orden, estado,exe, xml, pdf);                    
                            }
            })

            }
            }
            else{
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
                                xajax_guardar_detxml(id,titulo, detalle, orden, estado,exe, xml, pdf);                    
                            }
            })

                

            }

           

        }


         function edita_eli_detpdf(id,exe){

            
            var titulo = document.getElementById('tit_'+id).value;
            var detalle = document.getElementById('det_'+id).value;
            var formato = document.getElementById('tip_'+id).value;
            var orden = document.getElementById('ord_'+id).value;
            var estado = document.getElementById('est_'+id).checked;

            if(exe==1){
                if(estado){
                estado='S';
            }
            else{
                estado='N';
            }

            
            if(titulo==''){
                alert('Ingrese el titulo');
                foco('tit_'+id);
            }
            else if(detalle==''){
                alert('Ingrese el detalle');
                foco('det_'+id);
            }
            else if(formato==''){
                alert('Ingrese el formato');
                
            }
            else if(orden<=0){
                alert('Valor no permitido');
                foco('ord_'+id);
            }
            else{

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
                                xajax_guardar_detpdf(id,titulo, detalle, formato, orden, estado,exe);                    
                            }
            })

            }
            }
            else{
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
                                xajax_guardar_detpdf(id,titulo, detalle, formato, orden, estado,exe);                    
                            }
            })

                

            }

           

        }

        function recargar_formulario(){
           $("#form1")[0].reset();
            location.reload();
        }

        function foco(idElemento) {
            document.getElementById(idElemento).focus();
        }


function validar_firma(){
    var empr = document.getElementById('empr_cod_empr').value;
    //var clave = document.getElementById('empr_pass_token').value;
    var chk = document.getElementById("empr_ws_sri_sn");
    chk.value = chk.checked ? "S" : "N";

    if(empr == ''){
        alert("Seleccione la Empresa");
    }
    else{
        xajax_validar_firma(xajax.getFormValues("form1"));
    }
}
		
    </script>

    <!--DIBUJA FORMULARIO FILTRO-->
    <!--DIBUJA FORMULARIO FILTRO-->
    <body onload='javascript:cambiarPestanna(pestanas, pestana1);'>
        <div class="row">
            <form id="form1" name="form1" action="javascript:void(null);">
                <div id="pestanas">
                    <ul id="lista" class="nav nav-tabs bg-info">
                        <li role="presentation" id="pestana1"><a href='javascript:cambiarPestanna(pestanas,pestana1);'>EMPRESA</a></li>
                        <li role="presentation" id="pestana2" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana2);'>DATOS</a></li>
                        <li role="presentation" id="pestana3" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana3);'>CONTACTOS</a></li>
                        <li role="presentation" id="pestana4" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana4);'>SUBCLIENTE</a></li>
                        <li role="presentation" id="pestana5" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana5);'>CONTRATO</a></li>
                        <li role="presentation" id="pestana6" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana6);'>SERVICIOS-PRODUCTOS</a></li>
                        <li role="presentation" id="pestana7" style='display:none;'><a href='javascript:cambiarPestanna(pestanas,pestana7);'>LINEA DE NEGOCIO</a></li>
                    </ul>
                </div>
                <div class="col-md-3 col-md-offset-9" align="right">
                    <h6 class="text-primary fecha_letra" id="informacionCliente"></h6>
                </div>
                <div id="contenidopestanas">
                    <div id="cpestana1"></div>
                    <div id="tpestana1" class="main-row col-md-12">
                        <div class="col-md-5">
                            <div class="table responsive" style="width: 100%;">
                                <table id="example" class="table table-striped table-bordered table-hover table-condensed"  style="width: 100%;" align="center">
                                    <thead>
                                        <tr>
                                            <td colspan="6" class="bg-primary">REPORTE DE EMPRESA</td>
                                        </tr>
                                        <tr class="info">
                                            <td>Codigo</td>
                                            <td>Sucursal</td>
                                            <td>Direccion</td>
											<td>Sigla</td>
											<td>Facturacion Electronica</td>
                                            <td>Editar</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>        
                            </div>
                        </div>
                        <div class="col-md-7" id="divFormularioCli" align="center"></div> 
                        <div class="col-md-5"  align="center"></div> 
                        <div class="col-md-7" id="divFormularioIxml" align="center"></div>
                        <div class="col-md-5"  align="center"></div> 
                        <div class="col-md-7" id="divFormularioIpdf" align="center"></div>                       
                    </div>
                    
                    <div id="cpestana2"></div>
                    <div id="tpestana2" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioDatos" align="center" width="100%"></div>
                        <div id="divReporteDatos" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana3"></div>
                    <div id="tpestana3" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioContactoTelf" class="col-md-6"></div>
                        <div id="divFormularioContactoEmai" class="col-md-6"></div>
                        <div id="divFormularioContactoDire" class="col-md-12"></div>
                    </div>
                    <div id="cpestana4"></div>
                    <div id="tpestana4" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioCcli" align="center" width="100%"></div>
                        <div id="divReporteCcli" align="center" width="100%"></div>
                    </div>
                    <div id="cpestana5"></div>
                    <div id="tpestana5" style="width:99%; height:98%; overflow: scroll;">
                        <div id="divFormularioContrato" align="center" width="100%"></div>
                        <div id="divReporteContrato" align="center" width="100%"></div>
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
                </div>
                <div style="width: 100%;">
                    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                </div>
            </form>
        </div>
    </body>


   
    <script>genera_formulario();</script>
    <script src="js/validacion_.js" type="text/javascript"></script>
    <script>
        function prueba_conexion_sftp_bpi() {
            xajax_test_conexion_sftp_pichincha(xajax.getFormValues("form1"));
        }
        function acciones_integracion(accion = 1){
            // console.log('accion',accion);
            xajax_acciones_integracion(accion);
        }

        function editar_integracion (id_integracion){
            xajax_acciones_integracion(3,{'id_integracion':id_integracion});
        }

        function editar_integracion_config (id_integracion_config){
            xajax_acciones_integracion(10,{'id_integracion_config':id_integracion_config});
        }


        function guardar_integracion(accion,id_integracion=0){
            // jsShowWindowLoad();       
            // console.log(xajax.getFormValues("form1"));

            xajax_acciones_integracion(accion,xajax.getFormValues("form1"),id_integracion);
        }
        function guardar_integracion_config(accion,id_integracion_config=0){
            var clave_integracion_config = document.getElementById('clave_integracion_config').value;
            var id_integracion_config = id_integracion_config?id_integracion_config:document.getElementById('id_integracion_config').value;
            var id_integracion = document.getElementById('id_integracion').value;
            var valor_integracion_config = document.getElementById('valor_integracion_config').value;
            var desc_integracion_config = document.getElementById('desc_integracion_config').value;

            var data = {
                "clave_integracion_config":clave_integracion_config,
                "id_integracion":id_integracion,
                "id_integracion_config":id_integracion_config,
                "valor_integracion_config":valor_integracion_config,
                "desc_integracion_config":desc_integracion_config
            };
            xajax_acciones_integracion(accion,data,id_integracion);
        }

        function limpiar_integracion_config(accion){
            if (accion==1){
                document.getElementById('id_integracion_config').value = '';
            }
            document.getElementById('clave_integracion_config').value = '';
            document.getElementById('valor_integracion_config').value = '';
            document.getElementById('desc_integracion_config').value = '';
        }

        function generate_modal(id_modal){
            
            $('#'+id_modal).on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget) // Button that triggered the modal
                    var recipient = button.data('whatever') // Extract info from data-* attributes
                    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                    var modal = $(this)
                    // modal.find('.modal-title').text('New message to ' + recipient)
                    // modal.find('.modal-body input').val(recipient)
                });
            $('#'+id_modal).modal('show');
        }

        function getBrowserTheme() {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                changeTheme(event.matches ? "dark" : "light");
            });
        }

        function hide_modal(id_modal){
            $('#'+id_modal).modal('hide');
        }

        function editar_token(check_element,id_element){
            var tmp_element_input = document.getElementById(id_element);
            var tmp_element_input_temp_value = tmp_element_input.getAttribute("temp_value");

            if(check_element.checked){
                tmp_element_input.readOnly = false;
                tmp_element_input.value = '';
                check_element.value = 'S';


            }else{
                tmp_element_input.readOnly = true;
                tmp_element_input.value = tmp_element_input_temp_value;
                check_element.value = 'N';
            }
            console.log(tmp_element_input_temp_value);
            console.log(tmp_element_input);
        }

        function changeTheme(new_theme){
            console.log(new_theme);

        }
        getBrowserTheme();
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>