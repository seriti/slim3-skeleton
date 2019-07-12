<?php
namespace App\Customise;

use Seriti\Tools\SetupModuleData;

class SetupData extends SetupModuledata
{

    public function setupSql()
    {
        $this->tables = ['menu'];

        $this->addCreateSql('menu',
                            'CREATE TABLE `TABLE_NAME` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `id_parent` int(11) NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `level` int(11) NOT NULL,
                              `lineage` varchar(255) NOT NULL,
                              `rank` int(11) NOT NULL,
                              `rank_end` int(11) NOT NULL,
                              `menu_type` varchar(64) NOT NULL,
                              `menu_link` varchar(255) NOT NULL,
                              `menu_access` varchar(64) NOT NULL,
                              `link_mode` varchar(64) NOT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('help',
                            'CREATE TABLE `TABLE_NAME` (
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `title` VARCHAR(255) NOT NULL,
                              `text_markdown` TEXT NOT NULL,
                              `text_html` TEXT NOT NULL,
                              `rank` INT NOT NULL,
                              `access` VARCHAR(64) NOT NULL,
                              `status` VARCHAR(64) NOT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE = MyISAM DEFAULT CHARSET = utf8');

        //initialisation
        $this->addInitialSql('INSERT INTO `TABLE_PREFIXmenu` (id_parent,title,level,lineage,menu_link,menu_type,menu_access) '.
                             'VALUES("0","Dashboard","1","","admin/dashboard","LINK_SYSTEM",VIEW")');
        

        //updates use time stamp in ['YYYY-MM-DD HH:MM'] format, must be unique and sequential
        //$this->addUpdateSql('YYYY-MM-DD HH:MM','Update TABLE_PREFIX--- SET --- "X"');
    }
}


  
?>
