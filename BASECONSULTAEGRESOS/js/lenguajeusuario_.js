function listar_proveedores(){
    var nomProveedor = document.getElementById('proveedor').value;
	//console.log(nomProveedor);
    $('#table_proveedor').DataTable( {
        "searching": true,
        "pageLength": 30,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "buscar_proveedor.php?nomProveedor="+nomProveedor,
            "type": "POST"
        },
        "columns": [
            { "data": "ruc" },
            { "data": "nombre" },
            { "data": "selecciona" }
        ],
        "keys": {
            "columns": ":not(:first-child)",
            "editor":  "editor"
        },
        "oLanguage": {
            "sProcessing":     "Procesando...",
            "sLengthMenu": 'Mostrar <select id="cantidad_datos" name="cantidad_datos">'+
            '<option value="30">30</option>'+
            '<option value="60">60</option>'+
            '<option value="90">90</option>'+
            '<option value="120">120</option>'+
            '<option value="150">150</option>'+
            '<option value="-1">Todo</option>'+
            '</select> registros',
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningun dato disponible en esta tabla",
            "sInfo":           "Mostrando (_START_ al _END_) de  _TOTAL_ registros",

            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Filtrar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Por favor espere - cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Ultimo",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}

	