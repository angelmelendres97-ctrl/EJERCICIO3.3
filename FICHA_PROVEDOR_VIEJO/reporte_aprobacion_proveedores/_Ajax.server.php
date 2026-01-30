    <?php

    // NO MODIFICAR ESTA LÍNEA
    require("_Ajax.comun.php");

    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    /* FUNCIONES AJAX DEL MÓDULO */
    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    //para ver errores en el navegador
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    function genera_formulario_pedido($sAccion = 'nuevo', $aForm = '')
    {
        global $DSN_Ifx, $DSN;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $oReturn = new xajaxResponse();

        $idempresa  = isset($_SESSION['U_EMPRESA']) ? intval($_SESSION['U_EMPRESA']) : 0;

        // LISTA EMPRESA
        $sql = "SELECT empr_cod_empr, empr_nom_empr FROM saeempr";
        $lista_empr = lista_boostrap_func($oIfx, $sql, $idempresa, 'empr_cod_empr', 'empr_nom_empr');

        $html = '
        <h3>APROBACION DE PROVEEDORES</h3>

        <div class="row" style="margin-top:10px; margin-bottom:15px;">
            <div class="col-md-12" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                <div class="btn-group" style="margin-right:10px;">
                    <div class="btn btn-primary btn-sm" onclick="genera_formulario();">
                        <span class="glyphicon glyphicon-file"></span> Nuevo
                    </div>
                    <div class="btn btn-primary btn-sm" onclick="guardar();">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-bottom:10px; padding:10px 12px; background:#f7f7f7; border:1px solid #e0e0e0; border-radius:4px;">
            <div class="col-md-12" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

                <div class="form-group" style="min-width:220px;">
                    <label style="margin-bottom:4px;">* Empresa</label>
                    <select id="empresa" name="empresa" class="form-control input-sm">
                        <option value="">Seleccione una opción...</option>'
                        . $lista_empr .
                    '</select>
                </div>

                <div class="form-group" style="flex:1 1 280px; min-width:260px;">
                    <label style="margin-bottom:4px;">Proveedor</label>
                    <div class="input-group">
                        <input type="hidden" id="proveedor_codigo" name="proveedor_codigo">
                        <input type="text" id="proveedor_nombre" name="proveedor_nombre"
                            class="form-control input-sm"
                            placeholder="ESCRIBA EL PROVEEDOR Y PRESIONE ENTER O F4"
                            onkeydown="proveedorKeyHandler(event)">
                        <span class="input-group-addon primary" onclick="autocompletar_proveedor_btn()">
                            <i class="fa fa-search"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group" style="min-width:240px;">
                    <label style="margin-bottom:4px;">Fecha</label>
                    <div class="input-group">
                        <input type="date" id="fecha_ini" name="fecha_ini"
                            value="" class="form-control input-sm" aria-label="Fecha inicio">
                        <span class="input-group-addon">–</span>
                        <input type="date" id="fecha_fin" name="fecha_fin"
                            value="" class="form-control input-sm" aria-label="Fecha fin">
                    </div>
                </div>

                <div class="form-group" style="min-width:150px; margin-left:auto;">
                    <label style="margin-bottom:4px; visibility:hidden;">Acción</label>
                    <button type="button" class="btn btn-primary btn-sm btn-block" style="width:100%;"
                        onclick="limpiarConsulta(); consultar();">
                        <span class="glyphicon glyphicon-search"></span> Consultar
                    </button>
                </div>

            </div>
        </div>
        ';


        $oReturn->assign("divFormularioCabecera", "innerHTML", $html);
        return $oReturn;
    }

    function consultar($aForm = '')
    {
        global $DSN_Ifx;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // ==========================
        // CONEXIONES A INFORMIX
        // ==========================
        $oIfx  = new Dbo;  
        $oIfx->DSN  = $DSN_Ifx; 
        $oIfx->Conectar();  

        $oAux  = new Dbo;  
        $oAux->DSN  = $DSN_Ifx; 
        $oAux->Conectar();  

        $oReturn = new xajaxResponse();

        // ==========================
        // VARIABLES
        // ==========================
        $empresa   = isset($aForm['empresa']) ? intval($aForm['empresa']) : 0;
        $proveedor = isset($aForm['proveedor_codigo']) ? intval($aForm['proveedor_codigo']) : 0;

        $fechaIni  = isset($aForm['fecha_ini']) ? trim($aForm['fecha_ini']) : '';
        $fechaFin  = isset($aForm['fecha_fin']) ? trim($aForm['fecha_fin']) : '';

        // Validar fechas (YYYY-MM-DD)
        $fechaIni = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaIni)) ? $fechaIni : '';
        $fechaFin = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) ? $fechaFin : '';

        if ($empresa <= 0) {
            $oReturn->alert("Seleccione una empresa para continuar con la consulta.");
            $oReturn->assign("divFormularioDetalle", "innerHTML", "");
            return $oReturn;
        }

        // ==========================
        // FILTROS
        // ==========================
        $condProveedor = "";
        $condEstado    = " AND p.clpv_est_clpv <> 'A' ";

        if ($proveedor > 0) {
            $condProveedor = " AND p.clpv_cod_clpv = $proveedor ";
            $condEstado = ""; 
        }
        //filtro de fechas
        $condFecha = "";

        if ($proveedor == 0) {

            // si no selecciona proveedor aplicamos fechas
            if ($fechaIni != "" && $fechaFin != "") {
                $condFecha = " AND p.clpv_fec_des BETWEEN '$fechaIni' AND '$fechaFin' ";
            } else if ($fechaIni != "") {
                $condFecha = " AND p.clpv_fec_des >= '$fechaIni' ";
            } else if ($fechaFin != "") {
                $condFecha = " AND p.clpv_fec_des <= '$fechaFin' ";
            }

        }

        // ==========================
        // CONSULTA PRINCIPAL
        // ==========================
        $sql = "
            SELECT 
            p.clpv_cod_clpv,
            p.clpv_ruc_clpv,
            p.clpv_nom_clpv,
            p.clpv_cod_sucu,
            s.sucu_nom_sucu,
            p.grpv_cod_grpv,
            p.clpv_cod_cact,
            p.clpv_cod_zona,
            p.clpv_cod_tprov,
            p.clpv_cod_fpagop,
            p.clpv_cod_tpago

        FROM saeclpv p
        LEFT JOIN saesucu s 
            ON s.sucu_cod_empr = p.clpv_cod_empr
        AND s.sucu_cod_sucu = p.clpv_cod_sucu

        WHERE p.clpv_clopv_clpv = 'PV'
        AND p.clpv_cod_empr = $empresa
        $condProveedor
        $condEstado
        $condFecha
        ORDER BY p.clpv_nom_clpv

        ";

        //     $oReturn->alert("SQL final:\n\n".$sql);
         // return $oReturn;


        // ==========================
        // ARMADO DE TABLA
        // ==========================
        $html = '
            <div class="col-md-12">
            <table id="tbclientes" class="table table-bordered table-hover table-striped table-condensed" style="margin-top: 30px">
                        <thead>
                            <tr>
                                <th colspan="30"><h6>LISTA DE PROVEEDORES</h6></th>
                            </tr>

                    <tr>
                        <th class="success" style="color: #00859B; font-weight: bold">N.-</th>
                        <th class="success" style="color: #00859B; font-weight: bold">CÓDIGO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">RUC</th>
                        <th class="success" style="color: #00859B; font-weight: bold">NOMBRE</th>
                        <th class="success" style="color: #00859B; font-weight: bold">SUCURSAL</th>

                        <th class="success" style="color: #00859B; font-weight: bold">GRUPO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">FLUJO CAJA</th>
                        <th class="success" style="color: #00859B; font-weight: bold">ZONA</th>
                        <th class="success" style="color: #00859B; font-weight: bold">TIPO PROV.</th>
                        <th class="success" style="color: #00859B; font-weight: bold">FORMA PAGO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DESTINO PAGO</th>

                        <th class="success" style="color: #00859B; font-weight: bold">TELÉFONO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">CORREO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DIRECCIÓN</th>

                        <th class="success"> 
                            <input type="checkbox" onclick="marcar(this);"> 
                        </th>
                    </tr>
                </thead>
                <tbody>
        ';

        // ==========================
        // EJECUTAR CONSULTA
        // ==========================
        $hayDatos = false;
        if (!$oIfx->Query($sql)) {
            $oReturn->alert("Ocurrió un problema al consultar los proveedores. Intente nuevamente.");
        } elseif ($oIfx->NumFilas() > 0) {
            $contador = 1;
            do {
                $hayDatos = true;
                // ============================================================
                // DATOS DEL PROVEEDOR
                // ============================================================
                $codigo  = intval($oIfx->f('clpv_cod_clpv'));
                $ruc     = trim($oIfx->f('clpv_ruc_clpv'));
                $nombre  = trim($oIfx->f('clpv_nom_clpv'));

                $sucuCod = intval($oIfx->f('clpv_cod_sucu'));
                $sucursal_nombre = trim($oIfx->f('sucu_nom_sucu'));
                if ($sucursal_nombre === '') {
                    $sucursal_nombre = 'Sucursal no registrada';
                }

                // ============================================================
                // CONSULTAS RELACIONADAS
                // ============================================================

                // -------- TELEFONO ----------
                $telefono = "";
                $sql_tel = "
                    SELECT tlcp_tlf_tlcp
                    FROM saetlcp
                    WHERE tlcp_cod_empr = $empresa
                    AND tlcp_cod_sucu = $sucuCod
                    AND tlcp_cod_clpv = $codigo
                    LIMIT 1
                ";
                if ($oAux->Query($sql_tel) && $oAux->NumFilas() > 0) {
                    $telefono = trim($oAux->f('tlcp_tlf_tlcp'));
                }
                if ($telefono === '') {
                    $telefono = 'Teléfono no registrado';
                }

                // -------- CORREO ----------
                $correo = "";
                $sql_cor = "
                    SELECT emai_ema_emai
                    FROM saeemai
                    WHERE emai_cod_empr = $empresa
                    AND emai_cod_sucu = $sucuCod
                    AND emai_cod_clpv = $codigo
                    LIMIT 1
                ";
                if ($oAux->Query($sql_cor) && $oAux->NumFilas() > 0) {
                    $correo = trim($oAux->f('emai_ema_emai'));
                }
                if ($correo === '') {
                    $correo = 'Correo no registrado';
                }

                // -------- DIRECCIÓN ----------
                $direccion = "";
                $sql_dir = "
                    SELECT dire_dir_dire
                    FROM saedire
                    WHERE dire_cod_empr = $empresa
                    AND dire_cod_sucu = $sucuCod
                    AND dire_cod_clpv = $codigo
                    LIMIT 1
                ";
                if ($oAux->Query($sql_dir) && $oAux->NumFilas() > 0) {
                    $direccion = trim($oAux->f('dire_dir_dire'));
                }
                if ($direccion === '') {
                    $direccion = 'Dirección no registrada';
                }

                // ============================================================
                // CAMPOS RELACIONADOS
                // ============================================================

                // GRUPO
                $grupo = "";
                $codigoGrupo = trim($oIfx->f('grpv_cod_grpv'));

                if ($codigoGrupo != "") {

                    $sql_g = "
                        SELECT grpv_nom_grpv
                        FROM saegrpv
                        WHERE grpv_cod_empr = $empresa
                        AND grpv_cod_modu = 4
                        AND grpv_cod_grpv = '$codigoGrupo'
                        LIMIT 1
                    ";

                    if ($oAux->Query($sql_g) && $oAux->NumFilas() > 0) {
                        $grupo = trim($oAux->f('grpv_nom_grpv'));
                    }
                }


                // FLUJO CAJA
                $flujo = "";
                if ($oIfx->f('clpv_cod_cact') != "") {
                    $sql_f = "
                        SELECT cact_nom_cact
                        FROM saecact
                        WHERE cact_cod_empr = $empresa
                        AND cact_cod_cact = '{$oIfx->f('clpv_cod_cact')}'
                    ";
                    if ($oAux->Query($sql_f) && $oAux->NumFilas() > 0) {
                        $flujo = trim($oAux->f('cact_nom_cact'));
                    }
                }

                // ZONA
                $zona = "";
                if ($oIfx->f('clpv_cod_zona') != "") {
                    $sql_z = "
                        SELECT zona_nom_zona
                        FROM saezona
                        WHERE zona_cod_empr = $empresa
                        AND zona_cod_zona = '{$oIfx->f('clpv_cod_zona')}'
                    ";
                    if ($oAux->Query($sql_z) && $oAux->NumFilas() > 0) {
                        $zona = trim($oAux->f('zona_nom_zona'));
                    }
                }

                // TIPO PROVEEDOR
                $tipoProv = "";
                if ($oIfx->f('clpv_cod_tprov') != "") {
                    $sql_t = "
                        SELECT tprov_des_tprov
                        FROM saetprov
                        WHERE tprov_cod_empr = $empresa
                        AND tprov_cod_tprov = '{$oIfx->f('clpv_cod_tprov')}'
                    ";
                    if ($oAux->Query($sql_t) && $oAux->NumFilas() > 0) {
                        $tipoProv = trim($oAux->f('tprov_des_tprov'));
                    }
                }

                // FORMA DE PAGO
                $formaPago = "";
                if ($oIfx->f('clpv_cod_fpagop') != "") {
                    $sql_fp = "
                        SELECT fpagop_des_fpagop
                        FROM saefpagop
                        WHERE fpagop_cod_empr = $empresa
                        AND fpagop_cod_fpagop = '{$oIfx->f('clpv_cod_fpagop')}'
                    ";
                    if ($oAux->Query($sql_fp) && $oAux->NumFilas() > 0) {
                        $formaPago = trim($oAux->f('fpagop_des_fpagop'));
                    }
                }

                // DESTINO DE PAGO
                $destino = "";
                if ($oIfx->f('clpv_cod_tpago') != "") {
                    $sql_dp = "
                        SELECT tpago_des_tpago
                        FROM saetpago
                        WHERE tpago_cod_empr = $empresa
                        AND tpago_cod_tpago = '{$oIfx->f('clpv_cod_tpago')}'
                    ";
                    if ($oAux->Query($sql_dp) && $oAux->NumFilas() > 0) {
                        $destino = trim($oAux->f('tpago_des_tpago'));
                    }
                }

                // ============================================================
                // IMPRIMIR FILA
                // ============================================================
                $html .= "
                    <tr>
                        <td>$contador</td>
                        <td>".htmlspecialchars($codigo)."</td>
                        <td>".htmlspecialchars($ruc)."</td>
                        <td>".htmlspecialchars($nombre)."</td>
                        <td>".htmlspecialchars($sucursal_nombre)."</td>

                        <td>".htmlspecialchars($grupo)."</td>
                        <td>".htmlspecialchars($flujo)."</td>
                        <td>".htmlspecialchars($zona)."</td>
                        <td>".htmlspecialchars($tipoProv)."</td>
                        <td>".htmlspecialchars($formaPago)."</td>
                        <td>".htmlspecialchars($destino)."</td>

                        <td>".htmlspecialchars($telefono)."</td>
                        <td>".htmlspecialchars($correo)."</td>
                        <td>".htmlspecialchars($direccion)."</td>

                        <td align='center'>
                            <input type='checkbox' name='prov_$codigo' value='$codigo'>
                        </td>
                    </tr>
                ";
                $contador++;

            } while ($oIfx->SiguienteRegistro());

        }

        if (!$hayDatos) {
            $html .= "</tbody></table>";
            $html .= '<div class="alert alert-info" role="alert" style="margin-top:10px;">No existen proveedores para los filtros seleccionados.</div>';
        } else {
            $html .= "</tbody></table>";
        }

        $oReturn->assign("divFormularioDetalle", "innerHTML", $html);
        $oReturn->script("init()");
        return $oReturn;

    }

    function guardar_proveedores($aForm)
    {
        global $DSN_Ifx;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $oReturn = new xajaxResponse();
        $empresa = isset($_SESSION['U_EMPRESA']) ? intval($_SESSION['U_EMPRESA']) : 0;

        if ($empresa <= 0) {
            $oReturn->alert("No se pudo identificar la empresa. Inicie sesión nuevamente.");
            return $oReturn;
        }

        if (!is_array($aForm) || empty($aForm)) {
            $oReturn->alert("No se recibieron datos del formulario.");
            return $oReturn;
        }

        $seleccionados = [];
        foreach ($aForm as $key => $value) {
            // SOLO CAMPOS QUE SEAN prov_123
            if (strpos($key, "prov_") === 0) {
                $codigo = intval($value);
                if ($codigo > 0) {
                    $seleccionados[] = $codigo;
                }
            }
        }

        if (count($seleccionados) === 0) {
            $oReturn->alert("Seleccione al menos un proveedor para aprobar.");
            return $oReturn;
        }

        $activados = 0;
        $omitidos = 0;

        foreach ($seleccionados as $codigo) {

            // Verificar estado actual
            $sqlVerifica = "
                SELECT clpv_est_clpv
                FROM saeclpv
                WHERE clpv_cod_empr = $empresa
                AND clpv_cod_clpv = $codigo
                LIMIT 1
            ";

            $estadoActual = '';
            if ($oIfx->Query($sqlVerifica) && $oIfx->NumFilas() > 0) {
                $estadoActual = trim($oIfx->f('clpv_est_clpv'));
            }

            if ($estadoActual === 'A') {
                $omitidos++;
                continue;
            }

            // ACTUALIZAR ESTADO A ACTIVO SOLO SI NO ESTÁ ACTIVO
            $sql = "
                UPDATE saeclpv
                SET clpv_est_clpv = 'A'
                WHERE clpv_cod_empr = $empresa
                AND clpv_cod_clpv = $codigo
                AND clpv_est_clpv <> 'A'
            ";

            if ($oIfx->Query($sql)) {
                $activados++;
            } else {
                $omitidos++;
            }
        }

        // Mensaje al usuario
        $mensaje = "Proveedores aprobados: $activados.";
        if ($omitidos > 0) {
            $mensaje .= " Omitidos (ya aprobados o sin cambios): $omitidos.";
        }

        $oReturn->alert($mensaje);

        $oReturn->script("consultar()");

        return $oReturn;
    }


    $xajax->processRequest();

?>
