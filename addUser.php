<?php
/*******************************************************
  NAME:  addUser.php
  CALLED FROM: userMaint.php
  PURPOSE: add a user with the default info
  TABLES: schoolDB/users
********************************************************/
error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$error_message="";

$frm_login = $frm_fullname = $frm_alpha = "";
$frm_login = clean_input($_POST['frm_login']);
$frm_fullname = clean_input($_POST['frm_fullname']);
$frm_alpha = clean_input($_POST['frm_alpha']);

#if ($frm_login == "") {
#   die("No loginname is entered (or passed to the addUser program)".$frm_login);
#}

$PWD = password_hash($defaultPWD, PASSWORD_DEFAULT);
# INSERT INTO `users` (`login_name`, `full_name`, `alpha`, `password`, `defaultPWD`, `isAdmin`, `isWait`, `isTeam`) VALUES ('ddavis', 'Dawn Davis', 'SST', '', '', '1', '0', '1', '0');
//Add a new record, but ignore it if the record (ie. user) already exists
//Use default values for the fields that are not listed. 
$sql = "INSERT IGNORE INTO users (login_name, full_name, alpha, password) VALUES (?, ?, ?, ? )";
if ($stmt = $schoolDB->prepare($sql)) {
   $stmt->bind_param("ssss", $frm_login, $frm_fullname, $frm_alpha, $PWD);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_);
}

#echo $teacher . " " .$coursecode." ".$p." ".$room;
header("Location: userMaint.php");


//This is a function to see if the record already exists. It needs to be modified, but maybe we don't even need it, since we have INSERT IGNORE
function isDuplicate_record($studentNum, $schoolDB) {
        $sql = "SELECT * FROM students WHERE studentID = '" . $studentNum . "'";
        $result = mysqli_query($schoolDB, $sql);
        if (!$result) {
                die("$studentNum Query to search student numbers in students failed \n $sql");
        }
        $row_cnt = mysqli_num_rows($result);
        if ($row_cnt > 0) return true;
        return false;
}
?>

