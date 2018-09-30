<?php
/*******************************************************************************
   Name: udpateSSTdate.php
   Called from: commentPage.php
   Purpose: to set the date of the last meeting in the ssInfo record
		and to remove selected flag.
   Tables used: sssDB/comments
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);

//get commentID from URL parameter
$commentID = $_GET['ID'];

$date = date("Y-m-d");
$sql = "UPDATE sssInfo SET selected='0', lastMtg='$date' WHERE studentID=?";

if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("i", $commentID);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

//return to the comment page for that student
$dest = "commentPage.php?ID=".$studentID;
header("Location: $dest");
?>
