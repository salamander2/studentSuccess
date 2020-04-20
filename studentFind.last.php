<?php
/*******************************************************************************
  Name: studentFind.php
  Called from: home.php
  Purpose: This file holds the function for finding students and diplaying them as a table
  Tables used: schoolDB/students, sssDB/sssInfo
  Transfers control to: commentPage.php or studentInfo.php (for non TEAM members)
 ******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB_last", $sql_user, $sql_pass);
$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

if (1 === $isTeam) {
	$nextPage = "commentPage.last.php";
} else {
	//$nextPage = "studentInfo.php";
    die("ERROR: you are trying to look at last year's student data without appropriate permissions.<br>From 'studentFind.last.php'");
}

/*************************
$isTeam means that the user is a member of the at-risk team
$activate means that this search function was called by pressing the button "list all at-risk students"
***************************/

// get the q parameter from URL
$q = clean_input($_REQUEST["q"]);
$activate = false;
if ($q == "ACTIVATED") $activate = true;

/* get colour scheme from radio buttons
   0 = none
   1 = by issue
   2 = by date
 */

/* This can't come from inside this form. It has to come from a $session variable that is set by the radio buttons 
   But they can't do that either since it's no longer php at that point. 
   So ... it will have to call home.php which then sets the $session variable */
$colour=0;
$colour = $_SESSION["colourScheme"];
if (null === $colour || empty($colour)) {
	$colour=0;
}
if ($colour == 99) $colour = 0;
//$colour = 1;	//for testing

// Get current month and year
//$today = date("Y-m-d H:i:s");
//$date = "2010-01-21 00:00:00";
// SQL field lastMtg is 2018-10-15	DATE - format YYYY-MM-DD

$year=date('Y');
$month=date('m');

//make strings for the previous 4 months ($m1, $m2, $m3, $m4)
//and store month names ($mn1, $mn2, $mn3, $mn4)
#$dateObj   = DateTime::createFromFormat('m', $month);
$dateObj   = DateTime::createFromFormat('Y-m-d',$year."-".$month."-01");
$m1 = $dateObj->format('Y-m-d'); 
$mn1 = $dateObj->format('F'); 


$month--;
if ($month < 1) {
	$month=12; $year--;
}
$dateObj   = DateTime::createFromFormat('Y-m-d',$year."-".$month."-01");
$m2 = $dateObj->format('Y-m-d'); 
$mn2 = $dateObj->format('F'); 

$month--;
if ($month < 1) {
	$month=12; $year--;
}
$dateObj   = DateTime::createFromFormat('Y-m-d',$year."-".$month."-01");
$m3 = $dateObj->format('Y-m-d'); 
$mn3 = $dateObj->format('F'); 

$month--;
if ($month < 1) {
	$month=12; $year--;
}
$dateObj   = DateTime::createFromFormat('Y-m-d',$year."-".$month."-01");
$m4 = $dateObj->format('Y-m-d'); 
$mn4 = $dateObj->format('F'); 

//DEBUG
//echo $m1." ".$m2." ".$m3." ".$m4."<br>";
//echo $mn1." ".$mn2." ".$mn3." ".$mn4;

/************* Begin selecting all students by name/who are at risk. Store results in $resultArray (a name that I don't use for query results *****************/
if ($activate) {
	//this query uses two databases. It assumes that the first database (default) is schoolDB.
	#$query = "SELECT students.studentID, students.firstname, students.lastname, sssInfo.selected, sssInfo.lastMtg FROM students INNER JOIN sssDB.sssInfo ON students.studentID=sssDB.sssInfo.studentID ORDER BY lastname, firstname";
    #The database is now all one -- for last year. The students table has been copied across.
	$query = "SELECT students.studentID, students.firstname, students.lastname, sssInfo.grade, sssInfo.selected, sssInfo.lastMtg FROM students INNER JOIN sssInfo ON students.studentID=sssInfo.studentID ORDER BY lastname, firstname";
	$resultArray = mysqli_query($sssDB, $query);
	if (!$resultArray) {
		die("Query to list students from table failed");
	}

} else {

#$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
	$q = $q.'%';
	$q2 = $q;
	$q3 = $q;
	$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE ? or lastname LIKE ? or studentID LIKE ? ORDER BY lastname, firstname";
	if ($stmt = $sssDB->prepare($query)) {
		$stmt->bind_param("sss", $q, $q2, $q3);
		$stmt->execute(); 
		$resultArray = $stmt->get_result();
		$stmt->close();                 
	} else {
		$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
		$message_ .= 'SQL2: ' . $query;
		die($message_); 
	}
}
/************* END selecting students into $resultArray **********/

/***  HTML STARTS being written HERE ***/
if ($activate && 1===$isTeam) {
	echo "<p class='white' style='text-align:center;'>Highlighted rows are students to be discussed at next month's TEAM meeting</p>";
}


//print legend for colour scheme 1: by issue
if ($activate && $colour == 1) {
	echo '<table class="simpletable" style="xbackground-color:#777;font-size:80%;">';
	echo '<tr><th colspan=4 class="white">Colour coding &mdash; by issue</th></tr>';
	echo '<tr>';
	echo '<td class="row0" style="color:#000;">black = not AtRisk</td>';
	echo '<td class="row1" style="color:#06D;">blue = AtRisk, no issues</td>';
	echo '<td class="row2" style="color:#080;">green = AtRisk, all issues closed</td>';
	echo '<td class="row3" style="color:#C21;">dark red = AtRisk, some open issues</td>';
	echo '</tr>';
	echo '</table>';
}

//print legend for colour scheme 2: by date
if ($activate && $colour == 2) {
	echo '<table class="simpletable" style="xbackground-color:#777;font-size:80%;">';
	echo '<tr><th colspan=4 class="white">Colour coding &mdash; by date discussed</th></tr>';
	echo '<tr>';
	echo '<td style="color:#4F7;">mint = '.$mn1.' (this month)</td>';
	echo '<td style="color:#09F;">blue = '.$mn2.'</td>';
	echo '<td style="color:#E0E;">purple = '.$mn3.'</td>';
	echo '<td style="color:#AAA;">black = '.$mn4.'</td>';
	echo '<td style="color:#D21;">red = never/5 mo.</td>';
	echo '</tr>';
	echo '</table>';
}

//only show legend for ACTIVATED (ie. list AtRisk)
if ($activate) {
	echo '<div style="float:right;margin-right:2em;font-size:90%;border:dotted 1px #555;border-radius:5px;padding:4px;">
		<form class="white" action="home.php" method="POST" id="colourScheme">
		<div style="text-align:left;color:white;">
		<p><u>Select colour scheme</u></br>
		';
	echo '<input type="radio" name="colourScheme" id="none" value="99" onclick="this.form.submit();return false;" '; //none=99 because null also changes to be zero.
	if ($colour==0) echo ' checked '; 
	echo ' />
	<label for="none">none</label><br>
	';
	echo '<input type="radio" name="colourScheme" id="issues" value="1" onclick="this.form.submit();return false;" '; //the form submits as GET.  Try and force to POST
	if ($colour==1) echo ' checked ';
	echo '/> 
	<label for="issues">by issues</label><br>
	';
	echo '<input type="radio" name="colourScheme" id="date" value="2" onclick="this.form.submit();return false;" ';
	if ($colour==2) echo ' checked ';
	echo ' />
	<label for="date">by date</label>
	</div>
	</form>
	</div>';
}


//general HTML now being written
echo '<table class="pure-table pure-table-bordered table-canvas" style="border:none;">';
echo '<thead>';
echo '<tr>';
echo '<th>Student Name</th>';
if($activate) echo '<th>Grade</th>';
echo '<th>Student Number</th>';

if ($activate && 1==$isTeamAdmin) {
	echo "<th>Select?</th>";
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number, selected (if isTeamAdmin)
while ($row = mysqli_fetch_assoc($resultArray)){ 

	$selected = false;
	if ($row['selected'] == 1) $selected=true;

	//1. Is the student "at risk" - ie. does he/she have an sssInfo record?
	//this always gives 1 row with either a 1 or 0 in it.
	#$sql = "SELECT EXISTS(SELECT 1 FROM sssInfo WHERE studentID='" . $row['studentID'] . "')";
	#$sql = "SELECT studentID FROM sssInfo WHERE studentID='" . $row['studentID'] . "'";

	$sql = "SELECT studentID FROM sssInfo WHERE studentID = ? ";
	if ($stmt = $sssDB->prepare($sql)) {
		$stmt->bind_param("i", $row['studentID']);
		$stmt->execute();
		$stmt->bind_result($result2);
		//$stmt->fetch(); //NONONO - not if you're storing the result
		$stmt->store_result();
		$num_rows = $stmt->num_rows;
		$stmt->close();
	} else {
		$message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
		$message_ .= 'SQL: ' . $sql;
		die($message_); 
	}

	if ($num_rows == 0) $status = 0; 
	else $status = 1;

	/* Setup a $status variable for colour coding.
	   0 = not at risk
	   1 = at risk, no issues
	   2 = at risk, all issues completed
	   3 = at risk, some open issues
	 */
	if ($colour == 1) {	//determine the status for the colours using the issues
		//2. If yes, then are all of the comments completed or not?
		if ($num_rows == 1) {
			$sql = "SELECT completed FROM comments WHERE studentID = ?";
			if ($stmt = $sssDB->prepare($sql)) {
				$stmt->bind_param("i", $row['studentID']);
				$stmt->execute();
				$stmt->bind_result($ans);
				//loop through all comments and see if any are not completed.
				$stmt->store_result();
				$nr = $stmt->num_rows;
				if ($nr == 0) {
					$status = 1;
				} else { 
					$completed = true;
					while ($stmt->fetch()) {
						if ($ans == 0) $completed = false;
					}
					if ($completed) $status = 2;
					else $status = 3;
				}
				$stmt->close();
			} else {
				$message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
				$message_ .= 'SQL: ' . $sql;
				die($message_); 
			}

		}
	}

	/* $status variable for colour coding based on lastMtg
	   0 = not at risk
	   5 = current month (e.g February)
	   6 = month-1 (January)
	   7 = month-2 (December)
	   8 = month-3 (November)
	   9 = never
	 */
	if ($colour ==2) {
		$lastMtg = $row['lastMtg']; 
		if ($lastMtg < $m4) $status = 9;
		else if ($lastMtg < $m3) $status = 8;
		else if ($lastMtg < $m2) $status = 7;
		else if ($lastMtg < $m1) $status = 6;
		else $status = 5;

	}

	if ($colour == 0 ) $status = 1;
	if ($selected) $status = $status * 10;	//to apply highlight to the row

#  <!-- select page based on "$nextPage"  -->
# should look like this: <tr onclick="window.document.location='commentPage.php?ID=339671216';" class="row0">
# old code: echo "<tr onclick=".'"'."window.document.location='commentPage.php?ID=".$row['studentID'] ."';".'" class="row0">';
#echo "<tr onclick=\"window.document.location='commentPage.php?ID=". $row['studentID'] . "';\" class=\"row$num_rows\">";
	if ($activate) {
		echo "<tr class=\"row$status\">";
	} else {
		echo "<tr>";
	}
	echo "<td onclick=\"window.document.location='$nextPage?ID=". $row['studentID'] . "';\" >".$row['lastname'], ", ", $row['firstname'] ."</td>";
    if($activate) {
		echo "<td onclick=\"window.document.location='$nextPage?ID=". $row['studentID'] . "';\" >".$row['grade']. "</td>";
	}
	echo "<td onclick=\"window.document.location='$nextPage?ID=". $row['studentID'] . "';\" >".$row['studentID']. "</td>";
	if ($activate) {
		if (1==$isTeamAdmin) {
			//			echo '<td onclick="toggleSelect('.$row['studentID'].','.$selected.')" >';
			echo '<td>';
			echo '<input onclick="toggleSelect('.$row['studentID'].','.$selected.')" type="checkbox" id="fluency"';
			if ($selected) echo " checked ";
			echo '>';
			echo '</td>';
		}
	}
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

// mysqli_free_result($resultArray);
?>

