<?php 
namespace App;

echo $this->fetch('include/header.php',[]); 
?>
    <body>
        <div id="main_div">
            <div class="container">
                <h1><?php echo $title; ?></h1>
                <div>
                <?php
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