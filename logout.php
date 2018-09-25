<?php
//Used to logout the user (destroys the session variables)
require_once('../../DB-admin/php_includes/sssDB.inc.php');

session_start();
session_destroy();

header("Location: $home");
?>
