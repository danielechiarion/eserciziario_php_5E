<?php
session_start(); // recover session data

/* section to remove the current session of the account
and return to the login account */
$_SESSION['username'] = null;
$_SESSION['logged_in'] = false;
$_SESSION['ID'] = null;

header("Location: login.php");