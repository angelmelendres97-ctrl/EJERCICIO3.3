<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>
    	
    <!--CSS--> 
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/css/bootstrap-treeview.css" media="screen"> 
	
    <!--Javascript--> 
    
  
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>          
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/bootstrap.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/js/bootstrap-treeview.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	<script src="js/lenguajeusuario_.js"></script>
    <script>

        function genera_cabecera_formulario() {
            xajax_genera_cabecera_formulario('nuevo', xajax.getFormValues("form1"));
        }

 
        function genera_cabecera_filtro() {
            xajax_genera_cabecera_formulario('filtro', xajax.getFormValues("form1"));
        }		
        function generar(){
            if(ProcesarFormulario() == true){
                xajax_generar(xajax.getFormValues("form1"));
            }
        }

        function f_filtro_sucursal(data){
            xajax_f_filtro_sucursal(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_sucursal() {
            var sel = document.getElementById("sucursal");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_sucursal(x, i, elemento) {
            var lista = document.form1.sucursal;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.sucursal.value = i;
        }

        function f_filtro_ciudad(data){
            xajax_f_filtro_ciudad(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_ciudad() {
            var sel = document.getElementById("ciudad");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elementos_ciudad(x, i, elemento) {
            var lista = document.form1.ciudad;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.ciudad.value = i;
        }

		
        function f_filtro_documento(data){
			//alert('hola');
            xajax_f_filtro(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_documentos() {
            var sel = document.getElementById("documento");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_documentos(x, i, elemento) {
			//alert('hola');
            var lista = document.form1.documento;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.documento.value = i;
        }


        function f_filtro_ejercicio(data){
			//alert(data);
            xajax_f_filtro_ejercicio(xajax.getFormValues("form1"), data);           
        }
   
		function eliminar_lista_anio() {
            var sel = document.getElementById("ejercicio");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_anio(x, i, elemento) {
			//alert(x);
            var lista = document.form1.ejercicio;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.ejercicio.value = i;
        }
 
		
        function f_filtro_periodo(data){
            xajax_f_filtro_periodo(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_periodo() {
            var sel = document.getElementById("periodo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_periodo(x, i, elemento) {
            var lista = document.form1.periodo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.periodo.value = i;
        }
        function f_filtro_mes_fin(data){
            xajax_f_filtro_mes_fin(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_mes_fin() {
            var sel = document.getElementById("mes_fin");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_mes_fin(x, i, elemento) {
            var lista = document.form1.mes_fin;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.mes_fin.value = i;
        }
		
		function f_filtro_subgrupo(data){
            xajax_f_filtro_subgrupo(xajax.getFormValues("form1"), data);           
        }
   
		function eliminar_lista_subgrupo() {
            var sel = document.getElementById("cod_subgrupo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_subgrupo(x, i, elemento) {
            var lista = document.form1.cod_subgrupo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.cod_subgrupo.value = i;
        }
	
   
		function f_filtro_activos1(data){
            xajax_f_filtro_activos1(xajax.getFormValues("form1"), data);           
        }
   
		function eliminar_lista_activo1() {
            var sel = document.getElementById("cod_activo_hasta");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_activo1(x, i, elemento) {
            var lista = document.form1.cod_activo_hasta;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.cod_activo_hasta.value = i;
        }
		function seleccionaItem(empr, sucu, ejer, mes, asto){
			$("#miModal2").modal("show");
			$("#divInfo").html('');
			$("#divDirectorio").html('');
			$("#divRetencion").html('');
			$("#divDiario").html('');
			$("#divAdjuntos").html('');
			xajax_verDiarioContable(xajax.getFormValues("form1"), empr, sucu, ejer, mes, asto);
		}

		function vista_previa_diario(sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod) {
			var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
			var pagina = '../contabilidad_comprobante/vista_previa.php?sesionId=<?= session_id() ?>&sucursal='+  sucursal+'&cod_prove='+cod_prove+'&asto='+asto_cod+'&ejer='+ejer_cod+'&mes='+prdo_cod;
			window.open(pagina, "", opciones);
		}
	
		// FUNCION PARA EXPORTAR DATA A EXCEL	
		function f_exportar(){
			document.location = "excel.php";
		}
		function f_pdf(){
			var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=370, top=255, left=130";
			var pagina = '../../Include/documento_pdf_ctrlprov.php?sesionId=<?= session_id() ?>';
			window.open(pagina, "", opciones);

		}
				
        function cambioFiltroFecha(op) {
            xajax_cambioFiltroFecha(xajax.getFormValues("form1"), op);
        }

        function cargarMes() {
            xajax_cargarMes(xajax.getFormValues("form1"));
        }
		
		function eliminarCampo() {
            var sel = document.getElementById('mes');
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadirElementoCampo(x, i, elemento) {
            var lista = document.form1.mes;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		function buca_proveedor_id() {  		
			//alert('HOLA....');
			$("#myModalProveedores").modal("show");
			var table = $('#table_proveedor').DataTable();
			table.destroy();
			listar_proveedores();
		
		}
		function buca_proveedor( event, id) {  		
			if (event.keyCode == 13 || event.keyCode == 115) { // F4   
				$("#myModalProveedores").modal("show");
				var table = $('#table_proveedor').DataTable();
				table.destroy();
				listar_proveedores();
			}
		}
		function bajar_proveedores(id, nombre) {
			document.getElementById("clpv_cod_clpv").value = id;
			document.getElementById("proveedor").value = nombre;		
			$("#myModalProveedores").modal("hide");
		}
		function cambioRemesa(id) {
			if (id == 'anticipos'){
				document.getElementById('solo_ant').checked  = false;
				
			} else {
				document.getElementById('anticipos').checked  = false;
			}
		}
    </script>
    <!--DIBUJA FORMULARIO FILTRO-->
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <body>
        <form id="form1" name="form1" action="javascript:void(null);">
			<div class="col-md-12"> 
				<div class="table-responsive">
					<table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td align="center">
								<div id="divFormularioReportesGrupos"></div>
							</td>
						</tr>
					</table>
				</div> 
			</div> 
			<div> 
				<div id="myModalProveedores" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">LISTA DE PROVEEDORES</h4>
                            </div>
                            <div class="modal-body">
                                <table id="table_proveedor" class="table table-striped table-bordered table-hover table-condensed"  style="width: 100%;" align="center">
                                    <thead>
                                    <tr>
                                        <td colspan="5" class="bg-primary">LISTA PROVEEDORES</td>
                                    </tr>
                                    <tr class="info">
                                        <td>Ruc</td>
                                        <td>Nombre</td>
                                        <td>Seleccionar</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
                            </div>
                        </div>
                    </div>
                </div>
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
    </body>
    <script>genera_cabecera_formulario();/*genera_detalle();genera_form_detalle();*/</script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>