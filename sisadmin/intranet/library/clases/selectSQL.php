<?php

class selectSQL {
	var $sql;
	var $where;
	var $groupBy;
	var $order;
	var $limitDesde;
	var $limitCantidad;
	var $existe;
	
	function __construct(){
	}

	function addWhere($where){
		$this->where[] = $where;
	}

	function addGroupBy($groupBy){
		$this->groupBy[] = $groupBy;
	}

	function addOrder($order){
		$this->order[] = $order;
	}

	function addLimit($limitDesde,$limitCantidad){
		$this->limitDesde=$limitDesde;
                $this->limitCantidad=$limitCantidad;
	}

	function getSQL(){
		$sql=$this->sql;
                
                $sqlWhere='';
                if(is_array($this->where)){
                    for ($i=0; $i<sizeof($this->where); $i++) {
                            if(!strpos($sqlWhere,'WHERE',0)) $sqlWhere.="\n WHERE  ".$this->where[$i];
                            else $sqlWhere.=" AND ".$this->where[$i];
                    }
                }
                $sql.=$sqlWhere;
                        
                $sqlGroup='';
                if(is_array($this->groupBy)){
                    for ($i=0; $i<sizeof($this->groupBy); $i++) {
                            if(!strpos($sqlGroup,'GROUP BY',0)) $sqlGroup.="\n GROUP BY ".$this->groupBy[$i];
                            else $sqlGroup.=" ,".$this->groupBy[$i];
                    }
                }
                $sql.=$sqlGroup;
	
                $sqlOrder='';
                if(is_array($this->order)){
                    for ($i=0; $i<sizeof($this->order); $i++) {
                            if(!strpos($sqlOrder,'ORDER',0)) $sqlOrder.="\n ORDER BY ".$this->order[$i];
                            else $sqlOrder.=" ,".$this->order[$i];
                    }
                }
                $sql.=$sqlOrder;
                
                if(($this->limitDesde or $this->limitDesde==0) && $this->limitCantidad)
                    $sql.=" LIMIT ".$this->limitCantidad." OFFSET ".$this->limitDesde;

		return($sql);
	}

	function setDatos(){
		global $conn;						
                    
		$sql = $this->getSQL();
		$rs = new query($conn, $sql);

		if ($this->field=$rs->getrow()){
			$this->existe=$rs->numrows();
		}
		else $this->existe=0;
	}

	/* Para obtener el dato de un campo de la consulta */
	function field($nameField){
		return $this->field["$nameField"];
	}

	function existeDatos(){
		return($this->existe);
	}
}