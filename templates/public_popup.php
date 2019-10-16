<?php 
namespace App;

$menu_spacer = false;
echo $this->fetch('include/header_public.php',['spacer'=>$menu_spacer]); 

if(isset($title)) echo '<h1>'.$title.'</h1>';

echo $html; 

if(isset($javascript)) echo $javascript;
?>