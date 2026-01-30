<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { 
	 global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();
	?>
    <? /*     * ***************************************************************** */ ?>
    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/css/bootstrap-treeview.css" media="screen">
    <link rel="stylesheet" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/dataTables/dataTables.bootstrap.min.css">
    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/js/bootstrap-treeview.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/Webjs.js"></script>
    <script type="text/javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>

    <script type="text/javascript" language="javascript" src="js/reporte.js"></script>
	<script type="text/javascript" language="javascript" src="js/validaciones.js"></script>

    <script>
       
        /// guardar
        function guardar(){
            var validado = $("#form1").valid();
			if (validado) {
				 xajax_guardar(xajax.getFormValues("form1") );
			}
          
        
        }
       
		/// recarga datateble
        function recargar(){
            
            var table = $('#example').DataTable();
            table.destroy();
            recargarProyecto();
        }
        /// consultar
		function consultar(id, nombre, tiempo, persona, tipo, motivo){
			 document.getElementById("codigo").value=id;
			 document.getElementById("descripcion").value=nombre;
			 document.getElementById("tiempo").value=tiempo;
			 document.getElementById("persona").value=persona;
			 
			 //document.getElementById("tipo").value=tipo;
			 $('#tipo').val(tipo).trigger('change.select2');
			 cargar_lista_motivo(motivo);
			
			
		}
		function nuevo(){
				location.reload();
			}
	
       //alertas
		function alerts(mensaje, tipo){
			Swal.fire({
				type: tipo,
				title: mensaje,
				showCancelButton: false,
				showConfirmButton: false,
				timer: 1200,
				
			})
		}
		function cargar_lista_motivo(motivo) {
            xajax_cargar_lista_motivo(xajax.getFormValues("form1"), motivo);
        }

        function eliminar_lista_motivo() {
            var sel = document.getElementById("motivo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_motivo(x, i, elemento) {
            var lista = document.form1.motivo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
    </script>

	<body>
		<div id="divContenedor">
			<form id="form1" name="form1" action="javascript:void(null);" novalidate="novalidate">
				<div class="container-fluid">
					<div class="row">
						<div style="margin-top:10px" class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
							<h4 class="text-info">TIEMPOS <small> ACTIVIDAD </small></h4>
							<div class="table-responsive">
								<table id="example" class="table table-striped table-bordered table-hover table-condensed" >
                                    <thead>
                                    <tr>
                                        <td class="bg-primary">CODIGO</td>
                                        <td class="bg-primary">TIPO</td>
                                        <td class="bg-primary">DESCRIPCION</td>
                                        <td  class="bg-primary">TIEMPO</td>
                                        <td  class="bg-primary">PERSONAS</td>
                                        <td  class="bg-primary">EDITAR</td>
            
                                    </tr>
                                    </thead>
                                </table>
							</div>
						</div>
						<div style="margin-top:10px" class="col-xs-12 col-sm-12 col-md-7  col-lg-7">
							<input type="hidden" id="codigo" name="codigo">
							<div class="col-xs-12 col-sm-12 col-md-12  col-lg-12  col-lg-12">
								<div class="btn-group">
									<button class="btn btn-primary btn-sm" onclick="nuevo()">
										<span class="glyphicon glyphicon-file"></span>
										Nuevo
									</button>
								</div>
								<div class="btn-group">
									<button class="btn btn-primary btn-sm" onclick="guardar()">
										<span class="glyphicon glyphicon-floppy-disk"></span>
										Guardar
									</button>
								</div>
							</div>
							
							
							 <div class="form-group col-xs-12 col-sm-12 col-md-4  col-lg-4">
								<label for="tipo" class="control-label">*Tipo</label>
								<select id="tipo" name="tipo" class="form-control input-sm select2" onchange="cargar_lista_motivo()" required>
									<option value="">Seleccione una opcion..</option>
									<?
										$sql = "select id, descripcion, defecto
											from int_tipo_proceso
											where tecnico = 'S' ";
										if ($oCon->Query($sql)) {
											if ($oCon->NumFilas() > 0) {
												do {
													$id = $oCon->f('id');
													$descripcion = $oCon->f('descripcion');
													$defecto = $oCon->f('defecto');

													$default = '';
													if ($defecto == 'S') {
														//$default = 'selected';
													}

													echo '<option value="' . $id . '" ' . $default . '>' . $descripcion . '</option>';
												} while ($oCon->SiguienteRegistro());
											}
										}
										$oCon->Free();
										?>
								</select>
							</div>
							<div class="form-group col-xs-12 col-sm-12 col-md-4  col-lg-4">
								<label for="motivo" class="control-label">* Motivo</label>
								<select id="motivo" name="motivo" class="form-control input-sm select2"  required>
									<option value="">Seleccione una opcion..</option>
								</select>
							</div>
							<div class="form-group col-xs-12 col-sm-12 col-md-4  col-lg-4">
								<label for="descripcion">*Descripcion:</label>
								<input type="text" id="descripcion" name="descripcion" class="form-control" required>
							</div>
							
							<div class="form-group col-xs-12 col-sm-12 col-md-4  col-lg-4">
								<label for="tiempo">*Tiempo:</label>
								<input type="text" id="tiempo" name="tiempo" class="form-control" onkeypress="return solo_numero_2(event)" required>
							</div>
							<div class="form-group col-xs-12 col-sm-12 col-md-4  col-lg-4">
								<label for="persona">Numero Persona:</label>
								<input type="text" id="persona" name="persona" class="form-control" onkeypress="return solo_numero(event)" >
							</div>
						</div>
					</div>
				
					
				</div>
			</form>
		</div>
	</body>
	<script>
        

        //Initialize Select2 Elements
        $('.select2').select2();
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>