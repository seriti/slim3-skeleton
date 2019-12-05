<?php 
namespace App;

use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Validate;
use Seriti\Tools\Secure;
use Seriti\Tools\DbInterface;
use Seriti\Tools\IconsClassesLinks;
use Seriti\Tools\MessageHelpers;
use Seriti\Tools\ContainerHelpers;

use Psr\Container\ContainerInterface;

class Encrypt
{
    use IconsClassesLinks;
    use MessageHelpers;
    use ContainerHelpers;
   
    protected $container;
    protected $container_allow = ['user','system'];

    protected $db;
    protected $system;
    protected $debug = false;

    protected $mode = '';
    protected $errors = array();
    protected $errors_found = false; 
    protected $messages = array();

    protected $user_id;

    public function __construct(DbInterface $db, ContainerInterface $container) 
    {
        $this->db = $db;
        $this->container = $container;
        $this->system = $this->getContainer('system');

        if(defined('\Seriti\Tools\DEBUG')) $this->debug = \Seriti\Tools\DEBUG;
    }

    public function process()
    {
        $error = '';
        $key_hash = $this->system->getDefault('KEY_HASH',0);

        $mode = '';
        if(isset($_GET['mode'])) $mode = Secure::clean('basic',$_GET['mode']);

        if($mode === ''){
            unset($_SESSION['unique_id']);
            
            if($key_hash === 0) {
                $mode = 'create_key';
            } else {
                $key = $this->system->getEncryptKey($error);
                if($key === false or $error !== '') {
                    $mode = 'verify_key';  
                } else {
                    $mode = 'valid_key';
                    $this->addMessage('Your encryption key is still valid! '.
                                      'The key will be stored in memory untill you close your browser, '.
                                      'after which you will be prompted to re-enter the key. '.
                                      'Please proceed to manage your data using menu options above! '); 
                }  
            }    
        }

        //security code to stop dictionary attack
        if(!isset($_SESSION['unique_id'])) {
            $unique_id=md5(uniqid(mt_rand(),true));
            $_SESSION['unique_id']=$unique_id;
        }

        if($mode === 'create_key' ) $html .= $this->viewCreateKey();
        if($mode === 'create_update' ) $html .= $this->createKey();
        if($mode === 'verify_key' ) $html .= $this->viewVerifyKey();
        if($mode === 'verify_update' ) $html .= $this->verifyKey();

        $html = $this->viewMessages().$html;

        return $html;
    }

    protected function createKey()
    {
        $error = '';
        $html = '';
        $key = $_POST['key'];
        
        Validate::string('Encryption key',16,250,$key,$error);
        if($error === '') {
            $this->system->setupEncryptKey($key,$error);
        } else {
            $this->addError($error);
        }  

        if($error === '') {
            $this->system->storeEncryptKey($key,$error);
        } else {
            $this->addError($error);
        }    
            
        if($error === '') {
            $this->addMessage('<h1>Congratulations you now have configured your <strong>permanent</strong> encryption key! <strong>'.$key.'</strong></h1>'.
                              '<ul><li>NB: this key is unrecoverable if you forget it! Please keep a hard copy somewhere safe!</li>'.  
                              '<li>This is the last time it will ever be displayed as plain text!</li> '.
                              '<li>The key will be stored in a secure format untill you close your browser, after which you will be prompted to re-enter the key.</li> '.
                              '<li>Please proceed to manage your data using menu options above!</li></ul> ');
        } else {
            $this->addError($error);
            $html = $this->viewCreateKey();
        }

        return $html;  
    }

    protected function viewCreateKey()
    {
        $html = '';

        $str = '<h1>An encryption key is required to secure your login usernames and passwords, text notes and selected documents.</h1> '.
               '<ul><li>This key cannot be changed once it is created as data encrypted with it will need to be decrypted using the same key.</li> '.
               '<li>The key must contain from 16 to 250 alphanumeric(letters and numbers) characters with at least 1 lowercase letter, 1 uppercase   letter, and 1 number.</li> '.
               '<li><strong>NB: this key is never stored on the server(except as a one way hash to verify its autheticity), so if you forget it then there is NO way to recover it!!</strong></li>'.
               '<li>We recommend that you use a nonsense phrase(without spaces between words) that has a few "hooks" to stimulate your memory. And no, a line from the "Jabberwocky" is not a good idea!</li>'.
               '</ul> ';
        $this->addMessage($str);

        $html .= '<div id="secure_form">'.
                 '<form method="post" action="?mode=create_update" autocomplete="off">'.
                   '<input type="hidden" name="unique_id" value="'.$_SESSION['unique_id'].'">'.
                   '<h1>Please enter your data encryption key. '.
                       '(<input type="checkbox" onclick="checkbox_password_mask(\'key_input\',this)" checked>mask)</h1>'.
                   '<input id="key_input" type="password" name="key" class="form-control" value="'.Secure::clean('string',@$_POST['key']).'">'.
                   '<input type="submit" class="btn btn-primary" value="Create encryption key">';
                 '</form>';
                 '</div>';

        return $html;
    }

    protected function verifyKey()
    {
        $key = $_POST['key'];
    
        Validate::string('Encryption key',16,250,$key,$error);
        if($error === '') $this->system->storeEncryptKey($key,$error);
                    
        if($error === '') {
            $user = $this->getContainer('user');
            //redirect to last or default page  
            $user->redirectLastPage();
        } else {
            $this->addError($error);
            return $this->viewVerifyKey();
        }   
    }   

    protected function viewVerifyKey()
    {
        $html = '';

        $str = '<h1>Your encryption key is no longer in memory! Please re-enter so you can proceed with managing your data. Please note:</h1>'.
               '<ul>'.
               '<li>After verification the key is only stored until your browser closes, or you logout!</li>'.
               '<li>If you are not working on your own computing device, then always logout after accessing this site!</li>'.
               '<li>The encryption key is not stored in any permanent form to keep your data safe! Only you know the key, so please keep a hard copy in safe place!</li>'.
               '<li>The key authenticity will be verified before re-directing you to where you were when key storage expired.</li>'.
               '</ul>';
        $this->addMessage($str);

        $html .= '<div id="secure_form">'.
                 '<form method="post" action="?mode=verify_update" autocomplete="off">'.
                   '<input type="hidden" name="unique_id" value="'.$_SESSION['unique_id'].'">'.
                   '<h1>Please enter your data encryption key. '.
                       '(<input type="checkbox" onclick="checkbox_password_mask(\'key_input\',this)" checked>mask)</h1>'.
                   '<input id="key_input" type="password" name="key" class="form-control" value="'.Secure::clean('string',@$_POST['key']).'">'.
                   '<input type="submit" class="btn btn-primary" value="Verify encryption key">';
                 '</form>';
                 '</div>';
                 
        return $html;
    }

}