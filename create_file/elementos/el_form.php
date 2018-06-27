<?php 


class ElForm extends ElUtil
{
    

	public function makeFieldsForm($isBusca = false){

		$strpos_array = function($haystacks, $needle){
          foreach ($haystacks as $haystack)
            if(strpos($haystack,$needle) !== FALSE) return true;        

          return false;
      	};

		return 
		function($data) use ($strpos_array,$isBusca){
 
			$real = [
				'float',
				'double',
				'decimal'
			];

			$prefixo = $isBusca ? 'f_' : '';

			$label = self::beautyWord($data['Field']);

			if (strpos($data['Type'],'int') !== FALSE && strpos($data['Field'],'id') !== FALSE) {
				return '$body[] = Form::input_hidden("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"]); ';
			}

			elseif ($data['Field'] === 'status') {

				return '	
				$body[] =
				Form::grid( 
					Form::label("'.$label.'").
					Form::selectOptions("statusgeral",$reg["'.$prefixo.$data['Field'].'"],"'.$prefixo.$data['Field'].'")
				,2); ';
			}


			elseif (strpos($data['Type'],'int') !== FALSE && strpos($data['Field'],'id') === FALSE) {
				return '
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_numero("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
				,4);

				';
			}

			
			elseif ($strpos_array($real,$data['Type'])) {
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_moeda("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
				,4);

				';
			}elseif (strpos($data['Type'],'varchar') !== FALSE && !strpos($data['Field'],'_ids') !== FALSE) {
				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::input_text("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
				,12);

				';
			}
			elseif (strpos($data['Field'],'_ids') !== FALSE) {

				$control = str_replace("_ids",'',$data['Field']);

				return 
				'
				$body[] =
				Form::grid(
					Form::label("'.$label.'").
					Form::selectOptions([],$reg["'.$prefixo.$data['Field'].'"],"'.$prefixo.$data['Field'].'",[
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
					Form::textarea("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
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
					Form::input_datetimepicker("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
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
					Form::input_datepicker("'.$prefixo.$data['Field'].'",$reg["'.$prefixo.$data['Field'].'"])
				,12);
				$body[] = tpl::script("app.init(\'datepicker\')");
				';
			}

		};

	}


	public function makeForm($dados="",$isBusca=FALSE){
		return tpl::join(array_map(self::makeFieldsForm($isBusca), $dados));
	}	

	public function makeFormBusca($dados=""){
		return tpl::join(array_map(self::makeFieldsForm(TRUE), $dados));
	}	



} ?>