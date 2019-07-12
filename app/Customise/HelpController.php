<?php
namespace App\Customise;

use Psr\Container\ContainerInterface;
use App\Customise\Help;

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
        
        $table = new Help($this->container->mysql,$this->container,$table);

        $table->setup();
        $html = $table->processTable();
        
        $template['html'] = $html;
        $template['title'] = 'Help Content';
        
        return $this->container->view->render($response,'table.php',$template);
    }
}