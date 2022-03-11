<?php
namespace App\Data;

use Psr\Container\ContainerInterface;

use App\Data\SetupData;

class SetupDataController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $html = '';
        
        //First check for all framework tables and creates any that are missing
        $setup = new \Seriti\Tools\Setup($this->container['config']);
        $html .= $setup->viewOutput();

        //Now check for any updates to standard framework tables or custom system tables
        //NB: not a conventional module like those defined in src/setup_app.php 
        $module = ['name'=>'System','table_prefix'=>'sys']; 
        //this would normally be defined in module config class 
        define('TABLE_PREFIX',$module['table_prefix']);
        
        $setup_add = new SetupData($this->container->mysql,$this->container->system,$module);
        $setup_add->setupSql();

        $html .= $setup_add->process();
        
        $template['html'] = $html;
        $template['title'] = 'System data configuration';
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}