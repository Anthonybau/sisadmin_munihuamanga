/* Para obtener el valor del primer check de la lista marcado */
function ValuePrimerCheck() {
	var ss = $("#tLista tbody input[type=checkbox]");
	var vret	= 0;
	ss.each(function(){
		if (this.checked && vret==0) {
			vret=(this.value)
		} 
	});
	return (vret)
}

function func_jquerytablas(){

	/* Al dar Click en cualquier celda de la cabecera de la tabla, vuelvo a pintar la tabla cebra */
	$("#tLista thead tr th").click(function (){
		$('#tLista tr').removeClass('AlternateBackTD BackTD')
			   .filter(':odd').addClass('AlternateBackTD').end()
			   .filter(':even').addClass('BackTD');
		}
	)

	/* Para checkall  */
	$("#checkall").change(function() {
		var check = this.checked
		var ss = $("#tLista input[type=checkbox]");
		ss.each(function(){
			this.checked = check;
		});
	});

	/* Funci�n que se activa al cambiar el valor de cualquier check */
	$("#tLista input[type=checkbox]").change(function() {

		if(typeof SoloUnCheck=='undefined'){ /* Esta variable indica que solo se podr� marcar un check, esta variable se inicializa en la p�gina "Buscar" */
			var todocheck = true;
			var ss = $("#tLista input[type=checkbox]");
			ss.each(function(){
				$(this) // a�adimos o eliminamos una clase en el registro que lo contiene 
					.parents("tr")[ this.checked ? "addClass" : "removeClass" ]("selected"); 
				if (this.checked == false) {
					todocheck=false;
				}
			});
			/* Para marcar o desmarcar el checkall */
			$("#checkall").attr({checked: todocheck});
		}else{ /* Cuando deseo que solo se permita marcar un solo check a la vez */
			marcado=this.value;
			$(this).parents("tr")[ this.checked ? "addClass" : "removeClass" ]("selected"); /* Pinto o despinto de color amarillo la fila */
			var ss = $("#tLista input[type=checkbox]");
			ss.each(function(){
				if (marcado!==this.value){
					this.checked= false
					$(this).parents("tr")["removeClass"]("selected"); /* Quito el color amarillo de la fila */
				}
			});
		}
	});

	/* Para pintar la tabla cebra */
	$('#tLista ').addClass('DataFONT');
	$('#tLista tr:odd').addClass('AlternateBackTD');
	$('#tLista td:odd').addClass('AlternateDataTD');	

	$('#tLista tr:even').addClass('BackTD');
	$('#tLista td:even').addClass('DataTD');		
	$('.anulado').addClass('ANULADO');	
	$('.atendido').addClass('ATENDIDO');		
	$('.en_espera').addClass('EN_ESPERA');			
	
	/* Para las cabeceras de la tabla */
	$('#tLista thead tr th').addClass('ColumnTD DataFONT cursor:pointer');
	$('#tLista thead tr th').css({ cursor:"pointer"}); 

	/* Indicador de fila actual */
	$('#tLista tr')
		.hover(
			function() {
				$(this).addClass('over');
			},
			function() {
				$(this).removeClass('over');
			}
		)

}

$(document).ready(func_jquerytablas);