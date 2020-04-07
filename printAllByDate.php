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
   
#$sql="SELECT tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM tcontact ORDER BY tcontact.teacher;";// tcontact.timestamp;";
//INNER JOIN
//$sql="SELECT schoolDB.students.studentID, schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM tcontact INNER JOIN schoolDB.students ON tcontact.studentID=schoolDB.students.studentID ORDER BY schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.timestamp;";
//LEFT JOIN .... all students --> contact + no contact
$sql="SELECT tcontact.timestamp, schoolDB.students.studentID, schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID ORDER BY tcontact.timestamp DESC, schoolDB.students.lastname, schoolDB.students.firstname ;";
// sending query
$result = mysqli_query($sssDB, $sql);
if (!$result) {
    die("Query to retrieve and print all data failed");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Contact Database: <?php echo $username; ?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
</head>

<body>
<div id="header">
<!-- <a class="fa fa-plus-circle nav-button fleft" href="addstudent.php">  Add Student</a> -->
<a class="fa fa-sign-out nav-button fleft" href="reports.php">  Go Back</a>
<h1>Student Contact Database: Listing of all records.</h1>
<h2>Sorted by Timestamp Entered into Database</h2>
</div>

<div id="main">
<?php
$fields_num = mysqli_num_fields($result);

echo "<table border='1' cellspacing='0' cellpadding='3'><tr>";
// printing table headers
for($i=0; $i<$fields_num; $i++)
{
    $field = mysqli_fetch_field($result);
    if ($field->name == "personContacted") {
		echo "<th>Person Contacted</th>";
	} else {
		if ($field->name == "contactMethod") {
			echo "<th>Contact Method</th>";
		} else {
		    echo "<th>{$field->name}</th>";
		}
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
        echo "<td>$cell</td>";

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

