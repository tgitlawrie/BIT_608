<?php
session_start();

//overrides for development purposes only - comment this out when testing the login
// $_SESSION['loggedin'] = 0;     
// $_SESSION['userid'] = 1; //this is the ID for the admin user  
// $_SESSION['email'] = 'Test';
//end of overrides

function isAdmin()
{
    if (($_SESSION['loggedin'] == 1) and ($_SESSION['userid'] == 1))
        return TRUE;
    else
        return FALSE;
}

//function to check if the user is logged else send to the login page 
function checkUser()
{
    $_SESSION['URI'] = '';
    if ($_SESSION['loggedin'] == 1)
        return TRUE;
    else {
        $_SESSION['URI'] = 'http://localhost' . $_SERVER['REQUEST_URI']; //save current url for redirect     
        header('Location: http://localhost/pizza/login.php', true, 303);
    }
}

//just to show we are are logged in
function loginStatus()
{
    $firstName = $_SESSION['firstName'];
    $lastName = $_SESSION['lastName'];
    if ($_SESSION['loggedin'] == 1)
        echo "<h1>Logged in as $firstName  $lastName</h1>";
    else
        if ($firstName != '') {
        echo "<h1>Logged out</h1>";
        $_SESSION['email'] = '';
    }
}

//log a user in
function login($id, $firstName, $lastName, $email)
{
    //simple redirect if a user tries to access a page they have not logged in to
    if ($_SESSION['loggedin'] == 0 and !empty($_SESSION['URI']))
        $uri = $_SESSION['URI'];
    else {
        $_SESSION['URI'] =  'http://localhost/pizza/index.php';
        $uri = $_SESSION['URI'];
    }

    $_SESSION['loggedin'] = 1;
    $_SESSION['userid'] = $id;
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;
    $_SESSION['email'] = $email;
    $_SESSION['URI'] = '';
    header('Location: ' . $uri, true, 303);
}

//simple logout function
function logout()
{
    $_SESSION['loggedin'] = 0;
    $_SESSION['userid'] = -1;
    $_SESSION['firstName'] = '';
    $_SESSION['lastName'] = '';
    $_SESSION['email'] = '';
    $_SESSION['URI'] = '';
    header('Location: http://localhost/pizza/login.php', true, 303);
}

function isAuth($dbID, $sessionID)
{
    //if customer id from database doesnt match id from session or not admin send to login
    if ($dbID == $sessionID || isAdmin()) return true;
    else {
        $_SESSION['URI'] = 'http://localhost' . $_SERVER['REQUEST_URI']; //save current url for redirect  
        header('Location: http://localhost/pizza/login.php', true, 303);
    }
}