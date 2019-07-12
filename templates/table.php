<?php 
namespace App;

echo $this->fetch('include/header.php',[]); 
echo $this->fetch('include/menu.php',['menu'=>$menu]); 
?>
    <body>
        <div id="main_div">
            <div class="container">
                <h1>
                <?php
                 echo $title;

                 if(isset($title_xtra)) echo $title_xtra; 
                ?>
                </h1>
                <div>
                <?php
                if(isset($sub_menu)) echo $sub_menu;

                echo $html; 
                ?>
                </div>
            </div>    
        </div>
    </body>
</html>
<?php
if(isset($javascript)) echo $javascript;

?>