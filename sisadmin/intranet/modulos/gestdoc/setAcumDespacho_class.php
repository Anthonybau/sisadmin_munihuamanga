<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");
//1778
//1924
/*REGULARIZAR CONTADORES DE DOCUMENTOS EN PROCESO X DEPENDENCIA

UPDATE catalogos.dependencia SET depe_acum_despachos_enproceso=0;
UPDATE catalogos.dependencia SET depe_acum_despachos_porrecibir=0;


UPDATE catalogos.dependencia set depe_acum_despachos_enproceso=
coalesce( (SELECT  COUNT(a.desp_id)
FROM gestdoc.despachos_derivaciones a
WHERE  (a.dede_estado=3 OR a.dede_estado=7)
AND a.desp_adjuntadoid IS NULL
AND a.depe_iddestino=dependencia.depe_id),0)
WHERE depe_id in (
SELECT DISTINCT a.depe_iddestino
FROM gestdoc.despachos_derivaciones a
WHERE  (a.dede_estado=3 OR a.dede_estado=7));


UPDATE catalogos.dependencia set depe_acum_despachos_porrecibir=
                                        COALESCE((SELECT  COUNT(a.desp_id)
                                            FROM  gestdoc.despachos_derivaciones a
                                            WHERE  a.dede_estado=2
                                            AND a.depe_iddestino IN (dependencia.depe_id)),0)
WHERE depe_id in (
SELECT DISTINCT a.depe_iddestino
FROM gestdoc.despachos_derivaciones a
WHERE  (a.dede_estado=2));
                               


/*REGULARIZAR CONTADORES DE DOCUMENTOS EN PROCESO X USUARIO

UPDATE admin.usuario SET usua_acum_despachos_enproceso=0;
UPDATE admin.usuario SET usua_acum_despachos_porrecibir=0;
(*OJO PROBAR Y VERIFICAR*)
UPDATE admin.usuario 
set usua_acum_despachos_enproceso=COALESCE((SELECT  COUNT(a.desp_id)
              FROM gestdoc.despachos_derivaciones a
              WHERE  (a.dede_estado=3 OR a.dede_estado=7)
              AND a.usua_idrecibe=usuario.usua_id
              GROUP BY a.usua_idrecibe),0);


UPDATE admin.usuario 
set usua_acum_despachos_porrecibir=COALESCE((SELECT  COUNT(a.desp_id)
			FROM gestdoc.despachos_derivaciones a
            WHERE  a.dede_estado=2
            AND a.usua_iddestino=usuario.usua_id
            GROUP BY a.usua_iddestino),0);


(*ANTIGUO*)
UPDATE admin.usuario SET usua_acum_despachos_enproceso=0;

UPDATE admin.usuario set usua_acum_despachos_enproceso=
(SELECT  COUNT(a.desp_id)
FROM gestdoc.despachos_derivaciones a
WHERE  (a.dede_estado=3 OR a.dede_estado=7)
AND a.usua_idrecibe=usuario.usua_id
GROUP BY a.usua_idrecibe)
WHERE usua_id in (
SELECT DISTINCT a.usua_idrecibe
FROM gestdoc.despachos_derivaciones a
WHERE  (a.dede_estado=3 OR a.dede_estado=7));


*/
class setNotiDespachosEnProcesoUsuario extends selectSQL {
        function __construct($pers_id){
		$this->sql="UPDATE admin.usuario 
                                    SET usua_acum_despachos_enproceso=COALESCE((SELECT  COUNT(a.desp_id)
                                    FROM despachos_derivaciones a
                                    WHERE  (a.dede_estado=3 OR a.dede_estado=7) 
                                    AND desp_adjuntadoid IS NULL 
                                    AND a.usua_idrecibe=usuario.usua_id
                                    AND a.depe_iddestino IN (SELECT DISTINCT a.depe_id
                                                      FROM persona_datos_laborales a
                                                      LEFT JOIN dependencia b on  a.depe_id=b.depe_id
                                                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id) 
                                    GROUP BY a.usua_idrecibe),0) ";
                
	}

	function whereID($id){
		$this->addWhere("usua_id in (SELECT usua_id FROM usuario WHERE pdla_id=$id)");
	}

	function whereUserID($user_id){
		$this->addWhere("usua_id=$user_id");
	}

	function NOAdmin(){
                  $this->addWhere("usua_id!=1");
        }

        function getSQL(){
		$sql=$this->sql;

		for ($i=0; $i<sizeof($this->where); $i++) {
			if($i==0) $sql.="\n WHERE  ".$this->where[$i];
			else $sql.=" AND ".$this->where[$i];
		}

		return($sql);
	}        
}

class setNotiDespachosPorRecibirUsuario extends selectSQL {
        function __construct($pers_id){
		$this->sql="UPDATE usuario set usua_acum_despachos_porrecibir=
                                    COALESCE((SELECT  COUNT(a.desp_id)
                                    FROM despachos_derivaciones a
                                    WHERE  a.dede_estado=2 
                                    AND a.usua_iddestino=usuario.usua_id
                                    AND a.depe_iddestino IN (SELECT DISTINCT a.depe_id
                                                      FROM persona_datos_laborales a
                                                      LEFT JOIN dependencia b on  a.depe_id=b.depe_id
                                                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id) 
                                    GROUP BY a.usua_idrecibe),0) ";
                
	}

	function whereID($id){
		$this->addWhere("usua_id in (SELECT usua_id FROM usuario WHERE pdla_id=$id)");
	}

	function whereUserID($user_id){
		$this->addWhere("usua_id=$user_id");
	}

	function NOAdmin(){
                  $this->addWhere("usua_id!=1");
        }

        function getSQL(){
		$sql=$this->sql;

		for ($i=0; $i<sizeof($this->where); $i++) {
			if($i==0) $sql.="\n WHERE  ".$this->where[$i];
			else $sql.=" AND ".$this->where[$i];
		}

		return($sql);
	}        
}


class setNotiDespachosEnProcesoDependencia extends selectSQL {
        function __construct(){
		$this->sql="UPDATE dependencia set depe_acum_despachos_enproceso=
                                        COALESCE((SELECT  COUNT(a.desp_id)
                                            FROM despachos_derivaciones a
                                            WHERE  (a.dede_estado=3 OR a.dede_estado=7)
                                            AND desp_adjuntadoid IS NULL 
                                            AND a.depe_iddestino IN (dependencia.depe_id)),0) ";
                
	}

	function whereID($id){
		$this->addWhere("depe_id=$id");
	}

	function whereVarios($pers_id){
		$this->addWhere("depe_id IN (SELECT DISTINCT a.depe_id
		 			  FROM persona_datos_laborales a
		 			  LEFT JOIN dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}
        
        function getSQL(){
		$sql=$this->sql;

		for ($i=0; $i<sizeof($this->where); $i++) {
			if($i==0) $sql.="\n WHERE  ".$this->where[$i];
			else $sql.=" AND ".$this->where[$i];
		}

		return($sql);
	}        
}

class setNotiDespachosPorRecibirDependencia extends selectSQL {
        function __construct(){
		$this->sql="UPDATE dependencia set depe_acum_despachos_porrecibir=
                                        COALESCE((SELECT  COUNT(a.desp_id)
                                            FROM despachos_derivaciones a
                                            WHERE  a.dede_estado=2
                                            AND a.depe_iddestino IN (dependencia.depe_id)),0) ";
                
	}

	function whereID($id){
		$this->addWhere("depe_id=$id");
	}

	function whereVarios($pers_id){
		$this->addWhere("depe_id IN (SELECT DISTINCT a.depe_id
		 			  FROM persona_datos_laborales a
		 			  LEFT JOIN dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}
        
        function getSQL(){
		$sql=$this->sql;

		for ($i=0; $i<sizeof($this->where); $i++) {
			if($i==0) $sql.="\n WHERE  ".$this->where[$i];
			else $sql.=" AND ".$this->where[$i];
		}

		return($sql);
	}        
}