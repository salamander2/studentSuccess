<?php
/*******************************************************************************
   Name: studentFind.php
   Called from: home.php
   Purpose: This file holds the function for finding students and diplaying them as a table
   Tables used: schoolDB/students, sssDB/sssInfo
   Transfers control to: commentPage.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.php');
require_once('sssdb.php');

$schoolDB = connectToDB("schoolDB",$username,$password);
$sssDB = connectToDB("sssDB",$username,$password);

if (1 === $isTeam) {
  $nextPage = "commentPage.php";
} else {
  $nextPage = "studentInfo.php";
}

// get the q parameter from URL
$q = clean_input($_REQUEST["q"]);

if ($q == "ACTIVATED") {
   //this query uses two databases. It assumes that the first database (default) is schoolDB.
   $query = "SELECT students.studentID, students.firstname, students.lastname FROM students INNER JOIN sssDB.sssInfo ON students.studentID=sssDB.sssInfo.studentID ORDER BY lastname, firstname";
} else {
   $query = "SELECT * FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
//   $query = "SELECT * FROM students ORDER BY lastname, firstname";
}

$result = mysqli_query($schoolDB, $query);
if (!$result) {
	die("Query to list students from table failed");
}
?>

<table class="pure-table pure-table-bordered table-canvas">
<thead>
<tr>
<th>Student Name</th>
<th>Student Number</th>
</tr>
</thead>
<tbody>

<?php
// printing table rows
// $row = mysql_fetch_row($result);
while ($row = mysqli_fetch_assoc($result)){ 
   //try to get the corresponding sssInfo data

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
//TODO: add in something that colours the row purple if all of the comments are completed.
   if ($num_rows == 1) {
      $sql = "SELECT completed FROM comments WHERE studentID = ?";
      if ($stmt = $sssDB->prepare($sql)) {
         $stmt->bind_param("i", $row['studentID']);
         $stmt->execute();
         $stmt->bind_result($ans);
         $comp = true;
         while ($stmt->fetch()) {
	    if ($ans == 0) $comp = false;
         }
         $stmt->close();
      } else {
         $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
         $message_ .= 'SQL: ' . $sql;
         die($message_); 
      }
      if ($comp) $num_rows = 2;
   }

   ?> 

   <!-- //TODO: select page based on "$nextPage"  -->

   <!-- set colour based on $num_rows -->
   <?php #echo "<tr onclick=\"window.document.location='" .$nextPage. "?ID=$row['studentID']';\""  ;
         #echo " class=\"row$num_rows\"";
   ?>
   <tr onclick="window.document.location='commentPage.php?ID=<?php echo $row['studentID']; ?>';" class="row<?php echo $num_rows?>">
   <td><?php echo $row['lastname'], ", ", $row['firstname']; ?></td>
   <td><?php echo $row['studentID']; ?></td>
   </tr>
<?php
} //this is the end of the while loop
?>
</tbody>
</table>
<?php
// mysqli_free_result($result);
?>
