<?php 
//This is the page where teachers (readonly) can see student info
/*******************************************************************************
   Name: studentInfo.php
   Called from: home.php / studentFind.php
   Purpose: based on commentPage.php, but with sensitive information removed.
	It's for general teacher view)
   Tables used: schoolDB/students, sssDB/sssInfo, sssDB/social_workers
   Calls: 
   Transfers control to: home.php
******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('../../DB-admin/php_includes/sssDB.inc.php');
require_once('common.inc.php');

$sssDB = connectToDB("sssDB", $sql_user, $sql_pass);
$schoolDB = connectToDB("schoolDB", $sql_user, $sql_pass);

$studentID = $_GET['ID'];
$_SESSION["studentID"] = $studentID;

//$error_message="";
// if (empty($lastname))  $error_message = "You must enter a lastname";
// if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";

$sql = "SELECT firstname, lastname, studentID, gender, dob, guardianPhone, guardianEmail FROM students WHERE studentID = ?";
  if ($stmt = $schoolDB->prepare($sql)) {
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $studentID, $gender, $dob, $guardianPhone, $guardianEmail);
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
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
<?php
    echo "<style>#main-lower{display:none;}</style>";
?>
</head>

<body>
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
    <a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
    <h1>Student Success Database</h1>
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
	# Linux command to uppercase all files in a folder::>>>> for file in *; do mv -- "$file" "${file^^}"; done
    $filename1 = "$photoDir1/$studentID.BMP"; //absolute path
    $filename2 = "$photoDir2/$studentID.BMP"; //relative path for public_html (browsers)
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
   if ($timetable->num_rows == 0) {
     echo "<tr><td colspan=4> no timetable </td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
     echo "<tr><td colspan=4>&nbsp;</td></tr>";
   } else {
      while ($row = mysqli_fetch_assoc($timetable)) {
        $coursecode = formatCourse($row['coursecode']);

        $text = "<td>".$row['period'] ."</td><td>". $coursecode ."</td><td>". $row['teacher'] ."</td><td>". $row['room'] . "</td>";
        echo "<tr>" . $text . "</tr>";
      }
   }
?>
</table>
<p class="fontONE smaller fleft gray" title="Teacher and student course codes for COOP are completely different!"><i>COOP courses won't show up here</i></p>
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
    Grade: <input id="grade" type="text" size="2" name="grade" value="<?php echo $grade; ?>">
</span>
<span class="fright">
    Social Worker: 
<select name="swid">
   <?php echo $sw_name_opt; ?>
</select>
</span>
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
<p>

</div><!-- ************ end ssData **************** -->
<p class="fontONE smaller fleft">Guardian Phone: <?php echo $guardianPhone; ?><br>
Guardian Email: <?php echo str_replace(';','; ',$guardianEmail); ?></p>
</div><!-- ************ end of box2 *************** -->


</div><!-- ************ end of main-top *********** -->

<br clear="both" />

<!-- ********** start maim-lower section [continues til footer] ******************* -->
<div id="main-lower">

<p>&nbsp;</p>
<hr>
</div> <!-- end main-lower-->

<div id="footer" class="centered"> The At-Risk-Student team will be able to read and enter comments here.</div>

</div> <!-- end main -->
<?php
#echo var_dump($result);
//echo $timetable->num_rows;
echo $filename;
?>
</body>
</html>
