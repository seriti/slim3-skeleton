<?php
namespace App\Customise;

use App\Customise\PdfSetup;
use Psr\Container\ContainerInterface;
use Seriti\Tools\TABLE_MENU;

class PdfSetupController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $template['title'] = MODULE_LOGO.'Admin PDF customise';
        if($this->container->user->getAccessLevel() !== 'GOD') {
            $template['html'] = '<h1>Insufficient access rights!</h1>';
        } else {
            $setup = new PdfSetup($this->container->mysql);
            $html = $setup->process();

            $template['html'] = $html;
            $template['javascript'] = $setup->getJavascript();
        }
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}