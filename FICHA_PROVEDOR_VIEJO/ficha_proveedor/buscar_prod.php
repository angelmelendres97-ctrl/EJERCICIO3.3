
<!DOCTYPE html>
<?
	if(isset($_REQUEST['producto'])) $producto = $_REQUEST['producto'];
		else $producto = '';
	
	if(isset($_REQUEST['id_bodega'])) $id_bodega = $_REQUEST['id_bodega'];
		else $id_bodega = '';
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
		<script src="js/teclaEvent.js" type="text/javascript"></script>  
        <script>
		
			shortcut.add("Esc", function() {
                close();
            });
			
            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip(); 
            });

            function seleccionaItem(a,b){
				window.opener.cargarDatosProd(a, b);
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
                            <th>Cliente</th>
                            <th>Proveedor</th>
                            <th>Producto</th>
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
                            <th>Cliente</th>
                            <th>Proveedor</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>    
				<input type="hidden" name="producto" id="producto" value="<?=$producto?>"/>
				<input type="hidden" name="id_bodega" id="id_bodega" value="<?=$id_bodega?>"/>
            </div>
        </div>
    </body>
</html>
