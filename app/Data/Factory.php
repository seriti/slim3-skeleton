<?php 
namespace App\Data;

use Exception;

use Psr\Container\ContainerInterface;

use Seriti\Tools\BASE_URL;
use Seriti\Tools\BASE_PATH;

use Seriti\Tools\TableStructures;
use Seriti\Tools\DbInterface;
use Seriti\Tools\IconsClassesLinks;
use Seriti\Tools\MessageHelpers;
use Seriti\Tools\ContainerHelpers;

use Seriti\Tools\Secure;
use Seriti\Tools\Form;

//NB: Factory designed specifically to create all necessary basic class files for a given module database tables. FactoryController.php has module id hard coded  
class Factory 
{
    use IconsClassesLinks;
    use MessageHelpers;
    use ContainerHelpers;

    protected $container;
    protected $container_allow = ['config','user','system'];

    protected $db;
    protected $config;
    protected $module;
    protected $module_id;

    protected $mode = 'setup';
    protected $errors = [];
    protected $errors_found = false; 
    protected $messages = [];
    
    protected $tables = [];
    protected $create_dir = '';
    //folder from skeleton base directory 
    protected $app_dir = 'app';
    protected $name_space = '';
    protected $template = ['master'=>'admin.php','child'=>'admin_popup.php'];
    protected $table_standard = ['file'=>'file'];
    protected $routes = [];

    
    public function __construct(DbInterface $db, ContainerInterface $container, $module_id, $create_dir = '', $name_space = '')
    {
        $this->db = $db;

        $this->container = $container;
        $this->config = $this->getContainer('config') ;

        $this->module_id = $module_id; 
        $this->module = $this->config->get('module',$module_id);
        if($this->module === false) $this->addError('Module "'.$module_id.'" does not exist.');

        //NB: normally Folder and Namespace same as module 
        $folder = ucfirst(strtolower($module_id)); 

        if($create_dir !== '') $this->create_dir = $create_dir; else $this->create_dir = BASE_DIR.$this->app_dir.'/'.$folder.'/';
        if($name_space !== '') $this->name_space = $name_space; else $this->name_space = ucfirst($this->app_dir).'\\'.$folder;
    }

    
    public function process() 
    {
        $html = '';
        $id = 0;
        $param = array();
        $form = array();

        if(isset($_GET['mode'])) $this->mode = Secure::clean('basic',$_GET['mode']);

        $sql = 'SHOW TABLES LIKE "'.$this->db->escapeSql($this->module['table_prefix']).'%" ';
        $this->tables = $this->db->readSqlList($sql);
        if($this->tables == 0) {
            $this->addError('NO module['.$this->module['name'].'] tables found in database using SQL['.$sql.']');
        }    

        if(!$this->errors_found) {
            if($this->mode === 'setup') {
                $html .= $this->viewTableSetup();
            }    

            //check for existing module code files and create if not there
            if($this->mode === 'create') {
                foreach($this->tables as $table) {
                    if(isset($_POST[$table.'create'])) {
                        //will only create if file not existing in create_dir
                        $this->createController($table);
                        $this->createClass($table);

                        if($_POST[$table.'files'] !== 'NA') {
                            $location = $_POST[$table.'files'];
                            $this->createFileController('FILE',$table,$location);
                            $this->createFileClass('FILE',$table,$location);
                        }

                        if($_POST[$table.'images'] !== 'NA') {
                            $location = $_POST[$table.'images'];
                            $this->createFileController('IMAGE',$table,$location);
                            $this->createFileClass('IMAGE',$table,$location);
                        }
                    } 
                }

                //routes for includion in routes.php
                $this->addMessage('Copy routes below into applicable group within "src/routes.php"');
                $this->addMessage(implode('<br/>',$this->routes));
                
            }
        }

        

        $html .= $this->viewMessages();

        return $html;
    } 

    protected function viewTableSetup()
    {  
        $html = '';

        
        $html .= '<p>SPECIFY options for <strong>'.$this->module['name'].'</strong> module table management pages:</p>'.
                 '<form method="post" action="?mode=create" name="create_form" id="create_form">'.
                 '<input type="submit" value="CREATE CLASS FILES FOR SELECTED TABLES">'.
                 '<p>Leave field values "NA" to ignore that option. If a table is a CHILD, select MASTER "Linked table". Any existing class files will NOT be overwritten! </p>'.
                 '<p>Class files will be created in <b>'.$this->create_dir.'</b> using the namespace <b>'.$this->name_space.'</b></p>'.
                 '<table><tr><th>Table</th><th>Create</th><th>Type</th><th>Linked table</th><th>Files location</th><th>Images location</th><th>Xtra</th></tr>';

        $checked = false;
        foreach($this->tables as $table) {
            $html .= '<tr>';
            $html .= '<td>'.$table.'</td>';
            $html .= '<td>'.Form::checkBox($table.'create','YES',$checked).'</td>';
            $html .= '<td>'.Form::arrayList(['STANDARD','MASTER','CHILD'],$table.'type','STANDARD',false).'</td>';
            $html .= '<td>'.Form::arrayList($this->tables,$table.'linked','NA',false).'</td>';
            $html .= '<td>'.Form::textInput($table.'files','NA') .'</td>';
            $html .= '<td>'.Form::textInput($table.'images','NA') .'</td>';
            $html .= '<td>'.Form::textInput($table.'xtra','NA') .'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>'.                 
                 '</form>';

        return $html;         
        
    }

    //NB: Construct default names by convention using table name as base
    protected function getNames($table)
    {       
        $name = [];
        $class_name = '';
        $suffix_name = str_replace($this->module['table_prefix'],'',$table);

        //need to convert "aaa_bbb_ccc" to "aaaBbbCcc"
        $arr = explode('_',$suffix_name);
        $last_word = '';
        foreach($arr as $key=>$word) {
            $class_name .= ucfirst(strtolower($word));
            $last_word = $word;
        }
        
        $name['route'] = $suffix_name;

        $name['row'] = ucfirst(str_replace('_',' ',$suffix_name));

        if(substr($name['row'],-1) === 'y') {
            $name['row_plural'] = substr($name['row'],0,-1).'ies';
        } else {
            $name['row_plural'] = $name['row'].'s';
        }    

        $name['label'] = 'name'; //NB: assumes that a field "name" exists;
        $name['title'] = 'All '.$name['row_plural'];
        $name['title_child'] = $name['row'];

        $name['table'] = $suffix_name;
        $name['table_key'] = $last_word.'_id'; //best guess
        $name['base_class'] = $class_name;
        $name['controller_class'] = $class_name.'Controller';
        $name['table_url'] = strtolower($name['table']);

        $name['file_class'] = $class_name.'File';
        $name['file_controller_class'] = $class_name.'File'.'Controller';
        $name['file_url'] = $name['table_url'].'_file';

        $name['image_class'] = $class_name.'Image';
        $name['image_controller_class'] = $class_name.'Image'.'Controller';
        $name['image_url'] = $name['table_url'].'_image';

        return $name;
    }

    protected  function addRoute($route,$class_name)
    {
        $str = '$this->any(\'/'.$route.'\', '.$this->name_space.'\\'.$class_name.'::class);';
        $this->routes[] = $str;
    }

    protected function getColumns($table)
    {
        $cols = [];

        $sql = 'SHOW COLUMNS FROM `'.$this->db->escapeSql($table).'`';
        $table_cols = $this->db->readSqlArray($sql);

        //print_r($table_cols);
        //die();
        if($table_cols == 0) {
            $this->addError('NO columns found for table['.$table.'] using SQL['.$sql.']');
        } else {
            foreach($table_cols as $col_id => $table_col) {
                $col = [];

                $col['id'] = $col_id;
                if($col_id === 'sort') {
                    $col['title'] = 'Sort order';
                    $col['hint'] = 'Number to indicate dropdown display order';
                } else {
                    $col['title'] = ucfirst(str_replace('_',' ',$col_id));    
                }
                

                $col['key'] = false;
                $col['key_auto'] = false;
                if($table_col['Key'] === 'PRI') {
                    $col['key'] = true;
                    if($table_col['Extra'] === 'auto_increment') $col['key_auto'] = true;

                    if(substr($col_id,-3) === '_id') $col['title'] = substr($col_id,0,-3).' ID';
                }

                if(!$col['key'] and substr($col_id,-3) === '_id') {
                    $col['title'] = substr($col['title'],0,-3);
                    
                    $link_table = substr($col_id,0,-3);
                    $col['join'] = "`name` FROM `'.TABLE_PREFIX.'".$link_table."` WHERE `".$col_id."`";
                    $col['select'] = 'SELECT `'.$col_id."`, `name` FROM `'.TABLE_PREFIX.'".$link_table."` ORDER BY `name`";
                }


                if($table_col['Default'] != '') $col['new'] = $table_col['Default'];

                $col['type'] = 'STRING'; //default type if cannot identify
                $type = $table_col['Type'];
                if(substr($type,0,7)=='varchar') $col['type'] = 'STRING';
                if($type=='text' or $type=='longtext') $col['type'] = 'TEXT';
                if(substr($type,0,3)=='int') $col['type'] = 'INTEGER';
                if(substr($type,0,7)=='decimal' or $type=='double') $col['type'] = 'DECIMAL';
                if($type=='date') $col['type'] = 'DATE';
                if($type=='time') $col['type'] = 'TIME';
                if($type=='datetime') $col['type'] = 'DATETIME';
                if($type=='tinyint(1)') $col['type'] = 'BOOLEAN';

                //some refined guesses using field name
                if($col['type'] === 'STRING' and stripos($col_id,'password') !== false) $col['type'] = 'PASSWORD';
                if($col['type'] === 'STRING' and stripos($col_id,'email') !== false) $col['type'] = 'EMAIL';
                if($col['type'] === 'STRING' and (stripos($col_id,'url') !== false or stripos($col_id,'www') !== false)) $col['type'] = 'URL';

                $cols[$col_id] = $col;
            }
        }

        return $cols;
    }

    protected function createController($table)
    {       
        $str = '';
        $name = $this->getNames($table);

        $class_name_controller = $name['controller_class'];
        $class_name = $name['base_class'];
        
        $file_name = $this->create_dir.$name['controller_class'].'.php';
        if(file_exists($file_name)) {
            $this->addMessage('file['.$file_name.'] ALLREADY EXISTS, not created!');
        } else {
            if($_POST[$table.'type'] === 'CHILD') {
                $template = $this->template['child']; 
            } else {
                $template = $this->template['master'];
            }  

            $str.='<?php'."\r\n".
                  'namespace '.$this->name_space.';'."\r\n\r\n".
                  'use Psr\Container\ContainerInterface;'."\r\n".
                  'use '.$this->name_space.'\\'.$class_name.';'."\r\n\r\n".

                  'class '.$class_name_controller."\r\n".
                  '{'."\r\n".
                  '    protected $container;'."\r\n\r\n".
                  '    public function __construct(ContainerInterface $container)'."\r\n".
                  '    {'."\r\n". 
                  '        $this->container = $container;'."\r\n". 
                  '    }'."\r\n\r\n".

                  '    public function __invoke($request, $response, $args)'."\r\n".
                  '    {'."\r\n".
                  '        $table_name = TABLE_PREFIX.\''.$name['table'].'\';'."\r\n". 
                  '        $table = new '.$class_name.'($this->container->mysql,$this->container,$table_name);'."\r\n\r\n".

                  '        $table->setup();'."\r\n".
                  '        $html = $table->processTable();'."\r\n\r\n".
                   
                  '        $template[\'html\'] = $html;'."\r\n".
                  '        $template[\'title\'] = MODULE_LOGO.\''.$name['title'].'\';'."\r\n".
                   
                  '        return $this->container->view->render($response,\''.$template.'\',$template);'."\r\n".
                  '    }'."\r\n".
                  '}'."\r\n";
                

            if(file_put_contents($file_name,$str)) {
               $this->addMessage('SUCCESS creating "'.$file_name.'"');
            } else {
               $this->addError('ERROR creating "'.$file_name.'"');
            }

            $this->addRoute($name['route'],$class_name_controller);
        }
    } 

    protected function createFileController($file_type,$table,$location)
    {       
        $str = '';
        $name = $this->getNames($table);

        $template = $this->template['child']; 

        if($file_type === 'FILE') {
            $class_name_controller = $name['file_controller_class'];
            $class_name = $name['file_class'];
            $title = $name['title_child'].' documents';
            $route = $name['file_url'];
        }    
        if($file_type === 'IMAGE') {
            $class_name_controller = $name['image_controller_class'];
            $class_name = $name['image_class'];
            $title = $name['title_child'].' images';
            $route = $name['image_url'];
        }    

        $file_name = $this->create_dir.$class_name_controller.'.php';
        if(file_exists($file_name)) {
            $this->addMessage('file['.$file_name.'] ALLREADY EXISTS, not created!');
        } else {
            
            
            $str.='<?php'."\r\n".
                  'namespace '.$this->name_space.';'."\r\n\r\n".
                  'use Psr\Container\ContainerInterface;'."\r\n".
                  'use '.$this->name_space.'\\'.$class_name.';'."\r\n\r\n".

                  'class '.$class_name_controller."\r\n".
                  '{'."\r\n".
                  '    protected $container;'."\r\n\r\n".
                  '    public function __construct(ContainerInterface $container)'."\r\n".
                  '    {'."\r\n". 
                  '        $this->container = $container;'."\r\n". 
                  '    }'."\r\n\r\n".

                  '    public function __invoke($request, $response, $args)'."\r\n".
                  '    {'."\r\n".
                  '        $table_name = TABLE_PREFIX.\''.$this->table_standard['file'].'\';'."\r\n". 
                  '        $upload = new '.$class_name.'($this->container->mysql,$this->container,$table_name);'."\r\n\r\n".

                  '        $upload->setup();'."\r\n".
                  '        $html = $upload->processUpload();'."\r\n\r\n".
                   
                  '        $template[\'html\'] = $html;'."\r\n".
                  '        $template[\'title\'] = MODULE_LOGO.\''.$title.'\';'."\r\n".
                   
                  '        return $this->container->view->render($response,\''.$template.'\',$template);'."\r\n".
                  '    }'."\r\n".
                  '}'."\r\n";
                

            if(file_put_contents($file_name,$str)) {
               $this->addMessage('SUCCESS creating "'.$file_name.'"');
            } else {
               $this->addError('ERROR creating "'.$file_name.'"');
            }

            $this->addRoute($route,$class_name_controller);
                  
        }
    }

    protected function createFileClass($file_type,$table,$location)
    {       
        $str = '';
        $name = $this->getNames($table);

                
        if($file_type === 'FILE') {
            $class_name = $name['file_class'];
            $row_name = $name['row'].' document';
        }    
        if($file_type === 'IMAGE') {
            $class_name = $name['image_class'];
            $row_name = $name['row'].' image';
        }    

        $file_name = $this->create_dir.$class_name.'.php';
        if(file_exists($file_name)) {
            $this->addMessage('file['.$file_name.'] ALLREADY EXISTS, not created!');
        } else {
            $template = $this->template['child']; 
            
            $str.='<?php'."\r\n".
                  'namespace '.$this->name_space.';'."\r\n\r\n".
                  'use Seriti\Tools\Upload;'."\r\n".
                  'use Seriti\Tools\STORAGE;'."\r\n".
                  'use Seriti\Tools\BASE_PATH;'."\r\n".
                  'use Seriti\Tools\BASE_UPLOAD;'."\r\n".
                 
                  'class '.$class_name.' extends Upload'."\r\n".
                  '{'."\r\n".
                  '    public function setup($param = [])'."\r\n".
                  '    {'."\r\n". 
                  '        $id_prefix = \''.$location."';\r\n\r\n".

                  '        $param = [];'."\r\n".
                  '        $param[\'row_name\'] = \''.$row_name.'\';'."\r\n".
                  '        $param[\'pop_up\'] = true;'."\r\n".
                  '        $param[\'col_label\'] = \'file_name_orig\';'."\r\n".
                  '        $param[\'update_calling_page\'] = true;'."\r\n".
                  '        $param[\'prefix\'] = $id_prefix; //will prefix file_name if used, but file_id.ext is unique'."\r\n". 
                  '        $param[\'upload_location\'] = $id_prefix;'."\r\n". 
                  '        parent::setup($param);'."\r\n\r\n";
    
            if($file_type === 'FILE') {
                $str.='        //$this->allow_ext = [\'Documents\'=>[\'doc\',\'xls\',\'ppt\',\'pdf\',\'rtf\',\'docx\',\'xlsx\',\'pptx\',\'ods\',\'odt\',\'txt\',\'csv\',\'zip\',\'gz\',\'msg\',\'eml\']]; '."\r\n\r\n";
            }
            if($file_type === 'IMAGE') {
                $str.='        //$this->allow_ext = [\'Images\'=>[\'jpg\',\'jpeg\',\'gif\',\'png\']]; '."\r\n\r\n";
            }        
                  
            $str.='        $param = [];'."\r\n".
                  '        $param[\'table\']     = TABLE_PREFIX.\''.$name['table'].'\';'."\r\n".
                  '        $param[\'key\']       = \''.$name['table_key'].'\';'."\r\n".
                  '        $param[\'label\']     = \'name\';'."\r\n".
                  '        $param[\'child_col\'] = \'location_id\';'."\r\n".
                  '        $param[\'child_prefix\'] = $id_prefix;'."\r\n".
                  '        $param[\'show_sql\'] = \'SELECT CONCAT("'.$name['row'].': ",name) FROM \'.TABLE_PREFIX.\''.$name['table'].' WHERE '.$name['table_key'].' = "{KEY_VAL}" \';'."\r\n".
                  '        $this->setupMaster($param);'."\r\n\r\n".
    
                  '        $this->addAction([\'type\'=>\'edit\',\'text\'=>\'edit details of\',\'icon_text\'=>\'edit\']);'."\r\n".
                  '        $this->addAction([\'type\'=>\'delete\',\'text\'=>\'delete\',\'pos\'=>\'R\',\'icon_text\'=>\'delete\']);'."\r\n".
    
                  '        $this->info[\'ADD\'] = \'If you have Mozilla Firefox or Google Chrome you should be able to drag and drop files directly from your file explorer.\'.'."\r\n".
                  '                               \'Alternatively you can click [Add Documents] button to select multiple documents for upload using [Shift] or [Ctrl] keys. \'.'."\r\n".
                  '                               \'Finally you need to click [Upload selected Documents] button to upload documents to server.\';'."\r\n\r\n".
    
    
                  '        //$access[\'read_only\'] = true;'."\r\n".                         
                  '        //$this->modifyAccess($access);'."\r\n".
    
                  '    }'."\r\n".
                  '}'."\r\n";
                

            if(file_put_contents($file_name,$str)) {
               $this->addMessage('SUCCESS creating "'.$file_name.'"');
            } else {
               $this->addError('ERROR creating "'.$file_name.'"');
            }
                 
        }
    } 
    
    protected function createClass($table)
    {   
        $str = '';
        $name = $this->getNames($table);

        $cols = $this->getColumns($table);

        $class_name = $name['base_class'];

        $select = [];
        $search = [];
        $search_rows = floor(count($cols)/4);
        
        $file_name = $this->create_dir.$name['base_class'].'.php';
        if(file_exists($file_name)) {
            $this->addMessage('file['.$file_name.'] ALLREADY EXISTS, not created!');
        } else {

            $str.='<?php'."\r\n".
                  'namespace '.$this->name_space.';'."\r\n\r\n".
                  'use Seriti\Tools\Table;'."\r\n".
                  '//use Seriti\Tools\Date;'."\r\n".
                  '//use Seriti\Tools\Form;'."\r\n".
                  '//use Seriti\Tools\Secure;'."\r\n\r\n";

            //sort out master child relationships      
            $popup = 'false';
            $str_master = '';
            $str_action = '';
            $str_foreign = '';
            $indent = '        ';
            //NB used as flag to not show this col for CHILD table
            $master_key_col = '';

            //setup master
            if($_POST[$table.'type'] === 'CHILD') {
                $popup = 'true';
                
                $master_table = $_POST[$table.'linked'];
                $master_name = $this->getNames($master_table);
                $master_cols = $this->getColumns($master_table);
                foreach($master_cols as $col_id => $col) {
                    if($col['key']) $master_key_col = $col_id;
                }
                
                $str_master = $indent.'$this->setupMaster(['."'table'=>TABLE_PREFIX.'".$master_name['table']."','key'=>'".$master_key_col."','child_col'=>'".$master_key_col."',"."\r\n". 
                              $indent."                    'show_sql'=>'SELECT CONCAT(\"".$master_name['row'].": \",`name`) FROM `'.TABLE_PREFIX.'".$master_name['table']."` WHERE `".$master_key_col."` = \"{KEY_VAL}\" ']);".
                              "\r\n\r\n";
            } 

            //look for any children
            if($_POST[$table.'type'] === 'MASTER') {
                foreach($this->tables as $table_child) {
                    if($_POST[$table_child.'linked'] === $table) {
                        $child_name = $this->getNames($table_child);
                        $child_url = $child_name['table'];
                        $str_action .= $indent.'$this->addAction('."['type'=>'popup','text'=>'".$child_name['row']."','url'=>'".$child_url."','mode'=>'view','width'=>600,'height'=>600]);"."\r\n";

                        $str_foreign .= $indent.'$this->addForeignKey(['."'table'=>TABLE_PREFIX.'".$child_name['table']."','col_id'=>'".$name['table_key']."','message'=>'".$child_name['row']."s exist for this ".$name['row']."']);"."\r\n";
                    }   
                }
                if($str_action !== '') $str_action .= "\r\n"; 
                
            }

            $str.='class '.$class_name.' extends Table'."\r\n".
                  '{'."\r\n".
                  '    public function setup($param = []) '."\r\n".
                  '    {'."\r\n". 
                  '        $param = '."['row_name'=>'".$name['row']."','row_name_plural'=>'".$name['row_plural']."','col_label'=>'".$name['label']."','pop_up'=>".$popup."];\r\n".
                  '        parent::setup($param);'."\r\n\r\n";

            if($str_master !== '') $str .= $str_master;
            if($str_foreign !== '') $str .= $str_foreign."\r\n";

            $status_col = false;
            foreach($cols as $col_id => $col) {
                $show_col = true;
                if($col_id === $master_key_col) $show_col = false;

                if($show_col) {
                    $str .= '        $this->addTableCol('."['id'=>'".$col_id."','type'=>'".$col['type']."','title'=>'".$col['title']."'";
                    $key = false;
                    if($col['key']) {
                        $key = true;
                        $key_id = $col_id;
                        $str .= ",'key'=>true";
                    }    
                    if($col['key_auto']) $str .= ",'key_auto'=>true";

                    if(isset($col['join'])) {
                        $str .= ",'join'=>'".$col['join']."'";
                        $select[$col_id]  = $col['select'];
                    }
                   
                    if($col['type'] === 'DATE') $str .= ",'new'=>date('Y-m-d')";

                    if(stripos($col_id,'note') !== false or stripos($col_id,'comment') !== false) $str .= ",'required'=>false";

                    $str .= "]);\r\n"; 

                    if($col_id === 'status') $status_col = true;

                    $search[] = $col_id; 
                }    

            }
            $str.="\r\n\r\n";

            $str.='        $this->addSortOrder('."'T.".$key_id." DESC','Most recent first','DEFAULT');\r\n\r\n";

            $str.='        $this->addAction('."['type'=>'edit','text'=>'edit','icon_text'=>'edit']);\r\n";
            $str.='        $this->addAction('."['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);\r\n\r\n";

            //add any xtra actions
            $str .= $str_action;

            $str.='        $this->addSearch('."['".implode("','",$search)."'],['rows'=>".$search_rows."]);\r\n\r\n";

            if(count($select)) {
                foreach($select as $col_id => $sql) {
                    $str.='        $this->addSelect('."'".$col_id."','".$sql."');\r\n";
                }
            }

            if($status_col) {
               $str.='        $status = [\'OK\',\'HIDE\'];'."\r\n".
                     '        $this->addSelect(\'status\',[\'list\'=>$status,\'list_assoc\'=>false]);'."\r\n";
            }

            $str.="\r\n";

            if($_POST[$table.'files'] !== 'NA') {
                //file table location id prefix
                $location_id_prefix = $_POST[$table.'files'];

                $str.=$indent.'$this->setupFiles([\'table\'=>TABLE_PREFIX.\'file\',\'location\'=>\''.$location_id_prefix.'\',\'max_no\'=>100,'."\r\n".
                      $indent.'                   \'icon\'=>\'<span class="glyphicon glyphicon-file" aria-hidden="true"></span>&nbsp;manage\','."\r\n".
                      $indent.'                   \'list\'=>true,\'list_no\'=>5,\'storage\'=>STORAGE,'."\r\n".
                      $indent.'                   \'link_url\'=>\''.$name['file_url'].'\',\'link_data\'=>\'SIMPLE\',\'width\'=>\'700\',\'height\'=>\'600\']);'."\r\n\r\n";
            } 

            if($_POST[$table.'images'] !== 'NA') {
                //file table location id prefix
                $location_id_prefix = $_POST[$table.'images'];
                
                $str.=$indent.'$this->setupImages([\'table\'=>TABLE_PREFIX.\'file\',\'location\'=>\''.$location_id_prefix.'\',\'max_no\'=>10,'."\r\n".
                      $indent.'                   \'icon\'=>\'<span class="glyphicon glyphicon-picture" aria-hidden="true"></span>&nbsp;manage\','."\r\n".
                      $indent.'                   \'list\'=>true,\'list_no\'=>1,\'storage\'=>STORAGE,'."\r\n".
                      $indent.'                   \'link_url\'=>\''.$name['image_url'].'\',\'link_data\'=>\'SIMPLE\',\'width\'=>\'700\',\'height\'=>\'600\']);'."\r\n\r\n";
            }    


            $str.='    }'."\r\n\r\n";

            $str.='    /*** EVENT PLACEHOLDER FUNCTIONS ***/'."\r\n".
                  '    //protected function beforeUpdate($id,$context,&$data,&$error) {}'."\r\n".
                  '    //protected function afterUpdate($id,$context,$data) {}'."\r\n".  
                  '    //protected function beforeDelete($id,&$error) {}'."\r\n".
                  '    //protected function afterDelete($id) {}'."\r\n". 
                  '    //protected function beforeValidate($col_id,&$value,&$error,$context) {}'."\r\n\r\n".

                  '}'."\r\n";
                

            if(file_put_contents($file_name,$str)) {
               $this->addMessage('SUCCESS creating "'.$file_name.'"');
            } else {
               $this->addError('ERROR creating "'.$file_name.'"');
            }
        }    
    }    
    
}