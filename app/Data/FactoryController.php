<?php
namespace App\Data;

use Psr\Container\ContainerInterface;
use App\Data\Factory;

//must be called from same server 
if($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' AND $_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']) die('INVALID FACTORY ACCESS!'.$_SERVER['REMOTE_ADDR'].' !== '.$_SERVER['SERVER_ADDR']);

//NB: Use to setup basic classes for a modules database tables. You will need to edit class files extensively but most of boiler plate is done for you.
class FactoryController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        //see src/setup_app.php for module names
        $module_id = 'store';
        $factory = new Factory($this->container->mysql,$this->container,$module_id);
        
        $html = $factory->process();
        
        $template['html'] = $html;
        $template['title'] = strtoupper($module_id).' : Module database table class factory';

        return $this->container->view->render($response,'admin.php',$template);
    }
}