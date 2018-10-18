<?php
/*******************************************************************************
   Name: removeAtRisk.php
   Called from: commentPage.php
   Purpose: remmoves all next steps, all comments, and sssInfo record
		so that all at-risk info is gone.
   Tables used: sssDB/comments, sssDB/next_steps, sssDB/sssInfo
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);

//The admin user has already been asked if they want to do this and the confirm button has been pressed
//With a regular backup, comments that have been deleted can be restored from it.

//get commentID from URL parameter
$commentID = $_GET['ID'];

$date = date("Y-m-d");
$sql = "UPDATE sssInfo SET selected='0', lastMtg='$date' WHERE studentID=?";

die("NOT COMPLETED!!!");

/*
SELECT id FROM comments WITH studentID = $$
this will give you a list of comment numbers

delete all next steps that have that number

Delete next_steps where commentID = $$

delete sssInfo with studentID = $id

*/

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
