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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>
Student Success Database -- administrative options
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
<h1>Student Success database administration</h1>
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

echo '<li><a style="font-size:larger;" class="white" href="listUsers.php">List database users</a><br><br></li>'.PHP_EOL;

if (1==$isTeam) {
	echo '<a href="home.last.php"><button class="nav-button pure-button fright" style="border:solid 3px deeppink;">Last Year\'s Comments</button></a>'.PHP_EOL;
}
if (1==$isTeamAdmin) {
	echo '<li><a style="font-size:larger;color:red;padding:5px 10px;border:1px solid gray;" href="userMaint.php">Add, modify, delete users</a><br><br></li>';
}
echo "</ul>";

?>
<hr>
<p>&nbsp;</p>
</div>

<div id="footer" class="centered">
Created by Michael Harwood &copy; 2018.
</div>
</body>
</html>
