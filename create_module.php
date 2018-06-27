<?php

require_once 'vendor/autoload.php';
include('configs/config_banco.php');
include('funcoes/fn_start.php');
include("CLI/create_file/new_create.php");
include('funcoes/fn_consulta.php');

/**
 * summary
 */
class createModule {}


$create_module = new Commando\Command();

$create_module->option()
            ->require()
            ->describedAs('Nome do Modulo');

        // Define a flag "-t" a.k.a. "--title"
$create_module->option('m')
    ->aka('model')
    ->describedAs('Criar Apenas o model')    
    ->boolean();

$create_module->option('t')
    ->aka('table')
    ->describedAs("Nome da tabela")    
    ->argument();


$create_module->option('p')
    ->aka('controls_painel')
    ->describedAs('Criar apenas o controls_painel')
    ->boolean();

$create_module->option('l')
    ->aka('controls')
    ->describedAs('Criar apenas o controls')
    ->boolean();

$create_module->option('a')
    ->aka('controls_ajax')
    ->describedAs('Criar apenas o controls_ajax')
    ->boolean();

$create_module->option('c')
    ->aka('class')
    ->describedAs('Criar Apenas o model')
    ->boolean();

$create_module->option('v1')
    ->aka('vmodel1')
    ->describedAs('Usar a 1º versão do model')
    ->boolean();

$create_module->option('off')
    ->aka('off-query')
    ->describedAs('Não fazer a consulta no banco de dados')
    ->boolean();



$name_module = $create_module[0];


$table = !empty($create_module['table']) ? $create_module['table'] : "{$name_module}";

$p['off-query'] = !$create_module['off'];

$create = new Create($name_module,$table,$p);

$path_class = "classes/class_{$name_module}.php";
$path_model = !empty($create_module['v1']) ? "models/{$name_module}_model.php" : "models/{$name_module}.php";
$path_ctrl = "controls_painel/{$name_module}_control.php";


if (
    !$create_module['class'] && 
    !$create_module['m']     && 
    !$create_module['l']     && 
    !$create_module['c']     && 
    !$create_module['p']     &&
    !$create_module['a']
) 
{

    
    if (!file_exists($path_class)) 
        $create->module($path_class,'mkClass');
            

    if (!file_exists($path_model)) 
        $create->module($path_model,!empty($create_module['v1']) ? 'mkModel' : 'mkModel2');
    

    if (!file_exists($path_ctrl)) 
        $create->module($path_ctrl,'mkCtrl_p');

    echo "Todos os arquivos foram criados com sucesso!\n";

}elseif ($create_module['model']) {
    
    if (!file_exists($path_model)) 
        $create->module($path_model,!empty($create_module['v1']) ? 'mkModel' : 'mkModel2');

    echo "O model foi criado com sucesso com sucesso!\n";


}elseif ($create_module['controls_painel']) {
   
    if (!file_exists($path_ctrl)) 
        $create->module($path_ctrl,'mkCtrl_p');

    echo "O controls_painel foi criado com sucesso com sucesso!\n";

}elseif ($create_module['class']) {
   
    if (!file_exists($path_class)) 
        $create->module($path_class,'mkClass');

    echo "O class foi criado com sucesso com sucesso!\n";

}






