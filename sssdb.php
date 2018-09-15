<?php
//Databse information that is globally shared
//session_start();
$servername = getenv('IP');

//Load session variables again
$username = $_SESSION["username"];
$password = $_SESSION["password"];
$fullname = $_SESSION["fullname"];
$alpha    = $_SESSION["alpha"];
$isTeam   = $_SESSION["isTeam"];
$studentID = $_SESSION["studentID"];

/*
//TODO: do I need this?
//Checking User not Logged in
if(empty($_SESSION['username'])){
 header('location:index.php');
}
*/

//TODO: do not have every single page making these connections. Put them in a function and only call them if necessary.
/*
// Connecting, selecting database
$mysqli = mysqli_connect($servername, $username, $password, $database);
if (mysqli_connect_errno($mysqli)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die("Program terminated");
}
// Connecting, selecting database
$schoolDB = mysqli_connect($servername, $username, $password, "schoolDB");
if (mysqli_connect_errno($schoolDB)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die("Program terminated");
}
*/
function connectToDB($database, $username, $password) {
   $servername = getenv('IP');
   $db = mysqli_connect($servername, $username, $password, $database);
   if (mysqli_connect_errno($db)) {
       echo "Failed to connect to MySQL database $database : " . mysqli_connect_error();
       die("Program terminated");
   }
   return $db;
}

function runSimpleQuery($mysqli, $sql_) {
    $result = mysqli_query($mysqli, $sql_);
//  if (!$mysqli->error) {
//      printf("Errormessage: %s\n", $mysqli->error);
//  }

    // Check result. This shows the actual query sent to MySQL, and the error. Useful for debugging.
    if (!$result) {
       $message_  = 'Invalid query: ' . mysqli_error($mysqli) . "\n<br>";
       $message_ .= 'SQL: ' . $sql_;
       die($message_);
    }
}

//this is used on every page, so put it into a function for easy modification
function printHeader($fullname, $alpha, $isTeam = null) { //if you don't add isTeam it will still work.
   $text = "<h4> $fullname ";
   if (0===$isTeam || null===$isTeam) {
      $text.= '<span class="box fontONE">VIEW ONLY</span>';
   } else {
      $text.= "<span class=\"box fontONE\"> Dept: $alpha</span>";
   }
   $text .= "</h4>";
   echo $text;
}

//This condenses multiple white spaces down to a single space (I think)
function removeWhiteSpace($text)
{
    $text = preg_replace('/[\t\n\r\0\x0B]/', '', $text);
    $text = preg_replace('/([\s])\1+/', ' ', $text);
    $text = trim($text);
    return $text;
}

function clean_input($data) {
   //$data = addslashes(htmlspecialchars(trim($data))); NO!!
   $data = trim(strip_tags(addslashes($data)));
   return $data;
}
/*
This set of sanitizing inputs works the best. 
mysqli_real_escape_string() always just returns an empty string
htmlspecialchars makes all < and > and & into &lt; &gt; &amp; --- and it's displayed like this on screen.
addslashes is vital or else the SQL query fails
strip_tags gets rid of HTML tags <h2> ... and php tags. 

Most of these things can still be bypassed if you are clever enough and choose special character sets.
The correct way to do this is using prepared statements (or PDO).

*/
?>

