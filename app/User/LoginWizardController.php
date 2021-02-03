<?php
namespace App\User;

use Psr\Container\ContainerInterface;

use Seriti\Tools\Template;

use App\User\LoginWizard;
      
class LoginWizardController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $cache = $this->container->cache;
        $user = $this->container->user;

        //use temp token to identify user for duration of wizard
        $user_specific = false;
        $cache_name = 'login_wizard'.$user->getTempToken();
        $cache->setCache($cache_name,$user_specific);

        $wizard_template = new Template(BASE_TEMPLATE);
        
        $wizard = new LoginWizard($this->container->mysql,$this->container,$cache,$wizard_template);
        $wizard->setup();
        
        $html = $wizard->process();

        //$template['title'] = 'Login';
        $template['html'] = $html;
        
        
        return $this->container->view->render($response,'public.php',$template);
    }
}