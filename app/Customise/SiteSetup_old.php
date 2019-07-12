<?php
namespace App\Customise;

use Seriti\Tools\BASE_PATH;
use Seriti\Tools\BASE_UPLOAD_WWW;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\Mysql;
use Seriti\Tools\Form;
use Seriti\Tools\Secure;
use Seriti\Tools\DbInterface;
use Seriti\Tools\System;

class SiteSetup
{
    protected $db;
    protected $system;
    protected $debug = false;

    public function __construct(DbInterface $db, System $system) 
    {
        $this->db = $db;
        $this->system = $system;
       
        if(defined('\Seriti\Tools\DEBUG')) $this->debug = \Seriti\Tools\DEBUG;
    }


    public function process() {
        $error_str='';
        $error=array();
        $message=array();
        $html='';
        //flag to show refresh message if setting updated that is not immediately displayed
        $refresh_msg=false;

        //http access required
        $upload_dir=BASE_PATH.BASE_UPLOAD_WWW;
        $base_url=BASE_URL;
        $upload_url=BASE_URL.BASE_UPLOAD_WWW;
        $default_image_url='images/sunflower64.png';

        //default values
        $setup=array();

        $default=array();
        $default['type']='SELECT';
        $default['id']='MODULE_NAV'; //must be unique in system table
        $default['title']='Module menu style';
        $default['info']='Select the style for module menus that you would prefer';
        $default['options']=array('TABS','PILLS','BUTTONS');
        $default['value']='TABS';
        $setup[]=$default;

        $default=array();
        $default['type']='SELECT';
        $default['id']='SITE_THEME'; //must be unique in system table
        $default['title']='Colour theme';
        $default['info']='Select the colour theme for entire site. Thanks to <a href="www.bootswatch.com" target="_blank">bootswatch.com</a>';
        $default['options']=array('DEFAULT','cerulean','cosmo','cyborg','darkly','flatly','journal','lumen','paper','readable','sandstone','simplex','slate','spacelab','superhero','united','yeti');
        $default['value']='DEFAULT';
        $setup[]=$default;

        $default=array();
        $default['type']='SELECT';
        $default['id']='MENU_STYLE'; //must be unique in system table
        $default['title']='Main menu style';
        $default['info']='Select whether you would like default or inverse colors on main menu.';
        $default['options']=array('DEFAULT','INVERSE');
        $default['value']='INVERSE';
        $setup[]=$default;

        $default=array();
        $default['type']='IMAGE';
        $default['id']='MENU_IMAGE'; //must be unique in system table
        $default['title']='Main menu icon';
        $default['info']='Select the image you would like to use as an icon at top left of main menu (max 50KB)';
        $default['max_size']=50000;
        $default['value']=$default_image_url;
        $setup[]=$default;

        $default=array();
        $default['type']='IMAGE';
        $default['id']='LOGIN_IMAGE'; //must be unique in system table
        $default['title']='Login page image';
        $default['info']='Select the image you would like to appear at top of login form (max 50KB)';
        $default['max_size']=50000;
        $default['value']=$default_image_url;
        $setup[]=$default;

        $mode='list_all';
        if(@$_GET['mode']) $mode=$_GET['mode'];

        $date_now=getdate();

        if($mode==='reset') {
            foreach($setup as $default) {
                $reset=$this->system->removeDefault($default['id']);
                //$reset=seriti_custom::reset_system_default($conn,$default['id']);
                if($reset) {
                    $message[]='Successfully reset: '.$default['title'];
                } else {
                    $error[]='Could NOT reset: '.$default['title'];
                }    
            }  
            
        	$mode='list_all';	
        }	

        if($mode=='update') {
            $updated=array();
          
            foreach($setup as $default) {
                if($default['type']==='SELECT') {
                    if(isset($_POST[$default['id']])) {
                        $value_exist=$this->system->getDefault($default['id'],$default['value']);
                        //$value_exist=seriti_custom::get_system_default($conn,$default['id'],$default['value']);
                        $value=Secure::clean('alpha',$_POST[$default['id']]);
                        if($value_exist!==$value) {
                            $this->system->setDefault($default['id'],$value);
                            //seriti_custom::save_system_default($conn,$default['id'],$value);
                            $message[]='Successfully updated '.$default['title'].' setting.';
                            $updated[$default['id']]=true;
                        } else {
                            $updated[$default['id']]=false; 
                        }   
                    }   
                }  

                if($default['type']==='IMAGE') {
                    $file_options=array();
                    $file_options['upload_dir']=$upload_dir;
                    $file_options['allow_ext']=array('jpg','jpeg','png','gif');
                    $file_options['max_size']=$default['max_size'];
                    $save_name=$default['id'];
                    $image_name=Form::uploadFile($default['id'],$save_name,$file_options,$error_str);
                    if($error_str!='') {
                        if($error_str!='NO_FILE') $error[]=$default['title'].': '.$error_str;
                        $updated[$default['id']]=false;
                    } else {
                        $this->system->setDefault($default['id'],$image_name);
                        //seriti_custom::save_system_default($conn,$default['id'],$image_name);
                        $message[]='Successfully updated '.$default['title'].' image.';
                        $updated[$default['id']]=true;
                    }     
                }  
            }  
          
            $message[]='You will need to click <a href="?mode=list_all">here to refresh display</a> and show changes.';
            //assuming no errors
            $mode='list_all';
        }


        if($mode=='list_all') {
            $list_param=array();
            $list_param['class']='form-control';
            
            $html.='<form action="?mode=update" method="post" enctype="multipart/form-data">';
        	  
            $html.='<div class="row">'.
                   '<div class="col-sm-12">'.
                   '<h2>Change any settings below and then click update button to save: '.
                        '<input type="submit" class="btn btn-primary" value="Update admin site settings"></h2>'.
                        Form::viewMessages($error,$message);
                   '</div>'.
                   '</div>'.
                   '<hr/>';
            foreach($setup as $default) {
                //gets stored default or default value if none
    
                $value = $this->system->getDefault($default['id'],$default['value']);
                //$value=seriti_custom::get_system_default($conn,$default['id'],$default['value']);
                //if($value=='') $value=$default['value'];
                $html.='<div class="row">'.
                       '<div class="col-sm-3"><strong>'.$default['title'].'</strong></div>';
                if($default['type']==='SELECT') {
                    if(isset($default['options'][0])) $assoc=false; else $assoc=true;
                    $html.='<div class="col-sm-6">'.
                           Form::arrayList($default['options'],$default['id'],$value,$assoc,$list_param).
                           '</div>';
                }
                if($default['type']==='IMAGE') {
                    if($value===$default['value']) {
                        $image_url=$base_url.$value;
                    } else {
                        $image_url=$upload_url.$value;
                    }    
                    $html.='<div class="col-sm-6">'.
                           Form::fileInput($default['id'],'',$list_param).
                           '<br/><img src="'.$image_url.'" height="50" align="left">'.
                         '</div>';
                }  
                $html.='<div class="col-sm-3">'.$default['info'].'</div>';
    
                
                $html.='</div><hr/>';
            } 
          
            $html.='</form>';
          
            $html.='<a href="?mode=reset">Reset all settings to default values.</a>';
         
        }

        return $html;
    }    

}

?>
