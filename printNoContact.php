<?php
/*******************************************************************************
   Name: printAll.php
   Called from: 
   Purpose: 
   Tables used: waitlistDB/courses
   Calls: 
   Transfers control to: 
******************************************************************************/

error_reporting(E_ALL);
// Start the session
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);
$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);
   
$sql="SELECT tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM tcontact ORDER BY tcontact.teacher;";// tcontact.timestamp;";
//INNER JOIN
//$sql="SELECT schoolDB.students.studentID, schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM tcontact INNER JOIN schoolDB.students ON tcontact.studentID=schoolDB.students.studentID ORDER BY schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.timestamp;";
//LEFT JOIN .... all students --> contact + no contact
#Working. $sql="SELECT schoolDB.students.studentID, schoolDB.students.lastname, schoolDB.students.firstname, schoolDB.students.timetable FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID WHERE tcontact.studentID IS NULL ORDER BY schoolDB.students.lastname, schoolDB.students.firstname;";
$sql="SELECT schoolDB.students.studentID, CONCAT_WS(', ', schoolDB.students.lastname, schoolDB.students.firstname) AS studentName, schoolDB.students.timetable FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID WHERE tcontact.studentID IS NULL ORDER BY schoolDB.students.lastname, schoolDB.students.firstname;";
// sending query
$result = mysqli_query($sssDB, $sql);
if (!$result) {
    die("Query to retrieve and print all data failed");
}

$sql="SELECT students.studentID FROM students WHERE students.timetable='' ;";
$result2 = mysqli_query($schoolDB, $sql);
if (!$result2) {
    die("Query to count number of students with no timetable failed");
}
$count = mysqli_num_rows($result2);
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Contact Database: <?php echo $username; ?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<style>
.border{
  border:1px solid gray;
}
</style>
</head>

<body>
<div id="header">
<!-- <a class="fa fa-plus-circle nav-button fleft" href="addstudent.php">  Add Student</a> -->
<a class="fa fa-sign-out nav-button fleft" href="reports.php">  Go Back</a>
<h1>Student Contact Database: Listing of students with no contact.</h1>
<h2>Sorted by Student Name</h2>
</div>

<div id="main">
<?php
$numRows = mysqli_num_rows($result);
$fields_num = mysqli_num_fields($result);

echo '<table cellpadding=5><tr><td class="border">';
echo "<p>$numRows students have NOT been contacted so far.</p>";
echo "<p>$count students have no timetable, but may have been contacted in which case they won't show up here.</p>";
//echo "<hr>";
//echo "<p><b>Note:</b> the timetable data here is what shows up as 'Raw timetable data' on the student contact page.<br> It is the data from Markbook.<br> The timetables in the box (with teachers and periods) on the main student contact pages is created<br> by trying to match Markbook data with teacher schedules. It fails notably for co-op and alt courses.</p>";
//echo "<p>Reasons for no timetable: <br>&bull; left Beal after semester 1, <br>&bull; ? </p>";
//echo '</td></tr></table>';
echo "<p></p>";

echo "<table class='border' cellspacing='0' cellpadding='3'><tr>";

// printing table headers
for($i=0; $i<$fields_num; $i++)
{
    $field = mysqli_fetch_field($result);
	if ($field->name == "timetable") {
		echo "<th class='border'>Timetable (not necessarily ordered by period)</th>";
	} else {
		echo "<th class='border'>{$field->name}</th>";
	}
}
echo "</tr>\n";
// printing table rows
$prev="";
while($row = mysqli_fetch_row($result))
{
    $current=$row[0];
    echo "<tr>";

    // $row is array... foreach( .. ) puts every element
    // of $row to $cell variable
    foreach($row as $cell)
        echo "<td class='border'>$cell</td>";

    echo "</tr>\n";
/*    if($current==$prev) {
       echo "<tr><td colspan=".$fields_num.">&nbsp;</td></tr>";
    }
*/
    $prev = $current;
}
mysqli_free_result($result);
?>
</table>
</div>

</body>
</html>

