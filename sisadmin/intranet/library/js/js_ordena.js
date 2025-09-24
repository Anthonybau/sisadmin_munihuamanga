function move(index, to) {
	var list = document.frm.list;
	var total = list.options.length-1;

	if (index == -1) return false;
	if (to == +1 && index == total) return false;
	if (to == -1 && index == 0) return false;

	var items = new Array;
	var values = new Array;
	for (i = total; i >= 0; i--) {
		items[i] = list.options[i].text;
		values[i] = list.options[i].value;
	}
	for (i = total; i >= 0; i--) {
		if (index == i) {
			list.options[i + to] = new Option(items[i],values[i + to], 0, 1);
			list.options[i + to].value = values[i];
			list.options[i] = new Option(items[i + to], values[i]);
			list.options[i].value = values[i + to];
			i--;
		}
		else {
			list.options[i] = new Option(items[i], values[i]);
			list.options[i].value = values[i];
	  }
	}
	list.focus();
}

function Ordenar(url) {
	var list = document.frm.list;
	var theList = "?ids=";
	for (i = 0; i <= list.options.length-1; i++) { 
		theList += list.options[i].value;
		if (i != list.options.length-1) theList += ",";
	}	
	parent.oculto.location.href = url + theList;
}
// -->
