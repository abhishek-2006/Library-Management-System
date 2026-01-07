<?php
include('includes/config.php');
$_SESSION = array();

// Check if the session uses cookies and delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

unset($_SESSION['login']);

session_destroy();
header("location:../index.php"); 
exit(); 
?>