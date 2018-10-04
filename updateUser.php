<?php
/*******************************************************
  NAME:  updateUser.php
  CALLED FROM: userMaint.php
  PURPOSE: update a user with the default info
  TABLES: schoolDB/users
 ********************************************************/
/* FIXME:
   If this page is called from userMaint and type is TEAM
   then isWait_row is not set.
*********************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

## This will go to xmlhttp.responsetext
#die("HERE in updateUser!");

$error_message="";

$frm_login = $frm_alpha = $frm_wait = $frm_team = "";
$frm_login = clean_input($_POST['login']);
$frm_alpha = clean_input($_POST['alpha_row']);
// $frm_wait = $_POST['isWait_row']; FIXME
$frm_team = $_POST['isTeam_row'];

// if ($frm_wait == "true") $frm_wait=1;  FIXME
// else $frm_wait=0;

if ($frm_team == "true") $frm_team=1;
else $frm_team=0;

if ($frm_login == "") {
	die("No loginname is entered (or passed to the addUser program)".$frm_login);
}

// $sql = "UPDATE users SET alpha=?, isWait=?, isTeam=? WHERE login_name=?"; FIXME
$sql = "UPDATE users SET alpha=?, isTeam=? WHERE login_name=?";
if ($stmt = $schoolDB->prepare($sql)) {
	// $stmt->bind_param("siis", $frm_alpha, $frm_wait, $frm_team, $frm_login ); FIXME
	$stmt->bind_param("sis", $frm_alpha, $frm_team, $frm_login );
	$stmt->execute();
	$stmt->close();
} else {
	$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
	$message_ .= 'SQL: ' . $sql;
	die($message_);
}

#header("Location: userMaint.php");

?>

