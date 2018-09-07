<?php
/*******************************************************************************
   Name: index.php
   Called from: --
   Purpose: this is the login page
   Tables used: sssDB, schoolDB, no tables
   Transfers control to: home.php on successful login
******************************************************************************/

# TODO  Does this also have to connect to the schoolDB?

session_start(); // Start the session

$message="";

//if the login button has been pressed:
if(isset($_POST['login'])) {  
	//store session variables
	$_SESSION["username"] = protect($_POST['username']);
	$_SESSION["password"] = $_POST['password'];

	$servername = getenv('IP');
	//get session variables
	$username = $_SESSION["username"]; 
	$password = $_SESSION["password"];
	$database = "sssDB";

	// Connecting, selecting database
	$sssDB = @mysqli_connect($servername, $username, $password, $database);
	if (mysqli_connect_errno($sssDB)) {
		//$loginerror= mysqli_connect_errno($mysqli);
		$message = "<div class=\"error\"> Incorrect Username or Password for $database </div>";
	} else {
		header("Location: home.php");
		die();
	}

	// Connecting, selecting database
	$schoolDB = @mysqli_connect($servername, $username, $password, "schoolDB");
	if (mysqli_connect_errno($schoolDB)) {
		$message = "<div class=\"error\"> Incorrect Username or Password for schoolDB </div>";
	} else {
		header("Location: home.php");
		die();
	}
}

function protect($string) {
	#the mysql_real_escape_string for some reason gets rid of the whole string!
	//$string = mysqli_real_escape_string($mysqli, trim(strip_tags(addslashes($string))));
	$string = trim(strip_tags(addslashes($string)));
	return $string;
};
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

    <?php print $message ?>

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
