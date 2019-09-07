<?php 
namespace App;

echo $this->fetch('include/header_public.php',[]); 
echo $this->fetch('include/menu.php',['menu'=>$menu]); 

if(isset($sub_menu)) echo $sub_menu;

echo $html; 

if(isset($javascript)) echo $javascript;
echo $this->fetch('include/footer_public.php',[]); 
?>