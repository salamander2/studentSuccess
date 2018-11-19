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
$studentID = $_GET['ID'];

//Get a list of all comment numbers for selected student
$sql = "SELECT id FROM comments WHERE studentID = ?";

if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("i", $studentID);
   $stmt->execute();
   $result = $stmt->get_result();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

//for each comment
while ($row = mysqli_fetch_row($result)){
   $commentID = $row[0];

	// Delete all next steps. If there are no next steps, no problem.
	$sql = "DELETE FROM next_steps WHERE commentID = ?";
	if ($stmt = $sssDB->prepare($sql)) {
	   $stmt->bind_param("i", $commentID);
	   $stmt->execute();
	   $stmt->close();
	}

	/*
	// now delete all of the comment records for that student
	$sql = "DELETE FROM comments WHERE id = ?";
	if ($stmt = $sssDB->prepare($sql)) {
	   $stmt->bind_param("i", $commentID);
	   $stmt->execute();
	   $stmt->close();
	}
	*/
}

// now delete all of the comment records for that student
$sql = "DELETE FROM comments WHERE studentID = ?";
if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("i", $studentID);
   $stmt->execute();
   $stmt->close();
}

// Delete the sssInfo record
$sql = "DELETE FROM sssInfo WHERE studentID = ?";
if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("i", $studentID);
   $stmt->execute();
   $stmt->close();
}

//return to the comment page for that student
$dest = "commentPage.php?ID=".$studentID;
header("Location: $dest");
?>
