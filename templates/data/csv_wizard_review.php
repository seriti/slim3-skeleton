<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;
use Seriti\Tools\Calc;
use Seriti\Tools\ImportCsv;

$input_param = ['class'=>'form-control'];

$unique_param = $input_param;
$unique_param['xtra'] = ['NONE'=>'NONE: Import without checking for unique values'];

//$input_param['onchange']='display_options();';


$html = '';

$html .= '<div class="row">';

//first column
$html .= '<div class="col-lg-5">';
$html .= '<h1>CSV file: '.$data['csv_file'].'</h1>'.
         '<h1>Database table: '.$form['db_table'].'</h1>'.
         '<h1>NB: Sample output. NO data imported yet.</h1>';
$html .= '</div>';

//second column
$html .= '<div class="col-lg-7">';

$html .= '<div class="row">'.
         '<div class="col-lg-6">'.Form::arrayList($data['table_col_select'],'unique_field',$form['unique_field'],true,$unique_param).'</div>'.
         '<div class="col-lg-6">Check this unique field value & ignore row if it exists.</div>'.
         '</div>';

$html .= '<div class="row">'.
         '<div class="col-lg-6">'.Form::checkBox('update_flag',true,$form['update_flag'],$input_param).'</div>'.
         '<div class="col-lg-6">Update existing data if unique field value is found.</div>'.
         '</div>';

$html .= '<div class="row">'.
         '<div class="col-lg-6">'.Form::checkBox('import_flag',true,$form['import_flag'],$input_param).'</div>'.
         '<div class="col-lg-6">Flag imported data using settings below.</div>'.
         '</div>';

$html .= '<div class="row">'.
         '<div class="col-lg-6">'.Form::arrayList($data['table_col_select'],'import_flag_field',$form['import_flag_field'],true,$input_param).'</div>'.
         '<div class="col-lg-6">Database field to set import flag value to.</div>'.
         '</div>';

$html .= '<div class="row">'.
         '<div class="col-lg-6">'.Form::textInput('import_flag_value',$form['import_flag_value'],$input_param).'</div>'.
         '<div class="col-lg-6">Flag value. Make this unique so you can rollback all imports that match this value.</div>'.
         '</div>';

$html .= '<div class="row">'.
         '<div class="col-lg-12">'.
         '<input type="submit" class="btn btn-primary" id="proceed_button" value="Output looks OK. Proceed with data import" onclick="link_download(\'proceed_button\');">'.
         '</div>'.
         '</div>';

$html .= '</div>';
         
$html .= '</div>';

$html .= '<div class="row"><div class="col-lg-12">';
$html .= $data['test_output'];
$html .= '</div></div>';
      
echo $html;          

//print_r($form);
//print_r($data);
?>
