<?php
namespace App;

use Psr\Container\ContainerInterface;
use App\User;
use Seriti\Tools\TABLE_USER;

class UserController
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
            $table = new User($this->container->mysql,$this->container,TABLE_USER);
            $table->setup();
            $html = $table->processTable();
            
            $menu = $this->container->menu;
            
            $system = []; //can specify any GOD access system menu items
            $options['logo_link'] = BASE_URL.'admin/dashboard';
            $options['active_link'] = 'admin/user';
            $menu_html = $menu->buildMenu($system,$options);
            $this->container->view->addAttribute('menu',$menu_html); 

            $template['html'] = $html;
            $template['title'] = 'Admin users';
        }    

        return $this->container->view->render($response,'table.php',$template);
    }
}