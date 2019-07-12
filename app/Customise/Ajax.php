<?php
namespace App\Customise;

use Psr\Container\ContainerInterface;
use Seriti\Tools\Secure;


class Ajax
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $mode = '';
        $output = '';

        if(isset($_GET['mode'])) $mode = Secure::clean('basic',$_GET['mode']);

        if($mode === 'menu') $output = $this->getMenuRoutes();

        return $output;
    }

    protected function getMenuRoutes()
    {
        $output = '';

        $menu_type = Secure::clean('string',$_POST['menu_type']);
        //$menu_type = 'LINK_CUSTOM';
          
        $links = [];
        
        switch($menu_type) {
            case 'LINK_SYSTEM':
                $links['admin/dashboard'] = 'Home dashboard';
                $links['admin/help'] = 'Help';
                $links['admin/user'] = 'Admin users';
                $links['admin/audit'] = 'Audit trail';
                break;
            default:
                $modules = $this->container->config->get('module');  
                $module_id = strtolower(substr($menu_type,5)); 
                if(isset($modules[$module_id])) {
                    $module = $modules[$module_id];
                    $routes = $module['route_list'];
                    foreach($routes as $route=>$title) {
                        $links[$module['route_root'].$route] = $title;
                    }    
                } 
        }
                    
        if(count($links) === 0) {
            $output = 'ERROR';
        } else {
            $output = json_encode($links);    
        }    

        return $output;

    }
}