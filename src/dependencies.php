<?php
//Setup app container global dependancies

$container = $app->getContainer();

// view renderer
$container['view'] = function ($c) {
    $settings = $c->get('settings')['templates'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// seriti tools configuration
$container['config'] = function ($c) {
    return new Seriti\Tools\Config();
};

$container['mysql'] = function ($c) {
	$param = $c->config->get('db');
	$param['audit'] = $c->config->get('audit');
    //$param['debug']=true;
    //return $param;
    return new Seriti\Tools\Mysql($param);
};

$container['mail'] = function ($c) {
    $param = $c->config->get('email');
    $param['debug'] = false; //phpmailer is very verbose in debug mode
    return new Seriti\Tools\Email( new PHPMailer\PHPMailer\PHPMailer,$param);
};

$container['s3'] = function ($c) {
    $param = $c->config->get('s3');
    return Seriti\Tools\Amazon::setupS3($param);
};

$container['system'] = function ($c) {
    $config = $c->config;
    $table = Seriti\Tools\TABLE_SYSTEM;
    $system = new Seriti\Tools\System($c->mysql,$c,$table);
    $system->setup();
    return $system;
};

$container['user'] = function ($c) {
    $config = $c->config;
    $table = Seriti\Tools\TABLE_USER;
    $user = new Seriti\Tools\User($c->mysql,$c,$table);
    //ADMIN,ALL,PUBLIC are user zones => default route for that zone.
    $param = ['route_login'=>'login','route_default'=>['ADMIN'=>'admin/dashboard','ALL'=>'admin/dashboard','PUBLIC'=>'public/account/dashboard']];
    $user->setup($param);
    return $user;
};

$container['menu'] = function ($c) {
    $config = $c->config;
    $table = Seriti\Tools\TABLE_MENU;
    $menu = new Seriti\Tools\Menu($c->mysql,$c,$table);
    $param = ['check_access'=>true];
    $menu->setup($param);
    return $menu;
};

$container['cache'] = function ($c) {
    $param['mysql'] = $c->mysql;
    $param['table'] = Seriti\Tools\TABLE_CACHE;
    $param['user'] = $c->user->getId();
    $cache = new Seriti\Tools\Cache('MYSQL',$param);
    return $cache;
};

//will use default slim error handling in DEBUG = true mode which ouputs directly to browser
if(!DEBUG) {
    $container['errorHandler'] = function ($c) {
        //$logger = $c['logger'];

        return function($request, $response, $exception) use ($c) {
            
            $text = Seriti\Tools\Error::renderExceptionOrError($exception);
            $c['logger']->error($text);
            //$c['logger']->error($exception->getMessage());

            $template = [];
            return $c->view->render($response,'error.php',$template);
        };
    };

    $container['phpErrorHandler'] = function ($c) {
        return $c->get('errorHandler');
    };
}