<?php

namespace Model;

/******************************************************************************
 *   	Model {{name}} 
 *****************************************************************************/

class {{name_capitalize}} extends \App\Model {

	{{fields}}

	{{param}}
	
	public static function consulta($param=[]){
		global $dominio;
	
		$p = self::setParams($param)->normalizeParams()->getParams();

		$q = self::getInstance();

		if($p['select'])
			$q->select($p['select']);
		
		elseif ($p['count']) 
			$q->count(' * as total');


		//Filtros
		{{filtros}}

		if($p['first'])
			$q->first = true;

		return $q;

	}

	public static function getFields(){
		return self::$fields;
	}

	public static function salvar($dados=[],$param=[]){

		global $dominio;

		$out = NULL;

		$dados = !empty($dados) ? $dados : $_POST;

		$bdo = new \bdo('{{table}}');

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
