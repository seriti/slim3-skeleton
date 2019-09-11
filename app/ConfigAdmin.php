<?php 
namespace App;

use Psr\Container\ContainerInterface;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\SITE_NAME;
use Seriti\Tools\URL_CLEAN;
use Seriti\Tools\Secure;

class ConfigAdmin
{
    
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $user = $this->container->user;
        $menu = $this->container->menu;
        
        //default access levels=['GOD','ADMIN','USER','VIEW']
        $minimum_level = 'VIEW';

        $redirect_route = 'login';
        $zone = 'ADMIN';
        $valid = $user->checkAccessRights($zone);

        if($valid) {
            $this->container->mysql->setAuditUserId($user->getId());
            Secure::checkReferer(BASE_URL);
            //$user->level must be >= minimum level
            $valid = $user->checkUserAccess($minimum_level);
            //delete user session,tokens,cookies
            if(!$valid) $user->manageUserAction('LOGOUT');
        }    

        if(!$valid) return $response->withRedirect('/'.$redirect_route);
        
        $system = []; //can specify any GOD access system menu items
        $options['logo_link'] = BASE_URL.'admin/dashboard';
        $options['active_link'] = URL_CLEAN;
        $menu_html = $menu->buildMenu($system,$options);
        $this->container->view->addAttribute('menu',$menu_html);

        $response = $next($request, $response);
        
        return $response;
    }
}