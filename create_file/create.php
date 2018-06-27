<?php 


/**
 * Class Create
 */
class Create 	
{
    public static $data = [];

    public static function beautyWord($word){
		return 
		ucfirst(
			str_replace(
				['sao','cao','coes','dominio_id','_ids','_id','_' , '-',],
				['são','ção','ções','domínio'	 ,	''	, ''  ,' ' , ' ' ], 

				$word)
		);
    }
	public static function mkClass($name="teste",$table="")
	{
		

		$name = util::toCamelCase($name);

		$ths = '';
		$table = !empty($table) ? $table : $name;
		$alias = $table[0];
		$fields = QB::table($table)->getColumns();

		foreach ($fields as $field) {
			
			$beautyWord = self::beautyWord($field['Field']);
			$nowDoc = 
<<<'EOT'
							tpl::th('{{beautyWord}}').
EOT;
			$th[] = str_replace("{{beautyWord}}",ltrim($beautyWord),$nowDoc);

		}

		$ths = tpl::join($th);


		$tds = tpl::join(
			array_map(function($field){
				return 'tpl::td($reg["'.$field['Field'].'"]).';	
			},$fields)
		);

		$class = 
<<<'EOT'
<?php
class {{name}} extends {{name}}Model{

	public static function tabelar($recordset=[],$param=[]){

		return 
		!empty($recordset) ? 
			tpl::a('/painel/{{name}}/add','Adicionar','fa fa-plus',['class'=>'pull-right btn btn-primary btn-ajax']).
			tpl::div(
				tpl::table(
					tpl::thead(
						tpl::tr(
							{{ths}}
							tpl::th('menu')
						)
					).
					tpl::tbody(
						!empty($recordset) ? 
						tpl::join(
							array_map("self::tr",$recordset)
						) : NULL
					)
				)
			,['class'=>'table-responsive'])

			: util::semRegistros('Nenhum registro encontrado','/painel/{{name}}/add/');


	}

	public static function tr($reg=[],$p=""){	

		return 
		tpl::tr(
			{{tds}}
			tpl::td(self::menu($reg,$p))
			
		);
	}

	public static function menu($reg=""){
		$btn = [];
		
		if(user::temPermissao('{{name}}-edit')){
			$btn[] = tpl::btnWarning(tpl::i('fa fa-edit'),[
					'class'=>'btn-ajax margin-right-5 btn-sm',
					'data-url'=>"/painel/{{name}}/editar/{$reg['id']}/"
			]);				
		}
		if(user::temPermissao('{{name}}-edit-del')){
			$btn[] = tpl::btnDanger(tpl::i('fa fa-trash'),[
					'class'=>'btn-ajax btn-sm',
					'data-url'=>"/painel/{{name}}/excluir/{$reg['id']}/"
				]);
		}

		return tpl::join($btn);
	}	

	public static function form($reg=[],$param=[]){

		$reg = !empty($reg) ? $reg : {{name}}::getFields();

		{{body}}

		return 
		Form::create(['action'=>'/painel/{{name}}/salvar/','class'=>'form form-ajax','method'=>'POST'],
			$body,
		'Adicionar','{{name}}');

	}
}

EOT;


		$body = tpl::join(self::makeForm($fields));
		

		return str_replace(['{{name}}','{{ths}}','{{body}}','{{tds}}'], [ $name ,$ths ,$body ,$tds], $class);
	}

	public function makeForm($dados=""){

		$strpos_array = function($haystacks, $needle){
          foreach ($haystacks as $haystack)
            if(strpos($haystack,$needle) !== FALSE) return true;        

          return false;
      	};
          


		return array_map(function($data) use ($strpos_array){
 
			$real = [
				'float',
				'double',
				'decimal'
			];

			$label = self::beautyWord($data['Field']);

			if (strpos($data['Type'],'int') !== FALSE && strpos($data['Field'],'id') !== FALSE) {
				return '$body[] = Form::input_hidden("'.$data['Field'].'",$reg["'.$data['Field'].'"]); ';
				
			}
			elseif (strpos($data['Type'],'int') !== FALSE && strpos($data['Field'],'id') === FALSE) {
				return '
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_numero("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,4);

				';
			}

			
			elseif ($strpos_array($real,$data['Type'])) {
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_moeda("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,4);

				';
			}elseif (strpos($data['Type'],'varchar') !== FALSE && !strpos($data['Field'],'_ids') !== FALSE) {
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_text("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,8);

				';
			}
			elseif (strpos($data['Field'],'_ids') !== FALSE) {

				$control = str_replace("_ids",'',$data['Field']);

				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::selectOptions([],$reg["'.$data['Field'].'"],"'.$data['Field'].'",[
						"data-ajax--url"=>"/ajax/selects/'.$control.'/",
						"data-ajax--data-type"=>"json",
						"data-ajax--delay"=>"250"
					])
				,6);

				$body[] = tpl::script("app.init(\'chosen\')");

				';


			}
			elseif (strpos($data['Type'],'text') !== FALSE) {
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::textarea("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,12);

				';
			}elseif (
				strpos($data['Type'],'datetime') !== FALSE && 
				$data['Field'] !== 'data_atualizacao'  && 
				$data['Field'] !== 'data_cadastro'
			) 
			{
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_datetimepicker("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,12);
				$body[] = tpl::script("app.init(\'datepicker\')");
				';
			}elseif (
				strpos($data['Type'],'date') !== FALSE && 
				$data['Field'] !== 'data_atualizacao'  && 
				$data['Field'] !== 'data_cadastro'
			) 
			{
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_datepicker("'.$data['Field'].'",$reg["'.$data['Field'].'"])
				,12);
				$body[] = tpl::script("app.init(\'datepicker\')");
				';
			}


		}, $dados);


	}	

	public function mkModel($name,$table="")
	{

		$name = util::toCamelCase($name);

		$class = <<<'EOT'
<?php
/******************************************************************************
 *   	Model {{name}} 
 *****************************************************************************/

class {{name}}Model {

	{{fields}}

	{{param}}
	
	public function consulta($param=[]){
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

	public function get($id){
		if (is_numeric($id)) 
			return self::consulta(['f_id'=>$id,'first'=>true,'qtde_por_pagina'=>false]);
		elseif (is_array($id)) 
			return self::consulta(['f_id'=>$id,'qtde_por_pagina'=>false,'count'=>false]);
		
		throw new Exception('Id must be Integer or Array gived '.var_dump($id),1);

	}

	public function getFields(){
		return self::$fields;
	}

	public function getParams(){
		return self::$param;
	}

	public function salvar($dados=[],$param=[]){

		global $dominio;

		$dados = !empty($dados) ? $dados : $_POST;

		$bdo = new bdo('{{table}}');

		$dados['dominio_id'] = !empty($dados['dominio_id']) ? $dados['dominio_id'] : $dominio['id'];

		$bdo->setValid('nome',3,'Informe um nome para a tarefa com pelo menos 3 letras');							

		if (empty($dados['id'])) {

			$dados['data_cadastro'] = date('Y-m-d');

			$bdo->setCampo($dados);

			if ( !empty($id_add  = $bdo->adicionar()) ) {
				$out['sucesso'] = true;				 		
				$out['dados'] = $dados;
				$out['dados']['id'] = $id_add;
			}

		}elseif (!empty($dados['id']) && is_numeric($dados['id']) ) {

			$dados['data_atualizacao'] = date('Y-m-d');

			$bdo->setCampo($dados);
			$bdo->setId($dados['id']);
			
			if ($bdo->atualizar()) {
				$out['sucesso']  = true;					
				$out['dados'] = $dados;
			}	


		}

		return $out;
	}


}

EOT;

		$table = !empty($table) ? $table : $name;
		$alias = $table[0];
		$fields = QB::table($table)->getColumns();


    	foreach ($fields as $field) {
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
        		$f[] = "\t\t".'if(is_numeric($p["f_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' = "\'.$p["f_'.$field['Field'].'"].\'"  \';' ;
	        	$f[] = "\t\t".'elseif(is_array($p["f_'.$field['Field'].'"])) $f[]="`'.$alias.'`.'.$field['Field'].' IN (\'".join("\',\'",$p[\'f_'.$field['Field'].'\'])."\')";'.PHP_EOL;

	        	$p[] = "\t\t'f_not_{$field['Field']}' => null";

        		$f[] = "\t\t".'if(is_numeric($p["f_not_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' != "\'.$p["f_'.$field['Field'].'"].\'"  \';' ;
	        	$f[] = "\t\t".'elseif(is_array($p["f_not_'.$field['Field'].'"])) $f[]="`'.$alias.'`.'.$field['Field'].' NOT IN (\'".join("\',\'",$p[\'f_'.$field['Field'].'\'])."\')";'.PHP_EOL;


        	
        	}else{
	        	$f[] = "\t\t".'if(!empty($p["f_'.$field['Field'].'"])) $f[] = \' `'.$alias.'`.'.$field['Field'].' = "\'.$p["f_'.$field['Field'].'"].\'"  \';'.PHP_EOL;       		
        	}

        	$p[] = "\t\t'f_{$field['Field']}' => null";

    	}


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
			[ $name 	,$fields 	 ,$param	 ,	$filtros   ,$alias	,	$table],
			$class
		);
	}

	


	public function mkCtrl_p($name)
	{

		$name = util::toCamelCase($name);

		$class = <<<'EOT'
<?php
/******************************************************************************
 *   	Control {{name}} 
 *****************************************************************************/

class {{name}}Control extends appControl {


	public function __construct(){
		global $config;

    	user::autenticado(true);
    	user::temPermissao('{{name}}',true);

    	$this
    		->titulo($config['nomebase'].' -> Gerenciamento de {{nameCapitalaze}}')
    		->tituloConteudo("{{nameCapitalaze}}","fa fa-list");
	}


	public function index($param=[]){
		$this->tabelar();

	}
	public function form($reg=""){
		$form = {{name}}::form($reg);


		if (util::isAjax()) {
			$out['modal_id'] = "{{name}}";
			$out['modal'] = $form;
			$out['init'] = true;
			$this->json($out);
		}else{
			$this->view($form);	
		}
	}
	public function add(){
		$this->form();
	}
	public function editar($id){
		if(is_numeric($id)){
			$reg = {{name}}::get($id);
		}else{
			direcionar("","/painel/");
		}
		$this->form($reg);
	}
	public function salvar($reg=[]){
		$dados = !empty($reg) ? $reg : $_POST;

		$out = {{name}}::salvar($dados);

		if (!empty($out['sucesso'])) {
			$out['direciona'] = "/painel/{{name}}/";
			$out['msg'] = bd::getMsg();
		}else{
			$out['msg'] = bd::getMsgJson();
		}

		$this->json($out);
	}

	public function excluir($id){
		$msg = "Registro excluído com sucesso";
		if(bd::excluir((int)$id,"{{name}}","id")){
			if (util::isAjax()) {
				return $this->json(['direciona'=>'/painel/{{name}}/tabelar/','msg'=>$msg]);
			}
			direciona($msg,"/painel/{{name}}/tabelar");
		}
	}
	
	public function tabelar(){
		$p = {{name}}::getParams();
		${{name}} = {{name}}::consulta();


		$total_{{name}} = ${{name}}['total'];
		unset(${{name}}['total']);

		$this->view({{name}}::tabelar(${{name}}));
		$this->view(paginacao($total_{{name}},$p['qtde_por_pagina'],$p['qtde_por_pagina']));

	}


}

EOT;
		

		return str_replace(['{{name}}','{{nameCapitalaze}}'],[ $name,ucfirst($name)], $class);
	}


	public function file($path,$content)
	{
		$file = @fopen($path, "w+");
	    @fwrite($file,$content);
	    @fclose($file);		
	}


}

 ?>