<?php
session_start();
include('db.php');

//get commentID from URL parameter
//$commentID = $_GET['ID'];

$login = $fullNAME= $alpha_ = "";

if(isset($_POST['submit'])) {

        /* Check all input.
           Do this here instead of using functions because there are too many variables
           to make global.
           Do the verification in reverse order, so that the error messages will start with
           first field.  */

        $alpha_ = clean_input($_POST['alpha_']);
        if (empty($alpha_))  $error_message = "You must enter alpha";

        $fullNAME = clean_input($_POST['fullNAME']);
        if (empty($fullNAME))  $error_message = "You must enter a full name";

        //check if login already exists. If it does the query will die with an error.
        $login = clean_input($_POST["login"]);
        if (!empty($login)) {
                if (isDuplicate_login($login)) $error_message = "This login already exists!";
        }

        if (empty($login ))  $error_message = "You must enter a login name";

        if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";

 	//if corrent, then add to database
        if (empty($error_message)) {

        	$sql="CREATE USER '$login'@'localhost' IDENTIFIED BY 'gefdcab'";
                $result = mysqli_query($mysqli, $sql); // Perform Query
                // Check result
                // This shows the actual query sent to MySQL, and the error. Useful for debugging.
                if (!$result) {
                        $message  = 'Invalid query: ' . mysql_error() . "\n";
                        $message .= 'Whole query: ' . $query;
                        $message .= 'SQL: ' . $sql;
                        die($message);
                }

		$sql="GRANT SELECT, INSERT, UPDATE ON sssDB.* TO '$login'@'localhost';FLUSH PRIVILEGES;";
                $result = mysqli_query($mysqli, $sql); // Perform Query
                // Check result
                if (!$result) {
                        $message  = 'Invalid query: ' . mysql_error() . "\n";
                        $message .= 'Whole query: ' . $query;
                        $message .= 'SQL: ' . $sql;
                        die($message);
                }

                $sql = "INSERT INTO users (login_name, full_name,  alpha) VALUES ('$login', '$fullNAME', '$alpha_')";
                $result = mysqli_query($mysqli, $sql); // Perform Query
                // Check result
                if (!$result) {
                        $message  = 'Invalid query: ' . mysql_error() . "\n";
                        $message .= 'Whole query: ' . $query;
                        $message .= 'SQL: ' . $sql;
                        die($message);
                }

                $login = $fullNAME= $alpha_ = "";
                $error_message = "<div class=\"error green\">" . "Student successfully added." . "</div>";
		sleep(5); //does sleep() work?
                header("Location: home.php");
                //die();
	}
}

function isDuplicate_login($login) {
        global $mysqli;
        $sql = "SELECT * FROM users WHERE login_name = '" . $login . "'";
        $result = mysqli_query($mysqli, $sql);
        if (!$result) {
                die("$studentNum Query to search users failed \n $sql");
        }
        $row_cnt = mysqli_num_rows($result);
        if ($row_cnt > 0) return true;
        return false;
}

?>

<!DOCTYPE html>
<html>

<head lang="en">
<meta charset="UTF-8">
<title>
Student Waitlist Database -- administrative options
</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/sssDB.css">
</head>

<div id="header">
<a class="fa fa-arrow-left nav-button fleft" href="home.php">  Go Back</a>
<h1>Waitlist database administration</h1>
<h2><?php echo "$fullname <span class=\"box fontONE\">Alpha=\"$alpha\"</span>"; ?></h2>
</div>

<?php echo $commentID. " " . $sql; ?>

<h3 class="fleft">Add User</h3>
<br clear="both">
<form class="pure-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<fieldset>
<legend>
<?php echo $error_message; ?>

<table>
<tr>
<td class="tcol1">
<p>Login Name:</p>
</td><td class="tcol2">
<input name="login" type="text">
</td>
</tr><tr>
<td class="tcol1">
<p>Full Name</p>
</td><td class="tcol2">
<input name="fullNAME" type="text">
</td>
</tr><tr>
<td class="tcol1">
<p>Alpha:</p>
</td><td class="tcol2">
<input name="alpha_" type="text">
</td>
</tr>
</table>
</legend>
<button type="submit" name="submit" class="pure-button fleft" style="margin:0 0.75em;font-weight:bold;">Submit</button>
</fieldset>
</form>



</body>
</html>
