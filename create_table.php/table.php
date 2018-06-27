<?php 
require_once('configs/config_banco.php');
require_once('funcoes/fn_consulta.php');
require_once("/create_file/create.php");
require_once('vendor/autoload.php');
require_once('funcoes/fn_start.php');


class Table extends CONFIG_BANCO
{
	public static $instance = FALSE;

	public function getConfigBanco($op){
		return $this->servidores[$op];
	}
    
	public function getInstance(){
		try {
			if (self::$instance === FALSE) 
				self::$instance = (new conecta)->pdo();
			
			return self::$instance;
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function analizar($table){
		$columns = QB::table($table)->getColumns();
		$name_database = $this->getConfigBanco('dev');

		$tables = QB::table('information_schema.tables')->where('table_schema','=',$name_database)->get();
		
		if (file_exists('/tables.json')) {
			
		}



	}






} ?>