<?php 


require 'elementos/el_util.php';
require 'elementos/el_table.php';
require 'elementos/el_form.php';


define('DS', DIRECTORY_SEPARATOR);
define('ROOT',$_SERVER['DOCUMENT_ROOT']);


/**
 * Class Create
 */
class Create 	
{

	public $name;
	public $table;
	public $alias;
	public $data;


	public function __construct($name,$table="",$param = []){

		$this->name = util::toCamelCase($name);
		if(!$param['off-query']){
			$this->table = $name;
			$this->alias = strtolower($this->name[0]);
			$this->fields = [];
		}else{
			$this->table = !empty($table) ? $table : $name;
			$this->alias = strtolower($this->name[0]);
			$this->fields = QB::table($this->table)->getColumns();
			
		}

	}


	public function mkClass()
	{

		$class = $this->ler(ROOT.'CLI'.DS.'create_file'.DS.'modelos'.DS.'class.php');

		$filtros = 
		join(",".PHP_EOL,array_map(
			function($field){ 	
				return "\t\t'f_{$field['Field']}' => null"; 
			},$this->fields)
		);

		$ths = tpl::join(array_map(ElTable::th(),$this->fields));

		$trEtiquetas  = ElUtil::hasEtiquetas($this->fields) ? ElTable::trEtiquetas($this->table) : NULL;

		$tds = tpl::join(array_map(ElTable::td(),$this->fields));
		
		$form = Elform::makeForm($this->fields);
		
		$formBusca = Elform::makeFormBusca($this->fields);		

		return str_replace(
			['{{name}}','{{ths}}','{{tds}}','{{form}}','{{formBusca}}','{{filtros}}','{{trEtiquetas}}'], 
			[ $this->name ,$ths ,$tds, $form ,$formBusca,$filtros,$trEtiquetas]
		, $class);
	}




	public function mkCtrl_p()
	{

		$painel = $this->ler(ROOT.'CLI'.DS.'create_file'.DS.'modelos'.DS.'controls_painel.php');

		return str_replace(['{{name}}','{{nameCapitalaze}}'],[ $this->name,ucfirst($this->name)], $painel);
	}



	public function mkModel()
	{

		$model = $this->ler(ROOT.'CLI'.DS.'create_file'.DS.'modelos'.DS.'model.php');

		$f_busca_string = '$f_busca';

		$alias = $this->alias;

		$busca = <<< 'BUSCA'


		if(!empty($p['f_busca'])){
			$f_busca = anti_sql_injection($p['f_busca']);

			if(is_numeric($f_busca)){
				$f[] =
				"(
					{f_busca}
				)";
			}else{
				$f[] =
				"(
					{f_busca}
				)";
				
			}
			

		}
BUSCA;

    	foreach ($this->fields as $field) {
        	$fld[] = "\t\t'{$field['Field']}' => null";
        	
        	if (strpos($field['Field'],'dominio') !== FALSE) {
        		$f[] = "\t\t" . '$f[] =' . " ' `{$alias}`.{$field['Field']}  = '.". '$dominio[\'id\'];' .PHP_EOL;    
        	}elseif (strpos($field['Type'],'date') !== FALSE) {
        		$p[] = "\t\t'f_{$field['Field']}1' => null";	
        		$p[] = "\t\t'f_{$field['Field']}2' => null";	
        		
        		$f[] = "\t\t".'if(!empty($p[\'f_'.$field['Field'].'1\'])) $f[] = "`'.$alias.'`.`'.$field['Field'].'` >= \'".util::datasql($p[\'f_'.$field['Field'].'1\'])."\'"  ;';
				$f[] = "\t\t".'if(!empty($p[\'f_'.$field['Field'].'2\'])) $f[] = "`'.$alias.'`.`'.$field['Field'].'` <= \'".util::datasql($p[\'f_'.$field['Field'].'2\'])."\'"  ;'.PHP_EOL;


        	}elseif (
        		strpos($field['Type'], "int") !== FALSE ||
        		strpos($field['Type'], "tinyint")  !== FALSE ||
        		strpos($field['Type'], "smallint")  !== FALSE ||
        		strpos($field['Type'], "mediumint")  !== FALSE ||
        		strpos($field['Type'], "bigint")  !== FALSE ||
        		strpos($field['Type'], "bit")  !== FALSE ||
        		strpos($field['Type'], "float")  !== FALSE ||
        		strpos($field['Type'], "double")  !== FALSE ||
        		strpos($field['Type'], "decimal") 
        	) {

        		$f_busca_num[] = " `{$alias}`.{$field['Field']} = '{{$f_busca_num_string}}' ";

        		$f[] = "\t\t".'if(is_numeric($p["f_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' = "\'.$p["f_'.$field['Field'].'"].\'"  \';' ;
	        	$f[] = "\t\t".'elseif(is_array($p["f_'.$field['Field'].'"])) $f[]="`'.$alias.'`.'.$field['Field'].' IN (\'".join("\',\'",$p[\'f_'.$field['Field'].'\'])."\')";'.PHP_EOL;

	        	$p[] = "\t\t'f_not_{$field['Field']}' => null";

        		$f[] = "\t\t".'if(is_numeric($p["f_not_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' != "\'.$p["f_not_'.$field['Field'].'"].\'"  \';' ;
	        	$f[] = "\t\t".'elseif(is_array($p["f_not_'.$field['Field'].'"])) $f[]="`'.$alias.'`.'.$field['Field'].' NOT IN (\'".join("\',\'",$p[\'f_'.$field['Field'].'\'])."\')";'.PHP_EOL;


        	
        	}else{
        		$f_busca[] = " `{$alias}`.{$field['Field']} LIKE '%{{$f_busca_string}}%'";

	        	$f[] = "\t\t".'if(!empty($p["f_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' = "\'.$p["f_'.$field['Field'].'"].\'"  \';'.PHP_EOL;       		
        	}

        	$p[] = "\t\t'f_{$field['Field']}' => null";

    	}


    	if (!empty($f_busca_num)) 
    		$f_busca_num = join(' OR '.PHP_EOL,$f_busca_num);	
    	
    	if (!empty($f_busca)) 
    		$f_busca = join(' OR '.PHP_EOL,$f_busca);	


    	$f[] = str_replace(['{f_busca}','{f_busca_num}'],[$f_busca,$f_busca_num],$busca);

    	$p[] = "\t\t 'f_busca' => null";
    	$p[] = "\t\t 'first' => false";
    	$p[] = "\t\t 'debug' => false";
    	$p[] = "\t\t 'limite' => null";
    	$p[] = "\t\t 'qtde_por_pagina' => 50";
    	$p[] = "\t\t 'count'  => false";
    	$p[] = "\t\t 'pg'  => 1";
    	$p[] = "\t\t 'ini'  => 0";
    	$p[] = "\t\t 'indice'  => NULL";    	
    	$p[] = "\t\t 'ordem'  => '{$alias}.id'";    	
    
    	$fields = 'public static $fields = ['."\n".join(",".PHP_EOL,$fld)."\n\t".'];';
    	$param = 'public static $param = ['."\n".join(",".PHP_EOL,$p)."\n\t".'];';
    

    	$filtros = tpl::join($f);

		return str_replace(
			['{{name}}' ,'{{fields}}','{{param}}','{{filtros}}','{{alias}}','{{table}}'],
			[ $this->name 	,$fields 	 ,$param	 ,	$filtros   ,$this->alias	,	$this->table],
			$model
		);
	}
	
	public function mkModel2()
	{

		$model = $this->ler(ROOT.'CLI'.DS.'create_file'.DS.'modelos'.DS.'model_2.php');

		$alias = $this->alias;


    	foreach ($this->fields as $field) {
        	$fld[] = "\t\t'{$field['Field']}' => null";
        	
        	if (strpos($field['Field'],'dominio') !== FALSE) {
        		$f[] = "\t\t \$q->where('{$alias}.{$field['Field']}',\$dominio['id']);";    
        	}elseif (strpos($field['Type'],'date') !== FALSE) {
        		$p[] = "\t\t'f_{$field['Field']}1' => null";	
        		$p[] = "\t\t'f_{$field['Field']}2' => null";	
        		
        		$f[] = "\t\t if(!empty(\$p['f_{$field['Field']}1'])) \$q->where('`{$alias}`.{$field['Field']}','>=',\util::datasql(\$p['f_{$field['Field']}1']));";

        		$f[] = "\t\t if(!empty(\$p['f_{$field['Field']}2'])) \$q->where('`{$alias}`.{$field['Field']}','<=',\util::datasql(\$p['f_{$field['Field']}2']));";

    //     		$f[] = "\t\t".'if(!empty($p[\'f_'.$field['Field'].'1\'])) $q->where("`'.$alias.'`.`'.$field['Field'].'` >= \'".util::datasql($p[\'f_'.$field['Field'].'1\'])."\'")  ;';
				// $f[] = "\t\t".'if(!empty($p[\'f_'.$field['Field'].'2\'])) $q->where("`'.$alias.'`.`'.$field['Field'].'` <= \'".util::datasql($p[\'f_'.$field['Field'].'2\'])."\'")  ;'.PHP_EOL;


        	}elseif (
        		strpos($field['Type'], "int") !== FALSE ||
        		strpos($field['Type'], "tinyint")  !== FALSE ||
        		strpos($field['Type'], "smallint")  !== FALSE ||
        		strpos($field['Type'], "mediumint")  !== FALSE ||
        		strpos($field['Type'], "bigint")  !== FALSE ||
        		strpos($field['Type'], "bit")  !== FALSE ||
        		strpos($field['Type'], "float")  !== FALSE ||
        		strpos($field['Type'], "double")  !== FALSE ||
        		strpos($field['Type'], "decimal") 
        	) {

        		//$f_busca_num[] = " `{$alias}`.{$field['Field']} = '{{$f_busca_num_string}}' ";

        		$f[] = "\t\t if(is_numeric(\$p['f_{$field['Field']}'])) \$q->where('`{$alias}`.{$field['Field']}',\$p['f_{$field['Field']}']);";

	        	$f[] = "\t\t elseif(is_array(\$p['f_{$field['Field']}'])  && !empty(\$p['f_{$field['Field']}'])) \$q->whereIn('`{$alias}`.{$field['Field']}',\$p['f_{$field['Field']}']);";

	        	$p[] = "\t\t'f_not_{$field['Field']}' => null";

        		$f[] = "\t\t if(is_numeric(\$p['f_not_{$field['Field']}'])) \$q->where('`{$alias}`.{$field['Field']}','!=',\$p['f_not_{$field['Field']}']);";

	        	$f[] = "\t\t elseif(is_array(\$p['f_not_{$field['Field']}']) && !empty(\$p['f_not_{$field['Field']}'])) \$q->whereInNot('`{$alias}`.{$field['Field']}',\$p['f_not_{$field['Field']}']);";


        	
        	}else{
        		//$f_busca[] = " `{$alias}`.{$field['Field']} LIKE '%{{$f_busca_string}}%'";
        		if(strpos($field['Field'],'_ids') !== FALSE){
        			$param = str_replace('_ids', '',$field['Field']);
					$f[] = "\t\t if(!empty(\$p['f_{$param}'])) \$q->findInSet(\$p['f_{$param}'],'`{$alias}`.{$field['Field']}');";        		    			
        		}else{

        			$f[] = "\t\t if(!empty(\$p['f_{$field['Field']}'])) \$q->where('`{$alias}`.{$field['Field']}',\$p['f_{$field['Field']}']);";
        		}


        	}

        	
        	if(strpos($field['Field'],'_ids') !== FALSE)
        		$p[] = "\t\t'f_{$param}' => null";        		
        	else
        		$p[] = "\t\t'f_{$field['Field']}' => null";

    	}


    	// if (!empty($f_busca_num)) 
    	// 	$f_busca_num = join(' OR '.PHP_EOL,$f_busca_num);	
    	
    	// if (!empty($f_busca)) 
    	// 	$f_busca = join(' OR '.PHP_EOL,$f_busca);	


    	//$f[] = str_replace(['{f_busca}','{f_busca_num}'],[$f_busca,$f_busca_num],$busca);

    	$p[] = "\t\t 'f_busca' => null";
    	$p[] = "\t\t 'first' => false";
    	$p[] = "\t\t 'debug' => false";
    	$p[] = "\t\t 'limite' => null";
    	$p[] = "\t\t 'qtde_por_pagina' => 50";
    	$p[] = "\t\t 'count'  => false";
    	$p[] = "\t\t 'pg'  => 1";
    	$p[] = "\t\t 'ini'  => 0";
    	$p[] = "\t\t 'indice'  => NULL";    	
    	$p[] = "\t\t 'ordem'  => '{$alias}.id'";    	
    	$p[] = "\t\t 'select'  => null";    	
    	$p[] = "\t\t 'count'  => false";    	
    
    	$fields = 'public static $fields = ['."\n".join(",".PHP_EOL,$fld)."\n\t".'];';
    	$param = 'public static $param = ['."\n".join(",".PHP_EOL,$p)."\n\t".'];';
    

    	$filtros = join(PHP_EOL.PHP_EOL,$f);

		return str_replace(
			[
				'{{name_capitalize}}',
				'{{name}}' ,
				'{{fields}}',
				'{{param}}',
				'{{filtros}}',
				'{{alias}}',
				'{{table}}'
			],
			[ 
				ucfirst($this->name),
				$this->name,
				$fields,
				$param,	
				$filtros,
				$this->alias,
				$this->table
			],
			$model
		);
	}


	public function module($path,$module){
		return $this->file($path,$this->$module());
	}

	public function file($path,$content)
	{
		$file = @fopen($path, "w+");
	    @fwrite($file,$content);
	    @fclose($file);		
	}

	public function ler($arquivo){
	    //Variável arquivo armazena o nome e extensão do arquivo.
	    
	     
	    //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
	    $fp = fopen($arquivo, "r");
	 
	    //Lê o conteúdo do arquivo aberto.
	    $conteudo = fread($fp, filesize($arquivo));
	     
	    //Fecha o arquivo.
	    fclose($fp);
	     
	    //retorna o conteúdo.
	    return $conteudo;
	}



}

 ?>