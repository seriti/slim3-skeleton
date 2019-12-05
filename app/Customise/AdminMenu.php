<?php
namespace App\Customise;

use Seriti\Tools\Tree;
use Seriti\Tools\Crypt;
use Seriti\Tools\Form;
use Seriti\Tools\Secure;
use Seriti\Tools\Audit;

class AdminMenu extends Tree
{
    //configure
    public function setup($param = []) 
    {
        $param=['row_name'=>'menu-item',
                'col_label'=>'title'];

        parent::setup($param);        

        $config = $this->getContainer('config');
     
        //type_change() specified in menu.php template
        $this->addTreeCol(array('id'=>'menu_type','type'=>'STRING','title'=>'Type','onchange'=>'type_change()')); 
        $this->addTreeCol(array('id'=>'menu_link','type'=>'STRING','title'=>'Menu link to page','required'=>false));
        $this->addTreeCol(array('id'=>'link_mode','type'=>'STRING','title'=>'Default link mode ','required'=>false,'new'=>'list_all'));
        $this->addTreeCol(array('id'=>'menu_access','type'=>'STRING','title'=>'Access rights','required'=>false,'new'=>'ADMIN'));

        $link_mode = ['list_all'=>'List all records','add'=>'Add a record']; 

        $menu_types = ['TEXT'=>'PLain text placeholder',
                       'LINK_SYSTEM'=>'Link to system page',
                       //'LINK_TABLE'=>'Link to your tables',
                       'DIVIDER'=>'Menu divider'];

        $menu_links = [];

        $modules = $config->get('module');
        if($modules) {
            foreach($modules as $key=>$module) {
                $menu_types['LINK_'.$key]='Link to module '.$module['name'];
            }
        }

        $this->addSelect('link_mode',['list'=>$link_mode,'list_assoc'=>true]);
        $this->addSelect('menu_type',['list'=>$menu_types,'list_assoc'=>true]);
        $this->addSelect('menu_link',['list'=>$menu_links,'list_assoc'=>true]);
        $this->addSelect('menu_access',['list'=>$config->get('user','access'),'list_assoc'=>false]);
    }

    public function getJavascript()
    {
        $js = "
        <script type='text/javascript'>
        $(document).ready(function() {
          //alert('wtf');
            if(form = document.getElementById('update_form')) {
                type_change();
            }
        });

        function type_change() {
            var form = document.getElementById('update_form');
            var menu_type = form.menu_type.value;
            var menu_link = form.menu_link.value;
            
            var tr_menu_link = document.getElementById('tr_menu_link');
            var tr_link_mode = document.getElementById('tr_link_mode');
            var tr_menu_access = document.getElementById('tr_menu_access');
          
            tr_menu_link.style.display = 'none';
            tr_link_mode.style.display = 'none';
            tr_menu_access.style.display = 'none';
          
            if(menu_type.substring(0,5) == 'LINK_') {
                tr_menu_link.style.display = '';
                tr_menu_access.style.display = '';
                tr_link_mode.style.display = '';
                
                var param = 'menu_type='+menu_type;
                xhr('ajax?mode=menu',param,show_menu_links,menu_link);
            }
          
            if(menu_type == 'TEXT') {
                tr_menu_access.style.display='';
            }  
        } 

        function show_menu_links(str,menu_link) {
            if(str === 'ERROR') {
                alert('Menu ajax error!');
            } else {  
                var links = $.parseJSON(str);
                var sel = '';
                //use jquery to reset cols select list
                $('#menu_link option').remove();
                $.each(links, function(i,item){
                    // Create and append the new options into the select list
                    if(i == menu_link) sel = 'SELECTED'; else sel = '';
                    $('#menu_link').append('<option value='+i+' '+sel+'>'+item+'</option>');
                });
            }    
        }
        </script>";

        return $js;

    }
}
