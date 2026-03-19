<?php
/* destroy the session and
restart from the login page */
session_start();
session_destroy();

session_start();
$_SESSION['first_access'] = false; // avoid doing the first access procedure again

header("Location: index.php"); // restart from the login page
