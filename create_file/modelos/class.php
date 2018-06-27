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
							tpl::th('Menu')
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
			
		){{trEtiquetas}};
	}

	public static function menu($reg=""){
		$btn = [];
		
		//if(user::temPermissao('{{name}}-edit')){
			$btn[] = tpl::btnWarning(tpl::i('fa fa-edit'),[
					'class'=>'btn-ajax margin-right-5 btn-sm',
					'data-url'=>"/painel/{{name}}/editar/{$reg['id']}/"
			]);				
		//}
		//if(user::temPermissao('{{name}}-edit-del')){
			$btn[] = tpl::btnDanger(tpl::i('fa fa-trash'),[
					'class'=>'btn-ajax btn-sm',
					'data-url'=>"/painel/{{name}}/excluir/{$reg['id']}/",
					'data-confirm'=>'Tem certeza que deseja excluir definitivamente este item?',

				]);
		//}

		return tpl::join($btn);
	}	

	public static function form($reg=[],$param=[]){

		$reg = !empty($reg) ? $reg : {{name}}::getFields();

		$body[] = tpl::wam();

		{{form}}

		return 
		Form::create(['action'=>'/painel/{{name}}/salvar/','class'=>'form form-ajax','method'=>'POST'],
			$body,
		'Adicionar','{{name}}');

	}

	public function formBusca(){
		global $opcoes;

		$get_default = [
			'f_busca' => null,
			{{filtros}}

		];

		$reg = set_param($get_default,$_GET);

		$body[] = 
		Form::grid(
			Form::label("Palavra chave") . 
			Form::input_text("f_busca",$reg['f_busca'])
		,'col-md-12 col-sm-12');

		{{formBusca}}


		return 
		Form::create(['action'=>'/painel/{{name}}/pesquisar/','class'=>'form ','method'=>'GET'],
			$body,
		'Buscar','{{name}}');
	}	

}