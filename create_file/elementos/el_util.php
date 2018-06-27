<?php /**
 * summary
 */
class ElUtil
{
    public static function beautyWord($word){
		return 
		ucfirst(
			str_replace(
				['sao','cao','coes','dominio_id','_ids','_id','_' , '-',],
				['são','ção','ções','domínio'	 ,	''	, ''  ,' ' , ' ' ], 

				$word)
		);
    }


    public function hasEtiquetas($fields){
        $isEtiqueta = function($data) {
            return $data['Field'] === 'etiquetas_ids';
       };
       
       return !empty(array_filter($fields,$isEtiqueta)) ? true : false;
    	
    }


} ?>