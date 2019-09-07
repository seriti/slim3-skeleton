<?php
namespace App\Customise;

use App\Customise\AdminMenu;
use Psr\Container\ContainerInterface;
use Seriti\Tools\TABLE_MENU;

class AdminMenuController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $template['title'] = MODULE_LOGO.'Admin menu customise';
        
        if($this->container->user->getAccessLevel() !== 'GOD') {
            $template['html'] = '<h1>Insufficient access rights!</h1>';
        } else {  
            $module = $this->container->config->get('module','custom');        
            $table = $module['table_prefix'].'menu';

            $tree = new AdminMenu($this->container->mysql,$this->container,$table);

            $param = ['row_name'=>'menu-item','col_label'=>'title'];
            $tree->setup($param);
            $html = $tree->processTree();
            
            $template['html'] = $html;
            
            $template['javascript'] = $tree->getJavascript();
        }    
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}