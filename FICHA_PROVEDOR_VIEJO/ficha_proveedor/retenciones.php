<?
    if (isset($_REQUEST['rete']))
        $rete = $_REQUEST['rete'];
    else
        $rete = '';

    if (isset($_REQUEST['op']))
        $op = $_REQUEST['op'];
    else
        $op = '';
?>

<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Retenciones</title>
        <!--CSS-->    
        <link rel="stylesheet" href="media/css/bootstrap.css">
        <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
        <!--Javascript-->    
        <script src="media/js/jquery-1.10.2.js"></script>
        <script src="media/js/jquery.dataTables.min.js"></script>
        <script src="media/js/dataTables.bootstrap.min.js"></script>          
        <script src="media/js/bootstrap.js"></script>
        <script src="media/js/lenguajeusuario_2.js"></script>   
        <script src="js/teclaEvent.js" type="text/javascript"></script>  
        <script>

            shortcut.add("Esc", function() {
                close();
            });

            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip(); 
            });

            function seleccionaItem(cuenta, op, cta){
				if(cta != ''){
					if(op == 1){
						window.opener.document.form1.retencionBienes.value = cuenta;
						window.opener.document.form1.retencionServicios.focus();
					}else if(op == 2){
						window.opener.document.form1.retencionServicios.value = cuenta;
						window.opener.document.form1.retencionIvaBienes.focus();
					}else if(op == 3){
						window.opener.document.form1.retencionIvaBienes.value = cuenta;
						window.opener.document.form1.retencionIvaServicios.focus();
					}else if(op == 4){
						window.opener.document.form1.retencionIvaServicios.value = cuenta;
					}
					window.close();
				}else{
					alert('::.Cuenta no definida, acuda a Finanzas - Tesoreria - Configuraciones.::');
				}	
            }
        </script>   
    </head>

    <body>
        <div class="container-fluid">
            <div class="col-md-12 table-responsive"> 
                <input type="hidden" name="op" id="op" value="<?=$op?>">
                <input type="hidden" name="rete" id="rete" value="<?=$rete?>">
                <table id="divRetenciones" class="table table-striped table-bordered table-hover table-condensed" cellspacing="0" width="100%">
                    <thead>
                        <tr class="info">
                            <th>Codigo</th>
                            <th>Detalle</th>
							<th>Porcentaje</th>
							<th>Cta Debito</th>
							<th>Cta Credito</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr class="info">
                            <th>Codigo</th>
                            <th>Detalle</th>
							<th>Porcentaje</th>
							<th>Cta Debito</th>
							<th>Cta Credito</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>        
            </div>
        </div>
    </body>
</html>
