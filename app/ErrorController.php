<?php
namespace App;

use Psr\Container\ContainerInterface;

//use Seriti\Tools\TABLE_AUDIT;

class ErrorController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $html = '';
        /* should i show any menu??
        $menu = $this->container->menu;
                     
        $system = []; //can specify any GOD access system menu items
        $options['logo_link'] = BASE_URL.'admin/dashboard';
        $menu_html = $menu->buildMenu($system,$options);
        $this->container->view->addAttribute('menu',$menu_html); 
        */
        $title = '<h1>System Error enountered</h1>';

        if(isset($_SESSION['seriti_error'])) {
            $error = $_SESSION['seriti_error'];

            $html .= '<h2>'.$error['title'].'</h2>';
            $html .= '<p>'.$error['message'].'</p>';
        } else {
            $html .= '<h2>An unknown error occurred please contact support</h2>';
        }

        $template['html'] = $html;
        $template['title'] = $title;
            

        return $this->container->view->render($response,'admin.php',$template);
    }
}