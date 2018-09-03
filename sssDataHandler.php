<?php
/*******************************************************************************
   Name: sssDataHandler.php
   Called from: commentPage.php
   Purpose: adds or updates the sssInfo record for a student
   Tables used: sssDB/sssInfo
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.php');
require_once('sssdb.php');

$sssDB = connectToDB("sssDB",$username,$password);

$iep = (bool)$_POST["iep"]; //false=0; true=1; empty?
$fnmi = (bool)$_POST["fnmi"];
$grade = $_POST["grade"];
$swid = $_POST["swid"];
$staff = $_POST["staff"];

$staff = clean_input($staff);

if(empty($iep)) $iep='0';
if(empty($fnmi)) $fnmi='0';

/*
+----------------+---------------------+------+-----+---------+-------+
| Field          | Type                | Null | Key | Default | Extra |
+----------------+---------------------+------+-----+---------+-------+
| studentID      | int(10) unsigned    | NO   | PRI | NULL    |       |
| IEP            | tinyint(1)          | YES  |     | 0       |       |
| FNMI           | tinyint(1)          | YES  |     | 0       |       |
| grade          | int(10) unsigned    | NO   |     | NULL    |       |
| staff          | varchar(40)         | YES  |     | NULL    |       |
| swID           | bigint(20) unsigned | YES  |     | 1       |       |
+----------------+---------------------+------+-----+---------+-------+
*/

//setup the two SQL statements so that the parameters are in the same order for each one.
if ($_POST["dataExists"]) {
   //update existing record
   //$sql = "UPDATE sssInfo SET IEP='$iep', FNMI='$fnmi', grade='$grade', staff='$staff', swID='$swid' WHERE studentID='$studentID'";
   $sql = "UPDATE sssInfo SET IEP=?, FNMI=?, grade=?, staff=?, swID=? WHERE studentID=?";
} else {
   //create a new record
   //$sql = "INSERT INTO sssInfo (studentID, IEP, FNMI, grade, staff, swID) VALUES ('$studentID', '$iep', '$fnmi', '$grade', '$staff', '$swid')";
   $sql = "INSERT INTO sssInfo (IEP, FNMI, grade, staff, swID, studentID) VALUES (?, ?, ?, ?, ?, ?)";

}

if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("iiisii", $iep, $fnmi, $grade, $staff, $swid, $studentID);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

header("location: commentPage.php?ID=$studentID"); //go back to the main page

?>
