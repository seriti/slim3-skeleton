format<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$input_param = ['class'=>'form-control'];
$db_tables = $data['tables']; 

$csv_format = ['COMMA'=>'Comma separator[,] & column enclosure["]','SEMICOLON'=>'Semi-colon separator[;] & column enclosure["]'];

$html = '';

$html .= '<div class="row">'.
         '<div class="col-lg-6">';

$html .= '<div class="row"><div class="col-lg-12">'.
         '1.) Select the CSV file you want to import:<br/>'.
         Form::fileInput('csv_file','',$input_param).
         '</div></div>';

$html .= '<div class="row"><div class="col-lg-12">'.
         '2.) Select the format of your CSV file:<br/>'.
         Form::arrayList($csv_format,'csv_format',$form['csv_format'],true,$input_param).
         '</div></div>';


$html .= '<div class="row"><div class="col-lg-12">'.
         '3.) Specify the destination table for CSV data:<br/>'.
         Form::arrayList($db_tables,'db_table',$form['db_table'],true,$input_param).
         '</div></div>';


$html .= '<div class="row"><div class="col-lg-12">'.
         '4.) Upload CSV file and review data mapping to table fields...<br/>'.
         '<input type="submit" class="btn btn-primary" id="import_button" value="Review CSV data" onclick="link_download(\'import_button\');">'.
         '</div></div>';

$html .= '</div>'.
         '<div class="col-lg-6">';        
                        
$html .= '<div class="row"><div class="col-lg-12">'.
         '<p><b>NB1:</b> Your CSV text file must have .csv or .txt extension</p>'.
         '<p><b>NB2:</b> Default format is Comma Separated Values and " escape character!</p>'.
         '<p><b>NB3:</b> You will be able to review all details and data mappings before importing!</p>'.
         '</div></div>';       
        
$html .= '</div>'.
         '</div>';      
      
echo $html;          

//print_r($form);
//print_r($data);
?>
