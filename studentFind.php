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

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);
$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

if (1 === $isTeam) {
	$nextPage = "commentPage.php";
} else {
	$nextPage = "studentInfo.php";
}

// get the q parameter from URL
$q = clean_input($_REQUEST["q"]);

$activate = false;
if ($q == "ACTIVATED") $activate = true;

if ($activate) {
	//this query uses two databases. It assumes that the first database (default) is schoolDB.
	$query = "SELECT students.studentID, students.firstname, students.lastname, sssInfo.selected FROM students INNER JOIN sssDB.sssInfo ON students.studentID=sssDB.sssInfo.studentID ORDER BY lastname, firstname";
	$result = mysqli_query($schoolDB, $query);
	if (!$result) {
		die("Query to list students from table failed");
	}

} else {

#$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
	$q = $q.'%';
	$q2 = $q;
	$q3 = $q;
	$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE ? or lastname LIKE ? or studentID LIKE ? ORDER BY lastname, firstname";
	if ($stmt = $schoolDB->prepare($query)) {
		$stmt->bind_param("sss", $q, $q2, $q3);
		$stmt->execute(); 
		$result = $stmt->get_result();
		$stmt->close();                 
	} else {
		$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
		$message_ .= 'SQL2: ' . $query;
		die($message_); 
	}
}
?>

<?php
if ($activate && 1===$isTeam) {
	echo "<p class='white centered'>Highlighted rows are students to be discussed at next month's TEAM meeting</p>";
}
?>

<table class="pure-table pure-table-bordered table-canvas" style="border:none;">
<thead>
<tr>
<th>Student Name</th>
<th>Student Number</th>
<?php
if ($activate && 1==$isTeamAdmin) {
	echo "<th>Select?</th>";
}
?>
</tr>
</thead>
<tbody>

<?php
// printing table rows
// $row = mysql_fetch_row($result);
while ($row = mysqli_fetch_assoc($result)){ 

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

	/* Setup a status variable for colour coding.
	   0 = not at risk
	   1 = at risk, no issues
	   2 = at risk, all issues completed
	   3 = at risk, some open issues
	 */
	if ($num_rows == 0) $status = 0; 
	else $status = 1;

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
		if ($selected) $status = $status * 10;
	}
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
?>

</tbody>
</table>

<?php
// mysqli_free_result($result);
?>

