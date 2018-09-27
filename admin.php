<?php 
/*******************************************************************************
   Name: admin.php
   Called from: home.php
   Purpose: for administrators and users to change various options
   Tables used: --
   Transfers control to: --
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

?>

<!DOCTYPE html>
<html>

<head lang="en">
<meta charset="UTF-8">
<title>
Student Waitlist Database -- administrative options
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
<h1>Waitlist database administration</h1>
<?php printHeader($fullname, $alpha, $isTeam); ?>
</div>

<?php echo $error_message; ?>

<?php echo $sql_user; ?>
<div id="main">


<ul>
<?php
if (1==$isTeam) {
	echo '<li><a style="font-size:larger;" href="changePWD.php">Change your own login password</a><br><br></li>';
} else {
	echo '<li><a style="font-size:larger;" href=""><s>Change your own login password</s></a><br><br></li>';
}
?>
<li><a style="font-size:larger;" href="listUsers.php">List database users</a></li>
</ul>
<hr>
<p>&nbsp;</p>
<?php
//if ($username == "DB-admin") {
//   echo "<div id=\"admin_options\"><ul>";
//   echo "<li class=\"majorlinks\"><a href=\"userAdmin.php\">Add database users</a></li>";
//   echo "<li class=\"majorlinks\"><a href=\"userAdmin.php\">Inactivate database users</a></li>";
//   echo "<li class=\"majorlinks\"><a href=\"userAdmin.php\">Delete database users</a></li>";
//   echo "</ul> </div>";
//}
?>
</div>

<div id="footer" class="centered">
Created by Michael Harwood &copy; 2017.
</div>
</body>
</html>
