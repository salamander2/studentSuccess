<?php
/*******************************************************************************
   Name: ContactHandler.php
   Called from: --
   Purpose: This file is used to insert data into the tcontact table
   Tables used: sssDB/tcontact
   Transfers control to: home.php on successful login
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);

$teacherName = $_POST["teacherName"];
$teacherName = clean_input($teacherName);

$contactMethod = $_POST["contactMethod"];
$contactMethod = clean_input($contactMethod);

$personContacted = $_POST["personContacted"];
$personContacted = clean_input($personContacted);
$dateContacted = $_POST["dateContacted"];
$dateContacted = clean_input($dateContacted);

$notes = $_POST["notes"];
$notes = clean_input($notes);

$sql = "INSERT INTO tcontact (studentID, teacher, contactMethod, personContacted, date, notes) VALUES (?, ?, ?, ?, ?, ?)";

#die($dateContacted);
if ($stmt = $sssDB->prepare($sql)) {
   $stmt->bind_param("isssss", $studentID, $teacherName, $contactMethod, $personContacted, $dateContacted, $notes);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

header("location: showContacts.php?ID=$studentID"); //go back to the main page

?>
