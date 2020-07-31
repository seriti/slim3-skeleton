<?php
namespace App\Data;

use Seriti\Tools\SetupModuleData;
use Seriti\Tools\TABLE_USER;

//NB: THIS IS NOT A TRUE MODULE!!
class SetupData extends SetupModuledata
{

    public function setupSql()
    {
        /*
        NB: only use to define addtional system tables not required by default framework which are created in \Seriti\Tools\Setup class
        $this->tables = ['xxx'];

        $this->addCreateSql('xxx',
                            'CREATE TABLE `TABLE_NAME` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 
        */
        
        //NB: for legacy framework where no MOD_SYSTEM record make sure all updates processed
        $system_id = 'MOD_SYSTEM';
        $last_update_time = $this->system->getDefault($system_id,'NONE','count');
        if($last_update_time === 'NONE') {
            $this->system->setDefault($system_id,100,'count');
        }
        
        //updates use time stamp in ['YYYY-MM-DD HH:MM'] format, must be unique and sequential
        $this->addUpdateSql('2020-08-01 12:00','ALTER TABLE '.TABLE_USER.' ADD COLUMN `route_access` TINYINT NOT NULL AFTER `csrf_token`');
    }
}


  
?>
