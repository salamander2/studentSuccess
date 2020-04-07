<?php 
//This is the page where the Counsellors can view their created comments
/*******************************************************************************
   Name: showContacts.php
   Called from: home.php / studentFind.php
   Purpose: 
   Tables used: schoolDB/students, sssDB/sssInfo, sssDB/social_workers
	   sssDB/comment, ssssDB/next_steps, sssDB/tcontact
   Calls: sssDataHandler.php (when activating a student)
	nextSteps.php (when a 'next step' is added)
	completed.php (when a comment is completed)
   Transfers control to: home.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);
$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);

$studentID = $_GET['ID'];
$_SESSION["studentID"] = $studentID;

//$error_message="";
// if (empty($lastname))  $error_message = "You must enter a lastname";
// if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";

$sql = "SELECT firstname, lastname, studentID, gender, dob, guardianPhone, guardianEmail, loginID FROM students WHERE studentID = ?";
  if ($stmt = $schoolDB->prepare($sql)) {
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $studentID, $gender, $dob, $guardianPhone, $guardianEmail, $loginID);
    $stmt->fetch();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

$guardianPhone = clean_input($guardianPhone);
$guardianEmail = clean_input($guardianEmail);

//get timetable
$sql = "SELECT courses.coursecode, teacher, period, room FROM courses INNER JOIN student_course ON courses.coursecode = student_course.coursecode WHERE studentID = ? ORDER BY period";
if ($stmt = $schoolDB->prepare($sql)) {
  /* bind parameters for markers */
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    /* save output into array of rows in $timetable */
    $timetable = $stmt->get_result();
    $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_); 
}

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
<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
<title>
   <?php echo "Student Success Database -- ", $lastname, ", " , $firstname;?>
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">

<script>
function addText(text) {
  oldtext = document.getElementById("contactMethod").value;
  if (oldtext.trim().length > 0) {
      text = oldtext + ", " + text;
  } 
  document.getElementById("contactMethod").value = text;
  document.getElementById("contactMethod").focus;
}
</script>

</head>

<body>
<script>
// for at-risk data form
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
// for teacher contact form 
function validateData2() {
   data = document.getElementById("teacherName").value;
   if (data.trim().length == 0) {
      alert("You must enter your name in the teacher field");
      return false;
   }
   data = document.getElementById("contactMethod").value;
   if (data.trim().length == 0) {
      alert("You must enter something in Contact Method");
      return false;
   }
   data = document.getElementById("personContacted").value;
   if (data.trim().length == 0) {
      alert("You must enter the person contacted (student, parent, ...)");
      return false;
   }
   data = document.getElementById("dateContacted").value;
   if (data.trim().length == 0) {
      alert("You must enter the date contacted");
      return false;
   }
   return true;
}

</script>

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
    <a class="fa fa-arrow-left nav-button fleft" href="home.php" title="or press browser Back button">  Go Back</a>
    <h1>Beal Student Database</h1>
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
<div class="box2">       
<div id="mkbk">
<p style="border:1px solid gray;padding:2px 4px;">
    Student Number: <span class="white"><?php echo $studentID; ?></span>
    <span class="fright">Gender: <span class="white"><?php echo $gender; ?></span></span><br>
    Birthdate: <span class="white"><?php echo $dob; ?></span>
    <span class="fright">Age: <span class="white"><?php echo getAge($dob); ?></span></span>
</p>
<table class="timetable">
<tr><th>Period</th><th>Course</th><th>Teacher</th><th>Room</th></tr>
<?php
   //This prints out the timetable if there is one. 
   //If there is no timetable, we still have to print out 4 rows in order for the "mtgDate" to be positioned correctly on the page.
   if ($timetable->num_rows == 0) {
     echo "<tr><td colspan=4> no timetable </td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
   } else {
	  $n=1;
      while ($row = mysqli_fetch_assoc($timetable)) {
        $coursecode = formatCourse($row['coursecode']);

        $text = "<td>".$row['period'] ."</td><td>". $coursecode ."</td><td>". $row['teacher'] ."</td><td>". $row['room'] . "</td>";
        echo "<tr>" . $text . "</tr>";
		$n++;
      }
      for(;$n<=4; $n++) {
           echo "<tr><td colspan=4>&nbsp;</td></tr>";
      }
   }
   echo "</table>";
   echo '<p class="fontONE smaller fleft gray" title="Teacher and student course codes for COOP are completely different!"><i>COOP courses won\'t show up here</i></p>';
   if ($timetable->num_rows == 0) {
     echo '<p class="fontONE smaller fleft gray">If there are no courses in the timetable, the student might be off roll.</p>';
   }
?>
</div> <!-- end mkbk section -->

<!-- **************** Begin ssData section [right box in main-top section] ***************** -->
<div id="ssData">
<?php 
   if ($sssInfoFound) {
	  echo '<div class="fright row90"><b>&nbsp;At-Risk&nbsp;</b></div><br clear=all>';
   }
?>
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
<br clear="both" />
</p>
<!-- drop down box
<select name="cars">
 <optgroup label="Swedish Cars">
    <option value="volvo">Volvo</option>
    <option value="saab">Saab</option>
  </optgroup>
  <optgroup label="German Cars">
    <option value="mercedes">Mercedes</option>
    <option value="audi">Audi</option>
  </optgroup>
  </select>
-->

<p class="fontONE smaller fleft gray"><i>This box is only filled in for At-Risk students</i></p>
</div><!-- ************ end ssData **************** -->
<br clear="both">
<p class="fontONE smaller fleft">Guardian Phone: <span class="tan"><?php echo $guardianPhone; ?></span><br>
Guardian Email: <span class="tan"><?php echo str_replace(';','; ',$guardianEmail); ?></span><br>
Standard student email: <span class="tan"><?php echo $loginID?>@gotvdsb.ca</span></p>

<!-- *********** Other admin buttons ************ -->
<?php
if (1===$isTeam) {

	//Date Discussed and Set to today button
	if ($isTeamAdmin == 1) {
		echo '<div id="atRiskBtn">';
		$clickStr = "onclick=\"window.document.location='commentPage.php?ID=$studentID';\"";
		echo '<button class="pure-button" '.$clickStr.'" style="margin:0;background:orange">Go to AtRisk pages</button>';
		echo '</div>';
	}
}
?>

</div><!-- ************ end of box2 *************** -->

</div><!-- ************ end of main-top *********** -->

<br clear="both" />

<!-- ********** start maim-lower section [continues til footer] ******************* -->
<div id="main-lower">

<div id="newComment">

<!-- ************* teacher contact form ************* -->
<table class="pure-table pure-table-bordered">
<tr><td>
<form class="pure-form" method="post" action="contactHandler.php" onsubmit="return validateData2()">

<div class="group">
<fieldset>
<label for="teacherName" class="pure-button2" style="color:darkgreen;border:0;">Teacher (Lastname, Firstname):</label><br>
<input id="teacherName" name="teacherName" type="text" size="35" style="background:#CFD;">
</fieldset>
</div>

<div class="group">
<fieldset>
<span class="navy">Contact Method:</span>
<span class="pure-button2 " onclick="addText('Email');" >Email</span>
<span class="pure-button2 navy"  onclick="addText('Phone');">Phone</span>
<span class="pure-button2 navy"  onclick="addText('Translator');">Translator</span>
<br>
<input style="background-color:#DEF;width:100%" id="contactMethod" name="contactMethod" type=text size=35>
</fieldset>
</div>

<fieldset>
<div class="group">
<label for="dateContacted" class="" style="color:#518;">Date contacted:</label>
<input id="dateContacted" name="dateContacted" type="date" size="15" style="background:#CAF;">
<label for="personContacted" class="" style="color:#620;">&nbsp;&nbsp;&nbsp;&nbsp;Person contacted:</label>
<input id="personContacted" name="personContacted" type="text" size="15" style="background:#FFA;"> 
</div>
<div class="fright smaller">(student, father, <br>mother, guardian, ...)</div>
<div class="fontONE smaller gray">Date format: YYYY-MM-DD</div>
</fieldset>
<textarea name="notes" class="note-text fontONE" rows="5" placeholder="Enter any notes (optional)"></textarea>
<button type="submit" name="submit" class="pure-button" style="margin:0 0.75em;font-weight:bold;">Submit</button>
</form>
<noscript>
Please enable Javascript if this form is not working.
</noscript>
</td></tr>
</table>

</div> <!-- end of newComment -->

<div id="repeatingComments">
<hr>

    <?php echo $message; ?>

    <?php 
    // sending query: get all comments for this student.
    //$sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'),timestamp, login_name, AES_DECRYPT(next_steps,'$masterkeyhash'), id, completed FROM comments WHERE student_number=$student_number ORDER BY timestamp DESC";
    //$sql = "SELECT AES_DECRYPT(notes, '$masterkeyhash'),timestamp, login_name, id, completed FROM comments WHERE studentID=$studentID ORDER BY timestamp DESC";

    $sql = "SELECT teacher, contactMethod, date, notes, timestamp, personContacted FROM tcontact WHERE studentID=? ORDER BY timestamp DESC";
    if ($stmt = $sssDB->prepare($sql)) {
       $stmt->bind_param("i", $studentID);
       $stmt->execute();
	   //this is going to be a bunch of rows of data, so I don't think we'll do $stmt->bind_result($firstname,$lastname); $stmt->fetch()
       $result = $stmt->get_result();
       $stmt->close();
    } else {
       $message_  = 'Invalid query: ' . mysqli_error($sssDB) . "\n<br>";
       $message_ .= 'SQL: ' . $sql;
       die($message_); 
    }
    $row_cnt = mysqli_num_rows($result);
    if ($row_cnt == 0) {
      echo "<p class=\"tan\">No contact has been made with this student yet.</p>";
    }

	#starting displaying the comments and next steps for this student -->    

    //table rows [0] is teacher. [1] is contactMethod.  [2] is date, [3] is notes, [4] timestamp, [5] is personContacted

while ($row = mysqli_fetch_row($result)){ 
   $notes = stripslashes($row[3]); //to undo the addslashes from clean_input()

?>

<div class="box-repeat">
   <table width="100%" class="pure-table">
   <tr>
	<td align="left" class="topcell">

	Teacher: <label class="pure-button2" style="color:darkgreen;border:0;"><?php echo $row[0]?></label>
	Date: <label class="pure-button2" style="color:darkgreen;border:0;"><?php echo $row[2]?></label>
	Contact Method: <label class="pure-button2" style="color:darkgreen;border:0;"><?php echo $row[1]?></label>
	Person Contacted: <label class="pure-button2" style="color:darkgreen;border:0;"><?php echo $row[5]?></label>

	<?php
	if ($notes != "") {
	   echo "<textarea readonly cols=\"50\" class=\"prevComment fontONE\">";
       echo htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')."</textarea>";
    }
    ?>

   <p class="gray"><?php echo $row[4]; ?></p>
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

<div id="footer" class="centered"> Created by Michael Harwood &copy; 2020.  </div>

</div> <!-- end main -->
<?php
#echo var_dump($result);
//echo $timetable->num_rows;
echo $filename;
?>
</body>
</html>
