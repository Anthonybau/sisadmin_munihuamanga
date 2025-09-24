function abreJanelaAuxiliar(pagina,nWidth,nHeight){
	eval('janela = window.open("../../library/auxiliar.php?pag=' +  pagina +
	     '","janela","width='+nWidth+',height='+nHeight+',top=50,left=150' +
		  ',scrollbars=no,hscroll=0,dependent=yes,toolbar=no")');
	janela.focus();
}