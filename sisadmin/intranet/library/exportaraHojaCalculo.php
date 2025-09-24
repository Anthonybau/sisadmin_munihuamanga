<?php
$name_file=$name_file?$name_file:'reporte.xls';

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$name_file"); 

echo "<table><tr>";

$numCampos = $rs->numfields();
/* Titulos de las columnas */
for ($x=0;$x<$numCampos;$x++){
	echo "<td><b>".$rs->fieldname($x)."</b></td>";
}
echo "</tr><tr>";

while ($rs->getrow()) {
	for ($x=0;$x<$numCampos;$x++){
		echo "<td>".$rs->field($x)."</td>";
	}
	echo "</tr><tr>";
}

echo "</tr></table>";

/* cierra la conexion a la BD */
$conn->close();
exit;