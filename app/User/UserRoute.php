<?php 
namespace App\User;

use Seriti\Tools\Table;
use Seriti\Tools\TABLE_USER;
use Seriti\Tools\BASE_URL;

class UserRoute extends Table 
{
    protected $routes = [];   
    
    //configure
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Page','name'=>'route','pop_up'=>true];
        parent::setup($param);

        $config = $this->getContainer('config');
        
        $this->setupMaster(array('table'=>TABLE_USER,'key'=>'user_id','child_col'=>'user_id', 
                                'show_sql'=>'SELECT CONCAT("Allowed pages for user: ",`name`) FROM `'.TABLE_USER.'` WHERE `user_id` = "{KEY_VAL}" '));                        

        
        $this->addTableCol(array('id'=>'route_id','type'=>'INTEGER','title'=>'Route ID','key'=>true,'key_auto'=>true,'list'=>false));
        $this->addTableCol(array('id'=>'route','type'=>'STRING','title'=>'Allow page url',
                                 'hint'=>'(Copy url you require from address bar of browser. Note that domain name and leading "/" will be automatically excluded)'));
        $this->addTableCol(array('id'=>'title','type'=>'STRING','title'=>'Menu title','hint'=>'(this will appear in user menu bar, enter "NONE" to remove from menu)'));
        $this->addTableCol(array('id'=>'access','type'=>'STRING','title'=>'Access','new'=>'USER'));
        $this->addTableCol(array('id'=>'sort','type'=>'INTEGER','title'=>'Sort order','new'=>10,'hint'=>'(Set menu display order. First page is default page.)'));
        //$this->addTableCol(array('id'=>'config','type'=>'TEXT','title'=>'Configuration'));
        
        $this->addSortOrder('T.`sort`','Sort order','DEFAULT');

        $this->addAction(array('type'=>'edit','text'=>'edit','icon_text'=>'edit'));
        $this->addAction(array('type'=>'delete','text'=>'delete','pos'=>'R','icon_text'=>'delete'));

        $this->addSearch(array('route','access'),array('rows'=>1));

        $this->addSelect('access',['list'=>$config->get('user','access'),'list_assoc'=>false]);
    }

    protected function beforeUpdate($id,$context,&$data,&$error) 
    {
        $user = $this->getContainer('user'); 
        $access_levels = $user->getAccessLevels();

        $update_user = $user->getUser('ID',$this->master['key_val']);
        $base_access = $update_user[$this->user_cols['access']];

        //1=GOD, 2=ADMIN, 3=USER, 4=VIEW, so lower key is higher access
        $base_rank = array_search($base_access,$access_levels);
        $update_rank = array_search($data['access'],$access_levels);
        if($update_rank < $base_rank) $error .= 'You cannot grant route access level['.$data['access'].'] higher than user base access level['.$base_access.']';

        //clean up route if cut and paste from url
        if(stripos($data['route'],BASE_URL) !== false) {
            $data['route'] = str_replace(BASE_URL,'',$data['route']);
        } else {
            //remove leading / if included
            if(substr($data['route'],0,1) === '/') $data['route'] = substr($data['route'],1);    
        }
        
    }
}
?>
