<?php
/*******************************************************************************
  Name: toggleSelect.php
  Called from: home.php
  Purpose: 
  Tables used: schoolDB/students, sssDB/sssInfo
 ******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);
$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

// get the q parameter from URL
$ID_ = clean_input($_REQUEST["q"]);

$sql = "SELECT selected FROM sssInfo WHERE studentID=?";

if ($stmt = $sssDB->prepare($sql)) {
	$stmt->bind_param("i", $ID_);
	$stmt->execute(); 
	$result = $stmt->get_result();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
	$message_ .= 'SQL1: ' . $query;
	die($message_); 
}
$checked = $result->fetch_row()[0];

#echo "YES".$q;
#echo "<br>NO".$checked;

//toggle checked
if ($checked==1) $checked = 0;
else $checked = 1;
$sql = "UPDATE sssInfo SET selected=? WHERE studentID=?";

if ($stmt = $sssDB->prepare($sql)) {
	$stmt->bind_param("ii", $checked, $ID_);
	$stmt->execute(); 
#	$result = $stmt->get_result();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}

//send something back to XmlHTTPRequest
//echo $checked;
?>
