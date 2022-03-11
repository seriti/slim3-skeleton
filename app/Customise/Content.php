<?php 
namespace App\Customise;

use Seriti\Tools\Table;
use Seriti\Tools\Html;
use Seriti\Tools\STORAGE;

class Content extends Table 
{
    
     //configure
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Content block','col_label'=>'content_id'];
        parent::setup($param);

        //widens value column
        $this->classes['col_value'] = 'col-sm-9 col-lg-10 edit_value';
        
        $this->addTableCol(array('id'=>'content_id','type'=>'STRING','title'=>'Content ID','key'=>true,'key_auto'=>false,'list'=>true));
        $this->addTableCol(array('id'=>'type_id','type'=>'STRING','title'=>'Content type'));
        $this->addTableCol(array('id'=>'info','type'=>'STRING','title'=>'Info','required'=>false));
        $this->addTableCol(array('id'=>'text_markdown','type'=>'TEXT','secure'=>false,'title'=>'Content text','rows'=>20,
                            'hint'=>'Uses <a href="http://parsedown.org/tests/" target="_blank">parsedown</a> extended <a href="https://www.markdownguide.org/basic-syntax" target="_blank">markdown</a> format, or raw html','list'=>false));
        $this->addTableCol(array('id'=>'text_html','type'=>'TEXT','secure'=>false,'title'=>'Text HTML','required'=>true,'edit'=>false));
        $this->addTableCol(array('id'=>'status','type'=>'STRING','title'=>'Status','hint'=>'Set to HIDE to make invisible to users'));

        $this->addAction(array('type'=>'check_box','text'=>''));
        $this->addAction(array('type'=>'edit','text'=>'edit','icon_text'=>'edit'));
        //$this->addAction(array('type'=>'view','text'=>'view','icon_text'=>'view'));
        $this->addAction(array('type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R'));

        $this->addSearch(array('content_id','type_id','name','text_markdown','status'),array('rows'=>2));

        $this->addSelect('status','(SELECT "OK") UNION (SELECT "HIDE")');
        $this->addSelect('type_id','(SELECT "TEXT") UNION (SELECT "HTML")');
         
        /*                                  
        $this->setupFiles(array('table'=>TABLE_PREFIX.'files','location'=>'CONTENT','max_no'=>100,
                                'icon'=>'<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>&nbsp;&nbsp;manage',
                                'list'=>false,'list_no'=>1,'storage'=>STORAGE,
                                'link_url'=>'page_file','link_data'=>'SIMPLE','width'=>'700','height'=>'600'));
        */

        
    }

    protected function afterUpdate($id,$context,$data) 
    {
        //converts page markdown into html and save 
        $text = $data['text_markdown'];
        if($text !== '') {
            if($data['type_id'] === 'HTML') {
                //now convert any markdown to html
                $html = Html::markdownToHtml($text);          
            } else {
                $html = $text;
            }
            
            $sql='UPDATE `'.TABLE_PREFIX.'content` SET `text_html` = "'.$this->db->escapeSql($html).'" '.
                 'WHERE `content_id` = "'.$this->db->escapeSql($id).'"';
            $this->db->executeSql($sql,$error_tmp);
        }

        
    }  
    
}
