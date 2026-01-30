<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) {
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

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
	<link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/css/bootstrap-treeview.css" media="screen">
	<link rel="stylesheet" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css">
	<!--JavaScript-->
	<script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/js/bootstrap-treeview.js"></script>
	<script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/Webjs.js"></script>
	<script type="text/javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>

	<script type="text/javascript" language="javascript" src="js/reporte.js"></script>
	<script type="text/javascript" language="javascript" src="js/validaciones.js"></script>

	<!-- JSON -->
	<script type="text/javascript" language="javascript" src="json/saepccp.js"></script>

	<script>
		function genera_formulario() {
			consultarPara();
		}

		function dibuja_adjuntos() {
			xajax_dibuja_adjuntos();
		}


		/// Funcion guardar
		function guardar() {
			var validado = $("#form1").valid();
			if (validado) {
				xajax_guardar(xajax.getFormValues("form1"));
			}

		}
		//  Funcion Consultar

		function consultar() {
			xajax_consultar(xajax.getFormValues("form1"));
		}


		//alertas
		function alerts(mensaje, tipo) {
			Swal.fire({
				type: tipo,
				title: mensaje,
				showCancelButton: false,
				showConfirmButton: false,
				timer: 1200,

			})
		}

		function seleccionaItem(factu, fac, aut, bode, lotes, orden, credfis, numdig, docierreant, ctaret, codigo, parliq, sec_fgasto, cuenta_no_domiciliado, tran_det, empr_cod_empr) {
			if (factu == 'S') {
				document.getElementById("factu").checked = true;

			} else {
				document.getElementById("factu").checked = false;
			}

			$("#fac").val(fac).trigger('change.select2');

			$("#aut").val(aut).trigger('change.select2');

			document.getElementById('id_pccp').value = codigo;
			document.getElementById('sec_fgasto').value = sec_fgasto;
			document.getElementById('cuenta_no_domiciliado').value = cuenta_no_domiciliado;


			document.form1.lotes.value = lotes;
			$("#bodegaser").val(bode).trigger('change.select2');

			document.getElementById("orden").value = orden;

			document.form1.numdig.value = numdig;
			$("#docierreant").val(docierreant).trigger('change.select2');

			$("#credfis").val(credfis).trigger('change.select2');
			$("#ctaret").val(ctaret).trigger('change.select2');

			$("#parliq").val(parliq).trigger('change.select2');
			$("#tran_det").val(tran_det).trigger('change.select2');

			//document.form1.credfis.value = credfis;
			//document.form1.ctaret.value = ctaret;

			//Cargar los adjuntos de la empresa seleccionada
			xajax_consultarAdjuntos(xajax.getFormValues("form1"), empr_cod_empr);
		}

		// FUNCION NUEVO PARA INGRESAR NUEVOS DATOS

		function nuevo() {
			$("#form1")[0].reset();
			$("#form1").trigger("reset");
			$("#fac").val(0).trigger('change.select2');
			$("#aut").val(0).trigger('change.select2');
			$("#bodegaser").val(0).trigger('change.select2');
			$("#docierreant").val(0).trigger('change.select2');
			document.getElementById('id_pccp').value = 0;

			//Limpiar la tabla de adjuntos
			document.getElementById('divReporteAdjuntos').innerHTML = '';
			
			//También limpiar el grid de archivos temporales si existe
			document.getElementById('gridArchivos').innerHTML = '';
			
			//Resetear la sesión de archivos temporales
			xajax_limpiar_session_adjuntos();
		}

		function llenar_ceros(numero_ceros) {
			var numero_secuencial = document.getElementById('sec_fgasto').value;
			var nuevo_sec = numero_secuencial.toString().padStart(numero_ceros, '0');
			document.getElementById('sec_fgasto').value = nuevo_sec;
		}

		function cod_ret_b(op, event, tipo) {
			if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
				if (op == 0) {
					var cod = document.getElementById('cuenta_no_domiciliado').value;
				} else if (op == 1) {
					var cod = document.getElementById('cuenta_no_domiciliado').value;
				}
				xajax_cod_ret_b(op, cod, xajax.getFormValues("form1"), tipo);
			}
		}

		function ventana_cod_ret_b(op, tipo) {
			if (op == 0) {
				var cod = document.getElementById('cuenta_no_domiciliado').value;
			} else if (op == 1) {
				var cod = document.getElementById('cuenta_no_domiciliado').value;
			}
			var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=450, top=255, left=130";
			var pagina = '../cxp_parametros/buscar_tret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cod=' + cod + '&op=' + op + '&tipo=' + tipo;
			window.open(pagina, "", opciones);
		}

		function selectTret(id, nombre, op, tipo) {
			document.getElementById('cuenta_no_domiciliado').value = id;
			document.getElementById('nombre_ret').innerHTML = '<label style="color: green">' + id + ' - ' + nombre + '</label>';

		}

		function agregarArchivo() {
			//alert("agregarArchivo funciona");
            var titulo = $("#titulo").val();
            var archivo = $("#archivo").val();
            if (titulo != '' && archivo != '') {
                xajax_agrega_modifica_gridAdj(0, xajax.getFormValues("form1"), '', '');
            } else {
                alert("Ingrese Titulo, Adjunto para continuar...!");
            }

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

		function subirAdjunto() 
		{

			var formData = new FormData();

			formData.append("file[]", $("#archivo")[0].files[0]);

			$.ajax({
				url: "_upload_adjuntos.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				success: function(resp){

					// pasar el archivo subido al xajax
					xajax_agrega_modifica_gridAdj(0, xajax.getFormValues('form1'), '', resp);

				}
			});

		}


		




	</script>


	<body>
		<div id="divContenedor">
			<form id="form1" name="form1" action="javascript:void(null);" novalidate="novalidate" class="form-horizontal">
				<div class="container-fluid">
					<div class="row">

						<div style="margin-top:10px" class="col-md-6">
							<h3 class="text-primary"> PARAMETROS:</h3>
							<div class="table-responsive" id="ejemplo">
								<table id="example" class="table table-striped table-bordered table-hover table-condensed">
									<thead>
										<tr>
											<td class="bg-primary">ID</td>
											<td class="bg-primary">Empresa</td>
											<td class="bg-primary">Codigo Factura</td>
											<td class="bg-primary">Aut. Pagos</td>
											<td class="bg-primary">Bodega Servicios</td>
											<td class="bg-primary">Orden de Pago</td>
											<td class="bg-primary">Credito Fiscal</td>
											<td class="bg-primary">No. Digitos</td>
											<td class="bg-primary">Doc. Cierre Anticipos</td>
											<td class="bg-primary">Cta Ret Asu. Empresa</td>
											<td class="bg-primary">Seleciona</td>

										</tr>
									</thead>
								</table>
							</div>
						</div>



						<div class="col-md-6">

							<div class="btn-group" style="margin-top: 70px;">
								<button class="btn btn-primary btn-sm" onclick="nuevo()">
									<span class="glyphicon glyphicon-new-list"></span>
									Nuevo
								</button>
							</div>

							<div class="btn-group" style="margin-top: 70px;">
								<button class="btn btn-primary btn-sm" onclick="guardar()">
									<span class="glyphicon glyphicon-floppy-disk"></span>
									Guardar
								</button>
							</div>



							<div class="form-group" style="display:none">
								<div class="col-md-6" style="display:none">
									<label for="factu">Facturacion Electronica:
										<!-- SE DEBE PONER EN CADA CHECK EL VALUE DE S -->
										<input type="checkbox" name="factu" id="factu" value="S" />
									</label>
								</div>
							</div>

							<div class="form-group">

								<div class="col-md-6">
									<input type="text" class="form-control" name="id_pccp" id="id_pccp" style="display: none;">
									<!-- ///Colocar unicamente el casillero///7 -->
									<label for="fac" class="control-label">Codigo Factura:</label>
									<select id="fac" name="fac" class="form-control input-sm select2" required style="width: 100%;">
										<option value="">Seleccione una opcion..</option>
										<!-- /////////////Desplegar la lista de opciones del casillero////////////////// -->
										<?
										$sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_modu=4 and tran_cod_empr=$idempresa";

										if ($oIfx->Query($sql)) {
											if ($oIfx->NumFilas() > 0) {
												do {
													$id = $oIfx->f('tran_cod_tran');
													$descripcion = $oIfx->f('tran_des_tran');
													$defecto = $oIfx->f('defecto');
													echo '<option value="' . $id . '">' . $descripcion . '</option>';
												} while ($oIfx->SiguienteRegistro());
											}
										}
										$oIfx->Free();
										?>
									</select>
								</div>

								<div class="col-md-6">
									<label for="aut" class="control-label">Autorizacion Pagos:</label>
									<select id="aut" name="aut" class="form-control input-sm select2" required style="width: 100%;">
										<option value="">Seleccione una opcion..</option>

										<!-- /////////////Desplegar la lista de opciones del casillero////////////////// -->
										<?
										$sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_modu=4 and tran_cod_empr=$idempresa";

										if ($oIfx->Query($sql)) {
											if ($oIfx->NumFilas() > 0) {
												do {
													$id = $oIfx->f('tran_cod_tran');
													$descripcion = $oIfx->f('tran_des_tran');
													$defecto = $oIfx->f('defecto');

													echo '<option value="' . $id . '">' . $descripcion . '</option>';
												} while ($oIfx->SiguienteRegistro());
											}
										}
										$oIfx->Free();
										?>

									</select>
								</div>

							</div>

							<div class="form-group">

								<div class="col-lg-6" style="display:none">
									<label for="lotes" class="control-label">Por Lotes:
										<!-- SE DEBE PONER EN CADA CHECK EL VALUE DE S -->
										<input type="checkbox" name="lotes" id="lotes" value="S">
									</label>

								</div>

								<div class="col-lg-6">
									<label for="orden" class="control-label">Orden de Pago:</label>
									<input type="text" class="form-control" name="orden" id="orden">

								</div>

								<div class="col-md-6">
									<label for="bodegaser" class="control-label">Bodega Servicios:</label>
									<select id="bodegaser" name="bodegaser" class="form-control input-sm select2" required style="width: 100%;">
										<option value="">Seleccione una opcion..</option>

										<!-- /////////////Desplegar la lista de opciones del casillero////////////////// -->

										<?
										/////////////// COMPARACION TABLAS DE RELACION SAEBODE Y SAESUBO ///////////
										$sql = "select bode_cod_bode, bode_nom_bode from saebode  inner join saesubo on 
													subo_cod_bode=bode_cod_bode where bode_cod_empr=subo_cod_empr and 
													bode_cod_empr=$idempresa and  subo_cod_empr=$idempresa and subo_cod_sucu=$idempresa";
										if ($oIfx->Query($sql)) {
											if ($oIfx->NumFilas() > 0) {
												do {
													$id = $oIfx->f('bode_cod_bode');
													$descripcion = $oIfx->f('bode_nom_bode');
													$defecto = $oIfx->f('defecto');

													echo '<option value="' . $id . '">' . $descripcion . '</option>';
												} while ($oIfx->SiguienteRegistro());
											}
										}
										$oIfx->Free();
										?>
									</select>
								</div>

							</div>


							<div class="form-group">

								<div class="col-lg-6">
									<label for="numdig" class="control-label">No. Digitos:</label>
									<input type="number" class="form-control" name="numdig" id="numdig">
								</div>

								<div class="col-md-6">

									<label for="credfis" class="control-label">Credito Fiscal:</label>
									<?
										$sql = "select cuen_cod_cuen, cuen_nom_cuen
													from saecuen where
													cuen_cod_empr = $idempresa and
													cuen_mov_cuen = '1'
													order by 1 ";
										$lista_cuen = '';
										if ($oIfx->Query($sql)) {
											if ($oIfx->NumFilas() > 0) {
												do {
													$cuen_cod_cuen = $oIfx->f('cuen_cod_cuen');
													$cuen_nom_cuen = $cuen_cod_cuen . ' ' . htmlentities($oIfx->f('cuen_nom_cuen'));

													$lista_cuen .= '<option value="' . $cuen_cod_cuen . '" >' . $cuen_nom_cuen . '</option>';
												} while ($oIfx->SiguienteRegistro());
											}
										}
										$oIfx->Free();




										$sql_tran = "SELECT tran_cod_tran, tran_des_tran, trans_tip_tran from saetran where
															tran_cod_empr = $idempresa and
															tran_cod_sucu = $idsucursal and
															tran_cod_modu = 4 order by 2 ";
										$lista_transaccion = '';
										if ($oIfx->Query($sql_tran)) {
											if ($oIfx->NumFilas() > 0) {
												do {
													$tran_cod_tran = $oIfx->f('tran_cod_tran');
													$tran_des = $oIfx->f('tran_cod_tran') . ' || ' . $oIfx->f('tran_des_tran') . ' || ' . $oIfx->f('trans_tip_tran');
													$lista_transaccion .= '<option value="' . $tran_cod_tran . '" >' . $tran_des . '</option>';
												} while ($oIfx->SiguienteRegistro());
											}
										}
										$oIfx->Free();

									?>
									<!-- <input type="text"  class="form-control"  name="credfis" id="credfis"> -->
									<select id="credfis" name="credfis" class="form-control select2" style="text-align:left">
										<option value="0">Seleccione una opcion..</option>
										<?= $lista_cuen ?>
									</select>

								</div>

							</div>

							<div class="form-group">

								<div class="col-md-6">
									<label for="docierreant" class="control-label">Doc. Cierre Anticipos:</label>
									<select id="docierreant" name="docierreant" class="form-control input-sm select2" required style="width: 100%;">
										<option value="">Seleccione una opcion..</option>

										<?
											$sql = "select tidu_cod_tidu, tidu_des_tidu  from saetidu where tidu_cod_modu=4 and tidu_cod_empr=$idempresa";

											if ($oIfx->Query($sql)) {
												if ($oIfx->NumFilas() > 0) {
													do {
														$id = $oIfx->f('tidu_cod_tidu');
														$descripcion = $oIfx->f('tidu_des_tidu');
														$defecto = $oIfx->f('defecto');
														//  PARA QUE SALGA EL CODIGO ALADO DE LAS OPCIONES COLOCAR .$id.'-'
														echo '<option value="' . $id . '">' . $id . ' - ' . $descripcion . '</option>';
													} while ($oIfx->SiguienteRegistro());
												}
											}
											$oIfx->Free();
										?>

									</select>
								</div>

								<div class="col-lg-6">
									<label for="ctaret" class="control-label">Cta. Retencion Asumida por la Empresa </label>
									<!-- input type="text" class="form-control" name="ctaret" id="ctaret"> -->

									<select id="ctaret" name="ctaret" class="form-control select2" style="text-align:left">
										<option value="0">Seleccione una opcion..</option>
										<?= $lista_cuen ?>
									</select>

								</div>


								<div class="col-lg-6">
									<label for="parliq" class="control-label">Liquidaciones en Compras</label>
									<!-- input type="text" class="form-control" name="ctaret" id="ctaret"> -->

									<select id="parliq" name="parliq" class="form-control select2" style="text-align:left">
										<option value="3">AMBOS</option>
										<option value="2">PRODUCTOS</option>
										<option value="1">SERVICIOS</option>

									</select>

								</div>

								<div class="col-lg-6">
									<label for="numdig" class="control-label">Secuencial Factura Gasto:</label>
									<input type="number" class="form-control" name="sec_fgasto" id="sec_fgasto" onchange="llenar_ceros(9)">
								</div>

								<div class="col-lg-6">
									<label for="numdig" class="control-label">Cuenta Ret/Det No Domiciliado:</label>
									<input type="number" class="form-control" name="cuenta_no_domiciliado" id="cuenta_no_domiciliado" onkeyup="cod_ret_b( 0,  event, 0 )">
									<div id="nombre_ret" name="nombre_ret"></div>
								</div>




								<div class="col-lg-6">
									<label for="tran_det" class="control-label">Transaccion Detraccion Peru</label>
									<select id="tran_det" name="tran_det" class="form-control select2" style="text-align:left">
										<option value="0">Seleccione una opcion..</option>
										<?= $lista_transaccion ?>
									</select>
								</div>


							</div>

							<div class="form-group">
								<div class="col-md-12" id="divFormularioAdjuntos" style="margin-top:20px;"></div>

								<div class="col-md-12">
									<div id="gridArchivos"  style="text-align:center; width:100%;"></div>
								</div>

								<div class="col-md-12">
									<div id="divReporteAdjuntos"  style="text-align:center; width:100%;"></div>
								</div>
							</div>


						</div>

					</div>
				</div>
		</div>
		</form>
		</div>


	</body>
											
	<script>
    	dibuja_adjuntos();
		genera_formulario();
	</script>
	<script>
		//Initialize Select2 Elements
		$('.select2').select2();
	</script>
	<? /*     * ***************************************************************** */ ?>
	<? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>