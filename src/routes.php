<?php  
use Slim\Http\Request;
use Slim\Http\Response;

//Override the default Not Found Handler
unset($app->getContainer()['notFoundHandler']);
$app->getContainer()['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        //$response = new \Slim\Http\Response(404);
        //return $response->write("Page not found!");
        return $response->withRedirect('/');
    };
};

//*** COMMENT OUT THIS ROUTE AFTER YOU HAVE SETUP REQUIRED DATABASE TABLES ***
if(SETUP_APP === false) {
    $app->get('/setup', function (Request $request, Response $response, array $args) {
        $setup = new Seriti\Tools\Setup($this->config);

        $html = $setup->viewOutput();
        if(count($setup->error) === 0) {
            $html .= '<h1>Database setup complete.</h1>';
            $html .= '<h2>1.) Make define(\'SETUP_APP\',true); in src/env.php file</h2>';
            $html .= '<h2>2.) Then <a href="login">Login</a> with email: '.MAIL_WEBMASTER.' and Password: SUNFLOWER</h2>';
        }    

        return $html;
    });
}    
//*** END COMMENT OUT *** 

//*** BEGIN admin access ***
//NB: must be outside /admin route as Auth middleware will create infinite loop
$app->any('/login', \App\LoginController::class);

//for cronjob backups
$app->get('/backup', \App\BackupCronController::class);

//required by contact manager module
$app->get('/contact', \App\Contact\ContactPublicController::class);

//display system errors, not currently used as Slim3 
$app->get('/error', \App\ErrorController::class);

$app->group('/admin', function () {

    //where url: abc.com/admin or abc.com/admin/ 
    $this->redirect('', '/admin/dashboard', 301);
    $this->redirect('/', 'dashboard', 301);
    $this->get('/dashboard', \App\DashboardController::class);

    $this->any('/user', \App\UserController::class);
    $this->any('/help', \App\HelpController::class);
    $this->any('/audit', \App\AuditController::class);
    $this->any('/encrypt', \App\EncryptController::class);
    $this->any('/backup', \App\BackupController::class);

    $this->group('/custom', function () {
        $this->any('/dashboard', \App\Customise\DashboardController::class);
        $this->any('/menu', \App\Customise\AdminMenuController::class);
        $this->any('/content', \App\Customise\ContentController::class);
        $this->any('/pdf', \App\Customise\PdfSetupController::class);
        $this->any('/setup', \App\Customise\SiteSetupController::class);
        $this->get('/setup_data', \App\Customise\SetupDataController::class);
        $this->any('/help', \App\Customise\HelpController::class);
        $this->post('/ajax', \App\Customise\Ajax::class);
    })->add(\App\Customise\Config::class);

    //generic "admin/upload" for multiple file upload where files are uploaded to temp folder 
    //and file details are inserted into relevant form data for processing 
    $this->any('/upload', \App\UploadTempController::class);
    
    //generic ajax for csv download and other common tasks 
    $this->get('/ajax', \App\Ajax::class);
})->add(\App\ConfigAdmin::class);
//*** END admin access ***


//*** BEGIN public access ***
$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Seriti Slim3-Skeleton framework '/' route");
        
    // Render welcome page with setup links
    return $this->view->render($response,'index.php',$args);
});

//*** END public access ***


