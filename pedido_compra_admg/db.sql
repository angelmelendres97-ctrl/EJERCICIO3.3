-- Ajustes para manejo de tipos de solicitud en pedidos de compra
ALTER TABLE saepedi
    ADD COLUMN IF NOT EXISTS pedi_tip_sol INTEGER;

ALTER TABLE saepedi
    ADD CONSTRAINT saepedi_tip_sol_fk
        FOREIGN KEY (pedi_cod_empr, pedi_tip_sol)
        REFERENCES saetpro (tpro_cod_empr, tpro_cod_tpro);

-- Gestión de cargos y aprobadores por sucursal
CREATE TABLE IF NOT EXISTS comercial.aprobador_cargo (
    id              SERIAL PRIMARY KEY,
    empresa         INTEGER NOT NULL REFERENCES saeempr (empr_cod_empr),
    sucursal        INTEGER NOT NULL REFERENCES saesucu (sucu_cod_sucu),
    nombre          VARCHAR(150) NOT NULL,
    descripcion     VARCHAR(250),
    estado          CHAR(1) DEFAULT 'S',
    creado_en       TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    CONSTRAINT aprobador_cargo_unq UNIQUE (empresa, sucursal, nombre)
);

CREATE TABLE IF NOT EXISTS comercial.aprobador (
    id              SERIAL PRIMARY KEY,
    empresa         INTEGER NOT NULL REFERENCES saeempr (empr_cod_empr),
    sucursal        INTEGER NOT NULL REFERENCES saesucu (sucu_cod_sucu),
    cargo_id        INTEGER NOT NULL REFERENCES comercial.aprobador_cargo (id),
    nombre          VARCHAR(200) NOT NULL,
    correo          VARCHAR(180),
    telefono        VARCHAR(50),
    estado          CHAR(1) DEFAULT 'S',
    creado_en       TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    CONSTRAINT aprobador_unq UNIQUE (empresa, sucursal, nombre)
);

-- Grupos de aprobadores para organizar firmas por sucursal
CREATE TABLE IF NOT EXISTS comercial.grupo_aprobador (
    id              SERIAL PRIMARY KEY,
    empresa         INTEGER NOT NULL REFERENCES saeempr (empr_cod_empr),
    sucursal        INTEGER NOT NULL REFERENCES saesucu (sucu_cod_sucu),
    nombre          VARCHAR(150) NOT NULL,
    descripcion     VARCHAR(250),
    estado          CHAR(1) DEFAULT 'S',
    creado_en       TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    CONSTRAINT grupo_aprobador_unq UNIQUE (empresa, sucursal, nombre)
);

CREATE TABLE IF NOT EXISTS comercial.grupo_aprobador_det (
    id              SERIAL PRIMARY KEY,
    grupo_id        INTEGER NOT NULL REFERENCES comercial.grupo_aprobador (id) ON DELETE CASCADE,
    aprobador_id    INTEGER NOT NULL REFERENCES comercial.aprobador (id) ON DELETE CASCADE,
    empresa         INTEGER NOT NULL REFERENCES saeempr (empr_cod_empr),
    sucursal        INTEGER NOT NULL REFERENCES saesucu (sucu_cod_sucu),
    estado          CHAR(1) DEFAULT 'S',
    CONSTRAINT grupo_aprobador_det_unq UNIQUE (grupo_id, aprobador_id)
);

-- Aprobadores seleccionados para cada pedido
CREATE TABLE IF NOT EXISTS comercial.aprobador_pedido (
    id                  SERIAL PRIMARY KEY,
    empresa             INTEGER NOT NULL REFERENCES saeempr (empr_cod_empr),
    sucursal            INTEGER NOT NULL REFERENCES saesucu (sucu_cod_sucu),
    pedido              VARCHAR(50) NOT NULL,
    aprobador_id        VARCHAR(50) NOT NULL,
    aprobador_nombre    VARCHAR(255),
    cargo_id            INTEGER REFERENCES comercial.aprobador_cargo (id),
    cargo_nombre        VARCHAR(255),
    enviar              CHAR(1) DEFAULT 'S',
    creado_en           TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    CONSTRAINT aprobador_pedido_unq UNIQUE (empresa, sucursal, pedido, aprobador_id)
);

ALTER TABLE comercial.aprobador_pedido
    ADD COLUMN IF NOT EXISTS cargo_nombre VARCHAR(255);

ALTER TABLE saepedi
    ADD COLUMN IF NOT EXISTS pedi_omit_aprob CHAR(1) DEFAULT 'N';

-- Campos adicionales para manejar códigos y descripciones auxiliares en el detalle
ALTER TABLE saedped
    ADD COLUMN IF NOT EXISTS dped_cod_auxiliar VARCHAR(50),
    ADD COLUMN IF NOT EXISTS dped_desc_auxiliar VARCHAR(255);




-------------------CONSUlTAS PARA SABER ESTRUCTURA DE TABLAS DE PEDIDOS-----------------------


--------Tabla de pedidos de compra (saepedi)--------
INSERT INTO "public"."saepedi" ("pedi_cod_pedi", "pedi_cod_sucu", "pedi_cod_empr", "pedi_cod_empl", "pedi_cod_clpv", "pedi_cod_ftrn", "pedi_cod_usua", "pedi_num_prdo", "pedi_cod_ejer", "pedi_ban_pedi", "pedi_res_pedi", "pedi_det_pedi", "pedi_fec_pedi", "pedi_fec_entr", "pedi_est_pedi", "pedi_are_soli", "pedi_fec_clie", "pedi_pes_esti", "pedi_lug_entr", "pedi_via_pedi", "pedi_iol_pedi", "pedi_ent_pedi", "pedi_cod_pvcl", "pedi_oco_clpv", "pedi_uso_pedi", "pedi_cod_mone", "pedi_cod_pais", "pedi_cod_fpag", "pedi_cod_prtod", "pedi_cod_prtoe", "pedi_des_cons", "pedi_des_prec", "pedi_des_seg", "pedi_ter_pedi", "pedi_flet_pedi", "pedi_seg_pedi", "pedi_tot_pedi", "pedi_fec_aut", "pedi_num_min", "pedi_num_max", "pedi_cons_sino", "pedi_cod_prip", "pedi_cod_estp", "pedi_empl_apro", "pedi_tip_pror", "pedi_num_dui", "pedi_con_tone", "pedi_otr_gast", "pedi_inv_fact", "pedi_cod_embq", "pedi_hor_ini", "pedi_user_web", "pedi_fech_server", "pedi_tipo_pedi", "pedi_est_prof", "pedi_est_fina", "pedi_fec_tecn", "pedi_fec_dep", "pedi_fec_fina", "pedi_carea_pedi", "pedi_atec_pedi", "pedi_adep_pedi", "pedi_afin_pedi", "pedi_alog_pedi", "pedi_casig_pedi", "pedi_fasig_pedi", "pedi_rasig_pedi", "pedi_pre_pedi", "pedi_ganu_pedi", "pedi_fanu_pedi", "pedi_tanu_pedi", "pedi_evpr_pedi", "pedi_adpr_pedi", "pedi_appr_pedi", "pedi_canu_pedi", "pedi_vit1_pedi", "pedi_vit2_pedi", "pedi_user_anu", "pedi_fec_anu", "pedi_det_anu", "pedi_dped_apro", "pedi_dped_fecapr", "pedi_cod_anu", "pedi_num_preimp", "pedi_danu_prof", "pedi_uanu_prof", "pedi_fanu_prof", "pedi_det_fin", "pedi_user_fin", "pedi_fec_fin", "pedi_tip_sol", "pedi_tip_sol") 
VALUES (111, 1, 1, '', 0, 13, 1, 11, 25, '0', 'ADMIN JIREH', 'PRUEBA PROVEEDORES', '2024-11-05', '2024-11-05', '2', 'COMPRAS GENERALES', NULL, NULL, 'PB ', NULL, NULL, NULL, NULL, NULL, 'OFICINA', NULL, NULL, NULL, NULL, NULL, 'PRUEBA PROVEEDORES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-11-05', 'M', 'S', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-11-05 11:04:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-------- tabla de detalle de pedidos de compra (saedped)--------
INSERT INTO "public"."saedped" ("dped_cod_dped", "dped_cod_pedi", "dped_cod_prod", "dped_cod_bode", "dped_cod_sucu", "dped_cod_empr", "dped_num_prdo", "dped_cod_ejer", "dped_cod_ccos", "dped_cod_unid", "dped_can_ped", "dped_can_ent", "dped_can_pen", "dped_prc_dped", "dped_ban_dped", "dped_det_dped", "dped_pre_dped", "dped_cd1_clpv", "dped_cd2_clpv", "dped_cd3_clpv", "dped_mr1_dped", "dped_mr2_dped", "dped_mr3_dped", "dped_pr1_dped", "dped_pr2_dped", "dped_pr3_dped", "dped_chk_dped", "dped_cod_coti", "dped_num_caja", "dped_num_minv", "dped_eje_minv", "dped_prdo_min", "dped_env_dped", "dped_fec_soli", "dped_car_dped", "dped_esp_dped", "dped_num_ord", "dped_cod_clpv", "dped_fec_entr", "dped_hor_soli", "dped_cod_soli", "dped_pro_nume", "dped_cod_fpag", "dped_fac_empr", "dped_des_bode", "dped_sol_empl", "dped_cont_empr", "dped_cod_estp", "dped_desc_dped", "dped_iva_dped", "dped_unid_conv", "dped_por_aran", "dped_vol_dped", "dped_anc_dped", "dped_alt_dped", "dped_grm_dped", "dped_cant_uni_aux", "dped_pre_uni_aux", "dped_cod_lote", "dped_ela_lote", "dped_cad_lote", "dped_costo_dped", "dped_tot_dped", "dped_prod_nom", "dped_tot_modi", "dped_can_modi", "dped_obs_modi", "dped_est_dped", "dped_can_desp", "dped_pen_desp", "dped_tot_desp", "dped_usu_desp", "dped_obs_desp", "dped_cod_admi", "dped_tot_desd", "dped_aut_tecn", "dped_part_pres", "dped_val_pres", "dped_fech_tecn", "dped_raut_tecn", "dped_cod_empl", "dped_fech_ctz", "dped_est_prod", "dped_avt1_dped", "dped_avt2_dped", "dped_adj_dped", "dped_can_apro", "dped_user_anu", "dped_fec_anu") 
VALUES (1, 103, '22RCMAROTOD00001', 1, 1, 1, 10, 25, '1.01.006', 1, '1.00', '0.00', '1.00', '10.000000', '0', 'AROS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10.000000', '10.000000', 'ARO/LLANTA #12 X 4 HUECOS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'archivo_2024-10-24_21-54-42_0.75809900.png', '1.00', NULL, NULL);

-------- tabla de aprobadores (comercial.aprobador)--------
INSERT INTO "comercial"."aprobador" ("id", "empresa", "sucursal", "cargo_id", "nombre", "correo", "telefono", "estado", "creado_en") 
VALUES (1, 1, 1, 1, 'Juan Perez', 'juan@perez', '1234567890', 'A', '2024-10-24 21:54:42');  




---------------------------------

---------Tabla de unidades---------
INSERT INTO public.saeunid
(unid_cod_unid, unid_cod_empr, unid_nom_unid, unid_sigl_unid, unid_can_unid, unid_ueq_unid, unid_tip_unid)
VALUES(1, 1, 'UNIDAD', 'UN', NULL, NULL, NULL);

--------------CORRECCION PK PEDIDO_COMPRA--------
--VALIDACION EXISTENCIA DE CODIGOS DUPLICADOS
SELECT pedi_cod_pedi, COUNT(*) 
FROM public.saepedi
GROUP BY pedi_cod_pedi
HAVING COUNT(*) > 1;

--ELIMINAR CONSTRAINT

ALTER TABLE public.saepedi 
DROP CONSTRAINT "1407_5431";

--CREAR NUEVO CONSTRAINT
ALTER TABLE public.saepedi
ADD CONSTRAINT saepedi_pk PRIMARY KEY (pedi_cod_pedi);