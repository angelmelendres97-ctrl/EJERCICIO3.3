function consultarPara() {
    var table = $('#example').DataTable();
    table.destroy();

    var nomClpv = '';
    $('#example').DataTable({
        "searching": true,
        "pageLength": 30,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "saepccp.php?nomClpv=" + nomClpv,
            "type": "POST"
        },
        "columns": [
            { "data": "pccp_cod_pccp" },
            { "data": "pccp_cod_empr" },
            { "data": "pccp_cod_facp" },
            { "data": "pccp_aut_pago" },
            { "data": "pccp_bod_serv" },
            { "data": "pccp_num_orpa" },
            { "data": "pccp_cre_fis" },
            { "data": "pccp_num_digi" },
            { "data": "pccp_tidu_anti" },
            { "data": "pccp_cret_asumi" },
            { "data": "selecciona" }
        ],
        "keys": {
            "columns": ":not(:first-child)",
            "editor": "editor"
        },
        "oLanguage": {
            "sProcessing": "Procesando...",
            "sLengthMenu": 'Mostrar <select>' +
                '<option value="30">30</option>' +
                '<option value="60">60</option>' +
                '<option value="90">90</option>' +
                '<option value="120">120</option>' +
                '<option value="150">150</option>' +
                '<option value="-1">Todo</option>' +
                '</select> registros',
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningun dato disponible en esta tabla",
            "sInfo": "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Filtrar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Por favor espere - cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Ultimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}