<?php

require("_Ajax.comun.php"); // No modificar esta linea

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * **************************************************************** */
/* DF01 :: G E N E R A    F O R M U L A R I O    P E D I D O       */
/* * **************************************************************** */
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


//FUNCIONES PARA CREACION DE PROVEEDORES
function genera_formulario_cliente($sAccion = 'nuevo', $aForm = '', $cod, $pedi)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // LIMPIA EL ARRAY TEMPORAL AL ENTRAR AL MÓDULO
    unset($_SESSION['aDataGirdAdj']);
    unset($_SESSION['uafeCambios']);

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();
    $idempresa     = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];


    try {

        //lectura sucia
        //////////////

        unset($_SESSION['aDataGirdCuentaAplicada']);
        unset($_SESSION['aLabelGridCuentaAplicada']);
        $idempresa             = $_SESSION['U_EMPRESA'];
        $idsucursal         = $_SESSION['U_SUCURSAL'];
        $usuario_informix     = $_SESSION['U_USER_INFORMIX'];
        $_SESSION['aLabelGridCuentaAplicada'] = array('Id', 'Cuenta', 'Centro Costos', 'Porcentaje', 'Eliminar');

        $identificacion     = $aForm['identificacion'];
        $ruc_cli             = $aForm['ruc_cli'];
        $nombre             = $aForm['nombre'];
        $nombre_comercial     = $aForm['nombre_comercial'];
        $emai_ema_emai1     = $aForm['emai_ema_emai'];
        $telefono_cli        = $aForm['telefono_cli'];
        $direccion_cli         = $aForm['direccion_cli'];
        $zona                 = $aForm['zona'];
        $clpv_cod_sucu1     = $aForm['clpv_cod_sucu'];
        $clpv_pre_ven1         = $aForm['clpv_pre_ven'];
        $clpv_cod_vend         = $aForm['clpv_cod_vend'];
        $empr_cod_pais         = $_SESSION['U_PAIS_COD'];

        //CAMPOS NUEVOS PARA CEUNTAS BANCARIAS DE PERU

        $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];  // 593 ECUADOR 51 PERU
        // VALIDACION CAMPOS CUENTAS BANCARIAS PERU


        ///TABLA CUENTAS BANCARIAS PERU

        $sqlinf = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_NAME = 'cuentas_cash' and table_schema='public'";
        $ctralter = consulta_string($sqlinf, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqltb = "CREATE TABLE public.cuentas_cash(
            id int4 NOT NULL GENERATED ALWAYS AS IDENTITY (
            INCREMENT 1
            MINVALUE  1
            MAXVALUE 2147483647
            START 1
            ),
            cash_cod_clpv int,
            cash_ruc_clpv varchar(25) COLLATE \"pg_catalog\".\"default\",
            cash_cod_empr int,
            cash_num_cuen varchar(50) COLLATE \"pg_catalog\".\"default\",
            cash_tip_cuen varchar(5)  COLLATE \"pg_catalog\".\"default\",
            cash_cod_ban int,
            cash_cod_int  varchar(50) COLLATE \"pg_catalog\".\"default\",
            cash_cod_iden int,
            cash_cod_mone int,
            cash_est_del  varchar(1)  COLLATE \"pg_catalog\".\"default\",
            cash_created_at timestamp,
            cash_user_created int,
            cash_updated_at timestamp,
            cash_user_updated int, 
            cash_deleted_at timestamp,
            cash_user_deleted int,  
            CONSTRAINT \"id_cuentas_cash\" PRIMARY KEY (\"id\")
            );";
            $oCon->QueryT($sqltb);
        }

        switch ($sAccion) {
            case 'nuevo':

                $ifu->AgregarCampoTexto('codigoUnico', 'C&oacute;digo &Uacute;nico|left', false, '', 200, 100, true);
                $ifu->AgregarCampoTexto('cod_cuenta_in', 'Cuenta|left', false, '', 200, 100, true);

                $ifu->AgregarCampoCheck('clpv_par_rela',  'Compania Relacionada S/N|left', false, 'N');

                $ifu->AgregarCampoCheck('clpv_tec_sn',  'Técnico S/N|left', false, 'N');

                //query sucursal
                $sqlSucu = "select sucu_cod_sucu, sucu_nom_sucu 
                            from saesucu 
                            where sucu_cod_empr = $idempresa";
                if ($oIfx->Query($sqlSucu)) {
                    if ($oIfx->NumFilas() > 0) {
                        unset($arraySucu);
                        do {
                            $arraySucu[$oIfx->f('sucu_cod_sucu')] = $oIfx->f('sucu_nom_sucu');
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();

                // RUC 01
                // CEDULA 02
                // PASAPORTE 03
                // CONSUMIDOR FINAL 07
                // EXTRANJERIA 04

                $ifu->AgregarCampoLista('identificacion', 'Tipo|left', true, 170, 150, true);
                $sql = "SELECT t.id_iden_clpv, t.identificacion, t.tipo, c.identificacion AS iden, c.digitos
								FROM comercial.tipo_iden_clpv t , comercial.tipo_iden_clpv_pais c  WHERE
								t.id_iden_clpv = c.id_iden_clpv AND
								c.pais_cod_pais = '$empr_cod_pais' ";

                unset($array_iden);
                unset($array_iden_val);
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $array_iden[$oCon->f('tipo')] = $oCon->f('iden');
                            $array_iden_val[$oCon->f('tipo')] = $oCon->f('digitos');

                            $ifu->AgregarOpcionCampoLista('identificacion', $oCon->f('iden'), $oCon->f('tipo'));
                        } while ($oCon->SiguienteRegistro());
                    } else {
                        $oReturn->alert('Por favor Configure Pais - Etiquetas.....!!!!');
                    }
                }
                $oCon->Free();

                $sql_codInter_pais = "SELECT pais_codigo_inter from saepais where pais_cod_pais = $empr_cod_pais;";
                $codigo_pais = consulta_string_func($sql_codInter_pais, 'pais_codigo_inter', $oIfx, 0);

                if ($codigo_pais == 593) {
                    //$ifu->AgregarComandoAlCambiarValor('identificacion', 'consultaExistenciaIden();');
                    $ifu->AgregarCampoTexto('ruc_cli', 'Identificacion|left', true, '', 170, 180, true);
                    $ifu->AgregarComandoAlCambiarValor('ruc_cli', 'consultaExistenciaIden();');
                } else {
                    //$ifu->AgregarComandoAlCambiarValor('identificacion', 'consultaExistenciaIdenGeneral();');
                    $ifu->AgregarCampoTexto('ruc_cli', 'Identificacion|left', true, '', 170, 180, true);
                    $ifu->AgregarComandoAlCambiarValor('ruc_cli', 'consultaExistenciaIdenGeneral();');
                }


                $ifu->AgregarCampoTexto('nombre', 'Proveedor|left', true, '', 350, 200, true);
                $ifu->AgregarComandoAlEscribir('nombre', 'form1.nombre.value=form1.nombre.value.toUpperCase(); copiar_nombre(this);');

                $ifu->AgregarCampoTexto('nombre_comercial', 'Nombre Comercial|left', true, '', 400, 200, true);
                $ifu->AgregarComandoAlEscribir('nombre_comercial', 'form1.nombre_comercial.value=form1.nombre_comercial.value.toUpperCase()');

                /*$ifu->AgregarCampoListaSQL('grupo', 'Grupo|left', "SELECT GRPV_COD_GRPV, GRPV_NOM_GRPV FROM SAEGRPV WHERE
                                                                    GRPV_COD_EMPR = $idempresa AND
                                                                    GRPV_COD_MODU = 4", true, 170, 150);*/
                $ifu->AgregarCampoLista('grupo', 'Grupo|LEFT', true, 170, 150, true);
                $sql = "SELECT GRPV_COD_GRPV, GRPV_NOM_GRPV FROM SAEGRPV WHERE
                GRPV_COD_EMPR = $idempresa AND
                GRPV_COD_MODU = 4";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('grpv_cod_grpv');
                            $edes_des_edes = $oIfxA->f('grpv_nom_grpv');
                            $ifu->AgregarOpcionCampoLista('grupo', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $ifu->AgregarCampoTexto('emai_ema_emai', 'Email|left', true, '', 170, 150, true);

                $ifu->AgregarCampoTexto('telefono_cli', 'Telefono|left', true, '', 170, 150, true);

                $ifu->AgregarCampoTexto('direccion_cli', 'Direccion|left', true, '', 400, 220, true);
                $ifu->AgregarComandoAlEscribir('direccion_cli', 'form1.direccion_cli.value=form1.direccion_cli.value.toUpperCase()');

                $ifu->AgregarCampoNumerico('limite', 'Lim. Credito|left', false, 0, 70, 150, true);

                $ifu->AgregarCampoNumerico('dias_pago', 'D�as Pago|left', false, 0, 70, 150, true);

                $ifu->AgregarCampoNumerico('dsctDetalle', 'Dscto Detalle|left', false, '', 70, 150, true);

                $ifu->AgregarCampoNumerico('dsctGeneral', 'Dscto General|left', false, '', 70, 150, true);

                /*$ifu->AgregarCampoListaSQL('zona', 'Zona|left', "select zona_cod_zona, zona_nom_zona 
                                                                from saezona 
                                                                where zona_cod_empr = $idempresa
                                                                order by 2 ", true, 170, 150);*/
                $ifu->AgregarCampoLista('zona', 'Zona|LEFT', True, 170, 100, true);
                $sql = "select zona_cod_zona, zona_nom_zona 
                        from saezona 
                        where zona_cod_empr = $idempresa and
                        zona_cod_sucu = $idsucursal 
                        order by 2 ";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('zona_cod_zona');
                            $edes_des_edes = $oIfxA->f('zona_nom_zona');
                            $ifu->AgregarOpcionCampoLista('zona', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                /*$ifu->AgregarCampoListaSQL('clpv_cod_sucu', 'Sucursal|left', "select sucu_cod_sucu, sucu_nom_sucu from saesucu 
                                                                                    where sucu_cod_empr = $idempresa", true, 170, 150);*/
                $ifu->AgregarCampoLista('clpv_cod_sucu', 'Sucursal|left', True, 170, 150, true);
                $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu 
                        where sucu_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('sucu_cod_sucu');
                            $edes_des_edes = $oIfxA->f('sucu_nom_sucu');
                            $ifu->AgregarOpcionCampoLista('clpv_cod_sucu', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                $ifu->AgregarComandoAlCambiarValor('clpv_cod_sucu', 'cargar_zona_lista(' . $clpv_cod_zona . ');');

                /*$ifu->AgregarCampoListaSQL('clpv_pre_ven', 'Tipo de Precio|left', "select nomp_cod_nomp, nomp_nomb_nomp from saenomp 
                                                                        where nomp_cod_empr = $idempresa", true, 170, 150);*/
                $ifu->AgregarCampoLista('clpv_pre_ven', 'Tipo de Precio|LEFT', false, 170, 100, true);
                $sql = "select nomp_cod_nomp, nomp_nomb_nomp from saenomp 
                        where nomp_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('nomp_cod_nomp');
                            $edes_des_edes = $oIfxA->f('nomp_nomb_nomp');
                            $ifu->AgregarOpcionCampoLista('clpv_pre_ven', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                /*$ifu->AgregarCampoListaSQL('clpv_cod_vend', 'Vendedor|left', "select vend_cod_vend, vend_nom_vend from saevend 
                                            where vend_cod_empr = $idempresa order by vend_nom_vend", true, 170, 150);*/
                $ifu->AgregarCampoLista('clpv_cod_vend', 'Vendedor|LEFT', false, 170, 150, true);
                $sql = "select vend_cod_vend, vend_nom_vend from saevend 
                where vend_cod_empr = $idempresa order by vend_nom_vend";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('vend_cod_vend');
                            $edes_des_edes = $oIfxA->f('vend_nom_vend');
                            $ifu->AgregarOpcionCampoLista('clpv_cod_vend', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                $ifu->AgregarCampoLista('tipo_cliente', 'Flujo de Caja|LEFT', true, 170, 100, true);
                $sql = "SELECT cact_cod_cact, cact_nom_cact from saecact
						where cact_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $titu_cod_titu = $oIfxA->f('cact_cod_cact');
                            $titu_des_titu = $oIfxA->f('cact_nom_cact');
                            $ifu->AgregarOpcionCampoLista('tipo_cliente', $titu_des_titu, $titu_cod_titu);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                //tipo proveedor
                $ifu->AgregarCampoLista('tipo_prove', 'Tipo Proveedor|LEFT', true, 170, 100, true);
                $sql = "select tprov_cod_tprov, tprov_des_tprov from saetprov where tprov_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $tprov_cod_tprov = $oIfxA->f('tprov_cod_tprov');
                            $tprov_des_tprov = $oIfxA->f('tprov_des_tprov');
                            $ifu->AgregarOpcionCampoLista('tipo_prove', $tprov_cod_tprov . ' - ' . $tprov_des_tprov, $tprov_cod_tprov);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();


                //tipo proveedor
                $ifu->AgregarCampoLista('pago', 'Forma Pago|LEFT', true, 170, 100, true);
                $sql = "select fpagop_cod_fpagop, fpagop_des_fpagop from saefpagop where fpagop_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $fpagop_cod_fpagop = $oIfxA->f('fpagop_cod_fpagop');
                            $fpagop_des_fpagop = $oIfxA->f('fpagop_des_fpagop');
                            $ifu->AgregarOpcionCampoLista('pago', $fpagop_cod_fpagop . ' - ' . $fpagop_des_fpagop, $fpagop_cod_fpagop);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                //tipo pago
                $ifu->AgregarCampoLista('tipo_pago', 'Destino Pago|LEFT', true, 170, 100, true);
                $sql = "select tpago_cod_tpago, tpago_des_tpago from saetpago where tpago_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $tpago_cod_tpago = $oIfxA->f('tpago_cod_tpago');
                            $tpago_des_tpago = $oIfxA->f('tpago_des_tpago');
                            $ifu->AgregarOpcionCampoLista('tipo_pago', $tpago_cod_tpago . ' - ' . $tpago_des_tpago, $tpago_cod_tpago);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                //pais
                $ifu->AgregarCampoLista('pais', 'Pais Pago|LEFT', true, 170, 100, true);
                $sql = "select paisp_cod_paisp, paisp_des_paisp from saepaisp where paisp_cod_empr = $idempresa order by 2";
                $lista_pais = lista_boostrap($oIfx, $sql, '', 'paisp_cod_paisp',  'paisp_des_paisp');
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $paisp_cod_paisp = $oIfxA->f('paisp_cod_paisp');
                            $paisp_des_paisp = $oIfxA->f('paisp_des_paisp');
                            $ifu->AgregarOpcionCampoLista('pais', $paisp_cod_paisp . ' - ' . $paisp_des_paisp, $paisp_cod_paisp);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                $ifu->AgregarCampoTexto('codigoCliente', '|left', true, '', 50, 100, true);
                $ifu->AgregarComandoAlPonerEnfoque('codigoCliente', 'this.blur();');

                $ifu->AgregarCampoTexto('representante', 'Representante|left', false, '', 500, 200, true);
                $ifu->AgregarComandoAlEscribir('representante', 'form1.representante.value=form1.representante.value.toUpperCase()');

                $ifu->AgregarCampoMemo('observaciones', 'Observaciones|left', false, '', 500, 70, true);

                $ifu->AgregarCampoOculto('telf_op', '');

                $ifu->AgregarCampoOculto('dire_op', '');

                $ifu->AgregarCampoOculto('mail_op', '');

                // campos formulario datos clpv
                $ifu->AgregarCampoTexto('autUsuario', 'Auto. Usuario|left', false, '', 200, 100, true);

                $ifu->AgregarCampoTexto('autImprenta', 'Auto. Imprenta|left', false, '', 200, 100, true);

                $ifu->AgregarCampoNumerico('facturaInicio', 'Factuta Inicio|left', false, '', 100, 9, true);
                $ifu->AgregarComandoAlCambiarValor('facturaInicio', 'completa_ceros(1)');

                $ifu->AgregarCampoNumerico('facturaFin', 'Factura Fin|left', false, '', 100, 9, true);
                $ifu->AgregarComandoAlCambiarValor('facturaFin', 'completa_ceros(2)');

                $ifu->AgregarCampoNumerico('facturaSerie', 'Factura Serie|left', false, '', 60, 6, true);

                $ifu->AgregarCampoFecha('fechaCaduca', 'F. Caduca|left', false, '', 60, 6, true);

                $ifu->AgregarCampoLista('estadoATS', 'Estado|left', false, 170, 150, true);
                $ifu->AgregarOpcionCampoLista('estadoATS', 'Activo', 1);
                $ifu->AgregarOpcionCampoLista('estadoATS', 'Inactivo', 0);

                $ifu->AgregarCampoOculto('codigoCoa', 0);

                //campos del formulario contactos
                $ifu->AgregarCampoTexto('telefono_cli', 'Telefono|left', true, '', 120, 150, true);

                //$fu->AgregarCampoListaSQL('tipo_telefono', 'Tipo|left', "select codigo, tipo from comercial.tipo_telefono", false, 150, 150);

                $ifu->AgregarCampoLista('tipo_telefono', 'Pago|LEFT', false, 170, 150, true);
                $sql = "select codigo, tipo from comercial.tipo_telefono";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('codigo');
                            $edes_des_edes = $oIfxA->f('tipo');
                            $ifu->AgregarOpcionCampoLista('tipo_telefono', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                //$fu->AgregarCampoListaSQL('tipo_operador', 'Tipo|left', "select id, operador from comercial.tipo_operador", false, 150, 150);

                $ifu->AgregarCampoLista('tipo_operador', 'Pago|LEFT', false, 170, 150, true);
                $sql = "select id, operador from comercial.tipo_operador";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('id');
                            $edes_des_edes = $oIfxA->f('operador');
                            $ifu->AgregarOpcionCampoLista('tipo_operador', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                $ifu->AgregarCampoTexto('emai_ema_emai', 'Email|left', true, '', 200, 150, true);
                $ifu->AgregarComandoAlEscribir('emai_ema_emai', 'tipoCorreo(event)');

                // TIPO DE CORREOS
                /*$ifu->AgregarCampoListaSQL('emai_cod_tiem', 'Tipo|left', "select  tiem_cod_tiem, tiem_des_tiem from saetiem where
                                                                            tiem_cod_empr = $idempresa order by 1  ", false, 150, 150);*/

                $ifu->AgregarCampoLista('emai_cod_tiem', 'Pago|LEFT', false, 170, 150, true);
                $sql = "select  tiem_cod_tiem, tiem_des_tiem from saetiem where
                        tiem_cod_empr = $idempresa order by 1 ";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $edes_cod_edes = $oIfxA->f('tiem_cod_tiem');
                            $edes_des_edes = $oIfxA->f('tiem_des_tiem');
                            $ifu->AgregarOpcionCampoLista('emai_cod_tiem', $edes_des_edes, $edes_cod_edes);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                // html campos formulario contactos
                $sHtmlTelf .= '<table class="table table-striped table-bordered table-condensed" style="width: 99%; margin-top: 10px;" align="center">';
                $sHtmlTelf .= '<tr>
                            <td align="center" colspan="4" class="bg-primary">TELEFONOS</td>
                        </tr>';
                $sHtmlTelf .= '<tr>
                                <td align="center">TIPO</td>
                                <td align="center">NUMERO</td>
                                <td align="center">OPERADORA</td>
                                <td align="center">AGREGAR</td>
                            </tr>';
                $sHtmlTelf .= '<tr>
                            <td>' . $ifu->ObjetoHtml('tipo_telefono') . '</td>
                            <td>' . $ifu->ObjetoHtml('telefono_cli') . '</td>
                            <td>' . $ifu->ObjetoHtml('tipo_operador') . '</td>
                            <td align="center">
                                <div class="btn btn-success btn-sm" onclick="agregarEntidad(2);">
                                    <span class="glyphicon glyphicon-plus-sign"></span>
                                </div>
                            </td>
                         </tr>';
                $sHtmlTelf .= '</table>';

                $sHtmlTelf .= '<div id="divReporteTelefono" style="width: 100%; margin-top: 10px;"></div>';


                $sHtmlEmai .= '<table class="table table-bordered table-striped table-condensed" style="width: 99%; margin-top: 10px;" align="center">';
                $sHtmlEmai .= '<tr>
                                <td align="center" class="bg-primary" colspan="3">E-MAIL</td>
                            </tr>';
                $sHtmlEmai .= '<tr>
                                    <td align="center">CORREO ELECTRONICO</td>
                                    <td align="center">TIPO</td>
                                    <td align="center">AGREGAR</td>
                                </tr>';
                $sHtmlEmai .= '<tr>
                            <td>' . $ifu->ObjetoHtml('emai_ema_emai') . '</td>
                            <td>' . $ifu->ObjetoHtml('emai_cod_tiem') . '</td>
                            <td align="center">
                                <div class="btn btn-success btn-sm" onclick="agregarEntidad(3);">
                                    <span class="glyphicon glyphicon-plus-sign"></span>
                                </div>
                            </td>
                         </tr>';
                $sHtmlEmai .= '</table>';

                $sHtmlEmai .= '<div id="divReporteEmail" style="width: 100%; margin-top: 10px;"></div>';

                //formulario direccion cliente
                $ifu->AgregarCampoOculto('idDireccion', '');
                $fu->AgregarCampoListaSQL('tipo_direccion', 'Tipo|left', "select id, tipo from comercial.tipo_direccion", false, 170, 150, true);

                $fu->AgregarCampoListaSQL('tipo_casa', 'Vivienda|left', "select id, tipo from comercial.tipo_casa", false, 170, 150, true);

                $fu->AgregarCampoListaSQL('sectorDire', 'Sector|left', "select id, sector from comercial.sector_direccion", false, 170, 150, true);

                $ifu->AgregarCampoTexto('barrioDire', 'Barrio|left', false, '', 200, 220, true);
                $ifu->AgregarComandoAlEscribir('barrioDire', 'form1.barrioDire.value=form1.barrioDire.value.toUpperCase();');

                $ifu->AgregarCampoTexto('callePrincipal', 'Calle Principal|left', false, '', 500, 220, true);
                $ifu->AgregarComandoAlEscribir('callePrincipal', 'form1.callePrincipal.value=form1.callePrincipal.value.toUpperCase();');

                $ifu->AgregarCampoTexto('numeroDire', 'Numero|left', false, '', 170, 220, true);

                $ifu->AgregarCampoTexto('calleSecundaria', 'Calle Secundaria|left', false, '', 500, 220, true);
                $ifu->AgregarComandoAlEscribir('calleSecundaria', 'form1.calleSecundaria.value=form1.calleSecundaria.value.toUpperCase();');

                $ifu->AgregarCampoTexto('referenciaDire', 'Referencia|left', false, '', 500, 220, true);
                $ifu->AgregarComandoAlEscribir('referenciaDire', 'form1.referenciaDire.value=form1.referenciaDire.value.toUpperCase();');

                $ifu->AgregarCampoTexto('edificioDire', 'Edificio/Conjunto|left', false, '', 200, 220, true);
                $ifu->AgregarComandoAlEscribir('edificioDire', 'form1.edificioDire.value=form1.edificioDire.value.toUpperCase();');

                $ifu->AgregarCampoNumerico('antiguedadDire', 'Antiguedad|left', false, '', 120, 9, true);

                $ifu->AgregarCampoTexto('direccion', 'Direccion|left', false, '', 500, 220, true);
                $ifu->AgregarComandoAlEscribir('direccion', 'form1.direccion.value=form1.direccion.value.toUpperCase()');

                $ifu->AgregarComandoAlEscribir('direccion', 'form1.direccion_cli.value=form1.direccion_cli.value.toUpperCase()');

                $fu->cCampos["tipo_direccion"]->xValor = 1;

                $sHtmlDire .= '<table class="table table-striped table-condensed" style="width: 90%; margin-top: 10px;" align="center">';
                $sHtmlDire .= '<tr>
                                <td colspan="4">
                                    <div class="btn-group"> 
                                        <div class="btn btn-primary btn-sm" onclick="agregarEntidad(1);">
                                            <span class="glyphicon glyphicon-floppy-disk"></span>
                                            Guardar
                                        </div> 
                                        
                                    </div> 
                                </td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td align="center" class="bg-primary" colspan="4">DIRECCIONES</td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td>' . $fu->ObjetoHtmlLBL('tipo_direccion') . '</td>
                                <td>' . $fu->ObjetoHtml('tipo_direccion') . '' . $ifu->ObjetoHtml('idDireccion') . '</td>
                                <td>' . $fu->ObjetoHtmlLBL('tipo_casa') . '</td>
                                <td>' . $fu->ObjetoHtml('tipo_casa') . '</td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td>' . $fu->ObjetoHtmlLBL('sectorDire') . '</td>
                                <td>' . $fu->ObjetoHtml('sectorDire') . '</td>
                                <td>' . $ifu->ObjetoHtmlLBL('barrioDire') . '</td>
                                <td>' . $ifu->ObjetoHtml('barrioDire') . '</td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td>' . $ifu->ObjetoHtmlLBL('callePrincipal') . '</td>
                                <td>' . $ifu->ObjetoHtml('callePrincipal') . '</td>
                                <td>' . $ifu->ObjetoHtmlLBL('numeroDire') . '</td>
                                <td>' . $ifu->ObjetoHtml('numeroDire') . '</td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td>' . $ifu->ObjetoHtmlLBL('calleSecundaria') . '</td>
                                <td>' . $ifu->ObjetoHtml('calleSecundaria') . '</td>
                                <td>' . $ifu->ObjetoHtmlLBL('edificioDire') . '</td>
                                <td>' . $ifu->ObjetoHtml('edificioDire') . '</td>
                            </tr>';
                $sHtmlDire .= '<tr>
                                <td>' . $ifu->ObjetoHtmlLBL('referenciaDire') . '</td>
                                <td>' . $ifu->ObjetoHtml('referenciaDire') . '</td>
                                <td>' . $ifu->ObjetoHtmlLBL('antiguedadDire') . '</td>
                                <td>' . $ifu->ObjetoHtml('antiguedadDire') . ' Meses</td>
                            </tr>';
                $sHtmlDire .= '</table>';

                $sHtmlDire .= '<div id="divReporteDireccion" style="width: 100%; margin-top: 10px;"></div>';

                //datos sri
                $tableDatos .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;">
                                    <tr>
                                            <td colspan="4">
												<div class="btn-group">
													<div class="btn btn-primary btn-sm" onclick="nuevoCoa();">
														<span class="glyphicon glyphicon-file"></span>
														Nuevo
													</div>
													<div class="btn btn-primary btn-sm" onclick="guardarCcli();">
														<span class="glyphicon glyphicon-floppy-disk"></span>
														Guardar
													</div>
												</div>
                                            </td>
                                    </tr>	
                                    <tr>
                                            <td colspan="4" align="center" class="bg-primary">DATOS SRI PROVEEDOR</td>
                                    </tr>';
                $tableDatos .= '<tr>
                                            <td>' . $ifu->ObjetoHtmlLBL('autUsuario') . '</td>
                                            <td>' . $ifu->ObjetoHtml('autUsuario') . '' . $ifu->ObjetoHtml('codigoCoa') . '</td>
                                            <td >' . $ifu->ObjetoHtmlLBL('autImprenta') . '</td>
                                            <td>' . $ifu->ObjetoHtml('autImprenta') . '</td>
                                    </tr>
                                    <tr>
                                            <td>' . $ifu->ObjetoHtmlLBL('facturaInicio') . '</td>
                                            <td>' . $ifu->ObjetoHtml('facturaInicio') . '</td>
                                            <td>' . $ifu->ObjetoHtmlLBL('facturaFin') . '</td>
                                            <td>' . $ifu->ObjetoHtml('facturaFin') . '</td>
                                    </tr>
                                    <tr>
                                            <td>' . $ifu->ObjetoHtmlLBL('facturaSerie') . '</td>
                                            <td>' . $ifu->ObjetoHtml('facturaSerie') . '</td>
                                            <td>' . $ifu->ObjetoHtmlLBL('fechaCaduca') . '</td>
                                            <td>' . $ifu->ObjetoHtml('fechaCaduca') . '</td>
                                    </tr>
                                    <tr>
                                            <td>' . $ifu->ObjetoHtmlLBL('estadoATS') . '</td>
                                            <td colspan="3">' . $ifu->ObjetoHtml('estadoATS') . '</td>
                                    </tr>';
                $tableDatos .= '</table>';


                //cash management
                $ifu->AgregarCampoTexto('cuenta', '# Cuenta|left', false, '', 150, 100, true);
                $ifu->AgregarCampoTexto('cod_inter', 'CCI|left', false, '', 150, 100, true);

                $ifu->AgregarCampoTexto('identificacion_sf', 'Ced./Ruc|left', false, '', 150, 100, true);

                $ifu->AgregarCampoLista('banco', 'Banco|LEFT', false, 150, 100, true);
                $sql = "select banc_cod_banc, banc_nom_banc
						from saebanc 
						where banc_cod_empr = $idempresa";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $banc_cod_banc = $oIfxA->f('banc_cod_banc');
                            $banc_nom_banc = $oIfxA->f('banc_nom_banc');
                            $ifu->AgregarOpcionCampoLista('banco', $banc_nom_banc, $banc_cod_banc);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();

                //tipo cuenta
                $ifu->AgregarCampoLista('tipoCuenta', 'Tipo Cuenta|left', false, 150, 100, true);
                $ifu->AgregarOpcionCampoLista('tipoCuenta', 'CORRIENTE', '00', true);
                $ifu->AgregarOpcionCampoLista('tipoCuenta', 'AHORROS', '10', true);
                $ifu->AgregarOpcionCampoLista('tipoCuenta', 'PAGO VIRTUAL', '20', true);

                $tableCash .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;">
                                    <tr>
                                            <td colspan="6">
												<div class="btn-group">
													<div class="btn btn-primary btn-sm" onclick="editarCash();">
														<span class="glyphicon glyphicon-floppy-disk"></span>
														Guardar
													</div>
												</div>
                                            </td>
                                    </tr>	
                                    <tr>
                                            <td colspan="6" align="center" class="bg-primary">CASH MANAGEMENT</td>
                                    </tr>';


                $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];  // 593 ECUADOR 51 PERU
                // VALIDACION CAMPOS CUENTAS BANCARIAS PERU
                if ($S_PAIS_API_SRI == '51') {

                    //DOI    
                    $ifu->AgregarCampoLista('tipo_iden', 'DOI tipo|LEFT', false, 150, 100, true);
                    $sql = "select * from comercial.tipo_iden_clpv_pais where pais_codigo_inter='$S_PAIS_API_SRI'";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $identificacion = $oIfxA->f('identificacion');
                                $id_iden = $oIfxA->f('id_iden_pais');

                                $ifu->AgregarOpcionCampoLista('tipo_iden', $identificacion, $id_iden);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();

                    //MONEDA

                    $ifu->AgregarCampoLista('mone_cash', 'Moneda|LEFT', false, 150, 100, true);
                    $sql = "select mone_cod_mone, upper(mone_des_mone) as mone_des_mone
                                    from saemone
                                    where mone_cod_empr = $idempresa
                                    order by mone_des_mone";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $cod_mone = $oIfxA->f('mone_cod_mone');
                                $des_mone = $oIfxA->f('mone_des_mone');

                                $ifu->AgregarOpcionCampoLista('mone_cash', $des_mone, $cod_mone);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();


                    $tableCash .= '<tr>
                                    <td>' . $ifu->ObjetoHtmlLBL('tipo_iden') . '</td>
									<td>' . $ifu->ObjetoHtml('tipo_iden') . '</td>
									<td>DOI numero: </td>
									<td>' . $ifu->ObjetoHtml('identificacion_sf') . '</td>
									</tr>';

                    $tableCash .= '<tr><td>' . $ifu->ObjetoHtmlLBL('cuenta') . '</td>
                                        <td>' . $ifu->ObjetoHtml('cuenta') . '</td>
                                        <td>' . $ifu->ObjetoHtmlLBL('tipoCuenta') . '</td>
									<td>' . $ifu->ObjetoHtml('tipoCuenta') . '</td>
                                        
									
								</tr>';

                    $tableCash .= '<tr>
                                    <td>' . $ifu->ObjetoHtmlLBL('banco') . '</td>
									<td>' . $ifu->ObjetoHtml('banco') . '</td>
                                    <td>' . $ifu->ObjetoHtmlLBL('cod_inter') . '</td>
									<td>' . $ifu->ObjetoHtml('cod_inter') . '</td>
									
                                    
								</tr>
                                <tr>
                                    <td>' . $ifu->ObjetoHtmlLBL('mone_cash') . '</td>
									<td>' . $ifu->ObjetoHtml('mone_cash') . '</td>
                                    <td coslpan="3" >
                                    <div class="btn btn-success btn-sm"  onclick="agregarEntidad(4);">
                                        <span class="glyphicon glyphicon-plus-sign"></span>
                                        Agregar
                                    </div>
                                    </td>
                                </tr>';
                } else {
                    //TIPO DE IDENTIFICACION    
                    $ifu->AgregarCampoLista('tipo_iden', 'TIPO ID|LEFT', false, 150, 100, true);
                    $sql = "select * from comercial.tipo_iden_clpv_pais where pais_codigo_inter='$S_PAIS_API_SRI'";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $identificacion = $oIfxA->f('identificacion');
                                $id_iden = $oIfxA->f('id_iden_pais');

                                $ifu->AgregarOpcionCampoLista('tipo_iden', $identificacion, $id_iden);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();

                    //MONEDA

                    $ifu->AgregarCampoLista('mone_cash', 'Moneda|LEFT', false, 150, 100, true);
                    $sql = "select mone_cod_mone, upper(mone_des_mone) as mone_des_mone
                                    from saemone
                                    where mone_cod_empr = $idempresa
                                    order by mone_des_mone";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $cod_mone = $oIfxA->f('mone_cod_mone');
                                $des_mone = $oIfxA->f('mone_des_mone');

                                $ifu->AgregarOpcionCampoLista('mone_cash', $des_mone, $cod_mone);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();
                    $tableCash .= '<tr>
                                    <td>' . $ifu->ObjetoHtmlLBL('tipo_iden') . '</td>
									<td>' . $ifu->ObjetoHtml('tipo_iden') . '</td>

									<td>' . $ifu->ObjetoHtmlLBL('identificacion_sf') . '</td>
									<td>' . $ifu->ObjetoHtml('identificacion_sf') . '</td>
									</tr>';
                    $tableCash .= '<tr><td>' . $ifu->ObjetoHtmlLBL('cuenta') . '</td>
                                    <td>' . $ifu->ObjetoHtml('cuenta') . '</td>
                                    <td>' . $ifu->ObjetoHtmlLBL('tipoCuenta') . '</td>
                                <td>' . $ifu->ObjetoHtml('tipoCuenta') . '</td>
                                    
                                
                            </tr>';


                    $tableCash .= '<tr><td>' . $ifu->ObjetoHtmlLBL('banco') . '</td>
									<td>' . $ifu->ObjetoHtml('banco') . '</td>
									 <td>Codigo Interbancario:</td>
									<td>' . $ifu->ObjetoHtml('cod_inter') . '</td>
								</tr>
                                <tr>
                                    <td>' . $ifu->ObjetoHtmlLBL('mone_cash') . '</td>
									<td>' . $ifu->ObjetoHtml('mone_cash') . '</td>
                                    <td coslpan="3" >
                                    <div class="btn btn-success btn-sm"  onclick="agregarEntidad(4);">
                                        <span class="glyphicon glyphicon-plus-sign"></span>
                                        Agregar
                                    </div>
                                    </td>
                                </tr>';
                }



                $tableCash .= '</table>';
                $tableCash .= '<div id="divConsultaCash" style="width: 80%; margin-top: 10px;"></div>';

                //plantillas proveedor
                $ifu->AgregarCampoTexto('idPlantilla', 'Id|left', false, '', 50, 9, true);
                $ifu->AgregarComandoAlPonerEnfoque('idPlantilla', 'this.blur();');

                $ifu->AgregarCampoTexto('codigoPlantilla', 'Codigo|left', false, '', 150, 100, true);

                $ifu->AgregarCampoTexto('nombrePlantilla', 'Nombre|left', false, '', 400, 300, true);
                $ifu->AgregarComandoAlEscribir('nombrePlantilla', 'form1.nombrePlantilla.value=form1.nombrePlantilla.value.toUpperCase()');

                $ifu->AgregarCampoTexto('detallePlantilla', 'Detalle|left', false, '', 400, 300, true);
                $ifu->AgregarComandoAlEscribir('detallePlantilla', 'form1.detallePlantilla.value=form1.detallePlantilla.value.toUpperCase()');

                $ifu->AgregarCampoTexto('cuentaAplicada', 'Cuenta Aplicada|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('cuentaAplicada', 'ventanaCuentasContables(event, 1);');

                $ifu->AgregarCampoTexto('creditoBienes', 'Credito Bienes|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('creditoBienes', 'ventanaCuentasContables(event, 2);');

                $ifu->AgregarCampoTexto('creditoServicios', 'Credito Servicios|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('creditoServicios', 'ventanaCuentasContables(event, 3);');

                $ifu->AgregarCampoTexto('retencionBienes', 'Retencion Fuente Bienes|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('retencionBienes', 'ventanaRetenciones(event, 1);');

                $ifu->AgregarCampoTexto('retencionServicios', 'Retencion Fuente Servicios|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('retencionServicios', 'ventanaRetenciones(event, 2);');

                $ifu->AgregarCampoTexto('retencionIvaBienes', 'Retencion Iva Bienes|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('retencionIvaBienes', 'ventanaRetenciones(event, 3);');

                $ifu->AgregarCampoTexto('retencionIvaServicios', 'Retencion Iva Servicios|left', false, '', 150, 100, true);
                $ifu->AgregarComandoAlEscribir('retencionIvaServicios', 'ventanaRetenciones(event, 4);');

                $tablePlantilla .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;">
                                    <tr>
                                            <td colspan="6">
												<div class="btn-group">
													<div class="btn btn-primary btn-sm" onclick="guardarPlantilla();">
														<span class="glyphicon glyphicon-floppy-disk"></span>
														Guardar
													</div>
												</div>
                                            </td>
                                    </tr>	
                                    <tr>
                                            <td colspan="6" align="center" class="bg-primary">PLANTILLAS FACTURAS</td>
                                    </tr>';
                $tablePlantilla .= '<tr>
										<td>' . $ifu->ObjetoHtmlLBL('codigoPlantilla') . '</td>
										<td colspan="3">' . $ifu->ObjetoHtml('idPlantilla') . '' . $ifu->ObjetoHtml('codigoPlantilla') . '</td>
									</tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('nombrePlantilla') . '</td>
										<td colspan="3">' . $ifu->ObjetoHtml('nombrePlantilla') . '</td>
                                    </tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('detallePlantilla') . '</td>
										<td colspan="3">' . $ifu->ObjetoHtml('detallePlantilla') . '</td>
                                    </tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('cuentaAplicada') . '</td>
										<td colspan="3">' . $ifu->ObjetoHtml('cuentaAplicada') . '
											<div style="display:none" class="btn btn-success btn-sm" onclick="cuentaAplicada();">
												<span class="glyphicon glyphicon-th"></span>
											</div>
										</td>
                                    </tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('creditoBienes') . '</td>
										<td>' . $ifu->ObjetoHtml('creditoBienes') . '</td>
										<td>' . $ifu->ObjetoHtmlLBL('creditoServicios') . '</td>
										<td>' . $ifu->ObjetoHtml('creditoServicios') . '</td>
                                    </tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('retencionBienes') . '</td>
										<td>' . $ifu->ObjetoHtml('retencionBienes') . '</td>
										<td>' . $ifu->ObjetoHtmlLBL('retencionServicios') . '</td>
										<td>' . $ifu->ObjetoHtml('retencionServicios') . '</td>
                                    </tr>
									<tr>
										<td>' . $ifu->ObjetoHtmlLBL('retencionIvaBienes') . '</td>
										<td>' . $ifu->ObjetoHtml('retencionIvaBienes') . '</td>
										<td>' . $ifu->ObjetoHtmlLBL('retencionIvaServicios') . '</td>
										<td>' . $ifu->ObjetoHtml('retencionIvaServicios') . '</td>
                                    </tr>';
                $tablePlantilla .= '</table>';


                //campos formulario producto servicio
                //bodega
                $ifu->AgregarCampoLista('idBodegaProdServ', '|LEFT', false, 150, 100, true);
                $sql = "select  b.bode_cod_bode, b.bode_nom_bode, su.sucu_nom_sucu
						from saebode b, saesubo s, saesucu su
						where
						s.subo_cod_sucu = su.sucu_cod_sucu and
						b.bode_cod_bode = s.subo_cod_bode and
						b.bode_cod_empr = $idempresa and
						s.subo_cod_empr = $idempresa
						order by s.subo_cod_sucu, b.bode_nom_bode";
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $bode_cod_bode = $oIfxA->f('bode_cod_bode');
                            $bode_nom_bode = $oIfxA->f('bode_nom_bode');
                            $sucu_nom_sucu = $oIfxA->f('sucu_nom_sucu');
                            $ifu->AgregarOpcionCampoLista('idBodegaProdServ', $bode_nom_bode . ' - ' . $sucu_nom_sucu, $bode_cod_bode);
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();
                $ifu->AgregarComandoAlCambiarValor('idBodegaProdServ', 'focoBodega();');

                $ifu->AgregarCampoTexto('prodProdServ', '|LEFT', false, '', 200, 100, true);
                $ifu->AgregarComandoAlEscribir('prodProdServ', 'autocompletarProdServ(event);');

                $ifu->AgregarCampoTexto('alternoProdServ', '|LEFT', false, '', 110, 100, true);

                $ifu->AgregarCampoNumerico('precioProdServ', 'Precio|left', false, '', 60, 9, true);

                $ifu->AgregarCampoNumerico('pactadoProdServ', 'Ds1|left', false, 0, 60, 9, true);

                $ifu->AgregarCampoNumerico('diasProdServ', 'Ds2|left', false, 0, 60, 9, true);

                $ifu->AgregarCampoNumerico('mermaProdServ', 'Ds2|left', false, 0, 60, 9, true);

                $ifu->AgregarCampoTexto('detalleProdServ', '|LEFT', false, '', 200, 100, true);

                $ifu->AgregarCampoOculto('codProdProdServ', '');


                $tableProdServ .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;">
									<tr>
										<td colspan="9" align="center" class="bg-primary">LISTADO PRODUCTO</td>
									</tr>
									<tr class="info">
										<td>BODEGA</td>
										<td>PRODUCTO</td>
										<td>COD. ALTERNO</td>
										<td>PRECIO ULT.</td>
										<td>PRECIO PACT.</td>
										<td>DIAS ENTR.</td>
										<td>VAL MERMA</td>
										<td>OBSERVACIONES</td>
										<td></td>
									</tr>';
                $tableProdServ .= '<tr>
										<td>' . $ifu->ObjetoHtml('idBodegaProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('prodProdServ') . '' . $ifu->ObjetoHtml('codProdProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('alternoProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('precioProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('pactadoProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('diasProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('mermaProdServ') . '</td>
										<td>' . $ifu->ObjetoHtml('detalleProdServ') . '</td>
										<td>
											<div class="btn btn-success btn-sm" onclick="guardarProdServ(1);">
												<span class="glyphicon glyphicon-circle-arrow-down"></span>
												Agregar
											</div>
										</td>
									</tr>';
                $tableProdServ .= '</table>';

                //descuentos por linea

                $ifu->AgregarCampoListaSQL('linp', 'Linea Inventario|left', "select linp_cod_linp, linp_des_linp
																			from saelinp where linp_cod_empr = $idempresa", false, 170, 150, true);
                $ifu->AgregarComandoAlCambiarValor('linp', 'focoLinp();');

                $ifu->AgregarCampoNumerico('dsctoLinp', 'Descuento %|left', false, '', 50, 9, true);

                $tableDsctoLinp .= '<table align="center" class="table table-striped table-condensed" style="width: 80%;">
									<tr>
										<td colspan="8" align="center" class="bg-primary">DESCUENTO LINEA INVENTARIO</td>
									</tr>';
                $tableDsctoLinp .= '<tr>
										<td>' . $ifu->ObjetoHtmlLBL('linp') . '</td>
										<td>' . $ifu->ObjetoHtml('linp') . '</td>
										<td>' . $ifu->ObjetoHtmlLBL('dsctoLinp') . '</td>
										<td>' . $ifu->ObjetoHtml('dsctoLinp') . '</td>
										<td>
                                            <div class="btn btn-success btn-sm" onclick="guardarDsctoLinpCliente();">
                                                <span class="glyphicon glyphicon-plus-sign"></span>
                                                Agregar
                                            </div>
										</td>
									</tr>';
                $tableDsctoLinp .= '</table>';

                $ifu->AgregarCampoCheck('clpv_ret_sn',  'Aplica Retencion S/N|left', false, 'S');



                //------------------------------------------------------------------
                //INICIO ADJUNTOS APARTADO DE SUBIR ADJUNTOS VISUAL
                //------------------------------------------------------------------

                //adjuntos
                $ifu->AgregarCampoTexto('titulo', 'Titulo|left', false, '', 200, 200, true);
                $ifu->AgregarComandoAlEscribir('titulo', 'form1.titulo.value=form1.titulo.value.toUpperCase();');

                $ifu->AgregarCampoArchivo('archivo', 'Archivo|left', false, '', 100, 100, '', true);

                // Tipo de documento
                $ifu->AgregarCampoLista('tipo_adj', 'Tipo Documento|left', false, 150, 150, true);
                $ifu->AgregarOpcionCampoLista('tipo_adj', 'DOCUMENTO GENERAL', 0);
                $sqlUafeModal = "SELECT emmpr_uafe_cprov FROM saeempr WHERE empr_cod_empr = $idempresa";
                $usaUafeModal = consulta_string($sqlUafeModal, 'emmpr_uafe_cprov', $oIfx, 'f');
                $mostrarAdjuntoUafe = ($usaUafeModal == 't' || $usaUafeModal == 1 || $usaUafeModal == '1' || $usaUafeModal === true);
                if ($mostrarAdjuntoUafe) {
                    $ifu->AgregarOpcionCampoLista('tipo_adj', 'DOCUMENTO UAFE', 1);
                }

                // Documento UAFE se llena dinámicamente
                $ifu->AgregarCampoLista('id_archivo_uafe', 'Documento UAFE|left', false, 200, 200, true);

                // Cargar catálogo UAFE
                $sqlUafe = "
                    SELECT id, titulo
                    FROM comercial.archivos_uafe
                    WHERE empr_cod_empr = $idempresa
                    AND estado = 'AC'
                    ORDER BY id;
                ";
                //echo $sqlUafe;
                //exit;

                if ($mostrarAdjuntoUafe) {
                    $oCon->Query($sqlUafe);//liberar la conexion

                    if ($oCon->NumFilas() > 0) {
                        do {
                            $idu = $oCon->f('id');
                            $tit = $oCon->f('titulo');
                            $ifu->AgregarOpcionCampoLista('id_archivo_uafe', $tit, $idu);
                        } while ($oCon->SiguienteRegistro());
                    }
                }

                $tableAdjuntos .= '<table class="table table-striped table-condensed" align="center" style="width: 99%;">';
                $tableAdjuntos .= '<tr>';
                $tableAdjuntos .= '<td colspan="6"><h5>ADJUNTOS <small>Ingreso Informacion</small></h5></td>';
                $tableAdjuntos .= '</tr>';

                $tableAdjuntos .= '<tr>';
                $tableAdjuntos .=   '<td colspan="6">
                                        <div class="btn btn-primary btn-sm" onclick="guardarAdjuntos();">
                                            <span class="glyphicon glyphicon-floppy-disk"></span>
                                            Guardar
                                        </div>
                                    </td>';
                $tableAdjuntos .= '</tr>';

                
                //Tipo Documento
                $tableAdjuntos .= '<tr>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtmlLBL('tipo_adj') . '</td>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtml('tipo_adj') . '</td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .= '</tr>';

                //Documento UAFE (se mostrará/ocultará completa)
                $tableAdjuntos .= '<tr id="fila_uafe">';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtmlLBL('id_archivo_uafe') . '</td>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtml('id_archivo_uafe') . '</td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .=   '<td></td>';
                $tableAdjuntos .= '</tr>';


                $tableAdjuntos .= '<tr>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtmlLBL('titulo') . '</td>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtml('titulo') . '</td>';
                
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtmlLBL('archivo') . '</td>';
                $tableAdjuntos .=   '<td>' . $ifu->ObjetoHtml('archivo') . '</td>';

                $tableAdjuntos .= '<td align="center">
										<div class="btn btn-success btn-sm" onclick="agregarArchivo();">
											<span class="glyphicon glyphicon-plus-sign"></span>
											Agregar
										</div>
								   <td>';
                $tableAdjuntos .= '</tr>';


                $tableAdjuntos .= '</table>';
                //------------------------------------------------------------------
                //FIN ADJUNTOS APARTADO DE SUBIR ADJUNTOS VISUAL
                //------------------------------------------------------------------
                // MONEDA
                $ifu->AgregarCampoListaSQL('clpv_cod_mone', 'Moneda|left', "select mone_cod_mone, mone_des_mone  from saemone where mone_cod_empr = $idempresa ", true, 200, 200, true);
                $ifu->cCampos["clpv_cod_mone"]->xValor = 1;

            break;
        }

        //------------------------------------------------------------------------------
        //  INICIO VALIDACIÓN UAFE PARA HABILITAR/DESHABILITAR ESTADO
        //------------------------------------------------------------------------------

        // Consultar si la empresa usa validación UAFE
        $sqlUafeEmp = "
            SELECT emmpr_uafe_cprov
            FROM saeempr
            WHERE empr_cod_empr = $idempresa;
        ";

        $valorUafeRaw = consulta_string($sqlUafeEmp, 'emmpr_uafe_cprov', $oCon, 'f');
        $usaUAFE = valorLogicoActivado($valorUafeRaw);
        $oReturn->script("
            console.log('%cline 1: VALOR RAW DE usaUAFE = ' + JSON.stringify('$valorUafeRaw'), 'color:yellow;font-weight:bold');
            console.log('%cline 1b: usaUAFE normalizado = ' + JSON.stringify('$usaUAFE'), 'color:yellow;font-weight:bold');
        ");
        $oReturn->script("window.usaUafeEmpresa = " . ($usaUAFE ? 'true' : 'false') . ";");

        //------------------------------------------------------------------------------
        //  FIN VALIDACIÓN UAFE PARA HABILITAR/DESHABILITAR ESTADO
        //------------------------------------------------------------------------------

        //------------------------------------------------------------------------------
    //  BLOQUE UAFE: BLOQUEAR/HABILITAR ESTADO EN EDICIÓN SEGÚN DOCUMENTOS
    //------------------------------------------------------------------------------

    if ($sAccion == 'editar' && $usaUAFE) {

        $bloquearPorUafe = debeBloquearEstadoPorUafe($idempresa, $cod, $oCon);

        $oReturn->script("console.log('%cEstado UAFE detectado en carga inicial → ' + ($bloquearPorUafe ? 'BLOQUEAR' : 'HABILITAR'),'color:blue;font-weight:bold');");
        $oReturn->script("habilitarEstadoProveedor(" . ($bloquearPorUafe ? 'true' : 'false') . ");");
    }

        //variable del chekc del web service
        $S_URL_API_SRI_SN = $_SESSION['S_URL_API_SRI_SN'];

        $sHtml .= '<table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">
						<tr>
							<td>
								<div class="btn-group">
									<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
										<span class="glyphicon glyphicon-file"></span>
										Nuevo
									</div>
									<div id="imgSave" class="btn btn-primary btn-sm" onclick="guardar();">
										<span class="glyphicon glyphicon-floppy-disk"></span>
										Guardar
                                    </div>
                                    <div class="btn btn-primary btn-sm" onclick="verMapaContr(' . $id_contrato . ');">
                                        <span class="glyphicon glyphicon-list"></span>
                                        Localiza
                                    </div>
								</div>
							</td>
							<td align="right">
								<div class="btn btn-danger btn-sm" onclick="genera_formulario();">
									<span class="glyphicon glyphicon-remove"></span>
									Cancelar
								</div>
							</td>
						</tr>	  	
					</table>';
        $sHtml .= '<table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">
                    <tr>
						<td colspan="4" align="center" class="bg-primary" id="lgTitulo_frame">FICHA DE PROVEEDOR</td>
                    </tr>
                    <tr class="bg-warning">
                            <td colspan="4" align="center">Los campos con * son de ingreso obligatorio</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('identificacion') . '</td>
                        <td>' . $ifu->ObjetoHtml('identificacion') . '</td>
                        
                        <td>' . $ifu->ObjetoHtmlLBL('ruc_cli') . '</td>
                        <td><div class="form-group-sm input-group" style="display: flex; ">
                                ' . $ifu->ObjetoHtml('ruc_cli') . '
                                <span id="autocompletarBtn" class="input-group-addon primary" 
                                    style="cursor: pointer; padding: 6px 10px; font-size: 1.3em; margin-left: -1px; display: flex; align-items: center; justify-content: center;'
            . ($_SESSION['S_URL_API_SRI_SN'] == 'N' ? ' pointer-events: none; opacity: 0.5;' : '') . '"
                                    onclick="autocompletar_infomacion();" 
                                    title="Autocompletar información">
                                    <i class="fa fa-download"></i>
                                </span>
                            </div>
                        </td>
                </tr>';
        $sHtml .= '<tr>
                        <td>*Codigo Char</td>
                        <td><input style="color:red; width:80px" name="cod_char_clpv" id="cod_char_clpv" class="form-control"/></td>
                    </tr>
                    <tr>
                        <td>' . $ifu->ObjetoHtmlLBL('nombre') . '</td>
                        <td colspan="4">' . $ifu->ObjetoHtml('codigoCliente') . '' . $ifu->ObjetoHtml('nombre') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('nombre_comercial') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('nombre_comercial') . '</td>
                </tr>';
        $sHtml .= '<tr>
                        <td>* Descripción de actividades</td>
                        <td colspan="3">
                            <input class="form-control" type="text" id="clpv_desc_actividades" name="clpv_desc_actividades" maxlength="255" style="width: 98%;" />
                        </td>
                </tr>';
        $sHtml .= '<tr> 
                        <td>' . $ifu->ObjetoHtmlLBL('grupo') . '</td>
                        <td>' . $ifu->ObjetoHtml('grupo') . '</td>
						<td>* Estado</td>
						<td colspan="1">

                        <label>Activo</label><input type="radio" name="estado" id="AC" value="A" />
                                                <label>Suspendido</label><input type="radio" name="estado" id="SU" value="S" />
                                                <label>Pendiente</label><input type="radio" name="estado" id="PE" value="P"  />


					</td>
                </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('clpv_cod_sucu') . '</td>
                        <td>' . $ifu->ObjetoHtml('clpv_cod_sucu') . '</td>
                        <td>' . $ifu->ObjetoHtmlLBL('zona') . '</td>
                        <td>' . $ifu->ObjetoHtml('zona') . '</td>
                </tr>';
        $sHtml .= '<tr>
                        <td style="color: red;">* Contribuyente Especial</td>
                        <td><input type="checkbox" name="contriEspecial" id="contriEspecial" value="1"/></td>
                        <td>' . $ifu->ObjetoHtmlLBL('tipo_cliente') . '</td>
                        <td>' . $ifu->ObjetoHtml('tipo_cliente') . '</td>
                </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('limite') . '</td>
                        <td>' . $ifu->ObjetoHtml('limite') . '</td>
                        <td>' . $ifu->ObjetoHtmlLBL('dias_pago') . '</td>
                        <td>' . $ifu->ObjetoHtml('dias_pago') . '</td>
                </tr>';
        $sHtml .= '<tr>
                </tr>';
        $sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('dsctDetalle') . '</td>
						<td>' . $ifu->ObjetoHtml('dsctDetalle') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('dsctGeneral') . '</td>
						<td>' . $ifu->ObjetoHtml('dsctGeneral') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('tipo_prove') . '</td>
                        <td>' . $ifu->ObjetoHtml('tipo_prove') . '</td>
                        <td>' . $ifu->ObjetoHtmlLBL('pago') . '</td>
                        <td>' . $ifu->ObjetoHtml('pago') . '</td>
                </tr>';
        /**<select id="pais" name="pais" class="select2" style="height:25px; text-align:left">
											<option value="0">Seleccione una opcion..</option>
											'.$lista_pais.'
										</select>*/
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('tipo_pago') . '</td>
                        <td>' . $ifu->ObjetoHtml('tipo_pago') . '</td>
                        <td>' . $ifu->ObjetoHtmlLBL('pais') . '</td>
                        <td>' . $ifu->ObjetoHtml('pais') . '</td>
                </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('clpv_ret_sn') . '</td>
                        <td>' . $ifu->ObjetoHtml('clpv_ret_sn') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('clpv_cod_mone') . '</td>
                        <td>' . $ifu->ObjetoHtml('clpv_cod_mone') . '</td>
                    </tr>';
        $sHtml .= '<tr>
        <td>' . $ifu->ObjetoHtmlLBL('clpv_par_rela') . '</td>
        <td>' . $ifu->ObjetoHtml('clpv_par_rela') . '</td>
        </tr>';

        $sHtml .= '<tr>
        <td>' . $ifu->ObjetoHtmlLBL('clpv_tec_sn') . '</td>
        <td>' . $ifu->ObjetoHtml('clpv_tec_sn') . '</td>
        </tr>';

        $sHtml .= '<tr>
        <td>' . $ifu->ObjetoHtmlLBL('codigoUnico') . '</td>
        <td>' . $ifu->ObjetoHtml('codigoUnico') . '</td>

        <td>' . $ifu->ObjetoHtmlLBL('cod_cuenta_in') . '</td>
        <td>' . $ifu->ObjetoHtml('cod_cuenta_in') . '</td>
        </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('representante') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('representante') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtmlLBL('observaciones') . '</td>
                        <td colspan="3">' . $ifu->ObjetoHtml('observaciones') . '</td>
                    </tr>';
        $sHtml .= '<tr>
                        <td>' . $ifu->ObjetoHtml('telf_op') . '' . $ifu->ObjetoHtml('dire_op') . '' . $ifu->ObjetoHtml('mail_op') . '</td>
					</tr>';
        $sHtml .= '</table>';

        $sHtml .= '<table class="table table-striped table-condensed" align="center" style="width: 100%;">';
        $sHtml .= '<tr>
            <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="4">INFORMACION ADICIONAL</th>
                  </tr> ';
        $sHtml .= '<tr>
                  <td><i class="fa fa-facebook-square" aria-hidden="true">  FACEBOOK</i></td>
                  <td><input  class="form-control" type="text" id="facebook_cli" name="facebook_cli" /></td>
                  <td>  <i class="fa fa-instagram" aria-hidden="true"></i>  INSTAGRAM</td>
                  <td><input class="form-control" type="text" id="insta_cli" name="insta_cli" /></td>
              </tr>';

        $sHtml .= '<tr>
              <td><i class="fa fa-globe" aria-hidden="true"> Pagina Web</i></td>
              <td><input class="form-control" type="text" id="pagina_web_cli" name="pagina_web_cli" /></td>

              <td><i class="fa fa-sticky-note-o" aria-hidden="true"> Notas</i></td>
              <td><input class="form-control" type="text" id="notas_cli" name="notas_cli" /></td>
              
          </tr>';

        $sHtml .= '<tr>
          <td><i class="fa fa-birthday-cake" aria-hidden="true"> Aniversario Empresa</i></td>
          <td><input class="form-control" type="date" id="aniversario_empr" name="aniversario_empr" /></td>

          <td><i class="" aria-hidden="true"> Nombre Contacto</i></td>
          <td><input class="form-control" type="text" id="nombre_contacto_" name="nombre_contacto_" /></td>

            </tr>
            
            
            <tr>
                <td><i class="" aria-hidden="true"> Telefono Contacto</i></td>
                <td><input class="form-control" type="text" id="tlf_contacto_" name="tlf_contacto_" /></td>

                <td><i class="" aria-hidden="true"> Correo Contacto</i></td>

                <td>
                    <input class="form-control" type="text" id="correo_contacto_" name="correo_contacto_" />
                </td>


            </tr>';

        $sqlUafeModal = "SELECT emmpr_uafe_cprov FROM saeempr WHERE empr_cod_empr = $idempresa";
        $usaUafeModal = consulta_string($sqlUafeModal, 'emmpr_uafe_cprov', $oIfx, 'f');
        $mostrarBotonUafe = ($usaUafeModal == 't' || $usaUafeModal == 1 || $usaUafeModal == '1' || $usaUafeModal === true);
        if ($mostrarBotonUafe) {
            $sHtml .= '<tr id="filaNotificarUafe" style="display:none;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" onclick="enviar_mail();" style="font-weight: bold;">
                            DOCUMENTACIÓN UAFE
                            <span class="glyphicon glyphicon-envelope"></span>
                        </button>
                    </td>
                </tr>';
        }
            


        $sHtml .= '</table>';

        $sHtml .= '<table class="table table-striped table-condensed" align="center" style="width: 100%;">';
        $sHtml .= '<tr>
              <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="4">VENTA/TRANSPORTE</th>
                    </tr> ';

        $sHtml .= '<tr>
          <td>Atencion Oficina</td>
          <td><input class="form-control" type="text" id="atencion_tn_clie" name="atencion_tn_clie" /></td>

          <td><i class="fa fa-sticky-note-o" aria-hidden="true">Horarios</i></td>
          <td><input class="form-control" type="text" id="horarios_cli" name="horarios_cli" /></td>
          
      </tr>';

        $sHtml .= '<tr>
                <td>Empresa de Transporte</i></td>
                <td><input class="form-control" type="text" id="empr_trans" name="empr_trans" /></td>

                <td><i class="fa fa-sticky-note-o" aria-hidden="true">Tipo Entrega</i></td>
                <td><select class="form-control" id="tipo_entreg_clie" name="tipo_entreg_clie">
                <option value ="0">Seleccione una Opcion</option>
                <option value="domicilio">Domicilio</option>
                <option value="oficina">Oficina</option>
                </select>
                </td>
                
            </tr>';

        $sHtml .= '<tr>
        <td>Responsable de Flete</i></td>
        <td><input class="form-control" type="text" id="respon_flete" name="respon_flete" /></td>

        <td>Tipo de Tienda</i></td>
        <td><input class="form-control" type="text" id="tip_tiend" name="tip_tiend" /></td>

        
        </tr>';

        $sHtml .= '<tr>
        <td> Direccion llegada</i></td>
        <td><input class="form-control" type="text" id="direcc_llega_clie" name="direcc_llega_clie" /></td>
        <td>Condicion de Ventas</i></td>
        <td><input class="form-control" type="text" id="condicion_vnt" name="condicion_vnt" /></td>
        </tr>';

        $sHtml .= '<tr>
        <td>Tipo Facturacion</i></td>
        <td><select class="form-control" id="tip_fact_cli" name="tip_fact_cli">
        <option value="0">Seleccione una Opcion</option>
        <option value="B">BOLETA</option>
        <option value="F">FACTURA</option>
        </select>
        </td>

        <td>Ruta de Visita</i></td>
        <td><input class="form-control" type="text" id="ruta_visita_cli" name="ruta_visita_cli" /></td>
        </tr>
        </tr>';
        $sHtml .= '</table>';


        $sHtml .= '<table class="table table-striped table-condensed" align="center" style="width: 100%;">';
        $sHtml .= '     <tr>
                            <td align="center" class="bg-primary" id="lgTitulo_frame" colspan="8">REGIMEN PROVEEDOR</th>
                        </tr> ';

        $sHtml .= '     <tr>
                            <td>Regimen Buenos Contribuyentes</td>
                            <td>
                                <input type="checkbox" id="regimen_buen_contr_sn" name="regimen_buen_contr_sn" value="S">
                            </td>

                            <td>Regimen de Percepciones</td>
                            <td>
                                <input type="checkbox" id="regimen_percepcion_sn" name="regimen_percepcion_sn" value="S">
                            </td>

                            <td>Detraccion</td>
                            <td>
                                <input type="checkbox" id="detraccion_sn" name="detraccion_sn" value="S">
                            </td>

                            <td>Retencion</td>
                            <td>
                                <input type="checkbox" id="retencion_sn" name="retencion_sn" value="S">
                            </td>
                        
                        </tr>';

        $sHtml .= '</table>';

        $oReturn->assign("divFormularioCli", "innerHTML", $sHtml);
        $oReturn->script("console.log('DEBUG: FORMULARIO GENERADO');");

        $oReturn->script("console.log('ACCION REAL = [$sAccion]');");
        $oReturn->script("console.log('usaUAFE = [" . ($usaUAFE ? 't' : 'f') . "]');");


        $oReturn->script("
            console.log('%cline 2: Evaluando condicional…','color:cyan;font-weight:bold');
            console.log('%c  sAccion: $sAccion','color:cyan');
            console.log('%c  usaUAFE: " . ($usaUAFE ? 't' : 'f') . "  (tipo: " . gettype($usaUAFE) . ")','color:cyan');
            console.log('%c  Condicion usaUAFE: ' + (" . ($usaUAFE ? 'true' : 'false') . "), 'color:cyan');
        ");

        if ($sAccion == 'nuevo') {

            if ($usaUAFE) {
                // Con UAFE → nuevo proveedor = PENDIENTE + bloqueado
                $oReturn->script("document.getElementById('PE').checked = true;");
                $oReturn->script("habilitarEstadoProveedor(true);");
            } else {
                // Sin UAFE → nuevo proveedor = ACTIVO + habilitado
                $oReturn->script("document.getElementById('AC').checked = true;");
                $oReturn->script("habilitarEstadoProveedor(false);");
            }
        }




        if ($sAccion == 'nuevo' && $usaUAFE) {

            $oReturn->script("
                console.log('%cline 3: ENTRÓ AL IF DE BLOQUEO', 'color:lime;font-weight:bold');
            ");

            $oReturn->script("
                console.log('UAFE Nuevo: Bloqueando radios…');
                setTimeout(function(){
                    try {
                        habilitarEstadoProveedor(true);
                        console.log('%cBloqueo aplicado correctamente','color:orange;font-weight:bold');
                    } catch(e){
                        console.log('ERROR bloqueo nuevo:', e);
                    }
                }, 200);
            ");

        } else {
            $oReturn->script("console.log('%cline 3: NO ENTRÓ AL IF (no se bloquea)', 'color:red;font-weight:bold');");
        }




        $oReturn->assign("divReporteCli", "innerHTML", $table);
        $oReturn->assign("divFormularioDatos", "innerHTML", $tableDatos);
        $oReturn->assign("divFormularioCcli", "innerHTML", $tableCcli);
        $oReturn->assign("divFormularioProdServClpv", "innerHTML", $tableProdServ);
        $oReturn->assign("divFormularioDsctLinp", "innerHTML", $tableDsctoLinp);
        $oReturn->assign("divFormularioCash", "innerHTML", $tableCash);
        $oReturn->assign("divFormularioPlantilla", "innerHTML", $tablePlantilla);
        $oReturn->assign("divFormularioContactoTelf", "innerHTML", $sHtmlTelf);
        $oReturn->assign("divFormularioContactoEmai", "innerHTML", $sHtmlEmai);
        $oReturn->assign("divFormularioContactoDire", "innerHTML", $sHtmlDire);
        $oReturn->assign("divFormularioAdjuntos", "innerHTML", $tableAdjuntos);
        $oReturn->script("cambiarTipoAdjunto(); $('#tipo_adj').on('change', cambiarTipoAdjunto);");



        $oReturn->assign("nombreBuscar", "placeholder", "DIGITE NOMBRE O RUC PARA BUSCAR...");
        $oReturn->assign("cuentaAplicada", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("creditoBienes", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("creditoServicios", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("retencionBienes", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("retencionServicios", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("retencionIvaBienes", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("retencionIvaServicios", "placeholder", "DIGITE PARA BUSCAR...");
        $oReturn->assign("nombreBuscar", "focus();", "");
        $oReturn->script("$('.select2').select2();");

        if (!empty($cod)) {

            $sql = "select clpc_nom_provc,clpc_nom_come,clpc_id_clpc from saeclpc
            where clpv_cod_clpv=$cod and clpc_cod_empr=$idempresa and clpc_cod_pei=$pedi";

            $nombre = consulta_string($sql, 'clpc_nom_provc', $oCon, '');
            $nomcome = consulta_string($sql, 'clpc_nom_come', $oCon, '');
            $ruc = consulta_string($sql, 'clpc_id_clpc', $oCon, '');


            $oReturn->assign('nombre', 'value', $nombre);
            $oReturn->assign('nombre_comercial', 'value', $nomcome);
            $oReturn->assign('ruc_cli', 'value', $ruc);
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }




    return $oReturn;
}

function obtenerAdjuntosProveedorHTML($idempresa, $oCon)
{
    $sHtml = '';

    $sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 50%;">';
    $sHtml .= '<tr>';
    $sHtml .= '<td colspan="4"><h5>ADANTOS <small>Reporte Información</small></h5></td>';
    $sHtml .= '</tr>';
    $sHtml .= '<tr>';
    $sHtml .= '<td>No.</td>';
    $sHtml .= '<td>Título</td>';
    $sHtml .= '<td>Archivo</td>';
    $sHtml .= '</tr>';

    $sql = "SELECT id, titulo, ruta
            FROM comercial.archivos_uafe
            WHERE empr_cod_empr = $idempresa
              AND estado = 'AC'
            ORDER BY id ASC";

    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $i = 1;
            do {
                $id     = $oCon->f('id');
                $titulo = $oCon->f('titulo');
                $ruta   = $oCon->f('ruta');

                $ruta = str_replace('../', '', $ruta);
                $ruta_file = "../../Include/Clases/Formulario/Plugins/reloj/$ruta";

                $sHtml .= '<tr>';
                $sHtml .= '<td>' . $i++ . '</td>';
                $sHtml .= '<td>' . $titulo . '</td>';

                // Aquí se reemplaza basename($ruta) por "Ver archivo"
                $sHtml .= '<td><a href="' . $ruta_file . '" target="_blank">Ver archivo</a></td>';

                $sHtml .= '</tr>';
            } while ($oCon->SiguienteRegistro());
        } else {
            $sHtml .= '<tr><td colspan="3" align="center">No hay documentos adjuntos</td></tr>';
        }
    }

    $sHtml .= '</table>';

    return $sHtml;
}


// funcion de enviar correos electronicos
/*function enviar_mail($aForm){
    // Obtener tabla de adjuntos
    $tablaAdjuntos = obtenerAdjuntosProveedorHTML($idempresa, $oCon);
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {

        //Código del proveedor desde el formulario
        $clpv = $aForm['codigoCliente'];
        if ($clpv == '' || $clpv == null) { $clpv = 0; }

        //Obtener correo del proveedor
        $sqlCorreo = "
            SELECT emai_ema_emai
            FROM saeemai
            WHERE emai_cod_empr = $idempresa
            AND emai_cod_clpv = $clpv
            ORDER BY emai_cod_emai
            LIMIT 1
        ";
        // echo $sqlCorreo;
        // exit;

        $correo = consulta_string_func($sqlCorreo, 'emai_ema_emai', $oIfx, '');
        if ($correo == '') { $correo = ''; }

        //Construir el modal
        $sHtml = '
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">REENVIO DOCUMENTOS ELECTRONICOS</h4>
                </div>
                <div class="modal-body">';

        $ifu->AgregarCampoTexto('correo', 'Destinatario|left', false, $correo, 700, 600);


        $sHtml .= '
            <table class="table table-striped table-condensed" style="width: 99%;">
                <tr>
                    <td>'.$ifu->ObjetoHtmlLBL('correo').'</td>
                    <td>'.$ifu->ObjetoHtml('correo').'</td>
                </tr>
            </table>


        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" onclick="enviaEmail();">Procesar</button>
        </div>
        </div></div>';

        $oReturn->assign("miModal", "innerHTML", $sHtml);

    } catch(Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}*/

function notificarDocumentosUAFE($aForm)
{
    return enviar_mail($aForm);
}

function enviar_mail($aForm)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // ====== CONEXIONES ======
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    // ====== VARIABLES ======
    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {

        //Código del proveedor desde el formulario
        $clpv = $aForm['codigoCliente'];
        if ($clpv == '' || $clpv == null) {
            $clpv = 0;
        }

        //Obtener correo del proveedor
        $sqlCorreo = "
            SELECT emai_ema_emai
            FROM saeemai
            WHERE emai_cod_empr = $idempresa
            AND emai_cod_clpv = $clpv
            ORDER BY emai_cod_emai
            LIMIT 1
        ";

        $correo = consulta_string_func($sqlCorreo, 'emai_ema_emai', $oIfx, '');
        if ($correo == '') {
            $correo = '';
        }

        // ====== AQUI OBTENEMOS EL GRID DE ADJUNTOS ======
        $tablaAdjuntos = obtenerAdjuntosProveedorHTML($idempresa, $oCon);

        // ====== CONSTRUIR MODAL ======
        $sHtml = '
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header bg-primary" style="color: white;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <span class="glyphicon glyphicon-envelope"></span> Reenvío de Documentos UAFE
                    </h4>
                </div>

                <!-- BODY -->
                <div class="modal-body">
        ';


        // Campo correo
        $ifu->AgregarCampoTexto("correo", "Destinatario|left", false, $correo, 700, 600);

        // Input correo
        $sHtml .= '
        <table class="table table-striped table-condensed" style="width: 99%; margin-bottom: 15px;">
            <tr>
                <td style="width: 25%; font-weight: bold; vertical-align: middle;">
                    ' . $ifu->ObjetoHtmlLBL("correo") . '
                </td>
                <td style="width: 75%;">' . $ifu->ObjetoHtml("correo") . '</td>
            </tr>
        </table>

        <div style="font-size: 14px; font-weight: bold; margin-bottom: 8px;">
            Documentos Adjuntos
        </div>

        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            ' . $tablaAdjuntos . '
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
            <span class="glyphicon glyphicon-remove"></span> Cerrar
        </button>
        <button type="button" class="btn btn-primary" onclick="enviaEmail();">
            <span class="glyphicon glyphicon-send"></span> Procesar
        </button>
    </div>

    </div></div>';


        // Mostrar modal
        $oReturn->assign("miModal", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function enviaEmail($aForm = '', $correo_destino = '')
{
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    // TRAEMOS LAS VARIABLES DEL FORMULARIO Y SESION
    $idEmpresa = $_SESSION['U_EMPRESA'];

    $mailenvio = ''; // correo copia email
    $idTipo = 1;

    // CON TRIM QUITAMOS ESPACIOS AL INICIO Y FINAL Y CON ARRAY VREAMOS UN ARRAY DE UN OBJETO
    $correo_form = trim($aForm['correo']);
    if(empty($correo_destino)){
        $correo_destino = $correo_form;
    }
    $correo_destino = trim($correo_destino);
    $correo = array($correo_destino);
    $correocc = array(trim($mailenvio));


    // --------------------------------------------------------------------------
    // TRAYENDO LA INFORMACION DE LA EMRESA
    // --------------------------------------------------------------------------
    $sqlEmpr = "SELECT empr_nom_empr, empr_dir_empr, empr_tel_resp, empr_token_api, empr_ruc_empr from saeempr where empr_cod_empr = $idEmpresa";
    if ($oIfx->Query($sqlEmpr)) {
        $compania = $oIfx->f("empr_nom_empr");
        $dirMatriz = $oIfx->f('empr_dir_empr');
        $empr_tel_resp = $oIfx->f("empr_tel_resp");
        $empr_api_toke = $oIfx->f("empr_token_api");
        $ruc_empr = $oIfx->f('empr_ruc_empr');
    }
    $oIfx->Free();
    // --------------------------------------------------------------------------
    // FIN TRAYENDO LA INFORMACION DE LA EMRESA
    // --------------------------------------------------------------------------




    // --------------------------------------------------------------------------
    // TRAYENDO LA CONEXION DEL CORREO 
    // --------------------------------------------------------------------------
    $sqlSmtp = "SELECT server, port, auth, 
    config_email.user, pass, ssltls, 
			mail 
			from comercial.config_email
			where id_empresa = $idEmpresa and
			id_tipo = $idTipo";
    if ($oIfx->Query($sqlSmtp)) {
        if ($oIfx->NumFilas() > 0) {
            $host = $oIfx->f('server');
            $port = $oIfx->f('port');
            $smtpauth = $oIfx->f('auth');
            $userid = $oIfx->f('user');
            $smtpsecure = $oIfx->f('ssltls');
            $mailenvio = $oIfx->f('mail');
            $password = $oIfx->f('pass');
        }
    }
    $oIfx->Free();
    if ($smtpsecure == 'S' || $smtpsecure == 'ssl') {
        $smtpsecure = 'ssl';
    } else {
        $smtpsecure = 'tls';
    }
    $secure_type = $smtpsecure;

    // --------------------------------------------------------------------------
    // FIN TRAYENDO LA CONEXION DEL CORREO 
    // --------------------------------------------------------------------------


    // --------------------------------------------------------------------------
    // CREANDO LA PLANTILLA QUE VA A LLEGAR AL EMAIL
    // --------------------------------------------------------------------------
    $cuerpo_correo_html = "<div style='width: 900px;'>
				<table style='width:850px;'> 
					<tr>
						<td>Estimado cliente, se han enviado los formularios UAFE para su revisión.</td>
					</tr>
				</table>
				<br/>
				<table style='width:850px;'>
					<tr> 
						<td>Atentamente,</td>
					</tr> 
					<tr>&nbsp;</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold; font-size: 13px;'>$compania</td>
					</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold;'>Dire.: $dirMatriz</td>
					</tr>
					<tr>
						<td style='font-weight: bold;'>Telf.: $empr_tel_resp</td>
					</tr>
					 <tr>&nbsp;</tr>
				</table>
			</div>";
    // --------------------------------------------------------------------------
    // FIN CREANDO LA PLANTILLA QUE VA A LLEGAR AL EMAIL
    // --------------------------------------------------------------------------



    // --------------------------------------------------------------------------
    // TRAEMOS LOS DOCUMENTOS DE LA UAFE CONFIGURADOS EN PARAMETROS PROVEEDORES
    // --------------------------------------------------------------------------
    $sql = "SELECT id, titulo, ruta
            FROM comercial.archivos_uafe
            WHERE empr_cod_empr = $idEmpresa
            AND estado = 'AC'
            ORDER BY id ASC";

    $array_documentos = array();
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $id = $oIfx->f('id');
                $titulo = $oIfx->f('titulo');
                $ruta = $oIfx->f('ruta');

                $ruta = str_replace('../', '', $ruta);
                // $ruta_file = "../../Include/Clases/Formulario/Plugins/reloj/$ruta";

                // RUTA PARA ENVIO DE DOCUMENTOS AL EMAIL = 'Include/Clases/Formulario/Plugins/reloj/'
                $rutaArchivo = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $ruta;
                $nombreAdjunto = basename($ruta);
                if (empty($nombreAdjunto)) {
                    $nombreAdjunto = $titulo;
                }

                $mimeType = false;
                if (function_exists('mime_content_type')) {
                    $mimeType = @mime_content_type($rutaArchivo);
                }
                if (!$mimeType) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $mimeType = finfo_file($finfo, $rutaArchivo);
                        finfo_close($finfo);
                    }
                }
                if (!$mimeType) {
                    $mimeType = 'application/octet-stream';
                }

                // TRAEMOS LA INFORMACION DE CADA ARCHIVO
                $archivo_get_content = file_get_contents($rutaArchivo);
                // CONVERTIENDO LA INFORMACION DEL ARCHIVO EN BASE64
                $archivo_base64 = base64_encode($archivo_get_content);

                // GUARDANDO EN UN ARRAY DE OBJETOS LOS ARCHIVOS CARGADOS
                $data_documentos = array(
                    "name" => $nombreAdjunto,
                    "content" => $archivo_base64,
                    "mime_type" => $mimeType
                );
                array_push($array_documentos, $data_documentos);

            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();
    // --------------------------------------------------------------------------
    // FIN TRAEMOS LOS DOCUMENTOS DE LA UAFE CONFIGURADOS EN PARAMETROS PROVEEDORES
    // --------------------------------------------------------------------------



    // --------------------------------------------------------------------------
    // ENVIAMOS EL EMAIL POR CURL PASANDO LA DATA CORRESPONDIENTE
    // --------------------------------------------------------------------------
    $data = array(
        "smtp_server" => $host . ":" . $port,
        "secure_type" => $secure_type,
        "username" => $userid,
        "password" => $password,
        "from_address" => $mailenvio,
        "to_address" =>  $correo,
        "to_cc" =>  $correocc,
        "title" => 'Documentos UAFE',
        "content" => $cuerpo_correo_html,
        "attachments" => $array_documentos
    );

    $headers = array(
        "Content-Type:application/json",
        "Token-Api:$empr_api_toke"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_CORREOS . "/api/v1/correo/enviar");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($ch);
    $resultado = json_decode($respuesta, true);


    $mensaje = $resultado["msg"];
    $enviado = $resultado["result"];

    if ($enviado == true) {
        $oReturn->alert("Mail Enviado a: " . $correo_destino);
    } else {
        $oReturn->alert("Error al enviar email" . $mensaje);
    }
    // --------------------------------------------------------------------------
    // FIN ENVIAMOS EL EMAIL POR CURL PASANDO LA DATA CORRESPONDIENTE
    // --------------------------------------------------------------------------


    return $oReturn;
}



function verifica_tipo_mapa($latitud = 0, $longitud = 0, $aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {

        $id_empresa = $_SESSION['U_EMPRESA'];

        $sql_empresa_maps = "SELECT empr_gmaps_sn from saeempr where empr_cod_empr = $id_empresa";
        $empr_gmaps_sn = consulta_string_func($sql_empresa_maps, 'empr_gmaps_sn', $oIfx, 0);

        //$empr_gmaps_sn = 'S';
        if ($empr_gmaps_sn == 'S') {
            if ($latitud == '') {
                $oReturn->script('initMap()');
            }
            $oReturn->script('sendcoord()');
        } else {
            $oReturn->script('open_modal_open_street_maps(\'' . $longitud . '\', \'' . $latitud . '\')');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function seleccionaItem($aForm = '', $cliente = 0)
{

    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //Conexion 2
    $oCon = new Dbo;
    $oCon->DSN = $DSN_Ifx;
    $oCon->Conectar();


    $oReturn = new xajaxResponse();

    unset($_SESSION['aDataGirdCuentaAplicada']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {
        //lectura sucia
        //////////////

        $sql = "select clpv_cod_clpv, clv_con_clpv, clpv_nom_clpv, clpv_cod_char,
				clpv_ruc_clpv, clpv_nom_come, grpv_cod_grpv, clpv_cod_zona,
				clpv_cod_fpag, clpv_cod_sucu, clpv_pre_ven, clpv_cod_vend,
				clpv_lim_cred, clpv_pro_pago, clpv_est_clpv, clpv_dsc_clpv,
				clpv_dsc_prpg, clpv_cod_titu, clpv_cod_tclp, clpv_cod_trta,
				clpv_cod_fpagop, clpv_cod_tprov, clpv_cod_tpago, clpv_cod_paisp,
				clpv_etu_clpv, clpv_cod_banc, clpv_num_ctab, clpv_rep_clpv,
				clpv_cod_cact, clpv_nov_clpv, clpv_ret_sn  , clpv_par_rela, clpv_tec_sn, clpv_cod_mone,
                clpv_ubi_lati, clpv_ubi_long,clpv_cod_uniq, clpv_cod_cuen, clpv_ruc_tran, clpv_desc_actividades,
                clpv_tip_ctab, clpv_facebook_clpv, clpv_insta_clpv,
                ident_propi_clpv, fechnaci_propi_clpv, pagina_web_clpv,
                aniver_empr_clpv, atencion_ofi_clpv,  horarios_aten_clpv, 
                empresa_trans_clpv, tip_entrega_clpv,   resp_flete_clpv, 
                tip_tienda_clpv, direc_llegada, clpv_notas_clpv, cond_vent_clpv, tip_fac_clpv, ruta_visit_clpv
				from saeclpv where
				clpv_cod_empr = $idempresa and
				clpv_clopv_clpv = 'PV' and
				clpv_cod_clpv = $cliente";

                // RESETEAR RADIOS AL CAMBIAR DE PROVEEDOR
                //$oReturn->script("habilitarEstadoProveedor(false);");

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $clpv_cod_uniq = $oIfx->f('clpv_cod_uniq');
                $clpv_cod_clpv = $oIfx->f('clpv_cod_clpv');

                //Mostrar correo asignado
                // ==========================================
                // CARGAR EMAIL CONTACTO (tipo = 1)
                // ==========================================
                $sqlCorreoContacto = "
                    select emai_ema_emai
                    from saeemai
                    where emai_cod_empr = $idempresa
                    and emai_cod_clpv = $clpv_cod_clpv
                    and emai_cod_tiem = 1
                    limit 1
                ";

                $correo_contacto = '';
                if ($oCon->Query($sqlCorreoContacto)) {
                    if ($oCon->NumFilas() > 0) {
                        $correo_contacto = $oCon->f('emai_ema_emai');
                    }
                }
                
  


                $oReturn->assign('correo_contacto_', 'value', $correo_contacto);
                $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                $clpv_cod_fpag = $oIfx->f('clpv_cod_fpag');
                $clpv_cod_sucu = $oIfx->f('clpv_cod_sucu');
                $clv_con_clpv = $oIfx->f('clv_con_clpv');
                $clpv_nom_come = $oIfx->f('clpv_nom_come');
                $grpv_cod_grpv = $oIfx->f('grpv_cod_grpv');
                $clpv_cod_zona = $oIfx->f('clpv_cod_zona');
                $clpv_pre_ven = round($oIfx->f('clpv_pre_ven'));
                $clpv_cod_vend = $oIfx->f('clpv_cod_vend');
                $clpv_lim_cred = $oIfx->f('clpv_lim_cred');
                $clpv_pro_pago = $oIfx->f('clpv_pro_pago');
                $clpv_dsc_clpv = $oIfx->f('clpv_dsc_clpv');
                $clpv_dsc_prpg = $oIfx->f('clpv_dsc_prpg');
                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');
                $clpv_cod_titu = $oIfx->f('clpv_cod_titu');
                $clpv_cod_tclp = $oIfx->f('clpv_cod_tclp');
                $clpv_cod_trta = $oIfx->f('clpv_cod_trta');
                $clpv_cod_fpagop = $oIfx->f('clpv_cod_fpagop');
                $clpv_cod_tprov = $oIfx->f('clpv_cod_tprov');
                $clpv_cod_tpago = $oIfx->f('clpv_cod_tpago');
                $clpv_cod_paisp = $oIfx->f('clpv_cod_paisp');
                $clpv_etu_clpv = $oIfx->f('clpv_etu_clpv');
                $clpv_cod_banc = $oIfx->f('clpv_cod_banc');
                $clpv_num_ctab = $oIfx->f('clpv_num_ctab');
                $clpv_tip_ctab = $oIfx->f('clpv_tip_ctab');
                $clpv_cod_cact = $oIfx->f('clpv_cod_cact');
                $clpv_rep_clpv = $oIfx->f('clpv_rep_clpv');
                $clpv_nov_clpv = $oIfx->f('clpv_nov_clpv');
                $clpv_ret_sn   = $oIfx->f('clpv_ret_sn');
                $clpv_par_rela   = $oIfx->f('clpv_par_rela');
                $clpv_tec_sn   = $oIfx->f('clpv_tec_sn');
                $clpv_cod_mone = $oIfx->f('clpv_cod_mone');
                $clpv_ubi_lati = $oIfx->f('clpv_ubi_lati');
                $clpv_ubi_long = $oIfx->f('clpv_ubi_long');
                $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');

                $clpv_ruc_tran = $oIfx->f('clpv_ruc_tran');
                $clpv_cod_char = $oIfx->f('clpv_cod_char');
                $clpv_desc_actividades = $oIfx->f('clpv_desc_actividades');
                $oReturn->assign('cod_char_clpv', 'value', $clpv_cod_char);




                $ident_propi_clpv = $oIfx->f('ident_propi_clpv');
                $fechnaci_propi_clpv = $oIfx->f('fechnaci_propi_clpv');
                $pagina_web_clpv = $oIfx->f('pagina_web_clpv');
                $aniver_empr_clpv  = $oIfx->f('aniver_empr_clpv');

                //echo $aniver_empr_clpv;exit;
                $atencion_ofi_clpv  = $oIfx->f('atencion_ofi_clpv');
                $horarios_aten_clpv  = $oIfx->f('horarios_aten_clpv');
                $empresa_trans_clpv  = $oIfx->f('empresa_trans_clpv');
                $tip_entrega_clpv  = $oIfx->f('tip_entrega_clpv');
                $resp_flete_clpv  = $oIfx->f('resp_flete_clpv');
                $tip_tienda_clpv  = $oIfx->f('tip_tienda_clpv');
                $direc_llegada = $oIfx->f('direc_llegada');
                $clpv_notas_clpv = $oIfx->f('clpv_notas_clpv');
                $clpv_facebook_clpv = $oIfx->f('clpv_facebook_clpv');
                $clpv_insta_clpv = $oIfx->f('clpv_insta_clpv');
                $cond_vent_clpv = $oIfx->f('cond_vent_clpv');
                $tip_fac_clpv = $oIfx->f('tip_fac_clpv');
                $ruta_visita_cli = $oIfx->f('ruta_visit_clpv');

                $oReturn->assign('identif_propie', 'value', $ident_propi_clpv);
                $oReturn->assign('fech_nac_prop', 'value', $fechnaci_propi_clpv);
                $oReturn->assign('pagina_web_cli', 'value', $pagina_web_clpv);
                $oReturn->assign('aniversario_empr', 'value', $aniver_empr_clpv);
                $oReturn->assign('atencion_tn_clie', 'value', $atencion_ofi_clpv);
                $oReturn->assign('horarios_cli', 'value', $horarios_aten_clpv);
                $oReturn->assign('empr_trans', 'value', $empresa_trans_clpv);
                $oReturn->assign('tipo_entreg_clie', 'value', $tip_entrega_clpv);
                $oReturn->assign('respon_flete', 'value', $resp_flete_clpv);
                $oReturn->assign('tip_tiend', 'value', $tip_tienda_clpv);
                $oReturn->assign('direcc_llega_clie', 'value', $direc_llegada);
                $oReturn->assign('notas_cli', 'value', $clpv_notas_clpv);
                $oReturn->assign('facebook_cli', 'value', $clpv_facebook_clpv);
                $oReturn->assign('insta_cli', 'value', $clpv_insta_clpv);
                $oReturn->assign('notas_cli', 'value', $clpv_notas_clpv);
                $oReturn->assign('condicion_vnt', 'value', $cond_vent_clpv);
                $oReturn->assign('tip_fact_cli', 'value', $tip_fac_clpv);
                $oReturn->assign('ruta_visita_cli', 'value', $ruta_visita_cli);

                if ($clpv_ret_sn == 'S') {
                    $oReturn->assign('clpv_ret_sn', 'checked', true);
                } else {
                    $oReturn->assign('clpv_ret_sn', 'checked', false);
                }

                if ($clpv_par_rela == 'S') {
                    $oReturn->assign('clpv_par_rela', 'checked', true);
                } else {
                    $oReturn->assign('clpv_par_rela', 'checked', false);
                }

                if ($clpv_tec_sn == 'S') {
                    $oReturn->assign('clpv_tec_sn', 'checked', true);
                } else {
                    $oReturn->assign('clpv_tec_sn', 'checked', false);
                }

                if (empty($clpv_etu_clpv)) {
                    $clpv_etu_clpv = 0;
                }

                if (empty($clpv_cod_zona)) {
                    $clpv_cod_zona = 0;
                }
            }
        }
        $oIfx->Free();

        //echo $clpv_cod_uniq;
        $oReturn->assign('codigoUnico', 'value', $clpv_cod_uniq);
        $oReturn->assign('cod_cuenta_in', 'value', $clpv_cod_cuen);
        $valorIdentificacion = trim($clv_con_clpv);
        $valorIdentificacionPadded = str_pad($valorIdentificacion, 2, '0', STR_PAD_LEFT);
        // Cargar el valor almacenado y dejar que el ajuste JS sincronice con el combo/Chosen
        $oReturn->assign('identificacion', 'value', $valorIdentificacion);
        $oReturn->assign('ruc_cli', 'value', $clpv_ruc_clpv);
        $oReturn->assign('nombre', 'value', $clpv_nom_clpv);
        $oReturn->assign('nombre_comercial', 'value', $clpv_nom_come);
        $oReturn->assign('clpv_desc_actividades', 'value', $clpv_desc_actividades);
        $oReturn->assign('grupo', 'value', $grpv_cod_grpv);
        $oReturn->assign('clpv_cod_sucu', 'value', $clpv_cod_sucu);
        $oReturn->assign('zona', 'value', $clpv_cod_zona);
        $oReturn->assign('limite', 'value', $clpv_lim_cred);
        $oReturn->assign('dias_pago', 'value', $clpv_pro_pago);
        $oReturn->assign('dsctGeneral', 'value', $clpv_dsc_clpv);
        $oReturn->assign('dsctDetalle', 'value', $clpv_dsc_prpg);
        $oReturn->assign('codigoCliente', 'value', $clpv_cod_clpv);
        $oReturn->assign('tipo_cliente', 'value', $clpv_cod_cact);
        $oReturn->assign('tipo_prove', 'value', $clpv_cod_tprov);
        $oReturn->assign('pago', 'value', $clpv_cod_fpagop);
        $oReturn->assign('tipo_pago', 'value', $clpv_cod_tpago);
        $oReturn->assign('pais', 'value', $clpv_cod_paisp);
        $oReturn->assign('cuenta', 'value', $clpv_num_ctab);
        $oReturn->assign('banco', 'value', $clpv_cod_banc);
        $oReturn->assign('tipoCuenta', 'value', $clpv_tip_ctab);
        $oReturn->assign('representante', 'value', $clpv_rep_clpv);
        $oReturn->assign('observaciones', 'value', $clpv_nov_clpv);
        $oReturn->assign('clpv_cod_mone', 'value', $clpv_cod_mone);
        $oReturn->assign('latitud_tmp', 'value', $clpv_ubi_lati);
        $oReturn->assign('longitud_tmp', 'value', $clpv_ubi_long);
        $oReturn->assign('identificacion_sf', 'value', $clpv_ruc_tran);

        $oReturn->script("ajustarComboIdentificacion('" . $valorIdentificacion . "', '" . $valorIdentificacionPadded . "');");

        //  echo $clpv_est_clpv;exit;




        if (!empty($clpv_est_clpv)) {

            if ($clpv_est_clpv == 'A') {
                $clpv_est_clpv = 'AC';
            }
            if ($clpv_est_clpv == 'S') {
                $clpv_est_clpv = 'SU';
            }

            if ($clpv_est_clpv == 'P') {
                $clpv_est_clpv = 'PE';
            }

            $oReturn->script('editar(\'' . $clpv_est_clpv . '\')');
        } else {
            $clpv_est_clpv = 'PE';
            $oReturn->script('editar(\'' . $clpv_est_clpv . '\')');
        }



        if ($clpv_ret_sn != '') {
            $oReturn->assign($clpv_ret_sn, 'checked', true);
        } else {
            $oReturn->assign('A', 'checked', true);
        }

        if ($clpv_par_rela != '') {
            $oReturn->assign($clpv_par_rela, 'checked', true);
        } else {
            $oReturn->assign('A', 'checked', true);
        }

        // if ($clpv_tec_sn != '') {
        //     $oReturn->assign($clpv_tec_sn, 'checked', true);
        // } else {
        //     $oReturn->assign('A', 'checked', true);
        // }

        if ($clpv_etu_clpv == 1) {
            $oReturn->assign('contriEspecial', 'checked', true);
        } else {
            $oReturn->assign('contriEspecial', 'checked', false);
        }

        $oReturn->assign('lgTitulo_frame', 'innerHTML', 'EDITAR FICHA PROVEEDOR');
        $oReturn->script('reporteTelefonoCliente();');
        $oReturn->script('reporteEmailCliente();');
        $oReturn->script('reporteDireCliente();');

        $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];  // 593 ECUADOR 51 PERU
        // VALIDACION CAMPOS CUENTAS BANCARIAS PERU

        $oReturn->script("consulta_cash($cliente);");

        $oReturn->script('xajax_listaCcli(xajax.getFormValues(\'form1\'))');
        $oReturn->script('xajax_genera_formulario_portafolio(xajax.getFormValues(\'form1\'))');
        $oReturn->script('xajax_reportePlantillas(xajax.getFormValues(\'form1\'))');

        $oReturn->script('consultarAdjuntos();');
        
        // VALIDAR ESTADO UAFE DEL PROVEEDOR ANTES DE MARCAR ESTADO
        $oReturn->script("xajax_validarEstadoUAFEProveedor($cliente);");

        //MOSTRAR DOCUMENTOS UAFE DEL PROVEEDOR
        $oReturn->script('consultarAdjuntosUafe();');

    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

/*function seleccionaItem($aForm = '', $cliente = 0)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN_Ifx;
    $oCon->Conectar();

    // Conexión a BD principal (Postgres) para validaciones UAFE
    $oPg = new Dbo;
    $oPg->DSN = $DSN;
    $oPg->Conectar();

    $oReturn = new xajaxResponse();

    unset($_SESSION['aDataGirdCuentaAplicada']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {

        // ------------------------------------------------------------
        // VALIDACIÓN PREVIA DE ESTADO (UAFE)
        // ------------------------------------------------------------
        $usaUafe = usaValidacionUAFE($idempresa, $oPg);
        $bloquearEstado = $usaUafe && debeBloquearEstadoPorUafe($idempresa, $cliente, $oPg);

        // Mientras llegan los datos, aplicar inmediatamente el bloqueo/ habilitación correspondiente
        $oReturn->script("habilitarEstadoProveedor(" . ($bloquearEstado ? 'true' : 'false') . ");");

        // ------------------------------------------------------------
        // CARGA DE DATOS PRINCIPALES
        // ------------------------------------------------------------
        $sql = "
            select clpv_cod_clpv, clv_con_clpv, clpv_nom_clpv, clpv_cod_char,
                   clpv_ruc_clpv, clpv_nom_come, grpv_cod_grpv, clpv_cod_zona,
                   clpv_cod_fpag, clpv_cod_sucu, clpv_pre_ven, clpv_cod_vend,
                   clpv_lim_cred, clpv_pro_pago, clpv_est_clpv, clpv_dsc_clpv,
                   clpv_dsc_prpg, clpv_cod_titu, clpv_cod_tclp, clpv_cod_trta,
                   clpv_cod_fpagop, clpv_cod_tprov, clpv_cod_tpago, clpv_cod_paisp,
                   clpv_etu_clpv, clpv_cod_banc, clpv_num_ctab, clpv_rep_clpv,
                   clpv_cod_cact, clpv_nov_clpv, clpv_ret_sn, clpv_par_rela, clpv_tec_sn, 
                   clpv_cod_mone, clpv_ubi_lati, clpv_ubi_long, clpv_cod_uniq, 
                   clpv_cod_cuen, clpv_ruc_tran, clpv_tip_ctab,
                   clpv_facebook_clpv, clpv_insta_clpv,
                   ident_propi_clpv, fechnaci_propi_clpv, pagina_web_clpv,
                   aniver_empr_clpv, atencion_ofi_clpv, horarios_aten_clpv, 
                   empresa_trans_clpv, tip_entrega_clpv, resp_flete_clpv, 
                   tip_tienda_clpv, direc_llegada, clpv_notas_clpv, cond_vent_clpv, 
                   tip_fac_clpv, ruta_visit_clpv
            from saeclpv
            where clpv_cod_empr = $idempresa
              and clpv_clopv_clpv = 'PV'
              and clpv_cod_clpv = $cliente
        ";

        if ($oIfx->Query($sql) && $oIfx->NumFilas() > 0) {

            // Campos base
            $clpv_cod_clpv = $oIfx->f('clpv_cod_clpv');
            $clpv_cod_uniq = $oIfx->f('clpv_cod_uniq');
            $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
            $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
            $clpv_nom_come = $oIfx->f('clpv_nom_come');
            $clv_con_clpv   = $oIfx->f('clv_con_clpv');
            $clpv_est_clpv  = $oIfx->f('clpv_est_clpv');

            // Más campos...
            $clpv_cod_zona = $oIfx->f('clpv_cod_zona');
            $clpv_cod_sucu = $oIfx->f('clpv_cod_sucu');
            $clpv_lim_cred = $oIfx->f('clpv_lim_cred');
            $clpv_pro_pago = $oIfx->f('clpv_pro_pago');
            $grpv_cod_grpv = $oIfx->f('grpv_cod_grpv');
            $clpv_dsc_clpv = $oIfx->f('clpv_dsc_clpv');
            $clpv_dsc_prpg = $oIfx->f('clpv_dsc_prpg');
            $clpv_cod_cact = $oIfx->f('clpv_cod_cact');
            $clpv_cod_tprov = $oIfx->f('clpv_cod_tprov');
            $clpv_cod_tpago = $oIfx->f('clpv_cod_tpago');
            $clpv_cod_fpagop = $oIfx->f('clpv_cod_fpagop');
            $clpv_cod_paisp  = $oIfx->f('clpv_cod_paisp');
            $clpv_cod_mone = $oIfx->f('clpv_cod_mone');
            $clpv_ret_sn  = $oIfx->f('clpv_ret_sn');
            $clpv_par_rela = $oIfx->f('clpv_par_rela');
            $clpv_tec_sn = $oIfx->f('clpv_tec_sn');
            $clpv_num_ctab = $oIfx->f('clpv_num_ctab');
            $clpv_cod_banc = $oIfx->f('clpv_cod_banc');
            $clpv_tip_ctab = $oIfx->f('clpv_tip_ctab');

            // Contacto email
            $sqlCorreo = "
                select emai_ema_emai
                from saeemai
                where emai_cod_empr = $idempresa
                  and emai_cod_clpv = $clpv_cod_clpv
                  and emai_cod_tiem = 1
                limit 1
            ";

            $correo_contacto = '';
            if ($oCon->Query($sqlCorreo) && $oCon->NumFilas() > 0) {
                $correo_contacto = $oCon->f('emai_ema_emai');
            }

            $oReturn->assign('correo_contacto_', 'value', $correo_contacto);

            // Asignación general a controles
            $oReturn->assign('codigoUnico', 'value', $clpv_cod_uniq);
            $oReturn->assign('codigoCliente', 'value', $clpv_cod_clpv);
            $oReturn->assign('ruc_cli', 'value', $clpv_ruc_clpv);
            $oReturn->assign('nombre', 'value', $clpv_nom_clpv);
            $oReturn->assign('nombre_comercial', 'value', $clpv_nom_come);
            $oReturn->assign('grupo', 'value', $grpv_cod_grpv);
            $oReturn->assign('clpv_cod_sucu', 'value', $clpv_cod_sucu);
            $oReturn->assign('zona', 'value', $clpv_cod_zona);
            $oReturn->assign('limite', 'value', $clpv_lim_cred);
            $oReturn->assign('dias_pago', 'value', $clpv_pro_pago);
            $oReturn->assign('dsctGeneral', 'value', $clpv_dsc_clpv);
            $oReturn->assign('dsctDetalle', 'value', $clpv_dsc_prpg);
            $oReturn->assign('tipo_cliente', 'value', $clpv_cod_cact);
            $oReturn->assign('tipo_prove', 'value', $clpv_cod_tprov);
            $oReturn->assign('tipo_pago', 'value', $clpv_cod_tpago);
            $oReturn->assign('pago', 'value', $clpv_cod_fpagop);
            $oReturn->assign('pais', 'value', $clpv_cod_paisp);
            $oReturn->assign('banco', 'value', $clpv_cod_banc);
            $oReturn->assign('cuenta', 'value', $clpv_num_ctab);
            $oReturn->assign('tipoCuenta', 'value', $clpv_tip_ctab);

            // Checkboxes
            $oReturn->assign('clpv_ret_sn', 'checked', ($clpv_ret_sn == 'S'));
            $oReturn->assign('clpv_par_rela', 'checked', ($clpv_par_rela == 'S'));
            $oReturn->assign('clpv_tec_sn', 'checked', ($clpv_tec_sn == 'S'));

        }

        // -----------------------------------------------------------
        // INICIO VALIDACIONES DOCUMENTOS UAFE
        // -----------------------------------------------------------
        //Marcar estado del proveedor (A, S, P)
        if (!empty($clpv_est_clpv)) {

            if ($clpv_est_clpv == 'A') $clpv_est_clpv = 'AC';
            if ($clpv_est_clpv == 'S') $clpv_est_clpv = 'SU';
            if ($clpv_est_clpv == 'P') $clpv_est_clpv = 'PE';

            $oReturn->script('editar("' . $clpv_est_clpv . '")');

        }

        //AHORA VALIDAR UAFE (bloquea o habilita radios según documentos)
        $oReturn->script("xajax_validarEstadoUAFEProveedor($cliente);");

        //Consultar documentos UAFE visuales
        $oReturn->script('consultarAdjuntosUafe();');


        // =======================
        // RESTO DE PROCESOS VISUALES
        // =======================
        $oReturn->assign('lgTitulo_frame', 'innerHTML', 'EDITAR FICHA PROVEEDOR');
        $oReturn->script('reporteTelefonoCliente();');
        $oReturn->script('reporteEmailCliente();');
        $oReturn->script('reporteDireCliente();');
        $oReturn->script("consulta_cash($cliente);");
        $oReturn->script('consultarAdjuntos();');

    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}*/


function guardar_ubicacion_clpv($aForm = '')
{

    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();


    $codigoCliente = $aForm['codigoCliente'];
    $longitud_tmp = $aForm['longitud_tmp'];
    $latitud_tmp = $aForm['latitud_tmp'];


    try {
        $oIfx->QueryT('BEGIN WORK;');

        if (
            empty($codigoCliente) ||
            empty($longitud_tmp) ||
            empty($latitud_tmp)
        ) {
            throw new Exception('Debe seleccionar un Proveedor y ubicar en el mapa su ubicacion dando click en el mismo');
        }

        $id_usuario = $_SESSION['U_UID'];
        $fecha_actual = date('Y-m-d');

        $sql_update = "UPDATE saeclpv SET
                                clpv_ubi_lati = '$latitud_tmp',
                                clpv_ubi_long = '$longitud_tmp'
                            WHERE clpv_cod_clpv = $codigoCliente;
                                ";
        $oIfx->QueryT($sql_update);


        $oIfx->QueryT('COMMIT WORK');

        $oReturn->script("Swal.fire({
                                        position: 'center',
                                        type: 'success',
                                        title: 'Ubicacion Guardada Correctamente...!',
                                        showConfirmButton: true,
                                        confirmButtonText: 'Aceptar',
                                        timer: 10000
                                    })");
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function agregarEntidad($aForm = '', $op = 0)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables de sesion
    $empresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];
    //echo $clpv;exit;

    //$direccion = utf8_decode($aForm['direccion']);
    //echo $direccion;exit;
    $email = $aForm['emai_ema_emai'];
    $fechaSever = date("Y-m-d H:i:s");
    $emai_cod_tiem    = $aForm['emai_cod_tiem'];


    if (empty($sucursal)) {
        $sqlSucursal = "select clpv_cod_sucu from saeclpv where clpv_cod_clpv = $clpv";
        $sucursal = consulta_string_func($sqlSucursal, 'clpv_cod_sucu', $oIfx, 0);
    }

    try {

        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN;');

        //op
        if ($op == 1) {
            $idDireccion = $aForm['idDireccion'];
            $tipo_direccion = $aForm['tipo_direccion'];
            $tipo_casa = $aForm['tipo_casa'];
            $sectorDire = $aForm['sectorDire'];
            $barrioDire = $aForm['barrioDire'];
            $callePrincipal = $aForm['callePrincipal'];
            $numeroDire = $aForm['numeroDire'];
            $calleSecundaria = $aForm['calleSecundaria'];
            $edificioDire = $aForm['edificioDire'];
            $referenciaDire = $aForm['referenciaDire'];
            $antiguedadDire = $aForm['antiguedadDire'];

            $dire_dir_dire = $callePrincipal . ' ' . $numeroDire . ' ' . $calleSecundaria;
            //echo $dire_dir_dire;exit;

            if (!empty($dire_dir_dire)) {

                if (empty($tipo_direccion)) {
                    $tipo_direccion = 0;
                }

                if (empty($tipo_casa)) {
                    $tipo_casa = 0;
                }

                if (empty($sectorDire)) {
                    $sectorDire = 0;
                }

                if (empty($antiguedadDire)) {
                    $antiguedadDire = 0;
                }
                if ($idDireccion != '') {
                    //actualiza direccion
                    //$dire_dir_dire = $aForm['direccion'];
                    $dire_dir_dire = $callePrincipal . ' ' . $numeroDire . ' ' . $calleSecundaria;

                    $sqlDire = "update saedire set dire_cod_tipo = $tipo_direccion,
                                                    dire_cod_vivi = $tipo_casa,
                                                    dire_cod_sect = $sectorDire,
                                                    dire_barr_dire = '$barrioDire',
                                                    dire_call1_dire = '$callePrincipal',
                                                    dire_call2_dire = '$calleSecundaria',
                                                    dire_nume_dire = '$numeroDire',
                                                    dire_edif_dire = '$edificioDire',
                                                    dire_refe_dire = '$referenciaDire',
                                                    dire_anti_dire = $antiguedadDire,
                                                    dire_dir_dire = '$dire_dir_dire'
                                                where dire_cod_empr = $empresa and
                                                    dire_cod_clpv = $clpv and
                                                    dire_cod_dire = $idDireccion";
                    $oIfx->QueryT($sqlDire);

                    //control inserta datos
                    /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                                values($empresa, $clpv, 'D', 2, $usuario_web, '$fechaSever')";
                    $oCon->QueryT($sqlCtrl); */
                } else {
                    //inserta direccion
                    $sqlDire = "insert into saedire(dire_cod_empr, dire_cod_sucu, dire_cod_clpv, dire_dir_dire,
                    dire_cod_tipo, dire_cod_vivi, dire_cod_sect,
                    dire_barr_dire, dire_call1_dire, dire_call2_dire,
                    dire_nume_dire, dire_edif_dire, dire_refe_dire,
                    dire_anti_dire)
                    values($empresa, $sucursal, $clpv, '$dire_dir_dire',
                    $tipo_direccion, $tipo_casa, $sectorDire,
                    '$barrioDire', '$callePrincipal', '$calleSecundaria',
                    '$numeroDire', '$edificioDire', '$referenciaDire',
                    $antiguedadDire)";
                    $oIfx->QueryT($sqlDire);

                    //control inserta datos
                    /*   $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                    values($empresa, $clpv, 'D', 1, $usuario_web, '$fechaSever')";
                    $oCon->QueryT($sqlCtrl); */

                    $oReturn->assign('barrioDire', 'value', '');
                    $oReturn->assign('callePrincipal', 'value', '');
                    $oReturn->assign('numeroDire', 'value', '');
                    $oReturn->assign('numeroDire', 'value', '');
                    $oReturn->assign('calleSecundaria', 'value', '');
                    $oReturn->assign('edificioDire', 'value', '');
                    $oReturn->assign('referenciaDire', 'value', '');
                    $oReturn->assign('antiguedadDire', 'value', '');
                    $oReturn->script("reporteDireCliente();");
                }

                $oReturn->assign('barrioDire', 'value', '');
                $oReturn->assign('callePrincipal', 'value', '');
                $oReturn->assign('numeroDire', 'value', '');
                $oReturn->assign('numeroDire', 'value', '');
                $oReturn->assign('calleSecundaria', 'value', '');
                $oReturn->assign('edificioDire', 'value', '');
                $oReturn->assign('referenciaDire', 'value', '');
                $oReturn->assign('antiguedadDire', 'value', '');
                $oReturn->script("reporteDireCliente();");
            } else {
                $oReturn->alert('Ingrese Direccion para continuar...!');
            }
        } elseif ($op == 2) {

            //variables telefono
            $tipo_telefono = $aForm['tipo_telefono'];
            $telefono = $aForm['telefono_cli'];
            $tipo_operador = $aForm['tipo_operador'];

            if (!empty($telefono)) {

                if (empty($tipo_operador)) {
                    $tipo_operador = 0;
                }

                //inserta telefono
                $sqlTelf = "insert into saetlcp(tlcp_cod_empr, tlcp_cod_sucu, tlcp_cod_clpv, tlcp_tip_ticp, tlcp_tlf_tlcp, tlcp_cod_oper)
                                        values($empresa, $sucursal, $clpv, '$tipo_telefono', '$telefono', $tipo_operador)";
                $oIfx->QueryT($sqlTelf);

                //control inserta datos
                /*      $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                            values($empresa, $clpv, 'T', 1, $usuario_web, '$fechaSever')";
                $oCon->QueryT($sqlCtrl); */

                $oReturn->assign('tipo_telefono', 'value', '');
                $oReturn->assign('telefono_cli', 'value', '');
                $oReturn->assign('tipo_operador', 'value', '');
                $oReturn->script("reporteTelefonoCliente();");
            } else {
                $oReturn->alert('Ingrese numero de telefono para continuar...!');
            }
        } elseif ($op == 3) {

            if (!empty($email)) {

                //inserta telefono
                $sqlEmai = "insert into saeemai(emai_cod_empr, emai_cod_sucu, emai_cod_clpv, emai_ema_emai, emai_cod_tiem)
                                        values($empresa, $sucursal, $clpv, '$email', '$emai_cod_tiem' )";
                $oIfx->QueryT($sqlEmai);

                //control inserta datos
                /*   $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                            values($empresa, $clpv, 'E', 1, $usuario_web, '$fechaSever')";
                $oCon->QueryT($sqlCtrl); */

                $oReturn->assign('emai_ema_emai', 'value', '');
                $oReturn->script("reporteEmailCliente();");
            } else {
                $oReturn->alert('Ingrese E-mail para continuar...!');
            }
        } elseif ($op == 4) {

            $ruc = trim($aForm['identificacion_sf']);
            $num_cuenta = trim($aForm['cuenta']);
            $banco = $aForm['banco'];
            $tipo_cuenta = $aForm['tipoCuenta'];
            $cod_inter = trim($aForm['cod_inter']);
            $tip_iden = $aForm['tipo_iden'];
            $cod_mone = $aForm['mone_cash'];

            $sqldig = "select digitos from comercial.tipo_iden_clpv_pais where id_iden_pais=$tip_iden";
            $numdig = consulta_string($sqldig, 'digitos', $oCon, 0);



            $sql = "insert into cuentas_cash (
            cash_cod_clpv,    cash_ruc_clpv,   cash_cod_empr, 
            cash_num_cuen,    cash_tip_cuen,   cash_cod_ban,
            cash_cod_int,    cash_cod_iden,    cash_cod_mone,  
            cash_est_del,    cash_created_at, cash_user_created)
            values
            ($clpv,           '$ruc',            $empresa, 
             '$num_cuenta',   '$tipo_cuenta',    $banco,
                '$cod_inter', $tip_iden, $cod_mone,    
                'N', '$fechaSever',   $usuario_web
            )";
            if (strlen($ruc) != $numdig) {
                $oReturn->alert('El DOI numero debe contener: ' . $numdig . ' digitos');
                $oReturn->scripT("document.getElementById('identificacion_sf').focus();");
            } else {
                $oIfx->QueryT($sql);
                $oReturn->alert('Ingresada Correctamente');
                $oReturn->script("consulta_cash($clpv)");
            }
        }

        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT;');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function reporteTelefonoCliente($aForm = '')
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    unset($_SESSION['ARRAY_CLPV_TELF']);
    $empresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];

    try {

        //lectura sucia
        //////////////

        $sHtml .= '<table class="table table-bordered table-striped table-condensed" style="width: 99%; margin-top: 10px;" align="center">';
        $sHtml .= '<tr>
                    <td align="center" colspan="4">REPORTE # CONTACTOS</td>
                    <td align="center" colspan="2">
                        <div class="btn btn-primary btn-sm" onclick="updateEntidad(2);">
                            <span class="glyphicon glyphicon-floppy-disk"></span>
                            Actualizar
                        </div>
                    </td>
                </tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td>Codigo</td>';
        $sHtml .= '<td>Tipo</td>';
        $sHtml .= '<td>Numero</td>';
        $sHtml .= '<td>Operador</td>';
        $sHtml .= '<td>Cash</td>';
        $sHtml .= '<td>Eliminar</td>';
        $sHtml .= '</tr>';
        //Telefonos
        $sqlDire = "select tlcp_cod_tlcp, tlcp_tlf_tlcp, tlcp_tip_ticp, tlcp_cod_oper,
                    tlcp_cash_op 
                    from saetlcp
                    where tlcp_cod_empr = $empresa and
                    tlcp_cod_clpv = $clpv";
        //$oReturn->alert($sqlDire);
        if ($oIfx->Query($sqlDire)) {
            if ($oIfx->NumFilas() > 0) {
                $i = 1;
                unset($arrayDire);
                do {
                    $dire_cod_dire = $oIfx->f('tlcp_cod_tlcp');
                    $dire_dir_dire = $oIfx->f('tlcp_tlf_tlcp');
                    $tlcp_tip_ticp = $oIfx->f('tlcp_tip_ticp');
                    $tlcp_cod_oper = $oIfx->f('tlcp_cod_oper');
                    $tlcp_cash_op = $oIfx->f('tlcp_cash_op');

                    $arrayDire[] = array($dire_cod_dire);

                    //tipo telefono
                    $fu->AgregarCampoLista('tipoTelf_' . $dire_cod_dire, '|LEFT', false, 150, 100);
                    $sql = "select codigo, tipo from comercial.tipo_telefono";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            do {
                                $codigo = $oCon->f('codigo');
                                $tipo = $oCon->f('tipo');
                                $fu->AgregarOpcionCampoLista('tipoTelf_' . $dire_cod_dire, $tipo, $codigo);
                            } while ($oCon->SiguienteRegistro());
                        }
                    }
                    $oCon->Free();

                    //tipo operador
                    $fu->AgregarCampoLista('tipoTelfO_' . $dire_cod_dire, '|LEFT', false, 150, 100);
                    $sql = "select id, operador from comercial.tipo_operador";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            do {
                                $id = $oCon->f('id');
                                $operador = $oCon->f('operador');
                                $fu->AgregarOpcionCampoLista('tipoTelfO_' . $dire_cod_dire, $operador, $id);
                            } while ($oCon->SiguienteRegistro());
                        }
                    }
                    $oCon->Free();


                    $ifu->AgregarCampoNumerico('tele_' . $dire_cod_dire, '|left', false, $dire_dir_dire, 120, 100);

                    $fu->cCampos["tipoTelf_" . $dire_cod_dire]->xValor = $tlcp_tip_ticp;
                    $fu->cCampos["tipoTelfO_" . $dire_cod_dire]->xValor = $tlcp_cod_oper;

                    $checkedTelf = '';
                    if ($tlcp_cash_op == 'S') {
                        $checkedTelf = 'checked';
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left">' . $dire_cod_dire . '</td>';
                    $sHtml .= '<td align="left">' . $fu->ObjetoHtml('tipoTelf_' . $dire_cod_dire) . '</td>';
                    $sHtml .= '<td align="left">' . $ifu->ObjetoHtml('tele_' . $dire_cod_dire) . '</td>';
                    $sHtml .= '<td align="left">' . $fu->ObjetoHtml('tipoTelfO_' . $dire_cod_dire) . '</td>';
                    $sHtml .= '<td align="center">
                                    <input type="radio" name="telfCash" value="' . $dire_cod_dire . '" ' . $checkedTelf . '/>    
                                </td>';
                    $sHtml .= '<td align="center">
									<div class="btn btn-danger btn-sm" onclick="eliminarEntidad(' . $dire_cod_dire . ', 2);">
										<span class="glyphicon glyphicon-remove"></span>
									</div>
                                </td>';
                    $sHtml .= '</tr>';
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '</table>';


        $_SESSION['ARRAY_CLPV_TELF'] = $arrayDire;

        $oReturn->assign("divReporteTelefono", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function consultar_cash($clpv)
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];

    $oReturn = new xajaxResponse();

    $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];  // 593 ECUADOR 51 PERU

    $table_op = '';
    $table_op .= '<table id="tbcash" class="table table-striped table-bordered table-hover table-condensed table-responsive"  align="center" >';
    $table_op .= '<thead>';
    $table_op .= '<tr><th colspan="10">LISTA DE CUENTAS BANCARIAS</th></tr>
                    <tr>
                        <th class="success" style="color: #00859B; font-weight: bold">NRO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DOI tipo</th>
                        <th class="success" style="color: #00859B; font-weight: bold">DOI numero</th>
                        <th class="success" style="color: #00859B; font-weight: bold"># CUENTA</th>
                        <th class="success" style="color: #00859B; font-weight: bold">BANCO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">TIPO CUENTA</th>
                        <th class="success" style="color: #00859B; font-weight: bold">CODIGO INTERBANCARIO</th>
                        <th class="success" style="color: #00859B; font-weight: bold">MONEDA</th>
                        <th class="success" style="color: #00859B; font-weight: bold">EDITAR</th> 
						<th class="success" style="color: #00859B; font-weight: bold">ELIMINAR</th>  
                    </tr>					
        				</thead>';
    $table_op .= '<tbody>';

    $sql = "select * from  cuentas_cash where cash_cod_clpv=$clpv and cash_cod_empr=$idempresa and cash_est_del='N' order by id";
    $i = 1;
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $idItem = $oCon->f('id');
                $ruc = $oCon->f('cash_ruc_clpv');
                $num_cuenta = $oCon->f('cash_num_cuen');
                $tip_cuenta = $oCon->f('cash_tip_cuen');
                $banco = $oCon->f('cash_cod_ban');
                $cod_inter = $oCon->f('cash_cod_int');
                $cod_iden = $oCon->f('cash_cod_iden');
                $cod_mone = $oCon->f('cash_cod_mone');




                $optionBancos = '';
                $sql = "select banc_cod_banc, banc_nom_banc from saebanc where banc_cod_empr = $idempresa ";
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConA->f('banc_cod_banc') == $banco) {
                                $optionBancos .= '<option value="' . $oConA->f('banc_cod_banc') . '" selected>' . $oConA->f('banc_nom_banc') . '</option>';
                            } else {
                                $optionBancos .= '<option value="' . $oConA->f('banc_cod_banc') . '">' . $oConA->f('banc_nom_banc') . '</option>';
                            }
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();

                $optionTipo = '';

                if ($tip_cuenta == '00') {
                    $optionTipo .= '<option value="00" selected>Corriente</option>';
                } elseif ($tip_cuenta != '00') {
                    $optionTipo .= '<option value="00">Corriente</option>';
                }

                if ($tip_cuenta == '10') {
                    $optionTipo .= '<option value="10" selected>Cuenta de Ahorros</option>';
                } elseif ($tip_cuenta != '10') {
                    $optionTipo .= '<option value="10">Cuenta de Ahorros</option>';
                }

                if ($tip_cuenta == '20') {
                    $optionTipo .= '<option value="20" selected>Cuenta de Pago Virtual</option>';
                } elseif ($tip_cuenta != '20') {
                    $optionTipo .= '<option value="20">Cuenta de Pago Virtual</option>';
                }

                $optionIden = '';


                $sql = "select * from comercial.tipo_iden_clpv_pais where pais_codigo_inter='$S_PAIS_API_SRI'";
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConA->f('id_iden_pais') == $cod_iden) {
                                $optionIden .= '<option value="' . $oConA->f('id_iden_pais') . '" selected>' . $oConA->f('identificacion') . '</option>';
                            } else {
                                $optionIden .= '<option value="' . $oConA->f('id_iden_pais') . '">' . $oConA->f('identificacion') . '</option>';
                            }
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();

                $optionMone = '';


                $sql = "select mone_cod_mone, upper(mone_des_mone) as mone_des_mone
                    from saemone
                    where mone_cod_empr = $idempresa
                    order by mone_des_mone";
                if ($oConA->Query($sql)) {
                    if ($oConA->NumFilas() > 0) {
                        do {

                            if ($oConA->f('mone_cod_mone') == $cod_mone) {
                                $optionMone .= '<option value="' . $oConA->f('mone_cod_mone') . '" selected>' . $oConA->f('mone_des_mone') . '</option>';
                            } else {
                                $optionMone .= '<option value="' . $oConA->f('mone_cod_mone') . '">' . $oConA->f('mone_des_mone') . '</option>';
                            }
                        } while ($oConA->SiguienteRegistro());
                    }
                }
                $oConA->Free();



                $img = '<span class="btn btn-primary btn-sm" title="Editar" value="Editar" onClick="javascript:edit_del_cash(' . $idItem . ',1,' . $clpv . ');">
				<i class="glyphicon glyphicon-floppy-disk"></i>
				</span>';
                $eli = '<span class="btn btn-danger btn-sm" title="Eliminar" value="Eliminar" onClick="javascript:edit_del_cash(' . $idItem . ',2,' . $clpv . ');">
				<i class="glyphicon glyphicon-remove"></i>
				</span>';
                $table_op .= '<tr>';
                $table_op .= '<td align="center">' . $i . '</td>';
                $table_op .= '<td><select id="iden_' . $idItem . '" name="iden_' . $idItem . '" class="form-control select2" required>
                <option value="">..Seleccione una Opcion..</option>
                    ' . $optionIden . '
                </select></td>';
                $table_op .= '<td> <input type="text"  id="ruc_' . $idItem . '" name="ruc_' . $idItem . '"  value="' . $ruc . '" class="form-control"  /> </td>';
                $table_op .= '<td> <input type="text"  id="cta_' . $idItem . '" name="cta_' . $idItem . '"  value="' . $num_cuenta . '" class="form-control"  /> </td>';
                $table_op .= '<td><select id="ban_' . $idItem . '" name="ban_' . $idItem . '" class="form-control select2" required>
                <option value="">..Seleccione una Opcion..</option>
                    ' . $optionBancos . '
                </select></td>';
                $table_op .= '<td><select id="tip_' . $idItem . '" name="tip_' . $idItem . '" class="form-control select2" required>
                <option value="">..Seleccione una Opcion..</option>
                    ' . $optionTipo . '
                </select></td>';
                $table_op .= '<td> <input type="text"  id="int_' . $idItem . '" name="int_' . $idItem . '"  value="' . $cod_inter . '" class="form-control"  /> </td>';
                $table_op .= '<td><select id="mone_' . $idItem . '" name="mone_' . $idItem . '" class="form-control select2" required>
                <option value="">..Seleccione una Opcion..</option>
                    ' . $optionMone . '
                </select></td>';
                $table_op .= '<td align="center">' . $img . '</td>';
                $table_op .= '<td align="center">' . $eli . '</td>';
                $table_op .= '</tr>';
                $i++;
            } while ($oCon->SiguienteRegistro());

            $table_op .= '</tbody>';
            $table_op .= '</table>';
        } else {
            $table_op = '<div style="color:red;" align="center"><span>NO SE ENCONTRO REGISTROS</span></div>';
        }
    }
    $oCon->Free();



    $oReturn->assign("divConsultaCash", "innerHTML", $table_op);
    return $oReturn;
}


function actualiza_cash($id, $ruc, $cta, $banco, $tip, $int, $iden, $mone, $exe, $clpv)
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $usuario_web = $_SESSION['U_ID'];
    $fecha_hora = date('Y-m-d H:i:s');

    $ruc = trim($ruc);
    $cta = trim($cta);

    if (!empty($int)) {
        $int = trim($int);
    }




    if (!empty($idempresa)) {

        try {
            // commit
            $oCon->QueryT('BEGIN');

            $sqldig = "select digitos from comercial.tipo_iden_clpv_pais where id_iden_pais=$iden";
            $numdig = consulta_string($sqldig, 'digitos', $oCon, 0);



            if ($exe == 1) {




                if (strlen($ruc) != $numdig) {
                    $oReturn->alert('El DOI numero debe contener: ' . $numdig . ' digitos');
                } else {
                    $sql = "update cuentas_cash
				set 
				cash_ruc_clpv='$ruc',    
                cash_num_cuen='$cta',
                cash_tip_cuen='$tip',
                cash_cod_ban =$banco,
                cash_cod_int ='$int',
                cash_cod_iden=$iden,
                cash_cod_mone=$mone,
				cash_updated_at ='$fecha_hora',
				cash_user_updated =$usuario_web
				  where id=$id";
                    $oCon->QueryT($sql);
                }
            } else {

                $sql = "update cuentas_cash
				set 
				cash_deleted_at ='$fecha_hora',
				cash_user_deleted =$usuario_web,
				  cash_est_del='S'
				  where id=$id";
                $oCon->QueryT($sql);
            }


            if (strlen($ruc) == $numdig) {
                $oCon->QueryT('COMMIT');
                if ($exe == 1) {
                    $oReturn->alert('Actualizado Correctamente');
                } else {
                    $oReturn->alert('Eliminado Correctamente');
                }
                $oReturn->script("consulta_cash($clpv);");
            } else {
                $oReturn->alert('El DOI debe contener: ' . $numdig);
            }
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert("Su sesion finalizo vuelva a ingresar");
        $oReturn->script("recargar_formulario();");
    }



    return $oReturn;
}

function reporteEmailCliente($aForm = '')
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    unset($_SESSION['ARRAY_CLPV_EMAI']);
    $empresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];


    try {

        //lectura sucia
        //////////////

        $sql = "select  tiem_cod_tiem, tiem_des_tiem from saetiem where tiem_cod_empr = $empresa order by 1";
        unset($array_tipo);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $tiem_cod_tiem     = $oIfx->f('tiem_cod_tiem');
                    $tiem_des_tiem     = $oIfx->f('tiem_des_tiem');

                    $array_tipo[] = array($tiem_cod_tiem, $tiem_des_tiem);
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '<table class="table table-bordered table-striped table-condensed" style="width: 99%; margin-top: 10px;" align="center">';
        $sHtml .= '<tr>
                    <td align="center" colspan="2">REPORTE E-MAIL</td>
                    <td align="center" colspan="2">
                        <div class="btn btn-primary btn-sm" onclick="updateEntidad(3);">
                            <span class="glyphicon glyphicon-floppy-disk"></span>
                            Actualizar
                        </div>
                    </td>
                </tr>';

        $sHtml .= '<tr>';
        $sHtml .= '<td>Codigo</td>';
        $sHtml .= '<td>Email</td>';
        $sHtml .= '<td>Tipo</td>';
        $sHtml .= '<td>Cash</td>';
        $sHtml .= '<td>Eliminar</td>';
        $sHtml .= '</tr>';

        //Direcciones
        /*
        $sqlDire = "select emai_cod_emai, emai_ema_emai, emai_cash_op, emai_cod_tiem
                    from saeemai
                    where emai_cod_empr = $empresa and
                    emai_cod_clpv = $clpv";
                    */



        $sqlDire = "select emai_cod_emai, emai_ema_emai, emai_cash_op, emai_cod_tiem, *
                                        from saeemai, saeclpv
                                        where 
                                                            emai_cod_clpv = clpv_cod_clpv and
                                                            emai_cod_sucu = clpv_cod_sucu and
                                                            clpv_cod_empr = $empresa and
                                                            emai_cod_clpv = $clpv and
                                                            clpv_cod_clpv = $clpv and
                                                            clpv_clopv_clpv = 'PV'
                                                            ;";

        //$oReturn->alert($sqlDire);
        if ($oIfx->Query($sqlDire)) {
            if ($oIfx->NumFilas() > 0) {
                $i = 1;
                unset($arrayDire);
                do {
                    $dire_cod_dire = $oIfx->f('emai_cod_emai');
                    $dire_dir_dire = $oIfx->f('emai_ema_emai');
                    $emai_cash_op = $oIfx->f('emai_cash_op');
                    $emai_cod_tiem     = $oIfx->f('emai_cod_tiem');

                    if (!$emai_cod_tiem) {
                        $emai_cod_tiem = 0;
                    }

                    $sql = "select  tiem_cod_tiem, tiem_des_tiem from saetiem where
								tiem_cod_empr = $empresa and
								tiem_cod_tiem = $emai_cod_tiem	";
                    $tiem_des_tiem = consulta_string_func($sql, 'tiem_des_tiem', $oIfxA, '');

                    $arrayDire[] = array($dire_cod_dire);

                    $ifu->AgregarCampoTexto('emai_' . $dire_cod_dire, '|left', false, $dire_dir_dire, 250, 100);

                    // TIPO DE CORREOS
                    $ifu->AgregarCampoLista('emai_tiem' . $dire_cod_dire, 'Tipo|left', false, '100');
                    if (count($array_tipo) > 0) {
                        foreach ($array_tipo as $val) {
                            $ifu->AgregarOpcionCampoLista('emai_tiem' . $dire_cod_dire, $val[1], $val[0]);
                        }
                    }


                    $ifu->cCampos['emai_tiem' . $dire_cod_dire]->xValor = $emai_cod_tiem;

                    $checkedTelf = '';
                    if ($emai_cash_op == 'S') {
                        $checkedTelf = 'checked';
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left" colspan="1">' . $dire_cod_dire . '</td>';
                    $sHtml .= '<td align="left" colspan="1">' . $ifu->ObjetoHtml('emai_' . $dire_cod_dire) . '</td>';
                    $sHtml .= '<td align="left" colspan="1">' . $ifu->ObjetoHtml('emai_tiem' . $dire_cod_dire) . '</td>';
                    $sHtml .= '<td align="center">
                                    <input type="radio" name="EmailCash" value="' . $dire_cod_dire . '" ' . $checkedTelf . '/>    
                                </td>';
                    $sHtml .= '<td align="center">
									<div class="btn btn-danger btn-sm" onclick="eliminarEntidad(' . $dire_cod_dire . ', 3);">
										<span class="glyphicon glyphicon-remove"></span>
									</div>
                                </td>';
                    $sHtml .= '</tr>';
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '</table>';


        $_SESSION['ARRAY_CLPV_EMAI'] = $arrayDire;

        $oReturn->assign("divReporteEmail", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function reporteDireCliente($aForm = '')
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    unset($_SESSION['ARRAY_CLPV_EMAI']);
    $empresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];


    try {

        //lectura sucia
        //////////////
        $arrayDire = [];
        //tipo direccion
        $sqlTipoDire = "select id, tipo from comercial.tipo_direccion";
        if ($oCon->Query($sqlTipoDire)) {
            if ($oCon->NumFilas() > 0) {
                unset($arrayDire);
                do {
                    $arrayDire[$oCon->f('id')] = $oCon->f('tipo');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();
        $sHtml = '';
        $sHtml .= '<table class="table table-bordered table-striped table-condensed" style="width: 90%; margin-top: 10px;" align="center">';
        $sHtml .= '<tr>
                    <td align="center" colspan="7" class="bg-primary">REPORTE DIRECCIONES</td>
                </tr>';

        $sHtml .= '<tr>';
        $sHtml .= '<td>Codigo</td>';
        $sHtml .= '<td>Tipo</td>';
        $sHtml .= '<td>Calle 1</td>';
        $sHtml .= '<td>Calle 2</td>';
        $sHtml .= '<td>Direccion</td>';
        $sHtml .= '<td>Editar</td>';
        $sHtml .= '</tr>';

        //Direcciones
        $sqlDire = "select dire_cod_dire, dire_dir_dire,
                    dire_cod_tipo, dire_cod_vivi, dire_cod_sect,
                    dire_barr_dire, dire_call1_dire, dire_call2_dire,
                    dire_nume_dire, dire_edif_dire, dire_refe_dire,
                    dire_anti_dire
                    from saedire
                    where dire_cod_clpv = $clpv";
        //echo $sqlDire;exit;
        //$oReturn->alert($sqlDire);
        if ($oIfx->Query($sqlDire)) {
            if ($oIfx->NumFilas() > 0) {
                //unset($arrayDire);
                do {
                    $dire_cod_dire = $oIfx->f('dire_cod_dire');
                    $dire_cod_tipo = $oIfx->f('dire_cod_tipo');
                    //echo $dire_cod_tipo;exit;
                    $dire_dir_dire = $oIfx->f('dire_dir_dire');
                    $dire_call1_dire = $oIfx->f('dire_call1_dire');
                    $dire_call2_dire = $oIfx->f('dire_call2_dire');

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left">' . $dire_cod_dire . '</td>';
                    $sHtml .= '<td align="left">' . $arrayDire[$dire_cod_tipo] . '</td>';
                    $sHtml .= '<td align="left">' . $dire_call1_dire . '</td>';
                    $sHtml .= '<td align="left">' . $dire_call2_dire . '</td>';
                    $sHtml .= '<td align="left">' . $dire_dir_dire . '</td>';
                    $sHtml .= '<td align="center">
                                    <div class="btn btn-warning btn-sm" onclick="editarDireccion(' . $dire_cod_dire . ');">
                                        <span class="glyphicon glyphicon-pencil"></span>
                                    </div>
                                </td>';
                    $sHtml .= '<td align="center">
									<div class="btn btn-danger btn-sm" onclick="eliminarEntidad(' . $dire_cod_dire . ', 1);">
										<span class="glyphicon glyphicon-remove"></span>
									</div>
                                </td>';
                    $sHtml .= '</tr>';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '</table>';


        $oReturn->assign("divReporteDireccion", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function updateEntidad($aForm = '', $op = 0)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables de sesion
    $arrayDire = $_SESSION['ARRAY_CLPV_DIRE'];
    $arrayTele = $_SESSION['ARRAY_CLPV_TELF'];
    $arrayEmai = $_SESSION['ARRAY_CLPV_EMAI'];
    $usuario_web = $_SESSION['U_ID'];
    $empresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];
    $fechaSever = date("Y-m-d H:i:s");

    try {

        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN;');

        //op

        if ($op == 1) {
            if (count($arrayDire) > 0) {
                foreach ($arrayDire as $valDire) {
                    $dire_cod_dire = $valDire[0];
                    $direccion = utf8_decode($aForm['dire_' . $dire_cod_dire]);

                    $sqlDire = "update saedire set dire_dir_dire = '$direccion' where dire_cod_clpv = $clpv and dire_cod_dire = $dire_cod_dire";
                    //$oReturn->alert($sqlDire);
                    $oIfx->QueryT($sqlDire);
                }

                //control modificado datos
                /*  $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                            values($empresa, $clpv, 'D', 2, $usuario_web, '$fechaSever')";
                $oCon->QueryT($sqlCtrl); */

                $oReturn->script("editarDire($clpv)");
            }
        } elseif ($op == 2) {

            if (count($arrayTele) > 0) {
                foreach ($arrayTele as $valTele) {
                    $dire_cod_dire = $valTele[0];
                    $direccion = $aForm['tele_' . $dire_cod_dire];
                    $tipoTelf = $aForm['tipoTelf_' . $dire_cod_dire];
                    $tipoTelfO = $aForm['tipoTelfO_' . $dire_cod_dire];
                    $telfCash = $aForm['telfCash'];

                    $opCash = 'N';
                    if ($telfCash == $dire_cod_dire) {
                        $opCash = 'S';
                    }

                    if (empty($tipoTelfO)) {
                        $tipoTelfO = 0;
                    }

                    $sqlDire = "update saetlcp set tlcp_tlf_tlcp = '$direccion', 
                                                    tlcp_tip_ticp = '$tipoTelf', 
                                                    tlcp_cod_oper = $tipoTelfO,
                                                    tlcp_cash_op = '$opCash'
                                                    where tlcp_cod_clpv = $clpv and 
                                                    tlcp_cod_tlcp = $dire_cod_dire";
                    //$oReturn->alert($sqlDire);
                    $oIfx->QueryT($sqlDire);
                }

                //control modificado datos
                /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                            values($empresa, $clpv, 'T', 2, $usuario_web, '$fechaSever')";
                $oCon->QueryT($sqlCtrl); */

                $oReturn->script("reporteTelefonoCliente()");
            }
        } elseif ($op == 3) {

            //$oReturn->alert( count($arrayEmai) );
            if (count($arrayEmai) > 0) {
                foreach ($arrayEmai as $valEmai) {
                    $dire_cod_dire = $valEmai[0];
                    $direccion = $aForm['emai_' . $dire_cod_dire];
                    $EmailCash = $aForm['EmailCash'];
                    $emai_cod_tiem    = $aForm['emai_tiem' . $dire_cod_dire];

                    $opCash = 'N';
                    if ($EmailCash == $dire_cod_dire) {
                        $opCash = 'S';
                    }

                    if (empty($emai_cod_tiem)) {
                        $emai_cod_tiem = 0;
                    }

                    $sqlDire = "update saeemai set emai_ema_emai = '$direccion',
                                                    emai_cash_op = '$opCash' ,
                                                    emai_cod_tiem= $emai_cod_tiem  
                                                    where emai_cod_clpv = $clpv and 
                                                    emai_cod_emai = $dire_cod_dire";
                    //$oReturn->alert($sqlDire);
                    $oIfx->QueryT($sqlDire);
                }

                //control modificado datos
                /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                            values($empresa, $clpv, 'E', 2, $usuario_web, '$fechaSever')";
                $oCon->QueryT($sqlCtrl); */

                $oReturn->script("reporteEmailCliente()");
            }
        }


        $oReturn->alert('Procesado Correctamente');
        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT;');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function eliminarEntidad($aForm = '', $id = 0, $op = 0)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables de sesion
    $usuario_web = $_SESSION['U_ID'];
    $empresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];
    $fechaSever = date("Y-m-d H:i:s");

    try {

        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN;');

        //op

        if ($op == 1) {

            $sqlDire = "delete from saedire where dire_cod_clpv = $clpv and dire_cod_dire = $id";
            $oIfx->QueryT($sqlDire);

            //control modificado datos
            /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
						values($empresa, $clpv, 'D', 3, $usuario_web, '$fechaSever')";
			$oCon->QueryT($sqlCtrl); */

            $oReturn->script("reporteDireCliente()");
        } elseif ($op == 2) {

            $sqlDire = "delete from saetlcp where tlcp_cod_clpv = $clpv and tlcp_cod_tlcp = $id";
            $oIfx->QueryT($sqlDire);

            //control modificado datos
            /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
						values($empresa, $clpv, 'T', 3, $usuario_web, '$fechaSever')";
			$oCon->QueryT($sqlCtrl); */

            $oReturn->script("reporteTelefonoCliente()");
        } elseif ($op == 3) {

            $sqlDire = "delete from saeemai where emai_cod_clpv = $clpv and emai_cod_emai = $id";
            $oIfx->QueryT($sqlDire);

            //control modificado datos
            /* $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
						values($empresa, $clpv, 'E', 3, $usuario_web, '$fechaSever')";
            $oCon->QueryT($sqlCtrl); */

            $oReturn->script("reporteEmailCliente()");
        }


        $oReturn->alert('Procesado Correctamente');
        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT;');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function seleccionaItemd($aForm = '', $id = 0)
{

    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    unset($_SESSION['aDataGirdCuentaAplicada']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {
        //lectura sucia
        //////////////

        $sql = "select dire_cod_empr, dire_cod_sucu, dire_cod_clpv, dire_dir_dire,
				dire_cod_tipo, dire_cod_vivi, dire_cod_sect,
				dire_barr_dire, dire_call1_dire, dire_call2_dire,
				dire_nume_dire, dire_edif_dire, dire_refe_dire,
				dire_anti_dire
				from saedire
				where dire_cod_dire = $id";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $clpv_cod_clpv = $oIfx->f('clpv_cod_clpv');
                $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                $clpv_cod_fpag = $oIfx->f('clpv_cod_fpag');
                $clpv_cod_sucu = $oIfx->f('clpv_cod_sucu');
                $clv_con_clpv = $oIfx->f('clv_con_clpv');
                $clpv_nom_come = $oIfx->f('clpv_nom_come');
                $grpv_cod_grpv = $oIfx->f('grpv_cod_grpv');
                $clpv_cod_zona = $oIfx->f('clpv_cod_zona');
                $clpv_pre_ven = round($oIfx->f('clpv_pre_ven'));
                $clpv_cod_vend = $oIfx->f('clpv_cod_vend');
                $clpv_lim_cred = $oIfx->f('clpv_lim_cred');
                $clpv_pro_pago = $oIfx->f('clpv_pro_pago');
                $clpv_dsc_clpv = $oIfx->f('clpv_dsc_clpv');
                $clpv_dsc_prpg = $oIfx->f('clpv_dsc_prpg');
                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');
                $clpv_cod_titu = $oIfx->f('clpv_cod_titu');
                $clpv_cod_tclp = $oIfx->f('clpv_cod_tclp');
                $clpv_cod_trta = $oIfx->f('clpv_cod_trta');
                $clpv_cod_fpagop = $oIfx->f('clpv_cod_fpagop');
                $clpv_cod_tprov = $oIfx->f('clpv_cod_tprov');
                $clpv_cod_tpago = $oIfx->f('clpv_cod_tpago');
                $clpv_cod_paisp = $oIfx->f('clpv_cod_paisp');
                $clpv_etu_clpv = $oIfx->f('clpv_etu_clpv');
                $clpv_cod_banc = $oIfx->f('clpv_cod_banc');
                $clpv_num_ctab = $oIfx->f('clpv_num_ctab');
                $clpv_tip_ctab = $oIfx->f('clpv_tip_ctab');
                $clpv_cod_cact = $oIfx->f('clpv_cod_cact');
                $clpv_rep_clpv = $oIfx->f('clpv_rep_clpv');
                $clpv_nov_clpv = $oIfx->f('clpv_nov_clpv');

                if (empty($clpv_etu_clpv)) {
                    $clpv_etu_clpv = 0;
                }

                if (empty($clpv_cod_zona)) {
                    $clpv_cod_zona = 0;
                }
            }
        }
        $oIfx->Free();


        $oReturn->assign('identificacion', 'value', $clv_con_clpv);
        $oReturn->assign('ruc_cli', 'value', $clpv_ruc_clpv);
        $oReturn->assign('nombre', 'value', $clpv_nom_clpv);
        $oReturn->assign('nombre_comercial', 'value', $clpv_nom_come);
        $oReturn->assign('grupo', 'value', $grpv_cod_grpv);
        $oReturn->assign('clpv_cod_sucu', 'value', $clpv_cod_sucu);
        $oReturn->assign('zona', 'value', $clpv_cod_zona);
        $oReturn->assign('limite', 'value', $clpv_lim_cred);
        $oReturn->assign('dias_pago', 'value', $clpv_pro_pago);
        $oReturn->assign('pago', 'value', $clpv_dsc_prpg);
        $oReturn->assign('dsctGeneral', 'value', $clpv_dsc_clpv);
        $oReturn->assign('dsctDetalle', 'value', $clpv_dsc_prpg);
        $oReturn->assign('codigoCliente', 'value', $clpv_cod_clpv);
        $oReturn->assign('tipo_cliente', 'value', $clpv_cod_cact);
        $oReturn->assign('tipo_prove', 'value', $clpv_cod_tprov);
        $oReturn->assign('pago', 'value', $clpv_cod_tpago);
        $oReturn->assign('tipo_pago', 'value', $clpv_cod_fpagop);
        $oReturn->assign('pais', 'value', $clpv_cod_paisp);
        $oReturn->assign('cuenta', 'value', $clpv_num_ctab);
        $oReturn->assign('banco', 'value', $clpv_cod_banc);
        $oReturn->assign('tipoCuenta', 'value', $clpv_tip_ctab);
        $oReturn->assign('representante', 'value', $clpv_rep_clpv);
        $oReturn->assign('observaciones', 'value', $clpv_nov_clpv);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function genera_formulario_portafolio($aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    //$sucursal = $aForm['clpv_cod_sucu'];
    $codigoCliente = $aForm['codigoCliente'];

    // if (empty($sucursal)) {
    $sucursal = $_SESSION['U_SUCURSAL'];
    //}


    try {

        //LECTURA SUCIA
        //////////////

        //query bodega
        $sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayBodega);
                do {
                    $arrayBodega[$oIfx->f('bode_cod_bode')] = $oIfx->f('bode_nom_bode');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '<table class="table table-bordered table-hover table-striped table-condensed" style="width: 90%; margin-top: 20px;">
							<tr>
								<td colspan="9" align="center" class="bg-primary">REPORTE DE PRODUCTOS</td>
							</tr>
							<tr>
								<td>BODEGA</td>
								<td>PRODUCTO</td>
								<td>COD. ALTERNO</td>
								<td>PRECIO ULT.</td>
								<td>PRECIO PACT.</td>
								<td>DIAS ENTR.</td>
								<td>VAL MERMA</td>
								<td>OBSERVACIONES</td>
                                <td>EDITAR</td>';

        $sql = "select 	pp.ppvpr_cod_prod, 		pp.ppvpr_nom_prod, 		pp.ppvpr_cod_bode,
				pp.ppvpr_pre_ult, 		pp.ppvpr_pre_pac, 		pp.ppvpr_dia_entr, 
				pp.ppvpr_cod_alte,	pp.ppvpr_obs_ppvpr, pp.ppvpr_val_merm	
				from saeppvpr pp
				where 
				pp.ppvpr_cod_clpv = $codigoCliente and
				pp.ppvpr_cod_empr = $idempresa and
				pp.ppvpr_cod_sucu = $sucursal";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $i = 1;
                do {
                    $ppvpr_cod_prod = $oIfx->f('ppvpr_cod_prod');
                    $ppvpr_nom_prod = $oIfx->f('ppvpr_nom_prod');
                    $ppvpr_cod_bode = $oIfx->f('ppvpr_cod_bode');
                    $ppvpr_pre_ult     = $oIfx->f('ppvpr_pre_ult');
                    $ppvpr_pre_pac    = $oIfx->f('ppvpr_pre_pac');
                    $ppvpr_dia_entr    = $oIfx->f('ppvpr_dia_entr');
                    $ppvpr_cod_alte    = $oIfx->f('ppvpr_cod_alte');
                    $ppvpr_obs_ppvpr = $oIfx->f('ppvpr_obs_ppvpr');
                    $ppvpr_val_merm    = $oIfx->f('ppvpr_val_merm');




                    $ifu->AgregarCampoTexto($codigoCliente . '_' . $ppvpr_cod_prod . '_alternoProdServ', '|LEFT', false, $ppvpr_cod_alte, 110, 100);

                    $ifu->AgregarCampoNumerico($codigoCliente . '_' . $ppvpr_cod_prod . '_precioProdServ', 'Precio|left', false, $ppvpr_pre_ult, 60, 9);

                    $ifu->AgregarCampoNumerico($codigoCliente . '_' . $ppvpr_cod_prod . '_pactadoProdServ', 'Ds1|left', false, $ppvpr_pre_pac, 60, 9);

                    $ifu->AgregarCampoNumerico($codigoCliente . '_' . $ppvpr_cod_prod . '_diasProdServ', 'Ds2|left', false, $ppvpr_dia_entr, 60, 9);

                    $ifu->AgregarCampoNumerico($codigoCliente . '_' . $ppvpr_cod_prod . '_mermaProdServ', 'Ds2|left', false, $ppvpr_val_merm, 60, 9);

                    $ifu->AgregarCampoTexto($codigoCliente . '_' . $ppvpr_cod_prod . '_detalleProdServ', '|LEFT', false, $ppvpr_obs_ppvpr, 200, 100);





                    $sHtml .= ' <tr>';
                    $sHtml .= '<td align="left">' . $arrayBodega[$ppvpr_cod_bode] . '</td>';
                    $sHtml .= '<td align="left">' . $ppvpr_cod_prod . ' - ' . $ppvpr_nom_prod . '</td>';
                    $sHtml .= '<td align="left">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_alternoProdServ') . '</td>';
                    $sHtml .= '<td align="right">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_precioProdServ') . '</td>';
                    $sHtml .= '<td align="right">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_pactadoProdServ') . '</td>';
                    $sHtml .= '<td align="right">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_diasProdServ') . '</td>';
                    $sHtml .= '<td align="right">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_mermaProdServ') . '</td>';
                    $sHtml .= '<td align="left">' . $ifu->ObjetoHtml($codigoCliente . '_' . $ppvpr_cod_prod . '_detalleProdServ') . '</td>';
                    $sHtml .= '<td align="center">	<div class="btn btn-primary btn-sm" onclick="guardarProdServ(2,\'' . $ppvpr_cod_prod . '\');">
                    <span class="glyphicon glyphicon-pencil"></span>
                </div></td>';
                    $sHtml .= '</tr>';
                    $total = 0;
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        $sHtml .= '</table>';

        $oReturn->assign("divReporteProdServClpv", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function completa_ceros($aForm = '', $op = 0)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //Definiciones
    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];

    // CONTRIBUYENTE ESPECIAL
    if ($op == 1) {
        $factura = $aForm['facturaInicio'];
    } elseif ($op == 2) {
        $factura = $aForm['facturaFin'];
    }

    $ceros = secuencial(2, '', $factura - 1, 9);

    if ($op == 1) {
        $oReturn->assign("facturaInicio", "value", $ceros);
    } elseif ($op == 2) {
        $oReturn->assign("facturaFin", "value", $ceros);
    }

    return $oReturn;
}

function cargar_ciudad($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $provincia = $aForm['provincia'];

    //  LECTURA SUCIA
    //////////////

    $oReturn->script('limpiar_lista();');

    $sql = "select ciud_cod_ciud, ciud_nom_ciud from saeciud where
                ciud_cod_provc = $provincia order by ciud_nom_ciud";

    $i = 0;
    $msn = "-- Seleccione una Opcion --";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('ciud_cod_ciud');
                $ciud = $oIfx->f('ciud_nom_ciud');
                $oReturn->script(('anadir_elemento_comun(' . $i . ',\'' . $id . '\', \'' . $ciud . '\' )'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        } else {
            $oReturn->script('limpiar_lista();');
            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        }
    }
    $oIfx->Free();

    return $oReturn;
}

function eliminarCoa($coa_cod_coa = 0, $aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();


    try {
        $oIfx->QueryT('BEGIN WORK;');

        $sql_borrar = "DELETE from saecoa
                            where coa_cod_coa = $coa_cod_coa";
        $oIfx->QueryT($sql_borrar);

        $oReturn->script("Swal.fire({
                            position: 'center',
                            type: 'success',
                            title: 'Autorizacion Eliminada Correctamente...!',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                            timer: 2000
                            })");




        $oIfx->QueryT('COMMIT WORK');
        $oReturn->script("listaCcli();");
    } catch (Exception $e) {
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function cargar_canton($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $canton = $aForm['canton'];

    //  LECTURA SUCIA
    //////////////

    $oReturn->script('limpiar_lista_canton();');

    $sql = "select parr_cod_parr, parr_des_parr from saeparr where parr_cod_cant = '$canton'";

    $i = 0;
    $msn = "-- Seleccione una Opcion --";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('parr_cod_parr');
                $ciud = $oIfx->f('parr_des_parr');
                $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',\'' . $id . '\', \'' . $ciud . '\' )'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',"", \'' . $msn . '\' )'));
        } else {
            $oReturn->script('limpiar_lista_canton();');
            $oReturn->script(('anadir_elemento_comun_canton(' . $i . ',"", \'' . $msn . '\' )'));
        }
    }
    $oIfx->Free();

    return $oReturn;
}

function listaCcli($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    try {

        //lectura sucia
        //////////////

        //variables de session
        $idempresa = $_SESSION['U_EMPRESA'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        if (empty($codigoCliente)) {
            $codigoCliente = 0;
        }

        //query clpv
        $sqlClpv = "select coa_cod_coa, coa_fec_vali, coa_aut_usua, coa_aut_impr,
                    coa_seri_docu, coa_fact_ini, coa_fact_fin, coa_est_coa
                    from saecoa 
                    where clpv_cod_clpv = $codigoCliente and
                    clpv_cod_empr = $idempresa";
        if ($oIfx->Query($sqlClpv)) {
            if ($oIfx->NumFilas() > 0) {
                $sHtml .= '<table class="table table-bordered table-hover table-striped table-condensed" style="width: 90%; margin-top: 20px;">';
                $sHtml .= '<tr>';
                $sHtml .= '<td colspan="9" align="center" class="bg-primary">REPORTE AUTORIZACIONES</td>';
                $sHtml .= '</tr>';
                $sHtml .= '<tr class="info">';
                $sHtml .= '<td>No.</td>';
                $sHtml .= '<td>Autorizacion Usuario</td>';
                $sHtml .= '<td>Autorizacion Imprenta</td>';
                $sHtml .= '<td>Factura Inicio</td>';
                $sHtml .= '<td>Factura Fin</td>';
                $sHtml .= '<td>Serie</td>';
                $sHtml .= '<td>Fecha Caduca</td>';
                $sHtml .= '<td>Estado</td>';
                $sHtml .= '<td>Editar</td>';
                $sHtml .= '</tr>';
                $i = 1;
                do {
                    $coa_cod_coa = $oIfx->f('coa_cod_coa');
                    $coa_fec_vali = $oIfx->f('coa_fec_vali');
                    $coa_aut_usua = $oIfx->f('coa_aut_usua');
                    $coa_aut_impr = $oIfx->f('coa_aut_impr');
                    $coa_seri_docu = $oIfx->f('coa_seri_docu');
                    $coa_fact_ini = $oIfx->f('coa_fact_ini');
                    $coa_fact_fin = $oIfx->f('coa_fact_fin');
                    $coa_est_coa = $oIfx->f('coa_est_coa');

                    $classEstado = '';
                    $estado = '';
                    if ($coa_est_coa == 1) {
                        $estado = 'ACTIVO';
                        $classEstado = 'bg-success';
                    } else {
                        $estado = 'INACTIVO';
                        $classEstado = 'bg-danger';
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>' . $i++ . '</td>';
                    $sHtml .= '<td>' . $coa_aut_usua . '</td>';
                    $sHtml .= '<td>' . $coa_aut_impr . '</td>';
                    $sHtml .= '<td>' . $coa_fact_ini . '</td>';
                    $sHtml .= '<td>' . $coa_fact_fin . '</td>';
                    $sHtml .= '<td>' . $coa_seri_docu . '</td>';
                    $sHtml .= '<td>' . $coa_fec_vali . '</td>';
                    $sHtml .= '<td class="' . $classEstado . '">' . $estado . '</td>';
                    $sHtml .= '<td align="center">
									<div class="btn btn-warning btn-sm" onclick="editarCoa(' . $coa_cod_coa . ');">
										<span class="glyphicon glyphicon-pencil"></span>
									</div>
                                    <div class="btn btn-danger btn-sm" onclick="eliminarCoa(' . $coa_cod_coa . ');">
										<span class="glyphicon glyphicon-trash"></span>
									</div>
								</td>';
                    $sHtml .= '</tr>';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '</table>';

        $oReturn->assign("divReporteDatos", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function editarCoa($aForm = '', $id = 0)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    try {

        //lectura sucia
        //////////////

        //variables de session
        $idempresa = $_SESSION['U_EMPRESA'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        if (empty($codigoCliente)) {
            $codigoCliente = 0;
        }

        //query clpv
        $sqlClpv = "select coa_cod_coa, coa_fec_vali, coa_aut_usua, coa_aut_impr,
                    coa_seri_docu, coa_fact_ini, coa_fact_fin, coa_est_coa
                    from saecoa 
                    where clpv_cod_clpv = $codigoCliente and
                    clpv_cod_empr = $idempresa and
					coa_cod_coa = $id";
        if ($oIfx->Query($sqlClpv)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $coa_cod_coa = $oIfx->f('coa_cod_coa');
                    $coa_fec_vali = $oIfx->f('coa_fec_vali');
                    $coa_aut_usua = $oIfx->f('coa_aut_usua');
                    $coa_aut_impr = $oIfx->f('coa_aut_impr');
                    $coa_seri_docu = $oIfx->f('coa_seri_docu');
                    $coa_fact_ini = $oIfx->f('coa_fact_ini');
                    $coa_fact_fin = $oIfx->f('coa_fact_fin');
                    $coa_est_coa = $oIfx->f('coa_est_coa');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $oReturn->assign("codigoCoa", "value", $coa_cod_coa);
        $oReturn->assign("autUsuario", "value", $coa_aut_usua);
        $oReturn->assign("autImprenta", "value", $coa_aut_impr);
        $oReturn->assign("facturaInicio", "value", $coa_fact_ini);
        $oReturn->assign("facturaFin", "value", $coa_fact_fin);
        $oReturn->assign("facturaSerie", "value", $coa_seri_docu);
        $oReturn->assign("fechaCaduca", "value", $coa_fec_vali);
        $oReturn->assign("estadoATS", "value", $coa_est_coa);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function guardarCcli($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $codigoCoa = $aForm['codigoCoa'];
    $codigoCliente = $aForm['codigoCliente'];
    $autUsuario = $aForm['autUsuario'];
    $autImprenta = $aForm['autImprenta'];
    $facturaInicio = $aForm['facturaInicio'];
    $facturaFin = $aForm['facturaFin'];
    $facturaSerie = $aForm['facturaSerie'];
    $fechaCaduca = fecha_informix_func($aForm['fechaCaduca']);
    $estadoATS = $aForm['estadoATS'];

    // selecciona sucursal del clpv
    $sql_sucu = "select clpv_cod_sucu from saeclpv where clpv_cod_clpv = $codigoCliente and clpv_cod_empr = $idempresa";
    $clpv_cod_sucu = consulta_string($sql_sucu, 'clpv_cod_sucu', $oIfx, $idsucursal);

    if (!empty($codigoCliente)) {

        try {
            // commit
            $oIfx->QueryT('BEGIN WORK;');

            if (empty($codigoCoa)) {
                $sqlInsert = "insert into saecoa(clpv_cod_sucu, clpv_cod_empr, clpv_cod_clpv,
                            coa_aut_usua, coa_aut_impr, coa_fact_ini, coa_fact_fin,
                            coa_seri_docu, coa_fec_vali, coa_est_coa)
                            values($clpv_cod_sucu, $idempresa, '$codigoCliente',
                            '$autUsuario', '$autImprenta', '$facturaInicio', '$facturaFin',
                            '$facturaSerie', '$fechaCaduca', '$estadoATS')";
                $oIfx->QueryT($sqlInsert);
            } else {
                $sqlUpdate = "update saecoa set coa_aut_usua = '$autUsuario',
                            coa_aut_impr = '$autImprenta',
                            coa_fact_ini = '$facturaInicio',
                            coa_fact_fin = '$facturaFin',
                            coa_seri_docu = '$facturaSerie',
                            coa_fec_vali = '$fechaCaduca',
                            coa_est_coa = '$estadoATS'
                            where clpv_cod_empr = $idempresa and
                            coa_cod_coa = $codigoCoa and
                            clpv_cod_clpv = $codigoCliente";
                $oIfx->QueryT($sqlUpdate);
            }

            $oReturn->assign("codigoCoa", "value", '');
            $oReturn->assign("autUsuario", "value", '');
            $oReturn->assign("autImprenta", "value", '');
            $oReturn->assign("facturaInicio", "value", '');
            $oReturn->assign("facturaFin", "value", '');
            $oReturn->assign("facturaSerie", "value", '');
            $oReturn->assign("fechaCaduca", "value", date('Y/m/d'));
            $oReturn->assign("estadoATS", "value", '');

            $oIfx->QueryT('COMMIT WORK;');
            $oReturn->alert('Procesado Correctamente...!');
            $oReturn->script('xajax_listaCcli(xajax.getFormValues(\'form1\'))');
        } catch (Exception $e) {
            // rollback
            $oIfx->QueryT('ROLLBACK WORK;');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert('Por favor seleccione Cliente para continuar...!');
    }

    return $oReturn;
}

function listaProdServCliente($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    unset($_SESSION['ARRAY_CLPV_PRODSERV']);

    $nombreBuscar = $aForm['nombreBuscar'];

    try {

        //lectura sucia
        //////////////

        //variables de session
        $idempresa = $_SESSION['U_EMPRESA'];
        $idsucursal = $_SESSION['U_SUCURSAL'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        //query clpv
        $sqlClpv = "select clse_cod_clse, clse_cod_clpv, clse_cod_prod,
					clse_nom_prod, clse_cod_bode, clse_cod_nomp,
					clse_pre_clse, clse_ds1_clse, clse_ds2_clse,
					clse_cco_clse
					from saeclse
					where clse_cod_empr = $idempresa and
					clse_cod_clpv = $codigoCliente";
        if ($oIfx->Query($sqlClpv)) {
            if ($oIfx->NumFilas() > 0) {

                $table .= '<table align="center" cellpadding="0" cellspacing="2" width="100%" border="0">
							<tr>
								<td>
									<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/save.png"
									title = "Presione aqui para Modificar";
									style="cursor: hand !important; cursor: pointer !important;" 
									onclick="javascript:modificarProdServ();"
									alt="Nuevo Pedido" 
									align="bottom" />
								</td>
							</tr>
							<tr>
								<th>BODEGA</th>
								<th>CODIGO</th>
								<th>NOMBRE</th>
								<th>TIPO PRECIO</th>
								<th>PRECIO</th>
								<th>DES1</th>
								<th>DES2</th>
								<th>C. COSTOS</th>
								<th>ELIMINAR</th>
							</tr>';
                unset($array);
                do {
                    $clse_cod_clse = $oIfx->f('clse_cod_clse');
                    $clse_cod_clpv = $oIfx->f('clse_cod_clpv');
                    $clse_cod_prod = $oIfx->f('clse_cod_prod');
                    $clse_nom_prod = $oIfx->f('clse_nom_prod');
                    $clse_cod_bode = $oIfx->f('clse_cod_bode');
                    $clse_cod_nomp = $oIfx->f('clse_cod_nomp');
                    $clse_pre_clse = $oIfx->f('clse_pre_clse');
                    $clse_ds1_clse = $oIfx->f('clse_ds1_clse');
                    $clse_ds2_clse = $oIfx->f('clse_ds2_clse');
                    $clse_cco_clse = $oIfx->f('clse_cco_clse');


                    $array[] = array($clse_cod_clse, $clse_cod_clpv);

                    //campos formulario
                    //bodega
                    $ifu->AgregarCampoLista($clse_cod_clpv . '_id_bodega_' . $clse_cod_clse, '|LEFT', false, 150, 100);
                    $sql = "select  b.bode_cod_bode, b.bode_nom_bode 
							from saebode b, saesubo s
							where b.bode_cod_bode = s.subo_cod_bode and
							b.bode_cod_empr = $idempresa and
							s.subo_cod_empr = $idempresa";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $bode_cod_bode = $oIfxA->f('bode_cod_bode');
                                $bode_nom_bode = $oIfxA->f('bode_nom_bode');
                                $ifu->AgregarOpcionCampoLista($clse_cod_clpv . '_id_bodega_' . $clse_cod_clse, $bode_nom_bode, $bode_cod_bode);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();
                    $ifu->AgregarComandoAlPonerEnfoque($clse_cod_clpv . '_id_bodega_' . $clse_cod_clse, 'this.blur();');

                    //tipo de precio
                    $ifu->AgregarCampoLista($clse_cod_clpv . '_tprecio_' . $clse_cod_clse, '|LEFT', false, 110, 100);
                    $sql = "select nomp_cod_nomp, nomp_nomb_nomp from saenomp where nomp_cod_empr = $idempresa";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $nomp_cod_nomp = $oIfxA->f('nomp_cod_nomp');
                                $nomp_nomb_nomp = $oIfxA->f('nomp_nomb_nomp');
                                $ifu->AgregarOpcionCampoLista($clse_cod_clpv . '_tprecio_' . $clse_cod_clse, $nomp_nomb_nomp, $nomp_cod_nomp);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();

                    //centro de costos
                    $ifu->AgregarCampoLista($clse_cod_clpv . '_ccosto_' . $clse_cod_clse, '|LEFT', false, 110, 100);
                    $sql = "select ccosn_cod_ccosn, ccosn_nom_ccosn
							from saeccosn where
							ccosn_cod_empr = $idempresa and
							ccosn_mov_ccosn = 1 order by 2";
                    if ($oIfxA->Query($sql)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $ccosn_cod_ccosn = $oIfxA->f('ccosn_cod_ccosn');
                                $ccosn_nom_ccosn = $oIfxA->f('ccosn_nom_ccosn');
                                $ifu->AgregarOpcionCampoLista($clse_cod_clpv . '_ccosto_' . $clse_cod_clse, $ccosn_nom_ccosn, $ccosn_cod_ccosn);
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();


                    $ifu->AgregarCampoNumerico($clse_cod_clpv . '_precio_' . $clse_cod_clse, 'Precio|left', false, $clse_pre_clse, 50, 9);

                    $ifu->AgregarCampoNumerico($clse_cod_clpv . '_desc1_' . $clse_cod_clse, 'Ds1|left', false, $clse_ds1_clse, 50, 9);

                    $ifu->AgregarCampoNumerico($clse_cod_clpv . '_desc2_' . $clse_cod_clse, 'Ds2|left', false, $clse_ds2_clse, 50, 9);

                    $ifu->cCampos[$clse_cod_clpv . '_id_bodega_' . $clse_cod_clse]->xValor = $clse_cod_bode;
                    $ifu->cCampos[$clse_cod_clpv . '_tprecio_' . $clse_cod_clse]->xValor = $clse_cod_nomp;
                    $ifu->cCampos[$clse_cod_clpv . '_ccosto_' . $clse_cod_clse]->xValor = $clse_cco_clse;

                    if ($sClass == 'off')
                        $sClass = 'on';
                    else
                        $sClass = 'off';

                    $table .= '<tr bgcolor="#EBF0FA" height="20"  class="' . $sClass . '"
								onMouseOver="javascript:this.className=\'link\';"
								onMouseOut="javascript:this.className=\'' . $sClass . '\'"
								style="cursor: pointer;">
								   <td align="left">' . $ifu->ObjetoHtml($clse_cod_clpv . '_id_bodega_' . $clse_cod_clse) . '</td>
								   <td align="left">' . $clse_cod_prod . '</td>
								   <td align="left">' . $clse_nom_prod . '</td>
								   <td align="left">' . $ifu->ObjetoHtml($clse_cod_clpv . '_tprecio_' . $clse_cod_clse) . '</td>
								   <td align="right">' . $ifu->ObjetoHtml($clse_cod_clpv . '_precio_' . $clse_cod_clse) . '</td>
								   <td align="right">' . $ifu->ObjetoHtml($clse_cod_clpv . '_desc1_' . $clse_cod_clse) . '</td>
								   <td align="right">' . $ifu->ObjetoHtml($clse_cod_clpv . '_desc2_' . $clse_cod_clse) . '</td>
								   <td align="left">' . $ifu->ObjetoHtml($clse_cod_clpv . '_ccosto_' . $clse_cod_clse) . '</td>
								   <td align="center">
									<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/cancel.png"
									title = "Eliminar";
									style="cursor: hand !important; cursor: pointer !important; display:in-line;"
									onclick="eliminarProdServ(' . $clse_cod_clpv . ', ' . $clse_cod_clse . ');"/>   
								   </td>
							</tr>';
                } while ($oIfx->SiguienteRegistro());
                $table .= '</table>
							</fieldset>';
            }
        }
        $oIfx->Free();

        $_SESSION['ARRAY_CLPV_PRODSERV'] = $array;

        $oReturn->assign("divReporteProdServClpv", "innerHTML", $table);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function guardarProdServ($tipo, $prod = '', $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //varibales formulario			
    $codigoCliente = $aForm['codigoCliente'];
    $idBodegaProdServ = $aForm['idBodegaProdServ'];
    $prodProdServ = trim($aForm['prodProdServ']);
    $codProdProdServ = trim($aForm['codProdProdServ']);
    $alternoProdServ = trim($aForm['alternoProdServ']);




    $precioProdServ = $aForm['precioProdServ'];
    if (empty($precioProdServ)) {
        $precioProdServ = 'NULL';
    }
    $pactadoProdServ = $aForm['pactadoProdServ'];
    if (empty($pactadoProdServ)) {
        $pactadoProdServ = 'NULL';
    }
    $diasProdServ = $aForm['diasProdServ'];
    if (empty($diasProdServ)) {
        $diasProdServ = 'NULL';
    }

    $mermaProdServ = $aForm['mermaProdServ'];
    if (empty($mermaProdServ)) {
        $mermaProdServ = 'NULL';
    }

    $detalleProdServ = trim($aForm['detalleProdServ']);

    //CAMPOS ACTUALIZACION

    if ($tipo == 2) {


        $alternoProdServ = trim($aForm[$codigoCliente . '_' . $prod . '_alternoProdServ']);




        $precioProdServ = $aForm[$codigoCliente . '_' . $prod . '_precioProdServ'];
        if (empty($precioProdServ)) {
            $precioProdServ = 'NULL';
        }
        $pactadoProdServ = $aForm[$codigoCliente . '_' . $prod . '_pactadoProdServ'];
        if (empty($pactadoProdServ)) {
            $pactadoProdServ = 'NULL';
        }
        $diasProdServ = $aForm[$codigoCliente . '_' . $prod . '_diasProdServ'];
        if (empty($diasProdServ)) {
            $diasProdServ = 'NULL';
        }

        $mermaProdServ = $aForm[$codigoCliente . '_' . $prod . '_mermaProdServ'];
        if (empty($mermaProdServ)) {
            $mermaProdServ = 'NULL';
        }

        $detalleProdServ = trim($aForm[$codigoCliente . '_' . $prod . '_detalleProdServ']);
    }


    try {

        if ($tipo == 1) {

            //VALIDACION PRODUCTO

            $sql = "select count(*) as ctrl from saeppvpr where ppvpr_cod_clpv=$codigoCliente and trim(ppvpr_cod_prod)='$codProdProdServ' 
             and ppvpr_cod_empr=$idempresa and ppvpr_cod_sucu=$idsucursal and ppvpr_cod_bode=$idBodegaProdServ";
            $cont = consulta_string($sql, 'ctrl', $oCon, 0);
            if ($cont != 0) {

                $sql = "select ppvpr_cod_prod from saeppvpr where ppvpr_cod_clpv=$codigoCliente and trim(ppvpr_cod_prod)='$codProdProdServ' and ppvpr_cod_empr=$idempresa";
                $codprod = consulta_string($sql, 'ppvpr_cod_prod', $oCon, '');

                throw new Exception("El producto ya se encuentra ingresado");
            }
        }

        if (!empty($alternoProdServ) && $tipo == 1) {



            //VALIDACION CODIGO ALTERNO

            $sql = "select count(*) as ctrl from saeppvpr where ppvpr_cod_clpv=$codigoCliente  
            and trim(ppvpr_cod_alte)='$alternoProdServ' and ppvpr_cod_empr=$idempresa and ppvpr_cod_sucu=$idsucursal";
            $cont = consulta_string($sql, 'ctrl', $oCon, 0);
            if ($cont != 0) {

                $sql = "select ppvpr_cod_prod from saeppvpr where ppvpr_cod_clpv=$codigoCliente and trim(ppvpr_cod_alte)='$alternoProdServ' and ppvpr_cod_empr=$idempresa";
                $codprod = consulta_string($sql, 'ppvpr_cod_prod', $oCon, '');

                throw new Exception("El codigo alterno ya se encuentra asignado al producto: " . $codprod);
            }
        }


        if (!empty($alternoProdServ) && $tipo == 2) {

            //VALIDACION CODIGO ALTERNO

            $sql = "select count(*) as ctrl from saeppvpr where ppvpr_cod_clpv=$codigoCliente  
            and trim(ppvpr_cod_alte)='$alternoProdServ' and ppvpr_cod_empr=$idempresa and ppvpr_cod_sucu=$idsucursal and trim(ppvpr_cod_prod) <>'$prod'";
            $cont = consulta_string($sql, 'ctrl', $oCon, 0);
            if ($cont != 0) {

                $sql = "select ppvpr_cod_prod from saeppvpr where ppvpr_cod_clpv=$codigoCliente and trim(ppvpr_cod_alte)='$alternoProdServ' and ppvpr_cod_empr=$idempresa";
                $codprod = consulta_string($sql, 'ppvpr_cod_prod', $oCon, '');

                throw new Exception("El codigo alterno ya se encuentra asignado al producto: " . $codprod);
            }
        }


        $alternoProdServ = $alternoProdServ != '' ? "'" . $alternoProdServ . "'" : 'NULL';

        if ($tipo == 1) {
            $sqlInsert = "insert into saeppvpr(ppvpr_cod_empr, ppvpr_cod_sucu, ppvpr_cod_prod, 
					ppvpr_nom_prod, ppvpr_cod_bode, ppvpr_pre_ult, 
					ppvpr_pre_pac, ppvpr_dia_entr, ppvpr_cod_alte, 
					ppvpr_cod_clpv, ppvpr_obs_ppvpr,  ppvpr_val_merm)
					values($idempresa, $idsucursal, '$codProdProdServ',
					'$prodProdServ', $idBodegaProdServ, $precioProdServ,
					$pactadoProdServ, $diasProdServ, $alternoProdServ, 
					'$codigoCliente', '$detalleProdServ', $mermaProdServ)";
            $oIfx->QueryT($sqlInsert);
        } else {
            $sqlUpdate = "update saeppvpr set ppvpr_pre_ult=$precioProdServ, 
            ppvpr_pre_pac=$pactadoProdServ, ppvpr_dia_entr=$diasProdServ, ppvpr_cod_alte=$alternoProdServ, 
            ppvpr_cod_clpv='$codigoCliente', ppvpr_obs_ppvpr='$detalleProdServ',  ppvpr_val_merm=$mermaProdServ where 
            ppvpr_cod_clpv ='$codigoCliente' and trim(ppvpr_cod_prod)='$prod' and ppvpr_cod_empr=$idempresa and ppvpr_cod_sucu=$idsucursal";
            $oIfx->QueryT($sqlUpdate);
        }

        if ($tipo == 1) {
            $oReturn->alert('Ingresado Correctamente');
        } else {
            $oReturn->alert('Actualizado Correctamente');
        }


        $oReturn->script('xajax_genera_formulario_portafolio(xajax.getFormValues(\'form1\'))');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function modificarProdServ($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $array = $_SESSION['ARRAY_CLPV_PRODSERV'];
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];


    try {

        if (count($array) > 0) {
            foreach ($array as $val) {
                $clse_cod_clse = $val[0];
                $clse_cod_clpv = $val[1];

                //varibales formulario
                $idBodegaProdServ = $aForm[$clse_cod_clpv . '_id_bodega_' . $clse_cod_clse];
                $tprecioProdServ = $aForm[$clse_cod_clpv . '_tprecio_' . $clse_cod_clse];
                $precioProdServ = $aForm[$clse_cod_clpv . '_precio_' . $clse_cod_clse];
                $desc1ProdServ = $aForm[$clse_cod_clpv . '_desc1_' . $clse_cod_clse];
                $desc2ProdServ = $aForm[$clse_cod_clpv . '_desc2_' . $clse_cod_clse];
                $ccostoProdServ = $aForm[$clse_cod_clpv . '_ccosto_' . $clse_cod_clse];

                $sqlUpdate = "update saeclse set clse_cod_bode = $idBodegaProdServ,
							clse_cod_nomp = '$tprecioProdServ',
							clse_pre_clse = '$precioProdServ', 
							clse_ds1_clse = '$desc1ProdServ', 
							clse_ds2_clse = '$desc2ProdServ',
							clse_cco_clse = '$ccostoProdServ'
							where 
							clse_cod_clpv = $clse_cod_clpv and
							clse_cod_clse = $clse_cod_clse";
                $oIfx->QueryT($sqlUpdate);
            }
            //$oReturn->alert('Procesado Correctamente...');
            $oReturn->script('listaProdServCliente();');
        } else {
            $oReturn->alert('No existen registros para procesar...');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function eliminarProdServ($clpv, $clse)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {

        $sqlDelete = "delete from saeclse where clse_cod_clpv = $clpv and clse_cod_clse = $clse";
        $oIfx->QueryT($sqlDelete);

        //$oReturn->alert('Eliminado Correctamente...');
        $oReturn->script('listaProdServCliente();');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function cargarlistaProdServ($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();


    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables del formulario
    $idBodegaProdServ = $aForm['idBodegaProdServ'];

    $sql = "select p.prod_cod_prod, p.prod_nom_prod
			from saeprbo pr, saeprod p 
			where
			p.prod_cod_prod = pr.prbo_cod_prod and
			p.prod_cod_empr = pr.prbo_cod_empr and
			p.prod_cod_sucu = pr.prbo_cod_sucu and
			p.prod_cod_empr = $idempresa and
			pr.prbo_cod_bode = $idBodegaProdServ
			order by 2";
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_prod();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_prod(' . $i++ . ',\'' . $oIfx->f('prod_cod_prod') . '\', \'' . $oIfx->f('prod_nom_prod') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    return $oReturn;
}

function listaDsctoLinpCliente($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    unset($_SESSION['ARRAY_CLPV_DSCTOLINP']);

    try {

        //lectura sucia
        //////////////

        //variables de session
        $idempresa = $_SESSION['U_EMPRESA'];
        $idsucursal = $_SESSION['U_SUCURSAL'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        //query clpv
        $sqlClpv = "select clnp_cod_clnp, clnp_cod_empr, clnp_cod_sucu,
					clnp_cod_clpv, clnp_cod_linp, clnp_val_dsct,
					clnp_est_clnp, clnp_user_web, clnp_fech_server
					from saeclnp
					where clnp_cod_empr = $idempresa and
					clnp_cod_clpv = $codigoCliente";
        if ($oIfx->Query($sqlClpv)) {
            if ($oIfx->NumFilas() > 0) {

                $table .= '<table align="center" cellpadding="0" cellspacing="2" width="100%" border="0">
							<tr>
								<td>
									<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/save.png"
									title = "Presione aqui para Modificar";
									style="cursor: hand !important; cursor: pointer !important;" 
									onclick="javascript:modificarDsctoLinpCliente();"
									alt="Nuevo Pedido" 
									align="bottom" />
								</td>
							</tr>
							<tr>
								<th>LINEA INVENTARIO</th>
								<th>DESCUENTO</th>
								<th>ESTADO</th>
								<th>ELIMINAR</th>
							</tr>';
                unset($array);
                do {
                    $clnp_cod_clnp = $oIfx->f('clnp_cod_clnp');
                    $clnp_cod_empr = $oIfx->f('clnp_cod_empr');
                    $clnp_cod_sucu = $oIfx->f('clnp_cod_sucu');
                    $clnp_cod_clpv = $oIfx->f('clnp_cod_clpv');
                    $clnp_cod_linp = $oIfx->f('clnp_cod_linp');
                    $clnp_val_dsct = $oIfx->f('clnp_val_dsct');
                    $clnp_est_clnp = $oIfx->f('clnp_est_clnp');
                    $clnp_user_web = $oIfx->f('clnp_user_web');
                    $clnp_fech_server = $oIfx->f('clnp_fech_server');

                    $array[] = array($clnp_cod_clnp, $clnp_cod_clpv);

                    //query linea inventario
                    $sqlLinp = "select linp_des_linp from saelinp where linp_cod_empr = $idempresa and linp_cod_linp = $clnp_cod_linp";
                    $linp_des_linp = consulta_string($sqlLinp, 'linp_des_linp', $oIfxA, '');

                    $ifu->AgregarCampoNumerico($clnp_cod_clpv . '_dsct_' . $clnp_cod_clnp, 'Dscto|left', false, $clnp_val_dsct, 50, 9);

                    $ifu->AgregarCampoCheck($clnp_cod_clpv . '_check_' . $clnp_cod_clnp, 'Estado|left', false, $clnp_est_clnp);

                    $ifu->cCampos[$clnp_cod_clpv . '_linp_' . $clnp_cod_clnp]->xValor = $clnp_cod_linp;

                    if ($sClass == 'off')
                        $sClass = 'on';
                    else
                        $sClass = 'off';

                    $table .= '<tr bgcolor="#EBF0FA" height="20"  class="' . $sClass . '"
								onMouseOver="javascript:this.className=\'link\';"
								onMouseOut="javascript:this.className=\'' . $sClass . '\'"
								style="cursor: pointer;">
								   <td align="left">' . $linp_des_linp . '</td>
								   <td align="right">' . $ifu->ObjetoHtml($clnp_cod_clpv . '_dsct_' . $clnp_cod_clnp) . '</td>
								   <td align="center">' . $ifu->ObjetoHtml($clnp_cod_clpv . '_check_' . $clnp_cod_clnp) . '</td>
								   <td align="center">
									<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/cancel.png"
									title = "Eliminar";
									style="cursor: hand !important; cursor: pointer !important; display:in-line;"
									onclick="eliminarDsctoLinpCliente(' . $clnp_cod_clpv . ', ' . $clnp_cod_clnp . ');"/>   
								   </td>
							</tr>';
                } while ($oIfx->SiguienteRegistro());
                $table .= '</table>
							</fieldset>';
            }
        }
        $oIfx->Free();

        $_SESSION['ARRAY_CLPV_DSCTOLINP'] = $array;

        $oReturn->assign("divReporteDsctLinp", "innerHTML", $table);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function guardarDsctoLinpCliente($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $userWeb = $_SESSION['U_ID'];

    //varibales formulario
    $codigoCliente = $aForm['codigoCliente'];
    $linp = $aForm['linp'];
    $dsctoLinp = $aForm['dsctoLinp'];
    $fechaServer = date("Y-m-d");

    try {

        $sqlControl = "select count(*) as control from saeclnp
						where clnp_cod_clpv = $codigoCliente and
						clnp_cod_empr = $idempresa and
						clnp_cod_linp = $linp";
        $control = consulta_string($sqlControl, 'control', $oIfx, 0);

        if ($control == 0) {
            $sqlInsert = "insert into saeclnp(clnp_cod_empr, clnp_cod_sucu, clnp_cod_clpv,
						clnp_cod_linp, clnp_val_dsct, clnp_est_clnp,
						clnp_user_web, clnp_fech_server)
						values($idempresa, $idsucursal, $codigoCliente,
						$linp, '$dsctoLinp', 'S',
						$userWeb, '$fechaServer')";
            $oIfx->QueryT($sqlInsert);

            //$oReturn->alert('Ingresado Correctamente...');
            $oReturn->script('listaDsctoLinpCliente();');
        } else {
            $oReturn->alert('Linea de Inventario ya Parametrizada...');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function modificarDsctoLinpCliente($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $array = $_SESSION['ARRAY_CLPV_DSCTOLINP'];
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    try {

        if (count($array) > 0) {
            foreach ($array as $val) {
                $clnp_cod_clnp = $val[0];
                $clnp_cod_clpv = $val[1];

                //varibales formulario
                $dsctoLinp = $aForm[$clnp_cod_clpv . '_dsct_' . $clnp_cod_clnp];
                $checkLinp = $aForm[$clnp_cod_clpv . '_check_' . $clnp_cod_clnp];

                $estadoLinp = 'S';
                if (!empty($checkLinp)) {
                    $estadoLinp = 'S';
                } else {
                    $estadoLinp = 'N';
                }

                $sqlUpdate = "update saeclnp set 
							clnp_val_dsct = $dsctoLinp,
							clnp_est_clnp = '$estadoLinp'
							where 
							clnp_cod_clpv = $clnp_cod_clpv and
							clnp_cod_clnp = $clnp_cod_clnp";
                $oIfx->QueryT($sqlUpdate);
            }
            //$oReturn->alert('Procesado Correctamente...');
            $oReturn->script('listaDsctoLinpCliente();');
        } else {
            $oReturn->alert('No existen registros para procesar...');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function eliminarDsctoLinpCliente($clpv, $clnp)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {

        $sqlDelete = "delete from saeclnp where clnp_cod_clpv = $clpv and clnp_cod_clnp = $clnp";
        $oIfx->QueryT($sqlDelete);

        //$oReturn->alert('Eliminado Correctamente...');
        $oReturn->script('listaDsctoLinpCliente();');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function guardar_cliente($cod, $aForm = '')
{
    global $DSN_Ifx, $DSN;
    session_start();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_ifx = $_SESSION['U_USER_INFORMIX'];

    //LECTURA SUCIA
    //////////////

    $nombre = ($aForm['nombre']);
    $sucursal = $aForm['clpv_cod_sucu'];
    $nombre_come = ($aForm['nombre_comercial']);
    $clpv_desc_actividades = trim($aForm['clpv_desc_actividades'] ?? ($_POST['clpv_desc_actividades'] ?? ''));
    $ruc = $aForm['ruc_cli'];
    $direccion = ($aForm['direccion_cli']);
    $telefono     = $aForm['telefono_cli'];
    $zona         = $aForm['zona'];
    $emai_ema_emai = $aForm['emai_ema_emai'];
    $correo_contacto = $aForm['correo_contacto_'];

    $precio     = $aForm['clpv_pre_ven'];
    $vendedor     = $aForm['clpv_cod_vend'];
    $tipo         = $aForm['identificacion'];




    $grupo         = $aForm['grupo'];
    $limite     = $aForm['limite'];
    $dia         = $aForm['dias_pago'];
    $estado     = $aForm['estado'];
    $codigoCliente = isset($aForm['codigoCliente']) ? $aForm['codigoCliente'] : '';

    //echo $estado;exit;
    $dsctDetalle = $aForm['dsctDetalle'];
    $dsctGeneral = $aForm['dsctGeneral'];
    $tipo_cliente = $aForm['tipo_cliente'];
    $pago = $aForm['pago'];
    $tipo_prove = $aForm['tipo_prove'];
    $tipo_pago = $aForm['tipo_pago'];
    $pais = $aForm['pais'];
    $contriEspecial = $aForm['contriEspecial'];
    $representante = $aForm['representante'];
    $observaciones = $aForm['observaciones'];
    $clpv_ret_sn = $aForm['clpv_ret_sn'];
    $clpv_par_rela = $aForm['clpv_par_rela'];
    $clpv_tec_sn = $aForm['clpv_tec_sn'];
    $clpv_ubi_lati = $aForm['latitud_tmp'];
    $clpv_ubi_long = $aForm['longitud_tmp'];

    $codigo_unico = $aForm['codigoUnico'];
    $cod_cuenta_in = $aForm['cod_cuenta_in'];

    $clpv_cod_mone = $aForm['clpv_cod_mone'];

    //VARIABLES ADICIONALES

    $facebook_cli = $aForm['facebook_cli'];
    $insta_cli = $aForm['insta_cli'];
    $identif_propie = $aForm['identif_propie'];
    $fech_nac_prop = $aForm['fech_nac_prop'];
    $pagina_web_cli = $aForm['pagina_web_cli'];
    $aniversario_empr = $aForm['aniversario_empr'];
    $atencion_tn_clie = $aForm['atencion_tn_clie'];
    $horarios_cli = $aForm['horarios_cli'];
    $empr_trans = $aForm['empr_trans'];
    $tipo_entreg_clie = $aForm['tipo_entreg_clie'];
    $respon_flete = $aForm['respon_flete'];
    $tip_tiend = $aForm['tip_tiend'];
    $direcc_llega_clie = $aForm['direcc_llega_clie'];
    $condicion_vnt = $aForm['condicion_vnt'];
    $tip_fact_cli = $aForm['tip_fact_cli'];
    $ruta_vsta_cli = $aForm['ruta_visita_cli'];
    $notas_cli = $aForm['notas_cli'];
    $clpv_cod_char = $aForm['cod_char_clpv'];


    //REGIMEN PROVEEDOR
    $regimen_buen_contr_sn = $aForm['regimen_buen_contr_sn'];
    $regimen_percepcion_sn = $aForm['regimen_percepcion_sn'];
    $detraccion_sn = $aForm['detraccion_sn'];
    $retencion_sn = $aForm['retencion_sn'];

    if (empty($regimen_buen_contr_sn)) {
        $regimen_buen_contr_sn = 'N';
    }
    if (empty($regimen_percepcion_sn)) {
        $regimen_percepcion_sn = 'N';
    }
    if (empty($detraccion_sn)) {
        $detraccion_sn = 'N';
    }
    if (empty($retencion_sn)) {
        $retencion_sn = 'N';
    }
    $clpv_obs_clpv = "REGIMEN_BUEN_CONTRIBUYENTE:$regimen_buen_contr_sn,REGIMEN_PERCEPCION:$regimen_percepcion_sn,DETRACCION:$detraccion_sn,RETENCION:$retencion_sn";



    //$insta_cli = $aForm['insta_cli'];

    if (empty($fech_nac_prop)) {
        $fech_nac_prop = '2000-12-05';
    }
    if (empty($aniversario_empr)) {
        $aniversario_empr = '2000-12-05';
    }



    if (!empty($clpv_ret_sn)) {
        $clpv_ret_sn = 'S';
    } else {
        $clpv_ret_sn = 'N';
    }

    if (!empty($clpv_par_rela)) {
        $clpv_par_rela = 'S';
    } else {
        $clpv_par_rela = 'N';
    }

    if (!empty($clpv_tec_sn)) {
        $clpv_tec_sn = 'S';
    } else {
        $clpv_tec_sn = 'N';
    }

    if (empty($contriEspecial)) {
        $contriEspecial = 0;
    }


    if (!$limite) {
        $limite = 0;
    }
    if (!$dia) {
        $dia = 0;
    }
    if (!$dsctGeneral) {
        $dsctGeneral = 0;
    }
    if (!$dsctDetalle) {
        $dsctDetalle = 0;
    }

    //$oReturn->alert($contriEspecial);

    $fecha = date("Y-m-d");

    $estado = $aForm['estado'];

    // --------------------------------------------------
    // VALIDAR SI UAFE ESTÁ ACTIVO EN SAEEMPR
    // --------------------------------------------------
    $sqlUafe = "
        select emmpr_uafe_cprov
        from saeempr
        where empr_cod_empr = $idempresa
    ";
    $usaUafe = consulta_string($sqlUafe, 'emmpr_uafe_cprov', $oIfx, 'N');

    // Mantener el estado real cuando se edita: si viene vacío, obténgalo de la BD
    if (empty($estado) && !empty($codigoCliente)) {
        $sqlEstadoActual = "
            select clpv_est_clpv
            from saeclpv
            where clpv_cod_empr = $idempresa
              and clpv_cod_clpv = $codigoCliente
            limit 1
        ";
        $estado = consulta_string_func($sqlEstadoActual, 'clpv_est_clpv', $oIfx, 'P');
    }

    // Solo los nuevos proveedores deben iniciar en Pendiente cuando UAFE esté activo
    if (empty($codigoCliente) && ($usaUafe == 't' || $usaUafe == 'true' || $usaUafe == '1' || $usaUafe == 1)) {
        $estado = 'P';
    }
    
    // --------------------------------------------------
    // FIN VALIDAR SI UAFE ESTÁ ACTIVO EN SAEEMPR
    // --------------------------------------------------
    //control
    $control = 0;

    // RUC 01
    // CEDULA 02
    // PASAPORTE 03
    // CONSUMIDOR FINAL 07
    // EXTRANJERIA 04



    if ($tipo == '01' || $tipo == '02' || $tipo == '04') {
        // CEDULA - RUC
        $sql = "select count(*) as contador from saeclpv where
                        clpv_cod_empr = $idempresa and
                        clpv_ruc_clpv = '$ruc' and
                        clv_con_clpv = '$tipo' and
                        clpv_clopv_clpv = 'PV' ";
        $control = consulta_string($sql, 'contador', $oIfx, 0);
    }

    $val_ced = 'OK';

    if (!empty($tipo)) {
        if ($control <= 0) {
            if ($val_ced == 'OK') {

                // cuenta cliente
                $sql = "SELECT GRPV_CTA_GRPV FROM SAEGRPV WHERE
						GRPV_COD_EMPR = $idempresa AND
						GRPV_COD_MODU = 4 AND
						GRPV_COD_GRPV = '$grupo' ";
                $cuenta = consulta_string($sql, 'grpv_cta_grpv', $oIfx, '');

                try {
                    // commit
                    $oIfx->QueryT('BEGIN WORK;');

                    if (empty($zona)) {
                        $zona = "NULL";
                    }

                    // cliente nuevo
                    $sql = "insert into saeclpv (clpv_cod_sucu, clpv_cod_empr, clpv_cod_cuen,
                                                clpv_cod_zona, clv_con_clpv,  
                                                clpv_cod_char, clpv_clopv_clpv, clpv_nom_clpv, 
                                                clpv_ruc_clpv, clpv_est_clpv, 
                                                clpv_fec_des,  clpv_fec_has,  clpv_fec_reno,
                                                clpv_nom_come, clpv_desc_actividades, clpv_cal_clpv, clpv_est_mon,  
                                                clpv_lim_cred, clpv_pro_pago,   
                                                grpv_cod_grpv, clpv_dsc_clpv, clpv_dsc_prpg,
                                                clpv_cod_fpagop, clpv_cod_tprov, clpv_cod_tpago,
                                                clpv_cod_paisp, clpv_cod_cact, clpv_etu_clpv,
												clpv_rep_clpv, clpv_nov_clpv, clpv_ret_sn, clpv_par_rela, clpv_tec_sn, clpv_cod_mone,
                                                clpv_ubi_long, clpv_ubi_lati,clpv_cod_uniq,
                                                clpv_facebook_clpv, clpv_insta_clpv, ident_propi_clpv,
                                                fechnaci_propi_clpv, pagina_web_clpv, aniver_empr_clpv, 
                                                atencion_ofi_clpv,  horarios_aten_clpv,  empresa_trans_clpv, 
                                                tip_entrega_clpv, resp_flete_clpv,  tip_tienda_clpv, 
                                                direc_llegada, clpv_notas_clpv, cond_vent_clpv,
                                                tip_fac_clpv , ruta_visit_clpv, clpv_obs_clpv )
                                                VALUES  ($idsucursal, 	$idempresa, 	'$cuenta',
                                                $zona, 		'$tipo',       
                                                '$clpv_cod_char', 		'PV', 		'$nombre', 
                                                '$ruc', 		'$estado',
                                                '$fecha', 		'$fecha', 	'$fecha',
                                                '$nombre_come',         '$clpv_desc_actividades',         'A',            'N',           
                                                $limite, 		$dia,  		  		
                                                '$grupo', 		$dsctGeneral, $dsctDetalle,
                                                '$pago',        '$tipo_prove', '$tipo_pago',
                                                '$pais',        '$tipo_cliente', '$contriEspecial',
												'$representante', '$observaciones', '$clpv_ret_sn' , '$clpv_par_rela' ,'$clpv_tec_sn',  $clpv_cod_mone, 
                                                '$clpv_ubi_long', '$clpv_ubi_lati','$codigo_unico',
                                                '$facebook_cli', '$insta_cli',   '$identif_propie', 
                                                '$fech_nac_prop', '$pagina_web_cli', '$aniversario_empr', 
                                                '$atencion_tn_clie','$horarios_cli', '$empr_trans', 
                                                '$tipo_entreg_clie', '$respon_flete', '$tip_tiend', 
                                                '$direcc_llega_clie', '$notas_cli', '$condicion_vnt',
                                                '$tip_fact_cli','$ruta_vsta_cli', '$clpv_obs_clpv')";
                    //echo $sql; exit;
                    $oIfx->QueryT($sql);


                    // serial de cliente
                    $sql = "select clpv_cod_clpv from saeclpv where
                                        clpv_cod_empr = $idempresa and
                                        clpv_ruc_clpv = '$ruc' and
                                        clpv_clopv_clpv = 'PV' and
                                        clv_con_clpv = '$tipo' and
                                        clpv_fec_des = '$fecha'";
                    $clpv_cod_clpv = consulta_string($sql, 'clpv_cod_clpv', $oIfx, 0);
                    
                    // =============================================
                    //  INSERTAR CORREO_CONTACTO PARA CLIENTE NUEVO
                    // =============================================
                    if (!empty($correo_contacto)) {

                        // ¿El cliente ya tiene algún registro previo de email?
                        $sqlExiste = "
                            select count(*) as cant
                            from saeemai
                            where emai_cod_empr = $idempresa
                            and emai_cod_clpv = $clpv_cod_clpv
                            and emai_ema_emai = '$correo_contacto'
                        ";
                        $existe = consulta_string($sqlExiste, 'cant', $oIfx, 0);

                        if ($existe == 0) {

                            // INSERT del correo principal, tipo = 1
                            $sqlInsertCorreo = "
                                insert into saeemai(
                                    emai_cod_empr,
                                    emai_cod_sucu,
                                    emai_cod_clpv,
                                    emai_ema_emai,
                                    emai_cod_tiem,
                                    emai_cash_op
                                )values(
                                    $idempresa,
                                    $idsucursal,
                                    $clpv_cod_clpv,
                                    '$correo_contacto',
                                    1,      
                                    'N'     
                                )
                            ";
                            // echo $sqlInsertCorreo; exit;
                            $oIfx->QueryT($sqlInsertCorreo);
                        }
                    }



                    //VALIDACION PROCESO DE COMPRA

                    if (!empty($cod)) {
                        $sql = "select clpc_id_clpc,clpv_cod_clpv from saeclpc where clpv_cod_clpv=$cod";
                        if ($oIfxA->Query($sql)) {
                            if ($oIfxA->NumFilas() > 0) {
                                $clpv = $oIfxA->f('clpv_cod_clpv');
                                $sqlprof = "update comercial.inv_proforma_det set invpd_cod_clpv=$clpv_cod_clpv where invpd_cod_clpv='$clpv' ";
                                $oIfx->QueryT($sqlprof);

                                $sqlpedi = "update comercial.clpv_pedi set clpe_cod_clpv=$clpv_cod_clpv where clpe_cod_clpv=$cod ";
                                $oIfx->QueryT($sqlpedi);

                                $sqlprove = "delete from saeclpc where clpv_cod_clpv=$clpv ";
                                $oIfx->QueryT($sqlprove);
                            }
                        }
                    }

                    $oIfx->QueryT('COMMIT WORK;');
                    //----------------------------------------------------------
                    // ENVIAR CORREO AUTOMÁTICO UAFE
                    //----------------------------------------------------------
                    $usaUafe = strtolower(trim($usaUafe));

                    $esEmpresaUafe = ($usaUafe === 't' || $usaUafe === 'true' || $usaUafe === '1' || $usaUafe == 1);

                    if ($esEmpresaUafe && !empty($correo_contacto)) {
                        $oReturn->script("xajax_enviaEmail(xajax.getFormValues('form1'), '$correo_contacto');");
                    }
                    
                    //----------------------------------------------------------
                    // FIN ENVIAR CORREO AUTOMÁTICO UAFE
                    //----------------------------------------------------------

                    $oReturn->alert('Proveedor Ingresado Correctamente...');
                    $oReturn->assign('codigoCliente', 'value', $clpv_cod_clpv);
                } catch (Exception $e) {
                    // rollback
                    $oIfx->QueryT('ROLLBACK WORK;');
                    $oReturn->alert($e->getMessage());
                }
            } else {
                $oReturn->alert($val_ced);
            } // fin if ctrl validador cedula
        } else {
            $oReturn->alert('::.Error.:: El numero de identificacion ya esta asignado a otro Cliente...');
        }
    } else {
        $oReturn->alert('Por favor seleccione Tipo de Identificacion....');
    }



    return $oReturn;
}

function consultaExistenciaIden($codclpv = 0, $pedi = 0, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();


    //Lectura Sucia
    //////////////

    $idempresa = $_SESSION['U_EMPRESA'];

    $ruc = $aForm['ruc_cli'];
    $tipo = $aForm['identificacion'];
    $sql = "select count(*) as contador from saeclpv where
                        clpv_cod_empr = $idempresa and
                        clpv_ruc_clpv = '$ruc' and
                        clv_con_clpv = '$tipo' and
                        clpv_clopv_clpv = 'PV' ";
    echo $tipo;
    exit;
    $control = consulta_string($sql, 'contador', $oIfx, 0);
    //echo $control; exit;
    if ($control > 0) {
        $oReturn->alert('Numero de identificacion ya registrado...!');
        if ($codclpv != 0) {
            $oReturn->script("ingresar_prove_compras($codclpv,$pedi,'$ruc');");
        }
        $oReturn->assign('ruc_cli', 'value', '');
    } else {
        /*if ($tipo == '01')
        {
            if (substr($ruc, 10, 3) <> '001') 
            {
                $oReturn->alert('No es un RUC válido');
                $oReturn->assign('ruc_cli', 'value', '');

            }
            if ($ruc=='2222222222222')
            {
                $oReturn->alert('No es un RUC válido');
                $oReturn->assign('ruc_cli', 'value', '');
            }
            $provincia = substr($ruc, 0, 2);
            if ($provincia < 1 || $provincia > 24) 
            {
                $oReturn->alert('No es un RUC válido');
                $oReturn->assign('ruc_cli', 'value', '');
            }
            if (substr($ruc, 2, 1) >= 0 && substr($ruc, 2, 1) <= 5) 
            {
                $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
                $suma = 0;
                for ($i = 0; $i < 9; $i++) {
                    $producto = $ruc[$i] * $coeficientes[$i];
                    if ($producto >= 10) {
                        $producto -= 9;
                    }
                    $suma += $producto;
                }
                $digitoVerificador = (10 - ($suma % 10)) % 10;
                //echo $digitoVerificador; exit;
                if ($digitoVerificador != $ruc[9])
                {
                    $oReturn->alert('No es un RUC válido Persona Natural');
                    $oReturn->assign('ruc_cli', 'value', '');
                }            
            }

            if (substr($ruc, 2, 1) == 9) 
            {   
                $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
                $suma = 0;
                for ($i = 0; $i < 9; $i++) {
                    $suma += $ruc[$i] * $coeficientes[$i];
                }
                $digitoVerificador = (11 - ($suma % 11)) % 11;
                if ($digitoVerificador != $ruc[9])
                {
                    $oReturn->alert('No es un RUC válido Empresa Privada o SAS. Revise bien el RUC y contiue');
                    //$oReturn->assign('ruc_cli', 'value', '');
                }
            }        

            if (substr($ruc, 2, 1) == 6) 
            {
                $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
                $suma = 0;
                for ($i = 0; $i < 8; $i++) {
                    $suma += $ruc[$i] * $coeficientes[$i];
                }
                $digitoVerificador = (11 - ($suma % 11)) % 11;
                if ($digitoVerificador != $ruc[8])
                {
                    $oReturn->alert('No es un RUC válido Empresa Pública');
                    $oReturn->assign('ruc_cli', 'value', '');
                }
            }
        }*/
        if ($tipo == '02') {
            $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $producto = $ruc[$i] * $coeficientes[$i];
                if ($producto >= 10) {
                    $producto -= 9;
                }
                $suma += $producto;
            }
            $digitoVerificador = (10 - ($suma % 10)) % 10;
            if ($digitoVerificador != $ruc[9]) {
                $oReturn->alert('No es un RUC válido Persona Natural');
                $oReturn->assign('ruc_cli', 'value', '');
            }
        }
    }

    return $oReturn;
}

function ingresar_proveedor_compras($codclpv = 0, $ruc = 0, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];

    $sqlr = "select clpv_nom_clpv, clpv_cod_clpv from saeclpv where clpv_ruc_clpv='$ruc'";
    $clpv_cod_clpv = consulta_string($sqlr, 'clpv_cod_clpv', $oIfxA, 0);
    $clpv_nom = consulta_string($sqlr, 'clpv_nom_clpv', $oIfxA, 0);

    $sqlprof = "update comercial.inv_proforma_det set invpd_cod_clpv=$clpv_cod_clpv,invpd_nom_clpv='$clpv_nom' where invpd_cod_clpv='$codclpv' ";
    $oIfx->QueryT($sqlprof);

    $sqlpedi = "update comercial.clpv_pedi set clpe_cod_clpv=$clpv_cod_clpv,clpe_nom_clpv='$clpv_nom' where clpe_cod_clpv=$codclpv ";
    $oIfx->QueryT($sqlpedi);

    $sqlprove = "delete from saeclpc where clpv_cod_clpv=$codclpv ";
    $oIfx->QueryT($sqlprove);

    $oReturn->alert('Actualizado Correctamente');
    return $oReturn;
}

function validaTipoProve($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];

    $ruc = substr($aForm['ruc_cli'], 2, 1);

    if (!empty($ruc)) {
        if ($ruc == 6 || $ruc == 9) {
            $tipoProve = '02';
        } else {
            $tipoProve = '01';
        }
    }

    $oReturn->assign('tipo_prove', 'value', $tipoProve);

    return $oReturn;
}

function cargar_lista_zona($aForm = '', $cod = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    //Lectura Sucia
    //////////////

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $clpv_cod_sucu = $aForm['clpv_cod_sucu'];

    $oReturn = new xajaxResponse();

    $sql = "select zona_cod_zona, zona_nom_zona 
			from saezona 
			where zona_cod_empr = $idempresa and
			zona_cod_sucu = $clpv_cod_sucu order by 2";
    //$oReturn->alert($sql);
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_zona();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_zona(' . $i++ . ',\'' . $oIfx->f('zona_cod_zona') . '\', \'' . $oIfx->f('zona_nom_zona') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oReturn->assign('zona', 'value', $cod);

    return $oReturn;
}

function update_cliente_frame($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $oReturn = new xajaxResponse();

    //Variables de Sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $idvendedor = $_SESSION['U_VENDEDOR'];

    //Variables de Formulario
    $nombre = utf8_decode($aForm['nombre']);
    $sucursal = $aForm['clpv_cod_sucu'];
    $nombre_come = utf8_decode($aForm['nombre_comercial']);
    $clpv_desc_actividades = utf8_decode(
        trim($aForm['clpv_desc_actividades'] ?? ($_POST['clpv_desc_actividades'] ?? ''))
    );
    $ruc = $aForm['ruc_cli'];
    $direccion = utf8_decode($aForm['direccion_cli']);
    $telefono = $aForm['telefono_cli'];
    $zona = $aForm['zona'];
    $emai_ema_emai = $aForm['emai_ema_emai'];

    //email del formulario
    $correo_contacto = $aForm['correo_contacto_'];

    // echo $correo_contacto;
    // exit;

    $precio = $aForm['clpv_pre_ven'];
    $vendedor = $aForm['clpv_cod_vend'];
    $tipo = $aForm['identificacion'];
    $grupo = $aForm['grupo'];
    $limite = $aForm['limite'];
    $dia = $aForm['dias_pago'];
    $estado = $aForm['estado'];
    $dsctDetalle = $aForm['dsctDetalle'];
    $dsctGeneral = $aForm['dsctGeneral'];
    $cliente = $aForm['codigoCliente'];
    $pago = $aForm['pago'];
    $tipo_prove = $aForm['tipo_prove'];
    $tipo_pago = $aForm['tipo_pago'];
    $pais = $aForm['pais'];
    $contriEspecial = $aForm['contriEspecial'];
    $representante = $aForm['representante'];
    $observaciones = $aForm['observaciones'];
    $clpv_ret_sn = $aForm['clpv_ret_sn'];
    $clpv_par_rela = $aForm['clpv_par_rela'];
    $clpv_tec_sn = $aForm['clpv_tec_sn'];
    $clpv_ubi_lati = $aForm['latitud_tmp'];
    $clpv_ubi_long = $aForm['longitud_tmp'];


    $estado = $aForm['estado'];

    $codigo_unico = $aForm['codigoUnico'];
    $cod_cuenta_in = $aForm['cod_cuenta_in'];

    //VARIBLES ADICIONALES


    $facebook_cli = $aForm['facebook_cli'];
    $insta_cli = $aForm['insta_cli'];
    $identif_propie = $aForm['identif_propie'];
    $fech_nac_prop = $aForm['fech_nac_prop'];
    $pagina_web_cli = $aForm['pagina_web_cli'];
    $aniversario_empr = $aForm['aniversario_empr'];
    $atencion_tn_clie = $aForm['atencion_tn_clie'];
    $horarios_cli = $aForm['horarios_cli'];
    $empr_trans = $aForm['empr_trans'];
    $tipo_entreg_clie = $aForm['tipo_entreg_clie'];
    $respon_flete = $aForm['respon_flete'];
    $tip_tiend = $aForm['tip_tiend'];
    $direcc_llega_clie = $aForm['direcc_llega_clie'];
    $notas_cli = $aForm['notas_cli'];
    $condicion_vnt = $aForm['condicion_vnt'];
    $tip_fact_cli = $aForm['tip_fact_cli'];
    $ruta_vsta_cli = $aForm['ruta_visita_cli'];


    //REGIMEN PROVEEDOR
    $regimen_buen_contr_sn = $aForm['regimen_buen_contr_sn'];
    $regimen_percepcion_sn = $aForm['regimen_percepcion_sn'];
    $detraccion_sn = $aForm['detraccion_sn'];
    $retencion_sn = $aForm['retencion_sn'];



    if (empty($regimen_buen_contr_sn)) {
        $regimen_buen_contr_sn = 'N';
    }
    if (empty($regimen_percepcion_sn)) {
        $regimen_percepcion_sn = 'N';
    }
    if (empty($detraccion_sn)) {
        $detraccion_sn = 'N';
    }
    if (empty($retencion_sn)) {
        $retencion_sn = 'N';
    }

    $clpv_obs_clpv = "REGIMEN_BUEN_CONTRIBUYENTE:$regimen_buen_contr_sn,REGIMEN_PERCEPCION:$regimen_percepcion_sn,DETRACCION:$detraccion_sn,RETENCION:$retencion_sn";


    // echo $notas_cli;exit;

    if (empty($fech_nac_prop)) {
        $fech_nac_prop = '2000-12-05';
    }


    if (empty($aniversario_empr)) {
        $aniversario_empr = '2000-12-05';
    }

    if (!empty($clpv_ret_sn)) {
        $clpv_ret_sn = 'S';
    } else {
        $clpv_ret_sn = 'N';
    }

    if (!empty($clpv_par_rela)) {
        $clpv_par_rela = 'S';
    } else {
        $clpv_par_rela = 'N';
    }

    if (!empty($clpv_tec_sn)) {
        $clpv_tec_sn = 'S';
    } else {
        $clpv_tec_sn = 'N';
    }

    if (empty($contriEspecial)) {
        $contriEspecial = 0;
    }

    //campos por update
    $telf_op = $aForm['telf_op'];
    $dire_op = $aForm['dire_op'];
    $mail_op = $aForm['mail_op'];

    //datos cliente
    $tipo_cliente     = $aForm['tipo_cliente'];
    $clpv_cod_mone     = $aForm['clpv_cod_mone'];
    $clpv_cod_char     = $aForm['cod_char_clpv'];


    //Lectura Sucia
    //////////////

    try {
        //Commit
        $oIfx->QueryT('BEGIN WORK;');

        // cuenta cliente
        $sql = "SELECT GRPV_CTA_GRPV FROM SAEGRPV WHERE
				GRPV_COD_EMPR = $idempresa AND
				GRPV_COD_MODU = 4 AND
				GRPV_COD_GRPV = '$grupo' ";
        $cuenta = consulta_string($sql, 'grpv_cta_grpv', $oIfx, '');

        if (!$limite) {
            $limite = 0;
        }
        if (!$dia) {
            $dia = 0;
        }
        if (!$dsctGeneral) {
            $dsctGeneral = 0;
        }
        if (!$dsctDetalle) {
            $dsctDetalle = 0;
        }




        $sqlClpv .= "update saeclpv set clpv_nom_clpv = '$nombre', 
                        clpv_nom_come = '$nombre_come',
                        clpv_desc_actividades = '$clpv_desc_actividades',
                        clpv_fec_modi = CURRENT_DATE, 
                        clpv_ruc_clpv = '$ruc',
                        clv_con_clpv = '$tipo',
                        clpv_cod_zona = $zona,
                        grpv_cod_grpv = '$grupo',
                        clpv_est_clpv = '$estado',
                        clpv_lim_cred = $limite,
                        clpv_pro_pago = $dia,
                        clpv_dsc_clpv = $dsctGeneral,
                        clpv_dsc_prpg = $dsctDetalle,
                        clpv_cod_cact = '$tipo_cliente',
                        clpv_cod_fpagop = '$pago', 
                        clpv_cod_tprov = '$tipo_prove', 
                        clpv_cod_tpago = '$tipo_pago', 
                        clpv_cod_paisp = '$pais',
                        clpv_etu_clpv = '$contriEspecial',
						clpv_rep_clpv = '$representante',
						clpv_nov_clpv = '$observaciones',
						clpv_ret_sn   = '$clpv_ret_sn'   ,
						clpv_par_rela   = '$clpv_par_rela'   ,
						clpv_tec_sn   = '$clpv_tec_sn'   ,
                        clpv_ubi_long = '$clpv_ubi_long',
                        clpv_ubi_lati = '$clpv_ubi_lati',
						clpv_cod_mone = $clpv_cod_mone,
                        clpv_cod_uniq = '$codigo_unico', 
                        clpv_cod_cuen = '$cod_cuenta_in',

                        clpv_cod_char ='$clpv_cod_char',

                        clpv_facebook_clpv = '$facebook_cli',
                        clpv_insta_clpv ='$insta_cli',
                        ident_propi_clpv    = '$identif_propie',
                        fechnaci_propi_clpv = '$fech_nac_prop',
                        pagina_web_clpv     = '$pagina_web_cli',
                        aniver_empr_clpv    = '$aniversario_empr', 
                        atencion_ofi_clpv   = '$atencion_tn_clie', 
                        horarios_aten_clpv  = '$horarios_cli', 
                        empresa_trans_clpv  = '$empr_trans', 
                        tip_entrega_clpv    = '$tipo_entreg_clie', 
                        resp_flete_clpv     = '$respon_flete', 
                        tip_tienda_clpv     = '$tip_tiend', 
                        direc_llegada       = '$direcc_llega_clie',
                        clpv_notas_clpv     ='$notas_cli',
                        cond_vent_clpv      ='$condicion_vnt',
                        tip_fac_clpv        ='$tip_fact_cli',
                        ruta_visit_clpv     ='$ruta_vsta_cli',
                        clpv_obs_clpv       ='$clpv_obs_clpv'
                        where  clpv_cod_empr = $idempresa and
                        clpv_cod_clpv = $cliente";
        $oIfx->QueryT($sqlClpv);

        /*//selecciona sucursal del clpv
        $sql_sucu = "select clpv_cod_sucu from saeclpv where clpv_cod_clpv =  $cliente and clpv_cod_empr = $idempresa";
        $clpv_cod_sucu = consulta_string($sql_sucu, 'clpv_cod_sucu', $oIfx, $idsucursal);

        // telefono
        $sql_telf = "select count(*) as cont from saetlcp where tlcp_cod_empr = $idempresa and tlcp_cod_clpv = $cliente";
        $cont_telf = consulta_string($sql_telf, 'cont', $oIfx, 0);
        if ($cont_telf > 0) {
            $sql_tel = "update saetlcp set tlcp_tlf_tlcp = '$telefono' where tlcp_cod_clpv = $cliente and tlcp_tlf_tlcp = '$telf_op'";
            //$oReturn->alert($sql_tel);
        } else {
            $sql_tel = "insert into saetlcp(tlcp_cod_empr, tlcp_cod_sucu, tlcp_cod_clpv, tlcp_tlf_tlcp, tlcp_tip_ticp )
                                 values($idempresa,     $clpv_cod_sucu,   $cliente,  '$telefono' , 'T')";
        }
        $oIfx->QueryT($sql_tel);

        // direccion
        $sql_dire = "select count(*) as cont from saedire where dire_cod_empr = $idempresa and dire_cod_clpv = $cliente";
        $cont_dire = consulta_string($sql_dire, 'cont', $oIfx, 0);
        if ($cont_dire > 0) {
            $sql_dir = "update saedire set dire_dir_dire = '$direccion' where dire_cod_clpv = $cliente and dire_dir_dire = '$dire_op'";
            //$oReturn->alert($sql_dir);
        } else {
            $sql_dir = "insert into saedire(dire_cod_empr, dire_cod_sucu, dire_cod_clpv, dire_dir_dire )
                                      values($idempresa,   $clpv_cod_sucu, $cliente, '$direccion')";
        }
        $oIfx->QueryT($sql_dir);

        //correo
        $sql = "select count(*) as cont from saeemai where
				emai_cod_empr = $idempresa and
				emai_cod_clpv = $cliente ";
        $cont_correo = acento_func(consulta_string($sql, 'cont', $oIfx, 0));

        if ($cont_correo > 0) {
            $sql_ema = "update saeemai set emai_ema_emai = '$emai_ema_emai' where emai_cod_clpv = $cliente and emai_cod_empr = $idempresa and emai_ema_emai = '$mail_op'";
            //$oReturn->alert($sql_ema);
        } else {
            $sql_ema = "insert into saeemai (emai_cod_empr,  emai_cod_sucu,  emai_cod_clpv, emai_ema_emai)
                                    values ($idempresa,  $clpv_cod_sucu, $cliente, '$emai_ema_emai'  ) ";
        }
        $oIfx->QueryT($sql_ema);*/

        // ========================================
        //  ACTUALIZAR / INSERTAR CORREO CONTACTO
        // ========================================

        if (!empty($correo_contacto)) {

        //Verificar si ya existe ese correo exacto
        $sqlCheck = "
            select count(*) as cant
            from saeemai
            where emai_cod_empr = $idempresa
            and emai_cod_clpv = $cliente
            and emai_ema_emai = '$correo_contacto'
        ";
        $existeCorreo = consulta_string($sqlCheck, 'cant', $oIfx, 0);

        if ($existeCorreo == 0) {

            //El cliente ya tiene un registro de email
            $sqlBuscar = "
                select emai_cod_emai
                from saeemai
                where emai_cod_empr = $idempresa
                and emai_cod_clpv = $cliente
                limit 1
            ";
            $idCorreo = consulta_string($sqlBuscar, 'emai_cod_emai', $oIfx, 0);

            if ($idCorreo > 0) {
                // reemplazar el correo y dejar tipo 1
                $sqlUpdateCorreo = "
                    update saeemai
                    set 
                        emai_ema_emai = '$correo_contacto',
                        emai_cod_tiem = 1
                    where emai_cod_emai = $idCorreo
                ";
                $oIfx->QueryT($sqlUpdateCorreo);

            } else {
                //INSERT registrar correo nuevo con tipo = 1
                $sqlInsertCorreo = "
                    insert into saeemai(
                        emai_cod_empr,
                        emai_cod_sucu,
                        emai_cod_clpv,
                        emai_ema_emai,
                        emai_cod_tiem,
                        emai_cash_op
                    ) values(
                        $idempresa,
                        $idsucursal,
                        $cliente,
                        '$correo_contacto',
                        1,
                        'N'
                    )
                ";
                $oIfx->QueryT($sqlInsertCorreo);
            }
        }
    }

        $oReturn->alert('Actualizado Correctamente..');
        $oIfx->QueryT('COMMIT WORK;');
        $oReturn->script("consultarReporteCliente();");
        $oReturn->script("seleccionaItem('$cliente');");
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        //$oReturn->assign("ctrl_clie","value",1);
    }

    return $oReturn;
}

function editarCash($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //Variables de Sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $idvendedor = $_SESSION['U_VENDEDOR'];

    //Variables de Formulario
    $cuenta = $aForm['cuenta'];
    $banco = $aForm['banco'];
    $tipoCuenta = $aForm['tipoCuenta'];
    $cliente = $aForm['codigoCliente'];
    $identificacion_sf = $aForm['identificacion_sf'];

    //Lectura Sucia
    //////////////

    try {
        //Commit
        $oIfx->QueryT('BEGIN WORK;');


        $sqlClpv .= "update saeclpv set clpv_tip_ctab = '$tipoCuenta',
					clpv_cod_banc = '$banco',
					clpv_num_ctab = '$cuenta', 
                    clpv_ruc_tran = '$identificacion_sf'
					where  clpv_cod_empr = $idempresa and
					clpv_cod_clpv = $cliente";
        $oIfx->QueryT($sqlClpv);

        $oReturn->alert('Actualizado Correctamente..');
        $oIfx->QueryT('COMMIT WORK;');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        //$oReturn->assign("ctrl_clie","value",1);
    }

    return $oReturn;
}

function guardarPlantilla($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $user_web = $_SESSION['U_ID'];
    $aDataGrid = $_SESSION['aDataGirdCuentaAplicada'];

    //variables del formulario
    $codigoCliente = $aForm['codigoCliente'];
    $idPlantilla = $aForm['idPlantilla'];
    $codigoPlantilla = $aForm['codigoPlantilla'];
    $nombrePlantilla = $aForm['nombrePlantilla'];
    $detallePlantilla = $aForm['detallePlantilla'];
    $cuentaAplicada = $aForm['cuentaAplicada'];
    $creditoBienes = $aForm['creditoBienes'];
    $creditoServicios = $aForm['creditoServicios'];
    $retencionBienes = $aForm['retencionBienes'];
    $retencionServicios = $aForm['retencionServicios'];
    $retencionIvaBienes = $aForm['retencionIvaBienes'];
    $retencionIvaServicios = $aForm['retencionIvaServicios'];

    $fechaServer = date("Y/m/d H:i:s");

    if (!empty($codigoCliente)) {

        try {
            // commit
            $oCon->QueryT('BEGIN;');

            if (empty($idPlantilla)) {
                $sqlInsert = "insert into comercial.plantilla_clpv (id_empresa, id_sucursal, id_clpv, codigo,
							nombre, detalle, cuenta_aplicada, credito_bienes, credito_servicios, 
							rete_fuente_bienes, rete_fuente_servicios, rete_iva_bienes, rete_iva_servicios,
							estado, user_web, fecha_server)
							values($idempresa, $idsucursal, $codigoCliente, '$codigoPlantilla',
							'$nombrePlantilla', '$detallePlantilla', '$cuentaAplicada', '$creditoBienes', '$creditoServicios',
							'$retencionBienes', '$retencionServicios', '$retencionIvaBienes', '$retencionIvaServicios',
							'A', $user_web, '$fechaServer')";
                $oCon->QueryT($sqlInsert);

                //sql max 
                $sql = "select max(id) as id from comercial.plantilla_clpv
						where id_empresa = $idempresa and
						id_sucursal = $idsucursal and
						id_clpv = $codigoCliente and
						codigo = '$codigoPlantilla'";
                $idPlantilla = consulta_string($sql, 'id', $oCon, 0);
            } else {
                $sqlUpdate = "update comercial.plantilla_clpv set 
								codigo = '$codigoPlantilla',
								nombre = '$nombrePlantilla',
								detalle = '$detallePlantilla',
								cuenta_aplicada = '$cuentaAplicada',
								credito_bienes = '$creditoBienes',
								credito_servicios = '$creditoServicios',
								rete_fuente_bienes = '$retencionBienes',
								rete_fuente_servicios = '$retencionServicios',
								rete_iva_bienes = '$retencionIvaBienes',
								rete_iva_servicios = '$retencionIvaServicios',
								estado = 'A'
								where id_empresa = $idempresa and
								id_clpv = $codigoCliente and
								id = $idPlantilla";
                $oCon->QueryT($sqlUpdate);

                //delete from plantillas
                $sqlDel = "delete from comercial.plantilla_ccosn where id_clpv = $codigoCliente and id_plantilla = $idPlantilla and id_empresa = $idempresa";
                $oCon->QueryT($sqlDel);
            }

            if (count($aDataGrid) > 0) {
                foreach ($aDataGrid as $aValues) {
                    $aux = 0;
                    foreach ($aValues as $aVal) {
                        if ($aux == 0) {
                            $idDet = $aVal;
                        } elseif ($aux == 1) {
                            $cuentaC = $aVal;
                        } elseif ($aux == 2) {
                            $centtoC = $aVal;
                        } elseif ($aux == 3) {
                            $porcentajeC = $aVal;

                            $sqlDet = "insert into comercial.plantilla_ccosn(id_empresa, id_sucursal, id_clpv, id_plantilla, cuenta, centro_costos, porcentaje, estado)
										values($idempresa, $idsucursal, $codigoCliente, $idPlantilla, '$cuentaC', '$centtoC', '$porcentajeC', 'A')";
                            $oCon->QueryT($sqlDet);
                        }
                        $aux++;
                    }
                }
            }

            $oReturn->assign("idPlantilla", "value", '');
            $oReturn->assign("codigoPlantilla", "value", '');
            $oReturn->assign("nombrePlantilla", "value", '');
            $oReturn->assign("detallePlantilla", "value", '');
            $oReturn->assign("cuentaAplicada", "value", '');
            $oReturn->assign("creditoBienes", "value", '');
            $oReturn->assign("creditoServicios", "value", '');
            $oReturn->assign("retencionBienes", "value", '');
            $oReturn->assign("retencionServicios", "value", '');
            $oReturn->assign("retencionIvaBienes", "value", '');
            $oReturn->assign("retencionIvaServicios", "value", '');

            $oCon->QueryT('COMMIT;');
            $oReturn->alert('Procesado Correctamente...!');
            $oReturn->script('xajax_reportePlantillas(xajax.getFormValues(\'form1\'))');
        } catch (Exception $e) {
            // rollback
            $oCon->QueryT('ROLLBACK;');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert('Por favor seleccione Cliente para continuar...!');
    }

    return $oReturn;
}

function reportePlantillas($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {
        //variables de session
        unset($_SESSION['aDataGirdCuentaAplicada']);
        $idempresa = $_SESSION['U_EMPRESA'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        if (empty($codigoCliente)) {
            $codigoCliente = 0;
        }

        //query clpv
        $sqlClpv = "select id, id_empresa, id_sucursal, id_clpv, codigo,
					nombre, detalle, cuenta_aplicada, credito_bienes, credito_servicios, 
					rete_fuente_bienes, rete_fuente_servicios, rete_iva_bienes, rete_iva_servicios,
					estado, user_web, fecha_server
					from comercial.plantilla_clpv
                    where id_clpv = '$codigoCliente' and
                    id_empresa = $idempresa";
        if ($oCon->Query($sqlClpv)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml .= '<table class="table table-bordered table-hover table-striped table-condensed" style="width: 98%; margin-top: 20px;">';
                $sHtml .= '<tr>';
                $sHtml .= '<td colspan="11" align="center" class="bg-primary">REPORTE PLANTILLAS</td>';
                $sHtml .= '</tr>';
                $sHtml .= '<tr class="info">';
                $sHtml .= '<td>Codigo</td>';
                $sHtml .= '<td>Nombre</td>';
                $sHtml .= '<td>Detalle</td>';
                $sHtml .= '<td>Cta. Aplicada</td>';
                $sHtml .= '<td>Credito Bienes</td>';
                $sHtml .= '<td>Credito Servicios</td>';
                $sHtml .= '<td>Rete. Fuente Bienes</td>';
                $sHtml .= '<td>Rete. Fuente Servicios</td>';
                $sHtml .= '<td>Rete. Iva Bienes</td>';
                $sHtml .= '<td>Rete. Iva Servicios</td>';
                $sHtml .= '<td>Editar</td>';
                $sHtml .= '</tr>';
                $i = 1;
                do {
                    $id = $oCon->f('id');
                    $codigo = $oCon->f('codigo');
                    $nombre = $oCon->f('nombre');
                    $detalle = $oCon->f('detalle');
                    $cuenta_aplicada = $oCon->f('cuenta_aplicada');
                    $credito_bienes = $oCon->f('credito_bienes');
                    $credito_servicios = $oCon->f('credito_servicios');
                    $rete_fuente_bienes = $oCon->f('rete_fuente_bienes');
                    $rete_fuente_servicios = $oCon->f('rete_fuente_servicios');
                    $rete_iva_bienes = $oCon->f('rete_iva_bienes');
                    $rete_iva_servicios = $oCon->f('rete_iva_servicios');
                    $estado = $oCon->f('estado');

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>' . $codigo . '</td>';
                    $sHtml .= '<td>' . $nombre . '</td>';
                    $sHtml .= '<td>' . $detalle . '</td>';
                    $sHtml .= '<td><a href="#" onclick="detalleCentroCostos(' . $id . ')">' . $cuenta_aplicada . '</a></td>';
                    $sHtml .= '<td>' . $credito_bienes . '</td>';
                    $sHtml .= '<td>' . $credito_servicios . '</td>';
                    $sHtml .= '<td>' . $rete_fuente_bienes . '</td>';
                    $sHtml .= '<td>' . $rete_fuente_servicios . '</td>';
                    $sHtml .= '<td>' . $rete_iva_bienes . '</td>';
                    $sHtml .= '<td>' . $rete_iva_servicios . '</td>';
                    $sHtml .= '<td align="center">
									<div class="btn btn-warning btn-sm" onclick="editarPlantilla(' . $id . ', \'' . $codigo . '\', \'' . $nombre . '\',
																							\'' . $detalle . '\',\'' . $cuenta_aplicada . '\',\'' . $credito_bienes . '\',
																							\'' . $credito_servicios . '\',\'' . $rete_fuente_bienes . '\',\'' . $rete_fuente_servicios . '\',
																							\'' . $rete_iva_bienes . '\',\'' . $rete_iva_servicios . '\',\'' . $estado . '\',);">
										<span class="glyphicon glyphicon-pencil"></span>
									</div>
								</td>';
                    $sHtml .= '</tr>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '</table>';

        $oReturn->assign("divReportePlantilla", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function cuentaAplicada($aForm = '', $id = 0)
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];

    $cuentaAplicada = $aForm['cuentaAplicada'];

    $ifu->AgregarCampoTexto('cuentaContable', 'Cuenta Contable|left', false, $cuentaAplicada, 150, 100);
    $ifu->AgregarComandoAlEscribir('cuentaContable', 'ventanaCuentasContables(event, 4);');

    $ifu->AgregarCampoListaSQL('centroCostos', 'C.Costos|left', "select ccosn_cod_ccosn,  ccosn_nom_ccosn
														from saeccosn where
														ccosn_cod_empr = $idempresa and
														ccosn_mov_ccosn = 1 order by 2", false, 170, 150);

    $ifu->AgregarCampoNumerico('porcentaje', 'Porcentaje|left', false, '', 50, 9);
    $ifu->AgregarComandoAlEscribir('porcentaje', 'validarPorcentaje(this);');

    $sHtml = '<div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel">PARAMETROS CUENTAS CONTABLES</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">
							<tr>
								<td>' . $ifu->ObjetoHtmlLBL('cuentaContable') . '</td>
                                <td>' . $ifu->ObjetoHtml('cuentaContable') . '</td>
								<td>' . $ifu->ObjetoHtmlLBL('centroCostos') . '</td>
                                <td>' . $ifu->ObjetoHtml('centroCostos') . '</td>
								<td>' . $ifu->ObjetoHtmlLBL('porcentaje') . '</td>
                                <td>' . $ifu->ObjetoHtml('porcentaje') . '</td>
								<td>
                                    <div class="btn btn-success btn-sm" onclick="agregarCentroCostos();">
                                        <span class="glyphicon glyphicon-plus-sign"></span>
                                    </div>
                                </td>
							</tr>
                        </table>

                        <div id="divGridCentroCostos" class="table-responsive" style="margin-top:20px;"></div>
						<div id="divGridCentroCostosTotal" class="table-responsive" style="margin-top:10px;"></div>
                    </div>
                    <div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Procesar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>';

    $oReturn->assign('miModal', 'innerHTML', $sHtml);
    $oReturn->assign('cuentaContable', 'focus()', '');
    $oReturn->script('total_grid();');

    if (!empty($id)) {
        $oReturn->script('xajax_detalleCentroCostos(xajax.getFormValues(\'form1\'), ' . $id . ')');
    }

    return $oReturn;
}

function agrega_modifica_grid($nTipo = 0, $codigo_prod = '', $aForm = '', $cant_update = '', $detalle_update = '', $id = 0)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $idbodega = $_SESSION['U_BODE_COD_BODE_'];
    $aDataGrid = $_SESSION['aDataGirdCuentaAplicada'];
    $aLabelGrid = $_SESSION['aLabelGridCuentaAplicada'];

    $count_session = count($aDataGrid);
    if ($count_session > 0) {
        $totalPorcentaje = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 3) {
                    $totalPorcentaje += $aVal;
                }
                $aux++;
            }
        }
    }

    if ($nTipo == 0) {
        //variables formulario
        $cuentaContable = $aForm['cuentaContable'];
        $centroCostos = $aForm['centroCostos'];
        $porcentaje = $aForm['porcentaje'];

        $id = count($aDataGrid);
    } elseif ($nTipo == 1) {
        // actualizar
        $cuentaContable = $aDataGrid[$id]['Cuenta'];
        $centroCostos = $aDataGrid[$id]['Centro Costos'];
        $porcentaje = $aDataGrid[$id]['Porcentaje'];
    }

    $granTotal = $totalPorcentaje + $porcentaje;
    if ($granTotal  <= 100) {

        $aDataGrid[$id][$aLabelGrid[0]] = $id;
        $aDataGrid[$id][$aLabelGrid[1]] = $cuentaContable;
        $aDataGrid[$id][$aLabelGrid[2]] = $centroCostos;
        $aDataGrid[$id][$aLabelGrid[3]] = $porcentaje;
        $aDataGrid[$id][$aLabelGrid[4]] = '<div align="center">
												<div class="btn btn-danger btn-sm" onclick="elimina_detalle(' . $id . ');">
													<span class="glyphicon glyphicon-remove"></span>
												</div>
											</div>';

        $_SESSION['aDataGirdCuentaAplicada'] = $aDataGrid;
        $sHtml = mostrar_grid();

        $oReturn->assign("divGridCentroCostos", "innerHTML", $sHtml);
        $oReturn->assign("cuentaContable", "value", "");
        $oReturn->assign("centroCostos", "value", "");
        $oReturn->assign("porcentaje", "value", "");
        $oReturn->script('total_grid();');
    } else {
        $oReturn->alert('El total de porcentaje no puede ser mayor al 100%');
    }

    return $oReturn;
}

function mostrar_grid()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];
    $aLabelGrid = $_SESSION['aLabelGridCuentaAplicada'];
    $aDataGrid = $_SESSION['aDataGirdCuentaAplicada'];
    $iDataGrid = count($aDataGrid);

    if ($iDataGrid > 0) {
        $cont = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0)
                    $aDatos[$cont][$aLabelGrid[$aux]] = $cont + 1;
                elseif ($aux == 1) {
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                } elseif ($aux == 2) {
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                } elseif ($aux == 3) {
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                } elseif ($aux == 4)
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                else
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                $aux++;
            }
            $cont++;
        }
        return genera_grid($aDatos, $aLabelGrid, 'Reporte', 90);
    }
}

function elimina_detalle($id = null)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $aLabelGrid = $_SESSION['aLabelGridCuentaAplicada'];
    $aDataGrid = $_SESSION['aDataGirdCuentaAplicada'];

    $contador = count($aDataGrid);
    $i = 0;

    if ($contador > 1) {

        unset($aDataGrid[$id]);
        $aDataGrid = array_values($aDataGrid);
        $cont = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0)
                    $aDatos[$cont][$aLabelGrid[$aux]] = $cont;
                elseif ($aux == 4)
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
                                                            <div class="btn btn-danger btn-sm" onclick="elimina_detalle(' . $cont . ');">
																<span class="glyphicon glyphicon-remove"></span>
															</div>
                                                        </div>';
                else
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                $aux++;
            }
            $cont++;
        }
        $_SESSION['aDataGirdCuentaAplicada'] = $aDatos;

        $sHtml = mostrar_grid();
    } else {
        unset($aDataGrid[0]);
        $_SESSION['aDataGirdCuentaAplicada'] = $aDatos;
        $sHtml = "";
        $sHtml = $mostrar_prueba_grid;
    }

    $oReturn->assign("divGridCentroCostos", "innerHTML", $sHtml);
    $oReturn->script('total_grid();');
    return $oReturn;
}

function genera_grid($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $ccos = null, $color = null, $aAccion = null, $Totales = null, $aOrden = null)
{

    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    unset($arrayaDataGridVisible);
    $arrayaDataGridVisible[0] = 'S';
    $arrayaDataGridVisible[1] = 'S';
    $arrayaDataGridVisible[2] = 'S';
    $arrayaDataGridVisible[3] = 'S';
    $arrayaDataGridVisible[4] = 'S';

    if (is_array($aData) && is_array($aLabel)) {
        $iLabel = count($aLabel);
        $iData = count($aData);
        $sClass = 'on';
        $sHtml = '';
        $sHtml .= '<form id="DataGrid">';
        $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin:0px;">';
        $sHtml .= '<tr class="info">';

        for ($i = 0; $i < $iLabel; $i++) {
            $sLabel = explode('|', $aLabel[$i]);
            if ($sLabel[1] == '') {

                $aDataVisible = $arrayaDataGridVisible[$i];
                if ($aDataVisible == 'S') {
                    $aDataVisible = '';
                } else {
                    $aDataVisible = 'none;';
                }

                $sHtml .= '<td align="center" style="display: ' . $aDataVisible . '">' . $sLabel[0] . '</th>';
            } else {
                if ($sLabel[1] == $aOrden[0]) {
                    if ($aOrden[1] == 'ASC') {
                        $sLabel[1] .= '|DESC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_down.png" align="absmiddle" />';
                    } else {
                        $sLabel[1] .= '|ASC';
                        $sImg = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico_up.png" align="absmiddle" />';
                    }
                } else {
                    $sImg = '';
                    $sLabel[1] .= '|ASC';
                }

                $sHtml .= '<th onClick="xajax_' . $sLabel[2] . '(xajax.getFormValues(\'form1\'),\'' . $sLabel[1] . '\')"
                                style="cursor: hand !important; cursor: pointer !important;" >' . $sLabel[0] . ' ';
                $sHtml .= $sImg;
                $sHtml .= '</td>';
            }
        }
        $sHtml .= '</tr>';

        // Genera Filas de Grid
        //rsort($aData, 0);
        for ($i = 0; $i < $iData; $i++) {
            $sHtml .= '<tr class="warning">';
            for ($j = 0; $j < $iLabel; $j++) {
                $campo = $aData[$i][$aLabel[$j]];

                $aDataVisible = $arrayaDataGridVisible[$j];
                if ($aDataVisible == 'S') {
                    $aDataVisible = '';
                } else {
                    $aDataVisible = 'none;';
                }

                if (is_numeric($campo) && $j != 1) {
                    $campo = number_format($campo, 2, '.', '');
                    $sHtml .= '<td align="right" style="display: ' . $aDataVisible . '">' . $campo . '</td>';
                } else {
                    $sHtml .= '<td align="left" style="display: ' . $aDataVisible . '">' . $campo . '</td>';
                }
            } //fin for

            $sHtml .= '</tr>';
        }

        //Totales
        $sHtml .= '<tr>';
        if (is_array($Totales)) {
            for ($i = 0; $i < $iLabel; $i++) {
                if ($i == 0)
                    $sHtml .= '<th class="total_reporte">Totales</th>';
                else {
                    if ($Totales[$i] == '')
                        if ($Totales[$i] == '0.00')
                            $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                        else
                            $sHtml .= '<th align="right"></th>';
                    else
                        $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                }
            }
        }

        $sHtml .= '</tr></table>';
        $sHtml .= '</form>';
        $sHtml .= '</fieldset>';
    }
    return $sHtml;
}

function total_grid($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    //varibales de sesion
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];
    $aLabelGrid = $_SESSION['aLabelGridCuentaAplicada'];
    $aDataGrid = $_SESSION['aDataGirdCuentaAplicada'];
    $contdata = count($aDataGrid);


    if ($contdata > 0) {
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            $totalPorcentaje = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 3) {
                    $totalPorcentaje += $aVal;
                }
                $aux++;
            }
        }

        $fu->AgregarCampoNumerico('totalPorcentaje', 'Total|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('totalPorcentaje', 'this.blur()');

        $fu->cCampos["totalPorcentaje"]->xValor = $totalPorcentaje;

        $sHtml .= '<table class="table table-striped table-hover table-bordered table-condensed" style="margin-top: 10px; width: 98%" align="right">
					<tr>
						<td  class="iniciativa"  bgcolor="#EBF0FA" height="24">TOTAL:</td>
						<td  bgcolor="#EBEBEB" class="fecha_grande" align="right">%</td>
						<td  bgcolor="#EBEBEB" class="fecha_grande" align="right">' . $fu->ObjetoHtml('con_iva') . '</td>
					</rt>';
        $sHtml .= '</table>';
    } else {
        $sHtml = "";
    }

    $oReturn->assign("divGridCentroCostosTotal", "innerHTML", $sHtml);

    return $oReturn;
}

function detalleCentroCostos($aForm = '', $id = 0)
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    try {
        //variables de session
        unset($_SESSION['aDataGirdCuentaAplicada']);
        $aLabelGrid = $_SESSION['aLabelGridCuentaAplicada'];
        $idempresa = $_SESSION['U_EMPRESA'];

        //variables del formulario
        $codigoCliente = $aForm['codigoCliente'];

        if (empty($codigoCliente)) {
            $codigoCliente = 0;
        }

        //query clpv
        $sqlClpv = "select cuenta, centro_costos, porcentaje, estado
					from comercial.plantilla_ccosn
                    where id_clpv = $codigoCliente and
                    id_empresa = $idempresa and
					id_plantilla = $id";
        if ($oCon->Query($sqlClpv)) {
            if ($oCon->NumFilas() > 0) {
                $i = 1;
                do {
                    $id = $oCon->f('id');
                    $cuenta = $oCon->f('cuenta');
                    $centro_costos = $oCon->f('centro_costos');
                    $porcentaje = $oCon->f('porcentaje');
                    $estado = $oCon->f('estado');

                    $aDataGrid[$i][$aLabelGrid[0]] = $i;
                    $aDataGrid[$i][$aLabelGrid[1]] = $cuenta;
                    $aDataGrid[$i][$aLabelGrid[2]] = $centro_costos;
                    $aDataGrid[$i][$aLabelGrid[3]] = $porcentaje;
                    $aDataGrid[$i][$aLabelGrid[4]] = '<div align="center">
															<div class="btn btn-danger btn-sm" onclick="elimina_detalle(' . $i . ');">
																<span class="glyphicon glyphicon-remove"></span>
															</div>
														</div>';

                    $i++;
                    $_SESSION['aDataGirdCuentaAplicada'] = $aDataGrid;
                    $sHtml = mostrar_grid();
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $oReturn->assign("divGridCentroCostos", "innerHTML", $sHtml);
        $oReturn->script('total_grid();');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
function lista_boostrap($oIfx, $sql, $campo_defecto, $campo_id, $campo_nom)
{
    $optionEmpr = '';
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $empr_cod_empr = $oIfx->f($campo_id);
                $empr_nom_empr = htmlentities($oIfx->f($campo_nom));

                $selectedEmpr = '';
                if ($empr_cod_empr == $campo_defecto) {
                    $selectedEmpr = 'selected';
                }

                $optionEmpr .= '<option value="' . $empr_cod_empr . '" ' . $selectedEmpr . '>' . $empr_nom_empr . '</option>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    return $optionEmpr;
}
// FOOTER MAP
function FooterMap($id_contrato, $opcion)
{
    $oReturn = new xajaxResponse();

    $footer_map = '';
    if ($opcion == 1) {
        // GENERAL        
        $footer_map = '<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                       <button type="button" class="btn btn-primary" onclick="guardar_mapa_contr(' . $id_contrato . ');">Procesar</button>';
    } else {
        $footer_map = '<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                       <button type="button" class="btn btn-primary" onclick="guardar_mapa();">Procesar</button>';
    }

    $oReturn->assign("footer_map", "innerHTML", $footer_map);
    return $oReturn;
}


/*function agrega_modifica_gridAdj($nTipo = 0,  $aForm = '', $id = '', $total_fact = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGirdAdj'];

    $aLabelGrid = array('Id', 'Titulo', 'Archivo', 'Eliminar');

    $archivo = substr($aForm['archivo'], 3);
    $titulo  = $aForm['titulo'];

    //GUARDA LOS DATOS DEL DETALLE
    $cont = count($aDataGrid);

    $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
    $aDataGrid[$cont][$aLabelGrid[1]] = $titulo;
    $aDataGrid[$cont][$aLabelGrid[2]] = $archivo;
    $aDataGrid[$cont][$aLabelGrid[3]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
												title = "Presione aqui para Eliminar"
												style="cursor: hand !important; cursor: pointer !important;"
												onclick="javascript:xajax_elimina_detalleAdj(' . $cont . ');"
												alt="Eliminar"
												align="bottom" />';
    $_SESSION['aDataGirdAdj'] = $aDataGrid;
    $sHtml = mostrar_gridAdj();
    $oReturn->assign("gridArchivos", "innerHTML", $sHtml);

    return $oReturn;
}*/

//-----------------------------------------------------------------------------------------
//INICIO FUNCIONES DE LA UAFE Y DOCUMENTOS
//-----------------------------------------------------------------------------------------

function valorLogicoActivado($valor)
{
    $normalizado = strtolower(trim((string) $valor));

    return in_array($normalizado, ['t', 'true', '1', 's', 'si', 'y'], true);
}

function usaValidacionUAFE($idempresa, $oCon)
{
    $sqlUafe = "
        SELECT emmpr_uafe_cprov
        FROM saeempr
        WHERE empr_cod_empr = $idempresa
    ";

    $valor = consulta_string($sqlUafe, 'emmpr_uafe_cprov', $oCon, 'f');

    return valorLogicoActivado($valor);
}

function calcularEstadoDocumentoUafe($estadoBd, $fechaVencimiento)
{
    $estadoBase = $estadoBd ?: 'PE';

    if ($estadoBase !== 'AC') {
        return 'PE';
    }

    if (empty($fechaVencimiento)) {
        return 'PE';
    }

    $hoy = date('Y-m-d');
    $fechaVencimiento = substr($fechaVencimiento, 0, 10);

    return ($hoy > $fechaVencimiento) ? 'VE' : 'AC';
}

function obtenerFechaVencimientoUafe($idempresa, $id_clpv, $oCon)
{
    $sqlV = "
        SELECT tprov_venc_uafe
        FROM saetprov
        WHERE tprov_cod_empr = $idempresa
          AND tprov_cod_tprov = (
                SELECT clpv_cod_tprov
                FROM saeclpv
                WHERE clpv_cod_clpv = $id_clpv
                  AND clpv_cod_empr = $idempresa
          );
    ";

    $fecha = consulta_string($sqlV, 'tprov_venc_uafe', $oCon, '');

    return ($fecha !== '') ? substr($fecha, 0, 10) : '';
}

function proveedorCumpleUafe($idempresa, $id_clpv, $oCon)
{
    if (!usaValidacionUAFE($idempresa, $oCon)) {
        return true;
    }

    if (!$id_clpv) {
        return false;
    }

    $sql = "
        SELECT
            u.id,
            COALESCE(a.estado, 'PE') AS estado_adj,
            a.fecha_vencimiento_uafe
        FROM comercial.archivos_uafe u
        LEFT JOIN comercial.adjuntos_clpv a
            ON a.id_archivo_uafe = u.id
            AND a.id_clpv = $id_clpv
            AND a.id_empresa = $idempresa
            AND a.estado <> 'AN'
        WHERE u.empr_cod_empr = $idempresa
          AND u.estado = 'AC'
    ";

    if (!$oCon->Query($sql) || $oCon->NumFilas() <= 0) {
        return true;
    }

    $todosActivos = true;

    do {
        $estadoCalculado = calcularEstadoDocumentoUafe($oCon->f('estado_adj'), $oCon->f('fecha_vencimiento_uafe'));

        if ($estadoCalculado !== 'AC') {
            $todosActivos = false;
            break;
        }
    } while ($oCon->SiguienteRegistro());

    return $todosActivos;
}

function marcarAdjuntosUafeVencidos($idempresa, $id_clpv, $oCon)
{
    $sql = "
        UPDATE comercial.adjuntos_clpv
        SET estado = 'PE'
        WHERE id_empresa = $idempresa
          AND id_clpv = $id_clpv
          AND id_archivo_uafe IS NOT NULL
          AND estado = 'AC'
          AND fecha_entrega IS NOT NULL
          AND fecha_entrega::date < CURRENT_DATE
    ";

    $oCon->Query($sql);
}

function registrarCambioUafeTemporal($id_clpv, $id_uafe, $estado)
{
    if (!isset($_SESSION['uafeCambios'])) {
        $_SESSION['uafeCambios'] = [];
    }

    if (!isset($_SESSION['uafeCambios'][$id_clpv])) {
        $_SESSION['uafeCambios'][$id_clpv] = [];
    }

    $_SESSION['uafeCambios'][$id_clpv][$id_uafe] = $estado;
}

function aplicarCambiosUafePendientes($idempresa, $idsucursal, $id_clpv, $oCon)
{
    if (!usaValidacionUAFE($idempresa, $oCon)) {
        return;
    }

    $cambios = isset($_SESSION['uafeCambios'][$id_clpv]) ? $_SESSION['uafeCambios'][$id_clpv] : [];

    if (empty($cambios)) {
        return;
    }

    $fechaVencimientoNueva = obtenerFechaVencimientoUafe($idempresa, $id_clpv, $oCon);
    $fechaVencSql          = $fechaVencimientoNueva !== '' ? "'{$fechaVencimientoNueva}'" : 'NULL';
    $periodoSql            = $fechaVencimientoNueva !== '' ? intval(substr($fechaVencimientoNueva, 0, 4)) : 'NULL';

    foreach ($cambios as $id_uafe => $estado) {
        $estado = ($estado === 'AC') ? 'AC' : 'PE';
        $fecha  = ($estado === 'AC') ? "NOW()" : "NULL";

        $id_uafe = intval($id_uafe);

        $sqlExiste = "
            SELECT id
            FROM comercial.adjuntos_clpv
            WHERE id_clpv = $id_clpv
              AND id_archivo_uafe = $id_uafe
              AND id_empresa = $idempresa
              AND id_sucursal = $idsucursal
            LIMIT 1;
        ";

        $id_adj = 0;
        if ($oCon->QueryT($sqlExiste) && $oCon->NumFilas() > 0) {
            $id_adj = intval($oCon->f('id'));
        }

        if ($id_adj > 0) {
            $sqlUpd = "
                UPDATE comercial.adjuntos_clpv
                SET estado = '$estado',
                    fecha_entrega = $fecha
            WHERE id = $id_adj;
            ";

            if ($estado === 'AC') {
                $sqlUpd = "
                    UPDATE comercial.adjuntos_clpv
                    SET estado = '$estado',
                        fecha_entrega = $fecha,
                        fecha_vencimiento_uafe = $fechaVencSql,
                        periodo_uafe = $periodoSql
                    WHERE id = $id_adj;
                ";
            }

            $oCon->QueryT($sqlUpd);
            continue;
        }

        $sqlIns = "
            INSERT INTO comercial.adjuntos_clpv
            (id_empresa, id_sucursal, id_clpv, id_archivo_uafe, titulo, estado, fecha_entrega, fecha_vencimiento_uafe, periodo_uafe)
            VALUES (
                $idempresa,
                $idsucursal,
                $id_clpv,
                $id_uafe,
                (SELECT titulo FROM comercial.archivos_uafe WHERE id = $id_uafe),
                '$estado',
                $fecha,
                " . ($estado === 'AC' ? $fechaVencSql : 'NULL') . ",
                " . ($estado === 'AC' ? $periodoSql : 'NULL') . "
            );
        ";

        $oCon->QueryT($sqlIns);
    }
}

function sincronizarEstadoProveedorPorUafe($idempresa, $id_clpv, $bloquear)
{
    global $DSN, $DSN_Ifx;

    $estadoDestino = $bloquear ? 'P' : 'A';

    $sql = "
        UPDATE saeclpv
        SET clpv_est_clpv = '$estadoDestino'
        WHERE clpv_cod_empr = $idempresa
          AND clpv_cod_clpv = $id_clpv
          AND clpv_est_clpv IN ('A','P')
          AND clpv_est_clpv <> '$estadoDestino'
    ";

    if (!empty($DSN_Ifx)) {
        $oIfx = new Dbo();
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();
        $oIfx->Query($sql);
    }

    if (!empty($DSN)) {
        $oCon = new Dbo();
        $oCon->DSN = $DSN;
        $oCon->Conectar();
        $oCon->Query($sql);
    }
}

function obtenerEstadoProveedorInformix($idempresa, $id_clpv)
{
    global $DSN, $DSN_Ifx;

    if (empty($DSN_Ifx) || !$idempresa || !$id_clpv) {
        if (empty($DSN)) {
            return '';
        }
    }

    $sqlEstado = "
            SELECT clpv_est_clpv
            FROM saeclpv
            WHERE clpv_cod_empr = $idempresa
              AND clpv_cod_clpv = $id_clpv
            LIMIT 1
        ";

    $estadoDb = '';
    if (!empty($DSN_Ifx)) {
        $oIfx = new Dbo();
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();
        $estadoDb = consulta_string_func($sqlEstado, 'clpv_est_clpv', $oIfx, '');
    }

    if ($estadoDb === '' && !empty($DSN)) {
        $oCon = new Dbo();
        $oCon->DSN = $DSN;
        $oCon->Conectar();
        $estadoDb = consulta_string_func($sqlEstado, 'clpv_est_clpv', $oCon, '');
    }

    if ($estadoDb === 'A') return 'AC';
    if ($estadoDb === 'S') return 'SU';
    if ($estadoDb === 'P') return 'PE';

    return '';
}

function debeBloquearEstadoPorUafe($idempresa, $id_clpv, $oCon)
{
    if (!usaValidacionUAFE($idempresa, $oCon)) {
        return false;
    }

    if (!$id_clpv) {
        return true;
    }

    return !proveedorCumpleUafe($idempresa, $id_clpv, $oCon);
}

function validarEstadoUAFEProveedor($id_clpv)
{
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];

    // Conexión
    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $usaUafe = usaValidacionUAFE($idempresa, $oCon);

    if (!$usaUafe) {
        $oReturn->script("habilitarEstadoProveedor(false);");
        $oReturn->script("toggleNotificarUafe(false);");
        return $oReturn;
    }

    $cumple   = proveedorCumpleUafe($idempresa, $id_clpv, $oCon);
    $bloquear = !$cumple;

    $oReturn->script("habilitarEstadoProveedor(" . ($bloquear ? 'true' : 'false') . ");");
    $oReturn->script("toggleNotificarUafe(true);");

    return $oReturn;
}

function obtenerConteoProveedoresUafeVencida()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;

    $oReturn = new xajaxResponse();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $conteo = 0;

    try {
        $sql = "
            SELECT COUNT(DISTINCT a.id_clpv) AS proveedores_a_recalcular
            FROM saeclpv p
            JOIN comercial.adjuntos_clpv a
              ON a.id_clpv    = p.clpv_cod_clpv
             AND a.id_empresa = p.clpv_cod_empr
            WHERE
                p.clpv_est_clpv = 'A'
                AND a.id_archivo_uafe IS NOT NULL
                AND a.estado = 'AC'
                AND a.fecha_vencimiento_uafe IS NOT NULL
                AND CURRENT_DATE > a.fecha_vencimiento_uafe;
        ";

        if ($oCon->Query($sql) && $oCon->NumFilas() > 0) {
            $conteo = intval($oCon->f('proveedores_a_recalcular'));
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
        return $oReturn;
    }

    if ($conteo > 0) {
        $oReturn->script("mostrarModalRecalculoUafe(" . $conteo . ", " . $conteo . ");");
    }

    return $oReturn;
}

function recalcularEstadosUafeProveedores()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;

    $oReturn = new xajaxResponse();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $conteo = 0;

    try {
        $sqlConteo = "
            SELECT COUNT(DISTINCT a.id_clpv) AS proveedores_a_recalcular
            FROM saeclpv p
            JOIN comercial.adjuntos_clpv a
              ON a.id_clpv    = p.clpv_cod_clpv
             AND a.id_empresa = p.clpv_cod_empr
            WHERE
                p.clpv_est_clpv = 'A'
                AND a.id_archivo_uafe IS NOT NULL
                AND a.estado = 'AC'
                AND a.fecha_vencimiento_uafe IS NOT NULL
                AND CURRENT_DATE > a.fecha_vencimiento_uafe;
        ";

        if ($oCon->Query($sqlConteo) && $oCon->NumFilas() > 0) {
            $conteo = intval($oCon->f('proveedores_a_recalcular'));
        }

        if ($conteo > 0) {
            $oCon->QueryT('BEGIN;');

            $sqlUpdate = "
                UPDATE saeclpv p
                SET clpv_est_clpv = 'P'
                WHERE p.clpv_est_clpv = 'A'
                  AND EXISTS (
                        SELECT 1
                        FROM comercial.adjuntos_clpv a
                        WHERE a.id_clpv    = p.clpv_cod_clpv
                          AND a.id_empresa = p.clpv_cod_empr
                          AND a.id_archivo_uafe IS NOT NULL
                          AND a.estado = 'AC'
                          AND a.fecha_vencimiento_uafe IS NOT NULL
                          AND CURRENT_DATE > a.fecha_vencimiento_uafe
                  );
            ";

            $oCon->QueryT($sqlUpdate);
            $oCon->QueryT('COMMIT;');
        }
    } catch (Exception $e) {
        $oCon->QueryT('ROLLBACK;');
        $oReturn->alert($e->getMessage());
        return $oReturn;
    }

    $oReturn->script("mostrarResultadoRecalculoUafe(" . $conteo . ");");

    return $oReturn;
}


function agrega_modifica_gridAdj($nTipo = 0,  $aForm = '', $id = '', $total_fact = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oReturn = new xajaxResponse();

    // GRID TEMPORAL
    $aDataGrid = isset($_SESSION['aDataGirdAdj']) ? $_SESSION['aDataGirdAdj'] : array();
    $aLabelGrid = array('Id', 'Titulo', 'Archivo', 'Eliminar');

    // DATOS DEL FORM
    $tipo      = $aForm['tipo_adj'];          // 0 = NORMAL, 1 = UAFE
    $archivo   = substr($aForm['archivo'], 3);
    $titulo    = trim($aForm['titulo']);
    $id_uafe   = intval($aForm['id_archivo_uafe']);

    // TITULO SEGÚN TIPO
    if ($tipo == "0") {
        // NORMAL
        $titulo_final = $titulo;
        $id_uafe_final = 0;
    } else {
        // UAFE: leer catálogos
        $sqlDoc = "SELECT titulo FROM comercial.archivos_uafe WHERE id = $id_uafe";
        $oCnx->Query($sqlDoc);
        $titulo_uafe = ($oCnx->NumFilas() > 0) ? $oCnx->f('titulo') : 'Documento UAFE';

        $titulo_final  = $titulo_uafe;
        $id_uafe_final = $id_uafe;
    }

    // GUARDAR EN EL GRID
    $cont = count($aDataGrid);

    $aDataGrid[$cont]['Id']      = floatval($cont);
    $aDataGrid[$cont]['Titulo']  = $titulo_final;
    $aDataGrid[$cont]['Archivo'] = $archivo;

    // METADATA QUE NO SE DEBE MOSTRAR EN GRID
    $aDataGrid[$cont]['_extra'] = array(
        'tipo_adj' => $tipo,
        'id_archivo_uafe' => $id_uafe_final
    );

    // BOTÓN ELIMINAR
    $aDataGrid[$cont]['Eliminar'] = '
            <div align="center">
                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                     style="cursor:pointer;"
                     onclick="xajax_elimina_detalleAdj(' . $cont . ')"
                     alt="Eliminar">
             </div>';

    $_SESSION['aDataGirdAdj'] = $aDataGrid;

    // REPINTAR GRID
    $sHtml = mostrar_gridAdj();
    $oReturn->assign("gridArchivos", "innerHTML", $sHtml);

    return $oReturn;
}

function mostrar_gridAdj()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $aDataGrid = isset($_SESSION['aDataGirdAdj']) ? $_SESSION['aDataGirdAdj'] : array();
    $aLabelGrid = array('Id', 'Titulo', 'Archivo', 'Eliminar');

    $aDatos = array();
    $cont = 0;

    foreach ($aDataGrid as $row) {

        // Limpiar metadata
        if (isset($row['_extra'])) {
            unset($row['_extra']);
        }

        // Construir fila limpia
        $aDatos[$cont]['Id']      = $cont + 1;
        $aDatos[$cont]['Titulo']  = '<div align="left">' . $row['Titulo'] . '</div>';
        $aDatos[$cont]['Archivo'] = '<div align="left">' . $row['Archivo'] . '</div>';
        $aDatos[$cont]['Eliminar'] = $row['Eliminar'];

        $cont++;
    }

    return genera_grid($aDatos, $aLabelGrid, 'Adjuntos', 98, null, null);
}

/*function guardarAdjuntos($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];
    $aDataGirdAdj = $_SESSION['aDataGirdAdj'];

    //variables del formulario
    $cliente = $aForm['codigoCliente'];
    $fechaServer = date("Y-m-d H:i:s");

    if (count($aDataGirdAdj) > 0) {
        try {

            $oCon->QueryT('BEGIN;');

            foreach ($aDataGirdAdj as $aValues) {
                $aux = 0;
                foreach ($aValues as $aVal) {
                    if ($aux == 0) {
                        $idAdj = $aVal;
                    } elseif ($aux == 1) {
                        $titulo = $aVal;
                    } elseif ($aux == 2) {
                        $adjunto = $aVal;

                        $sql = "insert into comercial.adjuntos_clpv (id_empresa, id_sucursal, id_clpv, titulo, ruta, estado, fecha_server, user_web)
											values($idempresa, $idsucursal, $cliente, '$titulo', '$adjunto', 'A', '$fechaServer', $usuario_web)";
                        echo $sql;
                        exit;
                        $oCon->QueryT($sql);
                        //$oReturn->alert($sql);
                    }
                    $aux++;
                } //fin foreach
            } //fin foreach


            $oCon->QueryT('COMMIT;');

            $oReturn->alert('Procesado Correctamente...!');
            $oReturn->script('consultarAdjuntos();');
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
            $oReturn->alert($e->getMessage());
        }
    } else {
        $oReturn->alert('No existen Registros para procesar..!');
    }

    return $oReturn;
}*/

function guardarAdjuntos($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    // VARIABLES DE SESIÓN
    $idempresa   = $_SESSION['U_EMPRESA'];
    $idsucursal  = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];

    // GRID TEMPORAL
    $aDataGrid   = isset($_SESSION['aDataGirdAdj']) ? $_SESSION['aDataGirdAdj'] : [];

    // PROVEEDOR
    $id_clpv     = intval($aForm['codigoCliente']);

    $fechaServer = date("Y-m-d H:i:s");

    if (count($aDataGrid) <= 0) {
        $oReturn->alert('No existen registros para procesar.');
        return $oReturn;
    }

    try {

        $oCon->QueryT("BEGIN;");

        foreach ($aDataGrid as $row) {

            $titulo     = $row['Titulo'];
            $archivo    = $row['Archivo'];
            $tipo_adj   = $row['_extra']['tipo_adj'];       // 0=NORMAL / 1=UAFE
            $id_uafe    = intval($row['_extra']['id_archivo_uafe']);

            // NO USAR ESTADO NI FECHA DE ENTREGA EN ESTE PROCESO
            // Regla: subir archivo NO cambia estado, NO pone fecha_entrega
            // El check es el único que cambia estado y fecha.
            $fec_ent = "NULL";

            // =============================================================
            //  ADJUNTOS NORMALES
            // =============================================================
            if ($tipo_adj == "0") {

                $sql = "
                    INSERT INTO comercial.adjuntos_clpv
                    (id_empresa, id_sucursal, id_clpv, id_archivo_uafe, titulo, estado, ruta, user_web, fecha_server, fecha_entrega)
                    VALUES ($idempresa, $idsucursal, $id_clpv, NULL, '$titulo', 'PE', '$archivo', $usuario_web, '$fechaServer', NULL);
                ";

                $oCon->QueryT($sql);
                continue;
            }

            // =============================================================
            //  ADJUNTOS UAFE
            // =============================================================

            //BUSCAR SI YA EXISTE PARA ESTE PROVEEDOR Y ESTE DOCUMENTO UAFE
            $id_adj = 0;

            $sqlBusca = "
                SELECT id
                FROM comercial.adjuntos_clpv
                WHERE id_clpv = $id_clpv
                AND id_archivo_uafe = $id_uafe
                AND id_empresa = $idempresa
                AND id_sucursal = $idsucursal
                LIMIT 1;
            ";

            if ($oCon->Query($sqlBusca) && $oCon->NumFilas() > 0) {
                $id_adj = intval($oCon->f('id'));
            }

            // =============================================================
            // SI EXISTE → ACTUALIZAR SOLO RUTA
            // =============================================================
            if ($id_adj > 0) {

                $sqlUpdate = "
                    UPDATE comercial.adjuntos_clpv
                    SET ruta = '$archivo',
                        user_web = $usuario_web,
                        fecha_server = '$fechaServer'
                    WHERE id = $id_adj;
                ";

                $oCon->QueryT($sqlUpdate);
                continue;
            }

            // =============================================================
            // SI NO EXISTE → INSERTAR NUEVO
            // Estado debe SER SIEMPRE 'PE' al inicio.
            // =============================================================
            $sqlInsert = "
                INSERT INTO comercial.adjuntos_clpv
                (id_empresa, id_sucursal, id_clpv, id_archivo_uafe, titulo, ruta, estado, user_web, fecha_server, fecha_entrega)
                VALUES ($idempresa, $idsucursal, $id_clpv, $id_uafe, '$titulo', '$archivo', 'PE', $usuario_web, '$fechaServer', NULL);
            ";

            $oCon->QueryT($sqlInsert);
        }

        //FIN DE TRANSACCIÓN
        $oCon->QueryT("COMMIT;");

        //LIMPIAR GRID TEMPORAL
        $_SESSION['aDataGirdAdj'] = [];

        // LIMPIAR TABLA VISUAL
        $tablaVacia = mostrar_gridAdj();
        $oReturn->assign("gridArchivos", "innerHTML", $tablaVacia);

        //REFRESCAR TABLAS
        $oReturn->script("consultarAdjuntos();");
        $oReturn->script("consultarAdjuntosUafe();");

        $oReturn->alert("Adjuntos procesados correctamente.");

    } catch (Exception $e) {

        $oCon->QueryT("ROLLBACK;");
        $oReturn->alert("Error: " . $e->getMessage());
    }

    return $oReturn;
}

function cambiarEstadoUafe($id_uafe, $id_clpv, $valor) {

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];

    if (!usaValidacionUAFE($idempresa, $oCon)) {
        return new xajaxResponse();
    }

    $estado = ($valor == 1) ? 'AC' : 'PE';

    registrarCambioUafeTemporal($id_clpv, $id_uafe, $estado);

    $oReturn = new xajaxResponse();
    $oReturn->script("console.log('Cambios UAFE pendientes de guardar para el proveedor $id_clpv');");

    return $oReturn;
}

function elimina_detalleAdj($id)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oReturn = new xajaxResponse();

    // Siempre tomar el índice como FLOAT
    // porque 1.00 se recibe como string flotante
    $idx = intval(floatval($id) - 1);   // ESTA ES LA MAGIA

    if ($idx < 0) $idx = 0;

    // Recuperar grid
    $aDataGrid = isset($_SESSION['aDataGirdAdj']) ? $_SESSION['aDataGirdAdj'] : [];

    if (isset($aDataGrid[$idx])) {

        unset($aDataGrid[$idx]);

        $aDataGrid = array_values($aDataGrid);

        $_SESSION['aDataGirdAdj'] = $aDataGrid;
    }

    // Redibujar
    $sHtml = mostrar_gridAdj();
    $oReturn->assign("gridArchivos", "innerHTML", $sHtml);

    return $oReturn;
}

function consultarAdjuntos($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    // Si no usas Informix aquí, puedes comentar estas 3 líneas
    // $oIfx = new Dbo;
    // $oIfx->DSN = $DSN_Ifx;
    // $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables de formulario
    $cliente  = $aForm['codigoCliente'];

    try {

        $sHtml  = '';
        $sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 98%;">';
        $sHtml .= '<tr>';
        $sHtml .= '<td colspan="4"><h5>ADJUNTOS <small>Reporte Información</small></h5></td>';
        $sHtml .= '</tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td>No.</td>';
        $sHtml .= '<td>Título</td>';
        $sHtml .= '<td>Adjunto</td>';
        $sHtml .= '<td></td>';
        $sHtml .= '</tr>';

        // SOLO DOCUMENTOS NORMALES (NO UAFE)
        $sql = "
            SELECT id, titulo, ruta
            FROM comercial.adjuntos_clpv
            WHERE id_clpv   = $cliente 
              AND id_empresa = $idempresa
              AND (id_archivo_uafe = 0 OR id_archivo_uafe IS NULL)
            ORDER BY id DESC;
        ";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $i = 1;
                do {
                    $id     = $oCon->f('id');
                    $titulo = $oCon->f('titulo');
                    $ruta   = $oCon->f('ruta');

                    // Normaliza la ruta (por si viene con ../)
                    $ruta = str_replace('../', '', $ruta);
                    $ruta_file = "../../Include/Clases/Formulario/Plugins/reloj/$ruta";

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>' . $i++ . '</td>';
                    $sHtml .= '<td>' . $titulo . '</td>';
                    $sHtml .= ' <td>
                                    <a href="' . $ruta_file . '" target="_blank" class="btn btn-link btn-sm">
                                        Ver archivo
                                    </a>
                                </td>';

                
                    $sHtml .= ' <td style="text-align:center; vertical-align:middle;">
                                        <div class="btn btn-danger btn-sm" onclick="javascript:eliminar_adj(' . $id . ');">
                                            <span class="glyphicon glyphicon-remove"></span>
                                        </div>
                                </td>';
                    $sHtml .= '</tr>';
                } while ($oCon->SiguienteRegistro());
            } else {
                // Sin registros
                $sHtml .= '<tr>';
                $sHtml .= '<td colspan="4" align="center"><em>No existen adjuntos registrados.</em></td>';
                $sHtml .= '</tr>';
            }
        }

        $oCon->Free();

        $sHtml .= '</table>';

        $oReturn->assign('divReporteAdjuntos', 'innerHTML', $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function consultarAdjuntosUafe($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;
    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $id_clpv   = intval($aForm['codigoCliente']);

    if ($id_clpv <= 0) {
        $oReturn->alert("Seleccione un proveedor válido.");
        return $oReturn;
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $usaUafe = usaValidacionUAFE($idempresa, $oCon);

    if (!$usaUafe) {
        $oReturn->assign("divReporteAdjuntosUafe", "innerHTML", "");
        $oReturn->script("habilitarEstadoProveedor(false);");

        $estadoVisual = obtenerEstadoProveedorInformix($idempresa, $id_clpv);
        if ($estadoVisual === '' && empty($id_clpv)) {
            $estadoVisual = 'AC';
        }

        if ($estadoVisual !== '') {
            $oReturn->script("editar('$estadoVisual');");
        }

        return $oReturn;
    }

    // -----------------------------------------------------------------------
    // INICIO TABLA DE DOCUMENTOS UAFE
    // -----------------------------------------------------------------------
    $sql = "
        SELECT 
            u.id AS id_uafe,
            u.titulo,
            a.id AS id_adj,
            a.ruta AS ruta_adj,
            COALESCE(a.estado, 'PE') AS estado_adj,
            a.fecha_entrega,
            a.fecha_vencimiento_uafe
        FROM comercial.archivos_uafe u
        LEFT JOIN comercial.adjuntos_clpv a
            ON a.id_archivo_uafe = u.id
            AND a.id_clpv = $id_clpv
            AND a.id_empresa = $idempresa
            AND a.estado <> 'AN'
        WHERE u.empr_cod_empr = $idempresa
          AND u.estado = 'AC'
        ORDER BY u.id;
    ";
    $html  = "<table class='table table-bordered table-hover' style='width:98%;'>";
    // INICIO CABECERA DE LA TABLA
    $html .= "
        <tr>
            <td colspan='8' style='padding:8px;'>
                <div style='display:flex; justify-content:space-between; align-items:center;'>
                    <h5 style='margin:0;'>ADJUNTOS <small>Documentos UAFE</small></h5>

                    <button type='button' class='btn btn-primary btn-sm' onclick='guardarAdjuntosUAFE();'>
                        <span class='glyphicon glyphicon-floppy-disk'></span> Guardar
                    </button>
                </div>
            </td>
        </tr>
    ";

    $html .= "
        <tr class='bg-primary text-white'>
            <th>No.</th>
            <th>Documento</th>
            <th>Archivo</th>
            <th>Fecha Entrega</th>
            <th>Fecha Vencimiento</th>
            <th>Estado</th>
            <th>Cumplimiento</th>
            <th>Acción</th>
        </tr>
    ";
    // FIN CABECERA DE LA TABLA


    if ($oCon->Query($sql) && $oCon->NumFilas() > 0) {

        $i = 1;
        $hoy = date("Y-m-d");

        do {
            $id_uafe  = $oCon->f('id_uafe');
            $id_adj   = $oCon->f('id_adj');
            $titulo   = $oCon->f('titulo');
            $estado   = $oCon->f('estado_adj');
            $rutaAdj  = trim($oCon->f('ruta_adj'));
            $fecEnt   = $oCon->f('fecha_entrega');
            $fecVenc  = $oCon->f('fecha_vencimiento_uafe');

            if (isset($_SESSION['uafeCambios'][$id_clpv][$id_uafe])) {
                $estado = $_SESSION['uafeCambios'][$id_clpv][$id_uafe];
            }

            // Solo fecha
            if ($fecEnt != "" && $fecEnt != NULL) {
                $fecEnt = substr($fecEnt, 0, 10);
            } else {
                $fecEnt = "---";
            }

            $estadoCalculado = calcularEstadoDocumentoUafe($estado, $fecVenc);

            $estadoMostrar = $estadoCalculado;

            if (isset($_SESSION['uafeCambios'][$id_clpv][$id_uafe]) && $estadoCalculado !== $oCon->f('estado_adj')) {
                $estadoMostrar .= ' (sin guardar)';
            }

            // CHECK
            $checked = ($estadoCalculado == 'AC') ? "checked" : "";

            // Archivo
            if ($rutaAdj != "") {
                $rutaAdj = str_replace('../', '', $rutaAdj);
                $ruta = "../../Include/Clases/Formulario/Plugins/reloj/$rutaAdj";
                $link = "<a href='$ruta' target='_blank'>Ver archivo</a>";
            } else {
                $link = "---";
            }

            // Botón eliminar
            $btnEliminar = "
                <button type='button' class='btn btn-danger btn-sm'
                    onclick=\"eliminarArchivoUAFE($id_uafe, $id_clpv, $id_adj);\">
                    <span class='glyphicon glyphicon-remove'></span>
                </button>
            ";

            $html .= "
                <tr>
                    <td>$i</td>
                    <td>$titulo</td>
                    <td>$link</td>
                    <td>$fecEnt</td>
                    <td>" . ($fecVenc ? substr($fecVenc, 0, 10) : '---') . "</td>
                    <td>$estadoMostrar</td>
                    <td align='center'>
                        <input type='checkbox' $checked
                            onclick=\"cambiarEstadoUafe($id_uafe, $id_clpv, this.checked)\">
                    </td>
                    <td align='center'>$btnEliminar</td>
                </tr>
            ";

            $i++;

        } while ($oCon->SiguienteRegistro());
    }

    $html .= "</table>";

    // -----------------------------------------------------------------------
    // FIN TABLA DE DOCUMENTOS UAFE
    // -----------------------------------------------------------------------

    $oReturn->assign("divReporteAdjuntosUafe", "innerHTML", $html);

    $cumple   = proveedorCumpleUafe($idempresa, $id_clpv, $oCon);
    $bloquear = !$cumple;

    $oReturn->script("habilitarEstadoProveedor(" . ($bloquear ? 'true' : 'false') . ");");

    return $oReturn;
}

function guardarAdjuntosUAFE($id_clpv)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;
    $oReturn = new xajaxResponse();

    $idempresa  = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $usaUafe = usaValidacionUAFE($idempresa, $oCon);

    if (!$usaUafe) {
        return $oReturn;
    }

    $cumpliaAntes = proveedorCumpleUafe($idempresa, $id_clpv, $oCon);

    try {

        $oCon->QueryT("BEGIN;");

        aplicarCambiosUafePendientes($idempresa, $idsucursal, $id_clpv, $oCon);

        $oCon->QueryT("COMMIT;");
    } catch (Exception $e) {
        $oCon->QueryT("ROLLBACK;");
        $oReturn->alert("Error: " . $e->getMessage());
        return $oReturn;
    }

    unset($_SESSION['uafeCambios'][$id_clpv]);

    $cumpleDespues  = proveedorCumpleUafe($idempresa, $id_clpv, $oCon);
    $bloquearEstado = !$cumpleDespues;

    sincronizarEstadoProveedorPorUafe($idempresa, $id_clpv, $bloquearEstado);

    $oReturn->script("habilitarEstadoProveedor(" . ($bloquearEstado ? 'true' : 'false') . ");");
    $estadoVisual = obtenerEstadoProveedorInformix($idempresa, $id_clpv);
    if ($estadoVisual !== '') {
        $oReturn->script("editar('$estadoVisual');");
    }

    if ($usaUafe) {
        if ($cumpleDespues) {
            $mensaje = array(
                'icon'  => 'success',
                'title' => 'Documentos UAFE entregados',
                'text'  => 'Se cumplen todos los documentos solicitados. El proveedor pasará a estado Activo.',
            );
        } elseif ($cumpliaAntes && !$cumpleDespues) {
            $mensaje = array(
                'icon'  => 'warning',
                'title' => 'Documentos UAFE actualizados',
                'text'  => 'Este proveedor tiene documentos pendientes o vencidos.',
            );
        } else {
            $mensaje = array(
                'icon'  => 'warning',
                'title' => 'Documentos incompletos',
                'text'  => 'Faltan documentos UAFE por cumplir.',
            );
        }

        $oReturn->script("Swal.fire({
            icon: '{$mensaje['icon']}',
            title: '{$mensaje['title']}',
            text: '{$mensaje['text']}',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3085d6'
        });");
    }

    $oReturn->script("consultarAdjuntosUafe();");

    return $oReturn;
}

function eliminarArchivoUAFE($id_uafe, $id_clpv, $id_adj)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN;
    $oReturn = new xajaxResponse();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    try {

        $oCon->QueryT("BEGIN;");

        // SOLO eliminar el archivo (ruta). NO tocar estado. NO tocar fecha.
        $sql = "
            UPDATE comercial.adjuntos_clpv
            SET ruta = NULL
            WHERE id = $id_adj
              AND id_archivo_uafe = $id_uafe
              AND id_clpv = $id_clpv;
        ";

        $oCon->QueryT($sql);
        $oCon->QueryT("COMMIT;");

        $oReturn->script("
            Swal.fire({
                position: 'center',
                icon: 'warning',
                title: 'Archivo eliminado',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            });
        ");

        $oReturn->script("consultarAdjuntosUafe();");

    } catch (Exception $e) {
        $oCon->QueryT("ROLLBACK;");
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function eliminar_adj($id = 0, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN; // SOLO Postgres, no Informix
    $oReturn = new xajaxResponse();

    // Conexión
    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    try {

        // Inicia transacción
        $oCon->QueryT("BEGIN;");

        // ELIMINACIÓN SOLO PARA DOCUMENTOS NORMALES
        // (los documentos UAFE tienen su propia función)
        $sql = "
            DELETE FROM comercial.adjuntos_clpv
            WHERE id = $id
              AND (id_archivo_uafe = 0 OR id_archivo_uafe IS NULL)
        ";

        $oCon->QueryT($sql);

        // Commit
        $oCon->QueryT("COMMIT;");

        // Mensaje
        $oReturn->script("
            Swal.fire({
                position: 'center',
                icon: 'warning',
                title: 'Registro eliminado',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            });
        ");

        // Refresca tabla de adjuntos normales
        $oReturn->script("consultarAdjuntos();");

    } catch (Exception $e) {

        // rollback
        $oCon->QueryT("ROLLBACK;");

        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

//-----------------------------------------------------------------------------------------
//FIN FUNCIONES DE LA UAFE Y DOCUMENTOS 
//-----------------------------------------------------------------------------------------


function editarDireccion($aForm = '', $id = 0)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $clpv = $aForm['codigoCliente'];
    $idContrato = $aForm['idContrato'];

    try {
        //Direcciones
        $sqlDire = "select dire_dir_dire,
                    dire_cod_tipo, dire_cod_vivi, dire_cod_sect,
                    dire_barr_dire, dire_call1_dire, dire_call2_dire,
                    dire_nume_dire, dire_edif_dire, dire_refe_dire,
                    dire_anti_dire
                    from saedire
                    where dire_cod_clpv = $clpv and
					dire_cod_empr = $idempresa and
					dire_cod_dire = $id";
        //echo $sqlDire;exit;
        if ($oIfx->Query($sqlDire)) {
            if ($oIfx->NumFilas() > 0) {
                $dire_cod_tipo = $oIfx->f('dire_cod_tipo');
                $dire_dir_dire = $oIfx->f('dire_dir_dire');
                $dire_call1_dire = $oIfx->f('dire_call1_dire');
                $dire_call2_dire = $oIfx->f('dire_call2_dire');
                $dire_cod_sect = $oIfx->f('dire_cod_sect');
                $dire_barr_dire = $oIfx->f('dire_barr_dire');
                $dire_edif_dire = $oIfx->f('dire_edif_dire');
                $dire_anti_dire = $oIfx->f('dire_anti_dire');
                $dire_cod_vivi = $oIfx->f('dire_cod_vivi');
                $dire_nume_dire = $oIfx->f('dire_nume_dire');
                $dire_refe_dire = $oIfx->f('dire_refe_dire');
            }
        }
        $oIfx->Free();

        $oReturn->assign("idDireccion", "value", $id);
        $oReturn->assign("tipo_direccion", "value", $dire_cod_tipo);
        $oReturn->assign("tipo_casa", "value", $dire_cod_vivi);
        $oReturn->assign("sectorDire", "value", $dire_cod_sect);
        $oReturn->assign("barrioDire", "value", $dire_barr_dire);
        $oReturn->assign("callePrincipal", "value", $dire_call1_dire);
        $oReturn->assign("numeroDire", "value", $dire_nume_dire);
        $oReturn->assign("calleSecundaria", "value", $dire_call2_dire);
        $oReturn->assign("referenciaDire", "value", $dire_refe_dire);
        $oReturn->assign("edificioDire", "value", $dire_edif_dire);
        $oReturn->assign("antiguedadDire", "value", $dire_anti_dire);
        $oReturn->assign("direccion", "value", $dire_dir_dire);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function autocompletar_infomacion_cliente($tipo_identifiacion = '', $identificacion = '', $consumidor = false)
{

    global $DSN_Ifx, $DSN;
    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();

    try {
        $idempresa = $_SESSION['U_EMPRESA'];
        $idsucursal = $_SESSION['U_SUCURSAL'];
        $S_URL_API_SRI_SN = $_SESSION['S_URL_API_SRI_SN'];
        $S_URL_API_SRI = $_SESSION['S_URL_API_SRI'];
        $S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];
        $usuario_id = $_SESSION['U_ID'];


        $bandera_consulta_externa = true;
        $cod_cliente = "";
        $tipo_ruc_cliente = "";
        $identificacion_cliente = "";
        $nombres_cliente = "";
        $telefono_cliente = "";
        $direccion_cliente = "";
        $correo_cliente = "";


        if ($S_PAIS_API_SRI == '593' && $S_URL_API_SRI_SN == 'S' && $S_URL_API_SRI && $bandera_consulta_externa) {
            //$consultaExterna = new ValidarIdentificacion($S_URL_API_SRI);

            $consultaExterna = new ValidadorCedulaRucEcuador2024($oIfx, $oIfxA, $idempresa, $usuario_id);

            if ($tipo_identifiacion == '02') {
                /** VALIDAR CEDULA */
                $cedula_consulta = $consultaExterna->valida_identificacion_ws_ecuador($identificacion, $tipo_identifiacion);
                if ($cedula_consulta['status']) {
                    /**
                     * identificacion
                     * nombreCompleto
                     */

                    $identificacion_cliente = $cedula_consulta['data']['identificacion'];
                    $nombres_cliente = $cedula_consulta['data']['nombres'];
                } else {
                    $mensaje_error = $consultaExterna->getError();
                }
            } else if ($tipo_identifiacion == '01') {
                /**Validar si el RUC EXISTE*/
                $cedula_consulta = $consultaExterna->valida_identificacion_ws_ecuador($identificacion, $tipo_identifiacion);
                if ($cedula_consulta['status']) {
                    /**
                     * identificacion
                     * nombreCompleto
                     */

                    $identificacion_cliente = $cedula_consulta['data']['identificacion'];
                    $nombres_cliente = $cedula_consulta['data']['nombres'];
                } else {
                    $mensaje_error = $consultaExterna->getError();
                }
            }
        }

        if ($identificacion_cliente && $nombres_cliente) {
            if ($consumidor) {
                $oReturn->assign("ruc_cli", "value", $identificacion_cliente);
                $oReturn->assign("nombre", "value", $nombres_cliente);

                if (!$bandera_consulta_externa) {
                    $oReturn->assign("identificacion", "value", $tipo_ruc_cliente);
                    $oReturn->assign("telefono", "value", $telefono_cliente);
                    $oReturn->assign("direccion", "value", $direccion_cliente);
                    $oReturn->assign("email", "value", $correo_cliente);
                    $oReturn->assign("cliente", "value", $cod_cliente);
                }
            } else {
                $oReturn->assign("ruc_cli", "value", $identificacion_cliente);
                $oReturn->assign("nombre", "value", $nombres_cliente);

                if (!$bandera_consulta_externa) {
                    $oReturn->assign("identificacion", "value", $tipo_ruc_cliente);
                    $oReturn->assign("telefono", "value", $telefono_cliente);
                    $oReturn->assign("direccion", "value", $direccion_cliente);
                    $oReturn->assign("email", "value", $correo_cliente);
                    $oReturn->assign("cliente", "value", $cod_cliente);

                    $oReturn->script("cargar_lista_correo('', '$correo_cliente', $consumidor);");
                    $oReturn->script("cargar_lista_subcliente();");
                }
            }

            //$oReturn->script("limite_credito($cod_cliente);");
            //$oReturn->script("varificar_documentos_vencidos($cod_cliente);");

            //
        } else {

            $oReturn->script("alertSwal('No se encontro informacion, por favor registre al cliente','warning');");
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");


    return $oReturn;
}
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
