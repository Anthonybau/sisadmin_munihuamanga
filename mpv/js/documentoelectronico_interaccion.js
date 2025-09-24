$(function(){
    var depe_id=window.atob(unescape(encodeURIComponent( getParameterByName('id') )));    
    inicializar_controles(depe_id);

    $('.select').select2({
        minimumResultsForSearch: -1
    });

    $(".search_document").click(search_document);
    
    if( depe_id>0 ){
        $( "#tipo_dependencia" ).change(function() {
            llena_procedimientos( this.value );
        });
    }
    
    $('input[type=radio][name=tipo_documento]').change(function() {
        rellenar_tipo_documento(this.value);
    });

    $( "#documento_tipo_tramite" ).change(function() {
        link_requisitos( this.value );
    });
    


});




function rellenar_tipo_documento(tipocomprobante) {
    $("#persona_tipodocumento").empty();
    
    if(tipocomprobante == '01') { //Persona Natural
        $('#persona_tipodocumento').append($("<option></option>")
            .attr("value",'1')
            .text('DNI'));


        $('#persona_tipodocumento').trigger("change");

        $(".search_document").show();
        
        $("#titulo_numerodocumento").html('N° DNI');
        $("#titulo_nombresolicitante").html('Nombres y Apellidos');
    }    

    if(tipocomprobante == '02') { //Persona Juridica
        $('#persona_tipodocumento').append($("<option></option>")
            .attr("value",'6')
            .text('RUC'));
        $('#persona_tipodocumento').trigger("change");        

        $(".search_document").show();
        
        $("#titulo_numerodocumento").html('N° RUC');
        $("#titulo_nombresolicitante").html('Razón Social');
    }

    if(tipocomprobante == '03') { //Canet de extanjeria
        $('#persona_tipodocumento').append($("<option></option>")
            .attr("value",'4')
            .text('CARNET DE EXTRANJERIA'));

        $('#persona_tipodocumento').trigger("change");

        $(".search_document").show();
        
        $("#titulo_numerodocumento").html('N° CARNET DE EXTRANJERIA');
        $("#titulo_nombresolicitante").html('Nombres y Apellidos');
    }    
    
}

function search_document() {
    
    $("#icon_search_document").hide();
    $("#icon_searching_document").show();
    $(".search_document").prop('disabled', true);

    var tipodoc = $("#persona_tipodocumento").val().toString();
    var numdoc = $("#persona_numerodocumento").val();
    if(tipodoc == '6') { //RUC
        /*var resp = validar_numero_ruc(numdoc);
        if(resp == 1) {
            consultar_ruc(numdoc);
        }*/
        var ruc = new String(numdoc);
        if (ruc.length == 11){
            consultar_RUC_DNI(numdoc);
        }else{
            $("#icon_search_document").show();
            $("#icon_searching_document").hide();
            $(".search_document").prop('disabled', false);
            getConfirm('Valor del RUC no aceptado',function(result) {});
        }
            
    }else if(tipodoc == '4') { //CARNET DE EXTRANJERIA
        /*var resp = validar_numero_ruc(numdoc);
        if(resp == 1) {
            consultar_ruc(numdoc);
        }*/
        var ruc = new String(numdoc);
        if (ruc.length == 9){
            consultar_RUC_DNI(numdoc);
        }else{
            $("#icon_search_document").show();
            $("#icon_searching_document").hide();
            $(".search_document").prop('disabled', false);
            getConfirm('Valor de Carnet de Extranjeria no aceptado',function(result) {});
        }
            
    } else if (tipodoc == '1') { //DNI
        var dni = new String(numdoc);
        if (dni.length == 8){
            consultar_RUC_DNI(numdoc);
        }else{
            $("#icon_search_document").show();
            $("#icon_searching_document").hide();
            $(".search_document").prop('disabled', false);
            getConfirm('Valor del DNI no aceptado',function(result) {});
        }
    } else {

    }

}
// http://fullcomputercenter.com/sunatphp/search_identity.php
//  /sisadmin/intranet/library/sunatphp/consultar_RUC_DNI.php
function consultar_RUC_DNI(codigo){

    $.ajax({
    url : '/sisadmin/intranet/library/sunatphp/consultar_RUC_DNI.php',
    method :  'POST',
    dataType : "json",
    data: {'codigo' : codigo }
        }).then(function(data){
            if(data.success == true) {
                if( codigo.length == 11 ){
                    $("#persona_nombre").val(data.result.razon_social);
                }else{
                    $("#persona_nombre").val(data.result.paterno+' '+data.result.materno+' '+data.result.nombre);
                }
                
                $("#icon_search_document").show();
                $("#icon_searching_document").hide();
                $(".search_document").prop('disabled', false);
                $(".persona_nombre").prop('readonly', true);
            }else{
                if( codigo.length == 11 ){
                    getConfirm('NO se encontraron datos, sin embargo usted puede digitar su Razón Social',function(result) {});
                }else{
                    getConfirm('NO se encontraron datos, sin embargo usted puede digitar sus Nombres y Apellidos',function(result) {});
                }
                
                $("#icon_search_document").show();
                $("#icon_searching_document").hide();
                $(".search_document").prop('disabled', false);
                $(".persona_nombre").prop('readonly', false);
                $("#persona_nombre").focus();
                
            }
        }, function(reason){
            getConfirm(reason.responseText,function(result) {});
            $("#icon_search_document").show();
            $("#icon_searching_document").hide();
            $(".search_document").prop('disabled', false);
            //console.log(reason);
        });
 }

//71421062

function inicializar_controles(depe_id) {

    
    if(!depe_id) {
        depe_id=0;
        depe_idx=2;
    }else{
        depe_idx=depe_id;
    }

    if( depe_id==0 ){
        //LLENA DEPENDENCIAS
        $.ajax({
        url : '/sisadmin/intranet/modulos/catalogos/jswDependencias.php',
        method :  'GET',
        dataType : "json",
        data: {'depe_id' : depe_id }
            }).then(function(data){
                $.each(data, function(i, item) {
                    $("#tipo_dependencia").append(new Option(item.descripcion, item.id));                
                });
                
                /*INICIALIZA LA DEPENDENCIA*/
                $("#tipo_dependencia").val(depe_idx);


            $(".select").select2({
                placeholder: "Seleccione un elemento de la lista",
                allowClear: true
            });        

            }, function(reason){
                console.log(reason);
            });
    }
        
    //LLENA PROCEDIIENTOS
    llena_procedimientos( depe_idx )
    
    //LLENA TIPOS DE EXPEDIENTES
    $.ajax({
    url : '/sisadmin/intranet/modulos/gestdoc/jswTiposExpediente.php',
    method :  'GET',
    dataType : "json",
    data: {'depe_id' : 2 }
        }).then(function(data){
            $.each(data, function(i, item) {
                $("#documento_tipodocumento").append(new Option(item.tiex_descripcion, item.tiex_id));                
            });

        }, function(reason){
            console.log(reason);
        });


}

function llena_procedimientos( depe_id ) {
    //LLENA TUPA
    $("#documento_tipo_tramite").empty();
    $("#requisitos").empty();    
    $("#documento_tipo_tramite").append(new Option('Pulse aqui para abrir lista', 0));    
    
    $( "#TriSeaDefault" ).prop('checked', false);
    
    $.ajax({
    url : '/sisadmin/intranet/modulos/gestdoc/jswProcedimientos.php',
    method :  'GET',
    dataType : "json",
    data: {'depe_id' : depe_id }
        }).then(function(data){
            $.each(data, function(i, item) {
                $("#documento_tipo_tramite").append(new Option(item.descripcion, item.id));
            });
        $(".select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
        });        
        
        }, function(reason){
            console.log(reason);
        });
   
}

function link_requisitos( proc_id ) {
    $("#requisitos").empty();
    
    if( proc_id >0 && proc_id != 9999 ){
        var link = '<button type="button" class="btn btn-link" onclick="javascript:mostrar_requisitos( ' + proc_id + ' )"><B>VER REQUISITOS</B></button>';
        $( "#requisitos" ).html( link );
    }   
}

function mostrar_requisitos(proc_id) {
    $("#requisitos").empty();
    if( proc_id > 0 ){
        //LLENA TIPO DE TRAMITE
        $.ajax({
        url : '/sisadmin/intranet/modulos/gestdoc/jswProcedimientosRequisitos.php',
        method :  'GET',
        dataType : "json",
        data: {'proc_id' : proc_id }
            }).then(function(data){           
                var link = '<button type="button" class="btn btn-link" onclick="javascript:link_requisitos( ' + proc_id + ' )"><B>CERRAR REQUISITOS</B></button>';
                if( data[0].requisitos != ''){
                    $( "#requisitos" ).html('<B>REQUISITOS:</B><BR><textarea  rows="10" cols="60" readonly>'+data[0].requisitos+'</textarea><BR>'+ link);
                }else{
                    $( "#requisitos" ).html('<B>REQUISITOS:</B><BR><textarea  rows="10" cols="60" readonly></textarea><BR>'+ link);
                }
            }, function(reason){
                console.log(reason);
            });
    }
}

function getConfirm(confirmMessage,callback){
        confirmMessage = confirmMessage || '';
        $('#msg-myModalConfirm').text( confirmMessage );
        $('#myModalConfirm').modal('show');

        $(document).on("click", "#btnConfirmAceptar-myModalConfirm", function () {
            if (callback) callback(true);
        });
}


function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}