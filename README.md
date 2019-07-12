# Seriti Slim 3 MySQL Framework Application

Use this skeleton application to quickly setup and start working on a new Seriti Slim 3 MySQL Framework Application. 
This application uses the latest Slim 3 with the default PHP-View template renderer.
NB: FRAMEWORK USES MYSQLI COMPONENTS AND IS THEREFORE MYSQL/MARIA DATABASE COMPATIBLE ONLY 

This skeleton application was built for Composer. This makes setting up a new Seriti Slim 3 Framework Application quick and easy.

## Install the Application

Run this command from the directory in which you want to install your new Seriti Slim 3 Framework Application.

Option 1: If you have composer installed already and it is in default path.
    composer create-project seriti/slim3-skeleton [directory-for-app]

Option 2: [Install composer in desired directory](https://getcomposer.org/download/) and then use following syntax.
    php composer.phar create-project seriti/slim3-skeleton [directory-for-app]

Replace `[directory-for-app]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public_html/` directory.
* Ensure `logs/` is web writeable.
* Ensure `storage/docs` is web writeable.
* Ensure `storage/temp` is web writeable.
* Ensure `public_html/files` is web writeable.

FOR WEB SERVERS WHERE YOU HAVE FULL CONTROL, LIKE YOUR DEVELOPMENT SERVER
* Configure "[directory-for-app]/public_html" as web root
* OR run "php -S localhost:8000 -t public_html" from [directory-for-app] folder to use PHP built in web server
* OR use "composer start" or "php composer.phar start" from [directory-for-app] folder to run PHP built in web server as configured in composer.json

FOR SHARED HOSTING ENVIRONMENT where service provider has a predefined "public_html" folder which you cannot change
* Copy all code in "skeleton/public_html" to host "public_html" folder. 
* Check in "public_html/index.php" that constant BASE_DIR is absolute path to [directory-for-app] folder, 
* Check in "public_html/index.php" that constant BASE_DIR_PUBLIC is absolute path to "public_html" webroot
NB: Some service providers can have public_html folder as a symbolic link to another location so make sure that these paths are valid

## Define framework parameters

Review contents of "env-example.php" and set MySQL database connection parameters as a minimum requirement.
NB: MAKE SURE THAT SYSTEM TABLES SPECIFIED DO NOT CLASH WITH ANY OF YOUR EXISTING TABLES IN DATABASE
Rename this file to "env.php" once you have modified it.

## Check that webserver is working

Now goto URL:
"http://localhost:8000/" if you are using php built in server
OR 
"http://www.yourdomain.com/" if you have configured a domain on your server


## Setup database tables

Now goto URL:
"http://localhost:8000/setup" if you are using php built in server
OR 
"http://www.yourdomain.com/setup" if you have configured a domain on your server

If all goes well you should see a message detailing success in creating necessary database tables.
NB: AFTER SUCCESSFUL SETUP REMEMBER TO COMMENT OUT /SETUP ROUTE in [directory-for-app]/src/routes.php

## Finally login to admin interface:

Now goto URL:
"http://localhost:8000/login" if you are using php built in server
OR 
"http://www.yourdomain.com/login" if you have configured a domain on your server

Default login credentials are:
* User: Whatever email address you defined "MAIL_WEBMASTER" as in env.php above
* pwd: SUNFLOWER

## NB!!!!: ONCE YOU HAVE SUCCESSFULLY LOGGED IN REMEMBER TO CHANGE PASSWORD OR DELETE THE DEFAULT USER CREATED AT SETUP BEFORE USING IN A PRODUCTION ENVIRONMENT







