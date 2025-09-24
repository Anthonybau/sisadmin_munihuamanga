function func_jquerytablas(){

	/* Al dar Click en cualquier celda de la cabecera de la tabla, vuelvo a pintar la tabla cebra */
	$("#tLista thead tr th").click(function (){
		$('#tLista tbody tr').removeClass('AlternateBackTD BackTD')
			   .filter(':odd').addClass('AlternateBackTD').end()
			   .filter(':even').addClass('BackTD');
		}
	)

	/* Para pintar la tabla cebra */
	$('#tLista tbody').addClass('DataFONT');
	$('#tLista tbody tr:odd').addClass('AlternateBackTD');
	$('#tLista tbody td:odd').addClass('AlternateDataTD');	

	$('#tLista tbody tr:even').addClass('BackTD');
	$('#tLista tbody td:even').addClass('DataTD');		
	
	/* Para las cabeceras de la tabla */
	$('#tLista thead tr th').addClass('ColumnTD DataFONT cursor:pointer');
	$('#tLista thead tr th').css({ cursor:"pointer"}); 

	/* Indicador de fila actual */
	$('#tLista tbody tr')
		.hover(
			function() {
				$(this).addClass('over');
			},
			function() {
				$(this).removeClass('over');
			}
		)
        
        
	/* Al dar Click en cualquier celda de la cabecera de la tabla, vuelvo a pintar la tabla cebra */
	$("#tLista2 thead tr th").click(function (){
		$('#tLista2 tbody tr').removeClass('AlternateBackTD BackTD')
			   .filter(':odd').addClass('AlternateBackTD').end()
			   .filter(':even').addClass('BackTD');
		}
	)

	/* Para pintar la tabla cebra */
	$('#tLista2 tbody').addClass('DataFONT');
	$('#tLista2 tbody tr:odd').addClass('AlternateBackTD');
	$('#tLista2 tbody td:odd').addClass('AlternateDataTD');	

	$('#tLista2 tbody tr:even').addClass('BackTD');
	$('#tLista2 tbody td:even').addClass('DataTD');		
	
	/* Para las cabeceras de la tabla */
	$('#tLista2 thead tr th').addClass('ColumnTD DataFONT cursor:pointer');
	$('#tLista2 thead tr th').css({ cursor:"pointer"}); 

	/* Indicador de fila actual */
	$('#tLista2 tbody tr')
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
