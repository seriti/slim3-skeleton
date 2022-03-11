<?php
namespace App\User;

use Seriti\Tools\Table;
use Seriti\Tools\TABLE_USER;

class Audit extends Table 
{
    //configure
    public function setup($param = []) 
    {
        $param=['row_name'=>'Audit','col_label'=>$this->audit_cols['date']];
        parent::setup($param);        

        $config = $this->getContainer('config');
        $login_user = $this->getContainer('user');

        $access['read_only'] = true;
        $this->modifyAccess($access);

        $this->addTableCol(array('id'=>$this->audit_cols['id'],'type'=>'INTEGER','title'=>'Audit ID','key'=>true,'key_auto'=>true,'list'=>false));

        $join = '`'.$this->user_cols['name'].'` FROM `'.TABLE_USER.'` WHERE `'.$this->user_cols['id'].'`';
        $this->addTableCol(array('id'=>$this->audit_cols['user_id'],'type'=>'STRING','title'=>'User','join'=>$join));

        $this->addTableCol(array('id'=>$this->audit_cols['date'],'type'=>'DATETIME','title'=>'Date & time'));
        $this->addTableCol(array('id'=>$this->audit_cols['action'],'type'=>'STRING','title'=>'Action'));
        $this->addTableCol(array('id'=>$this->audit_cols['text'],'type'=>'TEXT','title'=>'Description'));

        $this->addSortOrder($this->audit_cols['date'].' DESC','Date & time DESC','DEFAULT');

        //$this->addAction(array('type'=>'edit','text'=>'edit'));
        //$this->addAction(array('type'=>'delete','text'=>'del','pos'=>'R'));

        $this->addSearch(array($this->audit_cols['user_id'],$this->audit_cols['date'],$this->audit_cols['action'],
                               $this->audit_cols['text']),array('rows'=>2));

        $sql = 'SELECT `'.$this->user_cols['id'].'`,`'.$this->user_cols['name'].'` FROM `'.TABLE_USER.'` ORDER BY `'.$this->user_cols['name'].'`';
        $this->addSelect($this->audit_cols['user_id'],$sql);
        $sql = 'SELECT DISTINCT(`'.$this->audit_cols['action'].'`) FROM `'.$this->table.'` ORDER BY `'.$this->audit_cols['action'].'`';
        $this->addSelect($this->audit_cols['action'],$sql);
    }
}