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
### WORKING $sql="SELECT schoolDB.students.studentID, schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID ORDER BY schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.timestamp;";
#working version
$sql="SELECT schoolDB.students.studentID, CONCAT_WS(', ', schoolDB.students.lastname, schoolDB.students.firstname) AS studentName, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes, tcontact.timestamp FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID ORDER BY schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.timestamp;";
#for testing with only one record
#$sql="SELECT schoolDB.students.studentID, CONCAT_WS(', ', schoolDB.students.lastname, schoolDB.students.firstname) AS studentName, tcontact.teacher, tcontact.contactMethod, tcontact.personContacted, tcontact.date, tcontact.notes, tcontact.timestamp FROM schoolDB.students LEFT JOIN tcontact ON tcontact.studentID=schoolDB.students.studentID WHERE schoolDB.students.studentID='333444555' ORDER BY schoolDB.students.lastname, schoolDB.students.firstname, tcontact.teacher, tcontact.timestamp;";
// sending query
$result = mysqli_query($sssDB, $sql);
if (!$result) {
    die("Query to retrieve data for CSV export failed");
}

function cleanData($str)
  {
    // escape tab characters
    $str = preg_replace("/\t/", "\\t", $str);

    // escape new lines
    $str = preg_replace("/\r?\n/", "\\n", $str);

    // convert 't' and 'f' to boolean values
    if($str == 't') $str = 'TRUE';
    if($str == 'f') $str = 'FALSE';

    // force certain number/date formats to be imported as strings
    if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
    }

    // escape fields that include double quotes
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	return $str;
  }

######### MAIN PROGRAM STARTS HERE #################
download_send_headers("ContactData.csv");

$fields_num = mysqli_num_fields($result);

// printing table headers
$headers=array();
for($i=0; $i<$fields_num; $i++)
{
    $field = mysqli_fetch_field($result);
    if ($field->name == "personContacted") {
		$headers[]= "Person Contacted";
	} else {
		if ($field->name == "contactMethod") {
			$headers[]="Contact Method";
		} else {
		    $headers[]=$field->name;
		}
	}
}
$out = fopen("php://output", 'w');
fputcsv($out, $headers);
#echo $headers;

#echo "<table border='1' cellspacing='0' cellpadding='3'><tr>";
#echo "</tr>\n";
// printing table rows
$firstLine=true;
while($row = mysqli_fetch_row($result))
{
	//clean input. HTML tags, single and double quotes are already clean when the data is stored.
    //just replacing CRLF with spaces
    $row = preg_replace("/\r?\n/", " ", $row);
	fputcsv($out, $row);
#    echo "<tr>";
    // $row is array... foreach( .. ) puts every element
    // of $row to $cell variable
//    foreach($row as $cell)
//$cell = cleanData($cell);
//        echo $cell."<br>";

#    echo "</tr>\n";
}
mysqli_free_result($result);


function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
#    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

	//for viewing in webpage
#   header("Content-Type: text/plain");

    // force download  
    header("Content-Type: application/force-download");
#    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
	header("Content-Type: text/csv");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
#    header("Content-Transfer-Encoding: binary");

}

?>



