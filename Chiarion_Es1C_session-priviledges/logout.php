<?php
session_start(); // recover session data

//session_destroy(); // just destroy the session
$_SESSION['username'] = null;
$_SESSION['logged_in'] = false;
$_SESSION['ID'] = null;

header("Location: login.php");