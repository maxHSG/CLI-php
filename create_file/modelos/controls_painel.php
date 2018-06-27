<?php
/******************************************************************************
 *   	Control {{name}} 
 *****************************************************************************/

class {{name}}Control extends appControl {


	public function __construct(){
		global $config;

    	user::autenticado(true);
    	//user::temPermissao('{{name}}',true);

    	$this
    		->titulo($config['nomebase'].' -> Gerenciamento de {{nameCapitalaze}}')
    		->tituloConteudo("{{nameCapitalaze}}".tpl::col(Form::busca(['action'=>'/painel/{{name}}/listar/']),'col-md-4 margin-top-10-sm pull-right'),"fa fa-list");
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
		
		if(is_numeric($id))
			$reg = {{name}}::get($id);
		else
			direcionar("","/painel/");
		
		$this->form($reg);
	}


	public function salvar($reg=[]){
		$dados = !empty($reg) ? $reg : $_POST;

		$out = {{name}}::salvar($dados);

		if (!empty($out['sucesso'])) {
			$out['direciona'] = "/painel/{{name}}/";
		}

		$out['msg'] = bd::getMsgJson();
		

		$this->json($out);
	}

	public function excluir($id){
		$msg = "Registro excluído com sucesso";

		if(bd::excluir((int)$id,"{{name}}","id")){
			
			if (util::isAjax()) 
				return $this->json(['direciona'=>'/painel/{{name}}/tabelar/','msg'=>$msg]);
			
			return direciona($msg,"/painel/{{name}}/tabelar");
		}
	}
	
	public function listar(){
		return $this->tabelar();
	}

	public function tabelar(){

		$p = set_param({{name}}::getParams(),$_GET);
		${{name}} = {{name}}::consulta($p);


		$total_{{name}} = ${{name}}['total'];
		unset(${{name}}['total']);

		$this->view({{name}}::tabelar(${{name}}));
		$this->view(paginacao($total_{{name}},$p['qtde_por_pagina'],$p['qtde_por_pagina']));

	}

	public function pesquisar(){
		$this->view({{name}}::formBusca());		
		$this->tabelar();
	}


}
