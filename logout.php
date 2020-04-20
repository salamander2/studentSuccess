<?php
//Used to logout the user (destroys the session variables)
require_once('../../DB-admin/php_includes/sssDB.inc.php');

session_start();
// use both unset and destroy for compatibility
// with all browsers and all versions of PHP
session_unset();
session_destroy();

header("Location: $home");
?>
