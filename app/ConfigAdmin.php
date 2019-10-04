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
        //default access levels=['GOD','ADMIN','USER','VIEW']
        $minimum_level = 'VIEW';
        $redirect_route = 'login';
        $zone = 'ADMIN';
        //will return false unless a user is logged in with access >= minimum level and zone = ALL or ADMIN and status <> HIDE
        $valid = $user->checkAccessRights($zone);

        //after user logged in
        $menu = $this->container->menu;

        if($valid) {
            $this->container->mysql->setAuditUserId($user->getId());
            Secure::checkReferer(BASE_URL);
            //$user->level must be valid level and >= minimum level
            $valid = $user->checkUserAccess($minimum_level);

            //check current route is valid for user  
            if($valid) $valid = $menu->checkRouteAccess(URL_CLEAN);

            //delete user session,tokens,cookies
            if(!$valid) $user->manageUserAction('LOGOUT');
        }    

        if(!$valid) return $response->withRedirect('/'.$redirect_route);
        
        $system = []; //can specify any GOD access system menu items
        $menu_options['logo_link'] = BASE_URL.'admin/dashboard';
        $menu_options['active_link'] = URL_CLEAN;
        $menu_html = $menu->buildMenu($system,$menu_options);
        $this->container->view->addAttribute('menu',$menu_html);

        $response = $next($request, $response);
        
        return $response;
    }
}