<?php 
namespace App;

echo $this->fetch('include/header.php',[]); 
echo $this->fetch('include/menu.php',['menu'=>$menu]); 
?>
    <body>
        <div id="main_div">
            <div class="container">
                <h1><?php echo $title; ?></h1>
                <div>
                <?php echo $html; ?>
                </div>
            </div>    
        </div>
    </body>
</html>

<script type="text/javascript">

$(document).ready(function() {
  //alert('wtf');
    if(form = document.getElementById('update_form')) {
        type_change();
    }
});

function type_change() {
    var form = document.getElementById('update_form');
    var menu_type = form.menu_type.value;
    var menu_link = form.menu_link.value;
    
    var tr_menu_link = document.getElementById('tr_menu_link');
    var tr_link_mode = document.getElementById('tr_link_mode');
    var tr_menu_access = document.getElementById('tr_menu_access');
  
    tr_menu_link.style.display = 'none';
    tr_link_mode.style.display = 'none';
    tr_menu_access.style.display = 'none';
  
    if(menu_type.substring(0,5) == 'LINK_') {
        tr_menu_link.style.display = '';
        tr_menu_access.style.display = '';
        tr_link_mode.style.display = '';
        
        var param = 'menu_type='+menu_type;
        xhr('ajax?mode=menu',param,show_menu_links,menu_link);
    }
  
    if(menu_type == 'TEXT') {
        tr_menu_access.style.display='';
    }  
} 

function show_menu_links(str,menu_link) {
    if(str === 'ERROR') {
        alert('Menu ajax error!');
    } else {  
        var links = $.parseJSON(str);
        var sel = '';
        //use jquery to reset cols select list
        $("#menu_link option").remove();
        $.each(links, function(i,item){
            // Create and append the new options into the select list
            if(i == menu_link) sel = 'SELECTED'; else sel = '';
            $("#menu_link").append("<option value="+i+" "+sel+">"+item+"</option>");
        });
    }    
}     
</script>
