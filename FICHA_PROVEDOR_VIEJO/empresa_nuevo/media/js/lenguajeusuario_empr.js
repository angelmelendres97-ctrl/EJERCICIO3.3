$(document).ready(function() {	
	var nomClpv = '';
	$('#example').DataTable( {	
		"searching": true,
		"pageLength": 30,
		"bDeferRender": true,	
		"sPaginationType": "full_numbers",
		"ajax": {
			"url": "busca_empresa.php?nomClpv="+nomClpv,
	    	"type": "POST"
		},					
		"columns": [
			{ "data": "empr_cod_empr" },
			{ "data": "empr_nom_empr" },
			{ "data": "empr_ruc_empr" },
			{ "data": "empr_dir_empr" },
			{ "data": "empr_nom_cont" },
			{ "data": "selecciona" }
		],
		"keys": {
            "columns": ":not(:first-child)",
            "editor":  "editor"
        },
		"oLanguage": {
            "sProcessing":     "Procesando...",
		    "sLengthMenu": 'Mostrar <select>'+ 
		        '<option value="30">30</option>'+
		        '<option value="60">60</option>'+
		        '<option value="90">90</option>'+
		        '<option value="120">120</option>'+
		        '<option value="150">150</option>'+
		        '<option value="-1">Todo</option>'+
		        '</select> registros',    
		    "sZeroRecords":    "No se encontraron resultados",
		    "sEmptyTable":     "Ningun dato disponible en esta tabla",
		    "sInfo":           "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
		    "sInfoEmpty":      "Mostrando del 0 al 0 de un total de 0 registros",
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
});