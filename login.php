<?php

session_start();
include_once("db_connect.php");

//Check for the connection of the server
if (isset($_POST['login_button'])) { //if sink_login has been pressed
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT username, password FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
    $row = mysqli_fetch_assoc($result);

    if($row['password'] == $password){
        echo "ok";
        $_SESSION['user_session'] = $row['username'];
    } else {
        echo "Username or Password is invalid";
    }
}

?>