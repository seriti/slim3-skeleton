<?php
namespace App\Customise;

use Seriti\Tools\SetupModule;

class SiteSetup extends SetupModule
{
    public function setup() {

        $param = [];
        $param['info'] = 'Select the style for module menus that you would prefer';
        $param['rows'] = 5;
        $param['value'] = 'TABS';
        $param['options'] = array('TABS','PILLS','BUTTONS');
        $this->addDefault('SELECT','MODULE_NAV','Module menu style',$param);

        $param = [];
        $param['info'] = 'Select the colour theme for entire site. Thanks to <a href="http://www.bootswatch.com" target="_blank">bootswatch.com</a>';
        $param['rows'] = 5;
        $param['value'] = 'DEFAULT';
        $param['options'] = array('DEFAULT','cerulean','cosmo','cyborg','darkly','flatly','journal','lumen','paper','readable','sandstone','simplex','slate','spacelab','superhero','united','yeti');
        $this->addDefault('SELECT','SITE_THEME','Colour theme',$param);

        $param = [];
        $param['info'] = 'Select whether you would like default or inverse colors on main menu.';
        $param['rows'] = 5;
        $param['value'] = 'INVERSE';
        $param['options'] = array('DEFAULT','INVERSE');
        $this->addDefault('SELECT','MENU_STYLE','Main menu style',$param);

        $param = [];
        $param['info'] = 'Select the image you would like to use as an icon at top left of main menu (max 100KB)';
        $param['max_size'] = 100000;
        $param['value'] = 'images/sunflower64.png';
        $this->addDefault('IMAGE','MENU_IMAGE','Main menu icon',$param);

        $param = [];
        $param['info'] = 'Select the image you would like to appear at top of login form (max 100KB)';
        $param['max_size'] = 100000;
        $param['value'] = 'images/sunflower64.png';
        $this->addDefault('IMAGE','LOGIN_IMAGE','Login page image',$param);
    }    
}
