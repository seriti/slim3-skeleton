<?php 
namespace App\Customise;

use Exception;
use Seriti\Tools\Table;
use Seriti\Tools\Html;

class Help extends Table 
{
    //configure
    public function setup() 
    {
        $param = ['row_name'=>'Help topic','col_label'=>'title'];
        parent::setup($param);

        $config = $this->getContainer('config');

        $this->addTableCol(array('id'=>'id','type'=>'INTEGER','title'=>'Help ID','key'=>true,'key_auto'=>true,'list'=>true));
        $this->addTableCol(array('id'=>'title','type'=>'STRING','title'=>'Title'));
        $this->addTableCol(array('id'=>'text_markdown','type'=>'TEXT','secure'=>false,'title'=>'Help content markdown','rows'=>20,
                                 'hint'=>'Uses <a href="http://parsedown.org/tests/" target="_blank">parsedown</a> extended <a href="https://www.markdownguide.org/basic-syntax" target="_blank">markdown</a> format, or raw html','list'=>false));
        $this->addTableCol(array('id'=>'text_html','type'=>'HTML','title'=>'Help content','edit'=>false,'list'=>true,'secure'=>false));
        $this->addTableCol(array('id'=>'rank','type'=>'INTEGER','title'=>'List rank order','hint'=>'Determines sequence of help items'));
        $this->addTableCol(array('id'=>'access','type'=>'STRING','title'=>'User access minimum','new'=>'USER'));
        $this->addTableCol(array('id'=>'status','type'=>'STRING','title'=>'Status'));
        
        $this->addSortOrder('rank','By ranking number','DEFAULT');

        //$this->addAction(array('type'=>'check_box','text'=>''));
        $this->addAction(array('type'=>'edit','text'=>'edit','icon_text'=>'edit'));
        //$this->addAction(array('type'=>'view','text'=>'view','icon_text'=>'view'));
        $this->addAction(array('type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R'));

        $this->addSearch(array('title','text_markdown','access','status'),array('rows'=>1));
        
        $this->addSelect('status','(SELECT "OK") UNION ALL (SELECT "HIDE")');
        $this->addSelect('access',['list'=>$config->get('user','access'),'list_assoc'=>false]);

    } 

     function afterUpdate($id,$edit_type,$form) 
    {
        $error = '';
        //converts page markdown into html and save 
        $text = $form['text_markdown'];
        if($text !== '') {
            $html = Html::markdownToHtml($text);  
            $sql='UPDATE '.$this->table.' SET text_html = "'.$this->db->escapeSql($html).'" '.
                 'WHERE id = "'.$this->db->escapeSql($id).'"';
            $this->db->executeSql($sql,$error);
            if($error !== '') throw new Exception('CUSTOM_HELP: could not convert markdown into HTML');
        }  
    }    
}
?>
