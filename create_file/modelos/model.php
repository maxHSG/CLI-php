<?php
/******************************************************************************
 *   	Model {{name}} 
 *****************************************************************************/

class {{name}}Model {

	{{fields}}

	{{param}}
	
	public static function consulta($param=[]){
		global $dominio;

		$j = $c = $g  = $f = null;
		
		$p = set_param(self::$param,$param);

		//Filtros
		{{filtros}}

		// Peças da SQL
		$join = (count($j)) ? join(" " . PHP_EOL,$j) : null;
		$campos = (count($c)) ? join(", " . PHP_EOL,$c) : "*";
		$wherebusca = (count($f)>0) ? PHP_EOL . " WHERE " . join(PHP_EOL . " AND ",$f) : null;
		$ordem = ($p["ordem"]) ? PHP_EOL . " ORDER BY " . $p['ordem'] : "";
		$limite = ($p["limite"]) ? PHP_EOL . " LIMIT ".$p["limite"] : "";

		if($p["qtde_por_pagina"]){
		  	$p["ini"] = $p["ini"] > 0 ? $p["ini"] : 0;
		  	if($p["pg"]>1){
		  		$p["ini"] = $p["qtde_por_pagina"] * ($p["pg"]-1);
		  	}
		  	$limite = PHP_EOL . " LIMIT ".(int)$p["ini"].",".$p["qtde_por_pagina"];
		}			
		
		// SQL
		$sql_base = "FROM `{{table}}` `{{alias}}` {$join}{$wherebusca}";
		$sql = "SELECT {$campos} {$sql_base} GROUP BY `{{alias}}`.`id` {$ordem}{$limite}";
		$sql_count = "SELECT COUNT(DISTINCT(`{{alias}}`.`id`)) as total_regs ".$sql_base;
		$sql_count_paginado = "SELECT COUNT(DISTINCT(`regs`.`id`)) as total_regs FROM (SELECT DISTINCT(`{{alias}}`.`id`) {$sql_base} {$limite}) as regs";
	

		#echo nl2br($sql.str_repeat(PHP_EOL,2)); print_r($p); exit;
		
		if(empty($p["count"]))
			$recordset = consultasql($sql,$p["indice"]);

		//Efetua as consultas com ou sem paginacao
		if($p["count"] == "paginado")
			$recordset["total"] = consultabanco_count($sql_count_paginado,"total_regs");

		elseif($p["qtde_por_pagina"] || $p["count"])
			$recordset["total"] = consultabanco_count($sql_count,"total_regs");		

		if($p["count"])
			return $recordset["total"];

		if(!empty($p["first"])){
			return !empty($recordset) ? current($recordset) : false;
		}
		    
		return $recordset;
	}

	public static function getFields(){
		return self::$fields;
	}

	public static function getParams(){
		return self::$param;
	}

	public static  function salvar($dados=[],$param=[]){

		global $dominio;

		$out = NULL;

		$dados = !empty($dados) ? $dados : $_POST;

		$bdo = new bdo('{{table}}');

		$dados['dominio_id'] = !empty($dados['dominio_id']) ? $dados['dominio_id'] : $dominio['id'];

		//$bdo->setValid('nome',3,'Informe um nome com pelo menos 3 letras');							
		
		$bdo->setCampo($dados);

		if (empty($dados['id'])) {

			$dados['data_cadastro'] = date('Y-m-d');

			if ( !empty($id_add  = $bdo->adicionar()) ) {
				$out['sucesso'] = true;				 		
				$out['dados'] = $dados;
				$out['dados']['id'] = $id_add;
			}

		}elseif (!empty($dados['id']) && is_numeric($dados['id']) ) {

			$dados['data_atualizacao'] = date('Y-m-d');

			$bdo->setId($dados['id']);
			
			if ($bdo->atualizar()) {
				$out['sucesso']  = true;					
				$out['dados'] = $dados;
			}	


		}

		return $out;
	}


}
