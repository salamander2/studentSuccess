<?php
/*******************************************************************************
   Name: nextSteps.php
   Called from: commentPage.php
   Purpose: to add next steps comment to next steps table
   Tables used: sssDB/next_steps
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.php');
require_once('sssdb.php');

$sssDB = connectToDB("sssDB",$username,$password);

$nextSteps = $_POST["nextSteps"];
$commentID = $_POST["commentID"];
$nextSteps = clean_input($nextSteps);

//check to make sure that there is a comment to enter (after the comment has been santized).
if (strlen($nextSteps) == 0) {
    header("location: commentPage.php?ID=$studentID");
    return;
}

/*
+------------+---------------------+------+-----+-------------------+----------------+
| Field      | Type                | Null | Key | Default           | Extra          |
+------------+---------------------+------+-----+-------------------+----------------+
| id         | bigint(20) unsigned | NO   | PRI | NULL              | auto_increment |
| notes      | blob                | NO   |     | NULL              |                |
| commentID  | bigint(20) unsigned | NO   |     | NULL              |                |
| login_name | varchar(20)         | NO   |     | NULL              |                |
| timestamp  | timestamp           | NO   |     | CURRENT_TIMESTAMP |                |
+------------+---------------------+------+-----+-------------------+----------------+
*/

//$sql = "UPDATE comments SET next_steps=AES_ENCRYPT('$nextSteps', '$masterkeyhash'), completed='$done' WHERE id='$commentID'";
//$sql = "INSERT INTO next_steps (notes, commentID, login_name) VALUES (AES_ENCRYPT('$nextSteps', '$masterkeyhash'), '$commentID', '$username')";
$sql = "INSERT INTO next_steps (notes, commentID, login_name) VALUES (AES_ENCRYPT(?,?),?,?)";

if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("ssis", $nextSteps, $masterkeyhash, $commentID, $username);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

header("location: commentPage.php?ID=$studentID"); //go back to the main page

?>
