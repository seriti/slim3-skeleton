<?php
namespace App\User;

use Psr\Container\ContainerInterface;

use Seriti\Tools\BASE_URL;

use App\User\Dashboard;

class DashboardController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $menu = $this->container->menu;
        $system = []; //can specify any GOD access system menu items
        $options['logo_link'] = BASE_URL.'admin/user/dashboard';
        $menu_html = $menu->buildMenu($system,$options);
        $this->container->view->addAttribute('menu',$menu_html);

        $dashboard = new Dashboard($this->container->mysql,$this->container);
        
        $dashboard->setup();
        $html = $dashboard->viewBlocks();

        $template['html'] = $html;
        $template['title'] = 'Dashboard';
        //$template['javascript'] = $dashboard->getJavascript();

        return $this->container->view->render($response,'admin.php',$template);
    }
}