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
			echo "Failed to connect to MySQL database 'schoolDB' : " . mysqli_connect_error();
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
		$sql = "SELECT full_name, alpha, password, isAdmin, isTeamAdmin, isTeam FROM users WHERE login_name = ?";
		if ($stmt = $schoolDB->prepare($sql)) {
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$stmt->bind_result($fullname,$alpha,$hashPassword,$isAdmin,$isTeamAdmin,$isTeam);
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
			$error_message = "Invalid password.";
		}
	}

	// Extra check to make sure that the "teacher" login never gets isTeam access ...
	if (empty($error_message)) {
		if (1==$isTeam && $username=="teacher") {
			$error_message = "ERROR: someone has granted TEACHER user elevated permissions. Contact Michael Harwood ASAP.";
		}
	}

	//5. determine access priveledges and set session variables, then go to the next page
	if (empty($error_message)) {
		if (1==$isAdmin) {
			$sql_user = $username;
			$sql_pass = $password;
		} else if (1==$isTeamAdmin) {
			$sql_user = $userADM;
			$sql_pass = $passADM;
		} else if (1==$isTeam) {
			$sql_user = $userSTD;
			$sql_pass = $passSTD;
		} else {
			$sql_user = $userRO;
			$sql_pass = $passRO;
		}

		//store session variables
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
#		$_SESSION["alpha"] = $alpha;	//done in home.php

		$_SESSION["sql_user"] = $sql_user;
		$_SESSION["sql_pass"] = $sql_pass;

		$_SESSION["isAdmin"] = $isAdmin;
		$_SESSION["isTeamAdmin"] = $isTeamAdmin;
		$_SESSION["isTeam"] = $isTeam;

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css" type="text/css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
<title><?= $schoolName2 ?> Student Database: login</title>
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="http://iquark.ca">  Go Back</a>
<h1><?= $schoolName1 ?> : Student Database</h1>
<h2>Enter your credentials</h2>

<?php print $error_message ?>

<table style="margin:20px;text-align:left;" cell-spacing=10>
<tr>
<td width=40%>
<div class="box-repeat" style="color:#000;border-width:2px;">
<p>For this DEMONSTRATION DATABASE, the logins are<br>
<code>admin1, teamMember, generic</code>.</p>
<p>The password for all three is "FloralCanoe" (but it might be changed by users).</p>
<p>The SQL database will be restored to its original condition each Wednesday and Saturday nights.</p>
</div>
</td><td>
<form class="pure-form" style="margin: 0 1em;" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
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
</td></tr>
<tr><td colspan=3>
<div class="prevComment">
<b>This is a database program that has three purposes:</b>
<ol><li> It provides a quick way to see students' timetables and photos.
<li> It provides a handy way that staff can record teacher contacts with students (especially during the pandemic). It is used as a medium to facilitate communication between staff (and also administration) to help track student progress.
<li> It is also used by the "At Risk team" or "Student Success Team". This team can record more detailed comments about at-risk students. This intra-team communication tool is used for their team meetings. 
</ol>
</div>
<div class="prevComment">
<b>There are three categories of users:</b>

<ol><li><u>admin</u>: The admins can add more team member users. They can also see the reports, as well as see all of the comments and next steps for the at-risk students. The team admins are the only ones who can perform certain functions on the At-Risk student information. They can highlight the names (for discussion at the next team meeting), add students to the at-risk list, remove them from the list, etc.
<li><u>team member</u>: Team members have their own personal logins and passwords. They can read and add comments to at-risk students. They can also see the reports. 
Along with admin, they can edit student information (which will remain until the next update of student data).
<li><u>generic teacher</u>: This is a user who has no access whatsoever to the At-Risk comment side of things. The generic teacher can only add in student contact information.
</ol>
</div>

<div class="prevComment">
<b>Security:</b>

<ul><li> Users can change their own passwords (they login with a generic password when their account is created). All passwords are hashed and salted before storing.<br> 
  <code>$hashPassword = password_hash($newpass, PASSWORD_DEFAULT);</code>
<li>All database queries involving user input are done with prepared statements.
<li>We have not thought it worth creating a specific login for every single teacher, since our staff is so large, but it wouldn't be out of the question.
<li>All comments entered are not able to be subsequently modified.
<li>All comments and next steps entered on the At-Risk page are encrypted before they are stored (AES_encrypt), password is 32 character random alphanumeric string (generated upon installation).
</ul>
</div>
</td></tr>
</table>
<p class="prevComment"><b>Data source:</b><br>
For this demonstration database: random first names were generated (and attempted to match with gender), random last names were generated, random student numbers were generated (9 digits, beginning with 3, all unique). 
Random birthdays were generated for students. Random phone numbers and guardian emails were generated. 
Photos are of actual students, but their student numbers have been randomly generated. 
Teacher names are randomly generated. Actual course codes and timetables were used as it wasn't really necessary to generate fake ones. 
</div>
</body>
</html>
