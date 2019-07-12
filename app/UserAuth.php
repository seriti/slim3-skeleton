<?php 
namespace App;

use Psr\Container\ContainerInterface;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\SITE_NAME;
use Seriti\Tools\Secure;

class UserAuth
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
        $route = '' ; //passed by reference below
        $valid = $user->checkAccessRights($route);

        $this->container->mysql->setAuditUserId($user->getId());

        Secure::checkReferer(BASE_URL);

        if(!$valid) return $response->withRedirect('/'.$route);
        
        $response = $next($request, $response);
        
        return $response;
    }
}