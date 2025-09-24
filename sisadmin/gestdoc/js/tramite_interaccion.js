/*al terminar de cargar el HTML, iguak a: $(function(){}*/
$(document).ready(function(){
    $('.select').select2({
        placeholder: "Pulsa aquí para abrir la Lista",
        allowClear: true,
        minimumResultsForSearch: 1
    });

    $('#checkMasOpciones').change(function(event) {
         if(this.checked) {
            $( '#divMasOpciones' ).show()
         }else{
             $( '#divMasOpciones' ).hide()
             $( '#divTable' ).hide()
         }
    });

    $( "#btn_search" ).click(function() {
        buscarTramite();
    });

    inicializar_controles();
    
    
});
    
function inicializar_controles() {
    //LLENA TIPOS DE DOCUMENTOS
    $.ajax({
    url : '/sisadmin/intranet/modulos/catalogos/jswTipoExpedienteMPV.php',
    method :  'GET',
    dataType : "json",
    data: {'depe_id' : 2 }
        }).then(function(data){
            $.each(data, function(i, item) {
                $("#documento_tipodocumento").append(new Option(item.tiex_descripcion, item.tiex_id));
            });
            
            $('#documento_tipodocumento').val('').trigger('change.select2');
                    
        }, function(reason){
            console.log(reason);
        });

}



function buscarTramite(){
    var formElement = document.getElementById("frm");
    var formData = new FormData(formElement);
    var data=Object.fromEntries(formData);
    
    $( '#divTable' ).show();
    
    //https://datatables.net/    
    $( '#tablResultados' ).DataTable( {
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "select": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "pagingType": "full_numbers",
        "pageLength": 20,
        "ajax": {
            //OJO CON LA RUTA, se mide desde la carpeta index.php
            "type": "POST",
            "url": "../intranet/modulos/gestdoc/jswBuscarTramiteExterno.php",

            "data": function ( d ) {
                                d.formdata = data;
                            }
        },
        "initComplete":function( settings, json){
            //OJO hace un scroll hacia la tabla, despues de llenarla
            var element_to_scroll_to = document.getElementById('tablResultados');
            element_to_scroll_to.scrollIntoView();                    
        },
        "columns": [ //campos de la BD
            { "data": "desp_id", //OJO utilizo un campo de la BD
                    fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).html("<input type='button' value='Ver' class='btn btn-secondary btn-sm' onclick=javascript:abreConsulta('"+oData.desp_id+"')>");
                    }
                },            
            { "data": "desp_id" },
            { "data": "desp_fecha" },
            { "data": "desp_codigo" },
            { "data": "num_documento" },
            { "data": "desp_asunto" }
        ],
        "language": {
                   "lengthMenu": "Mostrar _MENU_ registros por página",
                   "zeroRecords": "Lo sentimos, no se encontraron registros",
                   "info": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
                   "infoEmpty": "No se hallaron registros ",
                   "infoFiltered": "(Filtrados _MAX_ del total de registros)",
                   "loadingRecords": "Cargando...",
                   "processing":     "Procesando...",
                   "paginate": {
                                    "previous": "Anterior",
                                    "next": "Siguiente",
                                    "first": "Primero",
                                    "last": "Ultimo"
                                  }
               },
        drawCallback: function () {
            // Pagination - Add BS4-Class for Horizontal Alignment (in second of 2 columns) & Top Margin
            $('#tablResultados_paginate').addClass("mt-3 mt-md-2");
            $('#tablResultados_paginate ul.pagination').addClass("pagination-sm");
        }    
    } );
    
}


function AbreVentana(sURL){
        var w=720, h=600;
        venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
        venrepo.focus();
    }

function abreConsulta(id) {
        AbreVentana('controllers/procesar_data.php?nr_numTramite=' + id+'&vista=NoConsulta');
}