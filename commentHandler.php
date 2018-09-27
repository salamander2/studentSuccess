<?php
/*******************************************************************************
   Name: CommentHandler.php
   Called from: --
   Purpose: This file is used to insert data into the comment's table
   Tables used: sssDB/comments
   Transfers control to: home.php on successful login
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);

$comment = $_POST["comment"];

//$comment = trim( strip_tags( mysqli_real_escape_string($mysqli, $comment)));
//$comment = trim( htmlspecialchars( mysqli_real_escape_string($mysqli, $comment)));
$comment = clean_input($comment);

if (strlen($comment) == 0) {
    header("location: commentPage.php?ID=$studentID");
    return;
}
//$comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
//$sql = "INSERT INTO comments (notes, student_number, login_name) VALUES (AES_ENCRYPT('$comment', '$masterkeyhash'), '$student_number', '$username')";
$sql = "INSERT INTO comments (notes, studentID, login_name) VALUES (AES_ENCRYPT(?, ?), ?, ?)";

if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("ssis", $comment, $masterkeyhash, $studentID, $username);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

header("location: commentPage.php?ID=$studentID"); //go back to the main page

?>
