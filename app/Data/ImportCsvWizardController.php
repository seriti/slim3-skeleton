<?php
namespace App\Data;

use Psr\Container\ContainerInterface;

use App\Data\ImportCsvWizard;

use Seriti\Tools\Template;
use Seriti\Tools\BASE_TEMPLATE;

class ImportCsvWizardController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $cache = $this->container->cache;
        $user_specific = true;
        $cache->setCache('data_import_wizard',$user_specific);

        $wizard_template = new Template(BASE_TEMPLATE);


        //$table = TABLE_PREFIX.'transact';
        //$import = New BankImport($this->container->mysql,$this->container,$table);
        
        $wizard = new ImportCsvWizard($this->container->mysql,$this->container,$cache,$wizard_template);
        
        $param = [];
        //NB: If no tables specified will get ALL database tables
        //$param['tables'] = ['table1_name'=>'Table1 description','table2_name'=>'Table2 description'];        
        $wizard->setup($param);
        //$wizard->addImport($import);

        $html = $wizard->process();

        $template['html'] = $html;
        $template['title'] = 'CSV Data import wizard';
        //$template['javascript'] = $dashboard->getJavascript();

        return $this->container->view->render($response,'admin.php',$template);
    }
}