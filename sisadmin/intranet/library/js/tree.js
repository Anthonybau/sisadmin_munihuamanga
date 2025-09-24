function tree(namePage, nameCampoForm, nameTabla, nameCampoClave, nameCampoMuestra, nameCampoDepen, ExcluNivelMax, iniStruct, titulo, width, height) {
	newWindow = window.open(namePage+'&nameCampoForm=' + nameCampoForm
	          + '&nameTabla=' + nameTabla
				 + '&nameCampoClave=' + nameCampoClave
				 + '&nameCampoMuestra=' + nameCampoMuestra
				 + '&nameCampoDepen=' + nameCampoDepen
				 + '&ExcluNivelMax=' + ExcluNivelMax
				 + '&iniStruct=' + iniStruct
				 + '&titulo=' + titulo
				 + '&clear=1',				 
				 'newWin',
				 'toolbar=no,location=no,scrollbars=yes,resizable=no,width='+width+',height='+height+',top=35,left=25');
}
