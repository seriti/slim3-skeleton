<?php
namespace App\User;

use Seriti\Tools\Dashboard AS DashboardTool;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\Calc;

class Dashboard extends DashboardTool
{
     

    //configure
    public function setup($param = []) 
    {
        $this->col_count = 2;  

        $login_user = $this->getContainer('user'); 
        $config = $this->getContainer('config'); 
        $access = $login_user->getAccessLevel();

        //(block_id,col,row,title)
        $this->addBlock('USER',1,1,'Your information');
        $this->addItem('USER','Access level:'.$access);
        $this->addItem('USER','Email :'.$login_user->getEmail());

        $this->addBlock('MODULES',1,2,'Available modules');
        $modules = $config->get('module');
        foreach($modules as $module) {
            $route = Calc::getArrayFirst($module['route_list']);
            $link = BASE_URL.$module['route_root'].$route['key'];
            $this->addItem('MODULES',$module['name'],['link'=>$link]);
        }

        if($access === 'GOD') {
            $this->addBlock('SETUP',2,1,'System database setup');
            $this->addItem('SETUP','Update Database',['link'=>'/admin/data/setup','icon'=>'setup']);
        }
    }

}