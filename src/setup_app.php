<?php
//START ADMIN WEBSITE application settings
//Application system menu options for GOD access only
//key = route from web root, value = menu text
define('SYSTEM_MENU',['admin/custom/menu'=>'Admin Customisation module',
                      'admin/user'=>'Admin Users',
                      'admin/audit'=>'Admin User audit']);

//Application layout setup
$theme = $container['system']->getDefault('SITE_THEME','DEFAULT');
if($theme === 'DEFAULT') {
    define('SITE_THEME_CSS','bootstrap.min.css');
} else {
    define('SITE_THEME_CSS','bootstrap.min_'.$theme.'.css');
}

$image = $container['system']->getDefault('MENU_IMAGE','');
if($image !== '') {
    define('SITE_MENU_LOGO','<img src="'.BASE_URL.BASE_UPLOAD_WWW.$image.'" height="40">');
    define('SITE_ICON','<link rel="icon" href="'.BASE_URL.BASE_UPLOAD_WWW.$image.'" type="image/x-icon">');
} else {
    define('SITE_MENU_LOGO',SITE_NAME);
    define('SITE_ICON','');
}

//define('SITE_ICON','<link rel="icon" href="'.BASE_URL.'images/favicon.png" type="image/x-icon">');

$image = $container['system']->getDefault('LOGIN_IMAGE','');
if($image !== '') {
    define('SITE_LOGIN_LOGO','<img src="'.BASE_URL.BASE_UPLOAD_WWW.$image.'">');
} else {
    define('SITE_LOGIN_LOGO','<h1>'.SITE_NAME.'</h1>');
}

$style = $container['system']->getDefault('MENU_STYLE','');
if($style === 'INVERSE') {
    define('SITE_MENU_STYLE','navbar navbar-inverse navbar-fixed-top'); 
} else {
    define('SITE_MENU_STYLE','navbar navbar-default navbar-fixed-top'); 
}  

$type = $container['system']->getDefault('MODULE_NAV','');
if($type !== '') {
    define('SITE_MODULE_NAV',$type);     
} else {
    define('SITE_MODULE_NAV','TABS');
}    
//END ADMIN WEBSITE application settings

//START PUBLIC WEBSITE settings
$footer = $container['system']->getDefault('WWW_FOOTER','');
if($footer !== '') {
    define('WWW_FOOTER',$footer);     
} else {
    define('WWW_FOOTER','');   
}    

$css = $container['system']->getDefault('WWW_SITE_CSS','');
if($css !== '') {
    define('WWW_SITE_CSS',$css);     
} else {
    define('WWW_SITE_CSS','');   
} 

$style = $container['system']->getDefault('WWW_MENU_STYLE','');
if($style === 'INVERSE') {
    define('WWW_MENU_STYLE','navbar navbar-inverse navbar-fixed-top'); 
} else {
    define('WWW_MENU_STYLE','navbar navbar-default navbar-fixed-top'); 
} 

$image = $container['system']->getDefault('WWW_MENU_IMAGE','');
if($image !== '') {
    define('WWW_MENU_LOGO','<img src="'.BASE_URL.BASE_UPLOAD_WWW.$image.'" height="40">');
    define('WWW_SITE_ICON','<link rel="icon" href="'.BASE_URL.BASE_UPLOAD_WWW.$image.'" type="image/x-icon">');
} else {
    define('WWW_MENU_LOGO',SITE_NAME);
    define('WWW_SITE_ICON','');
}

$theme = $container['system']->getDefault('WWW_SITE_THEME','DEFAULT');
if($theme === 'DEFAULT') {
    define('WWW_SITE_THEME_CSS','bootstrap.min.css');
} else {
    define('WWW_SITE_THEME_CSS','bootstrap.min_'.$theme.'.css');
} 
//END PUBLIC WEBSITE settings

//Application module setup
$container['config']->set('module','custom',['name'=>'Customise',
                                             'route_root'=>'admin/custom/',
                                             'route_list'=>['menu'=>'Menu items','pdf'=>'PDF layout','setup'=>'Site setup'],
                                             'table_prefix'=>'cus_'
                                            ]);

