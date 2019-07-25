<?php
/*****************************************************

FOR SHARED HOSTING ENVIRONMENT where service provider has a predefined "public_html" folder which you cannot change
- Copy all code in "skeleton/public_html" to host "public_html" folder. 
- Check that BASE_DIR is absolute path to "skeleton" folder, and that BASE_DIR_PUBLIC is absolute path to "public_html" webroot
- NB: Some service providers(ie: Hetzner) can have public_html folder as a symbolic link to another location so make sure that these paths are valid


FOR WEB SERVERS WHERE YOU HAVE FULL CONTROL, LIKE YOUR DEVELOPMENT SERVER
- Configure "skeleton/public_html" as web root
- OR run "php -S localhost:8000 -t public_html" from "skeleton" folder to use PHP built in web server
- OR use "composer start" from "skeleton" folder to run PHP built in web server as configured  in composer.json

*******************************************************/

//NB: SEQUENCE IS IMPORTANT!!
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

//NB: DO NOT FORGET TRAILING "/"
if($_SERVER['SERVER_NAME'] === 'localhost') { //LOCAL DEVELOPMENT SETUP
    define('BASE_DIR',__DIR__.'/../');  
    define('BASE_DIR_PUBLIC',__DIR__.'/');  
} else {  //PRODUCTION SETUP
    //Where "public_html" is outside "skeleton" folder
    //define('BASE_DIR',__DIR__.'/../skeleton/');  
    //define('BASE_DIR_PUBLIC',__DIR__.'/../public_html/');  

    //Hetzner setup has public_html as a symbolic link
    //define('BASE_DIR','/usr/home/*FTPuser*/');
    //define('BASE_DIR_PUBLIC','/usr/www/users/*FTPuser*/');
}

//load composer dump-autoload generated autoloader
require BASE_DIR.'vendor/autoload.php';

//defines local environement variables and access credentials
require BASE_DIR.'env.php';

//Configure cookies as accessible only through the HTTP protocol. Mitigates XSS attacks. The session cookie won't be accessible by JavaScript.
$cooked = session_get_cookie_params(); 
$cooked['httponly'] = true;
session_set_cookie_params($cooked['lifetime'],$cooked['path'],$cooked['domain'],$cooked['secure'],$cooked['httponly']);
session_start();

// Instantiate the app
$settings = require BASE_DIR.'src/setup_slim.php';
$app = new \Slim\App($settings);

//Setup all dependencies
require BASE_DIR.'src/dependencies.php';

//Setup application layout and configuration constants
if(SETUP_APP) require BASE_DIR.'src/setup_app.php';

// Register routes
require BASE_DIR.'src/routes.php';

// Run app
$app->run();
