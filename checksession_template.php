<?php
session_start();

//overrides for development purposes only - comment this out when testing the login
$_SESSION['loggedin'] = 0;     
$_SESSION['userid'] = 1; //this is the ID for the admin user  
$_SESSION['username'] = 'Test';
//end of overrides

function isAdmin() {
 if (($_SESSION['loggedin'] == 1) and ($_SESSION['userid'] == 1)) 
     return TRUE;
 else 
     return FALSE;
}

//function to check if the user is logged else send to the login page 
function checkUser() {
return true;
    $_SESSION['URI'] = '';    
    if ($_SESSION['loggedin'] == 1)
       return TRUE;
    else {
       $_SESSION['URI'] = 'http://localhost'.$_SERVER['REQUEST_URI']; //save current url for redirect     
       header('Location: http://localhost/pizza/login.php', true, 303);       
    }       
}

//just to show we are are logged in
function loginStatus() {
    $un = $_SESSION['username'];
    if ($_SESSION['loggedin'] == 1)     
        echo "<h1>Logged in as $un</h1>";
    else
        if ($un != '') {
            echo "<h1>Logged out</h1>";            
            $_SESSION['username'] = '';
        }    
}

//log a user in
function login($id,$username) {
   //simple redirect if a user tries to access a page they have not logged in to
   if ($_SESSION['loggedin'] == 0 and !empty($_SESSION['URI']))        
        $uri = $_SESSION['URI'];          
   else { 
     $_SESSION['URI'] =  'http://localhost/pizza/listcustomers.php';         
     $uri = $_SESSION['URI'];           
   }  

   $_SESSION['loggedin'] = 1;        
   $_SESSION['userid'] = $id;   
   $_SESSION['username'] = $username; 
   $_SESSION['URI'] = ''; 
   header('Location: '.$uri, true, 303);        
}

//simple logout function
function logout(){
  $_SESSION['loggedin'] = 0;
  $_SESSION['userid'] = -1;        
  $_SESSION['username'] = '';
  $_SESSION['URI'] = '';
  header('Location: http://localhost/pizza/login.php', true, 303);    
}
?>