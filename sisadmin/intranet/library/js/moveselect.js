function selecionaObjetosAssociados() {
	for (x=0; x < parent.content.document.frm.f_destino.options.length; x++) {
		parent.content.document.frm.f_destino.options[x].selected = true;
	}
	
	for (x=0; x < parent.content.document.frm.f_origem.options.length; x++) {
		parent.content.document.frm.f_origem.options[x].selected = true;
	}	
}

function move(fbox, tbox, vFiltro) {
	if(vFiltro.value) {
		alert("Limpie el campo 'Filtro' de la lista hacia donde desea mover el/los registro(s)");
		return false;
	}
	
	var arrFbox = new Array(); //cria array da lista origem
	var arrTbox = new Array(); //cria array da lista destino
	var arrLookup = new Array();
	var i;
	for (i = 0; i < tbox.options.length; i++) {
		arrLookup[tbox.options[i].text] = tbox.options[i].value;
		arrTbox[i] = tbox.options[i].text;
	}
	var fLength = 0;
	var tLength = arrTbox.length;
	for(i = 0; i < fbox.options.length; i++) {
		arrLookup[fbox.options[i].text] = fbox.options[i].value;
		if (fbox.options[i].selected && fbox.options[i].value != "") {
			arrTbox[tLength] = fbox.options[i].text;
			tLength++;
		} else {
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
		}
	}
	arrFbox.sort();
	arrTbox.sort();
	fbox.length = 0;
	tbox.length = 0;
	var c;
	for(c = 0; c < arrFbox.length; c++) {
		var no = new Option();
		no.value = arrLookup[arrFbox[c]];
		no.text = arrFbox[c];
		fbox[c] = no;
	}
	for(c = 0; c < arrTbox.length; c++) {
		var no = new Option();
		no.value = arrLookup[arrTbox[c]];
		no.text = arrTbox[c];
		tbox[c] = no;
	}
	return true
}


function move_dato(fbox, tbox, vFiltro, dato1, dato2) {
	if(vFiltro.value) {
		alert("Limpie el campo 'Filtro' de la lista hacia donde desea mover el/los registro(s)");
		return false;
	}
	
	var arrFbox = new Array(); //cria array da lista origem
	var arrTbox = new Array(); //cria array da lista destino
	var arrLookup = new Array();
	var i;

	for (i = 0; i < tbox.options.length; i++) {
		arrLookup[tbox.options[i].text.replace(dato1, dato2)] = tbox.options[i].value.replace(dato1, dato2);
		arrTbox[i] = tbox.options[i].text.replace(dato1, dato2);		
	}
	
	var fLength = 0;
	var tLength = arrTbox.length;

	for(i = 0; i < fbox.options.length; i++) {
		if (fbox.options[i].selected && fbox.options[i].value != "") {
			arrLookup[fbox.options[i].text.replace(dato1, dato2)] = fbox.options[i].value.replace(dato1, dato2);
			arrTbox[tLength] = fbox.options[i].text.replace(dato1, dato2);		
			tLength++;
		} else {
			arrLookup[fbox.options[i].text] = fbox.options[i].value;			
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
		}
	}

	arrFbox.sort();
	arrTbox.sort();
	fbox.length = 0;
	tbox.length = 0;
	var c;
	for(c = 0; c < arrFbox.length; c++) {
		var no = new Option();
		no.value = arrLookup[arrFbox[c]];
		no.text = arrFbox[c];
		fbox[c] = no;
	}
	for(c = 0; c < arrTbox.length; c++) {
		var no = new Option();
		no.value = arrLookup[arrTbox[c]];
		no.text = arrTbox[c];
		tbox[c] = no;
	}
	return true	
}
