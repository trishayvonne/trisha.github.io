<?php
session_start();
if(!isset($_SESSION['user_session'])){
    header("Location: index.php");
}
include_once("db_connect.php");
$sql = "SELECT username, password FROM admin WHERE username='".$_SESSION['user_session']."'";
$result = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$row = mysqli_fetch_assoc($result);
?>

<head>
    <title>Διαχείρηση</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_managedb.css">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab&display=swap" rel="stylesheet">
    <script type="text/javascript" src="filesubmit.js"></script>
</head>

<body>

<nav>
    <h1 class="logo"><span>P</span>arking<span>E</span>asy</h1>
    <ul class="nav-links">
        <li><a class="current" href="managedb.php"><i class="fa fa-wrench"></i>  Management</a></li>
        <li><a class="other" href="adminmap.php"><i class="fa fa-map"></i>  Map</a></li>
        <li><a class="other" href="logout.php"><i class="fa fa-user"></i>  Exit</a></li>
    </ul>
    <div class="bar">
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="line3"></div>
    </div>
</nav>
<div id="loader"></div>
<div class="container">
    <div class="box" >
        <div class="icon"><i class="fa fa-upload"></i></div>
        <div class="information1">
            <h3>Select the KML file</h3>
        </div>
        <form id="file-form" method="post" enctype="multipart/form-data">
            <button id="bt-file" type="button" name="button" onclick ="javascript:document.getElementById('file').click();"> Upload file </button>
            <input id="file" type="file" name="file" onchange="ChangeText();" required> <!-- These are hidden not displayed on the page -->
            <button id="bt-hid" type="submit" name="button"></button> <!--These are hidden not displayed on the page -->
        </form>
        <div class="information3"> <h3 id="result"> </h3> </div>
    </div>
    <div class="box">
        <div class="icon"><i class="fa fa-trash"></i></div>
        <div class="information2">
            <h3>Μπορείτε να διαγράbake the data from the base</h3>
        </div>
        <button id="bt" onclick="deleteDB()" name="button">Clear data</button>
    </div>
</div>

</body>