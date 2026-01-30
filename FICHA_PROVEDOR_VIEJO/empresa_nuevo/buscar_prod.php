
<!DOCTYPE html>

<? 
	$array = $_GET['array']; 
	list($bode, $prod, $sucu) = explode(",", $array);
?>

<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Productos - Inventario</title>
        
		<!--CSS-->    
        <link rel="stylesheet" href="media/css/bootstrap.css">
        <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
        
		<!--Javascript-->    
        <script src="media/js/jquery-1.10.2.js"></script>
        <script src="media/js/jquery.dataTables.min.js"></script>
        <script src="media/js/dataTables.bootstrap.min.js"></script>          
        <script src="media/js/bootstrap.js"></script>
        <script src="media/js/lenguajeusuario.js"></script>     
        
		<script>
		
			shortcut.add("Esc", function() {
                close();
            });
			
            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip(); 
            });

            function seleccionaItem(a,b){
                window.opener.document.form1.codProdProdServ.value = a;
                window.opener.document.form1.prodProdServ.value = b;
                window.opener.consultaPrecio();
                window.close();
            }
        </script>   
		
    </head>

    <body>
        <div class="container-fluid">
            <div class="col-md-12 table-responsive">   
                <table id="example" class="table table-striped table-bordered table-hover table-condensed" cellspacing="0" width="100%">
                    <thead>
                        <tr class="info">
                            <th>Codigo</th>
                            <th>Producto</th>
							<th>Cod. Barras</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr class="info">
                            <th>Codigo</th>
                            <th>Producto</th>
							<th>Cod. Barras</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>  
				<input type="hidden" id="bode" name="bode" value="<?=$bode?>"/>				
				<input type="hidden" id="prod" name="prod" value="<?=$prod?>"/>		
                <input type="hidden" id="sucu" name="prod" value="<?=$sucu?>"/> 
            </div>
        </div>
    </body>
</html>
