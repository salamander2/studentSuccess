<?php
/*******************************************************************************
   Name: admin.php
   Called from: courseMain.php
   Purpose:
   Tables used: --
   Transfers control to: --
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);
$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);


?>

<!DOCTYPE html>
<html>

<head lang="en">
<meta charset="UTF-8">
<title>
Student Contact Database -- Reports
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
<h1>Student Contact Reports</h1>
<?php printHeader($fullname, $alpha, $isWait); ?>
</div>


<div id="main">
<p>&nbsp;</p>
<p>1. <a href="printNoContact.php" class="lightblue">Students who have not been contacted</a> Sorted by name</p>
<p>2. <a href="printAll.php">Print complete database</a> All students, sorted by name</p>
<p>3. <a href="printAllByDate.php">Print complete database</a> All students, most recent entries at top</p>
<p>4. <a href="exportCSV.php">CSV download</a> of complete database. Sorted by student name</p>
<p class="tan"><i>If you want any other fields in the CSV download, let me know</i></p>
</div>
<p>&nbsp;</p>

<hr>
</body>
</html>
