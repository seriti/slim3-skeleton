<?php
namespace App\User;

use Exception;

use Seriti\Tools\Wizard;
use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Doc;
use Seriti\Tools\Calc;
use Seriti\Tools\Secure;
use Seriti\Tools\Plupload;
use Seriti\Tools\STORAGE;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\UPLOAD_DOCS;
use Seriti\Tools\TABLE_USER;

//NB: Only pages 1,2 are a wizard. Otherwise not a sequential wizard, more like a collection of login templates
class LoginWizard extends Wizard 
{
    protected $user_id;
    protected $user;
    protected $mode;
    protected $login_expire_days = 30;

    //configure
    public function setup($param = []) 
    {
        $this->user = $this->getContainer('user');

        $param['bread_crumbs'] = false;
        $param['strict_var'] = false;
        $param['csrf_token'] = $this->user->getTempToken();
        //$param['show_messages'] = false;
        parent::setup($param);

        if(defined('LOGIN_EXPIRE_DAYS')) $this->login_expire_days = LOGIN_EXPIRE_DAYS; 

        $this->addVariable(array('id'=>'email','type'=>'EMAIL','title'=>'Your email address'));
        $this->addVariable(array('id'=>'password','type'=>'PASSWORD','title'=>'Your password'));
        $this->addVariable(array('id'=>'password_repeat','type'=>'PASSWORD','title'=>'Your password repeated'));
        $this->addVariable(array('id'=>'human_id','type'=>'STRING','title'=>'Human ID'));
        $this->addVariable(array('id'=>'remember_me','type'=>'BOOLEAN','title'=>'Remember me'));
        $this->addVariable(array('id'=>'remember_days','type'=>'INTEGER','title'=>'Remember for number of days','new'=>$this->login_expire_days));
        $this->addVariable(array('id'=>'login_option','type'=>'STRING','title'=>'Other login options'));
        
        //define pages and templates
        $this->addPage(1,'Your email address','login/login_wizard_start.php',['go_back'=>false]);
        $this->addPage(2,'Your password','login/login_wizard_password.php');
        $this->addPage(3,'Password reset','login/login_wizard_password_reset.php');
        $this->addPage(4,'Login results','login/login_wizard_final.php',['final'=>true]);    

    }

    //NB: Only runs when NO page OR mode specified and /login route called by accident or malicious intent
    public function initialConfig() 
    {
        //NB: various link modes handled below in setupPageData()
        if(!isset($_GET['mode'])) {

            $this->user_id = $this->user->setupUserData();
            if($this->user_id != 0) {
                $this->addMessage('You are already logged in! You can login as another user or '.$this->js_links['back']);

                $_SESSION['login_redirect'] = $_SESSION['login_redirect'] + 1 ;
                if($_SESSION['login_redirect'] > 5) {
                    $this->addError('Something is wrong with your access credentials. Please contact support.');
                } else {
                    $this->user->redirectLastPage();
                }
            }
        }
        
    }
    
    //NB: handle all ?mode=xxx links outside of standard wizard flow
    public function beforeProcess() 
    {
        if(isset($_GET['mode'])) {
            $this->process_page_no = false;
            $this->mode = Secure::clean('basic',$_GET['mode']);
            
            //NB: need below to identify user for email links
            $this->getData('data');
        }    
        
        //process logout LINK and start on page 1
        if($this->mode === 'logout') {
            $this->user->manageUserAction('LOGOUT');
            $this->page_no = 1;
        }    

        //process reset password LINK result on page 3, capture new password
        if($this->mode === 'reset_pwd') {
            $this->user->resetPassword($_GET['token']);
            $this->page_no = 3;
        } 

        //email a reset link to user
        if($this->mode === 'send_reset') {
            $user_id = $this->data['user'][$this->user_cols['id']];
            $this->user->resetSendPasswordLink($user_id);
            $this->page_no = 4;
        }    

        //process login token LINK and show status on final page 4
        if($this->mode === 'reset_login') {
            $this->user->resetLoginLink($_GET['token'],$_GET['days']);
            $this->page_no = 4;
        }

        //email a login link to user
        if($this->mode === 'send_login') {
            $user_id = $this->data['user'][$this->user_cols['id']];
            $days_expire = $this->login_expire_days;
            $this->user->resetSendLoginLink($user_id,$days_expire);
            $this->page_no = 4;
        }    
    }

    //NB: Called just BEFORE viewPage($page_no)
    public function setupPageData($page_no) 
    {
        //NB: gets login page defaults all messages/errors
        $this->data['login'] = $this->user->viewLogin();
        $this->saveData('data');
    }

    //NB: called AFTER page submitted
    public function processPage() 
    {
        $error = '';
        $error_tmp = '';

                        
        //check if user exists
        if($this->page_no == 1) {
            //only process a valid email address
            if(!$this->errors_found) {
                //wiil not check max login attempts or status
                $user = $this->user->getUser('EMAIL_EXIST',$this->form['email']);
                if($user == 0) {
                    $this->addError('Your email['.$this->form['email'].'] is not recognised by '.SITE_NAME);  
                } else {
                    if($user[$this->user_cols['login_fail']] >= 10) {
                       $this->addError('You have exceeded maximum attempts at login, please contact us.');  
                    } elseif($user[$this->user_cols['status']] === 'HIDE') {
                       $this->addError('You have been locked out of system, please contact us.'); 
                    } else {
                       $this->data['user'] = $user;    
                    }
                }

            }

        } 
        
        //process password & reset password
        if($this->page_no == 2) {
            //translate wizard template to default form fields
            $form[$this->user_cols['email']] = $this->form['email'];
            $form[$this->user_cols['password']] = $this->form['password'];
            $form['human_id'] = $this->form['human_id'];
            $form['remember_me'] = $this->form['remember_me'];
            $form['days_expire'] = $this->form['remember_days'];

            $this->user->manageLogin('LOGIN',$form);

            $user_errors = $this->user->getErrors();
            if(count($user_errors)) {
                $this->errors_found = true;
                if($this->debug) { //will duplicate error display outside template
                    $this->errors = array_merge($user_errors,$this->errors);
                } 
            }   
        }  
        
        //process password reset link 
        if($this->page_no == 3) {
            //translate wizard template to default form fields
            $form[$this->user_cols['email']] = $this->form['email'];
            $form[$this->user_cols['password']] = $this->form['password'];
            $form['password_repeat'] = $this->form['password_repeat'];

            $form['human_id'] = $this->form['human_id'];
           
            //currently hard coded to remember for 30 days
            //$form['remember_me'] = $this->form['remember_me'];
            //$form['days_expire'] = $this->form['remember_days'];

            $this->user->manageLogin('LOGIN',$form);

            $user_errors = $this->user->getErrors();
            if(count($user_errors)) {
                $this->errors_found = true;
                if($this->debug) {
                    $this->errors = array_merge($user_errors,$this->errors);
                } 
            }
        } 
        
        //display messages and link process results
        if($this->page_no == 4) {

            $user_errors = $this->user->getErrors();
            if(count($user_errors)) {
                $this->errors_found = true;
                if($this->debug) {
                    $this->errors = array_merge($user_errors,$this->errors);
                } 
            }

            $user_messages = $this->user->getMessages(); 
            if(count($user_messages)) {
                if($this->debug) {
                    $this->messages = array_merge($user_messages,$this->messages);
                }    
            }  
          
        }  
    }


}

?>


