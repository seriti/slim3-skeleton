<?php
namespace App\Data;

use Seriti\Tools\ENCRYPT_ROUTE;

use Psr\Container\ContainerInterface;
use App\Data\Encrypt;

class EncryptController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $secure = new Encrypt($this->container->mysql,$this->container);
        $html = $secure->process();

        $menu = $this->container->menu;
        
        $system = []; //can specify any GOD access system menu items
        $options['logo_link'] = BASE_URL.'admin/user/dashboard';
        $options['active_link'] = ENCRYPT_ROUTE;
        $menu_html = $menu->buildMenu($system,$options);
        $this->container->view->addAttribute('menu',$menu_html); 

        $template['html'] = $html;
        $template['title'] = 'Encryption key configuration';

        return $this->container->view->render($response,'admin.php',$template);
    }
}