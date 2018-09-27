<?php
/*******************************************************************************
   Name: home.php
   Called from: index.php
   Purpose: this is the page to search for students
   Tables used: sssDB/users
   Calls: studentFind.php
	- with either the student number or else with 'ACTIVATED'
   Transfers control to: logout.php, admin.php, addstudent.php
******************************************************************************/
error_reporting(E_ALL);
// Start the session
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

//retrieve user info
#$sql = "SELECT full_name, alpha, isTeam FROM users WHERE login_name = ?";
$sql = "SELECT full_name, alpha FROM users WHERE login_name = ?";

if ($stmt = $schoolDB->prepare($sql)) {
  /* bind parameters for markers */
    $stmt->bind_param("s", $username);
    $stmt->execute();
    #$stmt->bind_result($fullname,$alpha,$isTeam);
    $stmt->bind_result($fullname,$alpha);
    $stmt->fetch();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

$_SESSION["fullname"] = $fullname;
$_SESSION["alpha"] = $alpha;

#$_SESSION["isTeam"] = $isTeam;	-- done in index.php
?>

<!DOCTYPE html>
<!--This is the page used to search for students -->
<html>
<head>
<title>Student Success Database: <?php echo $username; ?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
<script type="text/javascript" src="jquery-1.8.0.min.js"></script>
<script>
function showHint(str) {
    if (str.length == 0) { 
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "studentFind.php?q=" + str, true);
        xmlhttp.send();
    }
}
</script>
</head>

<body>
<div id="header">
<a class="fa fa-sign-out nav-button fleft" href="logout.php">  Logout</a>
<span class="fright">
<a class="fa fa-cogs nav-button" href="admin.php">  Administer</a>
<a class="fa fa-plus-circle nav-button" 
   <?php if (0===$isTeam) echo 'style="display:none;"'; ?>
   href="addstudent.php">  Add Student</a>
</span>
    <h1>Student Success Database</h1>
    <?php printHeader($fullname, $alpha, $isTeam); ?>
    <hr>
</div>

<?php echo $sql_user."=".$isTeam; ?>

<form class="pure-form">
<span class="white">Enter First Name, Last Name, or Student Number...</span>
<fieldset>
<input class="pure-input-2-3" autofocus="" type="text" onkeyup="showHint(this.value)" placeholder="Enter First Name, Last Name, or Student Number..." >

<!-- adding in a button to show all of the students who have comments -->
<?php

if (1 === $isTeam) {
echo '<input class="pure-button" style="border:5px outset #999;font-size:16px;" type="button" value="List at-risk students" onclick="showHint(\'ACTIVATED\')" >';
}
?>
</fieldset>
<!-- the student table is created here at txtHint. There is also formatting for this in the css  -->
<div id="txtHint"></div>
</form>
<?php
#  echo "===".$isTeam."===";
echo base64_encode(random_bytes(32));
?>
</body>
</html>

