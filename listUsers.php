<?php
/*******************************************************************************
   Name: listUsers.php
   Called from: admin.php
   Purpose: lists users of this database
   Tables used: sssDB/users
   Transfers control to: --
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

?>

<!DOCTYPE html>
<!--This page will list all of the users allowed to access this database -->
<html>
<head>
<title>Student Success Database: <?php echo $username; ?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="admin.php">  Go Back</a>
    <h1>Student Success Database</h1>
    <?php printHeader($fullname, $alpha, $isTeam); ?>
    <hr>
</div>

<div id="main-top">
<h2> Users allowed to access this database </h2>
<h5 class="tan">(ie. read comments and modify data)</h5>

<table class="pure-table pure-table-bordered">
<thead>
<tr>
<th>Login name</th>
<th>Full name</th>
<th>Alpha</th>
</tr>
</thead>
<tbody>

<?php
$sql = "SELECT * FROM users WHERE isTeam = '1'";
// sending query
$result = mysqli_query($schoolDB, $sql);
if (!$result) {
        die("Query to show fields from table failed. listUsers.php");
}

// printing table rows
// $row = mysql_fetch_row($result);
while ($row = mysqli_fetch_assoc($result)){
   echo"<tr>";
   echo "<td>".$row['login_name'] ."</td>";
   echo "<td>".$row['full_name'] ."</td>";
   echo "<td>".$row['alpha'] ."</td>";
   echo"</tr>";
} 
?>
</tbody>
</table>
<p>&nbsp;</p>
</div>

</body>
</html>

