<?php 

/**
 * summary
 */
class ElTable extends ElUtil
{
    
    public static function th(){
    	
    	return function($field){
            
            if ($field['Field'] === 'data_cadastro' || $field['Field'] === 'data_atualizacao' || $field['Field'] === 'etiquetas_ids') 
                return;


    		$beautyWord = self::beautyWord($field['Field']);
			
			$nowDoc = "tpl::th('{{beautyWord}}').";
		
			return str_replace("{{beautyWord}}",ltrim($beautyWord),$nowDoc);
    	};
    }

    public function trEtiquetas($table){
        return '.etiquetas::trEtiquetas($reg,"'.$table.'")';
    }

    public static function td(){
        $real = [
            'float',
            'double',
            'decimal'
        ];

    	return 
    	function($field) use ($real){
            if ($field['Field'] === 'data_cadastro' || $field['Field'] === 'data_atualizacao' || $field['Field'] === 'etiquetas_ids') 
                return;

            if ($field['Field'] === 'status') 
                return 'tpl::td((int)$reg["status"] ? "Ativo" : "Inativo").';

            if ($field['Type'] === 'date') 
                return 'tpl::td(util::databr($reg["'.$field['Field'].'"])).';                

            if ($field['Type'] === 'datetime') 
                return 'tpl::td(util::parseDate($reg["'.$field['Field'].'"]),"[\'databr\'] [\'horario\']" ).';                

            if (in_array($field['Type'],$real)) 
                return 'tpl::td(util::reais($reg["'.$field['Field'].'"]),"[\'databr\'] [\'horario\']" ).';                                
            
          
			return 'tpl::td($reg["'.$field['Field'].'"]).';	
        };
    }

}
