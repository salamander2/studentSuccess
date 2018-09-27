<?php
/*******************************************************************************
  Name: changePWD.php
  Called from: admin.php
  Purpose: change user password 
 ***NOTE: does not work with prepared statements
 Tables used: schoolDB/users
 Calls: 
 Transfers control to: logout.php
 ******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

//This won't work if you're a readonly user. You can't change your password. Maybe this is a good thing.
$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$error_message="";
$newpass = "";

//if the submit button has been pressed:
if(isset($_POST['submit'])) {  

	$newpass = clean_input($_POST['newpass']);
	if (strlen($newpass) < 7) $error_message = "Your password must be at least 7 characters";
	if (empty($newpass))  $error_message = "Please enter a password";

	//if correct, then add to database
	if (empty($error_message)) {
		$hashPassword = password_hash($newpass, PASSWORD_DEFAULT);

		$sql = "UPDATE users SET password=?, defaultPWD=0 WHERE login_name=?";

		if ($stmt = $schoolDB->prepare($sql)) {
			$stmt->bind_param("ss", $hashPassword, $username);
			$stmt->execute();
			$stmt->close();
		} else {
			$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
			$message_ .= 'SQL: ' . $sql;
			die($message_);
		}

		header("Location: logout.php");
	}
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Change password</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="admin.php">  Cancel</a>
<span class="fright">&nbsp; &nbsp; &nbsp;</span>
<h1>Change your login password: <span class="green"><?php echo $fullname; ?></span></h1>
</div>


<!-- <form class="pure-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"> -->
<form class="pure-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<p class="white">You will have to login again after changing your password.</p>
<fieldset>
<legend>

<?php echo $error_message; ?>

<table>
<tr>
<td class="tcol1">
<p>New Password:</p>
</td><td class="tcol2">
<input name="newpass" style="color:#777;" type="password" size="15" maxlength="15"><br>
</td>
</tr>
</table>
</legend>
<button type="submit" name="submit" class="pure-button fleft" style="margin:0 0.75em;font-weight:bold;">Submit</button>
</fieldset>
</form>
</div>
</body>
</html>

