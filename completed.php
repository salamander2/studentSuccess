<?php
/*******************************************************************************
   Name: completed.php
   Called from: commentPage.php
   Purpose: to set the completed flag in the comments record
   Tables used: sssDB/comments
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.php');
require_once('sssdb.php');

$sssDB = connectToDB("sssDB", $username, $password);

//get commentID from URL parameter
$commentID = $_GET['ID'];

//update the comment to set the field to completed.
$sql = "UPDATE comments SET completed='1' WHERE id= ?";

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
