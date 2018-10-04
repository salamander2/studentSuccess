<?php
/*******************************************************
  NAME:  deleteUser.php
  CALLED FROM: userMaint.php
  PURPOSE: deletes a user
  TABLES: schoolDB/users
********************************************************/
error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$error_message="";

$frm_login = $_GET['ID'];
#$frm_login = clean_input($_POST['ID']);

$sql = "DELETE FROM users WHERE login_name = ?";
if ($stmt = $schoolDB->prepare($sql)) {
   $stmt->bind_param("s", $frm_login);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_);
}

header("Location: userMaint.php");

?>

