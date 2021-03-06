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
        $table = TABLE_PREFIX.'help';
        
        $table = new Help($this->container->mysql,$this->container,$table);

        $table->setup();
        $html = $table->processTable();
        
        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.' Help content';
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}