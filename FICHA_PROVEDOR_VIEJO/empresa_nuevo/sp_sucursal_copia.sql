create procedure "informix".sp_sucursal_copia( in_empr integer, sucu_orig integer, sucu_dest integer, sucu_nom_dest varchar(255)  )

returning varchar(10) as resp;

define msn varchar(10);
define bode_cta_inv_v  varchar(100);
define bode_cta_cven_v varchar(100);
define bode_cta_vent_v varchar(100); 
define bode_cta_desc_v varchar(100);
define bode_cta_devo_v varchar(100);
define bode_cta_ideb_v varchar(100);
define bode_cta_icre_v varchar(100);
define in_bode integer;

-- COPIA DE ZONA
insert into saezona ( zona_cod_zona, 	zona_cod_empr, 	zona_cod_sucu,		zona_nom_zona )
			select    zona_cod_zona,	zona_cod_empr,	sucu_dest,			zona_nom_zona from saezona where	
					  zona_cod_empr = in_empr and
					  zona_cod_sucu = sucu_orig;
					  
-- COPIA SAETRAN
insert into saetran ( tran_cod_tran, 			 						tran_cod_modu,			tran_cod_empr, 		
					  tran_des_tran ,   		 						trans_tip_tran,			trans_tip_comp,		
					  tran_cod_tret,			 						tran_cod_sucu )
select (trim(tran_cod_tran) ||trim(sucu_dest)) as tran_cod_tran, 		tran_cod_modu,			tran_cod_empr,
(tran_des_tran || ' ' || sucu_nom_dest) as tran_des_tran  ,    			trans_tip_tran,		    trans_tip_comp,		
					  tran_cod_tret,							sucu_dest	from saetran where
					  tran_cod_empr = in_empr and
					  tran_cod_sucu = sucu_orig;

					  
-- COPIA DEFI
insert into saedefi ( defi_cod_modu,									defi_cod_tran , 	defi_cod_empr ,		defi_tip_defi , 
					  defi_can_defi, 									defi_iva_defi , 	defi_cos_defi , 	defi_cost_defi , 
					  defi_cco_defi, 									defi_lot_defi , 	defi_cac_defi , 	defi_dsc_defi , 
					  defi_prc_defi, 									defi_prd_defi , 	defi_otr_defi , 	defi_ped_defi , 
					  defi_ret_defi, 									defi_trs_defi , 	defi_pro_defi , 	defi_ctc_defi , 
					  defi_for_defi, 									defi_tip_comp , 	defi_det_dmov , 	defi_lot_clpv , 
					  defi_lis_prec, 									defi_mul_empr , 	defi_ice_defi , 	defi_cod_trtc , 
					  defi_ord_iniv, 									defi_iva_incl , 	defi_cod_cuen , 	defi_lis_prep , 
					  defi_pro_prov, 									defi_sno_seri , 	defi_can_seri , 	defi_des_prec , 
					  defi_cod_sucu, 									defi_eval_defi, 	defi_ord_trab , 	defi_cie_anti , 
					  defi_ant_movi, 									defi_cod_tidu , 	defi_tip_cons , 	defi_tip_rese , 
					  defi_tom_pre , 									defi_prod_rec , 	defi_barr_si  , 	defi_baj_ing  , 
					  defi_ocul_cos, 									defi_nov_mos  , 	defi_fact_defi, 	defi_mos_bode , 
					  defi_mos_fact, 									defi_cod_libro, 	defi_num_det  , 	defi_tip_roma , 
					  defi_mat_prim, 									defi_cod_retiva, 	defi_cod_crtr , 	defi_ing_xml  , 
					  defi_sin_fact, 									defi_mer_defi )
select defi_cod_modu,  (trim(defi_cod_tran) ||trim(sucu_dest)) as defi_cod_tran,			defi_cod_empr,		defi_tip_defi,
					  defi_can_defi,									defi_iva_defi,		defi_cos_defi,		defi_cost_defi,
					  defi_cco_defi, 									defi_lot_defi , 	defi_cac_defi , 	defi_dsc_defi , 
					  defi_prc_defi, 									defi_prd_defi , 	defi_otr_defi , 	defi_ped_defi , 
					  defi_ret_defi, 									defi_trs_defi , 	defi_pro_defi , 	defi_ctc_defi , 
					  defi_for_defi, 									defi_tip_comp , 	defi_det_dmov , 	defi_lot_clpv , 
					  defi_lis_prec, 									defi_mul_empr , 	defi_ice_defi , 	defi_cod_trtc , 
					  defi_ord_iniv, 									defi_iva_incl , 	defi_cod_cuen , 	defi_lis_prep , 
					  defi_pro_prov, 									defi_sno_seri , 	defi_can_seri , 	defi_des_prec , 
					  sucu_dest,										defi_eval_defi, 	defi_ord_trab , 	defi_cie_anti , 
					  defi_ant_movi, 									defi_cod_tidu , 	defi_tip_cons , 	defi_tip_rese , 
					  defi_tom_pre , 									defi_prod_rec , 	defi_barr_si  , 	defi_baj_ing  , 
					  defi_ocul_cos, 									defi_nov_mos  , 	defi_fact_defi, 	defi_mos_bode , 
					  defi_mos_fact, 									defi_cod_libro, 	defi_num_det  , 	defi_tip_roma , 
					  defi_mat_prim, 									defi_cod_retiva, 	defi_cod_crtr , 	defi_ing_xml  , 
					  defi_sin_fact, 									defi_mer_defi from saedefi where
					  defi_cod_empr = in_empr and
					  defi_cod_sucu = sucu_orig;
					  
-- COPIA BODE
select max(bode_cod_bode) as bode_cod_bode 
into in_bode 
from saebode where bode_cod_empr = in_empr;					  

let in_bode = in_bode +1 ;
 
insert into saebode ( 	bode_nom_bode, 		bode_cta_inv ,			bode_cta_cven,			bode_cta_vent,
						bode_cco_bode,		bode_cta_desc ,			bode_cta_prod ,			bode_cta_devo ,
						bode_cta_ideb,		bode_cta_icre ,			bode_cta_rcre ,			bode_cta_rdeb ,
						bode_cod_ciud,		bode_cod_empr ,			bode_ruc_bode ,			bode_dir_bode ,
						bode_tlf_bode,		bode_res_bode ,			bode_stk_bode ,			bode_act_multi,
						grbo_cod_grbo,		calp_cod_calp ,			bode_sig_bode ,			bode_par_tran ,
						bode_mos_bode,		bode_cod_empl ,			bode_tela_sn  ,			bode_copia_sn,
						bode_tip_cuen,      bode_cod_bode	)		
select  concat(bode_nom_bode,' ',sucu_nom_dest) as bode_nom_bode, 	bode_cta_inv ,			bode_cta_cven,			bode_cta_vent,
						bode_cco_bode,		bode_cta_desc ,			bode_cta_prod ,			bode_cta_devo ,
						bode_cta_ideb,		bode_cta_icre ,			bode_cta_rcre ,			bode_cta_rdeb ,
						bode_cod_ciud,		bode_cod_empr ,			bode_ruc_bode ,			bode_dir_bode ,
						bode_tlf_bode,		bode_res_bode ,			bode_stk_bode ,			bode_act_multi,
						grbo_cod_grbo,		calp_cod_calp ,			bode_sig_bode ,			bode_par_tran ,
						bode_mos_bode,		bode_cod_empl ,			bode_tela_sn  ,			bode_copia_sn,
						bode_tip_cuen,      bode_cod_bode from saebode where
						bode_cod_empr = in_empr;
									
-- COPIA SAESUBO
insert into saesubo ( subo_cod_sucu,		subo_cod_empr , 		subo_cod_bode  )
			select   sucu_dest,				subo_cod_empr,			subo_cod_bode from saesubo where	
					 subo_cod_empr = in_empr and
					 subo_cod_sucu = sucu_orig;
					 
-- COPIA SAEPROD									
insert into saeprod (	
	prod_cod_prod,              prod_cod_empr,                        prod_nom_prod,
	prod_fin_prod,              prod_cod_colr,                        prod_cod_marc,
	prod_cod_tpro,              prod_cod_medi,                        prod_cod_sucu,
	prod_cod_linp,              prod_cod_grpr,                        prod_cod_cate,
	prod_imp_prod,              prod_tip_pro,
	prod_cod_barra,             prod_alt_prov,                        prod_alt_clie,
	prod_des_prod,              prod_nom_ext,                         prod_det_prod,
	prod_lot_sino,              prod_ser_prod,                        prod_cod_aran,
	prod_fob_prod,              prod_sn_noi,                          prod_sn_exe,
	prod_dsc_prod,              prod_uni_caja,                        prod_stock_neg,
	prod_pro_prod,              prod_cod_gtalla,                      prod_cod_talla,
	prod_cod_gcolor,            prod_cod_color,                       prod_nom_refe,
	prod_nom_cole,              prod_nom_garan,						  prod_unid_ped															
 )	
 
select    
	prod_cod_prod,              prod_cod_empr,                        prod_nom_prod,
	prod_fin_prod,              prod_cod_colr,                        prod_cod_marc,
	prod_cod_tpro,              prod_cod_medi,                        sucu_dest,
	prod_cod_linp,              prod_cod_grpr,                        prod_cod_cate,
	prod_imp_prod,              prod_tip_pro,
	prod_cod_barra,             prod_alt_prov,                        prod_alt_clie,
	prod_des_prod,              prod_nom_ext,                         prod_det_prod,
	prod_lot_sino,              prod_ser_prod,                        prod_cod_aran,
	prod_fob_prod,              prod_sn_noi,                          prod_sn_exe,
	prod_dsc_prod,              prod_uni_caja,                        prod_stock_neg,
	prod_pro_prod,              prod_cod_gtalla,                      prod_cod_talla,
	prod_cod_gcolor,            prod_cod_color,                       prod_nom_refe,
	prod_nom_cole,              prod_nom_garan,						  prod_unid_ped			
	from saeprod where
	prod_cod_empr = in_empr and
	prod_cod_sucu = sucu_orig and
	prod_cod_prod not in ( select prod_cod_prod from saeprod where
								prod_cod_empr = in_empr and
								prod_cod_sucu = sucu_dest );

								
-- CUENTAS DE INVENTARIO
select  bode_cta_inv, bode_cta_cven, bode_cta_vent, 
bode_cta_desc, bode_cta_devo, bode_cta_ideb, bode_cta_icre
into bode_cta_inv_v, 	bode_cta_cven_v, 	bode_cta_vent_v, 
bode_cta_desc_v, 		bode_cta_devo_v, 	bode_cta_ideb_v, 	bode_cta_icre_v
from saebode where
bode_cod_empr = in_empr and
bode_cod_bode = bode_dest;

						
-- COPIA  SAEPRBO
insert into saeprbo (	
prbo_cod_prod,     	prbo_can_req,       	prbo_cod_bode,      	prbo_cta_inv ,      									 
prbo_cta_cven,     	prbo_cta_vent,      	prbo_cta_desc,      	prbo_cta_devo,      
prbo_cta_ideb,     	prbo_cta_icre,      	prbo_cod_unid,      	prbo_cod_empr,  
prbo_cod_sucu,     	prbo_est_prod,      	prbo_iva_sino,      	prbo_iva_porc, 
prbo_cos_prod,     	prbo_ice_sino,      	prbo_ice_porc,      	prbo_irbp_sino,    
prbo_val_irbp,		prbo_cre_prod,			prbo_sma_prod,			prbo_smi_prod 
)
select  		
prbo_cod_prod,     	prbo_can_req,       	bode_dest,	      	    bode_cta_inv_v ,      									 
bode_cta_cven_v,    bode_cta_vent_v,      	bode_cta_desc_v,      	bode_cta_devo_v,      
bode_cta_ideb_v,    bode_cta_icre_v,      	prbo_cod_unid,      	prbo_cod_empr,  
sucu_dest,    	    prbo_est_prod,      	prbo_iva_sino,      	prbo_iva_porc, 
prbo_cos_prod,     	prbo_ice_sino,      	prbo_ice_porc,      	prbo_irbp_sino,    
prbo_val_irbp,		prbo_cre_prod,			prbo_sma_prod,			prbo_smi_prod
from saeprbo where
prbo_cod_empr = in_empr and
prbo_cod_sucu = sucu_orig and
prbo_cod_bode = bode_orig and
prbo_cod_prod not in (  select prbo_cod_prod from saeprbo where
								prbo_cod_empr = in_empr and
								prbo_cod_sucu = sucu_dest and
								prbo_cod_bode = bode_dest );

-- COPIA SAEPPR
insert into saeppr (ppr_cod_ppr, 			ppr_cod_prod,		ppr_cod_bode,	
					ppr_cod_empr,			ppr_cod_sucu,		ppr_pre_raun,
					ppr_cod_nomp,			ppr_imp_ppr )	
			select 	ppr_cod_ppr, 			ppr_cod_prod,		ppr_cod_bode,	
					ppr_cod_empr,			sucu_dest,			ppr_pre_raun,
					ppr_cod_nomp,			ppr_imp_ppr		from saeppr where
					ppr_cod_empr = in_empr and
					ppr_cod_sucu = sucu_dest;
								
let msn = 'OK';
return msn;

end procedure
;                                                                                                                 