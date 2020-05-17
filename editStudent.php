<?php
/*******************************************************************************
   Name: editStudent
   Called from: commentPage, home
   Purpose: edit a student to the schoolDB database
   Tables used: schoolDB/students
   Calls: 
   Transfers control to: logout.php, admin.php, addstudent.php 
******************************************************************************/ 
error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

//$studentID = $_GET['ID'];
//$_SESSION["studentID"] = $studentID;

//$error_message="";
// if (empty($lastname))  $error_message = "You must enter a lastname";
// if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";

$sql = "SELECT firstname, lastname, gender, dob, guardianPhone, guardianEmail, loginID, timetable FROM students WHERE studentID = ?";
  if ($stmt = $schoolDB->prepare($sql)) {
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $gender, $dob, $guardianPhone, $guardianEmail, $loginID, $timetable);
    $stmt->fetch();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

$guardianPhone = clean_input($guardianPhone);
$guardianEmail = clean_input($guardianEmail);



/******************** if the submit button has been pressed *******************/
$error_message="";
if(isset($_POST['submit'])) {  

	/* Check all input.
	   Do this here instead of using functions because there are too many variables
	   to make global.
	   Do the verification in reverse order, so that the error messages will start with
	   first field.  */

	//gender: to uppercase, 1 letter: M or F
	$gender = clean_input($_POST['gender']);
	$gender = strtoupper($gender);
	if ($gender <> "M" and $gender <> "F")  $error_message = "Invalid gender";
	//date-of-birth
	//check if A-Z, 0-9:  preg_match('/^[a-zA-Z0-9]+$/', $var)
	$dob = clean_input($_POST['dob']);
	if (!validate_date($dob)) $error_message = "Invalid date or incorrect format";
	//CHECK YEAR: between 1990 and 2015
	list($year, $month, $day) = explode('-', $dob);
	if ($year < 1990 or $year > 2015) $error_message = "Invalid range for year: (1990-2015)";

	$firstname = clean_input($_POST["firstname"]);
	if (empty($firstname ))  $error_message = "You must enter a firstname";

	$lastname = clean_input($_POST['lastname']);
	if (empty($lastname))  $error_message = "You must enter a lastname";

	$guardianEmail = clean_input($_POST['guardianEmail']);
	$guardianPhone = clean_input($_POST['guardianPhone']);

	$timetable = clean_input($_POST['timetable']);
	$timetable = strtoupper($timetable);
	$timetable = str_replace("-", "", $timetable); 
	if (empty($timetable)) $timetable = "newly added";

	if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";
	//if corrent, then add to database
	if (empty($error_message)) {
/*
+-----------+------------------+------+-----+---------+-------+
| Field     | Type             | Null | Key | Default | Extra |
+-----------+------------------+------+-----+---------+-------+
| studentID | int(10) unsigned | NO   | PRI | NULL    |       |
| firstname | varchar(40)      | NO   |     | NULL    |       |
| lastname  | varchar(40)      | NO   |     | NULL    |       |
| gender    | char(1)          | YES  |     | NULL    |       |
| dob       | date             | YES  |     | NULL    |       |
| timetable | varchar(100)     | YES  |     | NULL    |       |
+-----------+------------------+------+-----+---------+-------+

*/

		$sql = "UPDATE students SET firstname=?, lastname=?, gender=?, dob=?, timetable=?, guardianEmail=?, guardianPhone=? WHERE studentID=?";
		if ($stmt = $schoolDB->prepare($sql)) {
		   $stmt->bind_param("sssssssi", $firstname, $lastname, $gender, $dob, $timetable, $guardianEmail, $guardianPhone, $studentID);
		   $stmt->execute();
//echo var_dump($stmt);
		   $stmt->close();
		} else {
	 	   $message  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n";
		   $message .= 'SQL: ' . $sql;
		   die($message);
		} 

		$firstname = $lastname = $studentNum = $gender = $dob = $timetable = "";
		$error_message = "<div class=\"error green\">" . "Student successfully added." . "</div>";
		header("Location: showContacts.php?ID=$studentID");
		//die();
	}
}

function is_digit($digit) {
	// check for numbers only ctype_digit($numeric_string);
	if(is_int($digit)) {
		return true;
	} elseif(is_string($digit)) {
		return ctype_digit($digit);
	} else { // booleans, floats and others
		return false;
	}
}

function validate_Date($mydate, $format = 'YYYY-MM-DD') {

	if ($format == 'YYYY-MM-DD') list($year, $month, $day) = explode('-', $mydate);
	if ($format == 'YYYY/MM/DD') list($year, $month, $day) = explode('/', $mydate);
	if ($format == 'YYYY.MM.DD') list($year, $month, $day) = explode('.', $mydate);

	if ($format == 'DD-MM-YYYY') list($day, $month, $year) = explode('-', $mydate);
	if ($format == 'DD/MM/YYYY') list($day, $month, $year) = explode('/', $mydate);
	if ($format == 'DD.MM.YYYY') list($day, $month, $year) = explode('.', $mydate);

	if ($format == 'MM-DD-YYYY') list($month, $day, $year) = explode('-', $mydate);
	if ($format == 'MM/DD/YYYY') list($month, $day, $year) = explode('/', $mydate);
	if ($format == 'MM.DD.YYYY') list($month, $day, $year) = explode('.', $mydate);       

	if (is_numeric($year) && is_numeric($month) && is_numeric($day))
		return checkdate($month,$day,$year);
	return false;           
}         

function isDuplicate_studentNum($studentNum, $schoolDB) {
	$sql = "SELECT firstname FROM students WHERE studentID = ?";
	if ($stmt = $schoolDB->prepare($sql)) {
    	$stmt->bind_param("i", $studentNum);
	    $stmt->execute();
		$stmt->store_result();
		$row_cnt = $stmt->num_rows;
		$stmt->close();
	}
	if ($row_cnt > 0) return true;
	return false;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Comment Database: Edit student info</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="showContacts.php?ID=<?php echo $studentID;?>">  Go Back</a>
    <h1><?= $schoolName2?> Student Database</h1>
<?php printHeader($fullname, $alpha, $isTeam); ?>
<hr color="black">
</div>

<h1 class="tan centered"><?php echo $lastname, ", ", $firstname."<br> <span class=\"smaller gray\">".$studentID."</span>";?></h1>
<h2 class="white">Edit student info</h2>

<form class="pure-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

<div style="border-top:1px solid gray;padding:15px;margin:0,5px;color:white;text-align:left">

<?php echo $error_message; ?>

<table style="margin:0 2px;">
<tr>
<td class="tcol1"><p>Last Name:</p></td><td class="tcol2"> <input class="ip2" name="lastname" type="text" value="<?php echo $lastname;?>"></td>
</tr><tr>
<td class="tcol1"><p>First Name:</p></td><td class="tcol2"><input class="ip2" name="firstname" type="text" placeholder="First name" value="<?php echo $firstname;?>"></td>
</tr><tr>
<td class="tcol1"><p>Gender</p></td><td class="tcol2"><input class="ip2" name="gender" type="text" size="1" maxlength="1" value="<?php echo $gender;?>"></td>
</tr><tr>
<td class="tcol1"><p>Date of Birth:</p></td><td class="tcol2"><input class="ip2" name="dob" type="text" placeholder="YYYY-MM-DD" value="<?php echo $dob;?>"></td>
</tr><tr>
<td class="tcol1"><p>Guardian Phone:</p> </td><td class="tcol2"> <input class="ip2" name="guardianPhone" type="text" size="40" value="<?php echo $guardianPhone;?>"></td>
</tr><tr>
<td class="tcol1"><p>Guardian Email:</p> </td><td class="tcol2"> <input class="ip2" name="guardianEmail" type="text" size="100" value="<?php echo $guardianEmail;?>"></td>
</tr><tr>
<td class="tcol1" style="vertical-align:top;"><p>Timetable:</p> </td><td class="tcol2"> <input class="ip2" name="timetable" type="text" size="60" value="<?php echo $timetable;?>"><br>

<p class="tan nomargin">Enter timetable as: AMV1O102 CGC1P102 ENG1P105 FSF1P102 SNC1P102<br>
<span class="smaller">It will be made all caps, and - will be stripped out, so "snc1d1-06" will also work, but courses MUST be separated by 1 space.</span></p>
</td>
</tr>
</table>
</div>
<br clear="both">
<div class="fleft">
<button type="submit" name="submit" class="pure-button" style="margin:0 0.75em;font-weight:bold;">Submit</button>
</div>
<br clear="both">
</form>
</div>
</body>
</html>

