<?php 

//referenced in Config class
define('SITE_NAME','Seriti Skeleton');

define('SETUP_APP',false); //make true once app setup and required database tables exist 
define('DEBUG',true); //make false in production
define('AUDIT',true);
define('STORAGE','local'); // use 'amazon' if you wish to store documents in an amazon s3 bucket as specified below
define('STORAGE_WWW','local');
define('CURRENCY_SYMBOL','R');
define('CURRENCY_ID','ZAR');

//Mysql database connection parameters
define('DB_HOST','localhost');
define('DB_NAME','nnnnn');
define('DB_USER','uuuuu');
define('DB_PASSWORD','pppppp');
define('DB_CHARSET','utf8');

//System table names to be used by framework, setup process will create them
define('TABLE_SYSTEM','system');
define('TABLE_USER','user_admin');
define('TABLE_ROUTE','user_route');
define('TABLE_TOKEN','user_token');
define('TABLE_CACHE','cache');
define('TABLE_BACKUP','backup');
define('TABLE_AUDIT','audit_trail');
define('TABLE_FILE','files');
define('TABLE_QUEUE','queue');
define('TABLE_MENU','cus_menu');

//only required is STORAGE = 'amazon'
define('AWS_S3_REGION','eu-west-1');
define('AWS_S3_KEY','key');
define('AWS_S3_SECRET','secret');
define('AWS_S3_BUCKET','bucket');

//email sending parameters vital for user management
//NB: MAIL_WEBMASTER is used to create default user record on setup
define('MAIL_ENABLED',true);
define('MAIL_FORMAT','text');
define('MAIL_METHOD','smtp');
define('MAIL_CHARSET','UTF-8');
define('MAIL_SUPPORT','support@yourdomain.com');
define('MAIL_FROM','from@yourdomain.com');
define('MAIL_TO','to@yourdomain.com');
define('MAIL_WEBMASTER','webmaster@yourdomain.com');
define('MAIL_HOST','mail.yourdomain.com'); //dedi14.jnb2.host-h.net must be used if tls is set
define('MAIL_USER','smtp@yourdomain.com');
define('MAIL_PASSWORD','password');
define('MAIL_SECURE',''); //tls, ssl
define('MAIL_PORT',587);

//only necessary if you intend to send SMS's
define('CLICKATELL_USER','user');
define('CLICKATELL_PWD','pwd');
define('CLICKATELL_API_ID','api');
define('CLICKATELL_HTTP_BASE','http://api.clickatell.com');

//absolute path to public html folder, BASE_DIR_PUBLIC should be defined in public_html/index.php
define('BASE_PATH',BASE_DIR_PUBLIC); 
//relative to public_html(BASE_PATH) folder which is publically accessible and must be writeable
define('BASE_UPLOAD_WWW','files/');
//relative to public_html(BASE_PATH) folder, for any asset/css/js 
define('BASE_INCLUDE','include/');
//absolute path to template folder, BASE_DIR should be defined in public_html/index.php
define('BASE_TEMPLATE',BASE_DIR.'templates/');

//absolute path to base storage folder (normally  outside of public_html folder)
define('BASE_UPLOAD',BASE_DIR.'storage/');
define('UPLOAD_DOCS','docs/');
define('UPLOAD_TEMP','temp/');
define('UPLOAD_ROUTE','/admin/data/upload');

//generic routes where not module specific
define('AJAX_ROUTE','/admin/data/ajax');
define('ENCRYPT_ROUTE','/admin/data/encrypt');

//http root pointing at public_html folder 
if($_SERVER['SERVER_NAME'] === 'localhost') { //DEVELOPMENT
  define('BASE_URL','http://'.$_SERVER['HTTP_HOST'].'/');
} else {  //PRODUCTION
  define('BASE_URL','https://'.$_SERVER['HTTP_HOST'].'/'); 

  //FORCE HTTPS CONNECTION IF REQUIRED
  /*
  if(empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] === 'off') {
    // NO SSL connection
    header('location: '.BASE_URL.substr($_SERVER['REQUEST_URI'],1));
    exit;
  } 
  */
}

//get route or path, used by seriti\tools\menu and seriti\tools\upload class
if(isset($_SERVER['REQUEST_URI'])) {
    $url = substr($_SERVER['REQUEST_URI'],1);
    $pos = strpos($url,'?');
    if($pos !== false) $url = substr($url,0,$pos);
    define('URL_CLEAN',$url);
    define('URL_CLEAN_LAST',basename($url));
} else {
    throw new Exception('$_SERVER["REQUEST_URI"] NOT available, you need this!');
}    

?>