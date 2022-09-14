<?php

include "header.php";
include "checksession.php";
include "menu.php";
// loginStatus(); //show the current login status


//simple logout
if (isset($_POST['logout'])) logout();

if (isset($_POST['login']) and !empty($_POST['login']) and ($_POST['login'] == 'Login')) {
  include "config.php"; //load in any variables
  $DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE) or die();

  //validate incoming data
  //email
  $error = 0; //clear our error flag
  $msg = 'Error: ';
  if (isset($_POST['email']) and !empty($_POST['email']) and is_string($_POST['email'])) {
    $email = htmlspecialchars(stripslashes(trim($_POST['email'])));
    //  $email = (strlen($em)>50)?substr($em,1,50):$un; //check length and clip if too big 
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid email '; //append error message
    $email = '';
  }

  //password   
  $password = trim($_POST['password']);
  //This should be done with prepared statements!!
  if ($error == 0) {
    $query = "SELECT customerID,firstname,lastname,password FROM customer WHERE email = '$email'";
    $result = mysqli_query($DBC, $query);
    if (mysqli_num_rows($result) == 1) { //found the user
      $row = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      mysqli_close($DBC); //close the connection once done
      if ($password === $row['password']) //using plaintext for demonstration only!            
        login($row['customerID'], $row['firstname'], $row['lastname'], $email);
    }
    echo "<h2>Login fail</h2>" . PHP_EOL;
  } else {
    echo "<h2>$msg</h2>" . PHP_EOL;
  }
} // end of post
?>
<div id="body">
  <div class="header">
    <div>
      <h1>Login</h1>
    </div>
  </div>
  <h2><a href="/pizza/">[Return to main page]</a></h2>
  <form method="POST" action="login.php">
    <p>
      <label for="email">Email: </label>
      <input type="text" id="email" name="email" maxlength="50">
    </p>
    <p>
      <label for="password">Password: </label>
      <input type="password" id="password" name="password" maxlength="32">
    </p>
    <input type="submit" name="login" value="Login">
    <input type="submit" name="logout" value="Logout">
  </form>

</div>
<?php
include "footer.php"
?>