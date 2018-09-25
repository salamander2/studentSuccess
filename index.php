<?php
/*******************************************************************************
  Name: index.php
  Called from: --
  Purpose: this is the login page
  Tables used: sssDB, schoolDB, no tables
  Transfers control to: home.php on successful login
 ******************************************************************************/

session_start(); // Start the session
require_once('../../DB-admin/php_includes/sssDB.inc.php');

$error_message="";

//if the login button has been pressed:
if(isset($_POST['login'])) {  

	$username = clean_input($_POST['username']);
	$password = $_POST['password'];
	//1. make sure that username and password are not empty
	if ($username == "" || $username == "username") {
		$error_message = "You must enter a username";
	}

	if ($password == "" || $password == "password") {
		$error_message = "You must enter a password";
	}

	//2. connect to schoolDB
	if (empty($error_message)) {
		$servername = getenv('IP');
		$schoolDB = mysqli_connect($servername, $userRO, $passRO, "schoolDB");
		if ($schoolDB->connect_error) {
			$error_message = "FATAL ERROR: cannot make initial connection to 'schoolDB'. (Contact DB admin)";
		}	
		if (mysqli_connect_errno($schoolDB)) {
			echo "Failed to connect to MySQL database $database : " . mysqli_connect_error();
			die("Program terminated");
		}
	}

/*
		echo $schoolDB->info;
		echo var_dump($schoolDB);
		echo "------";
	//	echo $error_message;
*/

	//3. get user info from schoolDB/users table

	if (empty($error_message)) {
		$sql = "SELECT full_name, alpha, password, isAdmin, isTeam FROM users WHERE login_name = ?";
		if ($stmt = $schoolDB->prepare($sql)) {
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$stmt->bind_result($fullname,$alpha,$hashPassword,$isAdmin,$isTeam);
			$stmt->fetch();
			$stmt->close();
		} else {
			$error_message  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
			$error_message .= 'SQL: ' . $sql;
		}
	}


	//4. verify the password that was entered (we already have the username)
	if (empty($error_message)) {
		if (!password_verify($password, $hashPassword)) {
			$error_message = "Invalid password. $username -- $password";
		}
	}


	//5. determine access priveledges and set session variables
	if (empty($error_message)) {
		if (1===isAdmin) {
			$sql_user = $username;
			$sql_pass = $password;
		} else if (1===isTeam) {
			$sql_user = $userSTD;
			$sql_pass = $passSTD;
		} else {
			$sql_user = $userRO;
			$sql_pass = $passRO;
		}

		//store session variables
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
#			$_SESSION["alpha"] = $alpha;	//done in home.php

		$_SESSION["sql_user"] = $sql_user;
		$_SESSION["sql_pass"] = $sql_pass;

		$_SESSION["isAdmin"] = $isAdmin;
#			$_SESSION["isTeam"] = $isTeam;	//done in home.php

		header("location: home.php");
	}

	if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";
}

//Do this as a function so that we can use return. No, then we have to pass too many variables
//function authenticate($username, $password, $userRO, $passRO) {
//}



function connectToDB($database, $username, $password) {
	$servername = getenv('IP');
	$db = mysqli_connect($servername, $username, $password, $database);
	if (mysqli_connect_errno($db)) {
		echo "Failed to connect to MySQL database $database : " . mysqli_connect_error();
		die("Program terminated");
	}
	return $db;
}


function clean_input($string) {
#the mysql_real_escape_string for some reason gets rid of the whole string!
	//$string = mysqli_real_escape_string($mysqli, trim(strip_tags(addslashes($string))));
	$string = trim(strip_tags(addslashes($string)));
	return $string;
}
?>

<!DOCTYPE html>
<!-- The login page-->
<html>
<head>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css" type="text/css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
<title>Student Success Database: login</title>
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="http://bealinfo.tvdsb.ca">  Go Back</a>
<h1>Student Success Database</h1>
<h2>Enter your credentials</h2>

<?php print $error_message ?>

<table zidth="75%" style="margin:20px;">
<tr>
<td width="30%">
<p class="prevComment">
This database shows you students' timetables and photos.<br><br>
If you're part of the "students at-risk team" you can also read comments and enter comments
about students to facilitate communication and follow-up regarding at-risk students
</p>
</td><td>
<form class="pure-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<!-- <form class="pure-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"> -->
<fieldset>
<div class="pure-control-group">
Username: <input name="username" autofocus="" type="text" placeholder="Username" >
</div>

<div class="pure-control-group">
Password:&nbsp;&nbsp;<input name="password" type="password" placeholder="password">
</div>
<button type="submit" name="login" class="pure-button">Login</button>
</fieldset>
</form>
</td>
<td width=30% valign="top">
<p>&nbsp;</p>
<p class="box">
eg. "jsmith"<br> or standard 'teacher' login</p>
</td></tr>
</table>

</div>
</body>
</html>
