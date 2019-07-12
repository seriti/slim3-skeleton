<?php
namespace App\Customise;

use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_DOCS;
use Seriti\Tools\Mysql;
use Seriti\Tools\Form;
use Seriti\Tools\Image;
use Seriti\Tools\DbInterface;

class PdfSetup
{
    protected $db;
    protected $debug = false;

    public function __construct(DbInterface $db) 
    {
        $this->db = $db;
       
        if(defined('\Seriti\Tools\DEBUG')) $this->debug = \Seriti\Tools\DEBUG;
    }

    public function getJavascript()
    {
        return '<script type="text/javascript" src="'.BASE_URL.'include/jscolor/jscolor.js"></script>';
        
    }

    public function process() {
        $error_str='';
        $error=array();
        $message=array();
        $report_id='';


        $sql_get='SELECT sys_text FROM system WHERE system_id = "{DEFAULT_ID}"';
        $sql_set='REPLACE INTO system (system_id,sys_count,sys_text) VALUES("{DEFAULT_ID}",1,"{DEFAULT_VALUE}") ';
        $sql_del='DELETE FROM system WHERE system_id LIKE "PDF_%"';


        $layout['show_desc']=true;

        //***** could make include file later if need to split above into two files ******************************

        $max_file_size=1000000;
        //NO http access required or desired!
        $upload_dir=BASE_UPLOAD.UPLOAD_DOCS;
        //$upload_dir=BASE_PATH.BASE_UPLOAD_WWW;//FILE_UPLOAD_DIR;
        //$upload_url=BASE_URL.BASE_UPLOAD_WWW;
        $default_image_url='images/pdf_sample_logo.png';
        $A4_width=200;
        $A4_height=300;
        $margin_max=50;
        $audit_changes=true;

        $date_array=array('DD-MM-YYYY','DD-MMM-YYYY','MM-DD-YYYY','MMM-DD-YYYY','MMMYY','MMM-YYYY');
        $font_face=array('arial'=>'Arial','times'=>'Times new roman','helvetica'=>'Helvetica','courier'=>'Courier');
        $font_style=array('N'=>'Normal','B'=>'Bold','I'=>'Italic','U'=>'Underline');
        $font_size=array(8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36);

        $text_align=array('L'=>'Left','R'=>'Right','C'=>'Center');

        $mode='list_all';
        if(@$_GET['mode']) $mode=$_GET['mode'];

        $date_now=getdate();

        if($mode=='delete') {
        	$sql=$sql_del;
        	$this->db->executeSql($sql,$error_str);
        	if($error_str=='') {
                $message[]='Successfully reset PDF layout parameters. Default values shown.';
            } else {
                $error[]='Could NOT reset PDF settings';
            }    
            
        	$mode='list_all';	
        }	

        if($mode=='list_all') {
        	
            $sql=str_replace('{DEFAULT_ID}','PDF_IMAGE',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str=$default_image_url.',5,10,15,15,YES';
            $value_arr=explode(',',$value_str);
            $pdf_image=$value_arr[0];
            $image_top=$value_arr[1];
            $image_left=$value_arr[2];
            $image_width=$value_arr[3];
            $image_height=$value_arr[4];
        	if(isset($value_arr[5])) $image_hide=$value_arr[5]; else $image_hide='NO';
            
            $sql=str_replace('{DEFAULT_ID}','PDF_MARGIN',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='20,10,10,20';
            $value_arr=explode(',',$value_str);
            $margin_top=$value_arr[0];
            $margin_left=$value_arr[1];
            $margin_right=$value_arr[2];
            $margin_bottom=$value_arr[3];
            
            $sql=str_replace('{DEFAULT_ID}','PDF_FONT',$sql_get);
            $pdf_font=$this->db->readSqlValue($sql);
            if($pdf_font===0) $pdf_font='arial';
          
            $sql=str_replace('{DEFAULT_ID}','PDF_DATE',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='DD-MMM-YYYY';
            $date_format=$value_str;
             
            $sql=str_replace('{DEFAULT_ID}','PDF_H1',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='33,33,33,B,10,"",15,30,L,NO,33,33,33,B,12,20,50';
            $value_arr=explode(',',$value_str);
            $h1_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $h1_style=$value_arr[3];
            $h1_size=$value_arr[4];
            $h1_text=$value_arr[5];
            $h1_text_top=$value_arr[6];
            $h1_text_left=$value_arr[7];
            $h1_text_align=$value_arr[8];
            $h1_date=$value_arr[9];
            $h1_date_color='#'.$value_arr[10].$value_arr[11].$value_arr[12];
            $h1_date_style=$value_arr[13];
            $h1_date_size=$value_arr[14];
            $h1_date_top=$value_arr[15];
            $h1_date_left=$value_arr[16];
               
            $sql=str_replace('{DEFAULT_ID}','PDF_H2',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='33,33,33,B,10';
            $value_arr=explode(',',$value_str);
            $h2_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $h2_style=$value_arr[3];
            $h2_size=$value_arr[4];
          
            $sql=str_replace('{DEFAULT_ID}','PDF_H3',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='33,33,33,B,8';
            $value_arr=explode(',',$value_str);
            $h3_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $h3_style=$value_arr[3];
            $h3_size=$value_arr[4];
            
            $sql=str_replace('{DEFAULT_ID}','PDF_TEXT',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='33,33,33,N,8';
            $value_arr=explode(',',$value_str);
            $pdf_text_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $pdf_text_style=$value_arr[3];
            $pdf_text_size=$value_arr[4];
            
            $sql=str_replace('{DEFAULT_ID}','PDF_LINK',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='00,00,FF,N,8';
            $value_arr=explode(',',$value_str);
            $pdf_link_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $pdf_link_style=$value_arr[3];
            $pdf_link_size=$value_arr[4];
            
            $sql=str_replace('{DEFAULT_ID}','PDF_TABLE',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='00,00,00,N,10,CC,CC,CC';
            $value_arr=explode(',',$value_str);
            $table_hr_font_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $table_hr_font_style=$value_arr[3];
            $table_hr_font_size=$value_arr[4];
            $table_hr_bg_color='#'.$value_arr[5].$value_arr[6].$value_arr[7];
        	  	
            $sql=str_replace('{DEFAULT_ID}','PDF_FOOT',$sql_get);
            $value_str=$this->db->readSqlValue($sql);
            if($value_str===0) $value_str='33,33,33,B,8,,5,0,C';
            $value_arr=explode(',',$value_str);
            $foot_color='#'.$value_arr[0].$value_arr[1].$value_arr[2];
            $foot_style=$value_arr[3];
            $foot_size=$value_arr[4];
            $foot_text=$value_arr[5];
            $foot_text_top=$value_arr[6];
            $foot_text_left=$value_arr[7];
            $foot_text_align=$value_arr[8];
        }

        if($mode=='update') {
          //first process any uploaded image
            if(isset($_FILES['pdf_image']) and $_FILES['pdf_image']['size']>0) {
                $file_base_name=basename($_FILES['pdf_image']['name']);
                $file_size=$_FILES['pdf_image']['size'];

                $temp=explode(".",$file_base_name);
                $file_ext=strtolower($temp[count($temp)-1]);

                switch($file_ext) {
                    case 'png' : break;
                    case 'jpeg': break;
                    case 'jpg' : $file_ext='jpeg'; break;
                    case 'bmp' : $error[]='<br>'.'BMP Image format not supported. Please upload a JPEG or PNG format image. Thanks. '; break;
                    case 'gif' : $error[]='<br>'.'GIF Image format not supported. Please upload a JPEG or PNG format image. Thanks. '; break;
                    default: $error[]='Image File is not a supported image filetype. Please upload a JPEG or PNG format image. Thanks. '; break;
                }
                
                if($file_size>$max_file_size) $error[]='File['.$file_base_name.'] size['.round($file_size/1000,1).'Kb] greater than maximum allowed['.($max_file_size/1000).'Kb] ';
                
                if(count($error)==0)    {
                    //get latest image count and increment by 1
                    $sql='SELECT sys_count FROM system WHERE system_id = "IMAGES" ';
                    $k=$this->db->readSqlValue($sql)+1;
                    $sql='UPDATE system SET sys_count = sys_count + 1 WHERE system_id = "IMAGES" ';
                    $this->db->executeSql($sql,$error_str);
                  
                    $file_name='PDF_logo'.$k.'.'.$file_ext;
                    $file_path=$upload_dir.$file_name;
                  
                    if(!move_uploaded_file($_FILES['pdf_image']['tmp_name'],$file_path))  {
                        $error[]='<br/>Error moving uploading file['.$file_base_name.']' ;
                    } 
                }
            } else {
                //if no image file uploaded then need to get existing image name
                $sql=str_replace('{DEFAULT_ID}','PDF_IMAGE',$sql_get);
                $value_str=$this->db->readSqlValue($sql);
                if($value_str===0) {
                    $file_name=$default_image_url;
                } else {  
                    $value_arr=explode(',',$value_str);
                    $file_name=$value_arr[0];
                }  
            }
            $pdf_image=$file_name;

            //image position/scale settings
            $image_top=$_POST['image_top'];
            $image_left=$_POST['image_left'];
            $image_width=$_POST['image_width'];
            $image_height=$_POST['image_height'];
          	if(isset($_POST['image_hide'])) $image_hide='YES'; else $image_hide='NO';
            
            if(!is_numeric($image_top)) $error[]='Image top position is NOT a numeric value!<br/>';
            if(!is_numeric($image_left)) $error[]='Image left position is NOT a numeric value!<br/>';
            if(!is_numeric($image_width)) $error[]='Image width is NOT a numeric value!<br/>';
            if(!is_numeric($image_height)) $error[]='Image height is NOT a numeric value!<br/>';
            if(count($error)==0)
            {
                $image_top=floor($image_top);
                if($image_top<0 or $image_top>$A4_height) $error[]="Image top position must have a value between 0 and $A4_height mm!<br/>";
                $image_left=floor($image_left);
                if($image_left<0 or $image_top>$A4_width) $error[]="Image left position must have a value between 0 and $A4_width mm!<br/>";
                $image_width=floor($image_width);
                if($image_width<0 or $image_width>$A4_width) $error[]="Image width must have a value between 0 and $A4_width mm!<br/>";
                $image_height=floor($image_height);
                if($image_height<0 or $image_height>$A4_height) $error[]="Image height must have a value between 0 and $A4_height mm!<br/>";
            }
          
            //margin settings
            $margin_top=$_POST['margin_top'];
            $margin_left=$_POST['margin_left'];
            $margin_right=$_POST['margin_right'];
            $margin_bottom=$_POST['margin_bottom'];
            
            if(!is_numeric($margin_top)) $error[]='Top margin is NOT a numeric value!<br/>';
            if(!is_numeric($margin_left)) $error[]='Left margin is NOT a numeric value!<br/>';
            if(!is_numeric($margin_right)) $error[]='Right margin is NOT a numeric value!<br/>';
            if(!is_numeric($margin_bottom)) $error[]='Bottom margin is NOT a numeric value!<br/>';
            if(count($error)==0)
            {
                $margin_top=floor($margin_top);
                if($margin_top<0 or $margin_top>$margin_max) $error[]="Top margin must have a value between 0 and $margin_max mm!<br/>";
                $margin_left=floor($margin_left);
                if($margin_left<0 or $margin_left>$margin_max) $error[]="Left margin must have a value between 0 and $margin_max mm!<br/>";
                $margin_right=floor($margin_right);
                if($margin_right<0 or $margin_right>$margin_max) $error[]="Right margin must have a value between 0 and $margin_max mm!<br/>";
                $margin_bottom=floor($margin_bottom);
                if($margin_bottom<0 or $margin_bottom>$margin_max) $error[]="Bottom margin must have a value between 0 and $margin_max mm!<br/>";
            }
          
            //FONT
            $pdf_font=$_POST['pdf_font'];
            if(array_key_exists($pdf_font,$font_face)===false) $error[]='Invalid font family selected!<br/>';
              
            //DATE FORMAT
            $date_format=$_POST['date_format'];
            if(in_array($date_format,$date_array)===false) $error[]='Invalid date format selected!<br/>';
            
            //H1 : All Page Header settings
            $h1_color=$_POST['h1_color'];
            $h1_style=$_POST['h1_style'];
            $h1_size=$_POST['h1_size'];
            
            $h1_text=$_POST['h1_text'];
            $h1_text_top=$_POST['h1_text_top'];
            $h1_text_left=$_POST['h1_text_left'];
            $h1_text_align=$_POST['h1_text_align'];
                  
            if(array_key_exists($h1_style,$font_style)===false) $error[]='Invalid H1 font style!<br/>';
            if(strlen($h1_color)>7)
            {
                $error[]='H1 color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($h1_color[0]=='#') $hex_color=substr($h1_color,1,6); else $hex_color=$h1_color;  
                $h1_r=substr($hex_color,0,2);
                $h1_g=substr($hex_color,2,2);
                $h1_b=substr($hex_color,4,2); 
            }
            if(array_search($h1_size,$font_size)===false) $error[]='Invalid H1 font size! Must have a value between 8 and 36';
            
            if(strlen($h1_text)>100) $error[]='Invalid H1 text! Must be less than 100 characters!';
            
            if(!is_numeric($h1_text_top)) $error[]='H1 page header Top position is NOT a numeric value!<br/>';
            if(!is_numeric($h1_text_left)) $error[]='H1 page header Left position is NOT a numeric value!<br/>';
            if(count($error)==0)
            {
                $h1_text_top=floor($h1_text_top);
                if($h1_text_top<0 or $h1_text_top>$margin_top) $error[]="H1 page header Top must have a value between 0 and $margin_top mm (top margin)!<br/>";
                $h1_text_left=floor($h1_text_left);
                if($h1_text_left<0 or $h1_text_left>300) $error[]="H1 page header Left must have a value between 0 and 300mm !<br/>";
            }
            
            if(array_key_exists($h1_text_align,$text_align)===false) $error[]='Invalid H1 text alignment!';
            
            if(isset($_POST['h1_date'])) $h1_date='YES'; else $h1_date='NO';
            $h1_date_color=$_POST['h1_date_color'];
            $h1_date_style=$_POST['h1_date_style'];
            $h1_date_size=$_POST['h1_date_size'];
            $h1_date_top=$_POST['h1_date_top'];
            $h1_date_left=$_POST['h1_date_left'];
           
            if(array_key_exists($h1_date_style,$font_style)===false) $error[]='Invalid page header date font style!<br/>';
            if(strlen($h1_date_color)>7) {
                $error[]='Page header date color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($h1_date_color[0]=='#') $hex_color=substr($h1_date_color,1,6); else $hex_color=$h1_date_color;  
                $h1_date_r=substr($hex_color,0,2);
                $h1_date_g=substr($hex_color,2,2);
                $h1_date_b=substr($hex_color,4,2); 
            }
            if(array_search($h1_date_size,$font_size)===false) $error[]='Invalid page header date font size! Must have a value between 8 and 36';
            
            if(!is_numeric($h1_date_top)) $error[]='Page header date Top position is NOT a numeric value!<br/>';
            if(!is_numeric($h1_date_left)) $error[]='Page header date Left position is NOT a numeric value!<br/>';
            if(count($error)==0) {
                $h1_date_top=floor($h1_date_top);
                //if($h1_date_top<0 or $h1_date_top>$margin_top) $error[]="Page header date Top must have a value between 0 and $margin_top mm (top margin)!<br/>";
                $h1_date_left=floor($h1_date_left);
                if($h1_date_left<0 or $h1_date_left>300) $error[]="Page header date Left must have a value between 0 and 300mm !<br/>";
            }
             
            //H2
            $h2_color=$_POST['h2_color'];
            $h2_style=$_POST['h2_style'];
            $h2_size=$_POST['h2_size'];
            
            if(array_key_exists($h2_style,$font_style)===false) $error[]='Invalid H2 font style!<br/>';
            if(strlen($h2_color)>7)
            {
                $error[]='H2 color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($h2_color[0]=='#') $hex_color=substr($h2_color,1,6); else $hex_color=$h2_color;  
                 $h2_r=substr($hex_color,0,2);
                $h2_g=substr($hex_color,2,2);
                 $h2_b=substr($hex_color,4,2); 
            }
            if(array_search($h2_size,$font_size)===false) $error[]='Invalid H2 font size! Must have a value between 8 and 36';
            
            //H3
            $h3_color=$_POST['h3_color'];
            $h3_style=$_POST['h3_style'];
            $h3_size=$_POST['h3_size'];
            
            if(array_key_exists($h3_style,$font_style)===false) $error[]='Invalid H3 font style!<br/>';
            if(strlen($h3_color)>7)
            {
                $error[]='H3 color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($h3_color[0]=='#') $hex_color=substr($h3_color,1,6); else $hex_color=$h3_color;  
                $h3_r=substr($hex_color,0,2);
                $h3_g=substr($hex_color,2,2);
                $h3_b=substr($hex_color,4,2); 
            }
            if(array_search($h3_size,$font_size)===false) $error[]='Invalid H3 font size! Must have a value between 8 and 36';

          
            //TEXT
            $pdf_text_color=$_POST['pdf_text_color'];
            $pdf_text_style=$_POST['pdf_text_style'];
            $pdf_text_size=$_POST['pdf_text_size'];
            
            if(array_key_exists($pdf_text_style,$font_style)===false) $error[]='Invalid standard text font style!<br/>';
            if(strlen($pdf_text_color)>7)
            {
                $error[]='Standard text color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($pdf_text_color[0]=='#') $hex_color=substr($pdf_text_color,1,6); else $hex_color=$pdf_text_color;  
                $text_r=substr($hex_color,0,2);
                $text_g=substr($hex_color,2,2);
                $text_b=substr($hex_color,4,2); 
            }
            if(array_search($pdf_text_size,$font_size)===false) $error[]='Invalid standard text font size! Must have a value between 8 and 36';
            
            //LINKS
            $pdf_link_color=$_POST['pdf_link_color'];
            $pdf_link_style=$_POST['pdf_link_style'];
            $pdf_link_size=$_POST['pdf_link_size'];
            
            if(array_key_exists($pdf_link_style,$font_style)===false) $error[]='Invalid link text font style!<br/>';
            if(strlen($pdf_link_color)>7)
            {
                $error[]='Link text color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($pdf_link_color[0]=='#') $hex_color=substr($pdf_link_color,1,6); else $hex_color=$pdf_link_color;  
                $link_r=substr($hex_color,0,2);
                $link_g=substr($hex_color,2,2);
                $link_b=substr($hex_color,4,2); 
            }
            if(array_search($pdf_link_size,$font_size)===false) $error[]='Invalid link text font size! Must have a value between 8 and 36';
          
            //TABLES
            $table_hr_font_color=$_POST['table_hr_font_color'];
            $table_hr_font_style=$_POST['table_hr_font_style'];
            $table_hr_font_size=$_POST['table_hr_font_size'];
            $table_hr_bg_color=$_POST['table_hr_bg_color'];
            
            if(array_key_exists($table_hr_font_style,$font_style)===false) $error[]='Invalid table header row font style!<br/>';
            if(strlen($table_hr_font_color)>7)
            {
                $error[]='Table header row font color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($table_hr_font_color[0]=='#') $hex_color=substr($table_hr_font_color,1,6); else $hex_color=$table_hr_font_color;  
                $table_hr_r=substr($hex_color,0,2);
                $table_hr_g=substr($hex_color,2,2);
                $table_hr_b=substr($hex_color,4,2); 
            }
            if(array_search($table_hr_font_size,$font_size)===false) $error[]='Invalid table header font size! Must have a value between 8 and 36';
            if(strlen($table_hr_bg_color)>7)
            {
                $error[]='Table header row background color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($table_hr_bg_color[0]=='#') $hex_color=substr($table_hr_bg_color,1,6); else $hex_color=$table_hr_bg_color;  
                $table_hr_bg_r=substr($hex_color,0,2);
                $table_hr_bg_g=substr($hex_color,2,2);
                $table_hr_bg_b=substr($hex_color,4,2); 
            }
          
        	//FOOTER text
        	$foot_color=$_POST['foot_color'];
            $foot_style=$_POST['foot_style'];
            $foot_size=$_POST['foot_size'];
            
            $foot_text=$_POST['foot_text'];
            $foot_text_top=$_POST['foot_text_top'];
            $foot_text_left=$_POST['foot_text_left'];
            $foot_text_align=$_POST['foot_text_align'];
                  
            if(array_key_exists($foot_style,$font_style)===false) $error[]='Invalid footer font style!<br/>';
            if(strlen($foot_color)>7)
            {
                $error[]='footer color must be in hexadecimal format "#RRGGBB"!<br/>';
            } else {
                if($foot_color[0]=='#') $hex_color=substr($foot_color,1,6); else $hex_color=$foot_color;  
                $foot_r=substr($hex_color,0,2);
                $foot_g=substr($hex_color,2,2);
                $foot_b=substr($hex_color,4,2); 
            }
            if(array_search($foot_size,$font_size)===false) $error[]='Invalid footer font size! Must have a value between 8 and 36';
            
            if(strlen($foot_text)>500) $error[]='Invalid footer text! Must be less than 500 characters!';
            
            if(!is_numeric($foot_text_top)) $error[]='footer text Top position is NOT a numeric value!<br/>';
            if(!is_numeric($foot_text_left)) $error[]='footer text Left position is NOT a numeric value!<br/>';
            if(count($error)==0)
            {
                $foot_text_top=floor($foot_text_top);
                if($foot_text_top<0 or $foot_text_top>$margin_bottom) $error[]="footer text Top must have a value between 0 and $margin_bottom mm (bottom margin)!<br/>";
                $foot_text_left=floor($foot_text_left);
                if($foot_text_left<0 or $foot_text_left>300) $error[]="footer text Left must have a value between 0 and 300mm !<br/>";
            }
            
            if(array_key_exists($foot_text_align,$text_align)===false) $error[]='Invalid footer text alignment!';
          
            //finally actually update defaults
            if(count($error)==0) {
                $value_str=$pdf_image.','.$image_top.','.$image_left.','.$image_width.','.$image_height.','.$image_hide;
                $sql=str_replace('{DEFAULT_ID}','PDF_IMAGE',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);

                $value_str=$margin_top.','.$margin_left.','.$margin_right.','.$margin_bottom;
                $sql=str_replace('{DEFAULT_ID}','PDF_MARGIN',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                    
                $sql=str_replace('{DEFAULT_ID}','PDF_FONT',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($pdf_font),$sql);
                $this->db->executeSql($sql,$error_str);
                    
                $sql=str_replace('{DEFAULT_ID}','PDF_DATE',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($date_format),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$h1_r.','.$h1_g.','.$h1_b.','.$h1_style.','.$h1_size.','.
                           $h1_text.','.$h1_text_top.','.$h1_text_left.','.$h1_text_align.','.
                           $h1_date.','.$h1_date_r.','.$h1_date_g.','.$h1_date_b.','.$h1_date_style.','.$h1_date_size.','.
                           $h1_date_top.','.$h1_date_left;
                $sql=str_replace('{DEFAULT_ID}','PDF_H1',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$h2_r.','.$h2_g.','.$h2_b.','.$h2_style.','.$h2_size;
                $sql=str_replace('{DEFAULT_ID}','PDF_H2',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$h3_r.','.$h3_g.','.$h3_b.','.$h3_style.','.$h3_size;
                $sql=str_replace('{DEFAULT_ID}','PDF_H3',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$text_r.','.$text_g.','.$text_b.','.$pdf_text_style.','.$pdf_text_size;
                $sql=str_replace('{DEFAULT_ID}','PDF_TEXT',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$link_r.','.$link_g.','.$link_b.','.$pdf_link_style.','.$pdf_link_size;
                $sql=str_replace('{DEFAULT_ID}','PDF_LINK',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
                
                $value_str=$table_hr_r.','.$table_hr_g.','.$table_hr_b.','.$table_hr_font_style.','.$table_hr_font_size.','.$table_hr_bg_r.','.$table_hr_bg_g.','.$table_hr_bg_b;
                $sql=str_replace('{DEFAULT_ID}','PDF_TABLE',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
               
                $value_str=$foot_r.','.$foot_g.','.$foot_b.','.$foot_style.','.$foot_size.','.
                           $foot_text.','.$foot_text_top.','.$foot_text_left.','.$foot_text_align;
                $sql=str_replace('{DEFAULT_ID}','PDF_FOOT',$sql_set);
                $sql=str_replace('{DEFAULT_VALUE}',$this->db->escapeSql($value_str),$sql);
                $this->db->executeSql($sql,$error_str);
             
                if($audit_changes)
                {
                    $audit_str='SYSTEM defaults... report settings';
                    $sql='INSERT INTO audit_trail (user_id,date,action,description) '.
                         'VALUES ("'.$this->db->escapeSql($_SESSION['admin_id']).'",NOW(),"PDF_SETUP","'.$this->db->escapeSql($audit_str).'") ';
                    $this->db->executeSql($sql,$error_str);
                }
            }
           
            if(count($error)==0) $message[]='Successfully updated PDF layout as displayed!';
          
        }
        //************************
        $html='';
        $html.= '
        <table width="95%">
          <tr>
            <td align="left"><b>View/Update PDF defaults listed below:</b></td>
          </tr>
          <tr><td>&nbsp;</td></tr>';
          $html.= Form::viewMessages($error,$message);
          
        $html.= '
          <form action="?mode=update" method="post" enctype="multipart/form-data">
          <tr>
            <td>';
              $html.= '<input type="submit" class="btn btn-primary" value="Update PDF layout">';
              $html.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
              $html.= '<a href="?mode=delete" onclick="javascript:return confirm(\'Are you sure you want to RESET layout?\')">reset layout</a>';
        $html.= '
            </td>
          </tr>
          <tr>
            <td> 
              <table class="table table-striped table-bordered table-hover table-condensed">
                <tr class="thead">
                  <th>Name</th><th>Settings</th>';
                  if($layout['show_desc']) $html.= '<th>Description</th>';
        $html.= '                  
                </tr>
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Background image/logo</td>       
                  <td valign="top">
                    <input type="file" name="pdf_image" size="30"><br/>';
                    if($pdf_image===$default_image_url) {
                      $image=$default_image_url;
                    } else {
                      //$image_src=$upload_url.$pdf_image;
                      //this is required as upload directory is not directly accesible
                      $error='';
                      $path=$upload_dir.$pdf_image;
                      $image=Image::getImage('SRC',$path,$error);
                    }    
                    $html.= '<img src="'.$image.'" height="40" align="left">';
                                if($image_hide==='YES') $checked='CHECKED'; else $checked='';
                    $html.= 'Hide image <input type="checkbox" name="image_hide" value="YES" '.$checked.' >';
        $html.= '
                  </td>';
                  if($layout['show_desc']) { 
                  $html.= '  
                  <td valign="top">
                    Image can range in size from a small logo to a full page background image.
                    Note that image will be positioned and scaled according to settings below.
                    <br/><b>NB:</b>Leave blank if you are happy with existing image.  
                  </td>';
                  } 
        $html.= '
                </tr>
                
                <tr class="trow">
                  <td width="150" valign="top" align="right">Image positioning</td>       
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Top</td><td><input type="text" name="image_top" size="3" maxlength="5" value="'.$image_top.'"></td>';
                    $html.= '<td>Left</td><td><input type="text" name="image_left" size="3" maxlength="5" value="'.$image_left.'"></td>';
                    $html.= '<td>Width</td><td><input type="text" name="image_width" size="3" maxlength="5" value="'.$image_width.'"></td>';
                    $html.= '<td>Height</td><td><input type="text" name="image_height" size="3" maxlength="5" value="'.$image_height.'"></td>';
                    $html.= '</tr></table>';
        $html.= '            
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '
                  <td>
                    Specify <b>mm</b> from top left corner of page where you wish to start drawing image, and width and height
                    of image in <b>mm</b>. The image will be rescaled to these dimensions regardless of original size.
                  </td>';
                  }
        $html.= ' 
                </tr>
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Page margins</td>       
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Top</td><td><input type="text" name="margin_top" size="3" maxlength="5" value="'.$margin_top.'"></td>';
                    $html.= '<td>Left</td><td><input type="text" name="margin_left" size="3" maxlength="5" value="'.$margin_left.'"></td>';
                    $html.= '<td>Right</td><td><input type="text" name="margin_right" size="3" maxlength="5" value="'.$margin_right.'"></td>';
                    $html.= '<td>Bottom</td><td><input type="text" name="margin_bottom" size="3" maxlength="5" value="'.$margin_bottom.'"></td>';
                    $html.= '</tr></table>';
        $html.= '                    
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td>
                    Specify page margins in <b>mm</b> from edges of page.
                    Note that H1 page header position and text must fall within top margin! 
                  </td>';
                  }
        $html.= '
                </tr>
                
                <tr class="trow">
                  <td width="150" valign="top" align="right">Font family</td>
                  <td valign="top">';
                    $html.= Form::arrayList($font_face,'pdf_font',$pdf_font,true);
        $html.= '            
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td>
                    Select Font that will be used throughout report.
                    If you would like to add a font to available options then please contact support.
                  </td>';
                  } 
        $html.= '
                </tr>
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Date format</td>
                  <td valign="top">';
                    $html.= Form::arrayList($date_array,'date_format',$date_format,false);
        $html.= '            
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '
                  <td>
                    Select date format that will be used throughout report.
                    If you would like to add a date format to available options then please contact support.
                  </td>';
                  }
        $html.= '
                </tr>
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Page header</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left">';
                    $html.= '<tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="h1_color" size="5" maxlength="7" value="'.$h1_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'h1_style',$h1_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'h1_size',$h1_size,false);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '<tr><td>Text</td><td colspan="5"><input type="text" name="h1_text" size="40" maxlength="100" value="'.$h1_text.'"></td></tr>';
                    $html.= '<tr>';
                    $html.= '<td>Text top</td><td><input type="text" name="h1_text_top" size="3" maxlength="5" value="'.$h1_text_top.'"></td>';
                    $html.= '<td>Text left</td><td><input type="text" name="h1_text_left" size="3" maxlength="5" value="'.$h1_text_left.'"></td>';
                    $html.= '<td>Align</td><td>'.Form::arrayList($text_align,'h1_text_align',$h1_text_align,true);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '<tr>';
                    $html.= '<td>Date colour</td><td><input type="text" class="jscolor" name="h1_date_color" size="5" maxlength="7" value="'.$h1_date_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'h1_date_style',$h1_date_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'h1_date_size',$h1_date_size,false);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '<tr>';
                    $html.= '<td>Date top</td><td><input type="text" name="h1_date_top" size="3" maxlength="5" value="'.$h1_date_top.'"></td>';
                    $html.= '<td>Date offset</td><td><input type="text" name="h1_date_left" size="3" maxlength="5" value="'.$h1_date_left.'"></td>';
                    $html.= '<td colspan="3">';
                    if($h1_date==='YES') $checked='CHECKED'; else $checked='';
                    $html.= 'Display date<input type="checkbox" name="h1_date" value="YES" '.$checked.' >';
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '</table>';
        $html.= '                    
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '
                  <td valign="top">
                    Select font colour, style and size to use for all page headers. 
                    Note that color is specified using standard hexadecimal color codes in #RRGGBB format.<br/>
                    Text(leave blank to omit) will appear at indicated position, regardless of top margin setting.<br/>
                    <b>NB:</b> All top and left positions in <b>mm</b> from edges of page.
                    Date offset is from right edge of page to accomodate landscape and portrait layouts.
                  </td>';
                  }
        $html.= '
                </tr>
                
                <tr class="trow">
                  <td width="150" valign="top" align="right">Section header</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="h2_color" size="5" maxlength="7" value="'.$h2_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'h2_style',$h2_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'h2_size',$h2_size,false);
                    $html.= '</td>';
                    $html.= '</tr></table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) { 
                  $html.= '  
                  <td valign="top">
                    Select font colour, style and size to use for content section headers. 
                  </td>';
                  } 
        $html.= '
                </tr>
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Sub-section header</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="h3_color" size="5" maxlength="7" value="'.$h3_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'h3_style',$h3_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'h3_size',$h3_size,false);
                    $html.= '</td>';
                    $html.= '</tr></table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td valign="top">
                    Select font colour, style and size to use for content sub-section headers. 
                  </td>';
                  }
        $html.= '
                </tr>
              
                <tr class="trow">
                  <td width="150" valign="top" align="right">Standard text</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="pdf_text_color" size="5" maxlength="7" value="'.$pdf_text_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'pdf_text_style',$pdf_text_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'pdf_text_size',$pdf_text_size,false);
                    $html.= '</td>';
                    $html.= '</tr></table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) { 
                  $html.= '  
                  <td valign="top">
                    Select font colour, style and size to use for all other text in reports. 
                  </td>';
                  }
        $html.= '
                </tr>     
                
                <tr class="trow_alt">
                  <td width="150" valign="top" align="right">Linked text</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left"><tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="pdf_link_color" size="5" maxlength="7" value="'.$pdf_link_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'pdf_link_style',$pdf_link_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'pdf_link_size',$pdf_link_size,false);
                    $html.= '</td>';
                    $html.= '</tr></table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td valign="top">
                    Select font colour, style and size to use for email and www links in reports. 
                  </td>';
                  }
        $html.= '
                </tr>
                
                <tr class="trow">
                  <td width="150" valign="top" align="right">Table header</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left">';
                    $html.= '<tr>';
                    $html.= '<td>Font colour</td><td><input type="text" class="jscolor" name="table_hr_font_color" size="5" maxlength="7" value="'.$table_hr_font_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'table_hr_font_style',$table_hr_font_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'table_hr_font_size',$table_hr_font_size,false);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '<tr>';
                    $html.= '<td>Background</td><td><input type="text" class="jscolor" name="table_hr_bg_color" size="5" maxlength="7" value="'.$table_hr_bg_color.'" ></td>';
                    $html.= '<td colspan="4">&nbsp;</td>';
                    $html.= '</tr>';
                    $html.= '</table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td valign="top">
                    Select font colour, style, size and background colour to use for table headers.<br/> 
                  </td>';
                  }
        $html.= '
                </tr> 
               
                      <tr class="trow">
                  <td width="150" valign="top" align="right">Report footer text</td>
                  <td valign="top">';
                    $html.= '<table class="text3" align="left">';
                    $html.= '<tr>';
                    $html.= '<td>Colour</td><td><input type="text" class="jscolor" name="foot_color" size="5" maxlength="7" value="'.$foot_color.'" ></td>';
                    $html.= '<td>Style</td><td>'.Form::arrayList($font_style,'foot_style',$foot_style,true);
                    $html.= '</td>';
                    $html.= '<td>Size</td><td>'.Form::arrayList($font_size,'foot_size',$foot_size,false);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '<tr><td>Text</td><td colspan="5">'.
                         '<textarea class="userinput" name="foot_text" cols="40" rows="3">'.$foot_text.'</textarea>'.
                         '</td></tr>';
                    $html.= '<tr>';
                    $html.= '<td>Text top</td><td><input type="text" name="foot_text_top" size="3" maxlength="5" value="'.$foot_text_top.'"></td>';
                    $html.= '<td>Text left</td><td><input type="text" name="foot_text_left" size="3" maxlength="5" value="'.$foot_text_left.'"></td>';
                    $html.= '<td>Align</td><td>'.Form::arrayList($text_align,'foot_text_align',$foot_text_align,true);
                    $html.= '</td>';
                    $html.= '</tr>';
                    $html.= '</table>';
        $html.= '
                  </td>';
                  if($layout['show_desc']) {
                  $html.= '  
                  <td valign="top">
                    Specify text that will appear at the bottom of every page.<br/>
                    NB1:Text top is <b>mm</b> from bottom margin setting!<br/>
                                NB2: The "bottom" page margin setting must be large enough to allow footer text and page numbering.
                  </td>';
                  }
        $html.= '
                </tr>
              </table>  
            </td>
          </tr>
          </form>
          
          <tr><td>&nbsp;</td></tr>
        </table>';


        return $html;

        //************************
    }    

}
?>

