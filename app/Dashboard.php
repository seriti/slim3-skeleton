<?php
namespace App;

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

        //(block_id,col,row,title)
        $this->addBlock('USER',1,1,'Your information');
        $this->addItem('USER','Access level:'.$login_user->getAccessLevel());
        $this->addItem('USER','Email :'.$login_user->getEmail());

        $this->addBlock('MODULES',1,2,'Available modules');
        $modules = $config->get('module');
        foreach($modules as $module) {
            $route = Calc::getArrayFirst($module['route_list']);
            $link = BASE_URL.$module['route_root'].$route['key'];
            $this->addItem('MODULES',$module['name'],['link'=>$link]);
        }
    }

}