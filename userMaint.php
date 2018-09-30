<?php
/*******************************************************
  NAME: userMaint.php
  CALLED FROM: admin.php
  PURPOSE: add, delete users. Change permissions.
  TABLES: schoolDB/users
 ********************************************************/
error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php'); //this has the passwords for loging into Student Success Database
require_once('common.inc.php');

/*******************************************************
  The following variable determines which user settings can be displayed and modified. Choices are  ALL, WAIT, TEAM
  The point of this is to be able to use this same program for all 3 sitations.
*******************************************************/
$userList="TEAM";

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$error_message1 = "";
$fileFound=true;

# count number of records in 'students' table
$sql = "SELECT * FROM users";
$result = mysqli_query($schoolDB,$sql);
if (!$result) {
	die("Query of user table failed");
}


// *************************  Handle form submission for update/delete ************************
// if(isset($_POST['submit'])) {
//}

?>

<!DOCTYPE HTML>
<head lang="en">
<meta charset="UTF-8">
<title>
schoolDB user maintenance page
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">

<script type="text/javascript" src="jquery-1.8.0.min.js"></script>

</head>
<body>

<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="admin.php">  Go Back</a>
<h1>Database user administration</h1>
</div>

<div id="main">
<script>
function updateRow(num, login) {

	//Create a formdata object
	var formData = new FormData();

	formData.append("login", login);

	//get the data from the row
	var name = "alpha_row" + num;
	var val = document.getElementById(name).value;
	formData.append("alpha_row",val);

	name = "isWait_row" + num;
	val = document.getElementById(name).checked;
	formData.append("isWait_row",val);

	name = "isTeam_row" + num;
	val = document.getElementById(name).checked;
	formData.append("isTeam_row",val);

	//Warning: You have to use encodeURIComponent() for all names and especially for the values so that possible & contained in the strings do not break the format.

	var xmlhttp = new XMLHttpRequest();
	//Send the proper header information along with the request: DOES NOT WORK!
	//xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	//xmlhttp.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=1');
	//xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			window.location.reload(true);
		}
	}

	xmlhttp.open("POST", "updateUser.php");
	xmlhttp.send(formData);
}
</script>

<script>
function validateUserData() {
	var x, text;

	x = document.getElementById("frm_fullname").value;
	if (x.length == 0) {
		text = "You must enter a full name";
		text = "<div class=\"error\">" + text + "</div>";
		document.getElementById("error_message").innerHTML = text;
		return false;
	}

	x = document.getElementById("frm_login").value;
	if (x.length == 0) {
		text = "You must enter a login name";
		text = "<div class=\"error\">" + text + "</div>";
		document.getElementById("error_message").innerHTML = text;
		return false;
	}

	x = document.getElementById("frm_alpha").value;
	if (x.length == 0) {
		text = "You must enter a department";
		text = "<div class=\"error\">" + text + "</div>";
		document.getElementById("error_message").innerHTML = text;
		return false;
	}
	return true;
}
</script>
<!-- ****************************************** ADD NEW USER ****************************** -->

<h3 class="centered lightblue" style='margin-bottom:0;'> Add a new user</h3>
<form class="pure-form" style="margin-top:0;"  method="post" action="addUser.php" onsubmit="return validateUserData();">
<fieldset class="centered" style="margin:auto;max-width:100px;">
<legend>

<table class="smaller centered">
<tr>
<td class="smaller center nomargin">Login</td>
<td class="smaller center nomargin">Full name</td>
<td class="smaller center nomargin">Department</td>
</tr><tr>
<td><input class="small" id="frm_login" name="frm_login" type="text" value=""></td>
<td><input class="small" id="frm_fullname" name="frm_fullname" type="text" value=""></td>
<td><input class="small" id="frm_alpha" name="frm_alpha" type="text" maxlength="26" value=""></td>
<td><button type="submit" name="submit" class="pure-button fleft" style="margin:0 0.75em;font-weight:bold;">Submit</button></td>
</tr>
</table>
</legend>
</fieldset>
</form>

<div id="error_message"></div>

<hr>

<!--
CREATE USER 'rburen'@'localhost' IDENTIFIED BY 'default_password_here';
GRANT SELECT, INSERT, UPDATE ON sssDB.* TO 'rburen'@'localhost';
GRANT SELECT ON schoolDB.* TO 'rburen'@'localhost';

use schoolDB;
INSERT INTO `users` (`login_name`, `full_name`, `alpha`, `password`, `salt`, `defaultPWD`, `isAdmin`, `isWait`, `isTeam`) VALUES ('ddavis', 'Dawn Davis', 'SST', '', '', '1', '0', '1', '0');
-->

<!-- ****************************** TABLE OF DB USERS ******************************************************** -->

<?php
echo "<p style='margin-bottom:0;'>&nbsp;</p>";

echo '<table class="pure-table pure-table-striped">';
echo '<tr>';
echo '<th class="smaller ">Login name</th>';
echo '<th class="smaller ">Full Name</th>';
echo '<th class="smaller ">Descr./Alpha</th>';
if ($userList == "ALL" || $userList == "WAIT") {
	echo '<th class="smaller ">Waitlist access</th>';
}
if ($userList == "ALL" || $userList == "TEAM") {
	echo '<th class="smaller ">AtRisk Team</th>';
}
echo '<th colspan=2>&nbsp;</th>';
echo '<th colspan=2 class="smaller fontONE" style="text-align:right;">Default PWD</th>';

echo '</tr>';
echo  PHP_EOL;

$colspan=5;
if ($userList != "ALL") $colspan=4;

$num = 1;
while ($row = mysqli_fetch_assoc($result)){

	$adminTxt= "";
	$admin = false;
	if ($row['isTeamAdmin'] ==1) {
		$admin = true;
		$adminTxt .= "At-Risk Team Admin  &nbsp;&nbsp;&nbsp; ";
	}
	if ($row['isWaitAdmin'] ==1) {
		$admin = true;
		$adminTxt .= "Waitlist Admin";
	}
	if ($row['isAdmin'] ==1) {
		$admin = true;
		$adminTxt = "Database Admin &nbsp;&nbsp;&nbsp; (".$adminTxt.")";
	}
	$adminTxt = "<i>".$adminTxt."</i>";	
	if ($admin) $style='style="color:black;background-color:darkgoldenrod;"';
	else $style = 'style="color:black;"';
	echo '<tr>';
	echo '<td '.$style.' >' .$row['login_name']. '</td>';
	echo '<td '.$style.' >' .$row['full_name'].  '</td>';

	if ($admin) {
		echo "<td colspan=$colspan style=\"color:white;background-color:#999;\">$adminTxt</td>";
	} else {
# The following do not show up for isAdmin */
		echo '<td '.$style.'><input type="text" class="smaller" id="alpha_row'.$num.'" size=15 value="' .$row['alpha']. '"></td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['login_name']. '</td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['full_name']. '</td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['alpha']. '</td>';

#     echo '<td style="color:black;" id="login" name="login">' .$row['isWait']. '</td>';
		if ($userList == "ALL" || $userList == "WAIT") {
			echo "<td $style>";
			echo '<input id="isWait_row'.$num.'" type="checkbox" value="isWait"';
			if ($row['isWait'] == 1) echo " checked ";
			echo '>';
			echo '</td>';
		}

#     echo '<td style="color:black;" id="login" name="login">' .$row['isTeam']. '</td>';
		if ($userList == "ALL" || $userList == "TEAM") {
			echo "<td $style>";
			echo '<input id="isTeam_row'.$num.'" type="checkbox" value="isTeam"';
			if ($row['isTeam'] == 1) echo " checked ";
			echo '>';
			echo '</td>';
		}
		/* The delete button is a straight call to a separate php page.
		   So is the reset password button

		   The update button must be the submit button on a form. The form is comprized of everything in the row up to there.
		   Each field must have a name, then the form can be done using POST method.
		 */
		echo '<td><button type="submit" onclick="updateRow('.$num.',\''.$row['login_name'].'\')">Update</button></td>';
		echo '<td><a href="deleteUser.php?ID='.$row['login_name'].'"><button type="submit" name="delete" style="color:red;" onclick="return confirm(\'Are you sure?\');" >Delete</button></a></td>';

	} //end of isAdmin check
	echo '<td><a href="resetPWD.php?ID='.$row['login_name'].'"><button type="submit" name="changePWD" onclick="return confirm(\'Are you sure?\');" >Reset Password</button></a></td>';
	echo "<td $style>";
	if ($row['defaultPWD'] == 1) echo " <center><b>*</b></center> ";
	echo '</td>';

	echo '</tr>';
	echo  PHP_EOL; //for viewing source code.
	$num ++;
}

echo '</table>';
#   echo '<input id="isTeam" name="isTeam" type="text" value="' .$row['isTeam'].'">';

/*
   echo password_hash("rasmuslerdorf", PASSWORD_DEFAULT)."<br>";
   echo password_hash("rasmuslerdorf", PASSWORD_DEFAULT)."<br>";
   echo password_hash("rasmuslerdorf", PASSWORD_DEFAULT)."<br>";

   $aa='$2y$10$/VQDiyei7ppcivPkQ1Hk7OcJmSUQAw3YRILGWIRKPpWN/2JCkNJlK';
   $bb='$2y$10$iY7md6WkX/bhc5dI8pQAAeArBKzFV.vi0lfQPTayE8ANzrVBbrB0u';
   $cc='$2y$10$pOJY5bXv5TmC4KvYegef0.A7Yh.NegLxNnpL4fX7.jqcziv/4Ic4C';

   echo password_verify("rasmuslerdorf",$aa);
   echo password_verify("rasmuslerdorf",$bb);
   echo password_verify("rasmuslerdorf",$cc);
 */

echo '<br>';
echo '<button type="button" onclick="showDP();">Show Default Password</button>';
echo '<hr>';

$DP = base64_encode($defaultPWD);
//$rand = substr(uniqid('', true), -5);	//5 random numbers
$length = 5;
$randomletter = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
$DP = $randomletter . $DP;
#echo $DP;
?>

<br>
<script>
function showDP() {
  var s = "<?php echo $DP?>";
  s = s.substr(5);
  alert(atob(s));
}
</script>
</div>
<div id="footer" class="centered">
Created by Michael Harwood &copy; 2018.
</div>
</body>
</html>

