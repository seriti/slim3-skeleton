<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;


$html = '';

$html .= '<div class="row">'.
         '<div class="col-lg-12">';

$html .= '<h1>Data import completed.</h1>';

$html .= '<h2>CSV file: '.$data['csv_file'].'</h2>'.
         '<h2>Database table: '.$form['db_table'].'</h2>'.
         '<h2>'.$data['import_result'].'</h2>';

$html .= '<p>All data mappings for this table have been saved for next time you import.</p>';

$html .= '<a href="import_csv"><button class="btn btn-primary">Restart wizard</button></a>';

$html .= '</div>'.
         '</div>';      
      
echo $html;          

//print_r($form);
//print_r($data);
?>
