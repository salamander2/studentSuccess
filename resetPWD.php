<?php
/*******************************************************
  NAME:  resetPWD.php
  CALLED FROM: userMaint.php
  PURPOSE: resets suer password to default
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

$hashPassword = password_hash($defaultPWD, PASSWORD_DEFAULT);
# for Teacher login only
#$hashPassword = password_hash("welcome2140", PASSWORD_DEFAULT);

$sql = "UPDATE users SET password=?, defaultPWD=1 WHERE login_name=?";

if ($stmt = $schoolDB->prepare($sql)) {
   $stmt->bind_param("ss", $hashPassword, $frm_login);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_);
}

header("Location: userMaint.php");

?>

