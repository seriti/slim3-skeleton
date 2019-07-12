<?php
namespace App\Customise;

use App\Customise\SiteSetup;
use Psr\Container\ContainerInterface;
use Seriti\Tools\BASE_URL;

class SiteSetupController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $module = $this->container->config->get('module','custom');  

        $template['title'] = MODULE_LOGO.'Admin site customise'; 
        if($this->container->user->getAccessLevel() !== 'GOD') {
            $template['html'] = '<h1>Insufficient access rights!</h1>';
        } else {  
            $setup = new SiteSetup($this->container->mysql,$this->container,$module);  
            $setup->setup();
            $html = $setup->processSetup();

            $template['html'] = $html;
            
        }    

        return $this->container->view->render($response,'table.php',$template);
    }
}