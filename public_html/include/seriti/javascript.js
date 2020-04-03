function is_max_length(obj,max_length){
  if (obj.value.length>max_length) {
    obj.value=obj.value.substring(0,max_length);
    alert('You have exceeded the maximum number of characters['+max_length+']');
  }  
}

function cursor_wait() {
  document.body.style.cursor='wait';
}

function cursor_clear() {
  document.body.style.cursor='default';
}

function link_download(id) {
  cursor_wait();
  var e = document.getElementById(id);
  e.style.cursor='wait';
  window.onblur = function() {
    //alert('window blur');
    cursor_clear();
    window.onblur='';
    e.style.cursor='';
  }

}  

function open_popup(url, width, height) {
  popup=window.open(url, 'Dialog_box', 'width='+width+',height='+height+',resizable,scrollbars')
  popup.focus();
}

function open_popup2(url, width, height) {
  popup=window.open(url, 'Dialog_box2', 'width='+width+',height='+height+',resizable,scrollbars')
  popup.focus();
}

function open_popup_full(name,url) {
  popup=window.open(url,name,'fullscreen');
  popup.focus();
}


function open_popup_modal(url, width, height) {
  if (window.showModalDialog) {
    //alert('hello');
    popup=window.showModalDialog(url,'Dialog_box','dialogWidth:'+width+'px;dialogHeight:'+height+'px');
  } else {
    popup=window.open(url, 'Dialog_box', 'width='+width+',height='+height+',resizable=yes,scrollbars=yes,modal=yes');
  }
  
  popup.focus();
}

function fullscreen_request(element) {
  if(!element) element=document.body;
  // Supports most browsers and their versions.
  var requestMethod = element.requestFullscreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullScreen;

  if (requestMethod) { // Native full screen.
    requestMethod.call(element);
  } else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
    var wscript = new ActiveXObject("WScript.Shell");
    if (wscript !== null) {
        wscript.SendKeys("{F11}");
    }
  }
}

function fullscreen_cancel() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if (document.webkitCancelFullScreen) {
    document.webkitCancelFullScreen();
  }  
}  

function fullscreen_check() {
  var full_screen=false;
  
  if(document.fullscreenElement || document.mozFullScreen || document.webkitIsFullScreen) {
    full_screen=true;
  }  
  return full_screen;
}  

function fullscreen_toggle() {
  if(fullscreen_check()) {
    fullscreen_cancel(); 
  } else {
    fullscreen_request();
  }    
}


function toggle_display(id) {
  var display_div = document.getElementById(id);
      
  if(display_div.style.display=='none') {
    display_div.style.display='block';
  } else {
    display_div.style.display='none';
  }
}

function toggle_display_inline(id) {
  var display_div = document.getElementById(id);
      
  if(display_div.style.display=='none') {
    display_div.style.display='inline';
  } else {
    display_div.style.display='none';
  }
}

function toggle_display_scroll(id) {
  var display_div = document.getElementById(id);
      
  if(display_div.style.display=='none') {
    display_div.style.display='block';
    display_div.scrollIntoView(false);
  } else {
    display_div.style.display='none';
  }
}

function toggle_display_adv(id,head_id,head_open,head_close) {
  var display_div = document.getElementById(id);
  var header_div = document.getElementById(head_id);
      
  if (display_div.style.display == 'none') {
    header_div.innerHTML=head_close;
    display_div.style.display = 'block';
  } else {
    header_div.innerHTML=head_open;
    display_div.style.display = 'none';
  }
}

function checkbox_password_mask(pwd_id,self) {
  var input_box = document.getElementById(pwd_id);
      
  if(self.checked==true) {
    input_box.type='password';
  } else {
    input_box.type='text';
  }
}  

function generate_password(len){
  len = parseInt(len);
  if(!len)
    len = 6;
  var password = "";
  var chars    = "23456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
  var charsN   = chars.length;
  var nextChar;
 
  for(i=0; i<len; i++){
    nextChar = chars.charAt(Math.floor(Math.random()*charsN));
    password += nextChar;
  }
  return password;
}

function update_calling_page(url) {
  if(opener!==null) { 
    if(url=='ORIGINAL') {
      opener.location.reload();
    } else {
      if(url!='') opener.location.href = url;
    }
  }
  self.close(); 
}

function update_calling_page_alt(url,alt_url) {
  if(opener!==null) { 
    if(url=='ORIGINAL') {
      opener.location.reload();
    } else {
      if(url!='') opener.location.href = url;
    }
    self.close();
  } else {
    if(alt_url!='') window.location=alt_url;
  }  
}

function mysql_date_convert(str) {
  var year=str.substr(0,4);
  var month=str.substr(5,2);
  var day=str.substr(8,2);
  
  date = new Date(year,month-1,day);
  return date;
}

function js_date_str(date,format) {
  var date_str='';
  var year=date.getFullYear();
  var month=date.getMonth()+1;
  var month_str;
  var day=date.getDate();
  var day_str;
    
  if(format=='MYSQL') {
    month_str=month.toString();
    if(month_str.length==1) month_str='0'+month_str;
    day_str=day.toString();
    if(day_str.length==1) day_str='0'+day_str;
    
    date_str=year.toString()+'-'+month_str+'-'+day_str;
  } 
   
  return date_str;
}

function js_date_inc(date,inc,type) {
  if(type=='DAYS') date.setDate(date.getDate() + inc); 
  if(type=='MONTHS') date.setMonth(date.getMonth() + inc); 
  if(type=='YEARS') date.setFullYear(date.getFullYear() + inc); 
   
  return date;
}


function check_valid_email(email) {
  var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(String(email).toLowerCase());
}

function is_numeric(str) {
  var ValidChars = "0123456789.";
  for(i=0; i<str.length; i++) {
    if(ValidChars.indexOf(str.charAt(i))==-1) return false;
  }
  return true;
}

function date_diff(date_from,date_to,diff_output) {
  var calc_diff=0;
  var interval=0;
  
  if(date_from<date_to) { 
    if(diff_output=='DAYS') {
      interval=1000*60*60*24; //one day in milliseconds
      calc_diff=Math.ceil((date_to.getTime()-date_from.getTime())/interval);
    } 
  }
  
  return calc_diff;
}

function change_image(image_url,image_caption,image_id) {
  var image_obj = document.getElementById(image_id);
  image_obj.src=image_url;
  image_obj.alt=image_caption;
  image_obj.title=image_caption;
}

function set_cookie(c_name,value,exdays) {
  var exdate=new Date();
  exdate.setDate(exdate.getDate() + exdays);
  var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
  document.cookie=c_name + "=" + c_value;
}

function get_cookie(c_name) {
  var i,x,y,ARRcookies=document.cookie.split(";");
  for (i=0;i<ARRcookies.length;i++) {
    x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
    y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
    x=x.replace(/^\s+|\s+$/g,"");
    if (x==c_name) {
      return unescape(y);
    }
  }
}

function check_cookies_enabled() {
  var test_val='cookiesOK';
  set_cookie('test',test_val,0);  
  if(get_cookie('test')!=test_val) {
    return true;
  } else {
    return false;
  }  
}  

// This function just adds commas. Could do all sorts of other cleanup...
function mask_num(v){
  var prefix='';
  var decimal_pos=v.lastIndexOf('.');
  var sa=v.split('.');
  var s=sa[0].replace(/,/g,'');
  var s1='';
  if(s.charAt(0)=='-') {
    prefix='-';
    s=s.slice(1);
  }  
  
  var l=s.length-1;
  for(var j=0;j<=l;j++){
    s1=s.charAt(l-j)+s1;
    if(j<l && j%3==2) s1=','+s1;
  }
  //if(sa[1]) s1+='.'+sa[1];
  if(sa[1] || decimal_pos>0 ) s1+='.'+sa[1];
  if(prefix!='') s1=prefix+s1;
  
  return s1;
}

//formats numbers as number.toMoney(2,'.',',');
Number.prototype.toMoney = function(decimals, decimal_sep, thousands_sep)
{ 
   var n = this,
   c = isNaN(decimals) ? 2 : Math.abs(decimals), //if decimal is zero we must take it, it means user does not want to show any decimal
   d = decimal_sep || ',', //if no decimal separetor is passed we use the comma as default decimal separator (we MUST use a decimal separator)
   t = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep, //if you don't want ot use a thousands separator you can pass empty string as thousands_sep value
   sign = (n < 0) ? '-' : '',
   //extracting the absolute value of the integer part of the number and converting to string
   i = parseInt(n = Math.abs(n).toFixed(c)) + '', 

   j = ((j = i.length) > 3) ? j % 3 : 0; 
   return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : ''); 
}

if(!String.prototype.trim) {  
  String.prototype.trim = function () {  
    return this.replace(/^\s+|\s+$/g,'');  
  };  
}


/*
base ajax function.
url : is php/html file that returns response text
param : is in 'xxx=123&yyy=456' url format
handler : is the function name to call with returned response text
handle_id : is div/span container id="handle_id" which will receive response text (but can also just be a marker)
*/
function xhr(url,param,handler,handle_id) {
  var ro=window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
  ro.open('POST',url,true);
  ro.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  ro.onreadystatechange=function(){if(ro.readyState==4) handler(ro.responseText,handle_id)};
  ro.send(param);
}
/* sends data to server and does not handle response */
function xhr_poke(url,param) {
  var ro=window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
  ro.open('POST',url,true);
  ro.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  ro.send(param);
}

function do_task(url,param,result_id) {
  xhr(url,param,show_task_result,result_id);
}

function show_task_result(str,result_id) {
  var container = document.getElementById(result_id);
  container.innerHTML = str;
}

function form_parameters(form_id,return_type) {
  if(typeof(return_type)==='undefined') return_type='url';
  var i = 0
  
  if(return_type=='url') {
    var param = '';
  } else {
    var param = new Object();
  }    
    
  var elem = document.getElementById(form_id).elements;
  for(i=0;i<elem.length;i++) {
    if(return_type=='url') {
      param+=elem[i].name+'='+encodeURIComponent(elem[i].value);
      if(i<(elem.length-1)) param+='&';
    } else {
      param[elem[i].name]=elem[i].value;
    }    
  }  
  
  return param;
}

function clear_div(div_id) {
  var div = document.getElementById(div_id);    
  div.innerHTML='';
}

function copy_to_clipboard(div_id) {
  var copyDiv = document.getElementById(div_id);
  var tempTextArea = document.createElement("textarea");

  tempTextArea.value = copyDiv.textContent;
  document.body.appendChild(tempTextArea);
  tempTextArea.select();
  document.execCommand("Copy");
  tempTextArea.remove();
  alert('Successfully copied text to clipboard. Right click and select "paste", or keystrokes [Ctrl][V] to paste into desired location');
}