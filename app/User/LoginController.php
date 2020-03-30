<?php
namespace App\User;

use Psr\Container\ContainerInterface;

//use Seriti\Tools\TABLE_AUDIT;

class LoginController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $user = $this->container->user;
        $html = $user->processLogin();

        $template['html'] = $html;
        $template['title'] = 'LOGIN';
        return $this->container->view->render($response,'login.php',$template);
    }
}