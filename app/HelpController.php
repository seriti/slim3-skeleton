<?php
namespace App;

use Psr\Container\ContainerInterface;
use App\Help;

class HelpController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        
        $module = $this->container->config->get('module','custom');
        $table = $module['table_prefix'].'help';
        $help = new Help($this->container->mysql,$this->container,$table);
        
        $html = $help->getHelp();
        
        $template['html'] = $html;
        $template['title'] =  SITE_NAME.': Help topics';
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}