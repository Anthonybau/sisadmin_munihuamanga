function CheckAll() {
	var val; 
	val = document.frm.checkall.checked; 
	dml = document.frm; 
	len = dml.elements.length; 
	var i=0;
	for( i=0 ; i<len ; i++)	{
		dml.elements[i].checked=val;
                var e = dml.elements[i];
                if (e.checked==false){
                    e.parentNode.parentNode.parentNode.style.backgroundColor = '';
                }else{
                    e.parentNode.parentNode.parentNode.style.backgroundColor = '#FFFFCC';
                }
	}
}

function uncheck(objform,objckeck) {
	dml = document.frm; 
	len = dml.elements.length; 
	var i=0;
	for( i=0 ; i<len ; i++)	{
		var e=dml.elements[i]
		if (objckeck.value!==e.value){
			e.checked = false;
		}
	}
	checkform(objform,objckeck)	
}


function checkform(objform,objckeck)
{
	var max = objform.elements.length;
	if (objckeck.name=='checkall'){
		for (var idx = 0; idx < max; idx++) {
			var e = objform.elements[idx]
			if (e.type=='checkbox' && e.name=='sel[]'){
				e.checked=objckeck.checked;		
				_color=objckeck.checked==true?"#FFFFCC":"";
				e.parentNode.parentNode.parentNode.style.backgroundColor = _color;
			}
		}
	}else{
		checkBoxAll = true;
		for (var idx = 0; idx < max; idx++) {
			var e = objform.elements[idx]
			if (e.type=='checkbox' && e.name=='sel[]'){
				if (e.checked==false){
					e.parentNode.parentNode.parentNode.style.backgroundColor = '';
					checkBoxAll = false;
				}else{
					e.parentNode.parentNode.parentNode.style.backgroundColor = '#FFFFCC';
				}
			}
		}
		if(typeof objform.checkall!='undefined') // Me aseguro de que el objeto existe, para el caso de las listas donde no necesito tener un checkbox que seleccione a todos los demas
			objform.checkall.checked=checkBoxAll;
	}
}

function checkCol(objform,objckeck,col)
{
	var max = objform.elements.length;
	if (objckeck.name=='checkcol'){
		for (var idx = 0; idx < max; idx++) {
			var e = objform.elements[idx]
			if (e.type=='checkbox' && e.name=='sel[]' && e.id==col){
				e.checked=objckeck.checked;		
				_color=objckeck.checked==true?"#FFFFCC":"";
				e.parentNode.parentNode.parentNode.style.backgroundColor = _color;
			}
		}
	}else{
		checkBoxAll = true;
		for (var idx = 0; idx < max; idx++) {
			var e = objform.elements[idx]
			if (e.type=='checkbox' && e.name=='sel[]' && e.id==col){
				if (e.checked==false){
					e.parentNode.parentNode.parentNode.style.backgroundColor = '';
					checkBoxAll = false;
				}else{
					e.parentNode.parentNode.parentNode.style.backgroundColor = '#FFFFCC';
				}
			}
		}
		if(typeof objform.checkall!='undefined') // Me aseguro de que el objeto existe, para el caso de las listas donde no necesito tener un checkbox que seleccione a todos los demas
			objform.checkall.checked=checkBoxAll;
	}
}
