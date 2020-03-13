<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;
use Seriti\Tools\Calc;
use Seriti\Tools\ImportCsv;

$input_param = ['class'=>'form-control'];
//$input_param['onchange']='display_options();';


$table = $form['db_table'];
$csv_format = $form['csv_format'];
$file_path = $data['csv_file_path'];


$html = '';

$html .= '<div class="row">';

//first column
$html .= '<div class="col-lg-8">';
$html .= '<h1>CSV file: '.$data['csv_file'].'</h1>'.
         '<h1>Database table: '.$form['db_table'].'</h1>'.
         '<input type="submit" class="btn btn-primary" id="proceed_button" value="Show sample data mapping" onclick="link_download(\'proceed_button\');">';

$html .= $data['col_map_form'];

$html .= '</div>';

//second column

$html .= '<div class="col-lg-4">';

$html .= '<h1>Information</h1><ul>';
$html .= '<li>Select IGNORE convert type if you do NOT wish to import that column from csv file. The database field selection is also ignored.</li>';
$html .= '<li>Select STRING convert type if column value has a maximum of 250 text characters .</li>';
$html .= '<li>Select TEXT convert type if column value has a maximum of 64000 text characters .</li>';
$html .= '<li>Select INTEGER convert type if column value is a whole number.</li>';
$html .= '<li>Select DECIMAL convert type if column value is number with decimal point values.</li>';
$html .= '<li>Select DATE-YYYY-MM-DD convert type if column value is a date with YYYY-MM-DD format.</li>';
$html .= '<li>Select DATE-YYYY-MM-DD HH:MM convert type if column value is a datetime with YYYY-MM-DD HH:MM format.</li>';
$html .= '<li>Select TIME-HH:MM:SS convert type if column value is a time with HH:MM:SS format.</li>';
$html .= '<li>Select BOOLEAN convert type if column value is a 1 or 0. Yes or No,  Y or N, True or False, T or F are acceptable and will be converted.</li>';
$html .= '<li>Select MERGE-LN convert type if you wish to combine column value with previous column value. The previous column convert type must be STRING or TEXT.</li>';

$html .= '<p>The database field selection can only be assigned to a single column.</p>';
$html .= '</ul>';

$html .= '</div>';
         

$html .= '</div>';
      
echo $html;          

//print_r($form);
//print_r($data);
?>
