<?php 
namespace App;

$menu_spacer = true;
echo $this->fetch('include/header_public.php',['spacer'=>$menu_spacer]); 
echo $this->fetch('include/menu.php',['menu'=>$menu]); 

if(isset($title)) echo '<h1>'.$title.'</h1>';

if(isset($sub_menu)) echo $sub_menu;

echo $html; 

if(isset($javascript)) echo $javascript;
echo $this->fetch('include/footer_public.php',[]); 
?>