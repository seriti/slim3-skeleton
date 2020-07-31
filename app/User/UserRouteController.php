<?php
namespace App\User;

use Psr\Container\ContainerInterface;

use Seriti\Tools\TABLE_USER;
use App\User\UserRoute;


class UserRouteController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        if($this->container->user->getAccessLevel() !== 'GOD') {
            $template['html'] = '<h1>Insufficient access rights!</h1>';
        } else {  
            $table_name = 'user_route'; 
            $table = new UserRoute($this->container->mysql,$this->container,$table_name);

            $table->setup();
            $html = $table->processTable();
            
            $template['html'] = $html;
            //$template['title'] = 'User: Route access';
        }    
        
        return $this->container->view->render($response,'admin_popup.php',$template);
    }
}