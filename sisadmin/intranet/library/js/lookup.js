function lookup(nomeCampoForm, nomeTabela, nomeCampoChave, nomeCampoExibicao, nomeCampoAuxiliar, upCase, titulo, nwidth, nheight, lListaInicial, NumForm) {
//	if (largura == null) {
//		largura = 250;
//	}
	newWindow = window.open('../library/lookup.php?nomeCampoForm=' + nomeCampoForm
	          + '&nomeTabela=' + nomeTabela
				 + '&nomeCampoChave=' + nomeCampoChave
				 + '&nomeCampoExibicao=' + nomeCampoExibicao
				 + '&nomeCampoAuxiliar=' + nomeCampoAuxiliar
				 + '&upCase=' + upCase
				 + '&ListaInicial=' + lListaInicial
				 + '&NumForm=' + NumForm 
				 + '&titulo=' + titulo,
				 'newWin',
				 'toolbar=no,location=no,scrollbars=yes,resizable=no,width='+nwidth+',height=520,top=35,left=25');
}