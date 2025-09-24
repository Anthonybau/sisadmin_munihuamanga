<?php
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=$filename"); 

/* Titulos de las columnas */
if($ImprimeCampos){
echo "|";
	for ($x=0;$x<$rs->numfields();$x++){
		echo $rs->fieldname($x)."|";
	}
	echo "\n\r";
	echo "|";		
}

while ($rs->getrow()) {
	for ($x=0;$x<$rs->numfields();$x++){
		//para el archivo del PDT debe colocar campos vacios en lugar de 0 en los campos de importe
		//exepto en la columa de dias laborados
		if(($rs->field($x)=='' or $rs->field($x)==0) && $x!=2)
			echo "|";		
		else
			echo $rs->field($x)."|";
	}
	echo "\r\n";	
}

/* cierra la conexion a la BD */
$conn->close();
exit;