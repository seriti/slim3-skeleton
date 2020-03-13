<?php
namespace App\Data;

use Seriti\Tools\Wizard;
use Seriti\Tools\Mysql;
use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Doc;
use Seriti\Tools\Calc;
use Seriti\Tools\Amazon;
use Seriti\Tools\ImportCsv;

use Seriti\Tools\STORAGE;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\UPLOAD_DOCS;

use App\Data\Helpers;


class ImportCsvWizard extends Wizard 
{
    protected $tables = [];
    protected $system;

    protected $upload_dir = BASE_UPLOAD.UPLOAD_TEMP;
    protected $max_size = 1000000;
       
    //configure
    public function setup($param = []) 
    {
        $wizard_param = ['bread_crumbs'=>true,'strict_var'=>false];
        $wizard_param['csrf_token'] = $this->getContainer('user')->getCsrfToken();
        parent::setup($wizard_param);

        //need system for saving table defaults
        $this->system = $this->getContainer('system');

        //must be an absolute path
        if(isset($param['upload_dir'])) $this->upload_dir = $param['upload_dir']; 
        
        if(!isset($param['tables'])) {
            $this->data['tables'] = Helpers::getAllTables($this->db); 
        } else {
            $this->data['tables'] = $param['tables'];
        }
        //so available on templates
        //$this->saveData('data');
        
        //define all wizard variables to be captured and stored for all wizard pages
        $this->addVariable(array('id'=>'db_table','type'=>'STRING','title'=>'Database table'));
        $this->addVariable(array('id'=>'unique_field','type'=>'STRING','title'=>'Field in table that must be unique','new'=>'NONE'));
        $this->addVariable(array('id'=>'csv_format','type'=>'STRING','title'=>'CSV File format'));
        $this->addVariable(array('id'=>'csv_file','type'=>'STRING','title'=>'CSV File Path'));
        //use these to set a table field with import details so can identify imports for reversal or whatever
        $this->addVariable(array('id'=>'import_flag','type'=>'BOOLEAN','title'=>'Set import flag so can reverse import','new'=>true));
        $this->addVariable(array('id'=>'import_flag_field','type'=>'STRING','title'=>'Table field to store import flag value','new'=>'import_flag'));
        $this->addVariable(array('id'=>'import_flag_value','type'=>'STRING','title'=>'Unique Import flag value to identify imported data','new'=>date('Y-m-d')));
        
        
        //define pages and templates
        $this->addPage(1,'Specify Table and CSV File','data/csv_wizard_start.php');
        $this->addPage(2,'Review data links','data/csv_wizard_links.php');
        $this->addPage(3,'Review claim data','data/csv_wizard_review.php');
        $this->addPage(4,'Confirmation page','data/csv_wizard_final.php',['final'=>true]);

    }

    

    public function processPage() 
    {
        $error = '';
        $message = '';
        $error_tmp = '';

        //upload bank file and display allocations for review
        if($this->page_no == 1) {
            $file_options = array();
            $file_options['upload_dir'] = $this->upload_dir;
            $file_options['allow_ext'] = array('csv','txt');
            $file_options['max_size'] = $max_size;
            $save_name = $this->form['db_table'].'_import';
            $file_name = Form::uploadFile('csv_file',$save_name,$file_options,$error);
            if($error !== '') {
                if($error !== 'NO_FILE') {
                    $this->addError('CSV file error: '.$error.'***'.$this->upload_dir);
                } else {
                    $this->addError('NO CSV file selected for import! Please click [Browse/Choose file] button and select a valid CSV file');
                }    
            } else {
                $csv_file_path = $this->upload_dir.$file_name;
                if(!file_exists($csv_file_path)) {
                    $this->addError('Import File['.$csv_file_path.'] does not exist');
                } else {
                    $this->data['csv_file_path'] = $csv_file_path;
                }  
            }


            if(!$this->errors_found) {
                $import = new ImportCsv($this->db,$this->form['db_table'],$this->form['csv_format']);
                $param = [];
                $param['file_path'] = $csv_file_path;
                $setup_valid = $import->setup($param);
                if(!$setup_valid) {
                    $this->errors_found = true;
                    $this->errors = array_merge($import->getErrors(),$this->errors);
                } 

                $this->data['csv_file'] = $file_name;
                $this->data['csv_file_path'] = $csv_file_path;
                
                if(isset($this->data['link_form'])) {
                    $form_data = $this->data['link_form']; 
                } else {
                    //will get previous link values for table if any exist
                    $default_id = 'IMPORT_'.strtoupper($this->form['db_table']); 
                    $default_data = $this->system->getDefault($default_id,'NONE');
                    if($default_data === 'NONE') {
                        $form_data = [];
                    } else {
                        $form_data = json_decode($default_data,true);
                    }
                }    
                $this->data['col_map_form'] = $import->getLinkForm($form_data);    
            }
        } 
                
        if($this->page_no == 2) {
            //print_r($this->form);
            //die('WTF');
            $import = new ImportCsv($this->db,$this->form['db_table'],$this->form['csv_format']);
            $param = [];
            $param['file_path'] = $this->data['csv_file_path'];
            //NB: will not import data just show import data for X rows
            $param['test'] = true;
            $setup_valid = $import->setup($param);

            $output = $import->processLinkForm();
            if(!$setup_valid or $output['errors_found']) {
                $this->errors_found = true;
                $this->errors = array_merge($import->getErrors(),$this->errors);
            } else {
                //in test mode! Errors are included in output.
                $this->data['test_output'] = $import->importCsvData();
            }

            //need for final processing
            $this->data['link_cols'] = $output['cols'];

            //regenerate link form with selections fot display in earlier steps if selected
            $form_data = $import->getForm();
            $this->data['link_form'] = $form_data;
            $this->data['col_map_form'] = $import->getLinkForm($form_data); 

            
        }  
        
        if($this->page_no == 3) {
            //save table link form values for next usage regardless of import success as assume user happy with linking
            $default_id = 'IMPORT_'.strtoupper($this->form['db_table']); 
            $this->system->setDefault($default_id,json_encode($this->data['link_form']));

            //finally process import
            $import = new ImportCsv($this->db,$this->form['db_table'],$this->form['csv_format']);
            $param = [];
            $param['file_path'] = $this->data['csv_file_path'];
            $param['setup_cols'] = $this->data['link_cols'];

            if($this->form['unique_field'] !== 'NONE') $param['unique_field'] = $this->form['unique_field'];
            
            $param['import_flag'] = $this->form['import_flag'];
            $param['import_flag_field'] = $this->form['import_flag_field'];
            $param['import_flag_value'] = $this->form['import_flag_value'];
            $setup_valid = $import->setup($param);

            if(!$setup_valid) {
                $this->errors_found = true;
                $this->errors = array_merge($import->getErrors(),$this->errors);
            } else {
                //process import for real, Errors are included in result.
                $this->data['import_result'] = $import->importCsvData();
            }
         
        } 
    }

    public function setupPageData($no) 
    {
        
        //only enable this if user knows what they are doing 
        if($no == 3) {
            if(!isset($this->data['table_col_select'])) {
                $this->data['table_col_select'] = Helpers::getAllTableCols($this->db,$this->form['db_table']);
            }

        }
        
    }

}
