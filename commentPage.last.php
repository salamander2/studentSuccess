<?php 
//This is the page where the Counsellors can view their created comments
/*******************************************************************************
   Name: commentPage.php
   Called from: home.php / studentFind.php
   Purpose: 
   Tables used: schoolDB/students, sssDB/sssInfo, sssDB/social_workers
	sssDB/comment, ssssDB/next_steps
   Calls: sssDataHandler.php (when activating a student)
	nextSteps.php (when a 'next step' is added)
	completed.php (when a comment is completed)
   Transfers control to: home.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

#$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);
$sssDB = connectToDB("sssDB_last", $sql_user, $sql_pass);

$studentID = $_GET['ID'];
$_SESSION["studentID"] = $studentID;

//$error_message="";
// if (empty($lastname))  $error_message = "You must enter a lastname";
// if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";

$sql = "SELECT firstname, lastname, studentID, gender, dob, guardianPhone, guardianEmail FROM students WHERE studentID = ?";
  if ($stmt = $sssDB->prepare($sql)) {
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $studentID, $gender, $dob, $guardianPhone, $guardianEmail);
    $stmt->fetch();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

$guardianPhone = clean_input($guardianPhone);
$guardianEmail = clean_input($guardianEmail);

//get timetable
/*
$sql = "SELECT courses.coursecode, teacher, period, room FROM courses INNER JOIN student_course ON courses.coursecode = student_course.coursecode WHERE studentID = ? ORDER BY period";
if ($stmt = $sssDB->prepare($sql)) {
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $timetable = $stmt->get_result();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}
*/
$timetable="";

// get info from sssInfo table (for current student)
$sql = "SELECT * FROM sssInfo WHERE studentID='" . $studentID. "'";
$result2 = mysqli_query($sssDB,$sql);
if (!$result2) {
    die("Query to show fields from sssInfo table failed");
}
$num_rows = mysqli_num_rows($result2);
$sssInfoFound = ($num_rows > 0);

if ($sssInfoFound) {
    while ($row = mysqli_fetch_assoc($result2)){
        $iep = $row['IEP'];
        $fnmi = $row['FNMI'];
        $grade = $row['grade'];
        $swID = $row['swID'];
        $staff = $row['staff'];
		$lastMtg = $row['lastMtg'];
    }
}

// get the social workers names stored as options. And, if $swID != "", then make that one "selected"
$sql = "SELECT id,sw FROM social_workers ORDER BY sw";
$sw_name_opt="";
$result3 = mysqli_query($sssDB, $sql);
if (!$result3) {
    die("Query to show fields from social_workers table failed");
}

while ($row=mysqli_fetch_assoc($result3)) 
{
	$sel="";
	if ($swID == $row["id"]) { 
	    $sel="selected ";
	}
	$sw_name_opt .= '<option value="'.$row["id"].'" '.$sel.'>'.$row["sw"].'</option>';
}

function getAge($then) {
    $then = date('Ymd', strtotime($then));
    $diff = date('Ymd') - $then;
    $age = substr($diff,0,-4);
    //try to get decimal years!
    //$age= sprintf("%u.%u",substr($diff, 0, 2),substr($diff,2,2));
    return $age;
}

function formatCourse($course) {
   if (strlen($course) != 8) return $course;

   $temp = substr($course,0,6) . "-" . substr($course,6);
   return $temp;
}

?>

<!DOCTYPE html>
<html>

<head lang="en">
<meta charset="UTF-8">
<!-- for mobile devices
    <meta name="viewport" content="width=device-width, initial-scale=1">
-->
<title>
   <?php echo "Student Success Database -- ", $lastname, ", " , $firstname;?>
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<?php
if (false === $sssInfoFound) {
    echo "<style>#main-lower{display:none;}#ssData{border-color:red;display:none;}.pure-button{color:red;}</style>";
} else {
    if (0 === $isTeam) {
       echo "<style>#main-lower{display:none;}</style>";
    }
}
?>
</head>

<body id="last">
<!--  ################ Structure of page #################
* header
  hr
* main

  * main-top
    photo
    * box2
      *mkbk
      *ssData

  * main-lower
    * newcomment (form, submit btn)
    * repeating comments
      * boxrepeat (this part repeats for multiple comments)
        table (pure-table)
        Row1: topcell (textarea) | rightcell (datestamp, username)
        Row2: bottomcell (colspan2)  
              table
              Row1: leftcell2 (filled in nextsteps)  | rightcell2 (datestamp,user)
              Row2... repeat above for multiple nextsteps
              LastRow: nextsteps (form, update btn) | noborder (completed btn)

  * footer
-->

<!-- standard header-->
<div id="header">
    <a class="fa fa-arrow-left nav-button fleft" href="home.last.php" title="or press browser Back button">  Go Back</a>
    <h1>Student Success Database &mdash; LAST YEAR'S COMMENTS</h1>
    <?php printHeader($fullname, $alpha, $isTeam); ?>
    <hr color="black">        
</div>
<!-- end of header -->

<div id="error_message"></div>

<div id="main">
<div id="main-top">
    <h1 class="centered"><?php echo $lastname, ", ", $firstname; ?></h1>

<!-- **************** Begin insert photo ***************** -->
    <?php
	# We have to look for both JPG and BMP; for some reason the school has switched back to BMP. I don't know which is more recent. 
	# Linux command to uppercase all files in a folder::>>>> for file in *; do mv -- "$file" "${file^^}"; done
	//1. check for JPG
    $filename1 = "$photoDir1/$studentID.JPG"; //absolute path
    $filename2 = "$photoDir2/$studentID.JPG"; //relative path for public_html (browsers)
    if (file_exists($filename1)) {
       echo "<img class=\"student-img\" src=$filename2>"; //echo "The file $filename exists";
    } else {
		//2. check for BMP
		$filename1 = "$photoDir1/$studentID.BMP";
		$filename2 = "$photoDir2/$studentID.BMP";
		if (file_exists($filename1)) {
		   echo "<img class=\"student-img\" src=$filename2>";
		} else {
		   echo "<img class=\"student-img\" src=\"$photoDir2/USER_BLANK.PNG\">";
		}
    }
    ?>
<!-- end insert photo -->

<!-- **************** Begin markbook section [left box in main-top section] ***************** -->
<div class="box2_last">       
<div id="mkbk">
<p style="border:1px solid gray;padding:2px 4px;">
    Student Number: <span class="white"><?php echo $studentID; ?></span>
    <span class="fright">Gender: <span class="white"><?php echo $gender; ?></span></span><br>
    Birthdate: <span class="white"><?php echo $dob; ?></span>
    <span class="fright">Age: <span class="white"><?php echo getAge($dob); ?></span></span>
</p>
</div> <!-- end mkbk section -->

<!-- **************** Begin ssData section [right box in main-top section] ***************** -->
<script>
function validateData() {
    var x, text;

    // Get the value of the input field with id="grade"
    x = document.getElementById("grade").value;
    // If x is Not a Number or less than one or greater than 10
    if (isNaN(x) || x < 9 || x > 13) {
        text = "Grade must be between 9 and 13";
        text = "<div class=\"error\">" + text + "</div>";
        document.getElementById("error_message").innerHTML = text;
        return false;
    } 
    return true;
}
</script>

<div id="ssData">
<p>
<span class="fleft">
<?php 
    echo "IEP: ";
if ($iep) {
   echo "<input type=\"radio\" name=\"iep\" value=\"1\" checked />Yes <input type=\"radio\" name=\"iep\" value=\"0\" />No";
} else {
   echo "<input type=\"radio\" name=\"iep\" value=\"1\" />Yes <input type=\"radio\" name=\"iep\" value=\"0\" checked />No";
}
?>
</span>
<span class="fright">
<?php
echo "FNMI: ";
 
if ($fnmi) {
   echo "<input type=\"radio\" name=\"fnmi\" value=\"1\" checked />Yes <input type=\"radio\" name=\"fnmi\" value=\"0\" />No";
} else {
   echo "<input type=\"radio\" name=\"fnmi\" value=\"1\" />Yes <input type=\"radio\" name=\"fnmi\" value=\"0\" checked />No";
}
?>
</span>
</p>
<br clear="both">
<p>
<span class="fleft">
    Grade: <input style="text-align:center;" id="grade" type="text" size="2" name="grade" value="<?php echo $grade; ?>">
</span>
<span class="fright">
    Social Worker: 
<select name="swid">
   <?php echo $sw_name_opt; ?>
</select>
</span>
</p>
</div><!-- ************ end ssData **************** -->
<p class="fontONE smaller fleft">Guardian Phone: <?php echo $guardianPhone; ?><br>
Guardian Email: <?php echo str_replace(';','; ',$guardianEmail); ?></p>

</div><!-- ************ end of box2 *************** -->

</div><!-- ************ end of main-top *********** -->

<br clear="both" />

<!-- ********** start maim-lower section [continues til footer] ******************* -->
<div id="main-lower">
<div id="repeatingComments">
    <h3 class="white centered"><span class="fa fa-chevron-down"></span>  Previous Comments / Issues  <span class="fa fa-chevron-down"></span></h3>

    <?php echo $message; ?>

    <?php 
    // sending query: get all comments for this student.
    //$sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'),timestamp, login_name, AES_DECRYPT(next_steps,'$masterkeyhash'), id, completed FROM comments WHERE student_number=$student_number ORDER BY timestamp DESC";
    //$sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'),timestamp, login_name, id, completed FROM comments WHERE studentID=$studentID ORDER BY timestamp DESC";

    $sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'),timestamp, login_name, id, completed FROM comments WHERE studentID=? ORDER BY timestamp DESC";
    if ($stmt = $sssDB->prepare($sql)) {
       $stmt->bind_param("i", $studentID);
       $stmt->execute();
       $result = $stmt->get_result();
       $stmt->close();
    } else {
       $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
       $message_ .= 'SQL: ' . $sql;
       die($message_); 
    }
    $row_cnt = mysqli_num_rows($result);
    if ($row_cnt == 0) {
      echo "You have not entered any comments for this student yet.";
    }
    ?>
<!-- starting displaying the comments and next steps for this student -->    

<script>
function validateNextSteps() {
// do not allow submussion if nextSteps field is empty. TODO : why is this here?
}
</script>

<?php
    //table rows [0] is decrypted comment. [1] is timestamp.  [2] is loginname, [3] is comment id, [4] completed

while ($row = mysqli_fetch_row($result)){ 
   $completed = false;
   if ($row[4] == 1) $completed = true;

   //now select the next steps for each comment 
   //$sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'), timestamp, login_name FROM next_steps WHERE commentID='$row[3]' ORDER BY timestamp ASC";
   $sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'), timestamp, login_name FROM next_steps WHERE commentID=? ORDER BY timestamp ASC";
   // for NS table [0] is decrypted notes, [1] is timestamp. [2] is login_name
    if ($stmt = $sssDB->prepare($sql)) {
       $stmt->bind_param("i", $row[3]);
       $stmt->execute();
       $resultNS = $stmt->get_result();
       $stmt->close();
    } else {
       $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
       $message_ .= 'SQL: ' . $sql;
       die($message_);
    }
   $row_cntNS = mysqli_num_rows($resultNS);

?>

<div class="box-repeat">
   <table width="100%" class="pure-table">
   <tr>
   <?php 
	if ($completed) {
	 echo  "<td align=\"left\" class=\"topcell completed\">";
	} else {
   	 echo "<td align=\"left\" class=\"topcell\">";
	}
   ?>
<textarea readonly rows="5" cols="50" class="prevComment fontONE"><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></textarea>
   </td>

   <td valign="center" class="rightcell"><?php echo $row[1],"<br>",$row[2]; ?></td>
   </tr>
   <tr>
   <!-- change colour of completed comments -->
   <?php 
	if ($completed) {
	 echo  "<td colspan=2 align=\"left\" class=\"bottomcell completed\">";
	} else {
   	 echo "<td colspan=2 align=\"left\" class=\"bottomcell\">";
	}
   ?>
<!-- TODO: also don't let next_steps be updated if it is completed. prevent the text area from being edited and make the update button non-working -->

<!-- in the bottomcell we are putting a table of all of the next steps -->
  <table width="100%" class="pure-table">

 <?php
  while($rowNS = mysqli_fetch_row($resultNS)) {
  ?>
  <tr>
  <td class="leftcell2">
     <textarea readonly rows="5" cols="50" class="comment-text fontONE"><?php echo htmlspecialchars($rowNS[0], ENT_QUOTES, 'UTF-8'); ?></textarea>
  </td>
<td valign="center" class="rightcell2">
<?php echo $rowNS[1],"<br>",$rowNS[2]; ?>
</td>
  </tr>
  <?php
  }
  ?>

  <!-- add a blank form to add next steps -->
  <!-- TODO: do not allow submission if the nextSteps field is empty. USe JS to check. This is already done in nextSteps.php -->
  <?php
  //if ($completed) {
    echo "<tr style=\"display:none;\">";
  //} else { 
  //  echo "<tr>";
  //}
  ?>

  <td>
  <form action="nextSteps.php" method="POST">
  <table width=100%>
  <tr>
     <td>
         <textarea rows="2" cols="50" name="nextSteps" class="comment-text2 fontONE" placeholder="Next steps ..."></textarea>
     </td>
     <td class="noborder">
         <input type="hidden" name="commentID" value="<?php echo $row[3] ?>">
         <button type="submit" name="submit" class="pure-button rightcell3">Update</button>
     </td>
   </tr>
   </table>
   </form> 
   </td>
   <td class="noborder">
      <a href="completed.php?ID=<?php echo $row[3]; ?>"><button type="submit" name="submit2" class="pure-button rightcell3">Completed</button></a>
   </td></tr>
  <!-- end of table for next steps -->
  </table> 

   </td> 
   </tr>
</table>
</div> <!-- end box-repeat -->

<!-- and here ends the } that started the box-repeat php section for each new comment. -->
<?php
}
?>
<!-- ...  -->

<p>&nbsp;</p>
<hr>
</div> <!-- end main-lower-->

<div id="footer" class="centered"> Created by Michael Harwood &copy; 2017.  </div>

</div> <!-- end main -->
<?php
#echo var_dump($result);
//echo $timetable->num_rows;
echo $filename;
?>
</body>
</html>
