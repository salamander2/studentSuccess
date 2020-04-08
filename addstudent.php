<?php
/*******************************************************************************
   Name: addStudent
   Called from: commentPage
   Purpose: add a student to the schoolDB database
   Tables used: schoolDB/students
   Calls: 
   Transfers control to: logout.php, admin.php, addstudent.php 
******************************************************************************/ 
error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$error_message="";
$firstname = $lastname = $studentNum = $gender = $dob = "";
//if the submit button has been pressed:
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

	//student number. Don't use the same variable name as in db.php ($student_number)
	$studentNum = clean_input($_POST['studentNum']);
	//check if number already exists. If it does the query will die with an error.
	if (!empty($studentNum)) {
		if (isDuplicate_studentNum($studentNum, $schoolDB)) $error_message = "This student number already exists!";
	}

	//if ($studentNum[0] <> "3") $error_message = "Student number must begin with a '3'";
	if (strlen($studentNum) <> 9 ) $error_message = "Student numbers must be 9 digits";
	if (!is_digit($studentNum)) $error_message = "Non-numeric data in student number: $studentNum";
	if (empty($studentNum))  $error_message = "Please enter a student number";

	$firstname = clean_input($_POST["firstname"]);
	if (empty($firstname ))  $error_message = "You must enter a firstname";

	$lastname = clean_input($_POST['lastname']);
	if (empty($lastname))  $error_message = "You must enter a lastname";

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
//		$sql = "INSERT INTO students (student_number, firstname, lastname,  gender, dob) VALUES ('$studentNum', '$firstname', '$lastname', '$gender', '$dob')";
		$sql = "INSERT INTO students (studentID, firstname, lastname,  gender, dob) VALUES (?, ?, ?, ?, ?)";
		if ($stmt = $schoolDB->prepare($sql)) {
		   $stmt->bind_param("issss", $studentNum, $firstname, $lastname, $gender, $dob);
		   $stmt->execute();
//echo var_dump($stmt);
		   $stmt->close();
		} else {
	 	   $message  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n";
		   $message .= 'SQL: ' . $sql;
		   die($message);
		} 

		$firstname = $lastname = $studentNum = $gender = $dob = "";
		$error_message = "<div class=\"error green\">" . "Student successfully added." . "</div>";
		//header("Location: home.php");
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
<!--This is the page used to search for students -->
<html>
<head>
<title>Student Comment Database: Add new student</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<!-- for mobile devices
<meta name="viewport" content="width=device-width, initial-scale=1">
-->
</head>

<body>
<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
<h3>&nbsp;</h3>
<?php printHeader($fullname, $alpha, $isTeam); ?>
<hr color="black">
</div>

<h2 class="white">Add a new student</h2>
<!-- <form class="pure-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"> -->
<form class="pure-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<fieldset>
<legend>

<?php echo $error_message; ?>

<table>
<tr>
<td class="tcol1">
<p>Student Name:</p>
</td><td class="tcol2">
<input name="lastname" type="text" placeholder="Last name" value="<?php echo $lastname;?>">
<input name="firstname" type="text" placeholder="First name" value="<?php echo $firstname;?>">
</td>
</tr><tr>
<td class="tcol1">
<p>Student Number:</p>
</td><td class="tcol2">
<input name="studentNum" type="text" size="9" maxlength="9" value="<?php echo $studentNum;?>"><br>
</td>
</tr><tr>
<td class="tcol1">
<p>Date of Birth:</p>
</td><td class="tcol2">
<input name="dob" type="text" placeholder="YYYY-MM-DD" value="<?php echo $dob;?>"><br>
</td>
</tr><tr>
<td class="tcol1">
<p>Gender:</p>
</td><td class="tcol2">
<input name="gender" type="text" size="1" maxlength="1" ><br>
</td>
</tr>
</table>
</legend>
<button type="submit" name="submit" class="pure-button fleft" style="margin:0 0.75em;font-weight:bold;">Submit</button>
</fieldset>
</form>
<h2 class="tan">Warning: this does not add a timetable for the student<br>&nbsp;</h2>
</div>
</body>
</html>

