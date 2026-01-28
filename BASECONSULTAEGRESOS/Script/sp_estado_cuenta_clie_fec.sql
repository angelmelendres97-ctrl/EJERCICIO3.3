drop procedure sp_estado_cuenta_clie_fec;
--execute procedure sp_estado_cuenta_clie_fec(1, 9816, '08/26/2019', '08/26/2019', 1, 1, '%', '%', '3', '%', '%')
create procedure "informix".sp_estado_cuenta_clie_fec( in_empr integer,  in_cli integer, in_fecha_ini date, in_fecha_fin date ,
					   in_op integer , in_user integer, in_sucu varchar(5), in_grupo varchar(20),
					   in_zona varchar(5), in_vend varchar(10), in_num_fact varchar(20))

returning  date as fecha_emision,  varchar(100) as tran_cod_tran,  varchar(255) as comprobante,  varchar(100) as factura,  
		date as fecha_vencimiento, varchar(255) as detalle, decimal(18,3) as debito, decimal(18,3) as credito,  varchar(255) as cliente,
		varchar(100) as ruc, integer as clpv_cod_clpv, integer as sucu_cod,  integer as ejer_cod, decimal(18,3) as debito_ext, decimal(18,3) as credito_ext;

define fec_emis date;
define cod_tran varchar(100);
define comprob varchar(255);
define num_fac varchar(100);
define fec_vence date;
define detalle varchar(255);
define deb, cre, deb_ext, cre_ext decimal(18,3);
define cliente varchar(255);
define ruc varchar(100);
define cod_clpv integer;
define msn varchar(10);
define cod_sucu integer;
define cod_ejer integer;

if (in_num_fact <> '%') then
	let in_num_fact = '%'||in_num_fact||'%';
end if

-- borrar TABLA TEMP
delete from tmp_est_cta_clie where
user_web = in_user;

-- CLIENTE SELECCCIONADO
if( in_op==1 ) then

FOREACH 

	SELECT  saedmcc.dmcc_fec_emis, saedmcc.dmcc_cod_tran, saedmcc.dmcc_num_comp,   
			saedmcc.dmcc_num_fac,  saedmcc.dmcc_fec_ven,  saedmcc.dmcc_det_dmcc,   
			saedmcc.dmcc_deb_ml,   saedmcc.dmcc_cre_ml,   saeclpv.clpv_nom_clpv,    
			saeclpv.clpv_ruc_clpv, saeclpv.clpv_cod_clpv, saedmcc.dmcc_cod_sucu, 
			saedmcc.dmcc_cod_ejer, saedmcc.dmcc_deb_mext,   saedmcc.dmcc_cre_mext
	into  fec_emis,  	cod_tran,  	comprob,  
	      num_fac,   	fec_vence, 	detalle,  
	      deb,   		cre,		cliente,
	      ruc , 		cod_clpv,   cod_sucu, 
		  cod_ejer,		deb_ext, 	cre_ext
	FROM saeclpv  ,  saedmcc   WHERE
	( saedmcc.clpv_cod_clpv = saeclpv.clpv_cod_clpv ) and  
	( saeclpv.clpv_cod_empr = saedmcc.dmcc_cod_empr ) and       
	( saedmcc.dmcc_cod_empr = in_empr ) AND  
	( saedmcc.dmcc_fec_emis between  in_fecha_ini  and  in_fecha_fin  ) AND  
	( saeclpv.clpv_clopv_clpv = 'CL'  )  AND
	  saeclpv.clpv_cod_clpv = in_cli  and
	  saedmcc.dmcc_num_fac	||'' like in_num_fact
	  
	order by  saeclpv.clpv_cod_clpv, saedmcc.dmcc_fec_emis, saedmcc.dmcc_num_fac, saedmcc.dmcc_cre_ml
	return fec_emis, cod_tran, comprob ,  
	        num_fac,   	fec_vence, 	detalle ,  
	        deb,   		cre ,		cliente ,
	        ruc , 		cod_clpv, 	cod_sucu, 
			cod_ejer, 	deb_ext, 	cre_ext  WITH RESUME;

end foreach;
end if;


-- TODOS LOS CLIENTES
if( in_op==2 ) then	
	
	FOREACH 

		SELECT  saedmcc.dmcc_fec_emis, saedmcc.dmcc_cod_tran, saedmcc.dmcc_num_comp,   
				saedmcc.dmcc_num_fac,  saedmcc.dmcc_fec_ven,  saedmcc.dmcc_det_dmcc,   
				saedmcc.dmcc_deb_ml,   saedmcc.dmcc_cre_ml,   saeclpv.clpv_nom_clpv,    
				saeclpv.clpv_ruc_clpv, saeclpv.clpv_cod_clpv, saedmcc.dmcc_cod_sucu,  
				saedmcc.dmcc_cod_ejer, saedmcc.dmcc_deb_mext,   saedmcc.dmcc_cre_mext
		into  	fec_emis,  	cod_tran,  	comprob,  
				num_fac,   	fec_vence, 	detalle,  
				deb,   		cre,		cliente,
				ruc, 		cod_clpv, 	cod_sucu, 
				cod_ejer, 	deb_ext, 	cre_ext
		FROM saeclpv  ,  saedmcc   WHERE
		( saedmcc.clpv_cod_clpv = saeclpv.clpv_cod_clpv ) and  
		( saeclpv.clpv_cod_empr = saedmcc.dmcc_cod_empr ) and  
		--( saeclpv.clpv_cod_sucu = saedmcc.dmcc_cod_sucu ) and  		  
		( saedmcc.dmcc_cod_empr = in_empr ) AND  
		( saedmcc.dmcc_fec_emis between  in_fecha_ini  and  in_fecha_fin  ) AND  
		( saeclpv.clpv_clopv_clpv = 'CL') and
		  saeclpv.clpv_cod_sucu ||'' like in_sucu and 
		  saeclpv.grpv_cod_grpv	||'' like in_grupo and 
		  saeclpv.clpv_cod_zona	||'' like in_zona and 
		  saeclpv.clpv_cod_vend	||'' like in_vend and 
		  saedmcc.dmcc_num_fac	||'' like in_num_fact 
		
		order by  saeclpv.clpv_cod_clpv, saedmcc.dmcc_fec_emis, saedmcc.dmcc_num_fac, saedmcc.dmcc_cre_ml

		return fec_emis, cod_tran,  comprob ,  
				num_fac, fec_vence, detalle ,  
				deb,   	 cre ,		cliente ,
				ruc , 	 cod_clpv,  cod_sucu, 
				cod_ejer, deb_ext, 	cre_ext   WITH RESUME;

	end foreach;
end if;

end procedure
                                                                               
