<?php
include('header.php');
include_once("login.php");
?>
<script type="text/javascript" src="script/validation.min.js"></script>
<script type="text/javascript" src="script/login.js"></script>

	<head>
		<meta charset="utf-8">
		<title>Admin login</title>
		<link href="https://fonts.googleapis.com/css?family=Roboto+Slab&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="style_login.css">
	</head>
	<body>
		<header>
			<nav>
				<div class="logo">
					<h1><span>P</span>arking<span>Management</span>System</h1>
				</div>
			</nav>
		</header>
		<div class="loginBox">
			<div id="error"></div>
			<img src="images/user-male-icon.png" class="user">
			<h2>ADMIN</h2>
			
			<form method = "post" id="login-form">
				
					<p>Username</p>
					<input type="username"  name="username" placeholder="Enter username" id="username" />
					<span id="check-e"></span>
			
					<p>Password</p>
					<input type="password"  name="password" placeholder="Enter password" id="password" />
					
				<button type="submit" class="btn btn-default" name="login_button" id="login_button">
                    <span class="glyphicon glyphicon-log-in"></span> &nbsp; Sign In
                </button>
			</form>
		</div>
	</body>